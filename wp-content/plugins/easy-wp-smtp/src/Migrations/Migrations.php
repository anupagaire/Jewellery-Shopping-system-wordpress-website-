<?php

namespace EasyWPSMTP\Migrations;

use EasyWPSMTP\Admin\DebugEvents\Migration as DebugEventsMigration;
use EasyWPSMTP\WP;

/**
 * Class Migrations.
 *
 * @since 2.0.0
 */
class Migrations {

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function hooks() {

		// Initialize DB migrations.
		add_action( 'admin_init', [ $this, 'init' ] );

		// Run deprecated options migration manually via GET param.
		add_action( 'admin_init', [ $this, 'maybe_run_deprecated_options_migration' ] );
	}

	/**
	 * Initialize DB migrations.
	 *
	 * @since 2.0.0
	 */
	public function init() {

		if ( WP::is_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		$migrations = [
			DeprecatedOptionsMigration::class,
			GeneralMigration::class,
			DebugEventsMigration::class,
		];

		/**
		 * Filters DB migrations.
		 *
		 * @since 2.0.0
		 *
		 * @param array $migrations Migrations classes.
		 */
		$migrations = apply_filters( 'easy_wp_smtp_migrations_init', $migrations );

		foreach ( $migrations as $migration ) {
			if ( is_subclass_of( $migration, MigrationAbstract::class ) && $migration::is_enabled() ) {
				( new $migration() )->init();
			}
		}
	}

	/**
	 * Run deprecated options migration manually via GET parameter.
	 *
	 * @since 2.0.0
	 */
	public function maybe_run_deprecated_options_migration() {

		if (
			current_user_can( 'manage_options' ) &&
			isset( $_GET['page'] ) && $_GET['page'] === 'easy-wp-smtp' &&
			isset( $_GET['easy-wp-smtp-migrate-deprecated-options'] )
		) {
			if ( empty( get_option( 'swpsmtp_options' ) ) ) {
				WP::add_admin_notice( esc_html__( 'Deprecated options were already removed from DB and can\'t be migrated.', 'easy-wp-smtp' ), WP::ADMIN_NOTICE_ERROR );

				return;
			}

			( new DeprecatedOptionsMigration() )->migrate_to_1( true );

			wp_safe_redirect( easy_wp_smtp()->get_admin()->get_admin_page_url() );
			exit();
		}
	}
}
