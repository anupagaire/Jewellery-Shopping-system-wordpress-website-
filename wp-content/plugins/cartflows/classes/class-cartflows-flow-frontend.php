<?php
/**
 * Frontend & Markup
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Flow Markup
 *
 * @since 1.0.0
 */
class Cartflows_Flow_Frontend {


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

		/* Analytics */
		add_action( 'cartflows_wp_footer', array( $this, 'footer_markup' ) );
		add_action( 'admin_bar_menu', array( $this, 'add_edit_flow_menu' ), 999 );
		add_action( 'admin_bar_menu', array( $this, 'update_edit_step_menu_link' ), 999 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_gcp_color_vars' ), 23 );
	}

	/**
	 * Add a new admin bar menu to navigate to edit the flow.
	 *
	 * @param object $admin_bar The object of admin bar.
	 * @return void
	 */
	public function add_edit_flow_menu( $admin_bar ) {

		if ( wcf()->utils->is_step_post_type() ) {
			$flow_id = wcf()->utils->get_flow_id();

			if ( ! empty( $flow_id ) ) {

				$path = Cartflows_Helper::get_global_setting( '_cartflows_store_checkout' ) === $flow_id ? 'store-checkout' : 'flows';

				$admin_bar->add_node(
					array(
						'id'    => 'edit-flow',
						'title' => '<span class="ab-icon dashicons dashicons-edit"></span>' . esc_html__( 'Edit Funnel', 'cartflows' ),
						'href'  => admin_url( 'admin.php?page=cartflows&path=' . $path . '&action=wcf-edit-flow&flow_id=' . $flow_id ),
						'meta'  => array(
							'class' => 'wcf-admin_bar-edit_flow--menu',
						),
					)
				);
			}
		}
	}

	/**
	 *  Footer markup
	 */
	public function footer_markup() {

		if ( wcf()->utils->is_step_post_type() ) {
			$flow_id = wcf()->utils->get_flow_id();
			?>
			<?php if ( $this->is_flow_testmode( $flow_id ) ) { ?>
			<div class="wcf-preview-mode">
				<span><?php esc_html_e( 'Test mode is active. It can be deactivated from the flow settings in the admin dashboard.', 'cartflows' ); ?></span>
				<?php if ( current_user_can( 'cartflows_manage_flows_steps' ) ) { ?>
					<?php
						$path           = Cartflows_Helper::get_global_setting( '_cartflows_store_checkout' ) === $flow_id ? 'store-checkout' : 'flows';
						$flow_edit_link = admin_url( 'admin.php?page=cartflows&path=' . $path . '&action=wcf-edit-flow&flow_id=' . $flow_id . '&tab=settings#sandbox' );
					?>
					<a href="<?php echo esc_url( $flow_edit_link ); ?>"><?php esc_html_e( 'Click here to disable it', 'cartflows' ); ?></a>
				<?php } ?>
			</div>
			<?php } ?>
			<?php
		}
	}

	/**
	 * Check if flow test mode is enable.
	 *
	 * @since 1.0.0
	 * @param int $flow_id flow ID.
	 *
	 * @return boolean
	 */
	public function is_flow_testmode( $flow_id = '' ) {

		if ( ! $flow_id ) {
			$flow_id = wcf()->utils->get_flow_id();
		}

		$test_mode = wcf()->options->get_flow_meta_value( $flow_id, 'wcf-testing' );

		if ( 'no' === $test_mode ) {
			return false;
		}

		return true;
	}

	/**
	 * Get steps data.
	 *
	 * @since 1.0.0
	 * @param int $flow_id flow ID.
	 *
	 * @return array
	 */
	public function get_steps( $flow_id ) {

		$steps = get_post_meta( $flow_id, 'wcf-steps', true );

		if ( ! is_array( $steps ) ) {

			$steps = array();
		}

		return $steps;
	}

