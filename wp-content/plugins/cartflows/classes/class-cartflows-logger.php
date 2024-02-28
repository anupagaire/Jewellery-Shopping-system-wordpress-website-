<?php
/**
 * Logger.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Initialization
 *
 * @since 1.0.0
 */
class Cartflows_Logger {


	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var logger
	 */
	public $logger;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 *  Constructor
	 */
	public function __construct() {

		/* Load WC Logger */
		add_action( 'init', array( $this, 'init_wc_logger' ), 99 );

		add_action( 'admin_init', array( $this, 'schedule_deletion_of_old_logs' ) );
		add_action( 'cartflows_delete_old_log_files', array( $this, 'delete_old_logs_files' ) );
	}

	/**
	 * Init Logger.
	 *
	 * @since 1.0.0
	 */
	public function init_wc_logger() {
		if ( class_exists( 'CartFlows_WC_Logger' ) ) {
			$this->logger = new CartFlows_WC_Logger();
		}
	}

	/**
	 * Enable log.
	 *
	 * @since 1.7.2
	 */
	public function is_log_enable() {
		return apply_filters( 'cartflows_enable_log', 'enable' );
	}

	/**
	 * Write log
	 *
	 * @param string $message log message.
	 * @param string $level type of log.
	 * @since 1.0.0
	 */
	public function log( $message, $level = 'info' ) {

		if ( 'enable' === $this->is_log_enable() &&
			is_a( $this->logger, 'CartFlows_WC_Logger' ) &&
			did_action( 'plugins_loaded' )
		) {

			$this->logger->log( $level, $message, array( 'source' => 'cartflows' ) );
		}
	}

	/**
	 * Write log
	 *
	 * @param string $message log message.
	 * @param string $level type of log.
	 * @since 1.0.0
	 */
	public function import_log( $message, $level = 'info' ) {

		if ( 'enable' === $this->is_log_enable() && defined( 'WP_DEBUG' ) &&
			WP_DEBUG &&
			is_a( $this->logger, 'CartFlows_WC_Logger' ) &&
			did_action( 'plugins_loaded' )
		) {

			$this->logger->log( $level, $message, array( 'source' => 'cartflows-import' ) );
		}
	}

	/**
	 * Migration log
	 *
	 * @param string $message migration message.
	 * @param string $level type of log.
	 * @since 1.7.0
	 */
	public function migration_log( $message, $level = 'info' ) {

		if ( 'enable' === $this->is_log_enable() && defined( 'WP_DEBUG' ) &&
			WP_DEBUG &&
			is_a( $this->logger, 'CartFlows_WC_Logger' ) &&
			did_action( 'plugins_loaded' )
		) {

			$this->logger->log( $level, $message, array( 'source' => 'cartflows-migration' ) );
		}
	}

	/**
	 * Sync log
	 *
	 * @param string $message log message.
	 * @param string $level type of log.
	 * @since 1.0.0
	 */
	public function sync_log( $message, $level = 'info' ) {

		if ( 'enable' === $this->is_log_enable() && defined( 'WP_DEBUG' ) &&
			WP_DEBUG &&
			is_a( $this->logger, 'CartFlows_WC_Logger' ) &&
			did_action( 'plugins_loaded' )
		) {

			$this->logger->log( $level, $message, array( 'source' => 'cartflows-sync' ) );
		}
	}

	/**
	 * Schedule the action to delete the CartFlows log files on monthly basis.
	 *
	 * @return void
	 */
	public function schedule_deletion_of_old_logs() {

		if ( ! function_exists( 'as_next_scheduled_action' ) ) {
			return;
		}

		$is_logging_enabled = $this->is_log_enable();

		if ( 'enable' === $is_logging_enabled && false === as_next_scheduled_action( 'cartflows_delete_old_log_files' ) ) {

			$date = new DateTime( 'last day of this month 12am' );

			// It will automatically reschedule the action once initiated.
			as_schedule_recurring_action( $date, MONTH_IN_SECONDS, 'cartflows_delete_old_log_files' );

		} elseif ( 'enable' !== $is_logging_enabled && as_next_scheduled_action( 'cartflows_delete_old_log_files' ) ) {
			as_unschedule_all_actions( 'cartflows_delete_old_log_files' );
		}
	}

	/**
	 * Delete the CartFlows logs files.
	 * This action will be executed on first day of each month.
	 */
	public function delete_old_logs_files() {

		if ( 'enable' === $this->is_log_enable() ) {

			$log_status = new CartflowsAdmin\AdminCore\Inc\LogStatus();
			$logs       = $log_status->get_log_files();

			if ( empty( $logs ) || ! is_array( $logs ) ) {
				return;
			}

			foreach ( $logs as $file_name ) {
				$file_path = CARTFLOWS_LOG_DIR . $file_name;

				if ( ! file_exists( $file_path ) ) {
					continue;
				}

				wp_delete_file( $file_path );
			}
		}
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Logger::get_instance();
