<?php
/**
 * Woo hooks for compatibility. Data processed for performance.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class for elementor page builder compatibility
 */
class Cartflows_Woo_Hooks {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	public static $ajax_data = array();

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 *  Constructor
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'register_wc_hooks' ), 999 );
	}

	/**
	 * Rgister wc hookes for elementor preview mode
	 */
	public function register_wc_hooks() {

		// Using global variable on WordPress hooks.Hence ignoring nonce verification.
		if ( isset( $_GET['wc-ajax'] ) && 'update_order_review' === $_GET['wc-ajax'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$post_data = array();

			$post_raw_data = isset( $_POST['post_data'] ) ? sanitize_text_field( wp_unslash( $_POST['post_data'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Missing

			parse_str( $post_raw_data, $post_data );

			self::$ajax_data = $post_data;

			do_action( 'cartflows_woo_checkout_update_order_review_init', self::$ajax_data );

			/* Woocommerce update order action */
			add_action( 'woocommerce_checkout_update_order_review', array( $this, 'update_order_review_hook' ) );
		}

	}

	/**
	 * Update order review hook
	 *
	 * @param string $post_raw_data post data.
	 */
	public function update_order_review_hook( $post_raw_data ) {

		do_action( 'cartflows_woo_checkout_update_order_review', self::$ajax_data );
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Woo_Hooks::get_instance();
