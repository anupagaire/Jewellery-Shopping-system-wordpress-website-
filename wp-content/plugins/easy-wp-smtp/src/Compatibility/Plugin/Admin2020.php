<?php

namespace EasyWPSMTP\Compatibility\Plugin;

/**
 * Admin 2020 Lite compatibility plugin.
 *
 * @since 2.1.0
 */
class Admin2020 extends PluginAbstract {

	/**
	 * Get plugin name.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public static function get_name() {

		return 'Admin 2020';
	}

	/**
	 * Get plugin path.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public static function get_path() {

		return 'admin-2020/admin-2020.php';
	}

	/**
	 * Execute on init action in admin area.
	 *
	 * @since 2.1.0
	 */
	public function load_admin() {

		add_action( 'easy_wp_smtp_admin_setup_wizard_load_setup_wizard_before', [ $this, 'disable_admin_bar' ] );
	}

	/**
	 * Disable admin bar on Setup Wizard page.
	 *
	 * @since 2.1.0
	 */
	public function disable_admin_bar() {

		global $wp_admin_bar;
		$wp_admin_bar = ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}
}
