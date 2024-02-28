<?php

namespace EasyWPSMTP\Admin;

use EasyWPSMTP\Admin\Pages\TestTab;
use EasyWPSMTP\Connect;
use EasyWPSMTP\Helpers\Helpers;
use EasyWPSMTP\Options;
use EasyWPSMTP\UsageTracking\UsageTracking;
use EasyWPSMTP\WP;
use EasyWPSMTP\Reports\Emails\Summary as SummaryReportEmail;
use EasyWPSMTP\Tasks\Reports\SummaryEmailTask as SummaryReportEmailTask;

/**
 * Class for the plugin's Setup Wizard.
 *
 * @since 2.1.0
 */
class SetupWizard {

	/**
	 * The WP Option key for storing setup wizard stats.
	 *
	 * @since 2.1.0
	 */
	const STATS_OPTION_KEY = 'easy_wp_smtp_setup_wizard_stats';

	/**
	 * Run all the hooks needed for the Setup Wizard.
	 *
	 * @since 2.1.0
	 */
	public function hooks() {

		add_action( 'admin_init', [ $this, 'maybe_load_wizard' ] );
		add_action( 'admin_init', [ $this, 'maybe_redirect_after_activation' ], 9999 );
		add_action( 'admin_menu', [ $this, 'add_dashboard_page' ], 20 );
		add_filter( 'removable_query_args', [ $this, 'maybe_disable_automatic_query_args_removal' ] );

		// API AJAX callbacks.
		add_action( 'wp_ajax_easy_wp_smtp_vue_wizard_steps_started', [ $this, 'wizard_steps_started' ] );
		add_action( 'wp_ajax_easy_wp_smtp_vue_get_settings', [ $this, 'get_settings' ] );
		add_action( 'wp_ajax_easy_wp_smtp_vue_update_settings', [ $this, 'update_settings' ] );
		add_action( 'wp_ajax_easy_wp_smtp_vue_get_oauth_url', [ $this, 'get_oauth_url' ] );
		add_action( 'wp_ajax_easy_wp_smtp_vue_remove_oauth_connection', [ $this, 'remove_oauth_connection' ] );
		add_action( 'wp_ajax_easy_wp_smtp_vue_install_plugin', [ $this, 'install_plugin' ] );
		add_action( 'wp_ajax_easy_wp_smtp_vue_get_partner_plugins_info', [ $this, 'get_partner_plugins_info' ] );
		add_action( 'wp_ajax_easy_wp_smtp_vue_subscribe_to_newsletter', [ $this, 'subscribe_to_newsletter' ] );
		add_action( 'wp_ajax_easy_wp_smtp_vue_upgrade_plugin', [ $this, 'upgrade_plugin' ] );
		add_action( 'wp_ajax_easy_wp_smtp_vue_check_mailer_configuration', [ $this, 'check_mailer_configuration' ] );
		add_action( 'wp_ajax_easy_wp_smtp_vue_send_feedback', [ $this, 'send_feedback' ] );
	}

	/**
	 * Get the URL of the Setup Wizard page.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public static function get_site_url() {

		return easy_wp_smtp()->get_admin()->get_admin_page_url() . '-setup-wizard';
	}

	/**
	 * Checks if the Wizard should be loaded in current context.
	 *
	 * @since 2.1.0
	 */
	public function maybe_load_wizard() {

		// Check for wizard-specific parameter
		// Allow plugins to disable the setup wizard
		// Check if current user is allowed to save settings.
		if (
			! (
				isset( $_GET['page'] ) && // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				Area::SLUG . '-setup-wizard' === $_GET['page'] && // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$this->should_setup_wizard_load() &&
				current_user_can( 'manage_options' )
			)
		) {
			return;
		}

		// Don't load the interface if doing an ajax call.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		set_current_screen();

		// Remove an action in the Gutenberg plugin ( not core Gutenberg ) which throws an error.
		remove_action( 'admin_print_styles', 'gutenberg_block_editor_admin_print_styles' );

		$this->load_setup_wizard();
	}

	/**
	 * Maybe redirect to the setup wizard after plugin activation on a new install.
	 *
	 * @since 2.1.0
	 */
	public function maybe_redirect_after_activation() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		// Check if we should consider redirection.
		if ( ! get_transient( 'easy_wp_smtp_activation_redirect' ) ) {
			return;
		}

		delete_transient( 'easy_wp_smtp_activation_redirect' );

		// Check option to disable setup wizard redirect.
		if ( get_option( 'easy_wp_smtp_activation_prevent_redirect' ) ) {
			return;
		}

		// Only do this for single site installs.
		if ( isset( $_GET['activate-multi'] ) || is_network_admin() ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		// Don't redirect if the Setup Wizard is disabled.
		if ( ! $this->should_setup_wizard_load() ) {
			return;
		}

		// Initial install.
		if ( get_option( 'easy_wp_smtp_initial_version' ) === EasyWPSMTP_PLUGIN_VERSION ) {
			update_option( 'easy_wp_smtp_activation_prevent_redirect', true );
			wp_safe_redirect( self::get_site_url() );
			exit;
		}
	}

	/**
	 * Register page through WordPress's hooks.
	 *
	 * Create a dummy admin page, where the Setup Wizard app can be displayed,
	 * but it's not visible in the admin dashboard menu.
	 *
	 * @since 2.1.0
	 */
	public function add_dashboard_page() {

		if ( ! $this->should_setup_wizard_load() ) {
			return;
		}

		add_submenu_page( '', '', '', 'manage_options', Area::SLUG . '-setup-wizard', '' );
	}

	/**
	 * Load the Setup Wizard template.
	 *
	 * @since 2.1.0
	 */
	private function load_setup_wizard() {

		/**
		 * Before setup wizard load.
		 *
		 * @since 2.1.0
		 *
		 * @param \EasyWPSMTP\Admin\SetupWizard  $setup_wizard SetupWizard instance.
		 */
		do_action( 'easy_wp_smtp_admin_setup_wizard_load_setup_wizard_before', $this );

		$this->enqueue_scripts();

		$this->setup_wizard_header();
		$this->setup_wizard_content();
		$this->setup_wizard_footer();

		/**
		 * After setup wizard load.
		 *
		 * @since 2.1.0
		 *
		 * @param \EasyWPSMTP\Admin\SetupWizard  $setup_wizard SetupWizard instance.
		 */
		do_action( 'easy_wp_smtp_admin_setup_wizard_load_setup_wizard_after', $this );

		exit;
	}

	/**
	 * Load the scripts needed for the Setup Wizard.
	 *
	 * @since 2.1.0
	 */
	public function enqueue_scripts() {

		if ( ! defined( 'EasyWPSMTP_VUE_LOCAL_DEV' ) || ! EasyWPSMTP_VUE_LOCAL_DEV ) {
			$rtl = is_rtl() ? '.rtl' : '';
			wp_enqueue_style( 'easy-wp-smtp-vue-style', easy_wp_smtp()->assets_url . '/vue/css/wizard' . $rtl . '.min.css', [], EasyWPSMTP_PLUGIN_VERSION );
		}

		wp_enqueue_script( 'easy-wp-smtp-vue-vendors', easy_wp_smtp()->assets_url . '/vue/js/chunk-vendors.min.js', [], EasyWPSMTP_PLUGIN_VERSION, true );
		wp_enqueue_script( 'easy-wp-smtp-vue-script', easy_wp_smtp()->assets_url . '/vue/js/wizard.min.js', [ 'easy-wp-smtp-vue-vendors' ], EasyWPSMTP_PLUGIN_VERSION, true );

		wp_localize_script(
			'easy-wp-smtp-vue-script',
			'easy_wp_smtp_vue',
			[
				'ajax_url'           => admin_url( 'admin-ajax.php' ),
				'nonce'              => wp_create_nonce( 'easywpsmtp-admin-nonce' ),
				'is_multisite'       => is_multisite(),
				'translations'       => WP::get_jed_locale_data( 'easy-wp-smtp' ),
				'exit_url'           => easy_wp_smtp()->get_admin()->get_admin_page_url(),
				'email_test_tab_url' => add_query_arg( 'tab', 'test', easy_wp_smtp()->get_admin()->get_admin_page_url( Area::SLUG . '-tools' ) ),
				'is_pro'             => easy_wp_smtp()->is_pro(),
				'is_ssl'             => is_ssl(),
				'license_exists'     => apply_filters( 'easy_wp_smtp_admin_setup_wizard_license_exists', false ),
				'plugin_version'     => EasyWPSMTP_PLUGIN_VERSION,
				'mailer_options'     => $this->prepare_mailer_options(),
				'defined_constants'  => $this->prepare_defined_constants(),
				'upgrade_link'       => easy_wp_smtp()->get_upgrade_link( 'setup-wizard' ),
				'versions'           => $this->prepare_versions_data(),
				'public_url'         => easy_wp_smtp()->assets_url . '/vue/',
				'current_user_email' => wp_get_current_user()->user_email,
				'completed_time'     => self::get_stats()['completed_time'],
				'education'          => [
					'upgrade_text'        => esc_html__( 'Sorry, but the %mailer% mailer isn’t available in the lite version. Please upgrade to PRO to unlock this mailer and much more.', 'easy-wp-smtp' ),
					'upgrade_button'      => esc_html__( 'Upgrade to PRO', 'easy-wp-smtp' ),
					'upgrade_url'         => add_query_arg( 'discount', 'SMTPLITEUPGRADE', easy_wp_smtp()->get_upgrade_link( '' ) ),
					'upgrade_bonus_short' => sprintf(
						wp_kses( /* Translators: %s - discount value 50%. */
							__( '<b>%s OFF</b> for Easy WP SMTP users, applied at checkout.', 'easy-wp-smtp' ),
							[
								'b' => [],
							]
						),
						'50%'
					),
					'upgrade_bonus_long'  => sprintf(
						wp_kses( /* Translators: %s - discount value 50%. */
							__( 'You can upgrade to the Pro plan and <b>save %s today</b>, automatically applied at checkout.', 'easy-wp-smtp' ),
							[
								'b' => [],
							]
						),
						'50%'
					),
					'upgrade_doc'         => sprintf(
						'<a href="%1$s" target="_blank" rel="noopener noreferrer" class="already-purchased">%2$s</a>',
						// phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
						esc_url( easy_wp_smtp()->get_utm_url( 'https://easywpsmtp.com/docs/how-to-upgrade-easy-wp-smtp-to-pro-version/', [ 'medium' => 'setup-wizard', 'content' => 'Wizard Pro Mailer Popup - Already purchased' ] ) ),
						esc_html__( 'Already purchased?', 'easy-wp-smtp' )
					),
				],
			]
		);
	}

