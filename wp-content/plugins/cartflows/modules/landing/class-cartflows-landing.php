<?php
/**
 * Landing
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'CARTFLOWS_LANDING_DIR', CARTFLOWS_DIR . 'modules/landing/' );
define( 'CARTFLOWS_LANDING_URL', CARTFLOWS_URL . 'modules/landing/' );

/**
 * Initial Setup
 *
 * @since 1.0.0
 */
class Cartflows_Landing {


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

		require_once CARTFLOWS_LANDING_DIR . 'classes/class-cartflows-landing-meta-data.php';
		require_once CARTFLOWS_LANDING_DIR . 'classes/class-cartflows-landing-markup.php';
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Landing::get_instance();
