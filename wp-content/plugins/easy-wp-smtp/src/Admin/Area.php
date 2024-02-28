<?php

namespace EasyWPSMTP\Admin;

use EasyWPSMTP\WP;
use EasyWPSMTP\Options;

/**
 * Class Area registers and process all wp-admin display functionality.
 *
 * @since 2.0.0
 */
class Area {

	/**
	 * Slug of the admin area page.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	const SLUG = 'easy-wp-smtp';

	/**
	 * Admin page unique hook.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $hook;

	/**
	 * List of admin area pages.
	 *
	 * @since 2.0.0
	 *
	 * @var PageAbstract[]
	 */
	private $pages;

	/**
	 * List of official registered pages.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	public static $pages_registered = [ 'general', 'logs', 'tools', 'reports' ];

	/**
	 * Area constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->hooks();
	}

	/**
	 * Assign all hooks to proper places.
	 *
	 * @since 2.0.0
	 */
	protected function hooks() {

		// Redirect from deprecated settings page.
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'swpsmtp_settings' && WP::in_wp_admin() ) {
			wp_safe_redirect( $this->get_admin_page_url() );
			exit();
		}

		// Add the Settings link to a plugin on Plugins page.
		add_filter( 'plugin_action_links_' . plugin_basename( EasyWPSMTP_PLUGIN_FILE ), [ $this, 'add_plugin_action_link' ], 10, 1 );

		// Add the options page.
		add_action( 'admin_menu', [ $this, 'add_admin_options_page' ] );

		// Register on load Email Log admin menu hook.
		add_action( 'load-' . $this->get_admin_page_hook( 'logs' ), [ $this, 'maybe_redirect_email_log_menu_to_email_log_settings_tab' ] );

		// Enqueue admin area scripts and styles.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );

		// Process the admin page forms actions.
		add_action( 'admin_init', [ $this, 'process_actions' ] );

		// Display custom notices based on the error/success codes.
		add_action( 'admin_init', [ $this, 'display_custom_auth_notices' ] );

		// Display notice instructing the user to complete plugin setup.
		add_action( 'admin_init', [ $this, 'display_setup_notice' ] );

		// Outputs the plugin admin header.
		add_action( 'in_admin_header', [ $this, 'display_admin_header' ], 100 );

		// Admin footer text.
		add_filter( 'admin_footer_text', [ $this, 'get_admin_footer' ], 1, 2 );

		// Hide all unrelated to the plugin notices on the plugin admin pages.
		add_action( 'admin_print_scripts', [ $this, 'hide_unrelated_notices' ] );

		// Process all AJAX requests.
		add_action( 'wp_ajax_easy_wp_smtp_ajax', [ $this, 'process_ajax' ] );

		// Init parent admin pages.
		if ( WP::in_wp_admin() || WP::is_doing_self_ajax() ) {
			add_action( 'init', [ $this, 'get_parent_pages' ] );
		}

		( new UserFeedback() )->init();
		( new SetupWizard() )->hooks();

