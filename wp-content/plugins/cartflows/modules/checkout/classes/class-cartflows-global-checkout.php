<?php
/**
 * Global Checkout.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Global Checkout
 *
 * @since 1.0.0
 */
class Cartflows_Global_Checkout {


	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

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

		/* Global Checkout */
		add_action( 'wp', array( $this, 'override_global_checkout' ), 0 );
		add_action( 'template_redirect', array( $this, 'global_checkout_template_redirect' ), 1 );
		add_action( 'admin_bar_menu', array( $this, 'update_checkout_link_for_global_checkout' ), 999 );

	}

	/**
	 * Update the checkout page link for global checkout.
	 *
	 * @since 1.10.0
	 */
	public function update_checkout_link_for_global_checkout() {

		if ( _is_wcf_checkout_type() ) {

			global $wp_admin_bar;
			global $post;

			$common                   = Cartflows_Helper::get_common_settings();
			$global_checkout          = intval( $common['global_checkout'] );
			$override_global_checkout = $common['override_global_checkout'];

			if ( $post && $global_checkout === $post->ID && 'enable' === $override_global_checkout ) {

				$edit_node = $wp_admin_bar->get_node( 'edit' );

				if ( $edit_node ) {
					$edit_node->href = get_edit_post_link( $post->ID );
					$wp_admin_bar->add_node( $edit_node );
				}
			}
		}
	}

	/**
	 * Override global checkout page
	 *
	 * @since 1.10.0
	 */
	public function override_global_checkout() {

		if ( wcf()->utils->is_step_post_type() ) {
			return;
		}

		if ( is_product() || ! is_checkout() || is_order_received_page() || is_checkout_pay_page() ) {
			return;
		}

		// Return if the key OR Order parameter is found in the URL for certain Payment gateways.
		if ( isset( $_GET['key'] ) || isset( $_GET['order'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$common = Cartflows_Helper::get_common_settings();

		$global_checkout          = $common['global_checkout'];
		$override_global_checkout = $common['override_global_checkout'];

		// Override only WooCommerce checkout page with CartFlows checkout page.
		if ( ! empty( $global_checkout ) && 'enable' === $override_global_checkout ) {

			$checkout_post = get_post( $global_checkout );

			if ( $checkout_post && 'publish' === $checkout_post->post_status ) {

				if ( isset( $GLOBALS['posts'][0] ) ) {
					$GLOBALS['posts'][0] = $checkout_post; //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				}

				if ( isset( $GLOBALS['wp_the_query']->post ) ) {
					$GLOBALS['wp_the_query']->post = $checkout_post; //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				}

				$GLOBALS['post'] = $checkout_post; //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			}
		}
	}

	/**
	 * Redirect from default to the global checkout page
	 *
	 * @since 1.10.0
	 */
	public function global_checkout_template_redirect() {

		if ( wcf()->utils->is_step_post_type() ) {
			return;
		}

		if ( is_product() || ! is_checkout() || is_order_received_page() || is_checkout_pay_page() ) {
			return;
		}

		// Return if the key OR Order parameter is found in the URL for certain Payment gateways.
		if ( isset( $_GET['key'] ) || isset( $_GET['order'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		// redirect only for cartflows checkout pages.
		$common = Cartflows_Helper::get_common_settings();

		$global_checkout          = $common['global_checkout'];
		$override_global_checkout = $common['override_global_checkout'];

		if ( ! empty( $global_checkout ) && 'enable' !== $override_global_checkout ) {

			$link = apply_filters( 'cartflows_global_checkout_url', get_permalink( $global_checkout ) );

			if ( ! empty( $link ) ) {

				wp_safe_redirect( $link );
				exit;
			}
		}
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Global_Checkout::get_instance();
