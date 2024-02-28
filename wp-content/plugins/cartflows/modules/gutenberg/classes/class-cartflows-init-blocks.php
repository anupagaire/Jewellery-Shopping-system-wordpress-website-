<?php
/**
 * Cartflows Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.6.15
 * @package Cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Cartflows_Init_Blocks.
 *
 * @package Cartflows
 */
class Cartflows_Init_Blocks {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Store Json variable
	 *
	 * @since 1.8.1
	 * @var instance
	 */
	public static $icon_json;

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
	 * Constructor
	 */
	public function __construct() {

		// Hook: Frontend assets.
		add_action( 'enqueue_block_assets', array( $this, 'block_assets' ) );

		// Hook: Editor assets.
		add_action( 'enqueue_block_editor_assets', array( $this, 'editor_assets' ) );

		if ( version_compare( get_bloginfo( 'version' ), '5.8', '>=' ) ) {
			add_filter( 'block_categories_all', array( $this, 'register_block_category' ), 10, 2 );
		} else {
			add_filter( 'block_categories', array( $this, 'register_block_category' ), 10, 2 );
		}

		add_action( 'wp_ajax_wpcf_order_detail_form_shortcode', array( $this, 'order_detail_form_shortcode' ) );
		add_action( 'wp_ajax_wpcf_order_checkout_form_shortcode', array( $this, 'order_checkout_form_shortcode' ) );
		add_action( 'wp_ajax_wpcf_optin_form_shortcode', array( $this, 'optin_form_shortcode' ) );

		add_filter(
			'cartflows_show_demo_order_details',
			function() {
				return true;
			}
		);

		add_action( 'enqueue_block_editor_assets', array( $this, 'add_gcp_vars_to_block_editor' ), 12 );

		// Load the action only if the theme.json file is present in the file.
		if ( wp_theme_has_theme_json() ) {
			add_filter( 'wp_theme_json_data_theme', array( $this, 'update_theme_json_file_config' ) );
		}
	}

	/**
	 * Add CartFlows GCP css vars to the theme's theme.json file if it is present.
	 *
	 * Use-case: Some themes adds the theme.json file in the theme's root directory. Due to this change, it overrides the default color pallet.
	 * and the filter which are using to add the CSS vars in the Gutenberg does not works and hence, we have to re-add and append it to the theme.json file.
	 *
	 * Note: If theme.json is not present then this filter will not be executed.
	 *
	 * @param WP_Theme_JSON_Data $theme_json_data The Data from the theme.json file.
	 * @return WP_Theme_JSON_Data $theme_json_data Modified data of theme.json file.
	 *
	 * @since x.x.x
	 */
	public function update_theme_json_file_config( $theme_json_data ) {
		$theme_json_data_two = $theme_json_data->get_data();

		$post_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$flow_id = wcf()->utils->get_flow_id_from_step_id( $post_id );

		if (
			! empty( $flow_id ) &&
			Cartflows_Helper::is_gcp_styling_enabled( (int) $flow_id ) &&
			isset( $theme_json_data_two['settings'] ) &&
			isset( $theme_json_data_two['settings']['color'] )
		) {

			$new_color_palette = Cartflows_Helper::generate_css_var_array( $flow_id );

			if ( ! empty( $new_color_palette ) ) {
				$theme_json_data_two['settings']['color']['palette']['theme'] = array_merge( $theme_json_data_two['settings']['color']['palette']['theme'], $new_color_palette );
			}
		}

		return $theme_json_data->update_with( $theme_json_data_two );
	}

	/**
	 * Enqueue the Global Color Pallet CSS vars to the page to use in the page builder settings.
	 * This CSS vars needs to be re-added so as to enqueue in the block editor to display the colors in the editor window.
	 *
	 * Note: Currently the GCP support is added for Elementor and Block Builder.
	 *
	 * @since x.x.x
	 * @return void
	 */
	public function add_gcp_vars_to_block_editor() {

		// Call the same function which generates the inline styles i:e the CSS VARs with the selected values.
		wcf()->flow->enqueue_gcp_color_vars( 'CF_block-cartflows-frontend-style' );

	}