	/**
	 * Outputs the simplified header used for the Setup Wizard.
	 *
	 * @since 2.1.0
	 */
	public function setup_wizard_header() {
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width"/>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<title><?php esc_html_e( 'Easy WP SMTP &rsaquo; Setup Wizard', 'easy-wp-smtp' ); ?></title>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_print_scripts' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="easy-wp-smtp-setup-wizard">
		<?php
	}

	/**
	 * Outputs the content of the current step.
	 *
	 * @since 2.1.0
	 */
	public function setup_wizard_content() {
		$admin_url = is_network_admin() ? network_admin_url() : admin_url();

		$this->settings_error_page( 'easy-wp-smtp-vue-setup-wizard', '<a href="' . $admin_url . '">' . esc_html__( 'Go back to the Dashboard', 'easy-wp-smtp' ) . '</a>' );
		$this->settings_inline_js();
	}

	/**
	 * Outputs the simplified footer used for the Setup Wizard.
	 *
	 * @since 2.1.0
	 */
	public function setup_wizard_footer() {
		?>
		<?php wp_print_scripts( 'easy-wp-smtp-vue-script' ); ?>
		</body>
		</html>
		<?php
	}

	/**
	 * Error page HTML
	 *
	 * @since 2.1.0
	 *
	 * @param string $id     The HTML ID attribute of the main container div.
	 * @param string $footer The centered footer content.
	 */
	private function settings_error_page( $id = 'easy-wp-smtp-vue-site-settings', $footer = '' ) {

		$inline_logo_image = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjU0IiB2aWV3Qm94PSIwIDAgMzAwIDU0IiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8ZyBjbGlwLXBhdGg9InVybCgjY2xpcDBfNjgwXzc5MDkpIj4KPHBhdGggZD0iTTI0LjI5NjQgNDYuNjEwMkMyNC44MzE4IDQ2LjMyNTMgMjUuNDMwNiA0Ni4xNzYyIDI2LjAzODggNDYuMTc2Mkg1Ni4xNTE4QzU2Ljc2IDQ2LjE3NjIgNTcuMzU4OCA0Ni4zMjUzIDU3Ljg5NDIgNDYuNjEwMkM2MS4yNTI4IDQ4LjM5NyA1OS45NjcgNTMuNDI4NiA1Ni4xNTE4IDUzLjQyODZIMjYuMDM4OEMyMi4yMjM2IDUzLjQyODYgMjAuOTM3OSA0OC4zOTcgMjQuMjk2NCA0Ni42MTAyWiIgZmlsbD0iIzIxMUY5QSIvPgo8cGF0aCBkPSJNMTMuMDgzNSAzMy45MTg0QzEzLjYxMDEgMzMuNjMzNiAxNC4xOTkgMzMuNDg0NCAxNC43OTcyIDMzLjQ4NDRINjcuMzkzMkM2Ny45OTE0IDMzLjQ4NDQgNjguNTgwMSAzMy42MzM2IDY5LjEwNjYgMzMuOTE4NEM3Mi40MTA0IDM1LjcwNTMgNzEuMTQ1OCA0MC43MzY4IDY3LjM5MzIgNDAuNzM2OEgxNC43OTcyQzExLjA0NDggNDAuNzM2OCA5Ljc4MDEzIDM1LjcwNTMgMTMuMDgzNSAzMy45MTg0WiIgZmlsbD0iIzIxMUY5QSIvPgo8cGF0aCBkPSJNOS41MzM2IDIuNTg0NTlDOC45NTQ2MyAzLjEyMzQgOC40NTUzIDMuNzU2NzkgOC4wNjA5MSA0LjQ2OTg0TDEuMjYwNTMgMTYuNzY1NkMtMS41NDE1MSAyMS44MzIgMi4xMjQyNiAyOC4wNDUxIDcuOTE1NTUgMjguMDQ1MUg3NC4yNzUxQzgwLjA2NjEgMjguMDQ1MSA4My43MzIgMjEuODMyIDgwLjkyOTcgMTYuNzY1Nkw3NC4xMjk1IDQuNDY5ODRDNzMuNjk5NiAzLjY5MjgzIDczLjE0NTQgMy4wMTA0IDcyLjQ5OSAyLjQ0MTg5QzY1Ljk2MzYgNi41Mzg0MSA0OS42MjcyIDE2LjE5ODIgNDAuOTAyNCAxNi4xOTgyQzMyLjI3NzQgMTYuMTk4MiAxNi4yMTM5IDYuNzU4MDcgOS41MzM2IDIuNTg0NTlaIiBmaWxsPSIjMjExRjlBIi8+CjxwYXRoIGQ9Ik0xMi4zNzgxIDAuOTE0MjE1QzE5LjU3ODYgNS4yODQ4MSAzMy4zNDEzIDEyLjk4NzYgNDAuOTAyMyAxMi45ODc2QzQ4LjUwODUgMTIuOTg3NiA2Mi4zOTEgNS4xOTIzNSA2OS41NTUzIDAuODM2MTIxQzY4Ljg4NzYgMC42NDYyMyA2OC4xODg0IDAuNTQ2MjY5IDY3LjQ3NDggMC41NDYyNjlMMTQuNzE1OSAwLjU0NjI2NUMxMy45MDk5IDAuNTQ2MjY1IDEzLjEyMjUgMC42NzM3MjYgMTIuMzc4MSAwLjkxNDIxNVoiIGZpbGw9IiMyMTFGOUEiLz4KPHBhdGggZD0iTTk5LjQ2MzEgMjYuMDk5MUgxMTMuMzYyVjIyLjYwMDlIMTAzLjUyOVYxNi41ODE1SDExMS4wNjFWMTMuMDgzMkgxMDMuNTI5VjcuMjIxMzRIMTEyLjg1OFYzLjcyMzE0SDk5LjQ2MzFWMjYuMDk5MVoiIGZpbGw9IiMwOTA5MkMiLz4KPHBhdGggZD0iTTExNC42NiAyMS40NjYzQzExNC42NiAyNC42ODA5IDExNy4yNDQgMjYuNDc3MyAxMjAuMTEyIDI2LjQ3NzNDMTIzLjc2OCAyNi40NzczIDEyNS4wMjggMjMuNjQwOSAxMjQuOTk3IDIzLjY0MDlIMTI1LjA2QzEyNS4wNiAyMy42NDA5IDEyNC45OTcgMjQuMTQ1MiAxMjQuOTk3IDI0Ljc3NTVWMjYuMDk5MUgxMjguNjg0VjE2LjA0NTdDMTI4LjY4NCAxMS45ODAyIDEyNi4yMjYgOS42NDgwNyAxMjIuMDAzIDkuNjQ4MDdDMTE4LjE5IDkuNjQ4MDcgMTE1LjcgMTEuNjMzNSAxMTUuNyAxMS42MzM1TDExNy4yMTMgMTQuNTAxNEMxMTcuMjEzIDE0LjUwMTQgMTE5LjMyNCAxMi45NTcyIDEyMS42MjUgMTIuOTU3MkMxMjMuMzkgMTIuOTU3MiAxMjQuNzEzIDEzLjY4MiAxMjQuNzEzIDE1Ljc5MzZWMTYuMDE0MkgxMjQuMTc3QzEyMS41NjIgMTYuMDE0MiAxMTQuNjYgMTYuMzYwOSAxMTQuNjYgMjEuNDY2M1pNMTE4LjY5NCAyMS4yNzcyQzExOC42OTQgMTkuMDM5NiAxMjIuMDAzIDE4Ljc1NiAxMjQuMDUyIDE4Ljc1NkgxMjQuNzQ1VjE5LjEzNDJDMTI0Ljc0NSAyMS4wODgyIDEyMy4yMzIgMjMuMzU3MyAxMjEuMTIxIDIzLjM1NzNDMTE5LjQ4MSAyMy4zNTczIDExOC42OTQgMjIuMzQ4NyAxMTguNjk0IDIxLjI3NzJaIiBmaWxsPSIjMDkwOTJDIi8+CjxwYXRoIGQ9Ik0xMzAuNTUgMjQuMTEzN0MxMzAuNTUgMjQuMTEzNyAxMzIuNzg3IDI2LjQ3NzMgMTM2Ljc4OSAyNi40NzczQzE0MC42MDMgMjYuNDc3MyAxNDIuOTM1IDI0LjMzNDMgMTQyLjkzNSAyMS42NTU0QzE0Mi45MzUgMTYuNDg2OSAxMzUuMTE5IDE2Ljc3MDYgMTM1LjExOSAxNC41MDE0QzEzNS4xMTkgMTMuNDkyOSAxMzYuMTI4IDEzLjA1MTcgMTM3LjE2OCAxMy4wNTE3QzEzOS42MjYgMTMuMDUxNyAxNDEuMTA3IDE0LjQzODQgMTQxLjEwNyAxNC40Mzg0TDE0Mi41ODggMTEuNDc1OUMxNDIuNTg4IDExLjQ3NTkgMTQwLjgyNCA5LjY0ODA3IDEzNy4xOTkgOS42NDgwN0MxMzMuNzMzIDkuNjQ4MDcgMTMxLjA1NCAxMS4zODE0IDEzMS4wNTQgMTQuMzc1M0MxMzEuMDU0IDE5LjU0MzkgMTM4Ljg2OSAxOS4yMjg3IDEzOC44NjkgMjEuNjIzOUMxMzguODY5IDIyLjU2OTMgMTM3Ljg5MyAyMy4wNzM2IDEzNi43NTggMjMuMDczNkMxMzQuMTQyIDIzLjA3MzYgMTMyLjM3NyAyMS4zMDg4IDEzMi4zNzcgMjEuMzA4OEwxMzAuNTUgMjQuMTEzN1oiIGZpbGw9IiMwOTA5MkMiLz4KPHBhdGggZD0iTTE0My4wMzQgMzEuNjc3M0MxNDMuMDM0IDMxLjY3NzMgMTQ0LjQ1MiAzMi43MTczIDE0Ni4zNzUgMzIuNzE3M0MxNDguODAyIDMyLjcxNzMgMTUxLjAzOSAzMS40NTY3IDE1Mi4xNzMgMjguNTI1OEwxNTkuMzU5IDEwLjAyNjJIMTU0Ljk3OUwxNTEuODI3IDE5LjM1NDdDMTUxLjU0MyAyMC4yMDU3IDE1MS4yOTEgMjEuNDY2MyAxNTEuMjkxIDIxLjQ2NjNIMTUxLjIyOEMxNTEuMjI4IDIxLjQ2NjMgMTUwLjk0NSAyMC4xNDI2IDE1MC42MjkgMTkuMjkxN0wxNDcuMjU3IDEwLjAyNjJIMTQyLjc1TDE0OS41MjYgMjUuODQ3TDE0OC45MjcgMjcuMjY1MUMxNDguMzI5IDI4LjY4MzQgMTQ3LjI1NyAyOS4zNDUyIDE0Ni4xNTUgMjkuMzQ1MkMxNDUuMjQgMjkuMzQ1MiAxNDQuMzU4IDI4LjY4MzQgMTQ0LjM1OCAyOC42ODM0TDE0My4wMzQgMzEuNjc3M1oiIGZpbGw9IiMwOTA5MkMiLz4KPHBhdGggZD0iTTE4Mi4xNjUgMy43MjMxNEgxNzguNjM2TDE3NC42MDIgMTkuMTk3MkMxNzQuMjU1IDIwLjQ4OTQgMTc0LjIyMyAyMS41NjA5IDE3NC4xOTIgMjEuNTYwOUgxNzQuMTI5QzE3NC4xMjkgMjEuNTYwOSAxNzQuMDM0IDIwLjQ1NzkgMTczLjc1MSAxOS4xOTcyTDE3MC4yMjEgMy43MjMxNEgxNjYuMDI5TDE3MS42MzkgMjYuMDk5MUgxNzYuMzM1TDE3OS43NyAxMi44NjI2QzE4MC4xNDggMTEuNDEyOSAxODAuMzM4IDkuOTMxNjkgMTgwLjMzOCA5LjkzMTY5SDE4MC40QzE4MC40IDkuOTMxNjkgMTgwLjU4OSAxMS40MTI5IDE4MC45NjggMTIuODYyNkwxODQuNDAzIDI2LjA5OTFIMTg5LjA5OEwxOTQuODY2IDMuNzIzMTRIMTkwLjY3NEwxODYuOTg3IDE5LjE5NzJDMTg2LjcwNCAyMC40NTc5IDE4Ni42MDkgMjEuNTYwOSAxODYuNjA5IDIxLjU2MDlIMTg2LjU0NkMxODYuNTE1IDIxLjU2MDkgMTg2LjQ4MyAyMC40ODk0IDE4Ni4xMzYgMTkuMTk3MkwxODIuMTY1IDMuNzIzMTRaIiBmaWxsPSIjMDkwOTJDIi8+CjxwYXRoIGQ9Ik0xOTcuNTM5IDI2LjA5OTFIMjAxLjYwNVYxOC4zNzc4SDIwNi4xNzVDMjEwLjM2NiAxOC4zNzc4IDIxMy4yOTcgMTUuMzUyMyAyMTMuMjk3IDExLjAwMzJDMjEzLjI5NyA2LjY1NDEgMjEwLjM2NiAzLjcyMzE0IDIwNi4xNzUgMy43MjMxNEgxOTcuNTM5VjI2LjA5OTFaTTIwMS42MDUgMTQuODQ4MVY3LjIyMTM0SDIwNS40ODFDMjA3Ljc4MiA3LjIyMTM0IDIwOS4xNjggOC43MDI1NyAyMDkuMTY4IDExLjAwMzJDMjA5LjE2OCAxMy4zMzU0IDIwNy43ODIgMTQuODQ4MSAyMDUuNDE4IDE0Ljg0ODFIMjAxLjYwNVoiIGZpbGw9IiMwOTA5MkMiLz4KPHBhdGggZD0iTTIyMC45NjggMjMuNDIwM0MyMjAuOTY4IDIzLjQyMDMgMjIzLjcxIDI2LjQ3NzMgMjI4LjY5IDI2LjQ3NzNDMjMzLjM1NCAyNi40NzczIDIzNi4wNjQgMjMuNDgzNCAyMzYuMDY0IDE5LjkyMjFDMjM2LjA2NCAxMi43NjgxIDIyNS41MzggMTMuNzQ1MSAyMjUuNTM4IDkuNzc0MTFDMjI1LjUzOCA4LjE5ODM2IDIyNy4wMiA3LjA5NTMyIDIyOC45MTEgNy4wOTUzMkMyMzEuNzE1IDcuMDk1MzIgMjMzLjg1OCA5LjA0OTI4IDIzMy44NTggOS4wNDkyOEwyMzUuNjIzIDUuNzQwMTRDMjM1LjYyMyA1Ljc0MDE0IDIzMy4zNTQgMy4zNDQ5NyAyMjguOTQyIDMuMzQ0OTdDMjI0LjY1NiAzLjM0NDk3IDIyMS40NDEgNi4xMTgzMyAyMjEuNDQxIDkuODM3MTJDMjIxLjQ0MSAxNi43MDc1IDIzMS45OTkgMTYuMDE0MiAyMzEuOTk5IDIwLjAxNjdDMjMxLjk5OSAyMS44NDQ1IDIzMC40NTQgMjIuNzI3IDIyOC43NTIgMjIuNzI3QzIyNS42MDEgMjIuNzI3IDIyMy4xNzQgMjAuMzYzMyAyMjMuMTc0IDIwLjM2MzNMMjIwLjk2OCAyMy40MjAzWiIgZmlsbD0iIzA5MDkyQyIvPgo8cGF0aCBkPSJNMjM4LjQ4MiAyNi4wOTkxSDI0Mi41NDdMMjQzLjQ5MyAxMy41ODc1QzI0My41ODcgMTIuMTA2MyAyNDMuNTI1IDEwLjA4OTMgMjQzLjUyNSAxMC4wODkzSDI0My41ODdDMjQzLjU4NyAxMC4wODkzIDI0NC4yODEgMTIuMjk1NCAyNDQuODE2IDEzLjU4NzVMMjQ4LjQwOSAyMi4yNTQySDI1MS45N0wyNTUuNTk1IDEzLjU4NzVDMjU2LjEzIDEyLjI5NTQgMjU2Ljc5MiAxMC4xMjA4IDI1Ni43OTIgMTAuMTIwOEgyNTYuODU1QzI1Ni44NTUgMTAuMTIwOCAyNTYuNzkyIDEyLjEwNjMgMjU2Ljg4NyAxMy41ODc1TDI1Ny44MzIgMjYuMDk5MUgyNjEuODY2TDI2MC4wNyAzLjcyMzE0SDI1NS43MjFMMjUxLjM0IDE0Ljc4NUMyNTAuODM2IDE2LjEwODcgMjUwLjIzNyAxOC4wNjI3IDI1MC4yMzcgMTguMDYyN0gyNTAuMTc0QzI1MC4xNzQgMTguMDYyNyAyNDkuNTQ0IDE2LjEwODcgMjQ5LjAzOSAxNC43ODVMMjQ0LjY1OSAzLjcyMzE0SDI0MC4zMUwyMzguNDgyIDI2LjA5OTFaIiBmaWxsPSIjMDkwOTJDIi8+CjxwYXRoIGQ9Ik0yNzAuNTMxIDI2LjA5OTFIMjc0LjU5N1Y3LjIyMTM0SDI4MS45NFYzLjcyMzE0SDI2My4xODhWNy4yMjEzNEgyNzAuNTMxVjI2LjA5OTFaIiBmaWxsPSIjMDkwOTJDIi8+CjxwYXRoIGQ9Ik0yODMuOTQyIDI2LjA5OTFIMjg4LjAwOFYxOC4zNzc4SDI5Mi41NzdDMjk2Ljc2OSAxOC4zNzc4IDI5OS43IDE1LjM1MjMgMjk5LjcgMTEuMDAzMkMyOTkuNyA2LjY1NDEgMjk2Ljc2OSAzLjcyMzE0IDI5Mi41NzcgMy43MjMxNEgyODMuOTQyVjI2LjA5OTFaTTI4OC4wMDggMTQuODQ4MVY3LjIyMTM0SDI5MS44ODRDMjk0LjE4NSA3LjIyMTM0IDI5NS41NzEgOC43MDI1NyAyOTUuNTcxIDExLjAwMzJDMjk1LjU3MSAxMy4zMzU0IDI5NC4xODUgMTQuODQ4MSAyOTEuODIxIDE0Ljg0ODFIMjg4LjAwOFoiIGZpbGw9IiMwOTA5MkMiLz4KPHBhdGggZD0iTTk5LjQ2MzEgNDcuNDUyMUM5OS40NjMxIDQ5LjE1MjUgMTAwLjg5NCA1MC4wNDQ3IDEwMi4zNTkgNTAuMDQ0N0MxMDQuMzk2IDUwLjA0NDcgMTA1LjA4NyA0OC4zNjEyIDEwNS4wODcgNDguMzYxMkgxMDUuMTJDMTA1LjEyIDQ4LjM2MTIgMTA1LjA4NyA0OC42NDc0IDEwNS4wODcgNDkuMDM0N1Y0OS44NDI3SDEwNi42MDJWNDQuNDU1NUMxMDYuNjAyIDQyLjMwMDYgMTA1LjM4OSA0MS4xMjIxIDEwMy4yMzQgNDEuMTIyMUMxMDEuMjgxIDQxLjEyMjEgMTAwLjA1MiA0Mi4xMzIyIDEwMC4wNTIgNDIuMTMyMkwxMDAuNzI2IDQzLjMyNzVDMTAwLjcyNiA0My4zMjc1IDEwMS43ODcgNDIuNTAyNiAxMDMuMSA0Mi41MDI2QzEwNC4xNzcgNDIuNTAyNiAxMDQuOTY5IDQyLjk3NCAxMDQuOTY5IDQ0LjM3MTNWNDQuNTIyOEgxMDQuNTk4QzEwMy4xNjcgNDQuNTIyOCA5OS40NjMxIDQ0LjY0MDcgOTkuNDYzMSA0Ny40NTIxWk0xMDEuMTEzIDQ3LjM2OEMxMDEuMTEzIDQ1LjgzNiAxMDMuMzM2IDQ1Ljc1MTggMTA0LjU2NCA0NS43NTE4SDEwNC45ODZWNDYuMDIxMkMxMDQuOTg2IDQ3LjI4MzggMTA0LjA5MyA0OC43MzE2IDEwMi43MTIgNDguNzMxNkMxMDEuNjUyIDQ4LjczMTYgMTAxLjExMyA0OC4wNTgyIDEwMS4xMTMgNDcuMzY4WiIgZmlsbD0iIzVGNUY3NiIvPgo8cGF0aCBkPSJNMTEyLjA5MyA0OC40NjIyQzExMi4wOTMgNDguNDYyMiAxMTMuNTU4IDUwLjA5NTIgMTE2LjIxOCA1MC4wOTUyQzExOC43MSA1MC4wOTUyIDEyMC4xNTcgNDguNDk1OSAxMjAuMTU3IDQ2LjU5MzVDMTIwLjE1NyA0Mi43NzE5IDExNC41MzQgNDMuMjkzOCAxMTQuNTM0IDQxLjE3MjZDMTE0LjUzNCA0MC4zMzA4IDExNS4zMjUgMzkuNzQxNiAxMTYuMzM1IDM5Ljc0MTZDMTE3LjgzNCAzOS43NDE2IDExOC45NzkgNDAuNzg1NCAxMTguOTc5IDQwLjc4NTRMMTE5LjkyMSAzOS4wMTc3QzExOS45MjEgMzkuMDE3NyAxMTguNzEgMzcuNzM4MiAxMTYuMzUyIDM3LjczODJDMTE0LjA2MyAzNy43MzgyIDExMi4zNDYgMzkuMjE5NyAxMTIuMzQ2IDQxLjIwNjJDMTEyLjM0NiA0NC44NzYzIDExNy45ODUgNDQuNTA1OSAxMTcuOTg1IDQ2LjY0NEMxMTcuOTg1IDQ3LjYyMDUgMTE3LjE2MSA0OC4wOTE5IDExNi4yNTEgNDguMDkxOUMxMTQuNTY4IDQ4LjA5MTkgMTEzLjI3MiA0Ni44MjkyIDExMy4yNzIgNDYuODI5MkwxMTIuMDkzIDQ4LjQ2MjJaIiBmaWxsPSIjMjExRjlBIi8+CjxwYXRoIGQ9Ik0xMjAuODc2IDQ1LjYwMDNDMTIwLjg3NiA0OC4wNDE0IDEyMi42NDQgNTAuMDk1MiAxMjUuNDg5IDUwLjA5NTJDMTI3LjYyNyA1MC4wOTUyIDEyOC45NCA0OC44NjYzIDEyOC45NCA0OC44NjYzTDEyOC4xMTYgNDcuMzM0M0MxMjguMTE2IDQ3LjMzNDMgMTI3LjAyMSA0OC4yNzcgMTI1LjY0IDQ4LjI3N0MxMjQuMzYxIDQ4LjI3NyAxMjMuMTk5IDQ3LjUwMjYgMTIzLjA2NSA0Ni4wMDQzSDEyOC45OUMxMjguOTkgNDYuMDA0MyAxMjkuMDQxIDQ1LjQzMTkgMTI5LjA0MSA0NS4xNzk0QzEyOS4wNDEgNDIuOTA2NyAxMjcuNzExIDQxLjEwNTMgMTI1LjIzNyA0MS4xMDUzQzEyMi42NzcgNDEuMTA1MyAxMjAuODc2IDQyLjk1NzEgMTIwLjg3NiA0NS42MDAzWk0xMjMuMTMyIDQ0LjUzOTdDMTIzLjMzNCA0My40Mjg1IDEyNC4wOTIgNDIuNzU1MSAxMjUuMTg2IDQyLjc1NTFDMTI2LjEyOSA0Mi43NTUxIDEyNi44NTMgNDMuMzc4IDEyNi44ODYgNDQuNTM5N0gxMjMuMTMyWiIgZmlsbD0iIzIxMUY5QSIvPgo8cGF0aCBkPSJNMTMwLjI5OSA0OS44OTMySDEzMi40MzdWNDUuOTUzOEMxMzIuNDM3IDQ1LjU0OTcgMTMyLjQ3MSA0NS4xNjI2IDEzMi41ODkgNDQuODA5QzEzMi45MDkgNDMuNzgyMSAxMzMuNzUgNDMuMDU4MiAxMzQuODk1IDQzLjA1ODJDMTM1Ljk4OSA0My4wNTgyIDEzNi4yNTkgNDMuNzY1MiAxMzYuMjU5IDQ0LjgwOVY0OS44OTMySDEzOC4zOFY0NC4zNzEzQzEzOC4zOCA0Mi4wOTg1IDEzNy4zMDMgNDEuMTA1MyAxMzUuNCA0MS4xMDUzQzEzMy42NjYgNDEuMTA1MyAxMzIuNzI0IDQyLjE2NTkgMTMyLjM1MyA0Mi44ODk4SDEzMi4zMTlDMTMyLjMxOSA0Mi44ODk4IDEzMi4zNTMgNDIuNjIwNCAxMzIuMzUzIDQyLjMwMDZWNDEuMzA3M0gxMzAuMjk5VjQ5Ljg5MzJaIiBmaWxsPSIjMjExRjlBIi8+CjxwYXRoIGQ9Ik0xMzkuNTI5IDQ1LjYwMDJDMTM5LjUyOSA0OC4yNjAyIDE0MS4wMTEgNTAuMDk1MiAxNDMuMzM0IDUwLjA5NTJDMTQ1LjMwNCA1MC4wOTUyIDE0Ni4wMjggNDguNjMwNSAxNDYuMDI4IDQ4LjYzMDVIMTQ2LjA2MkMxNDYuMDYyIDQ4LjYzMDUgMTQ2LjAyOCA0OC44NjYzIDE0Ni4wMjggNDkuMTg2MVY0OS44OTMySDE0OC4wNDhWMzcuOTQwMkgxNDUuOTFWNDEuNzExM0MxNDUuOTEgNDEuOTgwNiAxNDUuOTI3IDQyLjE5OTUgMTQ1LjkyNyA0Mi4xOTk1SDE0NS44OTNDMTQ1Ljg5MyA0Mi4xOTk1IDE0NS4zMDQgNDEuMTA1MiAxNDMuNDE4IDQxLjEwNTJDMTQxLjE0NSA0MS4xMDUyIDEzOS41MjkgNDIuODcyOSAxMzkuNTI5IDQ1LjYwMDJaTTE0MS42ODUgNDUuNjAwMkMxNDEuNjg1IDQzLjg5OTkgMTQyLjY2MSA0Mi45NDAzIDE0My44MzkgNDIuOTQwM0MxNDUuMjcgNDIuOTQwMyAxNDUuOTc3IDQ0LjI1MzQgMTQ1Ljk3NyA0NS41ODM0QzE0NS45NzcgNDcuNDg1OCAxNDQuOTM0IDQ4LjI5MzggMTQzLjgyMiA0OC4yOTM4QzE0Mi41NTkgNDguMjkzOCAxNDEuNjg1IDQ3LjIzMzIgMTQxLjY4NSA0NS42MDAyWiIgZmlsbD0iIzIxMUY5QSIvPgo8cGF0aCBkPSJNMTUwLjI0MiA0OS44OTMySDE1Ny41ODJWNDguMDI0NUgxNTIuNDE0VjM3Ljk0MDJIMTUwLjI0MlY0OS44OTMyWiIgZmlsbD0iIzIxMUY5QSIvPgo8cGF0aCBkPSJNMTU3Ljc1MiA0Ny40MTg1QzE1Ny43NTIgNDkuMTM1NiAxNTkuMTMyIDUwLjA5NTIgMTYwLjY2NCA1MC4wOTUyQzE2Mi42MTcgNTAuMDk1MiAxNjMuMjkgNDguNTgwMSAxNjMuMjczIDQ4LjU4MDFIMTYzLjMwN0MxNjMuMzA3IDQ4LjU4MDEgMTYzLjI3MyA0OC44NDk0IDE2My4yNzMgNDkuMTg2MVY0OS44OTMySDE2NS4yNDNWNDQuNTIyOEMxNjUuMjQzIDQyLjM1MTEgMTYzLjkzMSA0MS4xMDUzIDE2MS42NzQgNDEuMTA1M0MxNTkuNjM3IDQxLjEwNTMgMTU4LjMwNyA0Mi4xNjU5IDE1OC4zMDcgNDIuMTY1OUwxNTkuMTE1IDQzLjY5NzlDMTU5LjExNSA0My42OTc5IDE2MC4yNDMgNDIuODczIDE2MS40NzIgNDIuODczQzE2Mi40MTUgNDIuODczIDE2My4xMjIgNDMuMjYwMiAxNjMuMTIyIDQ0LjM4ODJWNDQuNTA2SDE2Mi44MzZDMTYxLjQzOSA0NC41MDYgMTU3Ljc1MiA0NC42OTEyIDE1Ny43NTIgNDcuNDE4NVpNMTU5LjkwNyA0Ny4zMTc1QzE1OS45MDcgNDYuMTIyMSAxNjEuNjc0IDQ1Ljk3MDYgMTYyLjc2OSA0NS45NzA2SDE2My4xMzlWNDYuMTcyNkMxNjMuMTM5IDQ3LjIxNjQgMTYyLjMzMSA0OC40Mjg2IDE2MS4yMDMgNDguNDI4NkMxNjAuMzI3IDQ4LjQyODYgMTU5LjkwNyA0Ny44ODk5IDE1OS45MDcgNDcuMzE3NVoiIGZpbGw9IiMyMTFGOUEiLz4KPHBhdGggZD0iTTE2NS44MDIgNTIuODczMUMxNjUuODAyIDUyLjg3MzEgMTY2LjU2IDUzLjQyODYgMTY3LjU4NiA1My40Mjg2QzE2OC44ODMgNTMuNDI4NiAxNzAuMDc4IDUyLjc1NTIgMTcwLjY4NCA1MS4xODk1TDE3NC41MjMgNDEuMzA3M0gxNzIuMTgyTDE3MC40OTkgNDYuMjkwNUMxNzAuMzQ3IDQ2Ljc0NSAxNzAuMjEzIDQ3LjQxODUgMTcwLjIxMyA0Ny40MTg1SDE3MC4xNzlDMTcwLjE3OSA0Ny40MTg1IDE3MC4wMjggNDYuNzExNCAxNjkuODU5IDQ2LjI1NjhMMTY4LjA1OCA0MS4zMDczSDE2NS42NUwxNjkuMjcgNDkuNzU4NUwxNjguOTUgNTAuNTE2MUMxNjguNjMgNTEuMjczNyAxNjguMDU4IDUxLjYyNzIgMTY3LjQ2OCA1MS42MjcyQzE2Ni45ODEgNTEuNjI3MiAxNjYuNTA5IDUxLjI3MzcgMTY2LjUwOSA1MS4yNzM3TDE2NS44MDIgNTIuODczMVoiIGZpbGw9IiMyMTFGOUEiLz4KPHBhdGggZD0iTTE3NC42MzIgNDUuNjAwM0MxNzQuNjMyIDQ4LjA0MTQgMTc2LjM5OSA1MC4wOTUyIDE3OS4yNDQgNTAuMDk1MkMxODEuMzgzIDUwLjA5NTIgMTgyLjY5NiA0OC44NjYzIDE4Mi42OTYgNDguODY2M0wxODEuODcxIDQ3LjMzNDNDMTgxLjg3MSA0Ny4zMzQzIDE4MC43NzYgNDguMjc3IDE3OS4zOTYgNDguMjc3QzE3OC4xMTcgNDguMjc3IDE3Ni45NTUgNDcuNTAyNiAxNzYuODIxIDQ2LjAwNDNIMTgyLjc0NkMxODIuNzQ2IDQ2LjAwNDMgMTgyLjc5NyA0NS40MzE5IDE4Mi43OTcgNDUuMTc5NEMxODIuNzk3IDQyLjkwNjcgMTgxLjQ2NyA0MS4xMDUzIDE3OC45OTIgNDEuMTA1M0MxNzYuNDMzIDQxLjEwNTMgMTc0LjYzMiA0Mi45NTcxIDE3NC42MzIgNDUuNjAwM1pNMTc2Ljg4OCA0NC41Mzk3QzE3Ny4wOSA0My40Mjg1IDE3Ny44NDcgNDIuNzU1MSAxNzguOTQyIDQyLjc1NTFDMTc5Ljg4NSA0Mi43NTUxIDE4MC42MDggNDMuMzc4IDE4MC42NDIgNDQuNTM5N0gxNzYuODg4WiIgZmlsbD0iIzIxMUY5QSIvPgo8cGF0aCBkPSJNMTg0LjA1NCA0OS44OTMySDE4Ni4xOTNWNDYuNDkyNUMxODYuMTkzIDQ1Ljk4NzUgMTg2LjI0MyA0NS41MTYxIDE4Ni4zNzggNDUuMDk1MkMxODYuNzgyIDQzLjgxNTggMTg3LjgwOSA0My4yOTM4IDE4OC43MTggNDMuMjkzOEMxODkuMDA0IDQzLjI5MzggMTg5LjIyMyA0My4zMjc1IDE4OS4yMjMgNDMuMzI3NVY0MS4yMjMyQzE4OS4yMjMgNDEuMjIzMiAxODkuMDM4IDQxLjE4OTUgMTg4LjgzNSA0MS4xODk1QzE4Ny41MjMgNDEuMTg5NSAxODYuNDk2IDQyLjE2NTkgMTg2LjEwOCA0My4zOTQ5SDE4Ni4wNzVDMTg2LjA3NSA0My4zOTQ5IDE4Ni4xMDggNDMuMTA4NiAxODYuMTA4IDQyLjc4ODhWNDEuMzA3M0gxODQuMDU0VjQ5Ljg5MzJaIiBmaWxsPSIjMjExRjlBIi8+CjxwYXRoIGQ9Ik0xOTQuNzE0IDUzLjIwOTdIMTk2LjM0OFY0OS4zODgxQzE5Ni4zNDggNDguOTUwNCAxOTYuMzE0IDQ4LjY0NzQgMTk2LjMxNCA0OC42NDc0SDE5Ni4zNDhDMTk2LjM0OCA0OC42NDc0IDE5Ny4wODggNTAuMDQ0NyAxOTguOTU3IDUwLjA0NDdDMjAxLjE4IDUwLjA0NDcgMjAyLjgxMyA0OC4yOTM4IDIwMi44MTMgNDUuNTgzM0MyMDIuODEzIDQyLjk0MDMgMjAxLjM2NSA0MS4xMjIxIDE5OS4wNzUgNDEuMTIyMUMxOTYuOTM3IDQxLjEyMjEgMTk2LjIxMyA0Mi42NzA5IDE5Ni4yMTMgNDIuNjcwOUgxOTYuMTc5QzE5Ni4xNzkgNDIuNjcwOSAxOTYuMjEzIDQyLjM4NDcgMTk2LjIxMyA0Mi4wNDhWNDEuMzI0SDE5NC43MTRWNTMuMjA5N1pNMTk2LjI5NyA0NS42MTdDMTk2LjI5NyA0My40NDUzIDE5Ny40NzUgNDIuNTUzIDE5OC43NTUgNDIuNTUzQzIwMC4xNjkgNDIuNTUzIDIwMS4xNjMgNDMuNzQ4MyAyMDEuMTYzIDQ1LjYwMDJDMjAxLjE2MyA0Ny41MzYyIDIwMC4wNTIgNDguNjQ3NCAxOTguNzA1IDQ4LjY0NzRDMTk3LjEzOSA0OC42NDc0IDE5Ni4yOTcgNDcuMTMyMiAxOTYuMjk3IDQ1LjYxN1oiIGZpbGw9IiM1RjVGNzYiLz4KPHBhdGggZD0iTTIwNC4zMDQgNDkuODQyN0gyMDUuOTM2VjQ2LjM1NzhDMjA1LjkzNiA0NS44MzU5IDIwNS45ODcgNDUuMzE0IDIwNi4xMzggNDQuODI1OEMyMDYuNTI2IDQzLjU2MzEgMjA3LjQ4NSA0Mi44MjI0IDIwOC41MjkgNDIuODIyNEMyMDguNzgyIDQyLjgyMjQgMjA5IDQyLjg3MyAyMDkgNDIuODczVjQxLjI1NjhDMjA5IDQxLjI1NjggMjA4Ljc5OSA0MS4yMjMxIDIwOC41OCA0MS4yMjMxQzIwNy4yNjcgNDEuMjIzMSAyMDYuMjczIDQyLjE5OTUgMjA1Ljg4NiA0My40NDUzSDIwNS44NTJDMjA1Ljg1MiA0My40NDUzIDIwNS44ODYgNDMuMTU5MiAyMDUuODg2IDQyLjgwNTZWNDEuMzI0MUgyMDQuMzA0VjQ5Ljg0MjdaIiBmaWxsPSIjNUY1Rjc2Ii8+CjxwYXRoIGQ9Ik0yMDkuNDQgNDUuNTY2NUMyMDkuNDQgNDguMTU5MSAyMTEuNDk0IDUwLjA0NDcgMjE0LjAzNiA1MC4wNDQ3QzIxNi41NzggNTAuMDQ0NyAyMTguNjMyIDQ4LjE1OTEgMjE4LjYzMiA0NS41NjY1QzIxOC42MzIgNDIuOTkwNyAyMTYuNTc4IDQxLjEyMjEgMjE0LjAzNiA0MS4xMjIxQzIxMS40OTQgNDEuMTIyMSAyMDkuNDQgNDIuOTkwNyAyMDkuNDQgNDUuNTY2NVpNMjExLjEwNiA0NS41NjY1QzIxMS4xMDYgNDMuNzk4OCAyMTIuNDM2IDQyLjUzNjIgMjE0LjAzNiA0Mi41MzYyQzIxNS42NTIgNDIuNTM2MiAyMTYuOTY1IDQzLjc5ODggMjE2Ljk2NSA0NS41NjY1QzIxNi45NjUgNDcuMzUxMSAyMTUuNjUyIDQ4LjYzMDUgMjE0LjAzNiA0OC42MzA1QzIxMi40MzYgNDguNjMwNSAyMTEuMTA2IDQ3LjM1MTEgMjExLjEwNiA0NS41NjY1WiIgZmlsbD0iIzVGNUY3NiIvPgo8cGF0aCBkPSJNMjE5LjUwOCA0NS41ODM0QzIxOS41MDggNDguMjI2NSAyMjAuOTU2IDUwLjA0NDcgMjIzLjI2MiA1MC4wNDQ3QzIyNS4zNjYgNTAuMDQ0NyAyMjYuMDU3IDQ4LjQ2MjIgMjI2LjA1NyA0OC40NjIySDIyNi4wOUMyMjYuMDkgNDguNDYyMiAyMjYuMDc0IDQ4LjY5NzkgMjI2LjA3NCA0OS4wMzQ2VjQ5Ljg0MjdIMjI3LjYyMlYzNy45NTdIMjI1Ljk4OVY0MS44OTY1QzIyNS45ODkgNDIuMjE2MyAyMjYuMDIzIDQyLjQ2ODkgMjI2LjAyMyA0Mi40Njg5SDIyNS45ODlDMjI1Ljk4OSA0Mi40Njg5IDIyNS4zMzMgNDEuMTIyMSAyMjMuMzYzIDQxLjEyMjFDMjIxLjEwNyA0MS4xMjIxIDIxOS41MDggNDIuODczIDIxOS41MDggNDUuNTgzNFpNMjIxLjE3NSA0NS41ODM0QzIyMS4xNzUgNDMuNjQ3NCAyMjIuMjg2IDQyLjUzNjIgMjIzLjYzMiA0Mi41MzYyQzIyNS4yNDggNDIuNTM2MiAyMjYuMDQgNDQuMDUxNCAyMjYuMDQgNDUuNTY2NUMyMjYuMDQgNDcuNzM4MyAyMjQuODQ0IDQ4LjYzMDYgMjIzLjU4MiA0OC42MzA2QzIyMi4xNjggNDguNjMwNiAyMjEuMTc1IDQ3LjQzNTMgMjIxLjE3NSA0NS41ODM0WiIgZmlsbD0iIzVGNUY3NiIvPgo8cGF0aCBkPSJNMjI5LjU1MSA0Ni43Nzg2QzIyOS41NTEgNDkuMDM0NiAyMzAuNTQ1IDUwLjA0NDcgMjMyLjQ2NCA1MC4wNDQ3QzIzNC4xMyA1MC4wNDQ3IDIzNS4yNDIgNDguOTMzNiAyMzUuNTk1IDQ4LjA5MThIMjM1LjYyOUMyMzUuNjI5IDQ4LjA5MTggMjM1LjU5NSA0OC4zNjEyIDIzNS41OTUgNDguNzE0N1Y0OS44NDI3SDIzNy4xNzhWNDEuMzI0SDIzNS41NDRWNDUuMzE0QzIzNS41NDQgNDYuOTk3NSAyMzQuNTE4IDQ4LjUyOTUgMjMyLjc4NCA0OC41Mjk1QzIzMS40MiA0OC41Mjk1IDIzMS4xODQgNDcuNTg2OCAyMzEuMTg0IDQ2LjQwODNWNDEuMzI0SDIyOS41NTFWNDYuNzc4NloiIGZpbGw9IiM1RjVGNzYiLz4KPHBhdGggZD0iTTIzOC42MiA0NS41ODMzQzIzOC42MiA0OC4xNDIzIDI0MC41MDUgNTAuMDQ0NyAyNDMuMTgyIDUwLjA0NDdDMjQ1LjQwNCA1MC4wNDQ3IDI0Ni41ODMgNDguNjgxMSAyNDYuNTgzIDQ4LjY4MTFMMjQ1LjkyNiA0Ny40ODU3QzI0NS45MjYgNDcuNDg1NyAyNDQuODgyIDQ4LjYzMDUgMjQzLjMgNDguNjMwNUMyNDEuNTMyIDQ4LjYzMDUgMjQwLjI4NiA0Ny4zMDA2IDI0MC4yODYgNDUuNTY2NUMyNDAuMjg2IDQzLjgxNTcgMjQxLjUzMiA0Mi41MzYyIDI0My4yNSA0Mi41MzYyQzI0NC42OTcgNDIuNTM2MiAyNDUuNjA2IDQzLjUxMjcgMjQ1LjYwNiA0My41MTI3TDI0Ni4zODEgNDIuMzY3OEMyNDYuMzgxIDQyLjM2NzggMjQ1LjMyIDQxLjEyMjEgMjQzLjE4MiA0MS4xMjIxQzI0MC41MDUgNDEuMTIyMSAyMzguNjIgNDMuMDU4MSAyMzguNjIgNDUuNTgzM1oiIGZpbGw9IiM1RjVGNzYiLz4KPHBhdGggZD0iTTI0OC4yODEgNDYuNzI4MkMyNDguMjgxIDQ5LjU3MzMgMjUwLjQ1MyA0OS45MSAyNTEuNTQ3IDQ5LjkxQzI1MS44ODQgNDkuOTEgMjUyLjExOSA0OS44NzY0IDI1Mi4xMTkgNDkuODc2NFY0OC40Mjg2QzI1Mi4xMTkgNDguNDI4NiAyNTEuOTY4IDQ4LjQ2MjIgMjUxLjczMiA0OC40NjIyQzI1MS4xMDkgNDguNDYyMiAyNDkuOTE0IDQ4LjI0MzMgMjQ5LjkxNCA0Ni41NDNWNDIuNzU1MUgyNTEuOTY4VjQxLjQ0MTlIMjQ5LjkxNFYzOC45ODRIMjQ4LjMzMlY0MS40NDE5SDI0Ny4xN1Y0Mi43NTUxSDI0OC4yODFWNDYuNzI4MloiIGZpbGw9IiM1RjVGNzYiLz4KPC9nPgo8ZGVmcz4KPGNsaXBQYXRoIGlkPSJjbGlwMF82ODBfNzkwOSI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iNTMuOTc0OSIgZmlsbD0id2hpdGUiLz4KPC9jbGlwUGF0aD4KPC9kZWZzPgo8L3N2Zz4K';

		if ( ! easy_wp_smtp()->is_pro() ) {
			$contact_url = 'https://wordpress.org/support/plugin/easy-wp-smtp/';
		} else {
			// phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			$contact_url = esc_url( easy_wp_smtp()->get_utm_url( 'https://easywpsmtp.com/contact/', [ 'medium' => 'setup-wizard', 'content' => 'Contact Us' ] ) );
		}

		?>
		<style type="text/css">
			#easy-wp-smtp-settings-area {
				visibility: hidden;
				animation: loadEasyWPSMTPSettingsNoJSView 0s 2s forwards;
			}

