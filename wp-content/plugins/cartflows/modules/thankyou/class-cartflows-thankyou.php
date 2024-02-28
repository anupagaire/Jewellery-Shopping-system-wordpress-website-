<?php
/**
 * Thankyou
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CARTFLOWS_THANKYOU_DIR', CARTFLOWS_DIR . 'modules/thankyou/' );
define( 'CARTFLOWS_THANKYOU_URL', CARTFLOWS_URL . 'modules/thankyou/' );

/**
 * Initial Setup
 *
 * @since 1.0.0
 */
class Cartflows_Thankyou {


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

		require_once CARTFLOWS_THANKYOU_DIR . 'classes/class-cartflows-thankyou-markup.php';
		require_once CARTFLOWS_THANKYOU_DIR . 'classes/class-cartflows-thankyou-meta-data.php';
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Thankyou::get_instance();