		// Enable "Compact Mode" menu view.
		if ( $this->is_top_level_menu_hidden() ) {
			if ( $this->is_admin_page() ) {
				global $pagenow;

				// Redirect from `options-general.php`.
				if ( WP::in_wp_admin() && $pagenow === 'options-general.php' ) {
					wp_safe_redirect( $this->get_admin_page_url() );
					exit();
				}

				// Highlight "Settings -> Easy WP SMTP" menu item on any plugin admin page.
				add_filter( 'submenu_file', function () {
					return self::SLUG;
				} );
			}

			// Hide all top level pages from "Settings" submenu.
			add_action( 'admin_head', function () {
				global $submenu;

				if ( isset( $submenu['options-general.php'] ) && is_array( $submenu['options-general.php'] ) ) {
					foreach ( $submenu['options-general.php'] as $key => $menu_item ) {
						if ( isset( $menu_item[2] ) && strpos( $menu_item[2], self::SLUG . '-' ) !== false ) {
							unset( $submenu['options-general.php'][ $key ] );
						}
					}
				}
			} );
		}
	}

	/**
	 * Display custom notices based on the error/success codes.
	 *
	 * @since 2.1.0
	 */
	public function display_custom_auth_notices() {

		$error   = isset( $_GET['error'] ) ? sanitize_key( $_GET['error'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$success = isset( $_GET['success'] ) ? sanitize_key( $_GET['success'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( empty( $error ) && empty( $success ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		switch ( $error ) {
			case 'oauth_invalid_state':
				WP::add_admin_notice(
					esc_html__( 'There was an error while processing the authentication request. The state key is invalid. Please try again.', 'easy-wp-smtp' ),
					WP::ADMIN_NOTICE_ERROR
				);
				break;
		}
	}

	/**
	 * Display notice instructing the user to complete plugin setup.
	 *
	 * @since 2.0.0
	 */
	public function display_setup_notice() {

		// Bail if we're not on a plugin page.
		if ( ! $this->is_admin_page( 'general' ) ) {
			return;
		}

		$default_options = wp_json_encode( Options::get_defaults() );
		$current_options = wp_json_encode( Options::init()->get_all() );

		// Check if the current settings are the same as the default settings.
		if ( $current_options !== $default_options ) {
			return;
		}

		// Display notice informing user further action is needed.
		WP::add_admin_notice(
			sprintf(
				wp_kses( /* translators: %s - Mailer anchor link. */
					__( 'Thanks for using Easy WP SMTP! To complete the plugin setup and start sending emails, <strong>please select and configure your <a href="%s">Mailer</a></strong>.', 'easy-wp-smtp' ),
					[
						'a'      => [
							'href' => [],
						],
						'strong' => [],
					]
				),
				easy_wp_smtp()->get_admin()->get_admin_page_url( self::SLUG . '#easy-wp-smtp-setting-row-mailer' )
			),
			WP::ADMIN_NOTICE_INFO
		);
	}

	/**
	 * Get menu item position.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_menu_item_position() {

		/**
		 * Filters menu item position.
		 *
		 * @since 2.0.0
		 *
		 * @param int $position Position number.
		 */
		return apply_filters( 'easy_wp_smtp_admin_area_get_menu_item_position', 98 );
	}

	/**
	 * Add admin area menu item.
	 *
	 * @since 2.0.0
	 */
	public function add_admin_options_page() {

		// Options pages access capability.
		$access_capability = 'manage_options';

		if ( $this->is_top_level_menu_hidden() ) {
			$this->hook = add_options_page(
				esc_html__( 'Easy WP SMTP', 'easy-wp-smtp' ),
				esc_html__( 'Easy WP SMTP', 'easy-wp-smtp' ),
				$access_capability,
				self::SLUG,
				[ $this, 'display' ]
			);
		} else {
			$this->hook = add_menu_page(
				esc_html__( 'Easy WP SMTP', 'easy-wp-smtp' ),
				esc_html__( 'Easy WP SMTP', 'easy-wp-smtp' ),
				$access_capability,
				self::SLUG,
				[ $this, 'display' ],
				'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMTMiIHZpZXdCb3g9IjAgMCAyMCAxMyIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTUuODgyMTEgMTEuMzI4NkM2LjAxMzM2IDExLjI1ODggNi4xNjAxMiAxMS4yMjIyIDYuMzA5MjIgMTEuMjIyMkwxMy42OTA4IDExLjIyMjJDMTMuODM5OSAxMS4yMjIyIDEzLjk4NjYgMTEuMjU4OCAxNC4xMTc5IDExLjMyODZDMTQuOTQxMiAxMS43NjY2IDE0LjYyNiAxMyAxMy42OTA4IDEzTDYuMzA5MjEgMTNDNS4zNzQwMSAxMyA1LjA1ODgzIDExLjc2NjYgNS44ODIxMSAxMS4zMjg2WiIgZmlsbD0id2hpdGUiLz4KPHBhdGggZD0iTTMuMTMzNTQgOC4yMTc0OUMzLjI2MjYzIDguMTQ3NjcgMy40MDY5OCA4LjExMTExIDMuNTUzNjIgOC4xMTExMUwxNi40NDY0IDguMTExMTFDMTYuNTkzIDguMTExMTEgMTYuNzM3NCA4LjE0NzY3IDE2Ljg2NjUgOC4yMTc0OUMxNy42NzYyIDguNjU1NSAxNy4zNjYyIDkuODg4ODkgMTYuNDQ2NCA5Ljg4ODg5TDMuNTUzNjIgOS44ODg4OUMyLjYzMzc5IDkuODg4ODkgMi4zMjM4IDguNjU1NSAzLjEzMzU0IDguMjE3NDlaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMi4yNjMzNCAwLjUzNjY5M0MyLjEyMTQyIDAuNjY4NzY5IDEuOTk5MDIgMC44MjQwMzMgMS45MDIzNSAwLjk5ODgyM0wwLjIzNTM4MiA0LjAxMjg3Qy0wLjQ1MTQ3OCA1LjI1NDc4IDAuNDQ3MTA2IDYuNzc3NzggMS44NjY3MSA2Ljc3Nzc4TDE4LjEzMzMgNi43Nzc3OEMxOS41NTI5IDYuNzc3NzggMjAuNDUxNSA1LjI1NDc4IDE5Ljc2NDYgNC4wMTI4N0wxOC4wOTc2IDAuOTk4ODIyQzE3Ljk5MjMgMC44MDgzNTQgMTcuODU2NCAwLjY0MTA3MiAxNy42OTggMC41MDE3MTRDMTYuMDk1OSAxLjUwNTg5IDEyLjA5MTQgMy44NzM3NyA5Ljk1MjcyIDMuODczNzdDNy44Mzg0OSAzLjg3Mzc3IDMuOTAwODcgMS41NTk3MyAyLjI2MzM0IDAuNTM2NjkzWiIgZmlsbD0id2hpdGUiLz4KPHBhdGggZD0iTTIuOTYwNjMgMC4xMjcyMzNDNC43MjU2NiAxLjE5ODU5IDguMDk5MzEgMy4wODY3NyA5Ljk1MjcyIDMuMDg2NzdDMTEuODE3MiAzLjA4Njc3IDE1LjIyMDIgMS4xNzU5MiAxNi45NzYzIDAuMTA4MDlDMTYuODEyNyAwLjA2MTU0MjMgMTYuNjQxMyAwLjAzNzAzOSAxNi40NjYzIDAuMDM3MDM5TDMuNTMzNjggMC4wMzcwMzc4QzMuMzM2MTIgMC4wMzcwMzc5IDMuMTQzMSAwLjA2ODI4MjcgMi45NjA2MyAwLjEyNzIzM1oiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo=',
				$this->get_menu_item_position()
			);

			add_submenu_page(
				self::SLUG,
				$this->get_current_tab_title() . ' &lsaquo; ' . esc_html__( 'Settings', 'easy-wp-smtp' ),
				esc_html__( 'Settings', 'easy-wp-smtp' ),
				$access_capability,
				self::SLUG,
				[ $this, 'display' ]
			);

			add_submenu_page(
				self::SLUG,
				esc_html__( 'Send a Test', 'easy-wp-smtp' ),
				esc_html__( 'Send a Test', 'easy-wp-smtp' ),
				$access_capability,
				self::SLUG . '-tools&tab=test',
				[ $this, 'display' ]
			);
		}

		$parent_slug = $this->is_top_level_menu_hidden() ? 'options-general.php' : self::SLUG;

		add_submenu_page(
			$parent_slug,
			esc_html__( 'Email Log', 'easy-wp-smtp' ),
			esc_html__( 'Email Log', 'easy-wp-smtp' ),
			$this->get_logs_access_capability(),
			self::SLUG . '-logs',
			[ $this, 'display' ]
		);

		foreach ( $this->get_parent_pages() as $page ) {
			add_submenu_page(
				$parent_slug,
				esc_html( $page->get_title() ),
				esc_html( $page->get_label() ),
				$access_capability,
				self::SLUG . '-' . $page->get_slug(),
				[ $this, 'display' ]
			);
		}
	}

	/**
	 * Redirect the "Email Log" WP menu link to the "Email Log" setting tab for lite version of the plugin.
	 *
	 * @since 2.1.0
	 */
	public function maybe_redirect_email_log_menu_to_email_log_settings_tab() {

		/**
		 * The Email Logs object to be used for loading the Email Log page.
		 *
		 * @var \EasyWPSMTP\Admin\PageAbstract $logs
		 */
		$logs = $this->generate_display_logs_object();

		if ( $logs instanceof \EasyWPSMTP\Admin\Pages\Logs ) {
			wp_safe_redirect( $logs->get_link() );
			exit;
		}
	}

	/**
	 * Enqueue admin area scripts and styles.
	 *
	 * @since 2.0.0
	 *
	 * @param string $hook Current hook.
	 */
	public function enqueue_assets( $hook ) {

		if ( strpos( $hook, self::SLUG ) === false ) {
			return;
		}

		// Set general body class.
		add_filter(
			'admin_body_class',
			function ( $classes ) {
				$classes .= ' easy-wp-smtp-admin-page-body';

				if ( easy_wp_smtp()->is_pro() ) {
					$classes .= ' easy-wp-smtp-pro';
				} else {
					$classes .= ' easy-wp-smtp-lite';
				}

				if ( apply_filters( 'easy_wp_smtp_admin_area_full_width_page', false ) ) {
					$classes .= ' easy-wp-smtp-full-width-page';
				}

				return $classes;
			}
		);

		// General styles and js.
		wp_enqueue_style(
			'easy-wp-smtp-admin',
			easy_wp_smtp()->assets_url . '/css/smtp-admin.min.css',
			false,
			EasyWPSMTP_PLUGIN_VERSION
		);

		wp_enqueue_script( 'underscore' );

		wp_enqueue_script(
			'easy-wp-smtp-admin',
			easy_wp_smtp()->assets_url . '/js/smtp-admin' . WP::asset_min() . '.js',
			[ 'jquery', 'underscore' ],
			EasyWPSMTP_PLUGIN_VERSION,
			false
		);

		$script_data = [
			'text_provider_remove'    => esc_html__( 'Are you sure you want to reset the current provider connection? You will need to immediately create a new one to be able to send emails.', 'easy-wp-smtp' ),
			'text_settings_not_saved' => esc_html__( 'Changes that you made to the settings are not saved!', 'easy-wp-smtp' ),
			'default_mailer_notice'   => [
				'title'         => esc_html__( 'Heads up!', 'easy-wp-smtp' ),
				'content'       => wp_kses(
					__( '<p>The Default (PHP) mailer is currently selected, but is not recommended because in most cases it does not resolve email delivery issues.</p><p>Please consider selecting and configuring one of the other mailers.</p>', 'easy-wp-smtp' ),
					[ 'p' => [] ]
				),
				'save_button'   => esc_html__( 'Save Settings', 'easy-wp-smtp' ),
				'cancel_button' => esc_html__( 'Cancel', 'easy-wp-smtp' ),
				'icon_alt'      => esc_html__( 'Warning icon', 'easy-wp-smtp' ),
			],
			'plugin_url'              => easy_wp_smtp()->plugin_url,
			'education'               => [
				'upgrade_icon_lock' => '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="lock" class="svg-inline--fa fa-lock fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M400 224h-24v-72C376 68.2 307.8 0 224 0S72 68.2 72 152v72H48c-26.5 0-48 21.5-48 48v192c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V272c0-26.5-21.5-48-48-48zm-104 0H152v-72c0-39.7 32.3-72 72-72s72 32.3 72 72v72z"></path></svg>',
				'upgrade_title'     => esc_html__( '%name% is a PRO Feature', 'easy-wp-smtp' ),
				'upgrade_content'   => esc_html__( 'Sorry, but the %name% mailer isn’t available in the lite version. Please upgrade to PRO to unlock this mailer and much more.', 'easy-wp-smtp' ),
				'upgrade_button'    => esc_html__( 'Upgrade to Pro', 'easy-wp-smtp' ),
				'upgrade_url'       => add_query_arg( 'discount', 'SMTPLITEUPGRADE', easy_wp_smtp()->get_upgrade_link( '' ) ),
				'upgrade_bonus'     => '<div class="easy-wp-smtp-upgrade-bonus-badge"><span>' .
											sprintf(
												wp_kses( /* Translators: %s - discount value 50%. */
													__( '<strong>%s OFF</strong> for Easy WP SMTP users, applied at checkout.', 'easy-wp-smtp' ),
													[
														'strong' => [],
													]
												),
												'50%'
											)
											. '</span></div>',
				'upgrade_doc'       => sprintf(
					'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
					// phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
					esc_url( easy_wp_smtp()->get_utm_url( 'https://easywpsmtp.com/docs/how-to-upgrade-easy-wp-smtp-to-pro-version/', [ 'medium' => 'plugin-settings', 'content' => 'Pro Mailer Popup - Already purchased' ] ) ),
					esc_html__( 'Already purchased?', 'easy-wp-smtp' )
				),
			],
			'all_mailers_supports'    => easy_wp_smtp()->get_providers()->get_supports_all(),
			'nonce'                   => wp_create_nonce( 'easy-wp-smtp-admin' ),
			'is_network_admin'        => is_network_admin(),
			'ajax_url'                => admin_url( 'admin-ajax.php' ),
			'icon'                    => esc_html__( 'Icon', 'easy-wp-smtp' ),
			'heads_up_title'          => esc_html__( 'Heads up!', 'easy-wp-smtp' ),
			'yes_text'                => esc_html__( 'Yes', 'easy-wp-smtp' ),
			'cancel_text'             => esc_html__( 'Cancel', 'easy-wp-smtp' ),
			'ok_text'                 => esc_html__( 'OK', 'easy-wp-smtp' ),
			'error_occurred'          => esc_html__( 'An error occurred!', 'easy-wp-smtp' ),
			'lang_code'               => sanitize_key( WP::get_language_code() ),
			'clear_debug_log'         => esc_html__( 'Are you sure want to clear log?', 'easy-wp-smtp' ),
			'debug_log_cleared'       => esc_html__( 'Log cleared.', 'easy-wp-smtp' ),
		];

		/**
		 * Filters plugin script data.
		 *
		 * @since 2.0.0
		 *
		 * @param array  $script_data Data.
		 * @param string $hook        Current hook.
		 */
		$script_data = apply_filters( 'easy_wp_smtp_admin_area_enqueue_assets_scripts_data', $script_data, $hook );

		wp_localize_script( 'easy-wp-smtp-admin', 'easy_wp_smtp', $script_data );

		/*
		 * jQuery Confirm library v3.3.4.
		 */
		wp_enqueue_style(
			'easy-wp-smtp-admin-jconfirm',
			easy_wp_smtp()->assets_url . '/css/vendor/jquery-confirm.min.css',
			[ 'easy-wp-smtp-admin' ],
			'3.3.4'
		);
		wp_enqueue_script(
			'easy-wp-smtp-admin-jconfirm',
			easy_wp_smtp()->assets_url . '/js/vendor/jquery-confirm.min.js',
			[ 'easy-wp-smtp-admin' ],
			'3.3.4',
			false
		);

		/*
		 * Logs page.
		 */
		if ( $this->is_admin_page( 'logs' ) ) {
			wp_enqueue_style(
				'easy-wp-smtp-admin-logs',
				apply_filters( 'easy_wp_smtp_admin_enqueue_assets_logs_css', '' ),
				[ 'easy-wp-smtp-admin' ],
				EasyWPSMTP_PLUGIN_VERSION
			);

			wp_enqueue_script(
				'easy-wp-smtp-admin-logs',
				apply_filters( 'easy_wp_smtp_admin_enqueue_assets_logs_js', '' ),
				[ 'easy-wp-smtp-admin' ],
				EasyWPSMTP_PLUGIN_VERSION,
				false
			);
		}

		/**
		 * Fires after enqueue plugin assets.
		 *
		 * @since 2.0.0
		 *
		 * @param string $hook Current hook.
		 */
		do_action( 'easy_wp_smtp_admin_area_enqueue_assets', $hook );
	}

	/**
	 * Outputs the plugin admin header.
	 *
	 * @since 2.0.0
	 */
	public function display_admin_header() {

		// Bail if we're not on a plugin page.
		if ( ! $this->is_admin_page() ) {
			return;
		}

		do_action( 'easy_wp_smtp_admin_header_before' );
		?>
		<div id="easy-wp-smtp-header-temp"></div>

		<div class="easy-wp-smtp-header">
			<div class="easy-wp-smtp-header__inner easy-wp-smtp-container">
				<img class="easy-wp-smtp-header__logo" src="<?php echo esc_url( easy_wp_smtp()->assets_url ); ?>/images/logo.svg" alt="Easy WP SMTP"/>

				<?php if ( $this->is_top_level_menu_hidden() ) : ?>
					<div class="easy-wp-smtp-header-menu easy-wp-smtp-header__menu">
						<a href="<?php echo esc_url( $this->get_admin_page_url() ); ?>" class="easy-wp-smtp-header-menu__link<?php echo $this->is_admin_page('general') ? ' easy-wp-smtp-header-menu__link--active' : ''; ?>"><?php esc_html_e( 'General', 'easy-wp-smtp' ); ?></a>
						<a href="<?php echo esc_url( $this->get_admin_page_url( self::SLUG . '-tools', 'test' ) ); ?>" class="easy-wp-smtp-header-menu__link"><?php esc_html_e( 'Send a Test', 'easy-wp-smtp' ); ?></a>
						<a href="<?php echo esc_url( $this->get_admin_page_url( self::SLUG . '-logs' ) ); ?>" class="easy-wp-smtp-header-menu__link<?php echo $this->is_admin_page('logs') ? ' easy-wp-smtp-header-menu__link--active' : ''; ?>"><?php esc_html_e( 'Email Log', 'easy-wp-smtp' ); ?></a>

						<?php foreach ( $this->get_parent_pages() as $parent_page ) : ?>
							<a href="<?php echo esc_url( $parent_page->get_link() ); ?>" class="easy-wp-smtp-header-menu__link<?php echo $this->is_admin_page( $parent_page->get_slug() ) ? ' easy-wp-smtp-header-menu__link--active' : ''; ?>"><?php echo esc_html( $parent_page->get_label() ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<a class="easy-wp-smtp-header__help-link" href="<?php echo esc_url( easy_wp_smtp()->get_utm_url( 'https://easywpsmtp.com/docs/', [ 'medium' => 'Top Header', 'content' => 'Help Link' ] ) ); ?>" target="_blank" rel="noopener noreferrer">
					<svg width="16" height="16" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#a)" fill="currentColor"><path d="M8 14.222A6.222 6.222 0 1 1 8 1.778a6.222 6.222 0 0 1 0 12.444zm0 .89A7.111 7.111 0 1 0 8 .888 7.111 7.111 0 0 0 8 15.11z" stroke="#53536B" stroke-width=".444"/><path d="M5.56 6.032a.21.21 0 0 0 .214.22h.734c.122 0 .22-.1.236-.223.08-.583.48-1.008 1.193-1.008.61 0 1.168.305 1.168 1.039 0 .564-.333.824-.858 1.218-.598.435-1.072.942-1.038 1.766l.003.193a.222.222 0 0 0 .222.219h.72a.222.222 0 0 0 .223-.222V9.14c0-.638.243-.824.898-1.32.541-.412 1.105-.869 1.105-1.828C10.38 4.649 9.246 4 8.004 4c-1.126 0-2.36.524-2.444 2.032zm1.384 5.123c0 .473.378.824.898.824.541 0 .914-.35.914-.824 0-.491-.374-.836-.915-.836-.52 0-.897.345-.897.836z"/></g><defs><clipPath id="a"><path fill="#fff" d="M0 0h16v16H0z"/></clipPath></defs></svg>
					<?php esc_html_e( 'Help', 'easy-wp-smtp' ); ?>
				</a>
			</div>
		</div>

		<?php
	}

	/**
	 * Display a text to ask users to review the plugin on WP.org.
	 *
	 * @since 2.0.0
	 *
	 * @param string $text The default text to display in admin plugin page footer.
	 *
	 * @return string
	 */
	public function get_admin_footer( $text ) {

		if ( $this->is_admin_page() ) {
			$url = 'https://wordpress.org/support/plugin/easy-wp-smtp/reviews/?filter=5#new-post';

			$text = sprintf(
				wp_kses(
				/* translators: %1$s - WP.org link; %2$s - same WP.org link. */
					__( 'Please rate <strong>Easy WP SMTP</strong> <a href="%1$s" target="_blank" rel="noopener noreferrer">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%2$s" target="_blank" rel="noopener noreferrer">WordPress.org</a> to help us spread the word.', 'easy-wp-smtp' ),
					[
						'strong' => [],
						'a'      => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				$url,
				$url
			);
		}

		return $text;
	}

	/**
	 * Display content of the admin area page.
	 *
	 * @since 2.0.0
	 */
	public function display() { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded

		// Bail if we're not on a plugin page.
		if ( ! $this->is_admin_page() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = ! empty( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
		?>

		<div class="wrap" id="easy-wp-smtp">

				<?php
				switch ( $page ) {
					case self::SLUG:
						?>
						<div class="easy-wp-smtp-page easy-wp-smtp-page-general easy-wp-smtp-tab-<?php echo esc_attr( $this->get_current_tab() ); ?>">
							<?php $this->display_tabs(); ?>
						</div>
						<?php
						break;

					case self::SLUG . '-logs':
						/**
						 * The Email Logs object to be used for loading the Email Log page.
						 *
						 * @var \EasyWPSMTP\Admin\PageAbstract $logs
						 */
						$logs = $this->generate_display_logs_object();

						$is_archive = easy_wp_smtp()->is_pro() && easy_wp_smtp()->pro->get_logs()->is_archive();
						?>

						<div class="easy-wp-smtp-page easy-wp-smtp-page-logs <?php echo $is_archive ? 'easy-wp-smtp-page-logs-archive' : 'easy-wp-smtp-page-logs-single'; ?>">
							<?php $logs->display(); ?>
						</div>

						<?php
						break;

					default:
						foreach ( $this->get_parent_pages() as $parent_page ) {
							if ( $page === self::SLUG . '-' . $parent_page->get_slug() ) {
								?>
								<div class="easy-wp-smtp-page easy-wp-smtp-page-<?php echo esc_attr( $parent_page->get_slug() ); ?> easy-wp-smtp-tab-<?php echo esc_attr( $parent_page->get_slug() ); ?>-<?php echo esc_attr( $parent_page->get_current_tab() ); ?>">
									<?php $parent_page->display(); ?>
								</div>
								<?php
								break;
							}
						}
				}
				?>
		</div>

		<?php
	}

	/**
	 * Generate the appropriate Email Log page object used for displaying the Email Log page.
	 *
	 * @since 2.1.0
	 *
	 * @return \EasyWPSMTP\Admin\PageAbstract
	 */
	public function generate_display_logs_object() {

		// Store generated object to make sure that it's created only once.
		static $logs_object = null;

		$logs_class = apply_filters( 'easy_wp_smtp_admin_display_get_logs_fqcn', \EasyWPSMTP\Admin\Pages\Logs::class );

		if ( $logs_object === null ) {
			$logs_object = new $logs_class();
		}

		return $logs_object;
	}

	/**
	 * Get email logs access capability.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_logs_access_capability() {

		/**
		 * Filter email logs access capability.
		 *
		 * @since 2.1.0
		 *
		 * @param string $capability Email logs access capability.
		 */
		return apply_filters( 'easy_wp_smtp_admin_area_get_logs_access_capability', 'manage_options' );
	}

	/**
	 * Display General page tabs.
	 *
	 * @since 2.0.0
	 */
	protected function display_tabs() {

		?>
		<div class="easy-wp-smtp-container">
			<div class="easy-wp-smtp-nav-menu">
				<div class="easy-wp-smtp-nav-menu__inner">
					<?php
					foreach ( $this->get_pages() as $page_slug => $page ) :
						$label = $page->get_label();
						if ( empty( $label ) ) {
							continue;
						}
						$class = $page_slug === $this->get_current_tab() ? 'easy-wp-smtp-nav-menu__item--active' : '';
						?>

						<a href="<?php echo esc_url( $page->get_link() ); ?>"
							 class="easy-wp-smtp-nav-menu__item <?php echo esc_attr( $class ); ?>">
							<?php echo esc_html( $label ); ?>
						</a>

					<?php endforeach; ?>
				</div>
			</div>

			<div class="easy-wp-smtp-page-content">
				<h1 class="screen-reader-text">
					<?php echo esc_html( $this->get_current_tab_title() ); ?>
				</h1>

				<?php do_action( 'easy_wp_smtp_admin_pages_before_content' ); ?>

				<?php $this->display_current_tab_content(); ?>
			</div>
		</div>

		<?php
	}

	/**
	 * Get the current tab content.
	 *
	 * @since 2.0.0
	 */
	public function display_current_tab_content() {

		$pages = $this->get_pages();

		if ( ! array_key_exists( $this->get_current_tab(), $pages ) ) {
			return;
		}

		$pages[ $this->get_current_tab() ]->display();
	}

	/**
	 * Get the current admin area tab.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_current_tab() {

		$current = '';

		if ( $this->is_admin_page( 'general' ) ) {
			$current = ! empty( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'settings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		return $current;
	}

	/**
	 * Get admin parent pages.
	 *
	 * @since 2.0.0
	 *
	 * @return ParentPageAbstract[]
	 */
	public function get_parent_pages() {

		static $pages = null;

		if ( $pages === null ) {
			$pages = [
				'reports' => new Pages\EmailReports(
					[
						'reports' => Pages\EmailReportsTab::class,
					]
				),
				'tools'   => new Pages\Tools(
					[
						'test'             => Pages\TestTab::class,
						'export'           => Pages\ExportTab::class,
						'action-scheduler' => Pages\ActionSchedulerTab::class,
						'debug-events'     => Pages\DebugEventsTab::class,
					]
				),
			];
		}

		/**
		 * Filters admin parent pages.
		 *
		 * @since 2.0.0
		 *
		 * @param ParentPageAbstract[] $pages Parent pages.
		 */
		return apply_filters( 'easy_wp_smtp_admin_area_get_parent_pages', $pages );
	}

	/**
	 * Get the array of default registered tabs for General page admin area.
	 *
	 * @since 2.0.0
	 *
	 * @return PageAbstract[]
	 */
	public function get_pages() {

		if ( empty( $this->pages ) ) {
			$this->pages = [
				'settings'    => new Pages\SettingsTab(),
				'logs'        => new Pages\LogsTab(),
				'misc'        => new Pages\MiscTab(),
				'auth'        => new Pages\AuthTab(),
			];
		}

		return apply_filters( 'easy_wp_smtp_admin_get_pages', $this->pages );
	}

	/**
	 * Get the current tab title.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_current_tab_title() {

		$pages = $this->get_pages();

		if ( ! array_key_exists( $this->get_current_tab(), $pages ) ) {
			return '';
		}

		return $pages[ $this->get_current_tab() ]->get_title();
	}

	/**
	 * Check whether we are on an admin page.
	 *
	 * @since 2.0.0
	 *
	 * @param array|string $slug ID(s) of a plugin page. Possible values: 'general', 'logs', 'about' or array of them.
	 *
	 * @return bool
	 */
	public function is_admin_page( $slug = array() ) { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$cur_page    = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
		$check       = self::SLUG;
		$pages_equal = false;

		if ( is_string( $slug ) ) {
			$slug = sanitize_key( $slug );

			if (
				in_array( $slug, self::$pages_registered, true ) &&
				$slug !== 'general'
			) {
				$check = self::SLUG . '-' . $slug;
			}

			$pages_equal = $cur_page === $check;
		} elseif ( is_array( $slug ) ) {
			if ( empty( $slug ) ) {
				$slug = array_map(
					function ( $v ) {
						if ( $v === 'general' ) {
							return Area::SLUG;
						}
						return Area::SLUG . '-' . $v;
					},
					self::$pages_registered
				);
			} else {
				$slug = array_map(
					function ( $v ) {
						if ( $v === 'general' ) {
							return Area::SLUG;
						}
						return Area::SLUG . '-' . sanitize_key( $v );
					},
					$slug
				);
			}

			$pages_equal = in_array( $cur_page, $slug, true );
		}

		return is_admin() && $pages_equal;
	}

	/**
	 * Give ability to use either admin area option or a filter to hide error notices about failed email delivery.
	 * Filter has higher priority and overrides an option.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_error_delivery_notice_enabled() {

		$is_hard_enabled = (bool) apply_filters( 'easy_wp_smtp_admin_is_error_delivery_notice_enabled', true );

		// If someone changed the value to false using a filter - disable completely.
		if ( ! $is_hard_enabled ) {
			return false;
		}

		return ! (bool) Options::init()->get( 'general', 'email_delivery_errors_hidden' );
	}

	/**
	 * All possible plugin forms manipulation will be done here.
	 *
	 * @since 2.0.0
	 */
	public function process_actions() {

		// Bail if we're not on a plugin General page.
		if ( ! $this->is_admin_page( 'general' ) ) {
			return;
		}

		$pages = $this->get_pages();

		// Allow to process only own tabs.
		if ( ! array_key_exists( $this->get_current_tab(), $pages ) ) {
			return;
		}

		// Process POST only if it exists.
		// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		if ( ! empty( $_POST ) && isset( $_POST['easy-wp-smtp-post'] ) ) {
			if ( ! empty( $_POST['easy-wp-smtp'] ) ) {
				$post = $_POST['easy-wp-smtp'];
			} else {
				$post = [];
			}

			/**
			 * Before process post.
			 *
			 * @since 2.0.0
			 *
			 * @param array  $post      POST data.
			 * @param string $page_slug Current page slug.
			 */
			do_action(
				'easy_wp_smtp_admin_area_process_actions_process_post_before',
				$post,
				$pages[ $this->get_current_tab() ]->get_slug()
			);

			$pages[ $this->get_current_tab() ]->process_post( $post );
		}
		// phpcs:enable

		// This won't do anything for most pages.
		// Works for plugin page only, when GET params are allowed.
		$pages[ $this->get_current_tab() ]->process_auth();
	}

	/**
	 * Process all AJAX requests.
	 *
	 * @since 2.0.0
	 */
	public function process_ajax() {

		$data = [];

		// Only admins can fire these ajax requests.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( $data );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['task'] ) ) {
			wp_send_json_error( $data );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$task = sanitize_key( $_POST['task'] );

		switch ( $task ) {
			case 'pro_banner_dismiss':
				if ( ! check_ajax_referer( 'easy-wp-smtp-admin', 'nonce', false ) ) {
					break;
				}

				update_user_meta( get_current_user_id(), 'easy_wp_smtp_pro_banner_dismissed', true );
				$data['message'] = esc_html__( 'Easy WP SMTP Pro related message was successfully dismissed.', 'easy-wp-smtp' );
				break;

			case 'notice_dismiss':
				$dismissal_response = $this->dismiss_notice_via_ajax();

				if ( empty( $dismissal_response ) ) {
					break;
				}

				$data['message'] = $dismissal_response;
				break;

			default:
				// Allow custom tasks data processing being added here.
				$data = apply_filters( 'easy_wp_smtp_admin_process_ajax_' . $task . '_data', $data );
		}

		// Final ability to rewrite all the data, just in case.
		$data = (array) apply_filters( 'easy_wp_smtp_admin_process_ajax_data', $data, $task );

		if ( empty( $data ) ) {
			wp_send_json_error( $data );
		}

		wp_send_json_success( $data );
	}

	/**
	 * Process the notice dismissal via AJAX call (Post request).
	 *
	 * @since 2.0.0
	 *
	 * @return false|string
	 */
	private function dismiss_notice_via_ajax() {

		if ( ! check_ajax_referer( 'easy-wp-smtp-admin', 'nonce', false ) ) {
			return false;
		}

		if ( empty( $_POST['notice'] ) || empty( $_POST['mailer'] ) ) {
			return false;
		}

		$notice = sanitize_key( $_POST['notice'] );
		$mailer = sanitize_key( $_POST['mailer'] );

		update_user_meta( get_current_user_id(), "easy_wp_smtp_notice_{$notice}_for_{$mailer}_dismissed", true );

		return esc_html__( 'Educational notice for this mailer was successfully dismissed.', 'easy-wp-smtp' );
	}

	/**
	 * Add plugin action links on Plugins page (lite version only).
	 *
	 * @since 2.0.0
	 *
	 * @param array $links Existing plugin action links.
	 *
	 * @return array
	 */
	public function add_plugin_action_link( $links ) {

		// Do not register lite plugin action links if on pro version.
		if ( easy_wp_smtp()->is_pro() ) {
			return $links;
		}

		$custom['easy-wp-smtp-pro'] = sprintf(
			'<a href="%1$s" aria-label="%2$s" target="_blank" rel="noopener noreferrer" 
				style="color: #00a32a; font-weight: 700;" 
				onmouseover="this.style.color=\'#008a20\';" 
				onmouseout="this.style.color=\'#00a32a\';"
				>%3$s</a>',
			// phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			esc_url( easy_wp_smtp()->get_upgrade_link( [ 'medium' => 'all-plugins', 'content' => 'Get Easy WP SMTP Pro' ] ) ),
			esc_attr__( 'Upgrade to Easy WP SMTP Pro', 'easy-wp-smtp' ),
			esc_html__( 'Get Easy WP SMTP Pro', 'easy-wp-smtp' )
		);

		$custom['easy-wp-smtp-settings'] = sprintf(
			'<a href="%s" aria-label="%s">%s</a>',
			esc_url( $this->get_admin_page_url() ),
			esc_attr__( 'Go to Easy WP SMTP Settings page', 'easy-wp-smtp' ),
			esc_html__( 'Settings', 'easy-wp-smtp' )
		);

		$custom['easy-wp-smtp-docs'] = sprintf(
			'<a href="%1$s" target="_blank" aria-label="%2$s" rel="noopener noreferrer">%3$s</a>',
			// phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			esc_url( easy_wp_smtp()->get_utm_url( 'https://easywpsmtp.com/docs/', [ 'medium' => 'all-plugins', 'content' => 'Documentation' ] ) ),
			esc_attr__( 'Go to EasyWPSMTP.com documentation page', 'easy-wp-smtp' ),
			esc_html__( 'Docs', 'easy-wp-smtp' )
		);

		return array_merge( $custom, (array) $links );
	}

	/**
	 * Get plugin admin area page URL.
	 *
	 * @since 2.0.0
	 *
	 * @param string $page The page slug to add as the page query parameter.
	 *
	 * @return string
	 */
	public function get_admin_page_url( $page = '', $tab = '' ) {

		if ( empty( $page ) ) {
			$page = self::SLUG;
		}

		$args = [
			'page' => $page,
		];

		if ( ! empty( $tab ) ) {
			$args['tab'] = $tab;
		}

		return add_query_arg( $args, WP::admin_url( 'admin.php' )
		);
	}

	/**
	 * Remove all non-Easy WP SMTP plugin notices from our plugin pages.
	 *
	 * @since 2.0.0
	 */
	public function hide_unrelated_notices() {

		// Bail if we're not on our screen or page.
		if ( ! $this->is_admin_page() ) {
			return;
		}

		$this->remove_unrelated_actions( 'user_admin_notices' );
		$this->remove_unrelated_actions( 'admin_notices' );
		$this->remove_unrelated_actions( 'all_admin_notices' );
		$this->remove_unrelated_actions( 'network_admin_notices' );
	}

	/**
	 * Whether top level menu is hidden.
	 *
	 * @since 2.0.1
	 *
	 * @return bool
	 */
	public function is_top_level_menu_hidden() {

		// Apply changes after settings update.
		if ( isset( $_POST['easy-wp-smtp-post'] ) && isset( $_GET['tab'] ) && $_GET['tab'] === 'misc' ) {
			return ! empty( $_POST['easy-wp-smtp']['general']['top_level_menu_hidden'] );
		}

		return Options::init()->get( 'general', 'top_level_menu_hidden' );
	}

	/**
	 * Remove all non-Easy WP SMTP notices from the our plugin pages based on the provided action hook.
	 *
	 * @since 2.0.0
	 *
	 * @param string $action The name of the action.
	 */
	private function remove_unrelated_actions( $action ) {

		global $wp_filter;

		if ( empty( $wp_filter[ $action ]->callbacks ) || ! is_array( $wp_filter[ $action ]->callbacks ) ) {
			return;
		}

		foreach ( $wp_filter[ $action ]->callbacks as $priority => $hooks ) {
			foreach ( $hooks as $name => $arr ) {
				if (
					( // Cover object method callback case.
						is_array( $arr['function'] ) &&
						isset( $arr['function'][0] ) &&
						is_object( $arr['function'][0] ) &&
						strpos( strtolower( get_class( $arr['function'][0] ) ), 'easywpsmtp' ) !== false
					) ||
					( // Cover class static method callback case.
						! empty( $name ) &&
						strpos( strtolower( $name ), 'easywpsmtp' ) !== false
					)
				) {
					continue;
				}

				unset( $wp_filter[ $action ]->callbacks[ $priority ][ $name ] );
			}
		}
	}

	/**
	 * Get admin page hook.
	 *
	 * @since 2.1.0
	 *
	 * @param string $tab Tab slug.
	 *
	 * @return string
	 */
	public function get_admin_page_hook( $tab = '' ) {

		if ( $this->is_top_level_menu_hidden() ) {
			$hook = 'settings_page_' . self::SLUG;
		} elseif ( ! empty( $tab ) ) {
			$hook = self::SLUG . '_page_' . self::SLUG;
		} else {
			$hook = 'toplevel_page_' . self::SLUG;
		}

		if ( ! empty( $tab ) ) {
			$hook .= '-' . $tab;
		}

		return $hook;
	}
}
