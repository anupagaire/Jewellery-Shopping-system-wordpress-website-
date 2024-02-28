<?php
/**
 * Beaver Builder Editor Compatibility.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Checkout Markup
 *
 * @since 1.6.15
 */
class Cartflows_BB_Editor {

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
		if ( class_exists( 'FLBuilderModel' ) ) {
			$this->bb_editor_compatibility();
		}
	}

	/**
	 * Beaver Builder editor compatibility.
	 */
	public function bb_editor_compatibility() {

		if ( FLBuilderModel::is_builder_active() ) {

			$current_post_id = get_the_id();

			$cf_frontend = Cartflows_Frontend::get_instance();

			/* Load woo templates from plugin. */
			add_filter( 'woocommerce_locate_template', array( $cf_frontend, 'override_woo_template' ), 20, 3 );

			do_action( 'cartflows_bb_editor_compatibility', $current_post_id );

			/* Thank you filters. */
			add_filter( 'cartflows_show_demo_order_details', '__return_true' );

			add_action( 'cartflows_bb_before_checkout_shortcode', array( $this, 'before_checkout_shortcode_actions' ), 10, 2 );

			add_action(
				'wp_head',
				function() {
					$current_post_id = get_the_id();

					$cartflows_bb_vars = array(
						'wcf_enable_product_options' => get_post_meta( $current_post_id, 'wcf-enable-product-options', true ),
						'wcf_order_bump'             => get_post_meta( $current_post_id, 'wcf-order-bump', true ),
						'wcf_pre_checkout_offer'     => get_post_meta( $current_post_id, 'wcf-pre-checkout-offer', true ),
					);
					$localize_script   = '<script type="text/javascript">';
					$localize_script  .= 'var CartFlowsBBVars = ' . wp_json_encode( $cartflows_bb_vars ) . ';';
					$localize_script  .= '</script>';
					echo $localize_script; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			);

			add_action( 'fl_builder_after_save_layout', array( $this, 'update_required_step_meta_data' ), 10, 4 );

		}
	}

	/**
	 * Before checkout shortcode actions.
	 *
	 * @param int    $post_id step id.
	 * @param bool   $publish is published.
	 * @param object $layout_data post data.
	 * @param object $settings settings data.
	 */
	public function update_required_step_meta_data( $post_id, $publish, $layout_data, $settings ) {

		if ( wcf()->utils->is_step_post_type( get_post_type( $post_id ) ) ) {

			$step_type       = get_post_meta( $post_id, 'wcf-step-type', true );
			$module_settings = false;

			switch ( $step_type ) {

				case 'checkout':
					foreach ( $layout_data as $node => $data ) {
						if ( ! empty( $data->type ) && 'module' === $data->type && ! empty( $data->settings->type ) && 'cartflows-bb-checkout-form' === $data->settings->type ) {
							$module_settings = $data->settings;
							break;
						}
					}
					$meta_keys = array(
						'checkout_layout' => 'wcf-checkout-layout',
					);
					break;

				case 'optin':
					break;

				default:
			}

			if ( $module_settings ) {

				foreach ( $meta_keys as $key => $meta_key ) {

					if ( isset( $module_settings->$key ) ) {
						update_post_meta( $post_id, $meta_key, $module_settings->$key );
					}
				}
			}
		}
	}

	/**
	 * Before checkout shortcode actions.
	 *
	 * @param int $checkout_id checkout id.
	 */
	public function before_checkout_shortcode_actions( $checkout_id ) {

		// Added to modify the fields labels and placeholders to display it in the preview mode.
		Cartflows_Checkout_Fields::get_instance()->checkout_field_actions();

		do_action( 'cartflows_checkout_before_shortcode', $checkout_id );
	}

}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_BB_Editor::get_instance();
