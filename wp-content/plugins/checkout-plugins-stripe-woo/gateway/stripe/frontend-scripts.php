<?php
/**
 * Stripe Frontend Scripts
 *
 * @package checkout-plugins-stripe-woo
 * @since 0.0.1
 */

namespace CPSW\Gateway\Stripe;

use CPSW\Inc\Traits\Get_Instance;
use CPSW\Inc\Helper;
use WC_AJAX;
use CPSW\Inc\Traits\Subscription_Helper as SH;

/**
 * Consists frontend scripts for payment gateways
 */
class Frontend_Scripts {

	use Get_Instance;
	use SH;

	/**
	 * Prefix
	 *
	 * @var string
	 */
	private $prefix = 'cpsw-';

	/**
	 * Version
	 *
	 * @var string
	 */
	private $version = '';

	/**
	 * Url of assets directory
	 *
	 * @var string
	 */
	private $assets_url = CPSW_URL . 'assets/';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : CPSW_VERSION;
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Enqueue scripts
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( apply_filters( 'cpsw_exclude_frontend_scripts', ! is_product() && ! is_cart() && ! is_checkout() && ! is_add_payment_method_page() ) ) {
			return;
		}

		$public_key = ( 'live' === Helper::get_payment_mode() ) ? Helper::get_setting( 'cpsw_pub_key' ) : Helper::get_setting( 'cpsw_test_pub_key' );

		wp_register_script( $this->prefix . 'stripe-external', 'https://js.stripe.com/v3/', [], $this->version, true );
		wp_enqueue_script( $this->prefix . 'stripe-external' );

