<?php
/**
 * Uninstall all Easy WP SMTP data.
 *
 * @since 2.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Prevent data removal if Pro plugin is active.
if ( is_plugin_active( 'easy-wp-smtp-pro/easy-wp-smtp.php' ) ) {
	return;
}

// Load plugin file.
require_once 'easy-wp-smtp.php';
require_once dirname( __FILE__ ) . '/vendor/woocommerce/action-scheduler/action-scheduler.php';

global $wpdb;

/*
 * Remove Legacy options.
 */
$options = [
	'swpsmtp_options',
	'swpsmtp_enc_key',
	'swpsmtp_pass_encrypted',
	'smtp_test_mail',
];

/**
 * Disable Action Schedule Queue Runner, to prevent a fatal error on the shutdown WP hook.
 */
if ( class_exists( 'ActionScheduler_QueueRunner' ) ) {
	$as_queue_runner = \ActionScheduler_QueueRunner::instance();

	if ( method_exists( $as_queue_runner, 'unhook_dispatch_async_request' ) ) {
		$as_queue_runner->unhook_dispatch_async_request();
	}
}

// WP MS uninstall process.
if ( is_multisite() ) {
	$sites = get_sites();

	foreach ( $sites as $site ) {
		$settings = get_blog_option( $site->blog_id, 'easy_wp_smtp', [] );

		// Confirm user has decided to remove all data, otherwise stop.
		if ( empty( $settings['general']['uninstall'] ) ) {
			continue;
		}

		/*
		 * Delete network site plugin options.
		 */
		foreach ( $options as $option ) {
			delete_blog_option( $site->blog_id, $option );
		}

		// Switch to the current network site.
		switch_to_blog( $site->blog_id );

		// Delete plugin settings.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'easy\_wp\_smtp%'" ); // phpcs:ignore WordPress.DB

		// Delete plugin user meta.
		$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'easy\_wp\_smtp\_%'" ); // phpcs:ignore WordPress.DB

		// Remove any transients we've left behind.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_easy\_wp\_smtp\_%'" ); // phpcs:ignore WordPress.DB
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_site\_transient\_easy\_wp\_smtp\_%'" ); // phpcs:ignore WordPress.DB
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_timeout\_easy\_wp\_smtp\_%'" ); // phpcs:ignore WordPress.DB
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_site\_transient\_timeout\_easy\_wp\_smtp\_%'" ); // phpcs:ignore WordPress.DB

		// Delete debug events table.
		$debug_events_table = \EasyWPSMTP\Admin\DebugEvents\DebugEvents::get_table_name();
		$wpdb->query( "DROP TABLE IF EXISTS $debug_events_table;" ); // phpcs:ignore WordPress.DB

		/*
		 * Cleanup Pro plugin data.
		 */
		if (
			function_exists( 'easy_wp_smtp' ) &&
			is_readable( easy_wp_smtp()->plugin_path . '/src/Pro/Pro.php' )
		) {

			// Delete logs table.
			$table = \EasyWPSMTP\Pro\Emails\Logs\Logs::get_table_name();
			$wpdb->query( "DROP TABLE IF EXISTS $table;" ); // phpcs:ignore WordPress.DB

			// Delete attachments tables.
			$attachment_files_table = \EasyWPSMTP\Pro\Emails\Logs\Attachments\Attachments::get_attachment_files_table_name();
			$wpdb->query( "DROP TABLE IF EXISTS $attachment_files_table;" ); // phpcs:ignore WordPress.DB

			$email_attachments_table = \EasyWPSMTP\Pro\Emails\Logs\Attachments\Attachments::get_email_attachments_table_name();
			$wpdb->query( "DROP TABLE IF EXISTS $email_attachments_table;" ); // phpcs:ignore WordPress.DB

			// Delete all attachments if any.
			( new \EasyWPSMTP\Pro\Emails\Logs\Attachments\Attachments() )->delete_all_attachments();

			// Delete tracking tables.
			$tracking_events_table = \EasyWPSMTP\Pro\Emails\Logs\Tracking\Tracking::get_events_table_name();
			$wpdb->query( "DROP TABLE IF EXISTS $tracking_events_table;" ); // phpcs:ignore WordPress.DB

			$tracking_links_table = \EasyWPSMTP\Pro\Emails\Logs\Tracking\Tracking::get_links_table_name();
			$wpdb->query( "DROP TABLE IF EXISTS $tracking_links_table;" ); // phpcs:ignore WordPress.DB
		}

		/*
		 * Drop all Action Scheduler data and unschedule all plugin ActionScheduler actions.
		 */
		( new \EasyWPSMTP\Tasks\Tasks() )->remove_all();

		$meta_table = \EasyWPSMTP\Tasks\Meta::get_table_name();
		$wpdb->query( "DROP TABLE IF EXISTS $meta_table;" ); // phpcs:ignore WordPress.DB

		// Restore the current network site back to the original one.
		restore_current_blog();
	}
} else { // Non WP MS uninstall process (for normal WP installs).

	// Confirm user has decided to remove all data, otherwise stop.
	$settings = get_option( 'easy_wp_smtp', [] );
	if ( empty( $settings['general']['uninstall'] ) ) {
		return;
	}

	/*
	 * Delete plugin options.
	 */
	foreach ( $options as $option ) {
		delete_option( $option );
	}

	// Delete plugin settings.
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'easy\_wp\_smtp%'" ); // phpcs:ignore WordPress.DB

	// Delete plugin user meta.
	$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'easy\_wp\_smtp\_%'" ); // phpcs:ignore WordPress.DB

	// Remove any transients we've left behind.
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_easy\_wp\_smtp\_%'" ); // phpcs:ignore WordPress.DB
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_site\_transient\_easy\_wp\_smtp\_%'" ); // phpcs:ignore WordPress.DB
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_timeout\_easy\_wp\_smtp\_%'" ); // phpcs:ignore WordPress.DB
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_site\_transient\_timeout\_easy\_wp\_smtp\_%'" ); // phpcs:ignore WordPress.DB

	// Delete debug events table.
	$debug_events_table = \EasyWPSMTP\Admin\DebugEvents\DebugEvents::get_table_name();
	$wpdb->query( "DROP TABLE IF EXISTS $debug_events_table;" ); // phpcs:ignore WordPress.DB

	/*
	 * Cleanup Pro plugin data.
	 */
	if (
		function_exists( 'easy_wp_smtp' ) &&
		is_readable( easy_wp_smtp()->plugin_path . '/src/Pro/Pro.php' )
	) {

		// Delete logs table.
		$table = \EasyWPSMTP\Pro\Emails\Logs\Logs::get_table_name();
		$wpdb->query( "DROP TABLE IF EXISTS $table;" ); // phpcs:ignore WordPress.DB

		// Delete attachments tables.
		$attachment_files_table = \EasyWPSMTP\Pro\Emails\Logs\Attachments\Attachments::get_attachment_files_table_name();
		$wpdb->query( "DROP TABLE IF EXISTS $attachment_files_table;" ); // phpcs:ignore WordPress.DB

		$email_attachments_table = \EasyWPSMTP\Pro\Emails\Logs\Attachments\Attachments::get_email_attachments_table_name();
		$wpdb->query( "DROP TABLE IF EXISTS $email_attachments_table;" ); // phpcs:ignore WordPress.DB

		// Delete all attachments if any.
		( new \EasyWPSMTP\Pro\Emails\Logs\Attachments\Attachments() )->delete_all_attachments();

		// Delete tracking tables.
		$tracking_events_table = \EasyWPSMTP\Pro\Emails\Logs\Tracking\Tracking::get_events_table_name();
		$wpdb->query( "DROP TABLE IF EXISTS $tracking_events_table;" ); // phpcs:ignore WordPress.DB

		$tracking_links_table = \EasyWPSMTP\Pro\Emails\Logs\Tracking\Tracking::get_links_table_name();
		$wpdb->query( "DROP TABLE IF EXISTS $tracking_links_table;" ); // phpcs:ignore WordPress.DB
	}

	/*
	 * Drop all Action Scheduler data and unschedule all plugin ActionScheduler actions.
	 */
	( new \EasyWPSMTP\Tasks\Tasks() )->remove_all();

	$meta_table = \EasyWPSMTP\Tasks\Meta::get_table_name();
	$wpdb->query( "DROP TABLE IF EXISTS $meta_table;" ); // phpcs:ignore WordPress.DB
}
