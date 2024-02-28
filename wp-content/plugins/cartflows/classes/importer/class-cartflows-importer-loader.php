<?php
/**
 * CartFlows Importer Loader
 *
 * @package CartFlows
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'CartFlows_Importer_Loader' ) ) :

	/**
	 * CartFlows Import
	 *
	 * @since 1.0.0
	 */
	class CartFlows_Importer_Loader {

		/**
		 * Instance
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.0.0
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			$this->setup_classes();
		}

		/**
		 * Setup importer classes
		 */
		public function setup_classes() {

			include_once CARTFLOWS_DIR . 'classes/importer/class-cartflows-api.php';
			include_once CARTFLOWS_DIR . 'classes/importer/class-cartflows-importer-core.php';

			include_once CARTFLOWS_DIR . 'classes/importer/batch-process/class-cartflows-batch-process.php';
			include_once CARTFLOWS_DIR . 'classes/importer/class-cartflows-importer.php';
		}
	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	CartFlows_Importer_Loader::get_instance();

endif;
