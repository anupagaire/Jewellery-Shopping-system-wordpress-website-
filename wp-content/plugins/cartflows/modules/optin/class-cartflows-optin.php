<?php
/**
 * Checkout
 *
 * @package Woo Funnel Cart
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'CARTFLOWS_OPTIN_DIR', CARTFLOWS_DIR . 'modules/optin/' );
define( 'CARTFLOWS_OPTIN_URL', CARTFLOWS_URL . 'modules/optin/' );

/**
 * Initial Setup
 *
 * @since 1.0.0
 */
class Cartflows_Optin {


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

		require_once CARTFLOWS_OPTIN_DIR . 'classes/class-cartflows-optin-meta-data.php';
		require_once CARTFLOWS_OPTIN_DIR . 'classes/class-cartflows-optin-markup.php';
		require_once CARTFLOWS_OPTIN_DIR . 'classes/class-cartflows-optin-fields.php';
	}
}
/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Optin::get_instance();
