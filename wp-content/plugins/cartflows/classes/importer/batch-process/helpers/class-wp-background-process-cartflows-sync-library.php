<?php
/**
 * Single Page Background Process
 *
 * @package Cartflows
 * @since 1.6.15
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'WP_Background_Process_Cartflows_Sync_Library' ) && class_exists( 'WP_Background_Process' ) ) :

	/**
	 * Image Background Process
	 *
	 * @since 1.6.15
	 */
	class WP_Background_Process_Cartflows_Sync_Library extends WP_Background_Process {

		/**
		 * Image Process
		 *
		 * @var string
		 */
		protected $action = 'cartflows_sync_library';

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
		 * @param object $object Queue item object.
		 * @return mixed
		 */
		protected function task( $object ) {

			$process = $object['instance'];
			$method  = $object['method'];
 
			if ( 'import_sites' === $method ) {
				wcf()->logger->sync_log( '-------- Importing Sites --------' );
				$page = $object['page'];
				wcf()->logger->sync_log( 'Inside Batch ' . $page );
				update_site_option( 'cartflows-batch-status-string', 'Inside Batch ' . $page, 'no' );
				$process->import_sites( $page );
			}

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

			wcf()->logger->sync_log( esc_html__( 'All processes are complete', 'cartflows' ) );
			update_site_option( 'cartflows-batch-status-string', 'All processes are complete', 'no' );
			delete_site_option( 'cartflows-batch-status' );
			update_site_option( 'cartflows-batch-is-complete', 'yes', 'no' );

			do_action( 'cartflows_site_import_batch_complete' );
		}

	}

endif;
