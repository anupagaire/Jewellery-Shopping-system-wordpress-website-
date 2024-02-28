<?php

namespace EasyWPSMTP;

use WP_Error;
use EasyWPSMTP\Admin\PluginsInstallSkin;
use EasyWPSMTP\Admin\PluginsInstallUpgrader;

/**
 * Easy WP SMTP Connect.
 *
 * Easy WP SMTP Connect is our service that makes it easy for non-techy users to
 * upgrade to Pro version without having to manually install Pro plugin.
 *
 * @since 2.1.0
 */
class Connect {

	/**
	 * Hooks.
	 *
	 * @since 2.1.0
	 */
	public function hooks() {

		add_action( 'easy_wp_smtp_admin_area_enqueue_assets', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_ajax_easy_wp_smtp_connect_url', [ $this, 'ajax_generate_url' ] );
		add_action( 'wp_ajax_nopriv_easy_wp_smtp_connect_process', [ $this, 'process' ] );
	}

	/**
	 * Enqueue connect JS file to Easy WP SMTP admin area hook.
	 *
	 * @since 2.1.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script(
			'easy-wp-smtp-connect',
			easy_wp_smtp()->assets_url . '/js/connect' . WP::asset_min() . '.js',
			[ 'jquery' ],
			EasyWPSMTP_PLUGIN_VERSION,
			true
		);

		wp_localize_script(
			'easy-wp-smtp-connect',
			'easy_wp_smtp_connect',
			[
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'plugin_url' => easy_wp_smtp()->plugin_url,
				'nonce'      => wp_create_nonce( 'easy-wp-smtp-connect' ),
				'text'       => [
					'plugin_activate_btn' => esc_html__( 'Activate', 'easy-wp-smtp' ),
					'almost_done'         => esc_html__( 'Almost Done', 'easy-wp-smtp' ),
					'oops'                => esc_html__( 'Oops!', 'easy-wp-smtp' ),
					'ok'                  => esc_html__( 'OK', 'easy-wp-smtp' ),
					'server_error'        => esc_html__( 'Unfortunately there was a server connection error.', 'easy-wp-smtp' ),
				],
			]
		);
	}

	/**
	 * Generate and return Easy WP SMTP Connect URL.
	 *
	 * @since 2.1.0
	 *
	 * @param string $key      The license key.
	 * @param string $oth      The One-time hash.
	 * @param string $redirect The redirect URL.
	 *
	 * @return bool|string
	 */
	public static function generate_url( $key, $oth = '', $redirect = '' ) {

		if ( empty( $key ) || easy_wp_smtp()->is_pro() ) {
			return false;
		}

		$oth        = ! empty( $oth ) ? $oth : hash( 'sha512', wp_rand() );
		$hashed_oth = hash_hmac( 'sha512', $oth, wp_salt() );

		$redirect = ! empty( $redirect ) ? $redirect : easy_wp_smtp()->get_admin()->get_admin_page_url();

		update_option( 'easy_wp_smtp_connect_token', $oth );
		update_option( 'easy_wp_smtp_connect', $key );

		return add_query_arg(
			[
				'key'      => $key,
				'oth'      => $hashed_oth,
				'endpoint' => admin_url( 'admin-ajax.php' ),
				'version'  => EasyWPSMTP_PLUGIN_VERSION,
				'siteurl'  => admin_url(),
				'homeurl'  => home_url(),
				'redirect' => rawurldecode( base64_encode( $redirect ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				'v'        => 2,
			],
			'https://upgrade.easywpsmtp.com'
		);
	}

	/**
	 * AJAX callback to generate and return the Easy WP SMTP Connect URL.
	 *
	 * @since 2.1.0
	 */
	public function ajax_generate_url() { //phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		// Run a security check.
		check_ajax_referer( 'easy-wp-smtp-connect', 'nonce' );

		// Check for permissions.
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'You are not allowed to install plugins.', 'easy-wp-smtp' ),
				]
			);
		}

