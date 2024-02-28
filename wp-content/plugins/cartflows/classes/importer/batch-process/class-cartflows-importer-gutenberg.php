<?php
/**
 * Gutenberg Importer
 *
 * @package CartFlows
 * @since 1.6.15
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'CartFlows_Importer_Gutenberg' ) ) :

	/**
	 * CartFlows Import Gutenberg
	 *
	 * @since 1.6.15
	 */
	class CartFlows_Importer_Gutenberg {

		/**
		 * Instance
		 *
		 * @since 1.6.15
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.6.15
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
		 * @since 1.6.15
		 */
		public function __construct() {}

		/**
		 * Update post meta.
		 *
		 * @param  integer $post_id Post ID.
		 * @return void
		 */
		public function import_single_post( $post_id = 0 ) {

			if ( CARTFLOWS_STEP_POST_TYPE !== get_post_type( $post_id ) ) {
				return;
			}

			// Download and replace images.
			$content = get_post_field( 'post_content', $post_id );

			if ( empty( $content ) ) {
				wcf()->logger->import_log( '(✕) Not have "Gutenberg" Data. Post content is empty!' );
			} else {

				wcf()->logger->import_log( '(✓) Processing Request..' );

				// Update hotlink images.
				$content = CartFlows_Importer::get_instance()->get_content( $content );

				// Fix for gutenberg invalid html due & -> &amp -> \u0026amp.
				$content = str_replace( '&amp;', "\u0026amp;", $content );

				// Update post content.
				wp_update_post(
					array(
						'ID'           => $post_id,
						'post_content' => $content,
					)
				);

				wcf()->logger->import_log( '(✓) Process Complete' );
			}
		}

	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	CartFlows_Importer_Gutenberg::get_instance();

endif;
