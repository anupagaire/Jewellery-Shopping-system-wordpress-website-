<?php
/**
 * CartFlows Admin Menu.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\AdminCore\Inc;

use CartflowsAdmin\AdminCore\Inc\AdminHelper;
use CartflowsAdmin\AdminCore\Inc\LogStatus;


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_Menu.
 */
class AdminMenu {

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * For Gutenberg
	 *
	 * @var $is_gutenberg_editor_active
	 */
	private $is_gutenberg_editor_active = false;

	/**
	 * Initiator
	 *
	 * @since 1.0.0
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Instance
	 *
	 * @access private
	 * @var string Class object.
	 * @since 1.0.0
	 */
	private $menu_slug;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->menu_slug = 'cartflows';

		$this->initialize_hooks();
	}

	/**
	 * Init Hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function initialize_hooks() {

		add_action( 'admin_menu', array( $this, 'setup_menu' ) );
		add_action( 'admin_init', array( $this, 'settings_admin_scripts' ) );
		add_action( 'admin_init', array( $this, 'add_capabilities_to_admin' ) );

		/* Flow content view */
		add_action( 'cartflows_render_admin_page_content', array( $this, 'render_content' ), 10, 2 );

		add_action( 'edit_form_after_title', array( $this, 'new_admin_flow_setting_redirection' ), 10, 1 );

		/* To check the status of gutenberg */
		add_action( 'enqueue_block_editor_assets', array( $this, 'set_block_editor_status' ) );
		add_action( 'admin_footer', array( $this, 'back_to_new_step_ui_for_gutenberg' ), 999 );

		add_action( 'admin_notices', array( $this, 'back_to_new_step_ui_for_classic_editor' ) );
	}

	/**
	 * Display admin notices.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function back_to_new_step_ui_for_classic_editor() {

		if ( CARTFLOWS_STEP_POST_TYPE !== get_post_type() ) {
			return;
		}

		$flow_id = get_post_meta( get_the_id(), 'wcf-flow-id', true );
		$step_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( $flow_id && $step_id ) {
			$path = \Cartflows_Helper::get_global_setting( '_cartflows_store_checkout' ) === $flow_id ? 'store-checkout' : 'flows';

			$flow_redirect_url = esc_url( admin_url() . 'admin.php?page=' . $this->menu_slug . '&path=	' . $path . '&action=wcf-edit-flow&flow_id=' . $flow_id ); // phpcs:igmore Generic.Strings.UnnecessaryStringConcat.Found.
			?>
			<div class="wcf-notice-back-edit-step">
				<p>
					<a href="<?php echo esc_url( $flow_redirect_url ); ?>" class="button button-primary button-hero wcf-header-back-button" style="text-decoration: none;">
						<?php esc_html_e( 'Edit Funnel', 'cartflows' ); ?>
					</a>
				</p>
			</div>
			<?php
		}

	}

	/**
	 * Set status true for gutenberg.
	 *
	 * @return void
	 */
	public function set_block_editor_status() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		// Set gutenberg status here.
		$this->is_gutenberg_editor_active = true;
	}

	/**
	 * Back to flow button gutenberg template
	 *
	 * @return void
	 */
	public function back_to_new_step_ui_for_gutenberg() {

		// Exit if block editor is not enabled.
		if ( ! $this->is_gutenberg_editor_active ) {
			return;
		}

		wp_enqueue_script( 'cartflows-admin-common-script', CARTFLOWS_ADMIN_CORE_URL . 'assets/js/common.js', array( 'jquery' ), CARTFLOWS_VER, false );

		if ( CARTFLOWS_STEP_POST_TYPE !== get_post_type() ) {
			return;
		}

		$flow_id = get_post_meta( get_the_id(), 'wcf-flow-id', true );
		$step_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( $flow_id && $step_id ) {
			$path              = \Cartflows_Helper::get_global_setting( '_cartflows_store_checkout' ) === $flow_id ? 'store-checkout' : 'flows';
			$flow_redirect_url = esc_url( admin_url() . 'admin.php?page=' . $this->menu_slug . '&path=' . $path . '&action=wcf-edit-flow&flow_id=' . $flow_id );
			?>
		<script id="wcf-gutenberg-back-step-button" type="text/html">
			<div class="wcf-notice-back-edit-step gutenberg-button" style="display: flex; align-content: center; margin: 0 5px 0 0;flex-basis: 100%;">
				<a href="<?php echo esc_url( $flow_redirect_url ); ?>" class="button button-primary button-large wcf-header-back-button" style="text-decoration: none; font-size: 13px; line-height: 2.5;"><?php esc_html_e( 'Edit Funnel', 'cartflows' ); ?></a>
			</div>
		</script>
		<script>
			window.addEventListener('load', function () {
				( function( window, wp ){
					var link = document.querySelector('a.components-button.edit-post-fullscreen-mode-close');
					if (link) {
						link.setAttribute('href', "<?php echo htmlspecialchars_decode( esc_url( $flow_redirect_url ) );//phpcs:ignore ?>")
					}

				} )( window, wp )
			});
		</script>
			<?php
		}
	}


	/**
	 * Back to flow button
	 *
	 * @param array $meta meta data.
	 *
	 * @return void
	 */
	public function new_admin_flow_setting_redirection( $meta ) {

		$flow_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$post_type = get_post_type();

		if ( CARTFLOWS_FLOW_POST_TYPE === $post_type && 0 !== $flow_id ) {

			$redirect_url = esc_url( admin_url() . 'admin.php?page=' . $this->menu_slug . '&path=flows&action=wcf-edit-flow&flow_id=' . $flow_id );
			$btn_markup   = '<div class="wcf-flow-editing-action" style="padding: 50px;text-align: center;">';
			$btn_markup  .= '<a class="button button-primary button-hero" href="' . $redirect_url . '">' . __( 'Go to Funnel Editing', 'cartflows' ) . '</a>';
			$btn_markup  .= '</div>';

			echo wp_kses_post( $btn_markup );
		}
	}

	/**
	 *  Initialize after Cartflows pro get loaded.
	 */
	public function settings_admin_scripts() {

		// Enqueue admin scripts.
		// Ignoring nonce verification as using SuperGlobal variables on WordPress hooks.
		if ( isset( $_GET['page'] ) && ( 'cartflows' === sanitize_text_field( $_GET['page'] ) || false !== strpos( sanitize_text_field( $_GET['page'] ), 'cartflows_' ) ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			add_action( 'admin_enqueue_scripts', array( $this, 'styles_scripts' ) );

			add_filter( 'admin_footer_text', array( $this, 'add_footer_link' ), 99 );
		}
	}

	/**
	 * Add submenu to admin menu.
	 *
	 * @since 1.0.0
	 */
	public function setup_menu() {
		global $submenu;

		if ( current_user_can( 'cartflows_manage_flows_steps' ) ) {
			$parent_slug   = $this->menu_slug;
			$capability    = 'cartflows_manage_flows_steps';
			$menu_priority = apply_filters( 'cartflows_menu_priority', ! defined( 'ASTRA_THEME_VERSION' ) ? 40 : 3 );

			add_menu_page(
				'CartFlows',
				'CartFlows',
				$capability,
				$parent_slug,
				array( $this, 'render' ),
				'data:image/svg+xml;base64,' . base64_encode( file_get_contents( CARTFLOWS_DIR . 'assets/images/cartflows-icon.svg' ) ),
				$menu_priority
			);

			// Add settings menu.
			add_submenu_page(
				$parent_slug,
				__( 'Funnels', 'cartflows' ),
				__( 'Funnels', 'cartflows' ),
				$capability,
				'admin.php?page=' . $this->menu_slug . '&path=flows'
			);

			$global_checkout_id    = absint( \Cartflows_Helper::get_global_setting( '_cartflows_store_checkout' ) );
			$global_checkout_param = ( empty( $global_checkout_id ) ) ? 'path=store-checkout' : 'path=store-checkout&action=wcf-edit-flow&flow_id=' . $global_checkout_id;

			add_submenu_page(
				$parent_slug,
				__( 'Store Checkout', 'cartflows' ),
				__( 'Store Checkout', 'cartflows' ),
				$capability,
				'admin.php?page=' . $this->menu_slug . '&' . $global_checkout_param
			);

			if ( current_user_can( 'cartflows_manage_settings' ) ) {

				if ( ! get_option( 'wcf_setup_page_skipped', false ) && '1' === get_option( 'wcf_setup_skipped', false ) && $this->maybe_skip_setup_menu() ) {

					add_submenu_page(
						$parent_slug,
						__( 'Setup', 'cartflows' ),
						__( 'Setup', 'cartflows' ),
						$capability,
						'admin.php?page=' . $this->menu_slug . '&path=setup'
					);
				}
			}

			// Rename to Home menu.
			// Disable phpcs since we need to override submenu name.
			$submenu[ $parent_slug ][0][0] = __( 'Dashboard', 'cartflows' ); //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
	}

	/**
	 * Add custom capabilities to Admin user.
	 */
	public function maybe_skip_setup_menu() {

		$is_wcar_active = is_plugin_active( 'woo-cart-abandonment-recovery/woo-cart-abandonment-recovery.php' );

		if ( ! $is_wcar_active ) {
			return true;
		}

		$cpsw_connection_status = 'success' === get_option( 'cpsw_test_con_status', false ) || 'success' === get_option( 'cpsw_con_status', false );

		if ( ! $cpsw_connection_status ) {
			return true;
		}

		$is_set_report_email_ids = get_option( 'cartflows_stats_report_email_ids', false );

		if ( ! $is_set_report_email_ids ) {
			return true;
		}

		$is_store_checkout = \Cartflows_Helper::get_common_setting( 'global_checkout' );

		if ( empty( $is_store_checkout ) ) {
			return true;
		}

		update_option( 'wcf_setup_page_skipped', true );
		return false;

	}

	/**
	 * Add custom capabilities to Admin user.
	 */
	public function add_capabilities_to_admin() {

		global $wp_roles;
		// Add custom capabilities to admin by default.
		$capabilities = array(
			'cartflows_manage_settings',
			'cartflows_manage_flows_steps',
		);

		foreach ( $capabilities as $cap ) {
			$wp_roles->add_cap( 'administrator', $cap );
		}
	}

	/**
	 * Renders the admin settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render() {
		$menu_page_slug = ( isset( $_GET['page'] ) ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : CARTFLOWS_SETTINGS; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page_action    = '';

		if ( isset( $_GET['action'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$page_action = sanitize_text_field( wp_unslash( $_GET['action'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$page_action = str_replace( '_', '-', $page_action );
		}

		include_once CARTFLOWS_ADMIN_CORE_DIR . 'views/admin-base.php';
	}

	/**
	 * Renders the admin settings content.
	 *
	 * @since 1.0.0
	 * @param sting $menu_page_slug current page name.
	 * @param sting $page_action current page action.
	 *
	 * @return void
	 */
	public function render_content( $menu_page_slug, $page_action ) {

		if ( 'cartflows' === $menu_page_slug ) {
			if ( $this->is_current_page( 'cartflows' ) ) {
				include_once CARTFLOWS_ADMIN_CORE_DIR . 'views/settings-app.php';
			} elseif ( $this->is_current_page( 'cartflows', array( 'wcf-edit-flow' ) ) ) {
				include_once CARTFLOWS_ADMIN_CORE_DIR . 'views/editor-app.php';
			} elseif ( $this->is_current_page( 'cartflows', array( 'wcf-log' ) ) ) {
				include_once CARTFLOWS_ADMIN_CORE_DIR . 'views/debugger.php';
			} elseif ( $this->is_current_page( 'cartflows', array( 'wcf-license' ) ) && _is_cartflows_pro() ) {
				do_action( 'cartflows_admin_log', 'wcf-license' );
			} else {
				include_once CARTFLOWS_ADMIN_CORE_DIR . 'views/404-error.php';
			}
		}
	}

	/**
	 * Enqueues the needed CSS/JS for the builder's admin settings page.
	 *
	 * @since 1.0.0
	 */
	public function styles_scripts() {

		$admin_slug = 'cartflows-admin';

		// Styles.
		wp_enqueue_style( $admin_slug . '-common', CARTFLOWS_ADMIN_CORE_URL . 'assets/css/common.css', array(), CARTFLOWS_VER );
		wp_style_add_data( $admin_slug . '-common', 'rtl', 'replace' );

		wp_enqueue_script( $admin_slug . '-common-script', CARTFLOWS_ADMIN_CORE_URL . 'assets/js/common.js', array( 'jquery' ), CARTFLOWS_VER, false );

		$current_flow_steps = array();
		$flow_id            = isset( $_GET['flow_id'] ) ? intval( $_GET['flow_id'] ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $flow_id ) {
			$current_flow_steps = AdminHelper::get_flow_meta_options( $flow_id );
		}

		$product_src = esc_url_raw(
			add_query_arg(
				array(
					'post_type'      => 'product',
					'wcf-woo-iframe' => 'true',
				),
				admin_url( 'post-new.php' )
			)
		);

		$page_builder = \Cartflows_Helper::get_common_setting( 'default_page_builder' );

		$page_builder      = \Cartflows_Helper::get_common_setting( 'default_page_builder' );
		$page_builder_name = \Cartflows_Helper::get_page_builder_name( $page_builder );

		$global_checkout_id = \Cartflows_Helper::get_global_setting( '_cartflows_store_checkout' );

		$flow_action = 'wcf-edit-flow';
		$step_action = 'wcf-edit-step';

		$flows_and_steps = \Cartflows_Helper::get_instance()->get_flows_and_steps();

		$cf_pro_status        = $this->get_cartflows_pro_plugin_status();
		$cf_pro_type_inactive = '';
		if ( 'inactive' === $cf_pro_status ) {

			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$plugin               = get_plugins();
			$cf_pro_type_inactive = $plugin['cartflows-pro/cartflows-pro.php']['Name'];
		}

		$order_url = '#';

		if ( wcf()->is_woo_active && class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
			$order_url = \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ? admin_url( 'admin.php?page=wc-orders' ) : admin_url( 'edit.php?post_type=shop_order' );
		}

		$localize = apply_filters(
			'cartflows_admin_localized_vars',
			array(
				'current_user'                      => ! empty( wp_get_current_user()->user_firstname ) ? wp_get_current_user()->user_firstname : wp_get_current_user()->display_name,
				'cf_pro_status'                     => $this->get_cartflows_pro_plugin_status(),
				'cf_pro_type'                       => 'free',
				'cf_pro_type_inactive'              => $cf_pro_type_inactive,
				'woocommerce_status'                => $this->get_plugin_status( 'woocommerce/woocommerce.php' ),
				'default_page_builder'              => $page_builder,
				'required_plugins'                  => \Cartflows_Helper::get_plugins_groupby_page_builders(),
				'required_plugins_data'             => $this->get_required_plugins_data(),
				'is_any_required_plugins_missing'   => $this->get_any_required_plugins_status(),
				'admin_base_slug'                   => $this->menu_slug,
				'admin_base_url'                    => admin_url(),
				'title_length'                      => apply_filters(
					'cartflows_flows_steps_title_length',
					array(
						'max'            => 50,
						'display_length' => 40,
					)
				),
				'plugin_dir'                        => CARTFLOWS_URL,
				'admin_url'                         => admin_url( 'admin.php' ),
				'ajax_url'                          => admin_url( 'admin-ajax.php' ),
				'is_rtl'                            => is_rtl(),
				'home_slug'                         => $this->menu_slug,
				'is_pro'                            => _is_cartflows_pro(),
				'page_builder'                      => $page_builder,
				'page_builder_name'                 => $page_builder_name,
				'global_checkout'                   => \Cartflows_Helper::get_common_setting( 'global_checkout' ),
				'flows_count'                       => 1, // Removing the flow count condition.
				'currentFlowSteps'                  => $current_flow_steps,
				// Delete this code after 3 major update. Added in 1.10.4.
				'license_status'                    => \_is_cartflows_pro_license_activated(),
				'license_popup_url'                 => admin_url( 'plugins.php?cartflows-license-popup' ),
				'cf_domain_url'                     => CARTFLOWS_DOMAIN_URL,
				'logo_url'                          => esc_url_raw( CARTFLOWS_URL . 'assets/images/cartflows-logo.svg' ),
				'create_product_src'                => $product_src,
				'cf_font_family'                    => AdminHelper::get_font_family(),
				'flows_and_steps'                   => ! empty( $flows_and_steps ) ? $flows_and_steps : '',
				'store_checkout_flows_and_steps'    => \Cartflows_Helper::get_instance()->get_flows_and_steps( '', 'store-checkout' ),
				'woo_currency'                      => function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '',
				'template_library_url'              => wcf()->get_site_url(),
				'image_placeholder'                 => esc_url_raw( CARTFLOWS_URL . 'admin-core/assets/images/image-placeholder.png' ),
				'google_fonts'                      => \CartFlows_Font_Families::get_google_fonts(),
				'system_fonts'                      => \CartFlows_Font_Families::get_system_fonts(),
				'font_weights'                      => array(
					'100' => __( 'Thin 100', 'cartflows' ),
					'200' => __( 'Extra-Light 200', 'cartflows' ),
					'300' => __( 'Light 300', 'cartflows' ),
					'400' => __( 'Normal 400', 'cartflows' ),
					'500' => __( 'Medium 500', 'cartflows' ),
					'600' => __( 'Semi-Bold 600', 'cartflows' ),
					'700' => __( 'Bold 700', 'cartflows' ),
					'800' => __( 'Extra-Bold 800', 'cartflows' ),
					'900' => __( 'Ultra-Bold 900', 'cartflows' ),
				),
				'global_checkout_id'                => $global_checkout_id ? absint( $global_checkout_id ) : '',
				'flow_action'                       => $flow_action,
				'step_action'                       => $step_action,
				'old_global_checkout'               => get_option( '_cartflows_old_global_checkout', false ),
				'cpsw_status'                       => $this->get_plugin_status( 'checkout-plugins-stripe-woo/checkout-plugins-stripe-woo.php' ),
				'cppw_status'                       => $this->get_plugin_status( 'checkout-paypal-woo/checkout-paypal-woo.php' ),
				'ca_status'                         => $this->get_plugin_status( 'woo-cart-abandonment-recovery/woo-cart-abandonment-recovery.php' ),
				'cpsw_connection_status'            => 'success' === get_option( 'cpsw_test_con_status', false ) || 'success' === get_option( 'cpsw_con_status', false ),
				'current_user_can_manage_cartflows' => current_user_can( 'cartflows_manage_settings' ),
				'is_set_report_email_ids'           => get_option( 'cartflows_stats_report_email_ids', false ),
				'cf_docs_data'                      => get_option( 'cartflows_docs_data', false ),
				'woo_order_url'                     => $order_url,
				'integrations'                      => $this->get_recommendation_integrations(),
			)
		);

		if ( $this->is_current_page( $this->menu_slug ) ) {
			$this->settings_app_scripts( $localize );
		} elseif ( $this->is_current_page( 'cartflows', array( 'wcf-edit-flow' ) ) ) {
			wp_enqueue_media();
			wp_enqueue_editor(); // Require for Order Bump Desc WP Editor/ tinymce field.
			$this->editor_app_scripts( $localize );
		}
	}

	/**
	 * Get required plugin status.
	 */
	public function get_required_plugins_data() {

		$missing_plugins_data = array();

		$page_builder_plugins = \Cartflows_Helper::get_plugins_groupby_page_builders();

		foreach ( $page_builder_plugins as $slug => $data ) {

			$missing_plugins_data[ $slug ] = 'no';
			$current_page_builder_data     = $page_builder_plugins[ $slug ];

			foreach ( $current_page_builder_data['plugins'] as $plugin ) {
				if ( 'activate' === $plugin['status'] || 'install' === $plugin['status'] ) {
					$missing_plugins_data[ $slug ] = 'yes';
				}
			}

			// Divi.
			if ( 'divi' === $slug ) {
				if ( 'activate' === $current_page_builder_data['theme-status'] ) {
					$missing_plugins_data[ $slug ] = 'yes';
				}
			}
		}

		return $missing_plugins_data;
	}
	/**
	 * Get required plugin status
	 */
	public function get_any_required_plugins_status() {

		$default_page_builder = \Cartflows_Helper::get_common_setting( 'default_page_builder' );
		$any_inactive         = 'no';

		$page_builder_plugins = \Cartflows_Helper::get_plugins_groupby_page_builders();

		if ( isset( $page_builder_plugins[ $default_page_builder ] ) ) {

			$current_page_builder_data = $page_builder_plugins[ $default_page_builder ];

			foreach ( $current_page_builder_data['plugins'] as $plugin ) {
				if ( 'activate' === $plugin['status'] || 'install' === $plugin['status'] ) {
					$any_inactive = 'yes';
				}
			}

			// Divi.
			if ( 'divi' === $default_page_builder ) {
				if ( 'activate' === $current_page_builder_data['theme-status'] ) {
					$any_inactive = 'yes';
				}
			}
		}

		return $any_inactive;
	}

	/**
	 * Get CartFlows plugin status
	 *
	 * @since 1.11.8
	 *
	 * @return string
	 */
	public function get_cartflows_pro_plugin_status() {

		$status = $this->get_plugin_status( 'cartflows-pro/cartflows-pro.php' );

		if ( 'active' === $status && ! _is_cartflows_pro() ) {

			$status = 'inactive';
		}

		return $status;
	}

	/**
	 * Get plugin status
	 *
	 * @since 1.1.4
	 *
	 * @param  string $plugin_init_file Plguin init file.
	 * @return mixed
	 */
	public function get_plugin_status( $plugin_init_file ) {

		$installed_plugins = get_plugins();

		if ( ! isset( $installed_plugins[ $plugin_init_file ] ) ) {
			return 'not-installed';
		} elseif ( is_plugin_active( $plugin_init_file ) ) {
			return 'active';
		} else {
			return 'inactive';
		}
	}

	/**
	 * Settings app scripts
	 *
	 * @param array $localize Variable names.
	 */
	public function settings_app_scripts( $localize ) {
		$handle            = 'wcf-react-settings';
		$build_path        = CARTFLOWS_ADMIN_CORE_DIR . 'assets/build/';
		$build_url         = CARTFLOWS_ADMIN_CORE_URL . 'assets/build/';
		$script_asset_path = $build_path . 'settings-app.asset.php';
		$script_info       = file_exists( $script_asset_path )
			? include $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => CARTFLOWS_VER,
			);

		$script_dep = array_merge( $script_info['dependencies'], array( 'updates' ) );

		wp_register_script(
			$handle,
			$build_url . 'settings-app.js',
			$script_dep,
			$script_info['version'],
			true
		);

		wp_register_style(
			$handle,
			$build_url . 'settings-app.css',
			array(),
			CARTFLOWS_VER
		);

		wp_enqueue_script( $handle );

		wp_set_script_translations( $handle, 'cartflows' );

		wp_enqueue_style( $handle );
		wp_style_add_data( $handle, 'rtl', 'replace' );

		$localize['is_flows_limit'] = false; // Removed the flow count condition.

		$localize = $this->debugger_scripts( $localize );

		wp_localize_script( $handle, 'cartflows_admin', $localize );

	}

	/**
	 * Settings app scripts
	 *
	 * @param array $localize Variable names.
	 */
	public function editor_app_scripts( $localize ) {
		$handle            = 'wcf-editor-app';
		$build_path        = CARTFLOWS_ADMIN_CORE_DIR . 'assets/build/';
		$build_url         = CARTFLOWS_ADMIN_CORE_URL . 'assets/build/';
		$script_asset_path = $build_path . 'editor-app.asset.php';
		$script_info       = file_exists( $script_asset_path )
			? include $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => CARTFLOWS_VER,
			);

		wp_register_script(
			$handle,
			$build_url . 'editor-app.js',
			$script_info['dependencies'],
			$script_info['version'],
			true
		);

		wp_register_style(
			$handle,
			$build_url . 'editor-app.css',
			array(),
			CARTFLOWS_VER
		);

		wp_enqueue_script( $handle );

		wp_set_script_translations( $handle, 'cartflows' );

		wp_enqueue_style( $handle );
		wp_style_add_data( $handle, 'rtl', 'replace' );

		$localize['flow_id'] = isset( $_GET['flow_id'] ) ? intval( $_GET['flow_id'] ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$step_id = isset( $_GET['step_id'] ) ? intval( $_GET['step_id'] ) : false; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( $step_id ) {

			$meta_options        = AdminHelper::get_step_meta_options( $step_id );
			$localize['options'] = $meta_options['options'];
		}

		wp_localize_script( $handle, 'cartflows_admin', $localize );
	}

	/**
	 * Debugger scripts.
	 *
	 * @param array $localize Variable names.
	 */
	public function debugger_scripts( $localize ) {

		$logs            = LogStatus::get_instance()->get_log_files();
		$viewed_log      = '';
		$viewed_log_file = '';

		// Calling this function on CartFlows action hook. Hence ignoring nonce.
		if ( ! empty( $_REQUEST['log_file'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$filename = sanitize_text_field( wp_unslash( $_REQUEST['log_file'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( isset( $logs[ $filename ] ) ) {
				$viewed_log      = $filename;
				$viewed_log_file = $viewed_log . '.log';
			}
		} elseif ( ! empty( $logs ) ) {
			$viewed_log      = current( $logs ) ? pathinfo( current( $logs ), PATHINFO_FILENAME ) : '';
			$viewed_log_file = $viewed_log . '.log';
		}

		if ( ! empty( $viewed_log_file ) ) {
			$file_content             = file_get_contents( CARTFLOWS_LOG_DIR . $viewed_log_file );
			$localize['file_content'] = $file_content;
			$localize['log_key']      = $viewed_log;

		}
		$localize['logs'] = $logs;
		return $localize;
	}


	/**
	 * CHeck if it is current page by parameters
	 *
	 * @param string $page_slug Menu name.
	 * @param string $action Menu name.
	 *
	 * @return  string page url
	 */
	public function is_current_page( $page_slug = '', $action = '' ) {

		$page_matched = false;

		if ( empty( $page_slug ) ) {
			return false;
		}

		$current_page_slug = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_action    = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! is_array( $action ) ) {
			$action = explode( ' ', $action );
		}

		if ( $page_slug === $current_page_slug && in_array( $current_action, $action, true ) ) {
			$page_matched = true;
		}

		return $page_matched;
	}

	/**
	 *  Add footer link.
	 */
	public function add_footer_link() {

		$logs_page_url = add_query_arg(
			array(
				'page' => CARTFLOWS_SLUG,
				'path' => 'wcf-log',
			),
			admin_url( '/admin.php' )
		);

		return '<span id="footer-thankyou"> Thank you for using <a href="https://cartflows.com/?utm_source=dashboard&utm_medium=free-cartflows&utm_campaign=footer-link">CartFlows</a></span> | <a href="' . $logs_page_url . '">Logs</a>';
	}

	/**
	 * Get CartFlows recommended integrations.
	 *
	 * @return array
	 */
	public function get_recommendation_integrations() {

		return apply_filters(
			'cartflows_admin_integrated_plugins',
			array(
				array(
					'title'       => __( 'WooCommerce', 'cartflows' ),
					'subtitle'    => __( 'WooCommerce is a customizable, open-source ecommerce platform built on WordPress.', 'cartflows' ),
					'isPro'       => false,
					'status'      => $this->get_plugin_status( 'woocommerce/woocommerce.php' ),
					'slug'        => 'woocommerce',
					'path'        => 'woocommerce/woocommerce.php',
					'redirection' => admin_url( 'admin.php?page=wc-admin' ),
					'logoPath'    => array(
						'icon_path' => CARTFLOWS_ADMIN_CORE_URL . 'assets/images/plugins/woo.svg',
					),
				),
				array(
					'title'       => __( 'Cart Abandonment', 'cartflows' ),
					'subtitle'    => __( 'Recover abandonded carts with ease in less than 10 minutes.', 'cartflows' ),
					'isPro'       => false,
					'status'      => $this->get_plugin_status( 'woo-cart-abandonment-recovery/woo-cart-abandonment-recovery.php' ),
					'slug'        => 'woo-cart-abandonment-recovery',
					'path'        => 'woo-cart-abandonment-recovery/woo-cart-abandonment-recovery.php',
					'redirection' => admin_url( 'admin.php?page=woo-cart-abandonment-recovery' ),
					'logoPath'    => array(
						'icon_path' => CARTFLOWS_ADMIN_CORE_URL . 'assets/images/plugins/wcar.svg',
					),
				),
				array(
					'title'       => __( 'Spectra', 'cartflows' ),
					'subtitle'    => __( 'Power-up the Gutenberg editor with advanced and powerful blocks.', 'cartflows' ),
					'isPro'       => false,
					'status'      => $this->get_plugin_status( 'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php' ),
					'slug'        => 'ultimate-addons-for-gutenberg',
					'path'        => 'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php',
					'redirection' => admin_url( 'options-general.php?page=spectra' ),
					'logoPath'    => array(
						'icon_path' => CARTFLOWS_ADMIN_CORE_URL . 'assets/images/plugins/spectra.svg',
					),
				),
				array(
					'title'       => __( 'Stripe Payments For WooCommerce', 'cartflows' ),
					'subtitle'    => __( 'Accept credit card payments in your store with Stripe for WooCommerce.', 'cartflows' ),
					'isPro'       => false,
					'status'      => $this->get_plugin_status( 'checkout-plugins-stripe-woo/checkout-plugins-stripe-woo.php' ),
					'redirection' => admin_url( 'index.php?page=cpsw-onboarding' ),
					'slug'        => 'checkout-plugins-stripe-woo',
					'path'        => 'checkout-plugins-stripe-woo/checkout-plugins-stripe-woo.php',
					'logoPath'    => array(
						'icon_path' => CARTFLOWS_ADMIN_CORE_URL . 'assets/images/plugins/cpsw.svg',
					),
				),
				array(
					'title'       => __( 'PayPal Payments For WooCommerce', 'cartflows' ),
					'subtitle'    => __( 'Accept payments in your store with PayPal for WooCommerce.', 'cartflows' ),
					'isPro'       => false,
					'status'      => $this->get_plugin_status( 'checkout-paypal-woo/checkout-paypal-woo.php' ),
					'slug'        => 'checkout-paypal-woo',
					'path'        => 'checkout-paypal-woo/checkout-paypal-woo.php',
					'redirection' => admin_url( 'admin.php?page=wc-settings&tab=cppw_api_settings' ),
					'logoPath'    => array(
						'icon_path' => CARTFLOWS_ADMIN_CORE_URL . 'assets/images/plugins/cppw.svg',
					),
				),
			)
		);
	}
}

AdminMenu::get_instance();