		$key = ! empty( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';

		if ( empty( $key ) ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'Please enter your license key to connect.', 'easy-wp-smtp' ),
				]
			);
		}

		if ( easy_wp_smtp()->is_pro() ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'Only the Lite version can be upgraded.', 'easy-wp-smtp' ),
				]
			);
		}

		// Verify pro version is not installed.
		$active = activate_plugin( 'easy-wp-smtp-pro/easy_wp_smtp.php', false, false, true );

		if ( ! is_wp_error( $active ) ) {

			// Deactivate Lite.
			deactivate_plugins( plugin_basename( EasyWPSMTP_PLUGIN_FILE ) );

			wp_send_json_success(
				[
					'message' => esc_html__( 'Easy WP SMTP Pro was already installed, but was not active. We activated it for you.', 'easy-wp-smtp' ),
					'reload'  => true,
				]
			);
		}

		$url = self::generate_url( $key );

		if ( empty( $url ) ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'There was an error while generating an upgrade URL. Please try again.', 'easy-wp-smtp' ),
				]
			);
		}

		wp_send_json_success( [ 'url' => $url ] );
	}

	/**
	 * AJAX callback to process Easy WP SMTP Connect.
	 *
	 * @since 2.1.0
	 */
	public function process() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded

		$error = esc_html__( 'There was an error while installing an upgrade. Please download the plugin from easywpsmtp.com and install it manually.', 'easy-wp-smtp' );

		// Verify params present (oth & download link).
		$post_oth = ! empty( $_REQUEST['oth'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['oth'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$post_url = ! empty( $_REQUEST['file'] ) ? esc_url_raw( wp_unslash( $_REQUEST['file'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		if ( empty( $post_oth ) || empty( $post_url ) ) {
			wp_send_json_error( $error );
		}

		// Verify oth.
		$oth = get_option( 'easy_wp_smtp_connect_token' );

		if ( empty( $oth ) ) {
			wp_send_json_error( $error );
		}

		if ( hash_hmac( 'sha512', $oth, wp_salt() ) !== $post_oth ) {
			wp_send_json_error( $error );
		}

		// Delete so cannot replay.
		delete_option( 'easy_wp_smtp_connect_token' );

		// Set the current screen to avoid undefined notices.
		set_current_screen( 'toplevel_page_easy-wp-smtp' );

		// Prepare variables.
		$url = esc_url_raw( easy_wp_smtp()->get_admin()->get_admin_page_url() );

		// Verify pro not activated.
		if ( easy_wp_smtp()->is_pro() ) {
			wp_send_json_success( esc_html__( 'Plugin installed & activated.', 'easy-wp-smtp' ) );
		}

		// Verify pro not installed.
		$active = activate_plugin( 'easy-wp-smtp-pro/easy_wp_smtp.php', $url, false, true );

		if ( ! is_wp_error( $active ) ) {
			deactivate_plugins( plugin_basename( EasyWPSMTP_PLUGIN_FILE ) );
			wp_send_json_success( esc_html__( 'Plugin installed & activated.', 'easy-wp-smtp' ) );
		}

		$creds = request_filesystem_credentials( $url, '', false, false, null );

		// Check for file system permissions.
		$perm_error = esc_html__( 'There was an error while installing an upgrade. Please check file system permissions and try again. Also, you can download the plugin from easywpsmtp.com and install it manually.', 'easy-wp-smtp' );

		if ( false === $creds || ! WP_Filesystem( $creds ) ) {
			wp_send_json_error( $perm_error );
		}

		/*
		 * We do not need any extra credentials if we have gotten this far, so let's install the plugin.
		 */

		// Do not allow WordPress to search/download translations, as this will break JS output.
		remove_action( 'upgrader_process_complete', array( 'Language_Pack_Upgrader', 'async_upgrade' ), 20 );

		// Create the plugin upgrader with our custom skin.
		$installer = new PluginsInstallUpgrader( new PluginsInstallSkin() );

		// Error check.
		if ( ! method_exists( $installer, 'install' ) ) {
			wp_send_json_error( $error );
		}

		// Check license key.
		$key = get_option( 'easy_wp_smtp_connect', false );
		delete_option( 'easy_wp_smtp_connect' );

		if ( empty( $key ) ) {
			wp_send_json_error(
				new WP_Error(
					'403',
					esc_html__( 'There was an error while installing an upgrade. Please try again.', 'easy-wp-smtp' )
				)
			);
		}

		$installer->install( $post_url );

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();

		$plugin_basename = $installer->plugin_info();

		if ( $plugin_basename ) {

			// Deactivate the lite version first.
			deactivate_plugins( plugin_basename( EasyWPSMTP_PLUGIN_FILE ) );

			// Activate the plugin silently.
			$activated = activate_plugin( $plugin_basename, '', false, true );

			if ( ! is_wp_error( $activated ) ) {

				// Save the license data, since it was verified on the connect page.
				$options = Options::init();
				$all_opt = $options->get_all_raw();

				$all_opt['license']['key']         = $key;
				$all_opt['license']['type']        = 'pro';
				$all_opt['license']['is_expired']  = false;
				$all_opt['license']['is_disabled'] = false;
				$all_opt['license']['is_invalid']  = false;

				$options->set( $all_opt, false, true );

				wp_send_json_success( esc_html__( 'Plugin installed & activated.', 'easy-wp-smtp' ) );
			} else {
				// Reactivate the lite plugin if pro activation failed.
				activate_plugin( plugin_basename( EasyWPSMTP_PLUGIN_FILE ), '', false, true );
				wp_send_json_error( esc_html__( 'Pro version installed but needs to be activated on the Plugins page.', 'easy-wp-smtp' ) );
			}
		}

		wp_send_json_error( $error );
	}
}
