<?php
/**
 * Flow loader
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Initialization
 *
 * @since 1.0.0
 */
class Cartflows_Flow_Loader {


	/**
	 * Member Variable
	 *
	 * @var instance
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
	 *  Constructor
	 */
	public function __construct() {
		$this->include_files();
	}

	/**
	 * Load classes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function include_files() {

		require_once CARTFLOWS_FLOW_DIR . 'classes/class-cartflows-flow-post-type.php';
		require_once CARTFLOWS_FLOW_DIR . 'classes/class-cartflows-step-post-type.php';
		require_once CARTFLOWS_FLOW_DIR . 'classes/class-cartflows-permalink.php';
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Flow_Loader::get_instance();
