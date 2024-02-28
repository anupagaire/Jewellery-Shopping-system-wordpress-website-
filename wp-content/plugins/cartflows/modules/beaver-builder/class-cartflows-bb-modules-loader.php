<?php
/**
 * Modules Loader
 *
 * @package Beaver Builder Modules
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Initial Setup
 *
 * @since 1.6.15
 */
class Cartflows_BB_Modules_Loader {

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
	 * Constructor function that initializes required actions and hooks
	 */
	public function __construct() {

		add_action( 'wp', array( $this, 'init' ), 8 );
		add_action( 'admin_init', array( $this, 'register_modules' ), 8 );
	}

	/**
	 * Function that initializes init function
	 *
	 * @since 1.6.15
	 */
	public function init() {
		$this->include_modules_files();
	}

	/**
	 * Returns Script array.
	 *
	 * @return array()
	 * @since 1.6.15
	 */
	public static function get_module_list() {

		$widget_list = array(
			'cartflows-bb-next-step',
			'cartflows-bb-order-details',
			'cartflows-bb-checkout-form',
			'cartflows-bb-optin-form',
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
	public function include_modules_files() {

		global $post;

		if ( ! isset( $post ) ) {
			return;
		}

		$post_type = $post->post_type;

		if ( 'cartflows_step' === $post_type && class_exists( 'FLBuilder' ) ) {

			$this->get_module_files();
		}
	}

	/**
	 * Register the modules.
	 *
	 * Register modules early to disply them on options page
	 *
	 * @since 1.6.15
	 * @access public
	 */
	public function register_modules() {

		if ( is_admin() && isset( $_GET['page'] ) && 'fl-builder-settings' === sanitize_text_field( $_GET['page'] ) && class_exists( 'FLBuilder' ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$this->get_module_files();
		}
	}

	/**
	 * Get Module files
	 *
	 * @since 1.6.15
	 * @access public
	 */
	public function get_module_files() {
		/* Required files */
		require_once CARTFLOWS_DIR . 'modules/beaver-builder/classes/class-cartflows-bb-editor.php';

		include_once CARTFLOWS_DIR . 'modules/beaver-builder/classes/class-cartflows-bb-helper.php';

		$widget_list = $this->get_module_list();

		if ( ! empty( $widget_list ) ) {
			foreach ( $widget_list as $widget ) {

				include_once CARTFLOWS_DIR . 'modules/beaver-builder/' . $widget . '/' . $widget . '.php';
			}
		}
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_BB_Modules_Loader::get_instance();
