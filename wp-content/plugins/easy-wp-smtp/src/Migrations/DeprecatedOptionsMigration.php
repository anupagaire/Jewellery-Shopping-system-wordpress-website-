<?php

namespace EasyWPSMTP\Migrations;

use EasyWPSMTP\Options;

/**
 * Class Migration helps migrate deprecated plugin options.
 *
 * @since 2.0.0
 */
class DeprecatedOptionsMigration extends MigrationAbstract {

	/**
	 * Version of the latest migration.
	 *
	 * @since 2.0.0
	 */
	const DB_VERSION = 1;

	/**
	 * Option key where we save the current migration version.
	 *
	 * @since 2.0.0
	 */
	const OPTION_NAME = 'easy_wp_smtp_deprecated_options_migration_version';

	/**
	 * Option key where we save any errors while performing migration.
	 *
	 * @since 2.0.0
	 */
	const ERROR_OPTION_NAME = 'easy_wp_smtp_deprecated_options_migration_error';

	/**
	 * Migration from 1.x to 2.0.0.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $force Whether to force migration execution even if new options were already saved.
	 */
	public function migrate_to_1( $force = false ) {

		$current_options = get_option( Options::META_KEY, [] );

		// Skip migration if new options was already set.
		if ( ( ! $force && ! empty( $current_options ) ) || empty( get_option( 'swpsmtp_options' ) ) ) {
			$this->update_db_ver( 1 );

			return;
		}

		$new_values = ( new DeprecatedOptionsConverter() )->get_converted_options();

		Options::init()->set( $new_values );

		// Migrate test email saved data to new non auto-loaded option.
		$test_email = get_option( 'smtp_test_mail' );

		if ( ! empty( $test_email ) ) {
			add_option(
				'easy_wp_smtp_test_email',
				[
					'to'      => isset( $test_email['swpsmtp_to'] ) ? $test_email['swpsmtp_to'] : '',
					'subject' => isset( $test_email['swpsmtp_subject'] ) ? $test_email['swpsmtp_subject'] : '',
					'message' => isset( $test_email['swpsmtp_message'] ) ? $test_email['swpsmtp_message'] : '',
				],
				'',
				false
			);
		}

		$this->update_db_ver( 1 );
	}
}