		if (
			'yes' === Helper::get_setting( 'enabled', 'cpsw_stripe' ) ||
			'yes' === Helper::get_setting( 'enabled', 'cpsw_alipay' ) ||
			'yes' === Helper::get_setting( 'enabled', 'cpsw_ideal' ) ||
			'yes' === Helper::get_setting( 'enabled', 'cpsw_klarna' ) ||
			'yes' === Helper::get_setting( 'enabled', 'cpsw_p24' ) ||
			'yes' === Helper::get_setting( 'enabled', 'cpsw_wechat' ) ||
			'yes' === Helper::get_setting( 'enabled', 'cpsw_bancontact' ) ||
			'yes' === Helper::get_setting( 'enabled', 'cpsw_sepa' )
		) {
			$this->enqueue_card_payments_scripts( $public_key );
		}
	}

	/**
	 * Enqueue card payments scripts
	 *
	 * @since 1.0.0
	 *
	 * @param string $public_key Stripe public key.
	 *
	 * @return void
	 */
	private function enqueue_card_payments_scripts( $public_key ) {
		wp_register_script( $this->prefix . 'stripe-elements', $this->assets_url . 'js/stripe-elements.js', [ 'jquery', $this->prefix . 'stripe-external' ], $this->version, true );
		wp_enqueue_script( $this->prefix . 'stripe-elements' );

		wp_register_style( $this->prefix . 'stripe-elements', $this->assets_url . 'css/stripe-elements.css', [], $this->version );
		wp_enqueue_style( $this->prefix . 'stripe-elements' );

		wp_localize_script(
			$this->prefix . 'stripe-elements',
			'cpsw_global_settings',
			[
				'public_key'              => $public_key,
				'cpsw_version'            => CPSW_VERSION,
				'inline_cc'               => Helper::get_setting( 'inline_cc', 'cpsw_stripe' ),
				'is_ssl'                  => is_ssl(),
				'mode'                    => Helper::get_payment_mode(),
				'ajax_url'                => admin_url( 'admin-ajax.php' ),
				'js_nonce'                => wp_create_nonce( 'cpsw_js_error_nonce' ),
				'allowed_cards'           => Helper::get_setting( 'allowed_cards', 'cpsw_stripe' ),
				'stripe_localized'        => Helper::get_localized_messages(),
				'default_cards'           => [
					'mastercard' => __( 'MasterCard', 'checkout-plugins-stripe-woo' ),
					'visa'       => __( 'Visa', 'checkout-plugins-stripe-woo' ),
					'amex'       => __( 'American Express', 'checkout-plugins-stripe-woo' ),
					'discover'   => __( 'Discover', 'checkout-plugins-stripe-woo' ),
					'jcb'        => __( 'JCB', 'checkout-plugins-stripe-woo' ),
					'diners'     => __( 'Diners Club', 'checkout-plugins-stripe-woo' ),
					'unionpay'   => __( 'UnionPay', 'checkout-plugins-stripe-woo' ),
				],
				'not_allowed_string'      => __( 'is not allowed', 'checkout-plugins-stripe-woo' ),
				'get_home_url'            => get_home_url(),
				'current_user_billing'    => $this->get_current_user_billing_details(),
				'changing_payment_method' => $this->is_changing_payment_method_for_subscription(),
				'sepa_options'            => [
					'supportedCountries' => [ 'SEPA' ],
					'placeholderCountry' => WC()->countries->get_base_country(),
					'style'              => [
						'base' => [
							'fontSize' => '15px',
							'color'    => '#32325d',
						],
					],
				],
				'is_cart_amount_zero'     => WC()->cart->total > 0 ? 'no' : 'yes',
				'empty_sepa_iban_message' => __( 'Please enter a IBAN number to proceed.', 'checkout-plugins-stripe-woo' ),
				'empty_bank_message'      => __( 'Please select a bank to proceed.', 'checkout-plugins-stripe-woo' ),
			]
		);

		if ( 'yes' === Helper::get_setting( 'enabled', 'cpsw_stripe' ) && 'yes' === Helper::get_setting( 'express_checkout_enabled', 'cpsw_stripe' ) ) {
			wp_register_script( $this->prefix . 'payment-request', $this->assets_url . 'js/payment-request.js', [ 'jquery', $this->prefix . 'stripe-external', $this->prefix . 'stripe-elements' ], $this->version, true );
			wp_enqueue_script( $this->prefix . 'payment-request' );

			wp_register_style( $this->prefix . 'express-checkout', $this->assets_url . 'css/express-checkout.css', [], $this->version );
			wp_enqueue_style( $this->prefix . 'express-checkout' );

			$needs_shipping = false;
			$button_text    = empty( Helper::get_setting( 'express_checkout_button_text', 'cpsw_stripe' ) ) ? __( 'Pay now', 'checkout-plugins-stripe-woo' ) : Helper::get_setting( 'express_checkout_button_text', 'cpsw_stripe' );

			if ( ! is_null( WC()->cart ) && WC()->cart->needs_shipping() ) {
				$needs_shipping = true;
			}

			wp_localize_script(
				$this->prefix . 'payment-request',
				'cpsw_payment_request',
				apply_filters(
					'cpsw_payment_request_localization',
					[
						'ajax_url'        => admin_url( 'admin-ajax.php' ),
						'ajax_endpoint'   => WC_AJAX::get_endpoint( '%%endpoint%%' ),
						'public_key'      => $public_key,
						'cpsw_version'    => CPSW_VERSION,
						'mode'            => Helper::get_payment_mode(),
						'currency_code'   => strtolower( get_woocommerce_currency() ),
						'country_code'    => substr( get_option( 'woocommerce_default_country' ), 0, 2 ),
						'needs_shipping'  => $needs_shipping,
						'phone_required'  => 'required' === get_option( 'woocommerce_checkout_phone_field', 'required' ),
						'nonce'           => [
							'checkout'              => wp_create_nonce( 'cpsw_checkout' ),
							'payment'               => wp_create_nonce( 'cpsw_payment_request' ),
							'add_to_cart'           => wp_create_nonce( 'cpsw_add_to_cart' ),
							'selected_product_data' => wp_create_nonce( 'cpsw_selected_product_data' ),
							'shipping'              => wp_create_nonce( 'cpsw_shipping_address' ),
							'shipping_option'       => wp_create_nonce( 'cpsw_shipping_option' ),
							'js_nonce'              => wp_create_nonce( 'cpsw_js_error_nonce' ),
						],
						'style'           => [
							'theme'                 => Helper::get_setting( 'express_checkout_button_theme', 'cpsw_stripe' ),
							'icon'                  => Helper::get_setting( 'express_checkout_button_icon', 'cpsw_stripe' ),
							'button_position'       => Helper::get_setting( 'express_checkout_product_page_position', 'cpsw_stripe' ),
							'checkout_button_width' => absint( Helper::get_setting( 'express_checkout_button_width', 'cpsw_stripe' ) ),
							'button_length'         => strlen( $button_text ),
						],
						'icons'           => [
							'applepay_gray'   => CPSW_URL . 'assets/icon/apple-pay-gray.svg',
							'applepay_light'  => CPSW_URL . 'assets/icon/apple-pay-light.svg',
							'gpay_light'      => CPSW_URL . 'assets/icon/gpay_light.svg',
							'gpay_gray'       => CPSW_URL . 'assets/icon/gpay_gray.svg',
							'payment_request' => CPSW_URL . 'assets/icon/payment-request-icon.svg',
						],
						'is_product_page' => is_product() || wc_post_content_has_shortcode( 'product_page' ),
						'is_responsive'   => Helper::get_setting( 'express_checkout_product_sticky_footer', 'cpsw_stripe' ),
						'is_checkout'     => is_checkout(),
						'is_cart'         => is_cart(),
					]
				)
			);
		}
	}

	/**
	 * Get current user billing details
	 *
	 * @since 1.4.0
	 *
	 * @return array
	 */
	public function get_current_user_billing_details() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$user = wp_get_current_user();

		if ( ! empty( $user->display_name ) ) {
			$details['name'] = $user->display_name;
		}

		if ( ! empty( $user->user_email ) ) {
			$details['email'] = $user->user_email;
		}

		return apply_filters( 'cpsw_current_user_billing_details', $details, get_current_user_id() );
	}
}
