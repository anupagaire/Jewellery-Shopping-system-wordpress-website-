<?php
/**
 * Flow
 *
 * @package CartFlows
 */

define( 'CARTFLOWS_WD_FLOW_DIR', CARTFLOWS_DIR . 'modules/woo-dynamic-flow/' );
define( 'CARTFLOWS_WD_FLOW_URL', CARTFLOWS_URL . 'modules/woo-dynamic-flow/' );

/**
 * Initial Setup
 *
 * @since 1.0.0
 */
class Cartflows_Woo_Dynamic_Flow {

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
		require_once CARTFLOWS_WD_FLOW_DIR . 'classes/class-cartflows-wd-flow-loader.php';
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Woo_Dynamic_Flow::get_instance();
