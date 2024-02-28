<?php
/**
 * WP CLI
 *
 * 1. Run `wp cartflows info`       Info.
 *
 * @since 1.6.15
 *
 * @package Cartflows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Cartflows_WP_CLI' ) && class_exists( 'WP_CLI_Command' ) ) :

	/**
	 * Cartflows WP CLI
	 */
	class Cartflows_WP_CLI extends WP_CLI_Command {

		/**
		 *  Get All Flows & Steps
		 *
		 *  Example: wp cartflows list
		 *
		 * @param  array $args       Arguments.
		 * @param  array $assoc_args Associated Arguments.
		 * @alias  list
		 * @return void
		 */
		public function flow_list( $args, $assoc_args ) {

			$flows = Cartflows_Helper::get_instance()->get_flows_and_steps();

			$fields = array(
				'ID',
				'title',
				'page_builder',
				'category',
				'type',
			);

			$this->display( $assoc_args, $fields, $flows );
		}

		/**
		 * Display
		 *
		 * @since 1.6.15
		 * @param  array $assoc_args Arguments.
		 * @param  array $fields     Format fields.
		 * @param  array $items      Items list.
		 * @return void.
		 */
		private function display( $assoc_args = array(), $fields = array(), $items = array() ) {
			$formatter = new \WP_CLI\Formatter( $assoc_args, $fields );

			$formatter->display_items( $items );
		}

		/**
		 * Sync Library.
		 *
		 * Sync the library and create the .json files.
		 *
		 * Use: `wp cartflows sync`
		 *
		 * @since 1.6.15
		 * @param  array $args       Arguments.
		 * @param  array $assoc_args Associated Arguments.
		 * @return void.
		 */
		public function sync( $args = array(), $assoc_args = array() ) {
			CartFlows_Batch_Process::get_instance()->process_batch();
		}

		/**
		 * Sync Library.
		 *
		 * Sync the library and create the .json files.
		 *
		 * Use: `wp cartflows sync_all elementor`
		 * Use: `wp cartflows sync_all beaver-builder`
		 * Use: `wp cartflows sync_all gutenberg`
		 * Use: `wp cartflows sync_all divi`
		 *
		 * @since 1.6.15
		 * @param  array $args       Arguments.
		 * @param  array $assoc_args Associated Arguments.
		 * @return void.
		 */
		public function sync_all( $args = array(), $assoc_args = array() ) {

			$stored                         = get_site_option( '_cartflows_common', array() );
			$stored['default_page_builder'] = $args[0];
			update_option( '_cartflows_common', $stored );

			CartFlows_Batch_Process::get_instance()->process_batch();
		}

		/**
		 *  Import flows
		 *
		 *  Example: wp cartflows import_flows
		 *
		 * @since 1.6.15
		 * @param  array $args       Arguments.
		 * @param  array $assoc_args Associated Arguments.
		 * @return void
		 */
		public function import_flows( $args = array(), $assoc_args = array() ) {
			$file_url = isset( $args[0] ) ? $args[0] : '';

			if ( empty( $file_url ) ) {
				WP_CLI::error( 'Error: Empty file' );
			}

			$flows = json_decode( file_get_contents( $file_url ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			CartFlows_Importer::get_instance()->import_from_json_data( $flows );
		}
	}

	/**
	 * Add Command
	 */
	WP_CLI::add_command( 'cartflows', 'Cartflows_WP_CLI' );

endif;