	/**
	 * Renders the Order Detail Form shortcode.
	 *
	 * @since 1.6.15
	 */
	public function order_detail_form_shortcode() {

		check_ajax_referer( 'wpcf_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'cartflows' ) ) );
		}

		add_filter(
			'cartflows_show_demo_order_details',
			function() {
				return true;
			}
		);

		if ( ! empty( $_POST['thanyouText'] ) ) {

			add_filter(
				'cartflows_thankyou_meta_wcf-tq-text',
				function( $text ) {
					check_ajax_referer( 'wpcf_ajax_nonce', 'nonce' );

					$text = isset( $_POST['thanyouText'] ) ? sanitize_text_field( wp_unslash( $_POST['thanyouText'] ) ) : '';

					return $text;
				},
				10,
				1
			);
		}

		$thankyou_id          = isset( $_POST['id'] ) ? intval( wp_unslash( $_POST['id'] ) ) : 0;
		$data['html']         = do_shortcode( '[cartflows_order_details]' );
		$data['thankyouText'] = wcf()->options->get_thankyou_meta_value( $thankyou_id, 'wcf-tq-text' );

		wp_send_json_success( $data );
	}

	/**
	 * Renders the Order Checkout Form shortcode.
	 *
	 * @since 1.6.15
	 */
	public function order_checkout_form_shortcode() {
		check_ajax_referer( 'wpcf_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'cartflows' ) ) );
		}

		add_filter(
			'cartflows_show_demo_checkout',
			function() {
				return true;
			}
		);

		if ( isset( $_POST['id'] ) ) {
			$checkout_id = intval( $_POST['id'] );
		}

		$store_checkout = intval( \Cartflows_Helper::get_global_setting( '_cartflows_store_checkout' ) );

		$flow_id = wcf()->utils->get_flow_id_from_step_id( $checkout_id );

		if ( ! wcf()->flow->is_flow_testmode( $flow_id ) && ( $store_checkout !== $flow_id ) ) {

			$products = wcf()->utils->get_selected_checkout_products( $checkout_id );

			if ( ! is_array( $products ) || empty( $products[0]['product'] ) ) {
				wc_clear_notices();
				wc_add_notice( __( 'No product is selected. Please select products from the checkout meta settings to continue.', 'cartflows' ), 'error' );
			}
		}
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form' );

		add_action( 'woocommerce_checkout_order_review', array( Cartflows_Checkout_Markup::get_instance(), 'display_custom_coupon_field' ) );

		$attributes['layout']                          = isset( $_POST['layout'] ) ? sanitize_title( wp_unslash( $_POST['layout'] ) ) : '';
		$attributes['orderBumpSkin']                   = isset( $_POST['orderBumpSkin'] ) ? sanitize_title( wp_unslash( $_POST['orderBumpSkin'] ) ) : '';
		$attributes['orderBumpCheckboxArrow']          = isset( $_POST['orderBumpCheckboxArrow'] ) ? sanitize_title( wp_unslash( $_POST['orderBumpCheckboxArrow'] ) ) : '';
		$attributes['orderBumpCheckboxArrowAnimation'] = isset( $_POST['orderBumpCheckboxArrowAnimation'] ) ? sanitize_title( wp_unslash( $_POST['orderBumpCheckboxArrowAnimation'] ) ) : '';
		$attributes['sectionposition']                 = isset( $_POST['sectionposition'] ) ? sanitize_title( wp_unslash( $_POST['sectionposition'] ) ) : '';
		$attributes['productOptionsSkin']              = isset( $_POST['productOptionsSkin'] ) ? sanitize_title( wp_unslash( $_POST['productOptionsSkin'] ) ) : '';
		$attributes['productOptionsImages']            = isset( $_POST['productOptionsImages'] ) ? sanitize_title( wp_unslash( $_POST['productOptionsImages'] ) ) : '';
		$attributes['productOptionsSectionTitleText']  = isset( $_POST['productOptionsSectionTitleText'] ) ? sanitize_text_field( wp_unslash( $_POST['productOptionsSectionTitleText'] ) ) : '';
		$attributes['PreSkipText']                     = isset( $_POST['PreSkipText'] ) ? sanitize_title( wp_unslash( $_POST['PreSkipText'] ) ) : '';
		$attributes['PreOrderText']                    = isset( $_POST['PreOrderText'] ) ? sanitize_title( wp_unslash( $_POST['PreOrderText'] ) ) : '';
		$attributes['PreProductTitleText']             = isset( $_POST['PreProductTitleText'] ) ? sanitize_title( wp_unslash( $_POST['PreProductTitleText'] ) ) : '';
		$attributes['preSubTitleText']                 = isset( $_POST['preSubTitleText'] ) ? sanitize_title( wp_unslash( $_POST['preSubTitleText'] ) ) : '';
		$attributes['preTitleText']                    = isset( $_POST['preTitleText'] ) ? sanitize_title( wp_unslash( $_POST['preTitleText'] ) ) : '';
		$attributes['PreProductDescText']              = isset( $_POST['PreProductDescText'] ) ? sanitize_title( wp_unslash( $_POST['PreProductDescText'] ) ) : '';
		$attributes['inputSkins']                      = isset( $_POST['inputSkins'] ) ? sanitize_title( wp_unslash( $_POST['inputSkins'] ) ) : '';
		$attributes['enableNote']                      = isset( $_POST['enableNote'] ) ? sanitize_title( wp_unslash( $_POST['enableNote'] ) ) : '';
		$attributes['noteText']                        = isset( $_POST['noteText'] ) ? sanitize_text_field( wp_unslash( $_POST['noteText'] ) ) : '';
		$attributes['stepOneTitleText']                = isset( $_POST['stepOneTitleText'] ) ? sanitize_text_field( wp_unslash( $_POST['stepOneTitleText'] ) ) : '';
		$attributes['stepOneSubTitleText']             = isset( $_POST['stepOneSubTitleText'] ) ? sanitize_text_field( wp_unslash( $_POST['stepOneSubTitleText'] ) ) : '';
		$attributes['stepTwoTitleText']                = isset( $_POST['stepTwoTitleText'] ) ? sanitize_text_field( wp_unslash( $_POST['stepTwoTitleText'] ) ) : '';
		$attributes['stepTwoSubTitleText']             = isset( $_POST['stepTwoSubTitleText'] ) ? sanitize_text_field( wp_unslash( $_POST['stepTwoSubTitleText'] ) ) : '';
		$attributes['offerButtonTitleText']            = isset( $_POST['offerButtonTitleText'] ) ? sanitize_text_field( wp_unslash( $_POST['offerButtonTitleText'] ) ) : '';
		$attributes['offerButtonSubTitleText']         = isset( $_POST['offerButtonSubTitleText'] ) ? sanitize_text_field( wp_unslash( $_POST['offerButtonSubTitleText'] ) ) : '';

		$checkout_fields = array(
			// Input Fields.
			array(
				'filter_slug'  => 'wcf-fields-skins',
				'setting_name' => 'inputSkins',
			),
			array(
				'filter_slug'  => 'wcf-checkout-layout',
				'setting_name' => 'layout',
			),
		);

		if ( isset( $checkout_fields ) && is_array( $checkout_fields ) ) {

			foreach ( $checkout_fields as $key => $field ) {

				$setting_name = $field['setting_name'];

				if ( '' !== $attributes[ $setting_name ] ) {

					add_filter(
						'cartflows_checkout_meta_' . $field['filter_slug'],
						function ( $value ) use ( $setting_name, $attributes ) {

							$value = $attributes[ $setting_name ];

							return $value;
						},
						10,
						1
					);
				}
			}
		}
		do_action( 'cartflows_gutenberg_before_checkout_shortcode', $checkout_id );

		do_action( 'cartflows_gutenberg_checkout_options_filters', $attributes );

		do_action( 'cartflows_gutenberg_before_checkout_shortcode' );

		$data['html'] = do_shortcode( '[cartflows_checkout]' );

		wp_send_json_success( $data );
	}

