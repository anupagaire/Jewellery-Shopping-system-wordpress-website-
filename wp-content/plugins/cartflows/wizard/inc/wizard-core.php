<?php
/**
 * CartFlows Admin Menu.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\Wizard\Inc;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_Menu.
 */
class WizardCore {

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 1.0.0
	 */
	private static $instance;

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
	 * Constructor.
	 */
	public function __construct() {

		if ( apply_filters( 'cartflows_enable_setup_wizard', true ) && current_user_can( 'manage_options' ) ) {

			add_action( 'admin_menu', array( $this, 'admin_menus' ) );
			add_action( 'admin_init', array( $this, 'setup_wizard' ) );
			add_action( 'admin_init', array( $this, 'hide_notices' ) );
			add_action( 'admin_notices', array( $this, 'show_setup_wizard' ) );
			add_action( 'woocommerce_installed', array( $this, 'disable_woo_setup_redirect' ) );
			// We are hiding admin bar intentionally for setup wizard.
			add_filter( 'show_admin_bar', '__return_false', 1 ); //phpcs:ignore WordPressVIPMinimum.UserExperience.AdminBarRemoval.RemovalDetected

			add_action( 'init', array( $this, 'load_scripts' ) );
			add_action( 'admin_print_styles', array( $this, 'load_admin_media_styles' ) );

			add_action( 'admin_init', array( $this, 'redirect_to_onboarding' ) );
		}
	}

	/**
	 * Redirect to onboarding if required.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function redirect_to_onboarding() {

		if ( ! get_option( 'wcf_start_onboarding', false ) ) {
			return;
		}

		if ( '1' === get_option( 'wcf_setup_complete', false ) || '1' === get_option( 'wcf_setup_skipped', false ) ) {
			return;
		}

		delete_option( 'wcf_start_onboarding' );
		wp_safe_redirect( esc_url_raw( admin_url( 'index.php?page=cartflow-setup' ) ) );
		exit();
	}


	/**
	 * Load styles.
	 */
	public function load_admin_media_styles() {

		$ary_libs               = array(
			'common',
			'forms',
		);
		$admin_media_styles_url = add_query_arg(
			array(
				'c'      => 0,
				'dir'    => ! is_rtl() ? 'ltr' : 'rtl',
				'load[]' => implode( ',', $ary_libs ),
				'ver'    => 'you_wp_version',
			),
			admin_url() . 'load-styles.php'
		);
		echo "<link rel='stylesheet' id='admin_styles_for_media-css' href='" . esc_url( $admin_media_styles_url ) . "' type='text/css' media='all' />";
	}

	/**
	 * Load media.
	 */
	public function load_scripts() {

		add_action( 'wp_enqueue_scripts', array( $this, 'load_media_script' ) );
	}

	/**
	 * Load WP media script on init.
	 */
	public function load_media_script() {
		wp_enqueue_media();
	}

	/**
	 * Hide a notice if the GET variable is set.
	 */
	public function hide_notices() {

		if ( ! isset( $_GET['wcf-hide-notice'] ) ) {
			return;
		}

		$wcf_hide_notice   = isset( $_GET['wcf-hide-notice'] ) ? sanitize_text_field( wp_unslash( $_GET['wcf-hide-notice'] ) ) : '';
		$_wcf_notice_nonce = isset( $_GET['_wcf_notice_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wcf_notice_nonce'] ) ) : '';

