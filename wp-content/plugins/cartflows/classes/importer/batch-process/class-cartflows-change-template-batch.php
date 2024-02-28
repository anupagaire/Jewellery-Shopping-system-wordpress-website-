<?php
/**
 * Change Template Process
 *
 * @package CartFlows
 * @since 1.2.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Cartflows_Change_Template_Batch' ) && class_exists( 'WP_Background_Process' ) ) :

	/**
	 * Change Template Process
	 *
	 * @since 1.2.2
	 */
	class Cartflows_Change_Template_Batch extends WP_Background_Process {

		/**
		 * Template Process
		 *
		 * @var string
		 */
		protected $action = 'cartflows_change_template_process';

		/**
		 * Task
		 *
		 * Override this method to perform any actions required on each
		 * queue item. Return the modified item for further processing
		 * in the next pass through. Or, return false to remove the
		 * item from the queue.
		 *
		 * @param mixed $post_id Queue item to iterate over.
		 *
		 * @return mixed
		 */
		protected function task( $post_id ) {

			wcf()->logger->log( '(✓) Step ID ' . $post_id );
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				wcf()->logger->sync_log( 'Processed:' . $post_id );
			}
			update_post_meta( $post_id, '_wp_page_template', 'cartflows-default' );
			return false;
		}

		/**
		 * Complete
		 *
		 * Override if applicable, but ensure that the below actions are
		 * performed, or, call parent::complete().
		 */
		protected function complete() {
			parent::complete();
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				wcf()->logger->sync_log( 'Process Complete' );
			}
		}
	}

endif;
