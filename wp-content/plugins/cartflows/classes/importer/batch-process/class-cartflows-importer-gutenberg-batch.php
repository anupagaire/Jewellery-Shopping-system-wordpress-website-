<?php
/**
 * Gutenberg Batch Process
 *
 * @package CartFlows
 * @since 1.6.15
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Cartflows_Importer_Gutenberg_Batch' ) && class_exists( 'WP_Background_Process' ) ) :

	/**
	 * Image Background Process
	 *
	 * @since 1.6.15
	 */
	class Cartflows_Importer_Gutenberg_Batch extends WP_Background_Process {

		/**
		 * Image Process
		 *
		 * @var string
		 */
		protected $action = 'cartflows_gutenberg_image_process';

		/**
		 * Task
		 *
		 * Override this method to perform any actions required on each
		 * queue item. Return the modified item for further processing
		 * in the next pass through. Or, return false to remove the
		 * item from the queue.
		 *
		 * @since 1.6.15
		 *
		 * @param integer $post_id Post Id.
		 * @return mixed
		 */
		protected function task( $post_id ) {

			CartFlows_Importer_Gutenberg::get_instance()->import_single_post( $post_id );

			return false;
		}

		/**
		 * Complete
		 *
		 * Override if applicable, but ensure that the below actions are
		 * performed, or, call parent::complete().
		 *
		 * @since 1.6.15
		 */
		protected function complete() {

			parent::complete();

			do_action( 'cartflows_import_complete' );

		}

	}

endif;
