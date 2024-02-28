<?php
/**
 * Elementor Editor Compatibility.
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
 * @since 1.0.0
 */
class Cartflows_Elementor_Editor {

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

		$this->elementor_editor_compatibility();

		add_action( 'elementor/editor/after_save', array( $this, 'update_required_step_meta_data' ), 10, 2 );
	}

	/**
	 * Before checkout shortcode actions.
	 *
	 * @param int   $step_id checkout id.
	 * @param array $editor_data editor data.
	 */
	public function update_required_step_meta_data( $step_id, $editor_data ) {

		if ( wcf()->utils->is_step_post_type( get_post_type( $step_id ) ) ) {

			$step_type   = get_post_meta( $step_id, 'wcf-step-type', true );
			$widget_data = '';

			switch ( $step_type ) {

				case 'checkout':
					$widget_data = $this->elementor_find_element_recursive( $editor_data, 'checkout-form' );
					$meta_keys   = array(
						'layout' => 'wcf-checkout-layout',
					);
					break;

				case 'optin':
					break;

				default:
			}

			if ( is_array( $widget_data ) && ! empty( $widget_data ) && isset( $widget_data['settings'] ) ) {

				foreach ( $meta_keys as $key => $meta_key ) {

					if ( isset( $widget_data['settings'][ $key ] ) ) {
						update_post_meta( $step_id, $meta_key, $widget_data['settings'][ $key ] );
					}
				}
			}
		}
	}

	/**
	 * Get the elementor widget data.
	 *
	 * @param array  $elements elements data.
	 * @param string $slug widget name.
	 */
	public function elementor_find_element_recursive( $elements, $slug = 'checkout-form' ) {
		foreach ( $elements as $element ) {
			if ( 'widget' === $element['elType'] && $slug === $element['widgetType'] ) {
				return $element;
			}
			if ( ! empty( $element['elements'] ) ) {
				$element = $this->elementor_find_element_recursive( $element['elements'] );
				if ( $element ) {
					return $element;
				}
			}
		}
		return false;
	}

	/**
	 * Elementor editor compatibility.
	 */
	public function elementor_editor_compatibility() {
		// This file included on elementor actions.
		//phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_REQUEST['action'] ) && is_admin() ) {

			$current_post_id = false;
			$elementor_ajax  = false;

			if ( 'elementor' === $_REQUEST['action'] && isset( $_GET['post'] ) && ! empty( $_GET['post'] ) ) {
				$current_post_id = intval( $_GET['post'] );
			}

			if ( wp_doing_ajax() && 'elementor_ajax' === $_REQUEST['action'] && isset( $_REQUEST['editor_post_id'] ) && ! empty( $_REQUEST['editor_post_id'] ) ) {
				$current_post_id = intval( $_REQUEST['editor_post_id'] );
				$elementor_ajax  = true;
			}

			if ( $current_post_id ) {

				$current_post_type = get_post_type( $current_post_id );

				if ( wcf()->utils->is_step_post_type( $current_post_type ) ) {

					$cf_frontend = Cartflows_Frontend::get_instance();

					/* Load woo templates from plugin */
					add_filter( 'woocommerce_locate_template', array( $cf_frontend, 'override_woo_template' ), 20, 3 );

					do_action( 'cartflows_elementor_editor_compatibility', $current_post_id, $elementor_ajax );
				}
			}

			/* Compatibility without condition, just to add actions */
			if ( 'elementor' === $_REQUEST['action'] || 'elementor_ajax' === $_REQUEST['action'] ) {

				add_action( 'cartflows_elementor_before_checkout_shortcode', array( $this, 'before_checkout_shortcode_actions' ) );
				add_action( 'cartflows_elementor_before_optin_shortcode', array( $this, 'before_optin_shortcode_actions' ) );

				/* Thank you filters */
				add_filter( 'cartflows_show_demo_order_details', '__return_true' );
			}
		//phpcs:enable WordPress.Security.NonceVerification.Recommended
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

	/**
	 * Before optin shortcode actions.
	 *
	 * @param int $checkout_id checkout id.
	 */
	public function before_optin_shortcode_actions( $checkout_id ) {

		do_action( 'cartflows_optin_before_shortcode', $checkout_id );
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Elementor_Editor::get_instance();
