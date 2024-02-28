<?php

namespace EasyWPSMTP\Migrations;

use EasyWPSMTP;
use EasyWPSMTP\Tasks\Meta;

/**
 * Class Migration helps migrate plugin options, DB tables and more.
 *
 * @since 2.0.0
 */
class GeneralMigration extends MigrationAbstract {

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
	const OPTION_NAME = 'easy_wp_smtp_migration_version';

	/**
	 * Option key where we save any errors while performing migration.
	 *
	 * @since 2.0.0
	 */
	const ERROR_OPTION_NAME = 'easy_wp_smtp_migration_error';

	/**
	 * Migration from 1.x to 2.0.0.
	 * Create Tasks\Meta table, if it does not exist.
	 *
	 * @since 2.0.0
	 */
	protected function migrate_to_1() {

		$meta = new Meta();

		// Create the table if it doesn't exist.
		if ( $meta && ! $meta->table_exists() ) {
			$meta->create_table();
		}

		$this->update_db_ver( 1 );
	}
}
