<?php
/**
 * Stripe Gateway
 *
 * @package checkout-plugins-stripe-woo
 * @since 0.0.1
 */

namespace CPSW\Admin;

use CPSW\Gateway\Stripe\Stripe_Api;
use CPSW\Inc\Traits\Get_Instance;
use CPSW\Inc\Logger;
use CPSW\Inc\Helper;
use CPSW\Gateway\Stripe\Webhook;
use Stripe\OAuth;
use WC_Admin_Settings;
use Exception;

/**
 * Admin Controller - This class is used to update or delete stripe settings.
 *
 * @package checkout-plugins-stripe-woo
 * @since 0.0.1
 */
class Admin_Controller {

	use Get_Instance;

	/**
	 * Stripe settings fields configuration array
	 *
	 * @var $settings_keys array
	 */
	private $settings_keys = [
		'cpsw_test_pub_key',
		'cpsw_test_secret_key',
		'cpsw_test_con_status',
		'cpsw_pub_key',
		'cpsw_secret_key',
		'cpsw_con_status',
		'cpsw_mode',
		'cpsw_live_webhook_secret',
		'cpsw_test_webhook_secret',
		'cpsw_debug_log',
		'cpsw_account_id',
		'cpsw_auto_connect',
	];

	/**
	 * Navigation links for the payment method pages.
	 *
	 * @var $navigation array
	 */
	public $navigation = [];

	/**
	 * Stripe settings are stored in this array.
	 *
	 * @var $settings array
	 */
	private $settings = [];

	/**
	 * The secret key used for Stripe API authentication.
	 *
	 * @var string
	 */
	private $secret_key = '';

	/**
	 * Constructor
	 *
	 * @since 0.0.1
	 */
	public function __construct() {
		$this->init();

		foreach ( $this->settings_keys as $key ) {
			$this->settings[ $key ] = get_option( $key );
		}

		$this->navigation = apply_filters(
			'cpsw_settings_navigation',
			[
				'cpsw_api_settings'     => __( 'Stripe API Settings', 'checkout-plugins-stripe-woo' ),
				'cpsw_stripe'           => __( 'Credit Cards', 'checkout-plugins-stripe-woo' ),
				'cpsw_express_checkout' => __( 'Express Checkout', 'checkout-plugins-stripe-woo' ),
				'cpsw_alipay'           => __( 'Alipay', 'checkout-plugins-stripe-woo' ),
				'cpsw_ideal'            => __( 'iDEAL', 'checkout-plugins-stripe-woo' ),
				'cpsw_klarna'           => __( 'Klarna', 'checkout-plugins-stripe-woo' ),
				'cpsw_sepa'             => __( 'SEPA', 'checkout-plugins-stripe-woo' ),
				'cpsw_p24'              => __( 'Przelewy24', 'checkout-plugins-stripe-woo' ),
				'cpsw_wechat'           => __( 'WeChat', 'checkout-plugins-stripe-woo' ),
				'cpsw_bancontact'       => __( 'Bancontact', 'checkout-plugins-stripe-woo' ),
			]
		);
	}

