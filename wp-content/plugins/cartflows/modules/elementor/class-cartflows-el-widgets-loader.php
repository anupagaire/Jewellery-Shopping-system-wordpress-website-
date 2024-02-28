<?php
/**
 * Widgets loader for Cartflows.
 *
 * @package Cartflows
 * */

defined( 'ABSPATH' ) || exit;

/**
 * Set up Widgets Loader class
 */
class Cartflows_Widgets_Loader {

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
	 * Setup actions and filters.
	 *
	 * @since 1.6.15
	 */
	private function __construct() {

		// Register category.
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_widget_category' ) );

		// Remove this in CartFlows major update and keep only `register`.
		$action_name = ( true === version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' ) ) ? 'register' : 'widgets_registered';

		// Register widgets.
		add_action( 'elementor/widgets/' . $action_name, array( $this, 'register_widgets' ) );

		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'admin_enqueue_styles' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'override_flow_level_global_colors_elementor' ), 25 );
	}

	/**
	 * Override the global colors of every steps inside the funnel for elementor
	 * with the global colors selected in the funnel's setting by the help of elementor's global color.
	 *
	 * Note: Currently the GCP support is added for Elementor and Block Builder.
	 *
	 * @since x.x.x
	 * @return void
	 */
	public function override_flow_level_global_colors_elementor() {

		if ( wcf()->utils->is_step_post_type() ) {

			$flow_id = wcf()->utils->get_flow_id();

			// Return if no flow ID is found.
			if ( empty( $flow_id ) ) {
				return;
			}

			if ( ! Cartflows_Helper::is_gcp_styling_enabled( (int) $flow_id ) ) {
				return;
			}

			$style        = '';
			$page_builder = Cartflows_Helper::get_common_setting( 'default_page_builder' );

			// Override the CSS variables of selected page builder to override the page colors.
			if ( 'elementor' === $page_builder ) {

				$style .= '--e-global-color-primary: var( --wcf-gcp-primary-color ) !important;';

				$style .= '--e-global-color-secondary: var( --wcf-gcp-secondary-color ) !important;';

				$style .= '--e-global-color-accent: var( --wcf-gcp-accent-color ) !important;';

				$style .= '--e-global-color-text: var( --wcf-gcp-text-color ) !important;';
				// $style .= '--e-global-typography-primary-font-family: ' . $gcp_primary_font_family . ' !important; --e-global-typography-text-font-family: ' . $gcp_primary_font_family . ' !important;';
			}

			// Don't print the inline CSS style if the no style is generated.
			if ( ! empty( $style ) ) {
				$output = 'body.cartflows_step-template { ' . $style . ' }';
				wp_add_inline_style( 'wcf-frontend-global', $output );
			}
		}

	}

	/**
	 * Enqueue admin scripts
	 *
	 * @since 1.6.15
	 * @param string $hook Current page hook.
	 * @access public
	 */
	public function admin_enqueue_styles( $hook ) {

		// Register the icons styles.
		wp_register_style(
			'cartflows-elementor-icons-style',
			CARTFLOWS_URL . 'assets/elementor-assets/css/style.css',
			array(),
			CARTFLOWS_VER
		);

		wp_enqueue_style( 'cartflows-elementor-icons-style' );
	}

	/**
	 * Returns Script array.
	 *
	 * @return array()
	 * @since 1.6.15
	 */
	public static function get_widget_list() {

		$widget_list = array(
			'checkout-form',
			'order-details-form',
			'next-step-button',
			'optin-form',
		);

		return $widget_list;
	}


	/**
	 * Include Widgets files
	 *
	 * Load widgets files
	 *
	 * @since 1.6.15
	 * @access public
	 */
	public function include_widgets_files() {

		/* Required files */
		require_once CARTFLOWS_DIR . 'modules/elementor/classes/class-cartflows-elementor-editor.php';

		$widget_list = $this->get_widget_list();

		if ( ! empty( $widget_list ) ) {
			foreach ( $widget_list as $handle => $data ) {
				$file_path = CARTFLOWS_DIR . 'modules/elementor/widgets/class-cartflows-el-' . $data . '.php';
				if ( file_exists( $file_path ) ) {
					require_once $file_path;
				}
			}
		}

		// Emqueue the widgets style.
		wp_enqueue_style( 'cartflows-elementor-style', CARTFLOWS_URL . 'modules/elementor/widgets-css/frontend.css', array(), CARTFLOWS_VER );

	}

	/**
	 * Register Category
	 *
	 * @since 1.6.15
	 * @param object $this_cat class.
	 */
	public function register_widget_category( $this_cat ) {
		$category = __( 'Cartflows', 'cartflows' );

		$this_cat->add_category(
			'cartflows-widgets',
			array(
				'title' => $category,
				'icon'  => 'eicon-font',
			)
		);

		return $this_cat;
	}

	/**
	 * Register Widgets
	 *
	 * Register new Elementor widgets.
	 *
	 * @since 1.6.15
	 * @access public
	 */
	public function register_widgets() {

		global $post;

		if ( ! isset( $post ) ) {
			return;
		}

		$post_type = $post->post_type;

		$step_type = get_post_meta( $post->ID, 'wcf-step-type', true );

		if ( 'cartflows_step' === $post_type && class_exists( '\Elementor\Plugin' ) ) {

			$widget_manager = \Elementor\Plugin::$instance->widgets_manager;

			$widget_list = $this->get_widget_list();

			// Its is now safe to include Widgets files.
			$this->include_widgets_files();

			$fn_name = ( true === version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' ) ) ? 'register' : 'register_widget_type';

			foreach ( $widget_list as $widget ) {

				$widget_name = str_replace( '-', ' ', $widget );

				$class_name = 'Cartflows_' . str_replace( ' ', '_', ucwords( $widget_name ) );

				if ( $class_name::is_enable( $step_type ) ) {
					$widget_manager->{ $fn_name }( new $class_name() );

				}
			}
		}
	}

}

/**
 * Initiate the class.
 */
Cartflows_Widgets_Loader::get_instance();
