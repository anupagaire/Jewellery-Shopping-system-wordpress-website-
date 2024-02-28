<?php
/**
 * CartFlows Log status.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\AdminCore\Inc;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LogStatus.
 */
class LogStatus {

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
	 * Instance
	 *
	 * @access private
	 * @var string Class object.
	 * @since 1.0.0
	 */
	private $menu_slug;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

	}

	/**
	 * User action like download or delete log files.
	 */
	public function user_actions() {

		// Ignoring nonce verification as using SuperGlobal variables on WordPress hooks.
		if ( ! empty( $_REQUEST['handle'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( ! current_user_can( 'cartflows_manage_settings' ) ) {

				wp_die( esc_html__( 'You don\'t have permission to view this page.', 'cartflows' ) );
			}

			if ( isset( $_REQUEST['btn_action'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended

				$button_action = sanitize_text_field( wp_unslash( $_REQUEST['btn_action'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

				switch ( $button_action ) {
					case 'remove-log':
						$this->delete_log_file();
						break;

					case 'download-log':
						$this->download_log_file();
						break;

					default:
						break;
				}
			}
		}
	}

	/**
	 * Show the log page contents for file log handler.
	 */
	public function display_logs() {

		if ( self::$file_deleted ) {
			echo "<div class='wcf-notice updated inline wcf-delete-log--message'>" . esc_html__( 'Log deleted successfully!', 'cartflows' ) . ' </div>';
		}

		$logs = $this->get_log_files();

		$form_url = esc_url(
			add_query_arg(
				array(
					'page'   => 'cartflows',
					'action' => 'wcf-log',
				),
				admin_url( '/admin.php' )
			)
		);

		$viewed_log      = '';
		$viewed_log_file = '';

		// Calling this function on CartFlows action hook. Hence ignoring nonce.
		if ( ! empty( $_REQUEST['log_file'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$filename = sanitize_text_field( wp_unslash( $_REQUEST['log_file'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( isset( $logs[ $filename ] ) ) {
				$viewed_log      = $filename;
				$viewed_log_file = $viewed_log . '.log';
			}
		} elseif ( ! empty( $logs ) ) {
			$viewed_log      = current( $logs ) ? pathinfo( current( $logs ), PATHINFO_FILENAME ) : '';
			$viewed_log_file = $viewed_log . '.log';
		}

		include_once CARTFLOWS_ADMIN_CORE_DIR . 'views/debugger.php';
	}

	/**
	 * Get all log files in the log directory.
	 *
	 * @return array
	 */
	public function get_log_files() {
		$files  = scandir( CARTFLOWS_LOG_DIR );
		$result = array();

		if ( ! empty( $files ) ) {
			foreach ( $files as $key => $file ) {
				if ( ! is_dir( $file ) && strstr( $file, '.log' ) ) {
					$result[ pathinfo( $file, PATHINFO_FILENAME ) ] = $file;
				}
			}
		}

		return $result;
	}

	/**
	 * Delete Provided log file
	 */
	public function delete_log_file() {

		if ( empty( $_REQUEST['_wpnonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'remove_log' )
		) {
			wp_die( esc_html__( 'Nonce verification failed. Please refresh the page and retry.', 'cartflows' ) );
		}

		if ( empty( $_REQUEST['handle'] ) ) {
			wp_die( esc_html__( 'Filename is empty. Please refresh the page and retry.', 'cartflows' ) );
		}

		$file_name = trim( sanitize_text_field( wp_unslash( $_REQUEST['handle'] ) ) );
		$file_path = CARTFLOWS_LOG_DIR . $file_name;

		if ( file_exists( $file_path ) ) {
			wp_delete_file( $file_path );
			self::$file_deleted = true;
		}
	}

	/**
	 * Download the selected log file.
	 */
	public function download_log_file() {

		if ( empty( $_REQUEST['_wpnonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'download_log' )
		) {
			wp_die( esc_html__( 'Nonce verification failed. Please refresh the page and retry.', 'cartflows' ) );
		}

		$file_name = isset( $_REQUEST['handle'] ) ? trim( sanitize_text_field( wp_unslash( $_REQUEST['handle'] ) ) ) : '';
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

		header( 'Content-Type: text/log; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $file_name );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		echo file_get_contents( $file_path ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

}

LogStatus::get_instance();
