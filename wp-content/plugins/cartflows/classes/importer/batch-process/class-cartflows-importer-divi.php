<?php
/**
 * Divi Importer
 *
 * @package CartFlows
 * @since 1.1.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'CartFlows_Importer_Divi' ) ) :

	/**
	 * CartFlows Import Divi
	 *
	 * @since 1.1.1
	 */
	class CartFlows_Importer_Divi {

		/**
		 * Instance
		 *
		 * @since 1.1.1
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.1.1
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
		 * @since 1.1.1
		 */
		public function __construct() {}

		/**
		 * Allowed tags for the batch update process
		 *
		 * @since 1.6.15
		 *
		 * @param  array        $allowedposttags   Array of default allowable HTML tags.
		 * @param  string|array $context    The context for which to retrieve tags. Allowed values are 'post',
		 *                                  'strip', 'data', 'entities', or the name of a field filter such as
		 *                                  'pre_user_description'.
		 * @return array Array of allowed HTML tags and their allowed attributes.
		 */
		public function allowed_tags_and_attributes( $allowedposttags, $context ) {

			// Keep only for 'post' contenxt.
			if ( 'post' === $context ) {

				// <style> tag and attributes.
				$allowedposttags['style'] = array();
			}

			return $allowedposttags;
		}

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

			// Allow the SVG tags in batch update process.
			add_filter( 'wp_kses_allowed_html', array( $this, 'allowed_tags_and_attributes' ), 10, 2 );

			// Download and replace images.
			$content = get_post_meta( $post_id, 'divi_content', true );

			if ( empty( $content ) ) {
				wcf()->logger->import_log( '(✕) Not have "Divi" Data. Post content is empty!' );
			} else {

				wcf()->logger->import_log( '(✓) Processing Request..' );

				// Update hotlink images.
				$content = CartFlows_Importer::get_instance()->get_content( $content );

				// Update post content.
				wp_update_post(
					array(
						'ID'           => $post_id,
						'post_content' => $content,
					)
				);

				// Delete temporary meta key.
				delete_post_meta( $post_id, 'divi_content' );

				wcf()->logger->import_log( '(✓) Process Complete' );
			}
		}

	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	CartFlows_Importer_Divi::get_instance();

endif;
