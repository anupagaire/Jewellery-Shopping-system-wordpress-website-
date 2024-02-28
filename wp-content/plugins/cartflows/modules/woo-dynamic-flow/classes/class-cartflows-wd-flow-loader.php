<?php
/**
 * Dynamic Flow loader
 *
 * @package CartFlows
 */

/**
 * Initialization
 *
 * @since 1.0.0
 */
class Cartflows_Wd_Flow_Loader {


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
		require_once CARTFLOWS_WD_FLOW_DIR . 'classes/class-cartflows-wd-flow-actions.php';
		require_once CARTFLOWS_WD_FLOW_DIR . 'classes/class-cartflows-wd-flow-shortcodes.php';
		require_once CARTFLOWS_WD_FLOW_DIR . 'classes/class-cartflows-wd-flow-product-meta.php';
		require_once CARTFLOWS_WD_FLOW_DIR . 'classes/class-cartflows-wd-flow-product-actions.php';
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Wd_Flow_Loader::get_instance();
