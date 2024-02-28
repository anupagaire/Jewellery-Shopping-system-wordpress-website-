<?php
/**
 * Email.
 *
 * @package CartFlows.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use CartflowsAdmin\AdminCore\Inc\AdminHelper;
/**
 * Class Cartflows_Admin_Report_Emails.
 */
class Cartflows_Admin_Report_Emails {


	/**
	 * Class instance.
	 *
	 * @access private
	 * @var $instance Class instance.
	 */
	private static $instance;

		/**
		 * Initiator
		 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Constructor.
	 */
	public function __construct() {

		// It will run once.
		add_action( 'admin_init', array( $this, 'schedule_weekly_report_email' ) );

		add_action( 'cartflows_send_report_summary_email', array( $this, 'send_weekly_report_email' ) );

		add_filter( 'admin_init', array( $this, 'unsubscribe_cartflows_weekly_emails' ), 10 );

	}

	/**
	 * Schedule the weekly email.
	 */
	public function schedule_weekly_report_email() {

		$is_report_emails = get_option( 'cartflows_stats_report_emails', 'enable' );

		if ( 'enable' === $is_report_emails && function_exists( 'as_next_scheduled_action' ) && false === as_next_scheduled_action( 'cartflows_send_report_summary_email' ) ) {

			wcf()->logger->log( 'Is weekly report enabled: ' . $is_report_emails );

			$date = new DateTime( 'next monday 2pm' );

			// It will automatically reschedule the action once initiated.
			as_schedule_recurring_action( $date, WEEK_IN_SECONDS, 'cartflows_send_report_summary_email' );

			wcf()->logger->log( 'Weekly report action scheduled. Action: cartflows_send_report_summary_email ' );
		} elseif ( 'enable' !== $is_report_emails && as_next_scheduled_action( 'cartflows_send_report_summary_email' ) ) {
			as_unschedule_all_actions( 'cartflows_send_report_summary_email' );

			wcf()->logger->log( 'Weekly report action unscheduled. Action: cartflows_send_report_summary_email ' );
		}
	}

	/**
	 * Send weekly report email.
	 */
	public function send_weekly_report_email() {

		$is_report_emails = get_option( 'cartflows_stats_report_emails', 'enable' );

		$emails = get_option( 'cartflows_stats_report_email_ids', get_option( 'admin_email' ) );

		wcf()->logger->log( 'Start-' . __CLASS__ . '::' . __FUNCTION__ );

		if ( 'enable' === $is_report_emails && ! empty( $emails ) && apply_filters( 'cartflows_send_weekly_report_email', true ) ) {

			$stats = $this->get_last_week_stats();

			wcf()->logger->log( 'Send weekly emails to ' . $emails );
			wcf()->logger->log( 'Total Revenue: ' . $stats['total_revenue'] );

			if ( isset( $stats['total_revenue'] ) && $stats['total_revenue'] > 0 ) {

				$subject  = $this->get_email_subject();
				$headers  = 'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>' . "\r\n";
				$headers .= "Content-Type: text/html;\r\n";

				$emails = preg_split( "/[\f\r\n]+/", $emails );

				foreach ( $emails as $email_id ) {
					$user_info  = get_user_by( 'email', $email_id );
					$name       = $user_info ? $user_info->display_name : __( 'There', 'cartflows' );
					$email_body = $this->get_email_content( $stats, $name, $email_id );
					$status     = wp_mail( $email_id, $subject, stripslashes( $email_body ), $headers ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_mail_wp_mail
					wcf()->logger->log( 'Email send status for email ' . $email_id . ' is ' . $status );
				}
			}
		}
	}

		/**
		 *  Unsubscribe the user from the mailing list.
		 */
	public function unsubscribe_cartflows_weekly_emails() {

		$unsubscribe = filter_input( INPUT_GET, 'unsubscribe_weekly_email', FILTER_VALIDATE_BOOLEAN );
		$page        = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$email       = filter_input( INPUT_GET, 'email', FILTER_SANITIZE_EMAIL );

		if ( $unsubscribe && 'cartflows' === $page && ! empty( $email ) && is_user_logged_in() && current_user_can( 'cartflows_manage_settings' ) ) {

			$email_list = get_option( 'cartflows_stats_report_email_ids', false );

			if ( ! empty( $email_list ) ) {
				$email_list = preg_split( "/[\f\r\n]+/", $email_list );

				$email_list = array_filter(
					$email_list,
					function( $e ) use ( $email ) {
						return ( $e !== $email );
					}
				);

				$email_list = implode( "\n", $email_list );

				update_option( 'cartflows_stats_report_email_ids', $email_list );
				wcf()->logger->log( 'Email unsubscribed: ' . $email );
			}

			wp_die( esc_html__( 'You have successfully unsubscribed from our weekly emails list.', 'cartflows' ), esc_html__( 'Unsubscribed', 'cartflows' ) );
		}

	}

	/**
	 *  Get the stats mention in to email.
	 */
	public function get_last_week_stats() {

		$start_date = gmdate( 'Y-m-d', strtotime( '-7 days' ) );
		$end_date   = gmdate( 'Y-m-d' );

		return AdminHelper::get_earnings( $start_date, $end_date );
	}

	/**
	 *  Get the stats mention in to email.
	 */
	public function get_last_month_stats() {

		$start_date = gmdate( 'Y-m-d', strtotime( '-30 days' ) );
		$end_date   = gmdate( 'Y-m-d' );

		return AdminHelper::get_earnings( $start_date, $end_date );
	}

	/**
	 * Get admin report email subject.
	 */
	public function get_email_subject() {

		return esc_html__( 'Here’s how your store performed last week!', 'cartflows' );

	}

	/**
	 *  Get admin report email content.
	 *
	 * @param array  $stats reports details.
	 * @param string $user_name user name.
	 * @param string $email_id email id.
	 */
	public function get_email_content( $stats, $user_name, $email_id ) {

		$cf_logo            = CARTFLOWS_URL . 'assets/images/cartflows-email-logo.png';
		$unsubscribe_link   = add_query_arg(
			array(
				'page'                     => 'cartflows',
				'unsubscribe_weekly_email' => true,
				'email'                    => $email_id,
			),
			admin_url( 'admin.php' )
		);
		$facebook_icon      = CARTFLOWS_URL . 'assets/images/facebook2x.png';
		$twitter_icon       = CARTFLOWS_URL . 'assets/images/twitter2x.png';
		$youtube_icon       = CARTFLOWS_URL . 'assets/images/youtube2x.png';
		$from_date          = gmdate( 'M j', strtotime( '-7 days' ) );
		$to_date            = gmdate( 'M j' );
		$total_orders       = $stats['total_orders'];
		$total_visits       = $stats['total_visits'];
		$order_bump_revenue = $stats['total_bump_revenue'];
		$offers_revenue     = $stats['total_offers_revenue'];
		$lock_icon          = CARTFLOWS_URL . 'assets/images/lock.png';

		$total_revenue      = $stats['total_revenue'];
		$last_month_stats   = $this->get_last_month_stats();
		$last_month_revenue = $last_month_stats['total_revenue'];
		$store_name         = get_bloginfo( 'name' );

		return include CARTFLOWS_DIR . 'modules/email-report/templates/email-body.php';
	}
}

Cartflows_Admin_Report_Emails::get_instance();