	/**
	 * Init
	 *
	 * @since 0.0.1
	 */
	public function init() {
		add_filter( 'woocommerce_settings_tabs_array', [ $this, 'add_settings_tab' ], 50 );
		add_action( 'woocommerce_settings_tabs_cpsw_api_settings', [ $this, 'settings_tab' ] );
		add_action( 'woocommerce_update_options_cpsw_api_settings', [ $this, 'update_settings' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		/* NEW METHODS */
		add_action( 'admin_init', [ $this, 'redirect_if_manually_saved' ], 1 );
		add_action( 'woocommerce_admin_field_cpsw_stripe_connect', [ $this, 'stripe_connect' ] );
		add_action( 'woocommerce_admin_field_cpsw_account_id', [ $this, 'account_id' ] );
		add_action( 'woocommerce_admin_field_cpsw_webhook_url', [ $this, 'webhook_url' ] );
		add_action( 'woocommerce_admin_field_cpsw_delete_webhook', [ $this, 'delete_webhook_field' ] );
		add_action( 'woocommerce_admin_field_cpsw_create_webhook', [ $this, 'create_webhook_field' ] );
		add_action( 'woocommerce_admin_field_cpsw_live_delete_webhook', [ $this, 'delete_live_webhook_field' ] );
		add_action( 'woocommerce_admin_field_cpsw_live_create_webhook', [ $this, 'create_live_webhook_field' ] );
		add_action( 'woocommerce_admin_field_cpsw_express_checkout_preview', [ $this, 'express_checkout_preview' ] );
		add_action( 'woocommerce_admin_field_express_checkout_notice', [ $this, 'express_checkout_notice' ] );

		add_action( 'admin_init', [ $this, 'admin_options' ] );
		add_action( 'admin_init', [ $this, 'initialise_warnings' ] );

		add_action( 'wp_ajax_cpsw_test_stripe_connection', [ $this, 'connection_test' ] );
		add_action( 'wp_ajax_cpsw_disconnect_account', [ $this, 'disconnect_account' ] );
		add_action( 'wp_ajax_cpsw_delete_webhook', [ $this, 'delete_webhook_action' ] );
		add_action( 'wp_ajax_cpsw_create_webhook', [ $this, 'create_webhook_action' ] );
		add_action( 'wp_ajax_cpsw_js_errors', [ $this, 'js_errors' ] );
		add_action( 'wp_ajax_nopriv_cpsw_js_errors', [ $this, 'js_errors' ] );

		add_action( 'woocommerce_settings_save_cpsw_api_settings', [ $this, 'check_connection_on_updates' ] );
		add_filter( 'woocommerce_save_settings_checkout_cpsw_express_checkout', [ $this, 'cpsw_express_checkout_option_updates' ] );
		add_filter( 'cpsw_settings', [ $this, 'filter_settings_fields' ], 1 );
		add_action( 'update_option_cpsw_mode', [ $this, 'update_mode' ], 10, 3 );

		add_action( 'admin_head', [ $this, 'add_custom_css' ] );
		add_action( 'woocommerce_sections_cpsw_api_settings', [ $this, 'add_breadcrumb' ] );
		add_filter( 'admin_footer_text', [ $this, 'add_manual_connect_link' ] );

		if ( isset( $_GET['section'] ) && 0 === strpos( sanitize_text_field( $_GET['section'] ), 'cpsw_' ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_filter( 'woocommerce_get_sections_checkout', [ $this, 'add_settings_links' ] );
			add_filter( 'woocommerce_get_sections_cpsw_api_settings', [ $this, 'add_settings_links' ] );
		}
		add_filter( 'woocommerce_get_settings_checkout', [ $this, 'checkout_settings' ], 10, 2 );
	}

	/**
	 * Saves section cpsw_express_checkout_data to cpsw_stripe settings
	 *
	 * @return mixed
	 * @since 1.1.0
	 */
	public function cpsw_express_checkout_option_updates() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return false;
		}

		// This key used only for nonce verification.
		if ( empty( $_POST['cpsw_express_checkout_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['cpsw_express_checkout_nonce'] ), 'cpsw_express_checkout_nonce' ) ) {
			return false;
		}

		unset( $_POST['cpsw_express_checkout_nonce'] );

		if ( isset( $_POST['save'] ) ) {
			$express_checkout = [];
			$radio_checkbox   = [
				'express_checkout_enabled'               => 'no',
				'express_checkout_product_sticky_footer' => 'no',
			];
			foreach ( $_POST as $key => $value ) {
				if ( 0 === strpos( $key, 'cpsw_express_checkout' ) ) {
					$k = sanitize_text_field( str_replace( 'cpsw_', '', $key ) );
					if ( ! empty( $radio_checkbox ) && in_array( $k, array_keys( $radio_checkbox ), true ) ) {
						$express_checkout[ $k ] = 'yes';
						unset( $radio_checkbox[ $k ] );
					} else {
						if ( is_array( $value ) ) {
							$express_checkout[ $k ] = array_map( 'sanitize_text_field', $value );
						} else {
							$express_checkout[ $k ] = sanitize_text_field( $value );
						}
					}

					unset( $_POST[ $key ] );
				}
			}

			if ( ! empty( $express_checkout ) ) {
				$cpsw_stripe                              = get_option( 'woocommerce_cpsw_stripe_settings' );
				$cpsw_stripe['express_checkout_location'] = [];
				$cpsw_stripe                              = array_merge( $cpsw_stripe, $radio_checkbox, $express_checkout );
				update_option( 'woocommerce_cpsw_stripe_settings', $cpsw_stripe );
			}
		}

		return false;
	}

	/**
	 * WooCommerce Init
	 *
	 * @since 0.0.1
	 */
	public function initialise_warnings() {
		// If keys are not set bail.
		if ( ! $this->are_keys_set() ) {
			add_action( 'admin_notices', [ $this, 'are_keys_set_check' ] );
		}

		// If no SSL bail.
		if ( 'live' === Helper::get_payment_mode() && ! is_ssl() ) {
			add_action( 'admin_notices', [ $this, 'ssl_not_connect' ] );
		}

		// IF stripe connection established successfully. Showing admin notice nonce verification not required.
		if ( isset( $_GET['cpsw_call'] ) && ! empty( $_GET['cpsw_call'] ) && 'success' === $_GET['cpsw_call'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_action( 'admin_notices', [ $this, 'connect_success_notice' ] );
		}

		// IF stripe connection not established successfully. Showing admin notice nonce verification not required.
		if ( isset( $_GET['cpsw_call'] ) && ! empty( $_GET['cpsw_call'] ) && 'failed' === $_GET['cpsw_call'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_action( 'admin_notices', [ $this, 'connect_failed_notice' ] );
		}

		// Add notice if missing webhook secret key. Showing admin notice nonce verification not required.
		if ( isset( $_GET['page'] ) && 'wc-settings' === $_GET['page'] && ! Helper::get_webhook_secret() ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_action( 'admin_notices', [ $this, 'webhooks_missing_notice' ] );
		}
	}

	/**
	 * Enqueue Scripts
	 *
	 * @since 0.0.1
	 */
	public function enqueue_scripts() {
		$version               = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : CPSW_VERSION;
		$allow_scripts_methods = apply_filters(
			'cpsw_allow_admin_scripts_methods',
			[
				'cpsw_stripe',
				'cpsw_alipay',
				'cpsw_ideal',
				'cpsw_klarna',
				'cpsw_sepa',
				'cpsw_bancontact',
				'cpsw_p24',
				'cpsw_wechat',
			]
		);

		if ( false !== get_transient( 'cpsw_stripe_sepa_client_secret' ) ) {
			wp_register_script( 'cpsw-stripe-elements-external', 'https://js.stripe.com/v3/', [], $version, true );
			wp_enqueue_script( 'cpsw-stripe-elements-external' );

			wp_register_script( 'cpsw-stripe-elements', plugins_url( 'assets/js/stripe-elements.js', __FILE__ ), [ 'jquery', 'cpsw-stripe-elements-external' ], $version, true );
			wp_enqueue_script( 'cpsw-stripe-elements' );

			$public_key = ( 'live' === Helper::get_payment_mode() ) ? Helper::get_setting( 'cpsw_pub_key' ) : Helper::get_setting( 'cpsw_test_pub_key' );
			wp_localize_script(
				'cpsw-stripe-elements',
				'cpsw_admin_stripe_elements',
				[
					'public_key'    => $public_key,
					'cpsw_version'  => CPSW_VERSION,
					'is_ssl'        => is_ssl(),
					'mode'          => Helper::get_payment_mode(),
					'client_secret' => get_transient( 'cpsw_stripe_sepa_client_secret' ),
					'get_home_url'  => get_home_url(),
				]
			);
			delete_transient( 'cpsw_stripe_sepa_client_secret' );
		}

		// Admin enqueue scripts using $_GET to determine scope, nonce verification not required.
		if ( isset( $_GET['page'] ) && 'wc-settings' === $_GET['page'] && isset( $_GET['tab'] ) && ( 'cpsw_api_settings' === $_GET['tab'] || isset( $_GET['section'] ) && ( in_array( sanitize_text_field( $_GET['section'] ), $allow_scripts_methods, true ) ) ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_register_style( 'cpsw-admin-style', plugins_url( 'assets/css/admin.css', __FILE__ ), [], $version, 'all' );
			wp_enqueue_style( 'cpsw-admin-style' );

			wp_register_script( 'cpsw-admin-js', plugins_url( 'assets/js/admin.js', __FILE__ ), [ 'jquery' ], $version, true );
			wp_enqueue_script( 'cpsw-admin-js' );

			wp_localize_script(
				'cpsw-admin-js',
				'cpsw_ajax_object',
				apply_filters(
					'cpsw_admin_localize_script_args',
					[
						'site_url'                 => get_site_url() . '/wp-admin/admin.php?page=wc-settings',
						'ajax_url'                 => admin_url( 'admin-ajax.php' ),
						'cpsw_mode'                => Helper::get_payment_mode(),
						'admin_nonce'              => wp_create_nonce( 'cpsw_admin_nonce' ),
						'dashboard_url'            => admin_url( 'admin.php?page=wc-settings&tab=cpsw_api_settings' ),
						'generic_error'            => __( 'Something went wrong! Please reload the page and try again.', 'checkout-plugins-stripe-woo' ),
						'test_btn_label'           => __( 'Connect to Stripe', 'checkout-plugins-stripe-woo' ),
						'stripe_key_notice'        => __( 'Please enter all keys to connect to stripe.', 'checkout-plugins-stripe-woo' ),
						'stripe_key_error'         => __( 'You must enter your API keys or connect the plugin before performing a connection test. Mode:', 'checkout-plugins-stripe-woo' ),
						'stripe_key_unavailable'   => __( 'Keys Unavailable.', 'checkout-plugins-stripe-woo' ),
						'stripe_disconnect'        => __( 'Your Stripe account has been disconnected.', 'checkout-plugins-stripe-woo' ),
						'delete_webhook'           => __( 'Your Stripe webhook secret key has been deleted.', 'checkout-plugins-stripe-woo' ),
						'create_webhook'           => __( 'Your Stripe webhook secret key has been created.', 'checkout-plugins-stripe-woo' ),
						'stripe_connect_other_acc' => __( 'You can connect other Stripe account now.', 'checkout-plugins-stripe-woo' ),
						'is_connected'             => $this->is_stripe_connected(),
						// Localizing get values for use in JS, nonce verification not required.
						'is_manually_connected'    => isset( $_GET['connect'] ) ? sanitize_text_field( $_GET['connect'] ) : '', //phpcs:ignore WordPress.Security.NonceVerification.Recommended
						'cpsw_admin_settings_tab'  => isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : '', //phpcs:ignore WordPress.Security.NonceVerification.Recommended
						'cpsw_admin_current_page'  => isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : '', //phpcs:ignore WordPress.Security.NonceVerification.Recommended
					]
				)
			);
		}

		// Admin enqueue scripts using $_GET to determine scope, nonce verification not required.
		if ( isset( $_GET['page'] ) && 'wc-settings' === $_GET['page'] && isset( $_GET['tab'] ) && 'checkout' === $_GET['tab'] && isset( $_GET['section'] ) && 'cpsw_express_checkout' === $_GET['section'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_register_style( 'cpsw-express-checkout-style', CPSW_URL . 'assets/css/express-checkout.css', [], $version, 'all' );
			wp_enqueue_style( 'cpsw-express-checkout-style' );

			wp_register_script( 'cpsw-stripe-external', 'https://js.stripe.com/v3/', [], $version, true );
			wp_enqueue_script( 'cpsw-stripe-external' );

			wp_register_script( 'cpsw-express-checkout-js', plugins_url( 'assets/js/express-checkout.js', __FILE__ ), [ 'jquery', 'cpsw-stripe-external' ], $version, true );
			wp_enqueue_script( 'cpsw-express-checkout-js' );

			$public_key  = ( 'live' === Helper::get_payment_mode() ) ? Helper::get_setting( 'cpsw_pub_key' ) : Helper::get_setting( 'cpsw_test_pub_key' );
			$button_text = empty( Helper::get_setting( 'express_checkout_button_text', 'cpsw_stripe' ) ) ? __( 'Pay now', 'checkout-plugins-stripe-woo' ) : Helper::get_setting( 'express_checkout_button_text', 'cpsw_stripe' );

			wp_localize_script(
				'cpsw-express-checkout-js',
				'cpsw_express_checkout',
				apply_filters(
					'cpsw_express_checkout_localize_args',
					[
						'public_key'   => $public_key,
						'cpsw_version' => CPSW_VERSION,
						'style'        => [
							'text'  => $button_text,
							'theme' => Helper::get_setting( 'express_checkout_button_theme', 'cpsw_stripe' ),
						],
						'icons'        => [
							'applepay_gray'   => CPSW_URL . 'assets/icon/apple-pay-gray.svg',
							'applepay_light'  => CPSW_URL . 'assets/icon/apple-pay-light.svg',
							'gpay_light'      => CPSW_URL . 'assets/icon/gpay_light.svg',
							'gpay_gray'       => CPSW_URL . 'assets/icon/gpay_gray.svg',
							'payment_request' => CPSW_URL . 'assets/icon/payment-request-icon.svg',
						],
						'messages'     => [
							/* translators: Html Markup*/
							'no_method'     => sprintf( __( 'No payment method detected. Either your browser is not supported or you do not have save cards. For more details read %1$1sdocument$2$2s.', 'checkout-plugins-stripe-woo' ), '<a href="https://stripe.com/docs/stripe-js/elements/payment-request-button#html-js-testing" target="_blank">', '</a>' ),
							'checkout_note' => __( 'NOTE: Title and Tagline appears only on Checkout page.', 'checkout-plugins-stripe-woo' ),
							'default_text'  => __( 'Pay now', 'checkout-plugins-stripe-woo' ),
						],
					]
				)
			);
		}
	}

	/**
	 * Keys are not set.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function are_keys_set_check() {
		// $_GET values used to determine scope of admin notice, nonce verification not required in this function.
		if ( ! isset( $_GET['cpsw_connect_nonce'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET['tab'] ) && 'checkout' === $_GET['tab'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				/* translators: %1$1s HTML Markup */
				echo wp_kses_post( sprintf( '<div class="notice notice-error"><p>' . __( 'You Stripe Publishable and Secret Keys are not set correctly. You can connect to Stripe and correct them from <a href="%1$1s">here.</a>', 'checkout-plugins-stripe-woo' ) . '</p></div>', admin_url( 'admin.php?page=wc-settings&tab=cpsw_api_settings' ) ) );
			} elseif ( isset( $_GET['page'] ) && 'wc-settings' === $_GET['page'] && isset( $_GET['tab'] ) && 'cpsw_api_settings' === $_GET['tab'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$mode = '';
				if ( 'live' === $this->settings['cpsw_mode'] && ( empty( $this->settings['cpsw_pub_key'] ) || empty( $this->settings['cpsw_secret_key'] ) ) ) {
					$mode = 'live';
				} elseif ( 'test' === $this->settings['cpsw_mode'] && ( empty( $this->settings['cpsw_test_pub_key'] ) || empty( $this->settings['cpsw_test_secret_key'] ) ) ) {
					$mode = 'test';
				}

				if ( ! empty( $mode ) ) {
					$stripe_connect_link = '<a href=' . $this->get_stripe_connect_url() . '>' . __( 'Stripe Connect', 'checkout-plugins-stripe-woo' ) . '</a>';

					if ( isset( $_GET['connect'] ) && 'manually' === $_GET['connect'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
						$manual_api_link = '<a href="' . admin_url() . 'admin.php?page=wc-settings&tab=cpsw_api_settings" class="cpsw_connect_hide_btn">' . __( 'Hide API keys', 'checkout-plugins-stripe-woo' ) . '</a>';
					} else {
						$manual_api_link = '<a href="' . admin_url() . 'admin.php?page=wc-settings&tab=cpsw_api_settings&connect=manually" class="cpsw_connect_mn_btn">' . __( 'Manage API keys manually', 'checkout-plugins-stripe-woo' ) . '</a>';
					}
					/* translators: %1$1s: mode, %2$2s, %3$3s: HTML Markup */
					echo wp_kses_post( sprintf( '<div class="notice notice-error"><p>' . __( 'Stripe Keys for %1$1s mode are not set correctly. Reconnect via %2$2s or %3$3s', 'checkout-plugins-stripe-woo' ) . '</p></div>', $mode, $stripe_connect_link, $manual_api_link ) );
				}
			}
		}
	}

	/**
	 * Check for SSL and show warning.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function ssl_not_connect() {
		echo wp_kses_post( '<div class="notice notice-error"><p>' . __( 'No SSL was detected, Stripe live mode requires SSL.', 'checkout-plugins-stripe-woo' ) . '</p></div>' );
	}

	/**
	 * Connection success notice.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function connect_success_notice() {
		echo wp_kses_post( '<div class="notice notice-success is-dismissible"><p>' . __( 'Your Stripe account has been connected to your WooCommerce store. You may now accept payments in live and test mode.', 'checkout-plugins-stripe-woo' ) . '</p></div>' );
	}

	/**
	 * Connection failed notice.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function connect_failed_notice() {
		echo wp_kses_post( '<div class="notice notice-error is-dismissible"><p>' . __( 'We were not able to connect your Stripe account. Please try again. ', 'checkout-plugins-stripe-woo' ) . '</p></div>' );
	}

	/**
	 * Webhooks missing notice.
	 *
	 * @since 1.4.2
	 *
	 * @return void
	 */
	public function webhooks_missing_notice() {
		/* translators: %1$s Webhook secret page link, %2$s Webhook guide page link  */
		echo wp_kses_post( '<div class="notice notice-error"><p>' . sprintf( __( 'Stripe requires using the %1$swebhook%2$s. %3$sWebhook Guide%4$s ', 'checkout-plugins-stripe-woo' ), '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=cpsw_api_settings' ) . '">', '</a>', '<a href="https://checkoutplugins.com/docs/stripe-card-payments/#webhook" target="_blank">', '</a>' ) . '</p></div>' );
	}

	/**
	 * Insufficient permission notice.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function insufficient_permission() {
		echo wp_kses_post( '<div class="notice notice-error is-dismissible"><p>' . __( 'Error: The current user doesn\'t have sufficient permissions to perform this action. Please reload the page and try again.', 'checkout-plugins-stripe-woo' ) . '</p></div>' );
	}

	/**
	 * This method is used to update stripe options to the database.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options settings array of the stripe.
	 */
	public function update_options( $options ) {
		if ( ! is_array( $options ) ) {
			return false;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		foreach ( $options as $key => $value ) {
			update_option( $key, $value );
		}
	}

	/**
	 * This method is used to retrieve webhooks.
	 *
	 * @since 1.5.0
	 *
	 * @param string $id Webhook id.
	 *
	 * @return obj
	 */
	public function retrieve_webhooks( $id ) {
		$stripe_api = new Stripe_Api();
		$response   = $stripe_api->webhooks( 'retrieve', [ $id ] );

		if ( isset( $response['success'] ) && 1 === absint( $response['success'] ) && isset( $response['data'] ) ) {
			return $response['data'];
		} else {
			Logger::error( sprintf( 'Error retrieve Stripe webhook. Reason: %2$s', Helper::get_payment_mode(), $response['message'] ), true );
			return false;
		}
	}

	/**
	 * This method is used to delete webhooks.
	 *
	 * @since 1.5.0
	 *
	 * @param string $id Webhook id.
	 * @param string $mode Webhook mode.
	 *
	 * @return void
	 */
	public function delete_webhooks( $id, $mode = '' ) {
		if ( empty( $id ) ) {
			return;
		}

		if ( ! empty( $mode ) && Helper::get_payment_mode() !== $mode ) {
			$this->secret_key = '';
			if ( 'live' === $mode ) {
				$this->secret_key = Helper::get_setting( 'cpsw_secret_key' );
			} else {
				$this->secret_key = Helper::get_setting( 'cpsw_test_secret_key' );
			}

			if ( ! empty( $this->secret_key ) ) {
				add_filter(
					'cpsw_get_secret_key',
					function() {
						return $this->secret_key;
					},
					99
				);
			}
		}

		$stripe_api = new Stripe_Api();
		$response   = $stripe_api->webhooks( 'delete', [ $id ] );

		if ( isset( $response['success'] ) && 1 === absint( $response['success'] ) && isset( $response['data'] ) ) {
			$this->remove_webhook_secret_settings_keys( $mode );
			return true;
		} else {
			Logger::error( sprintf( 'Error deleting Stripe webhook. Reason: %2$s', Helper::get_payment_mode(), $response['message'] ), true );
			$this->remove_webhook_secret_settings_keys( $mode );
			return $response['message'];
		}
	}

	/**
	 * Remove webhook secret settings keys
	 *
	 * @since 1.5.0
	 * @param string $mode Webhook mode.
	 * @return void
	 */
	public function remove_webhook_secret_settings_keys( $mode ) {
		if ( 'live' === $mode ) {
			update_option( 'cpsw_live_webhook_secret', '' );
			update_option( 'cpsw_live_webhook_id', '' );
		} elseif ( 'test' === $mode ) {
			update_option( 'cpsw_test_webhook_secret', '' );
			update_option( 'cpsw_test_webhook_id', '' );
		}
	}

	/**
	 * This method is used to create webhooks.
	 *
	 * @since 1.5.0
	 *
	 * @param string $type Create webhook type.
	 * @param string $mode Create webhook mode.
	 * @return void
	 */
	public function create_webhooks( $type, $mode = '' ) {
		if ( ! isset( $type ) ) {
			return;
		}

		if ( false === $this->is_stripe_connected() ) {
			return;
		}

		if ( empty( $mode ) ) {
			$modes = [ 'test', 'live' ];
		} else {
			$modes = [ $mode ];
		}

		$webhooks_created = 0;

		$error_message = '';

		foreach ( $modes as $mode ) {

			if ( Helper::get_payment_mode() !== $mode ) {
				$this->secret_key = '';
				if ( 'live' === $mode ) {
					$this->secret_key = Helper::get_setting( 'cpsw_secret_key' );
				} else {
					$this->secret_key = Helper::get_setting( 'cpsw_test_secret_key' );
				}

				if ( ! empty( $this->secret_key ) ) {
					add_filter(
						'cpsw_get_secret_key',
						function() {
							return $this->secret_key;
						},
						99
					);
				}
			}

			$stripe_api = new Stripe_Api();

			$data = [
				'api_version'    => '2020-03-02',
				'url'            => esc_url( get_home_url() . '/wp-json/cpsw/v1/webhook' ),
				'enabled_events' => [
					'charge.failed',
					'charge.succeeded',
					'source.chargeable',
					'payment_intent.succeeded',
					'charge.refunded',
					'charge.dispute.created',
					'charge.dispute.closed',
					'review.opened',
					'review.closed',
				],
			];

			$response = $stripe_api->webhooks( 'create', [ $data ] );

			if ( isset( $response['success'] ) && 1 === absint( $response['success'] ) && isset( $response['data'] ) ) {
				if ( 'live' === $mode ) {
					update_option( 'cpsw_live_webhook_secret', $response['data']->secret );
					update_option( 'cpsw_live_webhook_id', $response['data']->id );
				} elseif ( 'test' === $mode ) {
					update_option( 'cpsw_test_webhook_secret', $response['data']->secret );
					update_option( 'cpsw_test_webhook_id', $response['data']->id );
				}

				$webhooks_created++;
			} else {
				$error_message = $response['message'];
				Logger::error( sprintf( 'Error creating Stripe webhook. Mode: %1$s. Reason: %2$s', $mode, $response['message'] ), true );
			}
		}

		if ( count( $modes ) === $webhooks_created ) {
			return true;
		}
		return $error_message;
	}

	/**
	 * This method is used to stripe connect button.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value Field name in string.
	 */
	public function stripe_connect( $value ) {
		if ( true === $this->is_stripe_connected() ) {
			return;
		}

		$label        = __( 'Connect with Stripe', 'checkout-plugins-stripe-woo' );
		$label_status = __( 'We make it easy to connect Stripe to your site. Click the Connect button to go through our connect flow.', 'checkout-plugins-stripe-woo' );
		$sec_var      = '';
		$manual_link  = true;

		/**
		 * Action before conection with stripe.
		 *
		 * @since 1.3.0
		 */
		do_action( 'cpsw_before_connection_with_stripe' );
		?>
		<tr valign="top">
			<th scope="row">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
			</th>
			<td class="form-wc form-wc-<?php echo esc_attr( $value['class'] ); ?>">
				<fieldset>
					<a class="cpsw_connect_btn" href="<?php echo esc_url( $this->get_stripe_connect_url() . $sec_var ); ?>">
						<span><?php echo esc_html( $label ); ?></span>
					</a>
					<div class="wc-connect-stripe-help">
						<?php
						/* translators: %1$1s, %2$2s: HTML Markup */
						echo wp_kses_post( sprintf( __( 'Have questions about connecting with Stripe? Read %1$s document. %2$s', 'checkout-plugins-stripe-woo' ), '<a href="https://checkoutplugins.com/docs/stripe-api-settings/" target="_blank">', '</a>' ) );
						?>
					</div>
					<?php

					if ( isset( $_GET['connect'] ) && 'manually' === $_GET['connect'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
						?>
					<div class="notice inline notice-warning cpsw_inline_notice" style="margin: 15px 0 -10px">
						<p><?php esc_html_e( 'Although you can add your API keys manually, we recommend using Stripe Connect. Stripe Connect prevents issues that can arise when copying and pasting account details from Stripe into Checkout Plugins - Stripe for WooCommerce settings.', 'checkout-plugins-stripe-woo' ); ?></p>
					</div>
					<?php } ?>
				</fieldset>
			</td>
		</tr>
		<?php

		/**
		 * Action after connection with stripe.
		 *
		 * @since 1.3.0
		 */
		do_action( 'cpsw_after_connection_with_stripe' );
	}

	/**
	 * This method is used to display stripe account ID block.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value Field name in string.
	 */
	public function account_id( $value ) {
		if ( false === $this->is_stripe_connected() ) {
			return;
		}

		$option_value = Helper::get_setting( 'cpsw_account_id' );

		/**
		 * Action before conected with stripe.
		 *
		 * @since 1.3.0
		 */
		do_action( 'cpsw_before_connected_with_stripe' );
		?>
		<tr valign="top">
			<th scope="row">
				<label><?php echo esc_html( $value['title'] ); ?></label>
			</th>
			<td class="form-wc form-wc-<?php echo esc_attr( $value['class'] ); ?>">
				<fieldset>
					<div class="account_status"><p>
						<?php
						if ( false === $this->get_account_info( $option_value ) ) {
							/* translators: %1$1s %2$2s %3$3s: HTML Markup */
							esc_html_e( 'Your manually managed API keys are valid.', 'checkout-plugins-stripe-woo' );
							echo '<span style="color:green;font-weight:bold;font-size:20px;margin-left:5px;">&#10004;</span>';

							echo '<div class="notice inline notice-success">';
							echo '<p>' . esc_html__( 'It is highly recommended to Connect with Stripe for easier setup and improved security.', 'checkout-plugins-stripe-woo' ) . '</p>';

							echo wp_kses_post( '<a class="cpsw_connect_btn" href="' . $this->get_stripe_connect_url() . '"><span>' . __( 'Connect with Stripe', 'checkout-plugins-stripe-woo' ) . '</span></a>' );

							echo '</div>';

						} else {
							?>
							<?php
							/* translators: $1s Acoount name, $2s html markup, $3s account id, $4s html markup */
							echo wp_kses_post( sprintf( __( 'Account (%1$1s) %2$2s %3$3s %4$4s is connected.', 'checkout-plugins-stripe-woo' ), $this->get_account_info( $option_value ), '<strong>', $option_value, '</strong>' ) );
							echo '<span style="color:green;font-weight:bold;font-size:20px;margin-left:5px;">&#10004;</span>';
							?>
						</p>
							<?php
						}
						?>
						<p>
						<?php
							echo '<a href="javascript:void();" id="cpsw_disconnect_acc">';
							esc_html_e( 'Disconnect &amp; connect other account?', 'checkout-plugins-stripe-woo' );
							echo '</a> | <a href="javascript:void();" id="cpsw_test_connection">';
							esc_html_e( 'Test Connection', 'checkout-plugins-stripe-woo' );
							echo '</a>';

						if ( 'no' === $this->settings['cpsw_auto_connect'] ) {
							echo ' | <a href="javascript:void(0)" class="cpsw_connect_mn_btn cpsw_show">';
							esc_html_e( 'Manage API keys manually', 'checkout-plugins-stripe-woo' );
							echo '</a>';
							echo '<a href="javascript:void(0)" class="cpsw_connect_hide_btn cpsw_hide">';
							esc_html_e( 'Hide API keys', 'checkout-plugins-stripe-woo' );
							echo '</a>';
						}
						?>
						</p>
						<p>
						<div class="notice inline notice-warning cpsw_inline_notice">
							<p><?php esc_html_e( 'Although you can add your API keys manually, we recommend using Stripe Connect: an easier and more secure way of connecting your Stripe account to your website. Stripe Connect prevents issues that can arise when copying and pasting account details from Stripe into your Checkout Plugins - Stripe for WooCommerce payment gateway settings. With Stripe Connect you\'ll be ready to go with just a few clicks.', 'checkout-plugins-stripe-woo' ); ?></p>
						</div>
						</p>
					</div>
				</fieldset>
			</td>
		</tr>
		<?php

		/**
		 * Action after conected with stripe.
		 *
		 * @since 1.3.0
		 */
		do_action( 'cpsw_after_connected_with_stripe' );
	}


	/**
	 * This method is used to display block for Stripe webhook url.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value Name of the field.
	 */
	public function webhook_url( $value ) {
		$data         = WC_Admin_Settings::get_field_description( $value );
		$description  = $data['description'];
		$tooltip_html = $data['tooltip_html'];
		$option_value = (array) WC_Admin_Settings::get_option( $value['id'] );

		if ( $tooltip_html && 'checkbox' === $value['type'] ) {
			$tooltip_html = '<p class="description">' . $tooltip_html . '</p>';
		} elseif ( $tooltip_html ) {
			$tooltip_html = wc_help_tip( $tooltip_html );
		}
		?>
		<tr valign="top">
			<th scope="row">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post( $tooltip_html ); ?></label>
			</th>
			<td class="form-wc form-wc-<?php echo esc_attr( $value['class'] ); ?>">
				<fieldset>
					<strong><?php echo esc_url( get_home_url() . '/wp-json/cpsw/v1/webhook' ); ?></strong>
				</fieldset>
				<fieldset>
					<?php echo wp_kses_post( $value['desc'] ); ?>
				</fieldset>
			</td>
		</tr>
		<?php
	}

	/**
	 * Displays express checkout button preview
	 *
	 * @since 1.0.0
	 *
	 * @param array $value settings data.
	 *
	 * @return void
	 */
	public function express_checkout_preview( $value ) {
		/**
		 * Action before express checkout preview.
		 *
		 * @since 1.3.0
		 */
		do_action( 'cpsw_before_express_checkout_preview' );
		wp_nonce_field( 'cpsw_express_checkout_nonce', 'cpsw_express_checkout_nonce', false, true );
		?>
		<tr valign="top">
			<th scope="row">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> </label>
			</th>
			<td class="form-wc form-wc-<?php echo esc_attr( $value['class'] ); ?>">
				<fieldset>
					<div class="cpsw_express_checkout_preview_wrapper">
						<div class="cpsw_express_checkout_preview"></div>
						<div id="cpsw-payment-request-custom-button" class="cpsw-payment-request-custom-button-admin">
							<button lang="auto" class="cpsw-payment-request-custom-button-render cpsw_express_checkout_button cpsw-express-checkout-button large" role="button" type="submit" style="height: 40px;">
								<div class="cpsw-express-checkout-button-inner" tabindex="-1">
									<div class="cpsw-express-checkout-button-shines">
										<div class="cpsw-express-checkout-button-shine cpsw-express-checkout-button-shine--scroll"></div>
										<div class="cpsw-express-checkout-button-shine cpsw-express-checkout-button-shine--hover"></div>
									</div>
									<div class="cpsw-express-checkout-button-content">
										<span class="cpsw-express-checkout-button-label"></span>
										<img src="" class="cpsw-express-checkout-button-icon">
									</div>
									<div class="cpsw-express-checkout-button-overlay"></div>
									<div class="cpsw-express-checkout-button-border"></div>
								</div>
							</button>
						</div>
					</div>
				</fieldset>
				<?php if ( ! empty( $value['desc'] ) ) { ?>
				<fieldset class="cpsw_express_checkout_preview_description">
					<div class="notice inline notice-warning cpsw_inline_notice" style="margin: 5px 0 -10px;padding:10px 20px;">
						<?php echo wp_kses_post( $value['desc'] ); ?>
					</div>
				</fieldset>
				<?php } ?>
			</td>
		</tr>
		<?php
		/**
		 * Action after express checkout preview.
		 *
		 * @since 1.3.0
		 */
		do_action( 'cpsw_after_express_checkout_preview' );
	}

	/**
	 * Displays express checkout button preview
	 *
	 * @since 1.0.0
	 *
	 * @param array $value settings data.
	 *
	 * @return void
	 */
	public function express_checkout_notice( $value ) {
		?>
		<div class="notice inline notice-error cpsw_inline_notice" style="margin: 5px 0 -10px;padding:10px 20px;">
			<?php echo wp_kses_post( $value['desc'] ); ?>
		</div>
		<?php
	}

	/**
	 * This method is used to display Stripe Account key information on the settings page.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value Name of the field.
	 *
	 * @return void
	 */
	public function account_keys( $value ) {
		if ( empty( Helper::get_setting( 'cpsw_pub_key' ) ) && empty( Helper::get_setting( 'cpsw_test_pub_key' ) ) ) {
			return;
		}

		$data         = WC_Admin_Settings::get_field_description( $value );
		$description  = $data['description'];
		$tooltip_html = $data['tooltip_html'];

		$option_value = (array) WC_Admin_Settings::get_option( $value['id'] );

		if ( $tooltip_html && 'checkbox' === $value['type'] ) {
			$tooltip_html = '<p class="description">' . $tooltip_html . '</p>';
		} elseif ( $tooltip_html ) {
			$tooltip_html = wc_help_tip( $tooltip_html );
		}
		?>
		<tr valign="top">
			<th scope="row">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post( $tooltip_html ); ?></label>
			</th>
			<td class="form-wc form-wc-<?php echo esc_attr( $value['class'] ); ?>">
				<fieldset>
					<a href="javascript:void(0)" id="cpsw_account_keys"><span><?php esc_html_e( 'Clear all Stripe account keys', 'checkout-plugins-stripe-woo' ); ?></span></a>
				</fieldset>
				<fieldset>
					<?php echo wp_kses_post( $value['desc'] ); ?>
				</fieldset>
			</td>
		</tr>
		<?php
	}

	/**
	 * This method is used to display block for Stripe Connect Button.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value Name of the field.
	 *
	 * @return void
	 */
	public function connect_button( $value ) {
		$data         = WC_Admin_Settings::get_field_description( $value );
		$description  = $data['description'];
		$tooltip_html = $data['tooltip_html'];
		$manual_link  = false;
		$option_value = (array) WC_Admin_Settings::get_option( $value['id'] );

		if ( $tooltip_html && 'checkbox' === $value['type'] ) {
			$tooltip_html = '<p class="description">' . $tooltip_html . '</p>';
		} elseif ( $tooltip_html ) {
			$tooltip_html = wc_help_tip( $tooltip_html );
		}

		if ( 'live' === Helper::get_payment_mode() && ! empty( Helper::get_setting( 'cpsw_pub_key' ) ) ) {
			$label        = __( 'Re-Connect to Stripe', 'checkout-plugins-stripe-woo' );
			$sec_var      = '&rec=yes';
			$label_status = '<span class="dashicons dashicons-yes stipe-connect-active"></span> ' . __( 'Your Stripe account has been connected. You can now accept Live and Test payments. You can Re-Connect if you want to recycle your API keys for security.', 'checkout-plugins-stripe-woo' );
		} elseif ( 'test' === Helper::get_payment_mode() && ! empty( Helper::get_setting( 'cpsw_test_pub_key' ) ) ) {
			$label        = __( 'Re-Connect to Stripe', 'checkout-plugins-stripe-woo' );
			$sec_var      = '&rec=yes';
			$label_status = '<span class="dashicons dashicons-yes stipe-connect-active"></span> ' . __( 'Your Stripe account has been connected. You can now accept Live and Test payments. You can Re-Connect if you want to recycle your API keys for security.', 'checkout-plugins-stripe-woo' );
		} else {
			$label        = __( 'Connect to Stripe', 'checkout-plugins-stripe-woo' );
			$label_status = __( 'We make it easy to connect Stripe to your site. Click the Connect button to go through our connect flow.', 'checkout-plugins-stripe-woo' );
			$sec_var      = '';
			$manual_link  = true;
		}

		/**
		 * Action before stripe connect button with stripe.
		 *
		 * @since 1.3.0
		 *
		 * @param array $value Connect button values.
		 * @param array $data Field description data.
		 */
		do_action( 'cpsw_before_stripe_connect_button', $value, $data );
		?>
		<tr valign="top">
			<th scope="row">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post( $tooltip_html ); ?></label>
			</th>
			<td class="form-wc form-wc-<?php echo esc_attr( $value['class'] ); ?>">
				<fieldset>
					<a class="cpsw_connect_btn" href="<?php echo esc_url( $this->get_stripe_connect_url() . $sec_var ); ?>">
						<span><?php echo esc_html( $label ); ?></span>
					</a>
				</fieldset>
				<fieldset>
					<?php echo wp_kses_post( $label_status ); ?>
					<?php if ( true === $manual_link ) { ?>
					<a class="cpsw_connect_mn_btn" href="<?php echo esc_url( admin_url() ); ?>admin.php?page=wc-settings&tab=cpsw_api_settings&connect=manually"><?php esc_html_e( 'Connect Manually', 'checkout-plugins-stripe-woo' ); ?></a>
					<?php } ?>
				</fieldset>
			</td>
		</tr>
		<?php

		/**
		 * Action after stripe connect button with stripe.
		 *
		 * @since 1.3.0
		 *
		 * @param array $value Connect button values.
		 * @param array $data Field description data.
		 */
		do_action( 'cpsw_after_stripe_connect_button', $value, $data );
	}

	/**
	 * This method is used to initialize the Stripe settings tab inside the WooCommerce settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings_tabs Adding settings tab to existing WooCommerce tabs array.
	 *
	 * @return mixed
	 */
	public function add_settings_tab( $settings_tabs ) {
		$settings_tabs['cpsw_api_settings'] = __( 'Stripe', 'checkout-plugins-stripe-woo' );
		return $settings_tabs;
	}

	/**
	 * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
	 *
	 * @since 1.0.0
	 *
	 * @uses woocommerce_admin_fields()
	 * @uses $this->get_settings()
	 *
	 * @return void
	 */
	public function settings_tab() {
		woocommerce_admin_fields( $this->get_settings() );
	}

	/**
	 * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function update_settings() {
		woocommerce_update_options( $this->get_settings() );
	}

	/**
	 * Generates Stripe Authorization URL for onboarding process
	 *
	 * @param boolean $redirect_url destination url to redirect after stripe connect.
	 * @return string
	 * @since 1.3.0
	 */
	public function get_stripe_connect_url( $redirect_url = false ) {
		if ( ! $redirect_url ) {
			$redirect_url = admin_url( 'admin.php?page=wc-settings&tab=cpsw_api_settings' );
		}

		$client_id = 'ca_KOXfLe7jv1m4L0iC4KNEMc5fT8AXWWuL';

		return OAuth::authorizeUrl(
			apply_filters(
				'cpsw_stripe_connect_url_data',
				[
					'response_type'  => 'code',
					'client_id'      => $client_id,
					'stripe_landing' => 'login',
					'always_prompt'  => 'true',
					'scope'          => 'read_write',
					// need to use base64_encode to encode data to be sent to server.
					'state'          => base64_encode( //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
						wp_json_encode(
							[
								'redirect' => add_query_arg( 'cpsw_connect_nonce', wp_create_nonce( 'stripe-connect' ), $redirect_url ),
							]
						)
					),
				]
			)
		);
	}

	/**
	 * This method is used to initialize all stripe configuration fields.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	public function get_settings() {
		$settings = [
			'section_title'       => [
				'name' => __( 'Stripe API Settings', 'checkout-plugins-stripe-woo' ),
				'type' => 'title',
				'id'   => 'cpsw_title',
			],
			'connection_status'   => [
				'name'  => __( 'Stripe Connect', 'checkout-plugins-stripe-woo' ),
				'type'  => 'cpsw_stripe_connect',
				'value' => '--',
				'class' => 'wc_cpsw_connect_btn',
				'id'    => 'cpsw_stripe_connect',
			],
			'account_id'          => [
				'name'     => __( 'Connection Status', 'checkout-plugins-stripe-woo' ),
				'type'     => 'cpsw_account_id',
				'value'    => '--',
				'class'    => 'account_id',
				'desc_tip' => __( 'This is your Stripe Connect ID and serves as a unique identifier.', 'checkout-plugins-stripe-woo' ),
				'desc'     => __( 'This is your Stripe Connect ID and serves as a unique identifier.', 'checkout-plugins-stripe-woo' ),
				'id'       => 'cpsw_account_id',
			],
			'account_keys'        => [
				'name'  => __( 'Stripe Account Keys', 'checkout-plugins-stripe-woo' ),
				'type'  => 'cpsw_account_keys',
				'class' => 'wc_stripe_acc_keys',
				'desc'  => __( 'This will disable any connection to Stripe.', 'checkout-plugins-stripe-woo' ),
				'id'    => 'cpsw_account_keys',
			],
			'connect_button'      => [
				'name'  => __( 'Connect Stripe Account', 'checkout-plugins-stripe-woo' ),
				'type'  => 'cpsw_connect_btn',
				'class' => 'wc_cpsw_connect_btn',
				'desc'  => __( 'We make it easy to connect Stripe to your site. Click the Connect button to go through our connect flow.', 'checkout-plugins-stripe-woo' ),
				'id'    => 'cpsw_connect_btn',
			],
			'live_pub_key'        => [
				'name'     => __( 'Live Publishable Key', 'checkout-plugins-stripe-woo' ),
				'type'     => 'text',
				'desc_tip' => __( 'Your publishable key is used to initialize Stripe assets.', 'checkout-plugins-stripe-woo' ),
				'id'       => 'cpsw_pub_key',
			],
			'live_secret_key'     => [
				'name'     => __( 'Live Secret Key', 'checkout-plugins-stripe-woo' ),
				'type'     => 'text',
				'desc_tip' => __( 'Your secret key is used to authenticate Stripe requests.', 'checkout-plugins-stripe-woo' ),
				'id'       => 'cpsw_secret_key',
			],
			'test_pub_key'        => [
				'name'     => __( 'Test Publishable Key', 'checkout-plugins-stripe-woo' ),
				'type'     => 'text',
				'desc_tip' => __( 'Your test publishable key is used to initialize Stripe assets.', 'checkout-plugins-stripe-woo' ),
				'id'       => 'cpsw_test_pub_key',
			],
			'test_secret_key'     => [
				'name'     => __( 'Test Secret Key', 'checkout-plugins-stripe-woo' ),
				'type'     => 'text',
				'desc_tip' => __( 'Your test secret key is used to authenticate Stripe requests for testing purposes.', 'checkout-plugins-stripe-woo' ),
				'id'       => 'cpsw_test_secret_key',
			],
			'test_mode'           => [
				'name'     => __( 'Mode', 'checkout-plugins-stripe-woo' ),
				'type'     => 'select',
				'options'  => [
					'test' => 'Test',
					'live' => 'Live',
				],
				'desc'     => __( 'No live transactions are processed in test mode. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.', 'checkout-plugins-stripe-woo' ),
				'id'       => 'cpsw_mode',
				'desc_tip' => true,
			],
			'webhook_url'         => [
				'name'  => __( 'Webhook URL', 'checkout-plugins-stripe-woo' ),
				'type'  => 'cpsw_webhook_url',
				'class' => 'wc_cpsw_webhook_url',
				/* translators: %1$1s - %2$2s HTML markup */
				'desc'  => sprintf( __( 'Important: the webhook URL is called by Stripe when events occur in your account, like a source becomes chargeable. %1$1sWebhook Guide%2$2s or create webhook on %3$3sstripe dashboard%4$4s', 'checkout-plugins-stripe-woo' ), '<a href="https://checkoutplugins.com/docs/stripe-card-payments/#webhook" target="_blank">', '</a>', '<a href="https://dashboard.stripe.com/webhooks/create" target="_blank">', '</a>' ),
				'id'    => 'cpsw_webhook_url',
			],
			'create_webhook'      => [
				'name'  => __( 'Create Test Webhook', 'checkout-plugins-stripe-woo' ),
				'type'  => 'cpsw_create_webhook',
				'class' => 'wc_cpsw_create_webhook',
				'id'    => 'cpsw_create_webhook',
			],
			'delete_webhook'      => [
				'name'  => __( 'Delete Test Webhook', 'checkout-plugins-stripe-woo' ),
				'type'  => 'cpsw_delete_webhook',
				'class' => 'wc_cpsw_delete_webhook',
				'id'    => 'cpsw_delete_webhook',
				'desc'  => Webhook::get_webhook_interaction_message( 'test' ),
			],
			'live_create_webhook' => [
				'name'  => __( 'Create Live Webhook', 'checkout-plugins-stripe-woo' ),
				'type'  => 'cpsw_live_create_webhook',
				'class' => 'wc_cpsw_create_webhook',
				'id'    => 'cpsw_live_create_webhook',
			],
			'live_delete_webhook' => [
				'name'  => __( 'Delete Live Webhook', 'checkout-plugins-stripe-woo' ),
				'type'  => 'cpsw_live_delete_webhook',
				'class' => 'wc_cpsw_delete_webhook',
				'id'    => 'cpsw_live_delete_webhook',
				'desc'  => Webhook::get_webhook_interaction_message( 'live' ),
			],
			'debug_log'           => [
				'name'        => __( 'Debug Log', 'checkout-plugins-stripe-woo' ),
				'type'        => 'checkbox',
				'desc'        => __( 'Log debug messages', 'checkout-plugins-stripe-woo' ),
				'description' => __( 'Your publishable key is used to initialize Stripe assets.', 'checkout-plugins-stripe-woo' ),
				'id'          => 'cpsw_debug_log',
			],
			'section_end'         => [
				'type' => 'sectionend',
				'id'   => 'cpsw_api_settings_section_end',
			],
		];
		$settings = apply_filters( 'cpsw_settings', $settings );

		return $settings;
	}

	/**
	 * This method is used to display block for create webhook field.
	 *
	 * @param string $value Name of the field.
	 */
	public function create_webhook_field( $value ) {
		$data         = WC_Admin_Settings::get_field_description( $value );
		$tooltip_html = $data['tooltip_html'];

		if ( ! empty( get_option( 'cpsw_test_webhook_id' ) ) ) {
			return;
		}
		?>
		<tr valign="top">
			<th scope="row">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post( $tooltip_html ); ?></label>
			</th>
			<td class="form-wc form-wc-<?php echo esc_attr( $value['class'] ); ?>">
				<fieldset>
					<button id="cpsw_create_webhook_key_test" data-mode="test" class="button" type="button"><?php esc_html_e( 'Create Webhook Automatically', 'checkout-plugins-stripe-woo' ); ?></button>
				</fieldset>
			</td>
		</tr>
		<?php
	}

	/**
	 * This method is used to display block for create webhook field.
	 *
	 * @param string $value Name of the field.
	 */
	public function create_live_webhook_field( $value ) {
		$data         = WC_Admin_Settings::get_field_description( $value );
		$tooltip_html = $data['tooltip_html'];

		if ( ! empty( get_option( 'cpsw_live_webhook_id' ) ) ) {
			return;
		}
		?>
		<tr valign="top">
			<th scope="row">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post( $tooltip_html ); ?></label>
			</th>
			<td class="form-wc form-wc-<?php echo esc_attr( $value['class'] ); ?>">
				<fieldset>
					<button id="cpsw_create_webhook_key_live" data-mode="live" class="button" type="button"><?php esc_html_e( 'Create Webhook Automatically', 'checkout-plugins-stripe-woo' ); ?></button>
				</fieldset>
			</td>
		</tr>
		<?php
	}

	/**
	 * This method is used to display block for delete webhook field.
	 *
	 * @param string $value Name of the field.
	 */
	public function delete_webhook_field( $value ) {
		$data          = WC_Admin_Settings::get_field_description( $value );
		$tooltip_html  = $data['tooltip_html'];
		$webhook_id    = get_option( 'cpsw_test_webhook_id' );
		$webhook_label = ! empty( $webhook_id ) ? __( 'Webhook ID: ', 'checkout-plugins-stripe-woo' ) . $webhook_id : '';
		if ( empty( $webhook_id ) ) {
			return;
		}
		?>
		<tr valign="top">
			<th scope="row">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post( $tooltip_html ); ?></label>
			</th>
			<td class="form-wc form-wc-<?php echo esc_attr( $value['class'] ); ?>">
				<fieldset>
					<button id="cpsw_delete_webhook_key_test" data-mode="test" data-webhook-secret-key="<?php echo esc_attr( $webhook_id ); ?>" class="button" type="button"><?php esc_html_e( 'Delete Webhook', 'checkout-plugins-stripe-woo' ); ?></button>
				</fieldset>
				<fieldset>
					<strong><?php echo esc_html( $webhook_label ); ?></strong>
					<?php echo wp_kses_post( $data['description'] ); ?>
				</fieldset>
			</td>
		</tr>
		<?php
	}

	/**
	 * This method is used to display block for delete webhook field.
	 *
	 * @param string $value Name of the field.
	 */
	public function delete_live_webhook_field( $value ) {
		$data          = WC_Admin_Settings::get_field_description( $value );
		$tooltip_html  = $data['tooltip_html'];
		$webhook_id    = get_option( 'cpsw_live_webhook_id' );
		$webhook_label = ! empty( $webhook_id ) ? __( 'Webhook ID: ', 'checkout-plugins-stripe-woo' ) . $webhook_id : '';

		if ( empty( $webhook_label ) ) {
			return;
		}
		?>
		<tr valign="top">
			<th scope="row">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post( $tooltip_html ); ?></label>
			</th>
			<td class="form-wc form-wc-<?php echo esc_attr( $value['class'] ); ?>">
				<fieldset>
					<button id="cpsw_delete_webhook_key_live" data-mode="live" data-webhook-secret-key="<?php echo esc_attr( $webhook_id ); ?>" class="button" type="button"><?php esc_html_e( 'Delete Webhook', 'checkout-plugins-stripe-woo' ); ?></button>
				</fieldset>
				<fieldset>
					<strong><?php echo esc_html( $webhook_label ); ?></strong>
					<?php echo wp_kses_post( $data['description'] ); ?>
				</fieldset>
			</td>
		</tr>
		<?php
	}

	/**
	 * Checks for response after stripe onboarding process
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function admin_options() {
		if ( ! isset( $_GET['cpsw_connect_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_GET['cpsw_connect_nonce'] ), 'stripe-connect' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			add_action( 'admin_notices', [ $this, 'insufficient_permission' ] );
			return;
		}

		$redirect_url = apply_filters( 'cpsw_stripe_connect_redirect_url', admin_url( '/admin.php?page=wc-settings&tab=cpsw_api_settings' ) );

		// Check if user is being returned from Stripe Connect.
		if ( isset( $_GET['error'] ) ) {
			// need to decode server data hence using base64_decode.
			$error = json_decode( base64_decode( wc_clean( $_GET['error'] ) ) ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
			if ( property_exists( $error, 'message' ) ) {
				$message = $error->message;
			} elseif ( property_exists( $error, 'raw' ) ) {
				$message = $error->raw->message;
			} else {
				$message = __( 'Please try again.', 'checkout-plugins-stripe-woo' );
			}

			$this->settings['cpsw_con_status']      = 'failed';
			$this->settings['cpsw_test_con_status'] = 'failed';

			$this->update_options( $this->settings );
			$redirect_url = add_query_arg( 'cpsw_call', 'failed', $redirect_url );
			wp_safe_redirect( $redirect_url );
			exit();
		} elseif ( isset( $_GET['response'] ) ) {
			// $response elements are getting sanitized in later stage.
			$response = json_decode( base64_decode( $_GET['response'] ) ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( ! empty( $response->live->stripe_publishable_key ) && ! empty( $response->test->stripe_publishable_key ) ) {
				$this->settings['cpsw_pub_key']         = sanitize_text_field( $response->live->stripe_publishable_key );
				$this->settings['cpsw_secret_key']      = sanitize_text_field( $response->live->access_token );
				$this->settings['cpsw_test_pub_key']    = sanitize_text_field( $response->test->stripe_publishable_key );
				$this->settings['cpsw_test_secret_key'] = sanitize_text_field( $response->test->access_token );
				$this->settings['cpsw_account_id']      = sanitize_text_field( $response->live->stripe_user_id );
				$this->settings['cpsw_mode']            = 'test';
				$this->settings['cpsw_con_status']      = 'success';
				$this->settings['cpsw_test_con_status'] = 'success';
				$redirect_url                           = add_query_arg( 'cpsw_call', 'success', $redirect_url );
			} else {
				$this->settings['cpsw_pub_key']         = '';
				$this->settings['cpsw_secret_key']      = '';
				$this->settings['cpsw_test_pub_key']    = '';
				$this->settings['cpsw_test_secret_key'] = '';
				$this->settings['cpsw_account_id']      = '';
				$this->settings['cpsw_con_status']      = 'failed';
				$this->settings['cpsw_test_con_status'] = 'failed';
				$redirect_url                           = add_query_arg( 'cpsw_call', 'failed', $redirect_url );
			}

			$this->settings['cpsw_auto_connect'] = 'yes';
			$this->settings['cpsw_debug_log']    = 'yes';
			$this->update_options( $this->settings );
			do_action( 'cpsw_after_connect_with_stripe', $this->settings['cpsw_con_status'] );
			wp_safe_redirect( $redirect_url );
			exit();
		}
	}

	/**
	 * Perform a connection test
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function connection_test() {
		if ( ! isset( $_GET['_security'] ) || ! wp_verify_nonce( sanitize_text_field( $_GET['_security'] ), 'cpsw_admin_nonce' ) ) {
			wp_send_json_error( [ 'message' => __( 'Error: Sorry, the nonce security check didn\'t pass. Please reload the page and try again.', 'checkout-plugins-stripe-woo' ) ] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Error: The current user doesn\'t have sufficient permissions to perform this action. Please reload the page and try again.', 'checkout-plugins-stripe-woo' ) ] );
		}

		$results = [];
		$keys    = [];

		if ( ! empty( $_GET['cpsw_test_sec_key'] ) ) {
			$keys['test'] = sanitize_text_field( $_GET['cpsw_test_sec_key'] );
		}
		if ( empty( $keys['test'] ) ) {
			$results['test']['mode']    = __( 'Test Mode:', 'checkout-plugins-stripe-woo' );
			$results['test']['status']  = 'invalid';
			$results['test']['message'] = __( 'Please enter secret key to test.', 'checkout-plugins-stripe-woo' );
		}

		if ( ! empty( $_GET['cpsw_secret_key'] ) ) {
			$keys['live'] = sanitize_text_field( $_GET['cpsw_secret_key'] );
		}
		if ( empty( $keys['live'] ) ) {
			$results['live']['mode']    = __( 'Live Mode:', 'checkout-plugins-stripe-woo' );
			$results['live']['status']  = 'invalid';
			$results['live']['message'] = __( 'Please enter secret key to live.', 'checkout-plugins-stripe-woo' );
		}

		if ( empty( $keys ) ) {
			wp_send_json_error( [ 'message' => __( 'Error: Empty String provided for keys', 'checkout-plugins-stripe-woo' ) ] );
		}

		foreach ( $keys as $mode => $key ) {
			$stripe = new \Stripe\StripeClient(
				$key
			);

			try {
				$response = $stripe->customers->create(
					[
						/* translators: %1$1s mode */
						'description' => sprintf( __( 'My first %1s customer (created for API docs)', 'checkout-plugins-stripe-woo' ), $mode ),
					]
				);
				if ( ! is_wp_error( $response ) ) {
					$results[ $mode ]['status']  = 'success';
					$results[ $mode ]['message'] = __( 'Connected to Stripe successfully', 'checkout-plugins-stripe-woo' );
				}
			} catch ( \Stripe\Exception\CardException $e ) {
				$results[ $mode ]['status']  = 'failed';
				$results[ $mode ]['message'] = $e->getError()->message;
			} catch ( \Stripe\Exception\RateLimitException $e ) {
				// Too many requests made to the API too quickly.
				$results[ $mode ]['status']  = 'failed';
				$results[ $mode ]['message'] = $e->getError()->message;
			} catch ( \Stripe\Exception\InvalidRequestException $e ) {
				// Invalid parameters were supplied to Stripe's API.
				$results[ $mode ]['status']  = 'failed';
				$results[ $mode ]['message'] = $e->getError()->message;
			} catch ( \Stripe\Exception\AuthenticationException $e ) {
				// Authentication with Stripe's API failed.
				// (maybe you changed API keys recently).
				$results[ $mode ]['status']  = 'failed';
				$results[ $mode ]['message'] = $e->getError()->message;
			} catch ( \Stripe\Exception\ApiConnectionException $e ) {
				// Network communication with Stripe failed.
				$results[ $mode ]['status']  = 'failed';
				$results[ $mode ]['message'] = $e->getError()->message;
			} catch ( \Stripe\Exception\ApiErrorException $e ) {
				$results[ $mode ]['status']  = 'failed';
				$results[ $mode ]['message'] = $e->getError()->message;
				// Display a very generic error to the user, and maybe send.
				// yourself an email.
			} catch ( Exception $e ) {
				// Something else happened, completely unrelated to Stripe.
				$results[ $mode ]['status']  = 'failed';
				$results[ $mode ]['message'] = $e->getError()->message;
			}

			switch ( $mode ) {
				case 'test':
					$results[ $mode ]['mode'] = __( 'Test Mode:', 'checkout-plugins-stripe-woo' );
					break;

				case 'live':
					$results[ $mode ]['mode'] = __( 'Live Mode:', 'checkout-plugins-stripe-woo' );
					break;

				default:
					break;
			}
		}
		update_option( 'cpsw_auto_connect', 'no' );
		wp_send_json_success( [ 'data' => apply_filters( 'cpsw_connection_test_results', $results ) ] );
	}

	/**
	 * Checks for response after stripe onboarding process
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function disconnect_account() {
		if ( ! isset( $_GET['_security'] ) || ! wp_verify_nonce( sanitize_text_field( $_GET['_security'] ), 'cpsw_admin_nonce' ) ) {
			wp_send_json_error( [ 'message' => __( 'Error: Sorry, the nonce security check didn\’t pass. Please reload the page and try again.', 'checkout-plugins-stripe-woo' ) ] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Error: The current user doesn\'t have sufficient permissions to perform this action. Please reload the page and try again.', 'checkout-plugins-stripe-woo' ) ] );
		}

		if ( $this->delete_webhooks( get_option( 'cpsw_test_webhook_id', true ), 'test' ) && $this->delete_webhooks( get_option( 'cpsw_live_webhook_id', true ), 'live' ) ) {
			$this->remove_settings_keys();
			wp_send_json_success( [ 'message' => __( 'Your Stripe account has been disconnected and webhook secret key deleted successfully', 'checkout-plugins-stripe-woo' ) ] );
		} else {
			$this->remove_settings_keys();
			wp_send_json_success( [ 'message' => __( 'Your Stripe account has been disconnected.', 'checkout-plugins-stripe-woo' ) ] );
		}
	}

	/**
	 * Remove settings keys
	 *
	 * @return void
	 */
	public function remove_settings_keys() {
		foreach ( $this->settings_keys as $key ) {
			update_option( $key, '' );
		}
		wp_send_json_success( [ 'message' => __( 'Stripe keys are reset successfully.', 'checkout-plugins-stripe-woo' ) ] );
	}

	/**
	 * Delete webhook secret key manually
	 *
	 * @return void
	 */
	public function delete_webhook_action() {
		if ( ! isset( $_GET['_security'] ) || ! wp_verify_nonce( sanitize_text_field( $_GET['_security'] ), 'cpsw_admin_nonce' ) ) {
			wp_send_json_error( [ 'message' => __( 'Error: Sorry, the nonce security check didn’t pass. Please reload the page and try again.', 'checkout-plugins-stripe-woo' ) ] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Error: The current user doesn’t have sufficient permissions to perform this action. Please reload the page and try again.', 'checkout-plugins-stripe-woo' ) ] );
		}

		$deletion_response = ! empty( $_GET['webhook_key'] ) && ! empty( $_GET['mode'] ) ? $this->delete_webhooks( sanitize_text_field( $_GET['webhook_key'] ), sanitize_text_field( $_GET['mode'] ) ) : false;
		if ( true === $deletion_response ) {
			wp_send_json_success( [ 'message' => __( 'Webhook secret key deleted successfully.', 'checkout-plugins-stripe-woo' ) ] );
		} else {
			// translators: %s - Error reason sent from stripe.
			wp_send_json_error( [ 'message' => sprintf( __( 'Error: Unable to delete Webhook.%s', 'checkout-plugins-stripe-woo' ), PHP_EOL . $deletion_response ) ] );
		}
	}

	/**
	 * Create webhook secret key manually
	 *
	 * @return void
	 */
	public function create_webhook_action() {
		if ( ! isset( $_GET['_security'] ) || ! wp_verify_nonce( sanitize_text_field( $_GET['_security'] ), 'cpsw_admin_nonce' ) ) {
			wp_send_json_error( [ 'message' => __( 'Error: Sorry, the nonce security check didn’t pass. Please reload the page and try again.', 'checkout-plugins-stripe-woo' ) ] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Error: The current user doesn’t have sufficient permissions to perform this action. Please reload the page and try again.', 'checkout-plugins-stripe-woo' ) ] );
		}

		$creation_response = isset( $_GET['mode'] ) ? $this->create_webhooks( 'manually', sanitize_text_field( $_GET['mode'] ) ) : false;
		if ( true === $creation_response ) {
			wp_send_json_success( [ 'message' => __( 'Webhook secret key created successfully.', 'checkout-plugins-stripe-woo' ) ] );
		} else {
			// translators: %s - Error reason sent from stripe.
			wp_send_json_error( [ 'message' => sprintf( __( 'Error: Webhook secret key not created.%s', 'checkout-plugins-stripe-woo' ), PHP_EOL . $creation_response ) ] );
		}
		exit();
	}

	/**
	 * Logs js errors
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function js_errors() {
		if ( ! isset( $_POST['_security'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['_security'] ), 'cpsw_js_error_nonce' ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid Nonce', 'checkout-plugins-stripe-woo' ) ] );
		}

		if ( isset( $_POST['error'] ) ) {
			$error         = wc_clean( $_POST['error'] );
			$error_message = $error['message'] . ' (' . $error['type'] . ')';
			$error_message = Helper::get_localized_messages( $error['code'], $error_message );
			Logger::error( $error_message, true );
			wp_send_json_success( [ 'message' => $error_message ] );
		}
		exit();
	}

	/**
	 * This method is used get account information from stripe.
	 *
	 * @since 1.0.0
	 *
	 * @param string $account_id Account ID of a stripe user.
	 */
	public function get_account_info( $account_id = '' ) {
		if ( empty( $account_id ) ) {
			return false;
		}

		$stripe_api = new Stripe_Api();
		$response   = $stripe_api->accounts( 'retrieve', [ $account_id ] );
		if ( $response['success'] ) {
			$response = $response['data'];
			return $response->settings->dashboard->display_name;
		} else {
			return '';
		}
	}

	/**
	 * Apply filters on cpsw_settings var to filter settings fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $array cpsw_settings values array.
	 * @return $array array It returns cpsw_settings array.
	 */
	public function filter_settings_fields( $array = [] ) {
		if ( 'success' !== Helper::get_setting( 'cpsw_con_status' ) && 'success' !== Helper::get_setting( 'cpsw_test_con_status' ) ) {
			unset( $array['test_mode'] );
			unset( $array['webhook_url'] );
			unset( $array['create_webhook'] );
			unset( $array['delete_webhook'] );
			unset( $array['live_create_webhook'] );
			unset( $array['live_delete_webhook'] );
			unset( $array['debug_log'] );
			unset( $array['test_conn_button'] );

			$webhook_options = apply_filters(
				'cpsw_webhook_options',
				[
					'cpsw_live_webhook_began_at',
					'cpsw_live_webhook_last_success_at',
					'cpsw_live_webhook_last_failure_at',
					'cpsw_live_webhook_last_error',
					'cpsw_test_webhook_began_at',
					'cpsw_test_webhook_last_success_at',
					'cpsw_test_webhook_last_failure_at',
					'cpsw_test_webhook_last_error',
				]
			);

			array_map( 'delete_option', $webhook_options );
		}
		return $array;
	}

	/**
	 * Checks for response after stripe onboarding process
	 *
	 * @return $mixed
	 */
	public function are_keys_set() {
		if ( ( 'live' === $this->settings['cpsw_mode']
				&& empty( $this->settings['cpsw_pub_key'] )
				&& empty(
					$this->settings['cpsw_secret_key']
				) )
			|| ( 'test' === $this->settings['cpsw_mode']
				&& empty( $this->settings['cpsw_test_pub_key'] )
				&& empty( $this->settings['cpsw_test_secret_key'] )
			)
			|| ( empty( $this->settings['cpsw_mode'] )
				&& empty( $this->settings['cpsw_secret_key'] )
				&& empty( $this->settings['cpsw_test_secret_key'] )
			) ) {
			return false;
		}
		return true;
	}

	/**
	 * Checks if stripe is connected or not.
	 *
	 * @since 1.0.0
	 *
	 * @return $mixed
	 */
	public function is_stripe_connected() {
		if ( 'success' === Helper::get_setting( 'cpsw_con_status' ) || 'success' === Helper::get_setting( 'cpsw_test_con_status' ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Checks if stripe is connected or not.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function check_connection_on_updates() {
		if ( 'yes' === Helper::get_setting( 'cpsw_auto_connect' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$test_key_test = false;

		// This function only saves static string in database nonce verification not required.
		if ( ! empty( $_POST['cpsw_test_secret_key'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$stripe = new \Stripe\StripeClient(
				sanitize_text_field( $_POST['cpsw_test_secret_key'] ) //phpcs:ignore WordPress.Security.NonceVerification.Missing
			);

			try {
				$response = $stripe->customers->create(
					[
						'description' => __( 'My First Test Customer (created for API docs)', 'checkout-plugins-stripe-woo' ),
					]
				);

				if ( ! is_wp_error( $response ) ) {
					$test_key_test = true;
				}
			} catch ( \Stripe\Exception\CardException $e ) {
				$test_key_test = false;
			} catch ( \Stripe\Exception\RateLimitException $e ) {
				// Too many requests made to the API too quickly.
				$test_key_test = false;
			} catch ( \Stripe\Exception\InvalidRequestException $e ) {
				// Invalid parameters were supplied to Stripe's API.
				$test_key_test = false;
			} catch ( \Stripe\Exception\AuthenticationException $e ) {
				// Authentication with Stripe's API failed.
				// (maybe you changed API keys recently).
				$test_key_test = false;
			} catch ( \Stripe\Exception\ApiConnectionException $e ) {
				// Network communication with Stripe failed.
				$test_key_test = false;
			} catch ( \Stripe\Exception\ApiErrorException $e ) {
				$test_key_test = false;
				// Display a very generic error to the user, and maybe send.
				// yourself an email.
			} catch ( Exception $e ) {
				// Something else happened, completely unrelated to Stripe.
				$test_key_test = false;
			}
		} else {
			$test_key_test = false;
		}

		if ( true === $test_key_test ) {
			update_option( 'cpsw_test_con_status', 'success' );
			update_option( 'cpsw_mode', 'test' );
		}

		$live_key_test = false;

		// This function only saves static string in database nonce verification not required.
		if ( isset( $_POST['cpsw_secret_key'] ) && ! empty( $_POST['cpsw_secret_key'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$stripe = new \Stripe\StripeClient(
				sanitize_text_field( $_POST['cpsw_secret_key'] ) //phpcs:ignore WordPress.Security.NonceVerification.Missing
			);

			try {
				$response = $stripe->customers->create(
					[
						'description' => __( 'My First Live Customer (created for API docs)', 'checkout-plugins-stripe-woo' ),
					]
				);
				if ( ! is_wp_error( $response ) ) {
					$live_key_test = true;
				}
			} catch ( \Stripe\Exception\CardException $e ) {
				$live_key_test = false;
			} catch ( \Stripe\Exception\RateLimitException $e ) {
				// Too many requests made to the API too quickly.
				$live_key_test = false;
			} catch ( \Stripe\Exception\InvalidRequestException $e ) {
				// Invalid parameters were supplied to Stripe's API.
				$live_key_test = false;
			} catch ( \Stripe\Exception\AuthenticationException $e ) {
				// Authentication with Stripe's API failed.
				// (maybe you changed API keys recently).
				$live_key_test = false;
			} catch ( \Stripe\Exception\ApiConnectionException $e ) {
				// Network communication with Stripe failed.
				$live_key_test = false;
			} catch ( \Stripe\Exception\ApiErrorException $e ) {
				$live_key_test = false;
				// Display a very generic error to the user, and maybe send.
				// yourself an email.
			} catch ( Exception $e ) {
				// Something else happened, completely unrelated to Stripe.
				$live_key_test = false;
			}
		} else {
			$live_key_test = false;
		}

		if ( true === $live_key_test ) {
			update_option( 'cpsw_con_status', 'success' );
			update_option( 'cpsw_mode', 'live' );
		}
	}

	/**
	 * Update the stripe payment mode on submit.
	 *
	 * @since 1.0.0
	 *
	 * @param string $old_value Old value of the option.
	 * @param string $value New value of the option.
	 *
	 * @return void
	 */
	public function update_mode( $old_value, $value ) {
		if ( 'yes' === Helper::get_setting( 'cpsw_auto_connect' ) ) {
			return;
		}

		if ( ! empty( Helper::get_setting( 'cpsw_secret_key' ) ) && empty( Helper::get_setting( 'cpsw_test_secret_key' ) ) ) {
			update_option( 'cpsw_mode', 'live' );
		} elseif ( ! empty( Helper::get_setting( 'cpsw_test_secret_key' ) ) && empty( Helper::get_setting( 'cpsw_secret_key' ) ) ) {
			update_option( 'cpsw_mode', 'test' );
		}
	}

	/**
	 * Adds custom css to hide navigation menu item.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_custom_css() {
		?>
		<style type="text/css">
			a[href='<?php echo esc_url( get_site_url() ); ?>/wp-admin/admin.php?page=wc-settings&tab=cpsw_api_settings'].nav-tab { display: none }
		</style>
		<?php
	}

	/**
	 * Adds custom breadcrumb on payment method's pages.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_breadcrumb() {
		if ( ! empty( $this->navigation ) ) {
			?>
		<ul class="subsubsub">
			<?php
			foreach ( $this->navigation as $key => $value ) {
				$current_class = '';
				$separator     = '';
				// Generates html content for Admin UI, nonce verification not required.
				if ( isset( $_GET['tab'] ) && 'cpsw_api_settings' === $_GET['tab'] && 'cpsw_api_settings' === $key ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$current_class = 'current';
					echo wp_kses_post( '<li> <a href="' . get_site_url() . '/wp-admin/admin.php?page=wc-settings&tab=cpsw_api_settings" class="' . $current_class . '">' . $value . '</a> | </li>' );
				} else {
					if ( end( $this->navigation ) !== $value ) {
						$separator = ' | ';
					}
					echo wp_kses_post( '<li> <a href="' . get_site_url() . '/wp-admin/admin.php?page=wc-settings&tab=checkout&section=' . $key . '" class="' . $current_class . '">' . $value . '</a> ' . $separator . ' </li>' );
				}
			}
			?>
		</ul>
		<br class="clear" />
			<?php
		}
	}

	/**
	 * Adds settings link to the checkout section.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings_tab Settings tabs array.
	 *
	 * @return array $settings_tab Settings tabs array returned.
	 */
	public function add_settings_links( $settings_tab ) {
		if ( ! empty( $this->navigation ) && is_array( $this->navigation ) ) {
			$settings_tab = array_merge( $settings_tab, $this->navigation );
			array_shift( $settings_tab );
		}

		return apply_filters( 'cpsw_setting_tabs', $settings_tab );
	}

	/**
	 * Adds manual api keys links.
	 *
	 * @since 1.0.0
	 *
	 * @param string $links default copyright link with text.
	 *
	 * @return string $links Return customized copyright text with link.
	 */
	public function add_manual_connect_link( $links ) {
		// Returns html content for Admin UI field, Nonce verification not required.
		if ( ! isset( $_GET['page'] ) || ! isset( $_GET['tab'] ) || 'wc-settings' !== $_GET['page'] || 'cpsw_api_settings' !== $_GET['tab'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $links;
		}

		if ( 'yes' === $this->settings['cpsw_auto_connect'] || 'no' === $this->settings['cpsw_auto_connect'] ) {
			return $links;
		}

		// Returns html content for Admin UI field, Nonce verification not required.
		if ( ! isset( $_GET['connect'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return '<a href="' . admin_url() . 'admin.php?page=wc-settings&tab=cpsw_api_settings&connect=manually" class="cpsw_connect_mn_btn">' . __( 'Manage API keys manually', 'checkout-plugins-stripe-woo' ) . '</a>';
		}

		// Returns html content for Admin UI field, Nonce verification not required.
		if ( isset( $_GET['connect'] ) && 'manually' === $_GET['connect'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return '<a href="' . admin_url() . 'admin.php?page=wc-settings&tab=cpsw_api_settings" class="cpsw_connect_hide_btn">' . __( 'Hide API keys', 'checkout-plugins-stripe-woo' ) . '</a>';
		}

		return $links;
	}

	/**
	 * Adds manual api keys links.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function redirect_if_manually_saved() {
		// Redirect to API Setting page, nonce verification not required.
		if ( isset( $_GET['connect'] ) && 'manually' === $_GET['connect'] && ( ! empty( get_option( 'cpsw_secret_key' ) ) || ! empty( get_option( 'cpsw_test_secret_key' ) ) ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=cpsw_api_settings' ) );
			exit;
		}
	}

	/**
	 * Add settings for section checkout
	 *
	 * @since 1.0.0
	 *
	 * @param array  $settings existing settings.
	 * @param string $current_section section.
	 *
	 * @return array
	 */
	public function checkout_settings( $settings, $current_section ) {
		if ( 'cpsw_api_settings' === $current_section ) {
			wp_safe_redirect( admin_url() . 'admin.php?page=wc-settings&tab=cpsw_api_settings' );
			exit();
		}
		if ( 'cpsw_express_checkout' === $current_section ) {
			$settings = [];
			$values   = Helper::get_gateway_settings();

			if ( 'no' === $values['enabled'] ) {
				$settings = [
					'notice' => [
						'title' => '',
						'type'  => 'express_checkout_notice',
						'desc'  => __( 'Express Checkout is a feature of Card Payments. Enable Card Payments to use Express Checkout', 'checkout-plugins-stripe-woo' ),
					],
				];
			} else {
				// Default values need to be set in Helper class.
				$settings = [
					'section_title'               => [
						'name' => __( 'Express Checkout', 'checkout-plugins-stripe-woo' ),
						'type' => 'title',
						/* translators: HTML Markup*/
						'desc' => sprintf( __( 'Accept payment using Apple Pay, Google Pay, Browser Payment Method.%1$1sExpress Checkout uses Payment Request API which is based on client\'s browser and saved cards.%1$1sPlease check %2$2sprerequisite%3$3s for Apple Pay, Google Pay and Browser Payment Method.', 'checkout-plugins-stripe-woo' ), '<br/>', '<a href="https://stripe.com/docs/stripe-js/elements/payment-request-button#html-js-testing" target="_blank">', '</a>' ),
						'id'   => 'cpsw_express_checkout',
					],
					'enable'                      => [
						'name'  => __( 'Enable Express Checkout', 'checkout-plugins-stripe-woo' ),
						'id'    => 'cpsw_express_checkout_enabled',
						'type'  => 'checkbox',
						'value' => $values['express_checkout_enabled'],
					],
					'button_location'             => [
						'title'    => __( 'Show button on', 'checkout-plugins-stripe-woo' ),
						'type'     => 'multiselect',
						'class'    => 'cpsw_express_checkout_location',
						'id'       => 'cpsw_express_checkout_location',
						'desc_tip' => __( 'Choose page to display Express Checkout buttons.', 'checkout-plugins-stripe-woo' ),
						'options'  => [
							'product'  => __( 'Product', 'checkout-plugins-stripe-woo' ),
							'cart'     => __( 'Cart', 'checkout-plugins-stripe-woo' ),
							'checkout' => __( 'Checkout', 'checkout-plugins-stripe-woo' ),
						],
						'value'    => $values['express_checkout_location'],
					],
					'button_type'                 => [
						'title'    => __( 'Button text', 'checkout-plugins-stripe-woo' ),
						'type'     => 'text',
						'id'       => 'cpsw_express_checkout_button_text',
						'desc'     => __( 'Add label text for the Express Checkout button.', 'checkout-plugins-stripe-woo' ),
						'value'    => $values['express_checkout_button_text'],
						'desc_tip' => true,
					],
					'button_theme'                => [
						'title'    => __( 'Button theme', 'checkout-plugins-stripe-woo' ),
						'type'     => 'select',
						'id'       => 'cpsw_express_checkout_button_theme',
						'desc'     => __( 'Select theme for Express Checkout button.', 'checkout-plugins-stripe-woo' ),
						'value'    => $values['express_checkout_button_theme'],
						'options'  => [
							'dark'          => __( 'Dark', 'checkout-plugins-stripe-woo' ),
							'light'         => __( 'Light', 'checkout-plugins-stripe-woo' ),
							'light-outline' => __( 'Light Outline', 'checkout-plugins-stripe-woo' ),
						],
						'desc_tip' => true,
					],
					'preview'                     => [
						'title' => __( 'Button Preview', 'checkout-plugins-stripe-woo' ),
						'type'  => 'cpsw_express_checkout_preview',
						'id'    => 'cpsw_express_checkout_preview',
					],
					'section_end'                 => [
						'type' => 'sectionend',
						'id'   => 'cpsw_express_checkout',
					],
					'product_page_section_title'  => [
						'name' => __( 'Product page options', 'checkout-plugins-stripe-woo' ),
						'type' => 'title',
						'desc' => __( 'Advanced customization options for product page.', 'checkout-plugins-stripe-woo' ),
						'id'   => 'cpsw_express_checkout_product_page',
					],
					'product_button_position'     => [
						'title'    => __( 'Button position', 'checkout-plugins-stripe-woo' ),
						'type'     => 'select',
						'id'       => 'cpsw_express_checkout_product_page_position',
						'class'    => 'cpsw_product_options',
						'desc'     => __( 'Select the position of Express Checkout button. This option will work only for Product page.', 'checkout-plugins-stripe-woo' ),
						'value'    => $values['express_checkout_product_page_position'],
						'options'  => [
							'above'  => __( 'Above Add to Cart', 'checkout-plugins-stripe-woo' ),
							'below'  => __( 'Below Add to Cart', 'checkout-plugins-stripe-woo' ),
							'inline' => __( 'Inline Button', 'checkout-plugins-stripe-woo' ),
						],
						'desc_tip' => true,
					],
					'separator_text'              => [
						'title'    => __( 'Separator text', 'checkout-plugins-stripe-woo' ),
						'type'     => 'text',
						'id'       => 'cpsw_express_checkout_separator_product',
						'desc'     => __( 'Add separator text for the Express Checkout button. This will help to distinguish between Express Checkout and other buttons.', 'checkout-plugins-stripe-woo' ),
						'value'    => $values['express_checkout_separator_product'],
						'class'    => 'cpsw_product_options',
						'desc_tip' => true,
					],
					'sticky_footer'               => [
						'name'  => __( 'Responsive behaviour', 'checkout-plugins-stripe-woo' ),
						/* translators: HTML Markup*/
						'desc'  => sprintf( __( 'If checked the Express Checkout button will stick%1$1sat bottom of screen on responsive devices.', 'checkout-plugins-stripe-woo' ), '<br/>' ),
						'class' => 'cpsw_product_options',
						'type'  => 'checkbox',
						'id'    => 'cpsw_express_checkout_product_sticky_footer',
						'value' => $values['express_checkout_product_sticky_footer'],
					],
					'product_page_section_end'    => [
						'type' => 'sectionend',
						'id'   => 'cpsw_express_checkout',
					],
					'cart_page_section_title'     => [
						'name'  => __( 'Cart page options', 'checkout-plugins-stripe-woo' ),
						'type'  => 'title',
						'desc'  => __( 'Advanced customization options for Cart page.', 'checkout-plugins-stripe-woo' ),
						'id'    => 'cpsw_express_checkout_cart_page',
						'class' => 'cpsw_cart_options',
					],
					'cart_separator_text'         => [
						'title'    => __( 'Separator text', 'checkout-plugins-stripe-woo' ),
						'type'     => 'text',
						'class'    => 'cpsw_cart_options',
						'id'       => 'cpsw_express_checkout_separator_cart',
						'desc'     => __( 'Add separator text for Cart page. If empty will show default separator text.', 'checkout-plugins-stripe-woo' ),
						'value'    => $values['express_checkout_separator_cart'],
						'desc_tip' => true,
					],
					'cart_page_section_end'       => [
						'type' => 'sectionend',
						'id'   => 'cpsw_express_checkout',
					],
					'checkout_page_section_title' => [
						'name'  => __( 'Checkout page options', 'checkout-plugins-stripe-woo' ),
						'type'  => 'title',
						'desc'  => __( 'Advanced customization options for Checkout page.', 'checkout-plugins-stripe-woo' ),
						'id'    => 'cpsw_express_checkout_checkout_page',
						'class' => 'cpsw_checkout_options',
					],
					'checkout_button_layout'      => [
						'title'    => __( 'Layout', 'checkout-plugins-stripe-woo' ),
						'type'     => 'select',
						'class'    => 'cpsw_checkout_options',
						'id'       => 'cpsw_express_checkout_checkout_page_layout',
						'desc'     => __( 'Select the layout of Express Checkout button. This option will work only for Checkout page.', 'checkout-plugins-stripe-woo' ),
						'value'    => $values['express_checkout_checkout_page_layout'],
						'options'  => [
							'custom'  => __( 'Custom', 'checkout-plugins-stripe-woo' ),
							'classic' => __( 'Classic', 'checkout-plugins-stripe-woo' ),
						],
						'desc_tip' => true,
					],
					'checkout_button_position'    => [
						'title'    => __( 'Button position', 'checkout-plugins-stripe-woo' ),
						'type'     => 'select',
						'class'    => 'cpsw_checkout_options',
						'id'       => 'cpsw_express_checkout_checkout_page_position',
						'desc'     => __( 'Select the position of Express Checkout button. This option will work only for Checkout page.', 'checkout-plugins-stripe-woo' ),
						'value'    => $values['express_checkout_checkout_page_position'],
						'options'  => [
							'above-checkout' => __( 'Above checkout form', 'checkout-plugins-stripe-woo' ),
							'above-billing'  => __( 'Above billing details', 'checkout-plugins-stripe-woo' ),
						],
						'desc_tip' => true,
					],
					'title'                       => [
						'title'    => __( 'Title', 'checkout-plugins-stripe-woo' ),
						'type'     => 'text',
						'class'    => 'cpsw_checkout_options',
						'id'       => 'cpsw_express_checkout_title',
						'desc'     => __( 'Add a title above Express Checkout button on Checkout page.', 'checkout-plugins-stripe-woo' ),
						'value'    => $values['express_checkout_title'],
						'desc_tip' => true,
					],
					'tagline'                     => [
						'title'    => __( 'Tagline', 'checkout-plugins-stripe-woo' ),
						'type'     => 'text',
						'class'    => 'cpsw_checkout_options',
						'id'       => 'cpsw_express_checkout_tagline',
						'desc'     => __( 'Add a tagline below the title on Checkout page.', 'checkout-plugins-stripe-woo' ),
						'value'    => $values['express_checkout_tagline'],
						'desc_tip' => true,
					],
					'checkout_button_width'       => [
						'title'    => __( 'Button width', 'checkout-plugins-stripe-woo' ),
						'type'     => 'number',
						'class'    => 'cpsw_checkout_options',
						'id'       => 'cpsw_express_checkout_button_width',
						'desc'     => __( 'Select width for button (in px). Default width 100%', 'checkout-plugins-stripe-woo' ),
						'value'    => $values['express_checkout_button_width'],
						'desc_tip' => true,
					],
					'checkout_button_alignment'   => [
						'title'    => __( 'Alignment', 'checkout-plugins-stripe-woo' ),
						'type'     => 'select',
						'class'    => 'cpsw_checkout_options',
						'id'       => 'cpsw_express_checkout_button_alignment',
						'desc'     => __( 'This setting will align title, tagline and button based on selection on Checkout page.', 'checkout-plugins-stripe-woo' ),
						'value'    => $values['express_checkout_button_alignment'],
						'options'  => [
							'left'   => __( 'Left', 'checkout-plugins-stripe-woo' ),
							'center' => __( 'Center', 'checkout-plugins-stripe-woo' ),
							'right'  => __( 'Right', 'checkout-plugins-stripe-woo' ),
						],
						'desc_tip' => true,
					],
					'checkout_separator_text'     => [
						'title'    => __( 'Separator text', 'checkout-plugins-stripe-woo' ),
						'type'     => 'text',
						'class'    => 'cpsw_checkout_options',
						'id'       => 'cpsw_express_checkout_separator_checkout',
						'desc'     => __( 'Add separator text for Checkout page. If empty will show default separator text.', 'checkout-plugins-stripe-woo' ),
						'value'    => $values['express_checkout_separator_checkout'],
						'desc_tip' => true,
					],
					'checkout_page_section_end'   => [
						'type' => 'sectionend',
						'id'   => 'cpsw_express_checkout',
					],
				];
			}
		}

		return apply_filters( 'cpsw_express_checkout_settings', $settings );
	}
}
