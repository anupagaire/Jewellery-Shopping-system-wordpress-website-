<?php

namespace EasyWPSMTP\Admin\Pages;

use EasyWPSMTP\ConnectionInterface;
use EasyWPSMTP\Providers\AuthAbstract;

/**
 * Class AuthTab.
 *
 * @since 2.1.0
 */
class AuthTab {

	/**
	 * @var string Slug of a tab.
	 */
	protected $slug = 'auth';

	/**
	 * Launch mailer specific Auth logic.
	 *
	 * @since 2.1.0
	 */
	public function process_auth() {

		$connection = easy_wp_smtp()->get_connections_manager()->get_primary_connection();

		/**
		 * Filters auth connection object.
		 *
		 * @since 2.1.0
		 *
		 * @param ConnectionInterface $connection The Connection object.
		 */
		$connection = apply_filters( 'easy_wp_smtp_admin_pages_auth_tab_process_auth_connection', $connection );

		$auth = easy_wp_smtp()->get_providers()->get_auth( $connection->get_mailer_slug(), $connection );

		if (
			$auth &&
			$auth instanceof AuthAbstract &&
			method_exists( $auth, 'process' )
		) {
			$auth->process();
		}
	}

	/**
	 * Return nothing, as we don't need this functionality.
	 *
	 * @since 2.1.0
	 */
	public function get_label() {
		return '';
	}

	/**
	 * Return nothing, as we don't need this functionality.
	 *
	 * @since 2.1.0
	 */
	public function get_title() {
		return '';
	}

	/**
	 * Do nothing, as we don't need this functionality.
	 *
	 * @since 2.1.0
	 */
	public function display() {
	}
}
