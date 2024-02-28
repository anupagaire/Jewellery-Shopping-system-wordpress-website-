<?php
/**
 * Apple Pay domain association
 *
 * @package checkout-plugins-stripe-woo
 * @since 1.1.0
 */

namespace CPSW\Compatibility;

use CPSW\Inc\Traits\Get_Instance;
use CPSW\Gateway\Stripe\Stripe_Api;
use CPSW\Inc\Helper;

/**
 * Apple Pay Domain Verification class
 */
class Apple_Pay {

	use Get_Instance;

	const APPLE_PAY_FILE = 'apple-developer-merchantid-domain-association';
	const APPLE_PAY_DIR  = '.well-known';

	/**
	 * Domain verification flag
	 *
	 * @var bool
	 */
	public $domain_is_verfied;

	/**
	 * Verified domain stored in database
	 *
	 * @var string
	 */
	public $verified_domain;

	/**
	 * Current domain
	 *
	 * @var string
	 */
	public $domain;

	/**
	 * Stores apple pay domain verification failure message.
	 *
	 * @var string
	 */
	private $failure_message;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'apple_pay_domain_association_rewrite_rule' ] );
		add_action( 'admin_init', [ $this, 'is_domain_verified' ] );
		add_filter( 'query_vars', [ $this, 'add_domain_association_query_var' ], 10, 1 );
		add_action( 'parse_request', [ $this, 'parse_domain_association_request' ], 10, 1 );

		$this->domain_is_verfied = get_option( 'cpsw_apple_pay_domain_is_verfied' );
		$this->verified_domain   = get_option( 'cpsw_apple_pay_verified_domain' );
		$this->secret_key        = Helper::get_setting( 'cpsw_secret_key' );
		$this->failure_message   = '';
		$this->domain            = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( $_SERVER['HTTP_HOST'] ) : str_replace( array( 'https://', 'http://' ), '', get_site_url() );
	}

	/**
	 * Rewrite rules for apple pay domain association.
	 *
	 * @return void
	 */
	public function apple_pay_domain_association_rewrite_rule() {
		$regex    = '^\\' . self::APPLE_PAY_DIR . '\/' . self::APPLE_PAY_FILE . '$';
		$redirect = 'index.php?' . self::APPLE_PAY_FILE . '=1';

		add_rewrite_rule( $regex, $redirect, 'top' );
	}

	/**
	 * Add domain association query var
	 *
	 * @param array $query_vars existing query vars.
	 * @return array
	 */
	public function add_domain_association_query_var( $query_vars ) {
		$query_vars[] = self::APPLE_PAY_FILE;
		return $query_vars;
	}

	/**
	 * Parse current domain should serve apple pay domain association file or not.
	 *
	 * @param object $wp query parameters.
	 * @return void
	 */
	public function parse_domain_association_request( $wp ) {
		if (
			self::APPLE_PAY_DIR . '/' . self::APPLE_PAY_FILE !== $wp->request ||
			self::APPLE_PAY_FILE !== $wp->query_vars['attachment']
		) {
			return;
		}

		$path = CPSW_DIR . 'compatibility/' . self::APPLE_PAY_FILE;
		header( 'Content-Type: text/plain;charset=utf-8' );
		echo esc_html( @file_get_contents( $path ) ); // @codingStandardsIgnoreLine
		exit;
	}

	/**
	 * Checks if current domain is verified or not else verifys current domain.
	 *
	 * @return boolean
	 */
	public function is_domain_verified() {
		if ( ! empty( $this->verified_domain ) && $this->domain === $this->verified_domain && $this->domain_is_verfied ) {
			return;
		}

		$settings               = Helper::get_gateway_settings();
		$this->express_checkout = $settings['express_checkout_enabled'];
		if ( 'yes' !== $this->express_checkout || 'yes' !== $settings['enabled'] ) {
			return;
		}

		// Generates admin notice in an admin scope if ssl not found, nonce verification not required.
		if ( isset( $_GET['page'] ) && isset( $_GET['tab'] ) && isset( $_GET['section'] ) && 'wc-settings' === $_GET['page'] && 'checkout' === $_GET['tab'] && 'cpsw_express_checkout' === $_GET['section'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( ! is_ssl() ) {
				add_action( 'admin_notices', [ $this, 'no_ssl_notice' ] );
				return;
			}

			// Required for apple pay integration.
			flush_rewrite_rules(); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.flush_rewrite_rules_flush_rewrite_rules

			$response = $this->move_file_to_apple_dir();
			if ( ! $response['success'] ) {
				$this->failure_message = $response['message'];
				add_action( 'admin_notices', [ $this, 'apple_pay_verification_failed' ] );
				return;
			}

			$this->verify_domain_for_apple_pay();
		}
	}

	/**
	 * Moves domain association file to required directory
	 *
	 * @return array
	 */
	public function move_file_to_apple_dir() {
		if ( $this->check_hosted_file() ) {
			return [
				'success' => true,
			];
		}

		$well_known_dir = untrailingslashit( ABSPATH ) . '/' . self::APPLE_PAY_DIR;
		$fullpath       = $well_known_dir . '/' . self::APPLE_PAY_FILE;

		if ( ! file_exists( $well_known_dir ) ) {
			if ( ! @mkdir( $well_known_dir, 0755 ) ) { // @codingStandardsIgnoreLine
				return [
					'success' => false,
					/* translators: 1 - 4 html entities */
					'message' => sprintf( __( 'Unable to create domain association folder to domain root due to file permissions. Please create %1$1s.well-known%2$2s directory under domain root and place %3$3sdomain verification file%4$4s under it and refresh.', 'checkout-plugins-stripe-woo' ), '<code>', '</code>', '<a href="https://stripe.com/files/apple-pay/apple-developer-merchantid-domain-association" target="_blank">', '</a>' ),
				];
			}
		}

		if ( ! @copy( CPSW_DIR . 'compatibility/' . self::APPLE_PAY_FILE, $fullpath ) ) { // @codingStandardsIgnoreLine
			return [
				'success' => false,
				'message' => __( 'Unable to copy domain association file to domain root.', 'checkout-plugins-stripe-woo' ),
			];
		}

		return [
			'success' => true,
		];
	}

	/**
	 * Checks if hosted domain verification file is correct or not, updates if required.
	 *
	 * @return bool
	 */
	public function check_hosted_file() {
		$new_contents    = @file_get_contents( CPSW_DIR . 'compatibility/' . self::APPLE_PAY_FILE ); // @codingStandardsIgnoreLine
		$fullpath        = untrailingslashit( ABSPATH ) . '/' . self::APPLE_PAY_DIR . '/' . self::APPLE_PAY_FILE;
		$local_contents  = @file_get_contents( $fullpath ); // @codingStandardsIgnoreLine
		$url             = get_site_url() . '/' . self::APPLE_PAY_DIR . '/' . self::APPLE_PAY_FILE;
		$response        = @wp_remote_get( $url ); // @codingStandardsIgnoreLine
		$remote_contents = @wp_remote_retrieve_body( $response ); // @codingStandardsIgnoreLine

		return $local_contents === $new_contents || $remote_contents === $new_contents;
	}

	/**
	 * Automatic verification for apple pay using stripe api
	 *
	 * @return void
	 */
	public function verify_domain_for_apple_pay() {

		if ( empty( $this->secret_key ) ) {
			add_action( 'admin_notices', [ $this, 'no_live_secret_key' ] );
			return;
		}

		add_filter( 'cpsw_get_secret_key', [ $this, 'get_live_secret_key' ], 10, 1 );
		$stripe = new Stripe_Api();

		$response = $stripe->apple_pay_domains(
			'create',
			[
				[
					'domain_name' => $this->domain,
				],
			]
		);

		$verification_response = $response['success'] ? $response['data'] : false;

		if ( $verification_response ) {
			update_option( 'cpsw_apple_pay_verified_domain', $this->domain );
			update_option( 'cpsw_apple_pay_domain_is_verfied', true );
			add_action( 'admin_notices', [ $this, 'apple_pay_verification_success' ] );
		} else {
			$this->failure_message = $response['message'];
			delete_option( 'cpsw_apple_pay_domain_is_verfied' );
			add_action( 'admin_notices', [ $this, 'apple_pay_verification_failed' ] );
		}
	}

	/**
	 * Generates admin notice if no live secret key is found
	 *
	 * @return void
	 */
	public function no_live_secret_key() {
		echo wp_kses_post( '<div class="notice notice-error is-dismissible"><p>' . __( 'We cannot find live secret key in database, Live secret key is required for Apple Pay domain verification. ', 'checkout-plugins-stripe-woo' ) . '</p></div>' );
	}

	/**
	 * Generates admin notice for apple pay success
	 *
	 * @return void
	 */
	public function apple_pay_verification_success() {
		echo wp_kses_post( '<div class="notice notice-success is-dismissible"><p>' . __( 'Apple Pay domain verification successful.', 'checkout-plugins-stripe-woo' ) . '</p></div>' );
	}

	/**
	 * Generates admin notice for SSL requirment for payment request Api
	 *
	 * @return void
	 */
	public function no_ssl_notice() {
		echo wp_kses_post( '<div class="notice notice-error is-dismissible"><p>' . __( 'SSL is required for Express Pay Checkout.', 'checkout-plugins-stripe-woo' ) . '</p></div>' );
	}

	/**
	 * Generates admin notice for apple pay registration failure
	 *
	 * @return void
	 */
	public function apple_pay_verification_failed() {
		/* translators: %1s - %3s HTML Entities, %4s Error Message */
		echo wp_kses_post( '<div class="notice notice-warning is-dismissible"><p>' . sprintf( __( '%1$1sApple Pay domain verification failed! %2$2sReason%3$3s: %4$4s', 'checkout-plugins-stripe-woo' ), '<b>', '<br/>', '</b>', $this->failure_message ) . '</p></div>' );
	}

	/**
	 * Returns live secret key for apple pay Verification
	 *
	 * @param string $secret_key current secret key as per mode.
	 * @return string
	 */
	public function get_live_secret_key( $secret_key ) {
		return $this->secret_key;
	}
}