	/**
	 * Renders the Optin Form shortcode.
	 *
	 * @since 1.6.15
	 */
	public function optin_form_shortcode() {

		check_ajax_referer( 'wpcf_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'cartflows' ) ) );
		}

		add_filter(
			'cartflows_show_demo_optin_form',
			function() {
				return true;
			}
		);

		if ( isset( $_POST['id'] ) ) {
			$optin_id = intval( $_POST['id'] );
		}

		$products = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-optin-product' );
		if ( is_array( $products ) && count( $products ) < 1 ) {
			wc_clear_notices();
			wc_add_notice( __( 'No product is selected. Please select a Simple, Virtual and Free product from the meta settings.', 'cartflows' ), 'error' );
		}

		$attributes['input_skins'] = isset( $_POST['input_skins'] ) ? sanitize_title( wp_unslash( $_POST['input_skins'] ) ) : '';

		$optin_fields = array(

			// Input Fields.
			array(
				'filter_slug'  => 'wcf-input-fields-skins',
				'setting_name' => 'input_skins',
			),
		);

		if ( isset( $optin_fields ) && is_array( $optin_fields ) ) {

			foreach ( $optin_fields as $key => $field ) {

				$setting_name = $field['setting_name'];

				add_filter(
					'cartflows_optin_meta_' . $field['filter_slug'],
					function ( $value ) use ( $setting_name, $attributes ) {

						$value = $attributes[ $setting_name ];

						return $value;
					},
					10,
					1
				);
			}
		}

		do_action( 'cartflows_gutenberg_optin_options_filters', $attributes );

		add_filter( 'woocommerce_cart_needs_payment', '__return_false' );
		add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );
		add_filter( 'woocommerce_cart_needs_shipping_address', '__return_false' );