	/**
	 * Check thank you page exists.
	 *
	 * @since 1.0.0
	 * @param array $order order data.
	 *
	 * @return bool
	 */
	public function is_thankyou_page_exists( $order ) {

		$thankyou_step_exist = false;

		$flow_id = wcf()->utils->get_flow_id_from_order( $order );

		if ( $flow_id ) {

			$step_id = wcf()->utils->get_checkout_id_from_order( $order );

			// Get control step and flow steps.
			$wcf_step_obj = wcf_get_step( $step_id );
			$flow_steps   = $wcf_step_obj->get_flow_steps();
			$control_step = $wcf_step_obj->get_control_step();

			if ( is_array( $flow_steps ) ) {

				$current_step_found = false;

				foreach ( $flow_steps as $index => $data ) {

					if ( $current_step_found ) {

						if ( 'thankyou' === $data['type'] ) {

							$thankyou_step_exist = true;
							break;
						}
					} else {

						if ( intval( $data['id'] ) === $control_step ) {

							$current_step_found = true;
						}
					}
				}
			}
		}

		return $thankyou_step_exist;
	}

	/**
	 * Check thank you page exists.
	 *
	 * @since 1.0.0
	 * @param array $order order data.
	 *
	 * @return bool
	 */
	public function get_thankyou_page_id( $order ) {

		$thankyou_step_id = false;

		$flow_id = wcf()->utils->get_flow_id_from_order( $order );

		if ( $flow_id ) {

			$step_id = wcf()->utils->get_checkout_id_from_order( $order );

			// Get control step and flow steps.
			$wcf_step_obj = wcf_get_step( $step_id );
			$flow_steps   = $wcf_step_obj->get_flow_steps();
			$control_step = $wcf_step_obj->get_control_step();

			if ( is_array( $flow_steps ) ) {

				$current_step_found = false;

				foreach ( $flow_steps as $index => $data ) {

					if ( $current_step_found ) {

						if ( 'thankyou' === $data['type'] ) {

							$thankyou_step_id = intval( $data['id'] );
							break;
						}
					} else {

						if ( intval( $data['id'] ) === $control_step ) {

							$current_step_found = true;
						}
					}
				}
			}
		}

		return $thankyou_step_id;
	}

	/**
	 * Update the CartFlows steps link as per the latest UI.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar object.
	 * @since x.x.x
	 */
	public function update_edit_step_menu_link( $wp_admin_bar ) {

		if ( wcf()->utils->is_step_post_type() ) {

			global $post;

			if ( ! empty( $post ) ) {

				$edit_node = $wp_admin_bar->get_node( 'edit' );

				if ( $edit_node ) {

					// Show the edit node only if the page builder option is set to other and gutenberg, else remove/hide the node.
					if ( in_array( Cartflows_Helper::get_common_setting( 'default_page_builder' ), array( 'other', 'gutenberg' ) ) ) {
						$edit_node->title = esc_html__( 'Edit Design', 'cartflows' );
						$edit_node->href  = Cartflows_Helper::get_page_builder_edit_link( $post->ID );
						$wp_admin_bar->add_node( $edit_node );
					} else {
						$wp_admin_bar->remove_node( 'edit' );
					}
				}
			}
		}
	}

	/**
	 * Enqueue the Global Color Pallet CSS vars to the page to use in the page builder settings.
	 *
	 * Note: Currently the GCP support is added for Elementor and Block Builder.
	 *
	 * @param string $css_dependency the CSS file handle as a dependency to add the inline css.
	 * @since x.x.x
	 * @return void
	 */
	public function enqueue_gcp_color_vars( $css_dependency = '' ) {

		if ( wcf()->utils->is_step_post_type() ) {

			$flow_id = wcf()->utils->get_flow_id();

			// Return if no flow ID is found.
			if ( empty( $flow_id ) ) {
				return;
			}

			if ( ! Cartflows_Helper::is_gcp_styling_enabled( (int) $flow_id ) ) {
				return;
			}

			$gcp_vars       = Cartflows_Helper::generate_gcp_css_style( (int) $flow_id );
			$css_dependency = ! empty( $css_dependency ) ? $css_dependency : 'wcf-frontend-global';

			// Don't print the inline CSS style if no style is generated.
			if ( ! empty( $gcp_vars ) ) {
				$output = ':root { ' . $gcp_vars . ' }';
				wp_add_inline_style( $css_dependency, $output );
			}
		}
	}

}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Flow_Frontend::get_instance();
