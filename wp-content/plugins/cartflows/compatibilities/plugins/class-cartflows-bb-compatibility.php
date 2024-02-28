<?php
/**
 * Beaver Builder page builder compatibility
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Cartflows_BB_Compatibility' ) ) :

	/**
	 * Class for Beaver Builder page builder compatibility
	 */
	class Cartflows_BB_Compatibility {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.1.4
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
		 * @since 1.1.4
		 */
		public function __construct() {
			add_filter( 'fl_builder_post_types', array( $this, 'post_types' ) );
			add_action( 'admin_init', array( $this, 'disable_rediraction' ), 99 );
		}

		/**
		 * Disable Beaver Builder Redirection after plugin install.
		 *
		 * @since 1.1.4
		 *
		 * @return void
		 */
		public function disable_rediraction() {
			delete_transient( '_fl_builder_activation_admin_notice' );
		}

		/**
		 * Add beaver builder support for step post type.
		 *
		 * @since 1.1.4
		 *
		 * @param array $post_types container Post types.
		 * @return array
		 */
		public function post_types( $post_types ) {

			$post_types[] = 'cartflows_step';

			return $post_types;
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_BB_Compatibility::get_instance();

endif;