		$data['html']       = do_shortcode( '[cartflows_optin]' );
		$data['buttonText'] = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-submit-button-text' );
		wp_send_json_success( $data );
	}

	/**
	 * Enqueue Gutenberg block assets for both frontend + backend.
	 *
	 * @since 1.6.15
	 */
	public function block_assets() {

		global $post;

		if ( $post && CARTFLOWS_STEP_POST_TYPE === $post->post_type ) {

			// Register block styles for both frontend + backend.
			wp_enqueue_style(
				'CF_block-cartflows-style-css', // Handle.
				CARTFLOWS_URL . 'modules/gutenberg/build/style-blocks.css',
				is_admin() ? array( 'wp-editor' ) : null, // Dependency to include the CSS after it.
				CARTFLOWS_VER // filemtime( plugin_dir_path( __DIR__ ) . 'build/style-blocks.css' ) // Version: File modification time.
			);

			$flow_id = wcf()->utils->get_flow_id_from_step_id( $post->ID );

			// Return if no flow ID is found.
			if ( empty( $flow_id ) ) {
				return;
			}

			if ( Cartflows_Helper::is_gcp_styling_enabled( (int) $flow_id ) ) {

				$gcp_vars = Cartflows_Helper::generate_gcp_css_style( (int) $flow_id );

				// Include the CSS/JS only if the CSS vars are set.
				if ( ! empty( $gcp_vars ) ) {
					// Add editor helper css & JS files.
					wp_enqueue_style( 'wcf-editor-helper-style', CARTFLOWS_URL . 'modules/gutenberg/assets/css/editor-assets.css', array( 'wp-edit-blocks', 'wp-editor' ), CARTFLOWS_VER );
					wp_enqueue_script( 'wcf-editor-helper-script', CARTFLOWS_URL . 'modules/gutenberg/assets/js/editor-assets.js', array( 'wp-editor', 'jquery' ), CARTFLOWS_VER, true );
				}
			}       
		}

	}

	/**
	 * Enqueue assets for both backend.
	 *
	 * @since 1.6.15
	 */
	public function editor_assets() {

		$post_id   = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$post_type = get_post_type( $post_id );

		if ( CARTFLOWS_STEP_POST_TYPE === $post_type ) {

			$wpcf_ajax_nonce       = wp_create_nonce( 'wpcf_ajax_nonce' );
			$step_type             = wcf()->utils->get_step_type( $post_id );
			$show_checkout_pro_opt = apply_filters( 'cartflows_show_checkout_pro_opt', false );

			if ( 'optin' === $step_type && wcf()->is_woo_active ) {
				wp_enqueue_style( 'wcf-optin-template', wcf()->utils->get_css_url( 'optin-template' ), array( 'wp-edit-blocks' ), CARTFLOWS_VER );
			}
			if ( 'checkout' === $step_type && wcf()->is_woo_active ) {
				wp_enqueue_style( 'wcf-checkout-template', wcf()->utils->get_css_url( 'checkout-template' ), array( 'wp-edit-blocks' ), CARTFLOWS_VER );
				Cartflows_Checkout_Markup::get_instance()->shortcode_scripts();
			}

			$script_dep_path = CARTFLOWS_DIR . 'modules/gutenberg/build/blocks.asset.php';
			$script_info     = file_exists( $script_dep_path )
				? include $script_dep_path
				: array(
					'dependencies' => array(),
					'version'      => CARTFLOWS_VER,
				);
			$script_dep      = array_merge( $script_info['dependencies'], array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ) );
			$script_ver      = $script_info['version'];

			// Register block editor script for backend.
			wp_register_script(
				'CF_block-cartflows-block-js', // Handle.
				CARTFLOWS_URL . 'modules/gutenberg/build/blocks.js',
				$script_dep, // Dependencies, defined above.
				$script_ver, // Version: filemtime — Gets file modification time.
				true // Enqueue the script in the footer.
			);

			wp_set_script_translations( 'CF_block-cartflows-block-js', 'cartflows' );

			// Register block editor styles for backend.
			wp_register_style(
				'CF_block-cartflows-block-editor-css', // Handle.
				CARTFLOWS_URL . 'modules/gutenberg/build/blocks.style.css',
				array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
				CARTFLOWS_VER // Version: File modification time.
			);

			// Common Editor style.
			wp_enqueue_style(
				'CF_block-common-editor-css', // Handle.
				CARTFLOWS_URL . 'modules/gutenberg/dist/editor.css',
				array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
				CARTFLOWS_VER // Version: File modification time.
			);

			// Enqueue frontend CSS in editor.
			wp_enqueue_style( 'CF_block-cartflows-frontend-style', CARTFLOWS_URL . 'assets/css/frontend.css', array( 'wp-edit-blocks' ), CARTFLOWS_VER );

			// WP Localized globals. Use dynamic PHP stuff in JavaScript via `cartflowsGlobal` object.
			wp_localize_script(
				'CF_block-cartflows-block-js',
				'cf_blocks_info', // Array containing dynamic data for a JS Global.
				array(
					'pluginDirPath'            => plugin_dir_path( __DIR__ ),
					'pluginDirUrl'             => plugin_dir_url( __DIR__ ),
					'category'                 => 'cartflows',
					'ajax_url'                 => admin_url( 'admin-ajax.php' ),
					'wpcf_ajax_nonce'          => $wpcf_ajax_nonce,
					'blocks'                   => Cartflows_Block_Config::get_block_attributes(),
					'tablet_breakpoint'        => CF_TABLET_BREAKPOINT,
					'mobile_breakpoint'        => CF_MOBILE_BREAKPOINT,
					'show_checkout_pro_opt'    => $show_checkout_pro_opt,
					'ID'                       => $post_id,
					'step_type'                => $step_type,
					'is_cartflows_pro_install' => _is_cartflows_pro(),
					'is_woo_active'            => wcf()->is_woo_active,
					'wcf_svg_icons'            => $this->backend_load_font_awesome_icons(),
					'show_product_options'     => $this->maybe_show_product_options_for_store_checkout( $post_id ),
					// Add more data here that you want to access from `cartflowsGlobal` object.
				)
			);

			/**
			 * Register Gutenberg block on server-side.
			 *
			 * Register the block on server-side to ensure that the block
			 * scripts and styles for both frontend and backend are
			 * enqueued when the editor loads.
			 *
			 * @link https://wordpress.org/gutenberg/handbook/blocks/writing-your-first-block-type#enqueuing-block-scripts
			 * @since 1.6.15
			 */
			register_block_type(
				'wcfb/next-step-button',
				array(
					// Enqueue blocks.js in the editor only.
					'editor_script' => 'CF_block-cartflows-block-js',
					// Enqueue blocks.css in the editor only.
					'style'         => 'CF_block-cartflows-block-editor-css',
					// Enqueue blocks.commoneditorstyle.build.css in the editor only.
					'editor_style'  => 'CF_block-common-editor-css',
				)
			);

		}
	}

	/**
	 * Maybe show product options.
	 *
	 * @param int $step_id step id.
	 * @return bool
	 */
	public function maybe_show_product_options_for_store_checkout( $step_id ) {

		$flow_id        = (int) wcf()->utils->get_flow_id_from_step_id( $step_id );
		$store_checkout = intval( get_option( '_cartflows_store_checkout', false ) );

		if ( $flow_id !== $store_checkout ) {
			return true;

		}

		if ( $flow_id === $store_checkout && apply_filters( 'cartflows_show_store_checkout_product_tab', false ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Get Json Data.
	 *
	 * @since 1.8.1
	 * @return Array
	 */
	public function backend_load_font_awesome_icons() {

		$json_file = CARTFLOWS_DIR . 'modules/gutenberg/src/controls/spectra-icons-v6.php';

		if ( ! file_exists( $json_file ) ) {
			return array();
		}

		// Function has already run.
		if ( null !== self::$icon_json ) {
			return self::$icon_json;
		}

		self::$icon_json = include $json_file;

		return self::$icon_json;
	}

	/**
	 * Gutenberg block category for WCFB.
	 *
	 * @param array  $categories Block categories.
	 * @param object $post Post object.
	 * @since 1.6.15
	 */
	public function register_block_category( $categories, $post ) {
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'cartflows',
					'title' => __( 'Cartflows', 'cartflows' ),
				),
			)
		);
	}

}

/**
 *  Prepare if class 'Cartflows_Init_Blocks' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Init_Blocks::get_instance();