		if ( $wcf_hide_notice && $_wcf_notice_nonce && wp_verify_nonce( sanitize_text_field( wp_unslash( $_wcf_notice_nonce ) ), 'wcf_hide_notices_nonce' ) ) {
			update_option( 'wcf_setup_skipped', true );
		}
	}

	/**
	 *  Disable the woo redirect for new setup.
	 */
	public function disable_woo_setup_redirect() {

		delete_transient( '_wc_activation_redirect' );
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function show_setup_wizard() {

		$screen          = get_current_screen();
		$screen_id       = $screen ? $screen->id : '';
		$allowed_screens = array(
			'cartflows_page_cartflows_settings',
			'edit-cartflows_flow',
			'dashboard',
			'plugins',
		);

		if ( ! in_array( $screen_id, $allowed_screens, true ) ) {
			return;
		}

		$status     = get_option( 'wcf_setup_complete', false );
		$skip_setup = get_option( 'wcf_setup_skipped', false );

		if ( false === $status && ! $skip_setup ) { ?>
			<div class="notice notice-info wcf-notice">
				<p><b><?php esc_html_e( 'Thanks for installing and using CartFlows!', 'cartflows' ); ?></b></p>
				<p><?php esc_html_e( 'It is easy to use the CartFlows. Please use the setup wizard to quick start setup.', 'cartflows' ); ?></p>
				<p>
					<a href="<?php echo esc_url( admin_url( 'index.php?page=cartflow-setup' ) ); ?>" class="button button-primary"> <?php esc_html_e( 'Start Wizard', 'cartflows' ); ?></a>
					<a class="button-secondary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wcf-hide-notice', 'install' ), 'wcf_hide_notices_nonce', '_wcf_notice_nonce' ) ); ?>"><?php esc_html_e( 'Skip Setup', 'cartflows' ); ?></a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {

		// Ignoring phpcs nonce rule as we are calling this function on admin action.
		if ( empty( $_GET['page'] ) || 'cartflow-setup' !== $_GET['page'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		add_dashboard_page( '', '', 'manage_options', 'cartflow-setup', '' );
	}

	/**
	 * Show the setup wizard.
	 */
	public function setup_wizard() {

		// Ignoring phpcs nonce rule as we are calling this function on admin action.
		if ( empty( $_GET['page'] ) || 'cartflow-setup' !== $_GET['page'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		$this->load_required_scripts();
		$this->localize_vars();

		// Diable loading of Query Monitor in footer.
		add_filter( 'qm/dispatch/html', '__return_false' );

		ob_start();
		include_once CARTFLOWS_DIR . 'wizard/views/wizard-base.php';
	}

	/**
	 * Load scripts.
	 */
	public function load_required_scripts() {
		$handle            = 'cartflows-wizard';
		$build_path        = CARTFLOWS_DIR . 'wizard/assets/build/';
		$build_url         = CARTFLOWS_URL . 'wizard/assets/build/';
		$script_asset_path = $build_path . 'wizard-app.asset.php';
		$script_info       = file_exists( $script_asset_path )
			? include $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => CARTFLOWS_VER,
			);

		$script_dep = array_merge( $script_info['dependencies'], array( 'updates' ) );

		wp_register_script(
			$handle,
			$build_url . 'wizard-app.js',
			$script_dep,
			$script_info['version'],
			true
		);

		wp_register_style(
			$handle,
			$build_url . 'wizard-app.css',
			array(),
			CARTFLOWS_VER
		);

		wp_enqueue_script( $handle );
		wp_enqueue_style( $handle );
		wp_style_add_data( $handle, 'rtl', 'replace' );

		wp_register_script(
			'cartflows-setup-helper',
			CARTFLOWS_URL . 'wizard/assets/js/helper.js',
			array( 'jquery', 'wp-util', 'updates', 'media-upload' ),
			CARTFLOWS_VER,
			true
		);
		wp_enqueue_script( 'cartflows-setup-helper' );
		wp_enqueue_media();
		wp_enqueue_script( 'jquery-ui' );

	}

	/**
	 * Save usage tracking Settings.
	 */
	public function save_usage_tracking_option() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_ajax_referer( 'wcf-usage-tracking-option', 'security' );

		$allow_usage_tracking = isset( $_POST['allow_usage_tracking'] ) && 'true' == $_POST['allow_usage_tracking'] ? 'yes' : 'no';

		$usage_tracking = get_site_option( 'cf_analytics_optin' );

		if ( ( false === $usage_tracking ) || $allow_usage_tracking !== $usage_tracking ) {
			update_site_option( 'cf_analytics_optin', $allow_usage_tracking );
		}

		wp_send_json_success( get_site_option( 'cf_analytics_optin' ) );
	}

	/**
	 * Redirect the user to create his first flow depending on which UI he is using.
	 */
	public function get_final_page_link() {

		$default_url = add_query_arg(
			array(
				'page' => CARTFLOWS_SLUG,
				'path' => 'flows',
			),
			admin_url()
		);

		return $default_url;

	}


	/**
	 * Localize variables in admin.
	 */
	public function localize_vars() {

		$vars = array();

		$plugins = array(
			'woocommerce'                   => $this->get_plugin_status( 'woocommerce/woocommerce.php' ),
			'woo-cart-abandonment-recovery' => $this->get_plugin_status( 'woo-cart-abandonment-recovery/woo-cart-abandonment-recovery.php' ),
		);

		$installed_plugins = get_plugins();

		$page_builders = array(
			'elementor'      => array(
				'slug'    => 'elementor',
				'init'    => 'elementor/elementor.php',
				'active'  => is_plugin_active( 'elementor/elementor.php' ) ? 'yes' : 'no',
				'install' => isset( $installed_plugins['elementor/elementor.php'] ) ? 'yes' : 'no',
			),
			'beaver-builder' => array(
				'slug'    => 'beaver-builder-lite-version',
				'init'    => 'beaver-builder-lite-version/fl-builder.php',
				'active'  => is_plugin_active( 'beaver-builder-lite-version/fl-builder.php' ) ? 'yes' : 'no',
				'install' => isset( $installed_plugins['beaver-builder-lite-version/fl-builder.php'] ) ? 'yes' : 'no',
			),
			'divi'           => array(
				'slug'    => 'divi',
				'init'    => 'divi',
				'active'  => 'yes',
				'install' => 'NA',
			),
			'gutenberg'      => array(
				'slug'    => 'ultimate-addons-for-gutenberg',
				'init'    => 'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php',
				'active'  => is_plugin_active( 'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php' ) ? 'yes' : 'no',
				'install' => isset( $installed_plugins['ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php'] ) ? 'yes' : 'no',
			),
			// Intentionally installing the GB plugin when the other option is selected.
			'other'          => array(
				'slug'    => 'ultimate-addons-for-gutenberg',
				'init'    => 'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php',
				'active'  => is_plugin_active( 'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php' ) ? 'yes' : 'no',
				'install' => isset( $installed_plugins['ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php'] ) ? 'yes' : 'no',
			),
		);

		$current_user = wp_get_current_user();

		$vars = array(
			'current_user_name'      => ! empty( $current_user->user_firstname ) ? $current_user->user_firstname : $current_user->display_name,
			'current_user_email'     => ! empty( $current_user->user_email ) ? $current_user->user_email : '',
			'plugins'                => $plugins,
			'page_builders'          => $page_builders,
			'ajax_url'               => admin_url( 'admin-ajax.php' ),
			'admin_url'              => admin_url( 'admin.php' ),
			'admin_base_url'         => admin_url(),
			'admin_index_url'        => admin_url( 'index.php' ),
			'default_page_builder'   => \Cartflows_Helper::get_common_settings()['default_page_builder'],
			'site_logo'              => $this->get_site_logo_url(),
			'template_import_errors' => array(
				'api_errors' => array(
					'title' => __( 'Oops!! Unexpected error occoured', 'cartflows' ),
					'msg'   => __( 'Import template API call failed. Please reload the page and try again!', 'cartflows' ),
				),
			),
			'is_pro'                 => _is_cartflows_pro(),
			'cf_pro_type'            => defined( 'CARTFLOWS_PRO_PLUGIN_TYPE' ) ? CARTFLOWS_PRO_PLUGIN_TYPE : 'free',
		);

		$vars = apply_filters( 'cartflows_admin_wizard_localized_vars', $vars );

		wp_localize_script( 'cartflows-wizard', 'cartflows_wizard', $vars );
	}

	/**
	 * Get customizer/site logo URL.
	 *
	 * @since 1.10.0
	 *
	 * @return $image_url Site logo URL
	 */
	public function get_site_logo_url() {

		$logo      = get_theme_mod( 'custom_logo' );
		$site_logo = '';

		if ( ! empty( $logo ) ) {

			$image = wp_get_attachment_image_src( $logo, 'full' );

			$site_logo = array(
				'id'     => $logo,
				'url'    => $image[0] ? $image[0] : '',
				'width'  => $image[1] ? $image[1] : '',
				'height' => $image[2] ? $image[2] : '',
			);
		}

		return $site_logo;
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
}

WizardCore::get_instance();
