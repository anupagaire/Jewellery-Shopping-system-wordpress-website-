<?php
/**
 * Flow
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'CARTFLOWS_FLOW_DIR', CARTFLOWS_DIR . 'modules/flow/' );
define( 'CARTFLOWS_FLOW_URL', CARTFLOWS_URL . 'modules/flow/' );

/**
 * Initial Setup
 *
 * @since 1.0.0
 */
class Cartflows_Flow {


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

		require_once CARTFLOWS_FLOW_DIR . 'classes/class-cartflows-step-meta-base.php';
		require_once CARTFLOWS_FLOW_DIR . 'classes/class-cartflows-flow-loader.php';
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Flow::get_instance();