			@keyframes loadEasyWPSMTPSettingsNoJSView{
				to { visibility: visible; }
			}

			body {
				background: #F2F2F4;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
				margin: 0;
			}

			#easy-wp-smtp-settings-area .easy-wp-smtp-setup-wizard-header {
				text-align: center;
			}

			#easy-wp-smtp-settings-area .easy-wp-smtp-setup-wizard-header h1 {
				margin: 0;
			}

			#easy-wp-smtp-settings-area .easy-wp-smtp-logo {
				display: inline-block;
				width: 300px;
				margin-top: 10px;
				padding: 0 10px;
			}

			#easy-wp-smtp-settings-area .easy-wp-smtp-logo img {
				width: 100%;
				height: 100%;
			}

			#easy-wp-smtp-settings-error-loading-area {
				box-sizing: border-box;
				max-width: 90%;
				width: auto;
				margin: 0 auto;
				background: #fff;
				border: 1px solid #DADADF;
				box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
				padding: 20px 30px;
			}

			#easy-wp-smtp-settings-area .easy-wp-smtp-error-footer {
				text-align: center;
				margin-top: 20px;
				margin-bottom: 20px;
				font-size: 14px;
			}

			#easy-wp-smtp-settings-area .easy-wp-smtp-error-footer a {
				color: #6F6F84;
				transition: 0.1s;
			}

			#easy-wp-smtp-settings-area .easy-wp-smtp-error-footer a:hover {
				color: #3A3A56;
			}

			#easy-wp-smtp-error-js h3 {
				font-weight: 500;
				font-size: 24px;
				line-height: 22px;
				margin: 0 0 15px;
				color: #09092C;
			}

			#easy-wp-smtp-error-js p.info,
			#easy-wp-smtp-error-js ul.info {
				color: #3A3A56;
				font-size: 16px;
				line-height: 24px;
				margin: 0 0 10px;
			}

			#easy-wp-smtp-error-js ul.info {
				margin: -10px 0 20px;
			}

			#easy-wp-smtp-error-js a.button {
				display: inline-block;
				background-color: #211FA6;
				color: #ffffff;
				padding: 12px 20px;
				font-size: 16px;
				line-height: 18px;
				border-radius: 4px;
				border: none;
				cursor: pointer;
				text-decoration: none;
				margin-top: 7px;
			}

			#easy-wp-smtp-error-js a.button:hover,
			#easy-wp-smtp-error-js a.button:active,
			#easy-wp-smtp-error-js a.button:focus {
				background-color: #15137A;
			}

			#easy-wp-smtp-error-js a.button:focus {
				box-shadow: 0 0 0 1px #ffffff, 0 0 0 3px #15137A;
			}

			#easy-wp-smtp-error-js .medium-bold {
				font-weight: 500;
			}

			#easy-wp-smtp-nojs-error-message > div {
				background: rgba(223, 42, 74, 0.05);
				border-left: 3px solid #DF2A4A;
				color: #42000C;
				font-weight: 400;
				font-size: 14px;
				line-height: 21px;
				padding: 15px;
				text-align: left;
			}

			@media (min-width: 782px) {
				#easy-wp-smtp-settings-area .easy-wp-smtp-logo {
					margin-top: 60px;
					padding: 0;
				}

				#easy-wp-smtp-settings-error-loading-area {
					width: 650px;
					margin-top: 50px;
					padding: 60px;
				}

				#easy-wp-smtp-settings-area .easy-wp-smtp-error-footer {
					margin-top: 50px;
					margin-bottom: 50px;
				}

				#easy-wp-smtp-error-js p.info {
					margin: 0 0 20px;
				}
			}
		</style>
		<!--[if IE]>
		<style>
			#easy-wp-smtp-settings-area{
				visibility: visible !important;
			}
		</style>
		<![endif]-->
		<div id="<?php echo esc_attr( $id ); ?>">
			<div id="easy-wp-smtp-settings-area" class="easy-wp-smtp-settings-area easywpsmtp-container">
				<header class="easy-wp-smtp-setup-wizard-header">
					<div class="easy-wp-smtp-logo">
						<img src="<?php echo esc_attr( $inline_logo_image ); ?>" alt="<?php esc_attr_e( 'Easy WP SMTP logo', 'easy-wp-smtp' ); ?>" class="easy-wp-smtp-logo-img">
					</div>
				</header>
				<div id="easy-wp-smtp-settings-error-loading-area-container">
					<div id="easy-wp-smtp-settings-error-loading-area">
						<div>
							<div id="easy-wp-smtp-error-js">
								<h3><?php esc_html_e( 'Whoops, something\'s not working.', 'easy-wp-smtp' ); ?></h3>
								<p class="info"><?php esc_html_e( 'It looks like something is preventing JavaScript from loading on your website. Easy WP SMTP requires JavaScript in order to give you the best possible experience.', 'easy-wp-smtp' ); ?></p>
								<p class="info">
									<?php esc_html_e( 'In order to fix this issue, please check each of the items below:', 'easy-wp-smtp' ); ?>
								</p>
								<ul class="info">
									<li><?php esc_html_e( 'If you are using an ad blocker, please disable it or whitelist the current page.', 'easy-wp-smtp' ); ?></li>
									<li><?php esc_html_e( 'If you aren\'t already using Chrome, Firefox, Safari, or Edge, then please try switching to one of these popular browsers.', 'easy-wp-smtp' ); ?></li>
									<li><?php esc_html_e( 'Confirm that your browser is updated to the latest version.', 'easy-wp-smtp' ); ?></li>
								</ul>
								<p class="info">
									<?php esc_html_e( 'If you\'ve checked each of these details and are still running into issues, then please get in touch with our support team. We’d be happy to help!', 'easy-wp-smtp' ); ?>
								</p>
								<div style="display: none;" id="easy-wp-smtp-nojs-error-message">
									<div>
										<strong style="font-weight: 500;" id="easy-wp-smtp-alert-message"></strong>
									</div>
									<p style="font-size: 14px;color: #6f6f84;padding-bottom: 15px;"><?php esc_html_e( 'Copy the error message above and paste it in a message to the Easy WP SMTP support team.', 'easy-wp-smtp' ); ?></p>
								</div>
								<a href="<?php echo esc_url( $contact_url ); ?>" target="_blank" class="button" rel="noopener noreferrer">
									<?php esc_html_e( 'Contact Us', 'easy-wp-smtp' ); ?>
								</a>
							</div>
						</div>
					</div>
					<div class="easy-wp-smtp-error-footer">
						<?php echo wp_kses_post( $footer ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Attempt to catch the js error preventing the Vue app from loading and displaying that message for better support.
	 *
	 * @since 2.1.0
	 */
	private function settings_inline_js() {
		?>
		<script type="text/javascript">
			window.onerror = function myErrorHandler( errorMsg, url, lineNumber ) {
				/* Don't try to put error in container that no longer exists post-vue loading */
				var message_container = document.getElementById( 'easy-wp-smtp-nojs-error-message' );
				if ( ! message_container ) {
					return false;
				}
				var message = document.getElementById( 'easy-wp-smtp-alert-message' );
				message.innerHTML = errorMsg;
				message_container.style.display = 'block';
				return false;
			}
		</script>
		<?php
	}

	/**
	 * Ajax handler for retrieving the plugin settings.
	 *
	 * @since 2.1.0
	 */
	public function get_settings() {

		check_ajax_referer( 'easywpsmtp-admin-nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You don\'t have permission to change options for this WP site!', 'easy-wp-smtp' ) );
		}

		$options = Options::init();

		wp_send_json_success( $options->get_all() );
	}

	/**
	 * Ajax handler for starting the Setup Wizard steps.
	 *
	 * @since 2.1.0
	 */
	public function wizard_steps_started() {

		check_ajax_referer( 'easywpsmtp-admin-nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You don\'t have permission to change options for this WP site!', 'easy-wp-smtp' ) );
		}

		self::update_stats(
			[
				'launched_time' => time(),
			]
		);

		wp_send_json_success();
	}

	/**
	 * Ajax handler for updating the settings.
	 *
	 * @since 2.1.0
	 */
	public function update_settings() {

		check_ajax_referer( 'easywpsmtp-admin-nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		$options   = Options::init();
		$overwrite = ! empty( $_POST['overwrite'] );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$value = isset( $_POST['value'] ) ? wp_slash( json_decode( wp_unslash( $_POST['value'] ), true ) ) : [];

		// Cancel summary report email task if summary report email was disabled.
		if (
			! SummaryReportEmail::is_disabled() &&
			isset( $value['general'][ SummaryReportEmail::SETTINGS_SLUG ] ) &&
			$value['general'][ SummaryReportEmail::SETTINGS_SLUG ] === true
		) {
			( new SummaryReportEmailTask() )->cancel();
		}

		/**
		 * Before updating settings in Setup Wizard.
		 *
		 * @since 2.1.0
		 *
		 * @param array $post POST data.
		 */
		do_action( 'easy_wp_smtp_admin_setup_wizard_update_settings', $value );

		$options->set( $value, false, $overwrite );

		wp_send_json_success();
	}

	/**
	 * Prepare mailer options for all mailers.
	 *
	 * @since 2.1.0
	 *
	 * @return array
	 */
	private function prepare_mailer_options() {

		$data = [];

		foreach ( easy_wp_smtp()->get_providers()->get_options_all() as $provider ) {
			$data[ $provider->get_slug() ] = [
				'slug'        => $provider->get_slug(),
				'title'       => $provider->get_title(),
				'description' => $provider->get_description(),
				'edu_notice'  => $provider->get_notice( 'educational' ),
				'min_php'     => $provider->get_php_version(),
				'disabled'    => $provider->is_disabled(),
				'recommended' => $provider->is_recommended(),
			];
		}

		return apply_filters( 'easy_wp_smtp_admin_setup_wizard_prepare_mailer_options', $data );
	}

	/**
	 * AJAX callback for getting the oAuth authorization URL.
	 *
	 * @since 2.1.0
	 */
	public function get_oauth_url() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		check_ajax_referer( 'easywpsmtp-admin-nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		$data   = [];
		$mailer = ! empty( $_POST['mailer'] ) ? sanitize_text_field( wp_unslash( $_POST['mailer'] ) ) : '';

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$settings = isset( $_POST['settings'] ) ? wp_slash( json_decode( wp_unslash( $_POST['settings'] ), true ) ) : [];

		if ( empty( $mailer ) ) {
			wp_send_json_error();
		}

		$settings = array_merge( $settings, [ 'is_setup_wizard_auth' => true ] );

		$options = Options::init();
		$options->set( [ $mailer => $settings ], false, false );

		$data = apply_filters( 'easy_wp_smtp_admin_setup_wizard_get_oauth_url', $data, $mailer );

		wp_send_json_success( array_merge( [ 'mailer' => $mailer ], $data ) );
	}

	/**
	 * AJAX callback for removing the oAuth authorization connection.
	 *
	 * @since 2.1.0
	 */
	public function remove_oauth_connection() {

		check_ajax_referer( 'easywpsmtp-admin-nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		$mailer = ! empty( $_POST['mailer'] ) ? sanitize_text_field( wp_unslash( $_POST['mailer'] ) ) : '';

		if ( empty( $mailer ) ) {
			wp_send_json_error();
		}

		$options = Options::init();
		$old_opt = $options->get_all_raw();

		foreach ( $old_opt[ $mailer ] as $key => $value ) {
			// Unset everything except Client ID, Client Secret and Domain.
			if ( ! in_array( $key, array( 'domain', 'client_id', 'client_secret' ), true ) ) {
				unset( $old_opt[ $mailer ][ $key ] );
			}
		}

		$options->set( $old_opt );

		wp_send_json_success();
	}

	/**
	 * AJAX callback for installing a plugin.
	 * Has to contain the `slug` POST parameter.
	 *
	 * @since 2.1.0
	 */
	public function install_plugin() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded

		check_ajax_referer( 'easywpsmtp-admin-nonce', 'nonce' );

		// Check for permissions.
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( esc_html__( 'Could not install the plugin. You don\'t have permission to install plugins.', 'easy-wp-smtp' ) );
		}

		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( esc_html__( 'Could not install the plugin. You don\'t have permission to activate plugins.', 'easy-wp-smtp' ) );
		}

		$slug = ! empty( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';

		if ( empty( $slug ) ) {
			wp_send_json_error( esc_html__( 'Could not install the plugin. Plugin slug is missing.', 'easy-wp-smtp' ) );
		}

		if ( ! in_array( $slug, [ 'wpforms-lite', 'all-in-one-seo-pack' ], true ) ) {
			wp_send_json_error( esc_html__( 'Could not install the plugin. Plugin is not whitelisted.', 'easy-wp-smtp' ) );
		}

		$url   = esc_url_raw( WP::admin_url( 'admin.php?page=' . Area::SLUG . '-setup-wizard' ) );
		$creds = request_filesystem_credentials( $url, '', false, false, null );

		// Check for file system permissions.
		if ( false === $creds ) {
			wp_send_json_error( esc_html__( 'Could not install the plugin. Don\'t have file permission.', 'easy-wp-smtp' ) );
		}

		if ( ! WP_Filesystem( $creds ) ) {
			wp_send_json_error( esc_html__( 'Could not install the plugin. Don\'t have file permission.', 'easy-wp-smtp' ) );
		}

		// Do not allow WordPress to search/download translations, as this will break JS output.
		remove_action( 'upgrader_process_complete', [ 'Language_Pack_Upgrader', 'async_upgrade' ], 20 );

		// Create the plugin upgrader with our custom skin.
		$installer = new PluginsInstallUpgrader( new PluginsInstallSkin() );

		// Error check.
		if ( ! method_exists( $installer, 'install' ) || empty( $slug ) ) {
			wp_send_json_error( esc_html__( 'Could not install the plugin. WP Plugin installer initialization failed.', 'easy-wp-smtp' ) );
		}

		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		$api = plugins_api(
			'plugin_information',
			[
				'slug'   => $slug,
				'fields' => [
					'short_description' => false,
					'sections'          => false,
					'requires'          => false,
					'rating'            => false,
					'ratings'           => false,
					'downloaded'        => false,
					'last_updated'      => false,
					'added'             => false,
					'tags'              => false,
					'compatibility'     => false,
					'homepage'          => false,
					'donate_link'       => false,
				],
			]
		);

		if ( is_wp_error( $api ) ) {
			wp_send_json_error( $api->get_error_message() );
		}

		$installer->install( $api->download_link );

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();

		if ( $installer->plugin_info() ) {
			$plugin_basename = $installer->plugin_info();

			// Disable the WPForms redirect after plugin activation.
			if ( $slug === 'wpforms-lite' ) {
				update_option( 'wpforms_activation_redirect', true );
			}

			// Disable the AIOSEO redirect after plugin activation.
			if ( $slug === 'all-in-one-seo-pack' ) {
				update_option( 'aioseo_activation_redirect', true );
			}

			// Activate the plugin silently.
			$activated = activate_plugin( $plugin_basename );

			if ( ! is_wp_error( $activated ) ) {
				wp_send_json_success(
					[
						'slug'         => $slug,
						'is_installed' => true,
						'is_activated' => true,
					]
				);
			} else {
				wp_send_json_success(
					[
						'slug'         => $slug,
						'is_installed' => true,
						'is_activated' => false,
					]
				);
			}
		}

		wp_send_json_error( esc_html__( 'Could not install the plugin. WP Plugin installer could not retrieve plugin information.', 'easy-wp-smtp' ) );
	}

	/**
	 * AJAX callback for getting all partner's plugin information.
	 *
	 * @since 2.1.0
	 * @since 2.2.0 Check if a SEO toolkit plugin is installed.
	 */
	public function get_partner_plugins_info() {

		check_ajax_referer( 'easywpsmtp-admin-nonce', 'nonce' );

		$contact_form_plugin_already_installed = false;
		$seo_toolkit_plugin_already_installed  = false;

		$contact_form_basenames = [
			'wpforms-lite/wpforms.php',
			'wpforms/wpforms.php',
			'formidable/formidable.php',
			'formidable/formidable-pro.php',
			'gravityforms/gravityforms.php',
			'ninja-forms/ninja-forms.php',
		];

		$seo_toolkit_basenames = [
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'all-in-one-seo-pack-pro/all_in_one_seo_pack.php',
			'seo-by-rank-math/rank-math.php',
			'seo-by-rank-math-pro/rank-math-pro.php',
			'wordpress-seo/wp-seo.php',
			'wordpress-seo-premium/wp-seo-premium.php',
		];

		$installed_plugins = get_plugins();

		foreach ( $installed_plugins as $basename => $plugin_info ) {
			if ( in_array( $basename, $contact_form_basenames, true ) ) {
				$contact_form_plugin_already_installed = true;
			} elseif ( in_array( $basename, $seo_toolkit_basenames, true ) ) {
				$seo_toolkit_plugin_already_installed = true;
			}
		}

		// Final check if maybe WPForms is already install and active as a MU plugin.
		if ( class_exists( '\WPForms\WPForms' ) ) {
			$contact_form_plugin_already_installed = true;
		}

		$data = [
			'plugins'                               => [],
			'contact_form_plugin_already_installed' => $contact_form_plugin_already_installed,
			'seo_toolkit_plugin_already_installed'  => $seo_toolkit_plugin_already_installed,
		];

		wp_send_json_success( $data );
	}

	/**
	 * AJAX callback for subscribing an email address to the Easy WP SMTP Drip newsletter.
	 *
	 * @since 2.1.0
	 */
	public function subscribe_to_newsletter() {

		check_ajax_referer( 'easywpsmtp-admin-nonce', 'nonce' );

		$email = ! empty( $_POST['email'] ) ? filter_var( wp_unslash( $_POST['email'] ), FILTER_VALIDATE_EMAIL ) : '';

		if ( empty( $email ) ) {
			wp_send_json_error();
		}

		$body = [
			'email' => base64_encode( $email ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		];

		$wpforms_version_type = $this->get_wpforms_version_type();

		if ( ! empty( $wpforms_version_type ) ) {
			$body['wpforms_version_type'] = $wpforms_version_type;
		}

		wp_remote_post(
			'https://connect.easywpsmtp.com/subscribe/drip/',
			[
				'user-agent' => Helpers::get_default_user_agent(),
				'body'       => $body,
			]
		);

		wp_send_json_success();
	}

	/**
	 * Get the WPForms version type if it's installed.
	 *
	 * @since 2.2.0
	 *
	 * @return false|string Return `false` if WPForms is not installed, otherwise return either `lite` or `pro`.
	 */
	private function get_wpforms_version_type() {

		if ( ! function_exists( 'wpforms' ) ) {
			return false;
		}

		if ( method_exists( wpforms(), 'is_pro' ) ) {
			$is_wpforms_pro = wpforms()->is_pro();
		} else {
			$is_wpforms_pro = wpforms()->pro;
		}

		return $is_wpforms_pro ? 'pro' : 'lite';
	}

	/**
	 * AJAX callback for plugin upgrade, from lite to pro.
	 *
	 * @since 2.1.0
	 */
	public function upgrade_plugin() {

		check_ajax_referer( 'easywpsmtp-admin-nonce', 'nonce' );

		if ( easy_wp_smtp()->is_pro() ) {
			wp_send_json_success( esc_html__( 'You are already using the Easy WP SMTP PRO version. Please refresh this page and verify your license key.', 'easy-wp-smtp' ) );
		}

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( esc_html__( 'You don\'t have the permission to perform this action.', 'easy-wp-smtp' ) );
		}

		$license_key = ! empty( $_POST['license_key'] ) ? sanitize_key( $_POST['license_key'] ) : '';

		if ( empty( $license_key ) ) {
			wp_send_json_error( esc_html__( 'Please enter a valid license key!', 'easy-wp-smtp' ) );
		}

		$url = Connect::generate_url(
			$license_key,
			'',
			add_query_arg( 'upgrade-redirect', '1', self::get_site_url() ) . '#/step/license'
		);

		if ( empty( $url ) ) {
			wp_send_json_error( esc_html__( 'Upgrade functionality not available!', 'easy-wp-smtp' ) );
		}

		wp_send_json_success( [ 'redirect_url' => $url ] );
	}

	/**
	 * AJAX callback for checking the mailer configuration.
	 * - Send a test email
	 * - Check the domain setup with the Domain Checker API.
	 *
	 * @since 2.1.0
	 */
	public function check_mailer_configuration() {

		check_ajax_referer( 'easywpsmtp-admin-nonce', 'nonce' );

		$options = Options::init();
		$mailer  = $options->get( 'mail', 'mailer' );
		$email   = $options->get( 'mail', 'from_email' );
		$domain  = '';

		// Send the test mail.
		$result = wp_mail(
			$email,
			'Easy WP SMTP Automatic Email Test',
			TestTab::get_email_message_text(),
			array(
				'X-Mailer-Type:EasyWPSMTP/Admin/SetupWizard/Test',
			)
		);

		if ( ! $result ) {
			$this->update_completed_stat( false );

			( new UsageTracking() )->send_failed_setup_wizard_usage_tracking_data();

			wp_send_json_error();
		}

		// Add the optional sending domain parameter.
		if ( in_array( $mailer, [ 'mailgun', 'sendinblue', 'sendgrid' ], true ) ) {
			$domain = $options->get( $mailer, 'domain' );
		}

		// Perform the domain checker API test.
		$domain_checker = new DomainChecker( $mailer, $email, $domain );

		if ( $domain_checker->has_errors() ) {
			$this->update_completed_stat( false );

			( new UsageTracking() )->send_failed_setup_wizard_usage_tracking_data( $domain_checker );

			wp_send_json_error();
		}

		$this->update_completed_stat( true );

		wp_send_json_success();
	}

	/**
	 * AJAX callback for sending feedback.
	 *
	 * @since 2.1.0
	 */
	public function send_feedback() {

		check_ajax_referer( 'easywpsmtp-admin-nonce', 'nonce' );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$data = ! empty( $_POST['data'] ) ? json_decode( wp_unslash( $_POST['data'] ), true ) : [];

		$feedback   = ! empty( $data['feedback'] ) ? sanitize_textarea_field( $data['feedback'] ) : '';
		$permission = ! empty( $data['permission'] );

		wp_remote_post(
			'https://easywpsmtp.com/wizard-feedback/',
			[
				'user-agent' => Helpers::get_default_user_agent(),
				'body'       => [
					'wpforms' => [
						'id'     => 2271,
						'fields' => [
							'1' => $feedback,
							'2' => $permission ? wp_get_current_user()->user_email : '',
							'3' => easy_wp_smtp()->get_license_type(),
							'4' => EasyWPSMTP_PLUGIN_VERSION,
						],
					],
				],
			]
		);

		wp_send_json_success();
	}

	/**
	 * Data used for the Vue scripts to display old PHP and WP versions warnings.
	 *
	 * @since 2.1.0
	 */
	private function prepare_versions_data() {

		global $wp_version;

		return [
			'php_version'          => phpversion(),
			'php_version_below_56' => apply_filters( 'easy_wp_smtp_temporarily_hide_php_under_55_upgrade_warnings', version_compare( phpversion(), '5.6', '<' ) ),
			'wp_version'           => $wp_version,
			'wp_version_below_52'  => version_compare( $wp_version, '5.2', '<' ),
		];
	}

	/**
	 * Remove 'error' from the automatic clearing list of query arguments after page loads.
	 * This will fix the issue with missing oAuth 'error' argument for the Setup Wizard.
	 *
	 * @since 2.1.0
	 *
	 * @param array $defaults Array of query arguments to be cleared after page load.
	 *
	 * @return array
	 */
	public function maybe_disable_automatic_query_args_removal( $defaults ) {

		if (
			( isset( $_GET['page'] ) && $_GET['page'] === 'easy-wp-smtp-setup-wizard' ) && // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			( ! empty( $_GET['error'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		) {
			$defaults = array_values( array_diff( $defaults, [ 'error' ] ) );
		}

		return $defaults;
	}

	/**
	 * Check if the Setup Wizard should load.
	 *
	 * @since 2.1.0
	 *
	 * @return bool
	 */
	public function should_setup_wizard_load() {

		return (bool) apply_filters( 'easy_wp_smtp_admin_setup_wizard_load_wizard', true );
	}

	/**
	 * Get the Setup Wizard stats.
	 * - launched_time  -> when the Setup Wizard was last launched.
	 * - completed_time -> when the Setup Wizard was last completed.
	 * - was_successful -> if the Setup Wizard was completed successfully.
	 *
	 * @since 2.1.0
	 *
	 * @return array
	 */
	public static function get_stats() {

		$defaults = [
			'launched_time'  => 0,
			'completed_time' => 0,
			'was_successful' => false,
		];

		return get_option( self::STATS_OPTION_KEY, $defaults );
	}

	/**
	 * Update the Setup Wizard stats.
	 *
	 * @since 2.1.0
	 *
	 * @param array $options Take a look at SetupWizard::get_stats method for the possible array keys.
	 */
	public static function update_stats( $options ) {

		update_option( self::STATS_OPTION_KEY, array_merge( self::get_stats(), $options ) , false );
	}

	/**
	 * Update the completed Setup Wizard stats.
	 *
	 * @since 2.1.0
	 *
	 * @param bool $was_successful If the Setup Wizard was completed successfully.
	 */
	private function update_completed_stat( $was_successful ) {

		self::update_stats(
			[
				'completed_time' => time(),
				'was_successful' => $was_successful,
			]
		);
	}

	/**
	 * Prepare an array of Easy WP SMTP PHP constants in use.
	 * Those that are used in the setup wizard.
	 *
	 * @since 2.1.0
	 *
	 * @return array
	 */
	private function prepare_defined_constants() {

		$options = Options::init();

		if ( ! $options->is_const_enabled() ) {
			return [];
		}

		$constants = [
			'EasyWPSMTP_MAIL_FROM'                     => [ 'mail', 'from_email' ],
			'EasyWPSMTP_MAIL_FROM_FORCE'               => [ 'mail', 'from_email_force' ],
			'EasyWPSMTP_MAIL_FROM_NAME'                => [ 'mail', 'from_name' ],
			'EasyWPSMTP_MAIL_FROM_NAME_FORCE'          => [ 'mail', 'from_name_force' ],
			'EasyWPSMTP_MAILER'                        => [ 'mail', 'mailer' ],
			'EasyWPSMTP_SMTPCOM_API_KEY'               => [ 'smtpcom', 'api_key' ],
			'EasyWPSMTP_SMTPCOM_CHANNEL'               => [ 'smtpcom', 'channel' ],
			'EasyWPSMTP_SENDINBLUE_API_KEY'            => [ 'sendinblue', 'api_key' ],
			'EasyWPSMTP_SENDINBLUE_DOMAIN'             => [ 'sendinblue', 'domain' ],
			'EasyWPSMTP_AMAZONSES_CLIENT_ID'           => [ 'amazonses', 'client_id' ],
			'EasyWPSMTP_AMAZONSES_CLIENT_SECRET'       => [ 'amazonses', 'client_secret' ],
			'EasyWPSMTP_AMAZONSES_REGION'              => [ 'amazonses', 'region' ],
			'EasyWPSMTP_MAILGUN_API_KEY'               => [ 'mailgun', 'api_key' ],
			'EasyWPSMTP_MAILGUN_DOMAIN'                => [ 'mailgun', 'domain' ],
			'EasyWPSMTP_MAILGUN_REGION'                => [ 'mailgun', 'region' ],
			'EasyWPSMTP_OUTLOOK_CLIENT_ID'             => [ 'outlook', 'client_id' ],
			'EasyWPSMTP_OUTLOOK_CLIENT_SECRET'         => [ 'outlook', 'client_secret' ],
			'EasyWPSMTP_SENDGRID_API_KEY'              => [ 'sendgrid', 'api_key' ],
			'EasyWPSMTP_SENDGRID_DOMAIN'               => [ 'sendgrid', 'domain' ],
			'EasyWPSMTP_SMTP_HOST'                     => [ 'smtp', 'host' ],
			'EasyWPSMTP_SMTP_PORT'                     => [ 'smtp', 'port' ],
			'EasyWPSMTP_SSL'                           => [ 'smtp', 'encryption' ],
			'EasyWPSMTP_SMTP_AUTH'                     => [ 'smtp', 'auth' ],
			'EasyWPSMTP_SMTP_AUTOTLS'                  => [ 'smtp', 'autotls' ],
			'EasyWPSMTP_SMTP_USER'                     => [ 'smtp', 'user' ],
			'EasyWPSMTP_SMTP_PASS'                     => [ 'smtp', 'pass' ],
			'EasyWPSMTP_LOGS_ENABLED'                  => [ 'logs', 'enabled' ],
			'EasyWPSMTP_SUMMARY_REPORT_EMAIL_DISABLED' => [ 'general', SummaryReportEmail::SETTINGS_SLUG ],
		];

		$defined = [];

		foreach ( $constants as $constant => $group_and_key ) {
			if ( $options->is_const_defined( $group_and_key[0], $group_and_key[1] ) ) {
				$defined[] = $constant;
			}
		}

		return $defined;
	}
}
