<?php
/**
 * Auto Loader.
 *
 * @package checkout-plugins-stripe-woo
 * @since 0.0.1
 */

namespace CPSW;

use CPSW\Gateway\Stripe\Card_Payments;
use CPSW\Gateway\Stripe\Sepa;
use CPSW\Gateway\Stripe\Alipay;
use CPSW\Gateway\Stripe\Klarna;
use CPSW\Gateway\Stripe\Payment_Request_Api;
use CPSW\Gateway\Stripe\Ideal;
use CPSW\Gateway\Stripe\Bancontact;
use CPSW\Gateway\Stripe\P24;
use CPSW\Gateway\Stripe\Wechat;
use CPSW\Compatibility\Apple_Pay;
use CPSW\Admin\Admin_Controller;
use CPSW\Gateway\Stripe\Webhook;
use CPSW\Gateway\Stripe\Frontend_Scripts;
use CPSW\Wizard\Onboarding;

/**
 * CPSW_Loader
 *
 * @since 0.0.1
 */
class CPSW_Loader {

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class Instance.
	 * @since 0.0.1
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 0.0.1
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Autoload classes.
	 *
	 * @param string $class class name.
	 */
	public function autoload( $class ) {
		if ( 0 !== strpos( $class, __NAMESPACE__ ) ) {
			return;
		}

		$class_to_load = $class;

		$filename = strtolower(
			preg_replace(
				[ '/^' . __NAMESPACE__ . '\\\/', '/([a-z])([A-Z])/', '/_/', '/\\\/' ],
				[ '', '$1-$2', '-', DIRECTORY_SEPARATOR ],
				$class_to_load
			)
		);

		$file = CPSW_DIR . $filename . '.php';

		// if the file redable, include it.
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}

	/**
	 * Constructor
	 *
	 * @since 0.0.1
	 */
	public function __construct() {
		// Activation hook.
		register_activation_hook( CPSW_FILE, [ $this, 'install' ] );

		spl_autoload_register( [ $this, 'autoload' ] );
		if ( ! class_exists( '\Stripe\Stripe' ) ) {
			require_once 'lib/stripe-php/init.php';
		}
		$this->setup_classes();
		add_action( 'plugins_loaded', [ $this, 'load_classes' ] );
		add_filter( 'plugin_action_links_' . CPSW_BASE, [ $this, 'action_links' ] );
		add_action( 'before_woocommerce_init', [ $this, 'hpos_compatibility_declaration' ] );
		add_action( 'woocommerce_init', [ $this, 'frontend_scripts' ] );
		add_action( 'plugins_loaded', [ $this, 'load_cpsw_textdomain' ] );

		if ( is_admin() ) {
			add_action( 'admin_init', [ $this, 'check_for_onboarding' ] );
		}
	}

	/**
	 * Sets up base classes.
	 *
	 * @return void
	 */
	public function setup_classes() {
		Admin_Controller::get_instance();
		Apple_Pay::get_instance();
	}

