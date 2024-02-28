<?php
/**
 * Plugin Name: Easy WP SMTP
 * Version: 2.2.0
 * Requires at least: 5.2
 * Requires PHP: 5.6.20
 * Plugin URI: https://easywpsmtp.com/
 * Author: Easy WP SMTP
 * Author URI: https://easywpsmtp.com/
 * Description: Fix your WordPress email delivery by sending them via a transactional email provider or an SMTP server.
 * Text Domain: easy-wp-smtp
 * Domain Path: /assets/languages
 */

if ( ! defined( 'EasyWPSMTP_PLUGIN_VERSION' ) ) {
	define( 'EasyWPSMTP_PLUGIN_VERSION', '2.2.0' );
}
if ( ! defined( 'EasyWPSMTP_PHP_VERSION' ) ) {
	define( 'EasyWPSMTP_PHP_VERSION', '5.6.20' );
}
if ( ! defined( 'EasyWPSMTP_WP_VERSION' ) ) {
	define( 'EasyWPSMTP_WP_VERSION', '5.2' );
}
if ( ! defined( 'EasyWPSMTP_PLUGIN_FILE' ) ) {
	define( 'EasyWPSMTP_PLUGIN_FILE', __FILE__ );
}

/**
 * Don't allow multiple versions (Lite and Pro) to be active.
 *
 * @since 2.1.0
 */
