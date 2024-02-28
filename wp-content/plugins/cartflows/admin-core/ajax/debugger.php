<?php
/**
 * CartFlows Flows ajax actions.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\AdminCore\Ajax;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use CartflowsAdmin\AdminCore\Ajax\AjaxBase;
use CartflowsAdmin\AdminCore\Inc\LogStatus;

/**
 * Class Steps.
 */
class Debugger extends AjaxBase {

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Deleted
	 *
	 * @access private
	 * @var object Class object.
	 * @since 1.0.0
	 */
	private static $file_deleted = false;

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
	 * Register ajax events.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_ajax_events() {

		$ajax_events = array(
			'show_cf_log',
			'delete_cf_log',
			'download_cf_log',
			'sync_kb_docs',
		);

		$this->init_ajax_events( $ajax_events );
	}

	/**
	 * Sync knowledge base library.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function sync_kb_docs() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_die( esc_html__( 'You don\'t have permission to perform this action.', 'cartflows' ) );
		}

		check_ajax_referer( 'cartflows_sync_kb_docs', 'security' );

		$docs_json = json_decode( wp_remote_retrieve_body( wp_remote_get( 'https://cartflows.com//wp-json/powerful-docs/v1/get-docs' ) ) );
		\Cartflows_Helper::update_admin_settings_option( 'cartflows_docs_data', $docs_json );

		$response_data = array(
			'message' => __( 'Sync Success.', 'cartflows' ),
			'status'  => true,
		);

		wp_send_json_success( $response_data );
	}


	/**
	 * Clone step with its meta.
	 */
	public function show_cf_log() {

		if ( ! current_user_can( 'cartflows_manage_settings' ) ) {
			wp_die( esc_html__( 'You don\'t have permission to view this page.', 'cartflows' ) );
		}

		check_ajax_referer( 'cartflows_show_cf_log', 'security' );

		$log_key = isset( $_POST['log_key'] ) ? sanitize_text_field( wp_unslash( $_POST['log_key'] ) ) : '';


		$logs = LogStatus::get_instance()->get_log_files();

		$log_filename = isset( $logs[ $log_key ] ) ? $logs[ $log_key ] : '';

		$file_content = file_get_contents( CARTFLOWS_LOG_DIR . $log_filename );

		wp_send_json(
			array(
				'log_content' => $file_content,
			)
		);
	}

	/**
	 * Delete Provided log file
	 */
	public function delete_cf_log() {


		if ( ! current_user_can( 'cartflows_manage_settings' ) ) {
			wp_die( esc_html__( 'You don\'t have permission to perform this action.', 'cartflows' ) );
		}

		check_ajax_referer( 'cartflows_delete_cf_log', 'security' );

		if ( empty( $_REQUEST['log_key'] ) ) {
			wp_die( esc_html__( 'Filename is empty. Please refresh the page and retry.', 'cartflows' ) );
		}

		$file_name = trim( sanitize_text_field( wp_unslash( $_REQUEST['log_key'] ) ) );
		$file_path = CARTFLOWS_LOG_DIR . $file_name;

		if ( file_exists( $file_path ) ) {
			wp_delete_file( $file_path );
			self::$file_deleted = true;
		}
	}

	/**
	 * Download the selected log file.
	 */
	public function download_cf_log() {

		if ( ! current_user_can( 'cartflows_manage_settings' ) ) {
			wp_die( esc_html__( 'You don\'t have permission to perform this action.', 'cartflows' ) );
		}

		check_ajax_referer( 'cartflows_download_cf_log', 'security' );

		$file_name = isset( $_REQUEST['log_key'] ) ? trim( sanitize_text_field( wp_unslash( $_REQUEST['log_key'] ) ) ) : '';
		$file_path = CARTFLOWS_LOG_DIR . $file_name;

		if ( ! file_exists( $file_path ) ) {
			return;
		}

		$file_extension = pathinfo( $file_name, PATHINFO_EXTENSION );
		$allowed_files  = array( 'log' );

		// Return if the desired file is not found for download.
		if ( ! in_array( $file_extension, $allowed_files, true ) || strpos( $file_name, '.php' ) !== false ) {
			wp_die( esc_html__( 'Invalid file.', 'cartflows' ) );
			return;
		}

		$file_content = file_get_contents( CARTFLOWS_LOG_DIR . $file_name );

		$response_data = array(
			'message'      => __( 'Export logs successfully', 'cartflows' ),
			'file_content' => $file_content,
		);

		wp_send_json_success( $response_data );
	}
}
