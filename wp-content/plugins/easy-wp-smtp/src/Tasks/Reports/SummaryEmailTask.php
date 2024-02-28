<?php

namespace EasyWPSMTP\Tasks\Reports;

use EasyWPSMTP\Tasks\Tasks;
use EasyWPSMTP\WP;
use EasyWPSMTP\Tasks\Task;
use EasyWPSMTP\Reports\Emails\Summary as SummaryReportEmail;

/**
 * Class SummaryEmailTask.
 *
 * @since 2.1.0
 */
class SummaryEmailTask extends Task {

	/**
	 * Action name for this task.
	 *
	 * @since 2.1.0
	 */
	const ACTION = 'easy_wp_smtp_summary_report_email';

	/**
	 * Class constructor.
	 *
	 * @since 2.1.0
	 */
	public function __construct() {

		parent::__construct( self::ACTION );
	}

	/**
	 * Initialize the task with all the proper checks.
	 *
	 * @since 2.1.0
	 */
	public function init() {

		// Register the action handler.
		add_action( self::ACTION, array( $this, 'process' ) );

		$is_disabled = SummaryReportEmail::is_disabled();

		// Exit if summary report email is disabled or this task is already scheduled.
		if ( ! empty( $is_disabled ) || Tasks::is_scheduled( self::ACTION ) !== false ) {
			return;
		}

		$date = new \DateTime( 'next monday 2pm', WP::wp_timezone() );

		// Schedule the task.
		$this->recurring( $date->getTimestamp(), WEEK_IN_SECONDS )->register();
	}

	/**
	 * Process summary report email send.
	 *
	 * @since 2.1.0
	 *
	 * @param int $meta_id The Meta ID with the stored task parameters.
	 */
	public function process( $meta_id ) {

		// Prevent email sending if summary report email is disabled.
		if ( SummaryReportEmail::is_disabled() ) {
			return;
		}

		$reports = easy_wp_smtp()->get_reports();

		$email = $reports->get_summary_report_email();

		$email->send();
	}
}