if ( function_exists( 'easy_wp_smtp' ) || class_exists( 'EasyWPSMTP' ) ) {

	if ( ! function_exists( 'easy_wp_smtp_lite_deactivate' ) ) {
		/**
		 * Deactivate Lite version.
		 *
		 * @since 2.1.0
		 */
		function easy_wp_smtp_lite_deactivate() {

			require_once ABSPATH . WPINC . '/pluggable.php';

			$plugin = 'easy-wp-smtp/easy-wp-smtp.php';

			deactivate_plugins( $plugin );
		}
	}

	/**
	 * When we activate a Pro version, we need to deactivate a Lite version.
	 *
	 * @since 2.1.0
	 */
	add_action( 'activate_easy-wp-smtp-pro/easy-wp-smtp.php', 'easy_wp_smtp_lite_deactivate' );

	/**
	 * If a Pro version already loaded, we need to deactivate a Lite version.
	 *
	 * @since 2.1.0
	 */
	add_action( 'admin_init', 'easy_wp_smtp_lite_deactivate' );

	/**
	 * Display notice if user wants to activate the Lite when Pro is active.
	 */

	if ( ! function_exists( 'easy_wp_smtp_lite_just_activated' ) ) {
		/**
		 * Store temporarily that the Lite version of the plugin was activated.
		 * This is needed because WP does a redirect after activation and
		 * we need to preserve this state to know whether user activated Lite or not.
		 *
		 * @since 2.1.0
		 */
		function easy_wp_smtp_lite_just_activated() {

			set_transient( 'easy_wp_smtp_lite_just_activated', true );
		}
	}
	add_action( 'activate_easy-wp-smtp/easy-wp-smtp.php', 'easy_wp_smtp_lite_just_activated' );

	if ( ! function_exists( 'easy_wp_smtp_lite_just_deactivated' ) ) {
		/**
		 * Store temporarily that Lite plugin was deactivated.
		 * Convert temporary "activated" value to a global variable,
		 * so it is available through the request. Remove from the storage.
		 *
		 * @since 2.1.0
		 */
		function easy_wp_smtp_lite_just_deactivated() {

			global $easy_wp_smtp_lite_just_activated, $easy_wp_smtp_lite_just_deactivated;

			$easy_wp_smtp_lite_just_activated   = (bool) get_transient( 'easy_wp_smtp_lite_just_activated' );
			$easy_wp_smtp_lite_just_deactivated = true;

			delete_transient( 'easy_wp_smtp_lite_just_activated' );
		}
	}
	add_action( 'deactivate_easy-wp-smtp/easy-wp-smtp.php', 'easy_wp_smtp_lite_just_deactivated' );

	if ( ! function_exists( 'easy_wp_smtp_lite_notice' ) ) {
		/**
		 * Display the notice after Lite deactivation when Pro is still active
		 * and user wanted to activate the Lite version of the plugin.
		 *
		 * @since 2.1.0
		 */
		function easy_wp_smtp_lite_notice() {

			global $easy_wp_smtp_lite_just_activated, $easy_wp_smtp_lite_just_deactivated;

			if (
				empty( $easy_wp_smtp_lite_just_activated ) ||
				empty( $easy_wp_smtp_lite_just_deactivated )
			) {
				return;
			}

			// Currently tried to activate Lite with Pro still active, so display the message.
			printf(
				'<div class="notice notice-warning">
					<p>%1$s</p>
					<p>%2$s</p>
				</div>',
				esc_html__( 'Heads up!', 'easy_wp_smtp-lite' ),
				esc_html__( 'Your site already has Easy WP SMTP Pro activated. If you want to switch to Easy WP SMTP, please first go to Plugins → Installed Plugins and deactivate Easy WP SMTP Pro. Then, you can activate Easy WP SMTP.', 'easy-wp-smtp' )
			);

			if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				unset( $_GET['activate'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}

			unset( $easy_wp_smtp_lite_just_activated, $easy_wp_smtp_lite_just_deactivated );
		}
	}
	add_action( 'admin_notices', 'easy_wp_smtp_lite_notice' );

	// Do not process the plugin code further.
	return;
}

if ( ! function_exists( 'easy_wp_smtp_insecure_php_version_notice' ) ) {
	/**
	 * Display admin notice, if the server is using old/insecure PHP version.
	 *
	 * @since 2.1.0
	 */
	function easy_wp_smtp_insecure_php_version_notice() {

		?>
		<div class="notice notice-error">
			<p>
				<?php
				printf(
					wp_kses( /* translators: %1$s - WPBeginner URL for recommended WordPress hosting. */
						__( 'Your site is running an <strong>insecure version</strong> of PHP that is no longer supported. Please contact your web hosting provider to update your PHP version or switch to a <a href="%1$s" target="_blank" rel="noopener noreferrer">recommended WordPress hosting company</a>.', 'easy-wp-smtp' ),
						array(
							'a'      => array(
								'href'   => array(),
								'target' => array(),
								'rel'    => array(),
							),
							'strong' => array(),
						)
					),
					'https://www.wpbeginner.com/wordpress-hosting/'
				);
				?>
				<br><br>
				<?php

				$doc_link = add_query_arg(
					[
						'utm_source'   => 'WordPress',
						'utm_medium'   => 'Admin Notice',
						'utm_campaign' => is_readable( rtrim( plugin_dir_path( __FILE__ ), '/\\' ) . '/src/Pro/Pro.php' ) ? 'plugin' : 'liteplugin',
						'utm_content'  => 'Minimal Required PHP Version',
					],
					'https://easywpsmtp.com/supported-php-versions-for-easy-wp-smtp/'
				);

				printf(
					wp_kses( /* translators: %s - EasyWPSMTP.com docs URL with more details. */
						__( '<strong>Easy WP SMTP plugin is disabled</strong> on your site until you fix the issue. <a href="%s" target="_blank" rel="noopener noreferrer">Read more for additional information.</a>', 'easy-wp-smtp' ),
						array(
							'a'      => array(
								'href'   => array(),
								'target' => array(),
								'rel'    => array(),
							),
							'strong' => array(),
						)
					),
					esc_url( $doc_link )
				);
				?>
			</p>
		</div>

		<?php

		// In case this is on plugin activation.
		if ( isset( $_GET['activate'] ) ) { //phpcs:ignore
			unset( $_GET['activate'] ); //phpcs:ignore
		}
	}
}

if ( ! function_exists( 'easy_wp_smtp_unsupported_wp_version_notice' ) ) {
	/**
	 * Display admin notice, if the site is using unsupported WP version.
	 *
	 * @since 2.1.0
	 */
	function easy_wp_smtp_unsupported_wp_version_notice() {

		?>
		<div class="notice notice-error">
			<p>
				<?php
				printf(
					wp_kses( /* translators: %s The minimal WP version supported by Easy WP SMTP. */
						__( 'Your site is running an <strong>old version</strong> of WordPress that is no longer supported by Easy WP SMTP. Please update your WordPress site to at least version <strong>%s</strong>.', 'easy-wp-smtp' ),
						[
							'strong' => [],
						]
					),
					esc_html( EasyWPSMTP_WP_VERSION )
				);
				?>
				<br><br>
				<?php
				echo wp_kses(
					__( '<strong>Easy WP SMTP plugin is disabled</strong> on your site until WordPress is updated to the required version.', 'easy-wp-smtp' ),
					[
						'strong' => [],
					]
				);
				?>
			</p>
		</div>

		<?php

		// In case this is on plugin activation.
		if ( isset( $_GET['activate'] ) ) { //phpcs:ignore
			unset( $_GET['activate'] ); //phpcs:ignore
		}
	}
}

/**
 * Display admin notice and prevent plugin code execution, if the server is
 * using old/insecure PHP version.
 *
 * @since 2.1.0
 */
if ( version_compare( phpversion(), EasyWPSMTP_PHP_VERSION, '<' ) ) {
	add_action( 'admin_notices', 'easy_wp_smtp_insecure_php_version_notice' );

	return;
}

/**
 * Display admin notice and prevent plugin code execution, if the WP version is lower than EasyWPSMTP_WP_VERSION.
 *
 * @since 2.1.0
 */
if ( version_compare( get_bloginfo( 'version' ), EasyWPSMTP_WP_VERSION, '<' ) ) {
	add_action( 'admin_notices', 'easy_wp_smtp_unsupported_wp_version_notice' );

	return;
}

/**
 * Autoloader. We need it being separate and not using Composer autoloader because of the vendor libs,
 * which are huge and not needed for most users.
 * Inspired by PSR-4 examples: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
 *
 * @since 2.0.0
 *
 * @param string $class The fully-qualified class name.
 */
spl_autoload_register( function ( $class ) {

	list( $plugin_space ) = explode( '\\', $class );
	if ( $plugin_space !== 'EasyWPSMTP' ) {
		return;
	}

	$plugin_dir = basename( __DIR__ );

	// Default directory for all code is plugin's /src/.
	$base_dir = plugin_dir_path( __DIR__ ) . '/' . $plugin_dir . '/src/';

	// Get the relative class name.
	$relative_class = substr( $class, strlen( $plugin_space ) + 1 );

	// Prepare a path to a file.
	$file = wp_normalize_path( $base_dir . $relative_class . '.php' );

	// If the file exists, require it.
	if ( is_readable( $file ) ) {
		/** @noinspection PhpIncludeInspection */
		require_once $file;
	}
} );

/*
 * This function should be wrapped to condition, otherwise `function_exists( 'easy_wp_smtp' )` at the beginning of
 * this file will return `true` which is incorrect.
 *
 * @see https://www.php.net/manual/en/function.function-exists.php#110163
 */
if ( ! function_exists( 'easy_wp_smtp' ) ) {
	/**
	 * Global function-holder. Works similar to a singleton's instance().
	 *
	 * @since 2.0.0
	 *
	 * @return EasyWPSMTP\Core
	 */
	function easy_wp_smtp() {
		/**
		 * @var \EasyWPSMTP\Core
		 */
		static $core;

		if ( ! isset( $core ) ) {
			$core = new \EasyWPSMTP\Core();
		}

		return $core;
	}
}

easy_wp_smtp();
