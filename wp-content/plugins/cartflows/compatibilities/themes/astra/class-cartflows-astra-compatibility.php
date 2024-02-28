<?php
/**
 * Astra theme compatibility
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Cartflows_Astra_Compatibility' ) ) :

	/**
	 * Class for Astra theme compatibility
	 */
	class Cartflows_Astra_Compatibility {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.5.7
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.5.7
		 */
		public function __construct() {

			add_action( 'cartflows_checkout_before_shortcode', array( $this, 'cartflows_theme_compatibility_astra' ) );
			add_action( 'cartflows_optin_before_shortcode', array( $this, 'cartflows_theme_compatibility_astra' ) );

			add_action( 'wp', array( $this, 'cartflows_load_wp_actions_for_astra' ), 56 );
			add_action( 'wp', array( $this, 'cartflows_page_template_specific_action' ), 10 );

			add_filter( 'astra_woo_shop_product_structure_override', array( $this, 'override_product_structure_on_checkout' ) );

			add_action( 'cartflows_after_save_store_checkout', array( $this, 'clear_astra_woo_css_cache' ) );

			// Update Astra's admin top level menu position.
			add_filter( 'astra_menu_priority', array( $this, 'update_admin_menu_position' ), 10, 1 );

		}

		/**
		 * Update Astra's menu priority to show after Dashboard menu.
		 *
		 * @param int $menu_priority priority for admin menu.
		 * @since 2.0.0
		 * @return float
		 */
		public function update_admin_menu_position( $menu_priority ) {
			return 2.1;
		}

		/**
		 * Clear theme cached CSS if required.
		 */
		public function clear_astra_woo_css_cache() {

			// Clear Astra CSS cache for modern checkout.
			if ( defined( 'ASTRA_THEME_VERSION' ) && function_exists( 'astra_clear_all_assets_cache' ) && is_callable( 'astra_clear_all_assets_cache' ) ) {
				astra_clear_all_assets_cache();
			}

		}


		/**
		 * Override the Astra's actions only for the CF Checkout page to display.
		 * Stripe/smart Payment buttons.
		 *
		 * @since 1.10.0
		 *
		 * @param bool $bool true/false to override actions or not.
		 *
		 * @return bool
		 */
		public function override_product_structure_on_checkout( $bool ) {
			return _is_wcf_checkout_type() ? true : $bool;
		}


		/**
		 * Function to remove the astra hooks.
		 *
		 * @since 1.5.7
		 *
		 * @return void
		 */
		public function cartflows_theme_compatibility_astra() {
			remove_action( 'woocommerce_checkout_before_customer_details', 'astra_two_step_checkout_form_wrapper_div', 1 );
			remove_action( 'woocommerce_checkout_before_customer_details', 'astra_two_step_checkout_form_ul_wrapper', 2 );
			remove_action( 'woocommerce_checkout_order_review', 'astra_woocommerce_div_wrapper_close', 30 );
			remove_action( 'woocommerce_checkout_order_review', 'astra_woocommerce_ul_close', 30 );
			remove_action( 'woocommerce_checkout_before_customer_details', 'astra_two_step_checkout_address_li_wrapper', 5 );
			remove_action( 'woocommerce_checkout_after_customer_details', 'astra_woocommerce_li_close' );
			remove_action( 'woocommerce_checkout_before_order_review', 'astra_two_step_checkout_order_review_wrap', 1 );
			remove_action( 'woocommerce_checkout_after_order_review', 'astra_woocommerce_li_close', 40 );

			add_filter( 'astra_theme_woocommerce_dynamic_css', '__return_empty_string' );

			if ( class_exists( 'ASTRA_Ext_WooCommerce_Markup' ) ) {
				remove_action( 'woocommerce_review_order_after_submit', array( ASTRA_Ext_WooCommerce_Markup::get_instance(), 'back_to_cart_button_on_checkout' ) );
			}
		}

		/**
		 * Function to remove page template specific actions.
		 * Used to remove undesigned menu from the footer of the CartFlows pages only.
		 *
		 * @since 1.6.6
		 *
		 * @return void
		 */
		public function cartflows_page_template_specific_action() {

			// Return if not the CartFlows page.
			if ( ! wcf()->utils->is_step_post_type() ) {
				return;
			}

			$page_template = get_post_meta( _get_wcf_step_id(), '_wp_page_template', true );

			if ( _wcf_supported_template( $page_template ) ) {

				add_action( 'wp_enqueue_scripts', array( $this, 'gutenberg_block_color_support' ), 21 );

				if ( class_exists( 'Astra_Builder_Header' ) ) {

					$astra_builder_header = Astra_Builder_Header::get_instance();

					remove_action( 'wp_footer', array( $astra_builder_header, 'mobile_popup' ) );
					remove_action( 'wp_footer', array( $astra_builder_header, 'mobile_cart_flyout' ) );
				}

				// Removed the scroll to top button if template type is not default.
				if ( class_exists( 'Astra_Ext_Scroll_To_Top_Markup' ) ) {

					$astra_ext_scroll_to_top = Astra_Ext_Scroll_To_Top_Markup::get_instance();
					remove_action( 'wp_footer', array( $astra_ext_scroll_to_top, 'html_markup_loader' ) );
				}
			}
		}

		/**
		 * Function to add/remove the actions/hooks on wp action.
		 *
		 * @since 1.5.7
		 *
		 * @return void
		 */
		public function cartflows_load_wp_actions_for_astra() {

			// Return if not the CartFlows page.
			if ( ! wcf()->utils->is_step_post_type() ) {
				return;
			}

			$page_template = get_post_meta( _get_wcf_step_id(), '_wp_page_template', true );

			if ( _wcf_supported_template( $page_template ) ) {
				return;
			}

			add_action( 'wp_enqueue_scripts', array( $this, 'astra_compatibility_external_css' ), 101 );

			// Re-add the WooCommerce's styles & script swhich are form Astra.
			$astra_woo = Astra_Woocommerce::get_instance();
			add_filter( 'woocommerce_enqueue_styles', array( $astra_woo, 'woo_filter_style' ), 9999 );
		}

		/**
		 * Function to add theme color on frontend.
		 *
		 * @since 1.10.0
		 *
		 * @return void
		 */
		public function gutenberg_block_color_support() {

			if ( class_exists( 'Astra_Global_Palette' ) && function_exists( 'astra_parse_css' ) ) {

				$palette_style[':root'] = Astra_Global_Palette::generate_global_palette_style();
				$css                    = astra_parse_css( $palette_style );
				wp_add_inline_style( 'wcf-normalize-frontend-global', $css );
			}
		}

		/**
		 * Load the CSS
		 *
		 * @since 1.5.7
		 *
		 * @return void
		 */
		public function astra_compatibility_external_css() {

			wp_enqueue_style( 'wcf-checkout-astra-compatibility', CARTFLOWS_URL . 'compatibilities/themes/astra/css/astra-compatibility.css', '', CARTFLOWS_VER );
		}
	}
	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Astra_Compatibility::get_instance();

endif;
