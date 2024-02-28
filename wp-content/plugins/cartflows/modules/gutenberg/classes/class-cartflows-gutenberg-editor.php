<?php
/**
 * Gutenburg Editor Compatibility.
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
class Cartflows_Gutenberg_Editor {

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

		add_action( 'admin_init', array( $this, 'gutenberg_editor_compatibility' ) );

		add_action( 'save_post_cartflows_step', array( $this, 'update_required_step_meta_data' ), 10, 3 );

		add_action( 'admin_init', array( $this, 'add_gcp_colors_to_block_default_color_pallet' ), 12 );
	}

	/**
	 * Function to add the CartFlows flow's Global Color Pallet to block editor's default color pallet.
	 * This will display the flow's GCP colors in the color component of Gutenberg.
	 *
	 * @since x.x.x
	 * @return void
	 */
	public function add_gcp_colors_to_block_default_color_pallet() {

		$post_id   = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$post_type = get_post_type( $post_id );
		$flow_id   = wcf()->utils->get_flow_id_from_step_id( $post_id );

		if ( ! empty( $flow_id ) && Cartflows_Helper::is_gcp_styling_enabled( (int) $flow_id ) && CARTFLOWS_STEP_POST_TYPE === $post_type ) {

			// Try to get the current theme default color palette.
			$default_color_pallet = current( (array) get_theme_support( 'editor-color-palette' ) );

			// Get default core color palette from wp-includes/theme.json.
			if ( false === $default_color_pallet && class_exists( 'WP_Theme_JSON_Resolver' ) ) {
				$settings = WP_Theme_JSON_Resolver::get_core_data()->get_settings();

				if ( isset( $settings['color']['palette']['default'] ) ) {
					$default_color_pallet = $settings['color']['palette']['default'];
				}
			}

			$new_color_palette = Cartflows_Helper::generate_css_var_array( $flow_id );

			// Merge the old and new color palettes.
			if ( ! empty( $default_color_pallet ) ) {
				$new_color_palette = array_merge( $default_color_pallet, $new_color_palette );
			}
			// Apply the color palette containing the original colors and the new colors.
			add_theme_support( 'editor-color-palette', $new_color_palette );

		}

	}

	/**
	 * Before checkout shortcode actions.
	 *
	 * @param int    $post_ID step id.
	 * @param object $post post data.
	 * @param bool   $update is updated.
	 */
	public function update_required_step_meta_data( $post_ID, $post, $update ) {

		$step_type  = get_post_meta( $post_ID, 'wcf-step-type', true );
		$block_data = '';

		switch ( $step_type ) {

			case 'checkout':
				$post_content = parse_blocks( $post->post_content );
				$block_data   = $this->gutenberg_find_block_recursive( $post_content, 'wcfb/checkout-form' );
				$meta_keys    = array(
					'layout' => 'wcf-checkout-layout',
				);
				break;

			case 'optin':
				break;

			default:
		}

		if ( is_array( $block_data ) && ! empty( $block_data ) && isset( $block_data['attrs'] ) ) {

			foreach ( $meta_keys as $key => $meta_key ) {

				if ( isset( $block_data['attrs'][ $key ] ) ) {
					update_post_meta( $post_ID, $meta_key, $block_data['attrs'][ $key ] );
				}
			}
		}
	}

	/**
	 * Get the block data.
	 *
	 * @param array  $elements elements data.
	 * @param string $slug widget name.
	 */
	public function gutenberg_find_block_recursive( $elements, $slug = 'wcfb/checkout-form' ) {
		foreach ( $elements as $element ) {
			if ( $slug === $element['blockName'] ) {
				return $element;
			}
			if ( ! empty( $element['innerBlocks'] ) ) {
				$element = $this->gutenberg_find_block_recursive( $element['innerBlocks'] );
				if ( $element ) {
					return $element;
				}
			}
		}
		return false;
	}

	/**
	 * Gutenburg editor compatibility.
	 */
	public function gutenberg_editor_compatibility() {
		// Calling this function on 'admin_init'. No nonce required.
		//phpcs:disable WordPress.Security.NonceVerification
		if ( is_admin() && isset( $_REQUEST['action'] ) ) {

			$current_post_id = false;

			if ( 'edit' === $_REQUEST['action'] && isset( $_GET['post'] ) ) {
				$current_post_id = intval( $_GET['post'] );
			} elseif ( isset( $_REQUEST['cartflows_gb'] ) && isset( $_POST['id'] ) ) {
				$current_post_id = intval( $_POST['id'] );
			}
		//phpcs:enable WordPress.Security.NonceVerification
			if ( $current_post_id ) {

				$current_post_type = get_post_type( $current_post_id );

				if ( wcf()->utils->is_step_post_type( $current_post_type ) ) {

					if ( wcf()->is_woo_active ) {

						$this->maybe_init_cart();

						/* Load woo templates from plugin */
						$cf_frontend = Cartflows_Frontend::get_instance();
						add_filter( 'woocommerce_locate_template', array( $cf_frontend, 'override_woo_template' ), 20, 3 );

						add_action( 'cartflows_gutenberg_before_checkout_shortcode', array( $this, 'before_gb_checkout_shortcode_actions' ) );

						add_action( 'cartflows_gutenberg_before_optin_shortcode', array( $this, 'before_gb_optin_shortcode_actions' ) );

					}

					do_action( 'cartflows_gutenberg_editor_compatibility', $current_post_id );
				}
			}
		}
	}

	/**
	 * Before checkout shortcode actions.
	 */
	public function maybe_init_cart() {

		wc()->frontend_includes();

		$has_cart = is_a( WC()->cart, 'WC_Cart' );

		if ( ! $has_cart ) {
			$session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );
			WC()->session  = new $session_class();
			WC()->session->init();
			WC()->cart     = new \WC_Cart();
			WC()->customer = new \WC_Customer( get_current_user_id(), true );

		}

		/* For preview */
		add_filter( 'woocommerce_checkout_redirect_empty_cart', '__return_false' );
	}

	/**
	 * Before checkout shortcode actions.
	 *
	 * @param int $checkout_id checkout id.
	 */
	public function before_gb_checkout_shortcode_actions( $checkout_id ) {

		// Added to modify the fields labels and placeholders to display it in the preview mode.
		Cartflows_Checkout_Fields::get_instance()->checkout_field_actions();

		Cartflows_Checkout_Markup::get_instance()->before_checkout_shortcode_actions();

		do_action( 'cartflows_checkout_before_shortcode', $checkout_id );
	}

	/**
	 * Before optin shortcode actions.
	 *
	 * @param int $checkout_id checkout id.
	 */
	public function before_gb_optin_shortcode_actions( $checkout_id ) {

		do_action( 'cartflows_optin_before_shortcode', $checkout_id );
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Gutenberg_Editor::get_instance();
