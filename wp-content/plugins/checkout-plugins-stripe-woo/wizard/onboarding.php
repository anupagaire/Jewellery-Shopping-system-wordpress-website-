<?php
/**
 * Wizard Class
 *
 * @package checkout-plugins-stripe-woo
 * @since 1.3.0
 */

namespace CPSW\Wizard;

use CPSW\Inc\Traits\Get_Instance;
use CPSW\Admin\Admin_Controller;
use CPSW\Inc\Helper;
/**
 * Onboarding Class - Handles Onboarding Process
 *
 * @since 1.3.0
 */
class Onboarding {
	use Get_Instance;

	/**
	 * Stores slug for WooCommerce plugin
	 *
	 * @var string
	 * @since 1.3.0
	 */
	public $woocommerce_slug = 'woocommerce/woocommerce.php';

	/**
	 * Constructor
	 *
	 * @since 1.3.0
	 */
	public function __construct() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->admin_controller = Admin_Controller::get_instance();

		add_action( 'admin_menu', [ $this, 'admin_menus' ] );
		add_action( 'admin_init', [ $this, 'setup_wizard' ] );
		add_action( 'admin_notices', [ $this, 'show_onboarding_wizard_notice' ] );
		add_filter( 'cpsw_stripe_connect_redirect_url', [ $this, 'redirect_to_onboarding' ], 5 );
		add_action( 'cpsw_after_connect_with_stripe', [ $this, 'update_connect_with_stripe_status' ] );
		add_action( 'wp_ajax_cpsw_onboarding_install_woocommerce', [ $this, 'cpsw_onboarding_install_woocommerce' ] );
		add_action( 'wp_ajax_cpsw_onboarding_enable_gateway', [ $this, 'cpsw_onboarding_enable_gateway' ] );
		add_action( 'wp_ajax_cpsw_onboarding_enable_express_checkout', [ $this, 'cpsw_onboarding_enable_express_checkout' ] );
		add_action( 'admin_init', [ $this, 'hide_notices' ] );
		add_action( 'admin_bar_menu', [ $this, 'admin_bar_icon' ], 999 );
	}

	/**
	 * Adding dashboard page for onboarding wizard
	 *
	 * @return void
	 * @since 1.3.0
	 */
	public function admin_menus() {
		// Adds dashboard page for onboarding. Nonce verification may not be required.
		if ( empty( $_GET['page'] ) || 'cpsw-onboarding' !== $_GET['page'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		add_dashboard_page( '', '', 'manage_options', 'cpsw-onboarding', '' );
	}

	/**
	 * Enqueue resource for onboarding wizard
	 *
	 * @return void
	 * @since 1.3.0
	 */
	public function setup_wizard() {
		// Generates HTML for onboarding wizard, Nonce verification may not be required.
		if ( empty( $_GET['page'] ) || 'cpsw-onboarding' !== $_GET['page'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$this->enqueue_scripts_styles();

		// Stops WooCommerce redirection on activation.
		delete_transient( '_wc_activation_redirect' );

		ob_start();
		$this->setup_wizard_html();
		exit;
	}

	/**
	 * Enqueues scripts and styles required for onboarding wizard
	 *
	 * @return void
	 * @since 1.3.0
	 */
	public function enqueue_scripts_styles() {
		// adding tailwindcss scripts and styles.
		wp_register_style( 'cpsw-onboarding', CPSW_URL . 'wizard/build/app.css', [], CPSW_VERSION );
		wp_enqueue_style( 'cpsw-onboarding' );
		wp_style_add_data( 'cpsw-onboarding', 'rtl', 'replace' );

		$script_asset_path = CPSW_DIR . 'wizard/build/app.asset.php';
		$script_info       = file_exists( $script_asset_path )
			? include $script_asset_path
			: [
				'dependencies' => [],
				'version'      => CPSW_VERSION,
			];

		$script_dep = array_merge( $script_info['dependencies'], [ 'updates' ] );

		wp_register_script( 'cpsw-onboarding', CPSW_URL . 'wizard/build/app.js', $script_dep, CPSW_VERSION, true );
		wp_enqueue_script( 'cpsw-onboarding' );
		wp_localize_script( 'cpsw-onboarding', 'onboarding_vars', $this->localize_vars() );

		wp_register_script( 'cpsw-onboarding-helper', CPSW_URL . 'wizard/js/helper.js', [ 'jquery', 'updates' ], CPSW_VERSION, true );
		wp_enqueue_script( 'cpsw-onboarding-helper' );
	}

	/**
	 * Creates HTML for onboarding wizard
	 *
	 * @return void
	 * @since 1.3.0
	 */
	public function setup_wizard_html() {
		set_current_screen();
		?>
		<html <?php language_attributes(); ?>>
			<head>
				<meta name="viewport" content="width=device-width" />
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<title><?php esc_html_e( 'Stripe for WooCommerce - Onboarding', 'checkout-plugins-stripe-woo' ); ?></title>

				<script type="text/javascript">
					addLoadEvent = function(func){
						if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}
					};
					var ajaxurl = '<?php echo esc_url( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
					var pagenow = '';
				</script>
				<?php do_action( 'admin_print_styles' ); ?>
				<?php do_action( 'admin_head' ); ?>
			</head>
			<body class="cpsw-setup wp-core-ui">
				<div class="cpsw-onboarding-content" id="cpsw-onboarding-content"></div>
			</body>
			<?php wp_print_scripts( [ 'cpsw-onboarding' ] ); ?>
			<?php wp_print_scripts( [ 'cpsw-onboarding-helper' ] ); ?>
		</html>
		<?php
	}

	/**
	 * Shows admin notice to initiate onboarding process
	 *
	 * @return void
	 * @since 1.3.0
	 */
	public function show_onboarding_wizard_notice() {
		$screen          = get_current_screen();
		$screen_id       = $screen ? $screen->id : '';
		$allowed_screens = [
			'woocommerce_page_wc-settings',
			'dashboard',
			'plugins',
		];

		if ( ! in_array( $screen_id, $allowed_screens, true ) || $this->admin_controller->is_stripe_connected() ) {
			return;
		}

		$status         = get_option( 'cpsw_setup_status', false );
		$onboarding_url = admin_url( 'index.php?page=cpsw-onboarding' );

		if ( ! class_exists( 'woocommerce' ) ) {
			$onboarding_url = add_query_arg( 'cpsw_call', 'setup-woocommerce', $onboarding_url );
		}

		if ( false === $status ) {
			?>
			<div class="notice notice-info wcf-notice">
				<p><b><?php esc_html_e( 'Thanks for installing Checkout Plugins - Stripe for WooCommerce!', 'checkout-plugins-stripe-woo' ); ?></b></p>
				<p><?php esc_html_e( 'Follow Onboarding process to connect your Stripe account', 'checkout-plugins-stripe-woo' ); ?></p>
				<p>
					<a href="<?php echo esc_url( $onboarding_url ); ?>" class="button button-primary"> <?php esc_html_e( 'Configure Stripe', 'checkout-plugins-stripe-woo' ); ?></a>
					<a class="button-secondary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'cpsw-hide-notice', 'install' ), 'cpsw_hide_notices_nonce', '_cpsw_notice_nonce' ) ); ?>"><?php esc_html_e( 'Skip Setup', 'checkout-plugins-stripe-woo' ); ?></a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Return url for stripe connect success
	 *
	 * @param string $return_url default return url to admin page.
	 * @return string
	 * @since 1.3.0
	 */
	public function redirect_to_onboarding( $return_url ) {
		return admin_url( 'index.php?page=cpsw-onboarding' );
	}

	/**
	 * Update onboarding setup status
	 *
	 * @param string $status Set status.
	 * @return void
	 * @since 1.3.0
	 */
	public function update_connect_with_stripe_status( $status = 'success' ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		update_option( 'cpsw_setup_status', $status );
	}

	/**
	 * Localized variables for onboarding wizard
	 *
	 * @return array
	 * @since 1.3.0
	 */
	public function localize_vars() {
		$redirect_url       = admin_url( 'index.php?page=cpsw-onboarding' );
		$available_gateways = $this->available_gateways();
		return [
			'ajax_url'                                => admin_url( 'admin-ajax.php' ),
			'base_url'                                => $redirect_url,
			'assets_url'                              => CPSW_URL . 'wizard/',
			'authorization_url'                       => $this->admin_controller->get_stripe_connect_url( $redirect_url ),
			'settings_url'                            => admin_url( 'admin.php?page=wc-settings&tab=cpsw_api_settings' ),
			'gateways_url'                            => admin_url( 'admin.php?page=wc-settings&tab=checkout&section=cpsw_stripe' ),
			'manual_connect_url'                      => admin_url( 'admin.php?page=wc-settings&tab=cpsw_api_settings&connect=manually' ),
			'available_gateways'                      => $available_gateways,
			'woocommerce_setup_url'                   => admin_url( 'plugin-install.php?s=woocommerce&tab=search' ),
			'cpsw_onboarding_enable_gateway'          => wp_create_nonce( 'cpsw_onboarding_enable_gateway' ),
			'cpsw_onboarding_enable_express_checkout' => wp_create_nonce( 'cpsw_onboarding_enable_express_checkout' ),
			'cpsw_onboarding_install_woocommerce'     => wp_create_nonce( 'cpsw_onboarding_install_woocommerce' ),
			'woocommerce_installed'                   => $this->is_woocommerce_installed(),
			'woocommerce_activated'                   => class_exists( 'woocommerce' ),
			'navigator_base'                          => '/wp-admin/index.php?page=cpsw-onboarding',
			'onboarding_base'                         => admin_url( 'index.php?page=cpsw-onboarding' ),
			'get_payment_mode'                        => Helper::get_payment_mode(),
			'get_webhook_secret'                      => Helper::get_webhook_secret(),
			'webhook_url'                             => esc_url( get_home_url() . '/wp-json/cpsw/v1/webhook' ),
		];
	}

	/**
	 * Returns available gateways as per woocommerce store setup
	 *
	 * @return mixed
	 * @since 1.3.0
	 */
	public function available_gateways() {
		// Retrieves available payment gateways, does not save anything. Nonce verification may not be required.
		if ( empty( $_GET['cpsw_call'] ) || 'success' !== $_GET['cpsw_call'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false;
		}

		$gateways = WC()->payment_gateways->payment_gateways();
		if ( empty( $gateways ) ) {
			return false;
		}

		$available_gateways = [
			[
				'id'          => 'cpsw_stripe',
				'name'        => 'Stripe Card Processing',
				'icon'        => CPSW_URL . 'assets/icon/credit-card.svg',
				'recommended' => true,
				'currencies'  => 'all',
				'enabled'     => true,
			],
		];

		$currency = get_woocommerce_currency();
		foreach ( $gateways as $id => $class ) {
			if (
				0 === strpos( $id, 'cpsw_' ) &&
				method_exists( $class, 'get_supported_currency' ) &&
				in_array( $currency, $class->get_supported_currency(), true )
				) {
				$temp                 = [];
				$icon                 = str_replace( 'cpsw_', '', $id );
				$temp['id']           = $id;
				$temp['name']         = $class->method_title;
				$temp['icon']         = CPSW_URL . 'assets/icon/' . $icon . '.svg';
				$temp['recommended']  = false;
				$temp['currencies']   = implode( ', ', $class->get_supported_currency() );
				$temp['enabled']      = false;
				$available_gateways[] = $temp;
			}
		}

		// Create webhook secret key.
		Admin_Controller::get_instance()->create_webhooks( 'automatic' );

		return $available_gateways;
	}

	/**
	 * Installs WooCommerce if reuired
	 *
	 * @return void
	 * @since 1.3.0
	 */
	public function cpsw_onboarding_install_woocommerce() {
		check_ajax_referer( 'cpsw_onboarding_install_woocommerce', 'security' );

		$activate = activate_plugin( $this->woocommerce_slug, '', false, true );

		if ( is_wp_error( $activate ) ) {
			wp_send_json_error(
				array(
					'success' => false,
					'message' => $activate->get_error_message(),
				)
			);
		}

		wp_send_json_success();
	}

	/**
	 * Handles enabling gateways from onboarding wizard
	 * Returns success / failure in form of json
	 *
	 * @return void
	 * @since 1.3.0
	 */
	public function cpsw_onboarding_enable_gateway() {
		check_ajax_referer( 'cpsw_onboarding_enable_gateway', 'security' );
		// sanitization is being done in later stage using wc_clean function.
		$gateway_status = isset( $_POST['formdata'] ) ? json_decode( wp_unslash( $_POST['formdata'] ), true ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( empty( $gateway_status ) || ! is_array( $gateway_status ) ) {
			wp_send_json_success( [ 'message' => 'no gateway selected' ] );
		}

		$gateways = WC()->payment_gateways->payment_gateways();

		$response = [];
		foreach ( $gateway_status as $id => $status ) {
			$status = wc_clean( $status );
			$id     = sanitize_text_field( $id );
			if ( 'true' === $status && isset( $gateways[ $id ] ) ) {
				if ( ( 'yes' !== $gateways[ $id ]->enabled && $gateways[ $id ]->update_option( 'enabled', 'yes' ) ) || 'yes' === $gateways[ $id ]->enabled ) {
					$response[ $id ] = true;
				} else {
					$response[ $id ] = false;
				}
			}
		}

		wp_send_json_success( [ 'activated_gateways' => $response ] );
	}

	/**
	 * Handles Express Checkout enabling call from onboarding wizard
	 *
	 * @return void
	 * @since 1.3.0
	 */
	public function cpsw_onboarding_enable_express_checkout() {
		check_ajax_referer( 'cpsw_onboarding_enable_express_checkout', 'security' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$cpsw_stripe = Helper::get_gateway_settings();
		if ( 'yes' === $cpsw_stripe['express_checkout_enabled'] ) {
			wp_send_json_success( [ 'express_checkout' => true ] );
		}
		$cpsw_stripe = array_merge( $cpsw_stripe, [ 'express_checkout_enabled' => 'yes' ] );
		if ( update_option( 'woocommerce_cpsw_stripe_settings', $cpsw_stripe ) ) {
			wp_send_json_success( [ 'express_checkout' => true ] );
		}

		wp_send_json_error( [ 'express_checkout' => false ] );
	}

	/**
	 * Checks if woocommerce is installed
	 *
	 * @return boolean
	 * @since 1.3.0
	 */
	public function is_woocommerce_installed() {
		$plugins = get_plugins();
		if ( isset( $plugins[ $this->woocommerce_slug ] ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Hide onboarding notice on clck of skip setup button
	 *
	 * @return void
	 * @since 1.3.0
	 */
	public function hide_notices() {
		if ( ! isset( $_GET['cpsw-hide-notice'] ) ) {
			return;
		}

		$cpsw_hide_notice   = filter_input( INPUT_GET, 'cpsw-hide-notice', FILTER_SANITIZE_STRING );
		$_cpsw_notice_nonce = filter_input( INPUT_GET, '_cpsw_notice_nonce', FILTER_SANITIZE_STRING );

		if ( $cpsw_hide_notice && $_cpsw_notice_nonce && wp_verify_nonce( sanitize_text_field( wp_unslash( $_cpsw_notice_nonce ) ), 'cpsw_hide_notices_nonce' ) ) {
			$this->update_connect_with_stripe_status( 'skipped' );
		}
	}

	/**
	 * Adds admin bar icon for onboarding wizard
	 *
	 * @param object $admin_bar object of WP_Admin_Bar.
	 * @return void
	 * @since 1.3.0
	 */
	public function admin_bar_icon( $admin_bar ) {
		if ( ! current_user_can( 'manage_options' ) || ! is_admin() ) {
			return;
		}

		if (
			'skipped' !== get_option( 'cpsw_setup_status', false ) &&
			$this->admin_controller->is_stripe_connected()
		) {
			return;
		}

		$iconurl = CPSW_URL . 'wizard/images/cpsw-logo-light.svg';

		$iconspan = '<span class="cpsw-logo-icon" style="float: left;
		width: 22px !important;
		height: 22px !important;
		margin-left: 5px !important;
		margin-top: 5px !important;
		background-size: 22px 22px;
		background-image:url(\'' . $iconurl . '\');"></span>';

		$title = $iconspan . '';

		$onboarding_url = admin_url( 'index.php?page=cpsw-onboarding' );

		if ( ! class_exists( 'woocommerce' ) ) {
			$onboarding_url = add_query_arg( 'cpsw_call', 'setup-woocommerce', $onboarding_url );
		}
		$args = [
			'id'    => 'cpsw-onboarding-link',
			'title' => $title,
			'href'  => $onboarding_url,
		];
		$admin_bar->add_node( $args );
	}

}