	/**
	 * Includes frontend scripts.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function frontend_scripts() {
		if ( is_admin() ) {
			return;
		}

		Frontend_Scripts::get_instance();
	}

	/**
	 * Adds links in Plugins page
	 *
	 * @param array $links existing links.
	 * @return array
	 * @since 1.0.0
	 */
	public function action_links( $links ) {
		$plugin_links = apply_filters(
			'cpsw_plugin_action_links',
			[
				'cpsw_settings'      => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=cpsw_api_settings' ) . '">' . __( 'Settings', 'checkout-plugins-stripe-woo' ) . '</a>',
				'cpsw_documentation' => '<a href="' . esc_url( 'https://checkoutplugins.com/docs/stripe-api-settings/' ) . '" target="_blank" >' . __( 'Documentation', 'checkout-plugins-stripe-woo' ) . '</a>',
			]
		);

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Loads classes on plugins_loaded hook.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function load_classes() {
		// Initializing Onboarding.
		Onboarding::get_instance();
		if ( ! class_exists( 'woocommerce' ) ) {
			add_action( 'admin_notices', [ $this, 'wc_is_not_active' ] );
			return;
		}
		// Initializing Gateways.

		Sepa::get_instance();
		Wechat::get_instance();
		Bancontact::get_instance();
		P24::get_instance();
		Klarna::get_instance();
		Ideal::get_instance();
		Alipay::get_instance();
		Card_Payments::get_instance();
		Payment_Request_Api::get_instance();
		Webhook::get_instance();
	}

	/**
	 * Loads classes on plugins_loaded hook.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function wc_is_not_active() {
		$install_url = wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'install-plugin',
					'plugin' => 'woocommerce',
				),
				admin_url( 'update.php' )
			),
			'install-plugin_woocommerce'
		);
		echo '<div class="notice notice-error is-dismissible"><p>';
		// translators: 1$-2$: opening and closing <strong> tags, 3$-4$: link tags, takes to woocommerce plugin on wp.org, 5$-6$: opening and closing link tags, leads to plugins.php in admin.
		echo sprintf( esc_html__( '%1$sCheckout Plugins - Stripe for WooCommerce is inactive.%2$s The %3$sWooCommerce plugin%4$s must be active for Checkout Plugins - Stripe for WooCommerce to work. Please %5$sinstall & activate WooCommerce &raquo;%6$s', 'checkout-plugins-stripe-woo' ), '<strong>', '</strong>', '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', '<a href="' . esc_url( $install_url ) . '">', '</a>' );
		echo '</p></div>';
	}

	/**
	 * Checks for installation routine
	 * Loads plugins translation file
	 *
	 * @return void
	 * @since 1.3.0
	 */
	public function install() {
		$admin_controller = Admin_Controller::get_instance();
		if ( get_option( 'cpsw_setup_status', false ) || apply_filters( 'cpsw_prevent_onboarding_redirect', false ) || $admin_controller->is_stripe_connected() ) {
			return;
		}

		update_option( 'cpsw_start_onboarding', true );
	}

	/**
	 * Checks whether onboarding is required or not
	 *
	 * @return void
	 * @since 1.3.0
	 */
	public function check_for_onboarding() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! get_option( 'cpsw_start_onboarding', false ) ) {
			return;
		}

		$onboarding_url = admin_url( 'index.php?page=cpsw-onboarding' );

		if ( ! class_exists( 'woocommerce' ) ) {
			$onboarding_url = add_query_arg( 'cpsw_call', 'setup-woocommerce', $onboarding_url );
		}

		delete_option( 'cpsw_start_onboarding' );

		wp_safe_redirect( esc_url_raw( $onboarding_url ) );
		exit();
	}

	/**
	 * Loads plugins translation file
	 *
	 * @return void
	 * @since 1.3.0
	 */
	public function load_cpsw_textdomain() {
		// Default languages directory.
		$lang_dir = CPSW_DIR . 'languages/';

		// Traditional WordPress plugin locale filter.
		global $wp_version;

		$get_locale = get_locale();

		if ( $wp_version >= 4.7 ) {
			$get_locale = get_user_locale();
		}

		$locale = apply_filters( 'plugin_locale', $get_locale, 'checkout-plugins-stripe-woo' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'checkout-plugins-stripe-woo', $locale );

		// Setup paths to current locale file.
		$mofile_local  = $lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/checkout-plugins-stripe-woo/ folder.
			load_textdomain( 'checkout-plugins-stripe-woo', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/checkout-plugins-stripe-woo/languages/ folder.
			load_textdomain( 'checkout-plugins-stripe-woo', $mofile_local );
		} else {
			// Load the default language files.
			load_plugin_textdomain( 'checkout-plugins-stripe-woo', false, $lang_dir );
		}
	}

	/**
	 * Declares compatibility with WooCommerce HPOS
	 *
	 * @return void
	 * @since 1.4.8
	 */
	public function hpos_compatibility_declaration() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', CPSW_FILE, true );
		}
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
CPSW_Loader::get_instance();

