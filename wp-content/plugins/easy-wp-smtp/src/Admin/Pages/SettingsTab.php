<?php

namespace EasyWPSMTP\Admin\Pages;

use EasyWPSMTP\Admin\ConnectionSettings;
use EasyWPSMTP\Admin\PageAbstract;
use EasyWPSMTP\Admin\SetupWizard;
use EasyWPSMTP\Options;
use EasyWPSMTP\WP;

/**
 * Class SettingsTab is part of Area, displays general settings of the plugin.
 *
 * @since 2.0.0
 */
class SettingsTab extends PageAbstract {

	/**
	 * Settings constructor.
	 *
	 * @since 2.1.0
	 */
	public function __construct() {

		parent::__construct();

		$this->hooks();
	}

	/**
	 * Slug of a tab.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $slug = 'settings';

	/**
	 * Link label of a tab.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Settings', 'easy-wp-smtp' );
	}

	/**
	 * Title of a tab.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_title() {

		return $this->get_label();
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.1.0
	 */
	public function hooks() {

		add_action( 'easy_wp_smtp_admin_pages_settings_license_key', [ __CLASS__, 'display_license_key_field_content' ] );

		add_action( 'easy_wp_smtp_admin_area_enqueue_assets', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Enqueue required JS and CSS.
	 *
	 * @since 2.1.0
	 */
	public function enqueue_assets() {

		if ( ! easy_wp_smtp()->get_admin()->is_admin_page( 'general' ) ) {
			return;
		}

		if ( $this->is_display_pro_banner() ) {
			wp_enqueue_style(
				'easy-wp-smtp-admin-lity',
				easy_wp_smtp()->assets_url . '/css/vendor/lity.min.css',
				[],
				'2.4.1'
			);
			wp_enqueue_script(
				'easy-wp-smtp-admin-lity',
				easy_wp_smtp()->assets_url . '/js/vendor/lity.min.js',
				[],
				'2.4.1',
				false
			);
		}
	}

	/**
	 * Settings tab content.
	 *
	 * @since 2.0.0
	 */
	public function display() {
		?>

		<form method="POST" action="" autocomplete="off" class="easy-wp-smtp-connection-settings-form">
			<?php $this->wp_nonce_field(); ?>

			<?php ob_start(); ?>

			<div class="easy-wp-smtp-meta-box">
				<div class="easy-wp-smtp-meta-box__header">
					<div class="easy-wp-smtp-meta-box__heading">
						<?php esc_html_e( 'License', 'easy-wp-smtp' ); ?>
					</div>
				</div>
				<div class="easy-wp-smtp-meta-box__content">
					<?php do_action( 'easy_wp_smtp_admin_pages_settings_license_key', Options::init() ); ?>
				</div>
			</div>

			<?php
			$connection          = easy_wp_smtp()->get_connections_manager()->get_primary_connection();
			$connection_settings = new ConnectionSettings( $connection );

			// Display connection settings.
			$connection_settings->display();
			?>

			<?php
			$settings_content = apply_filters( 'easy_wp_smtp_admin_settings_tab_display', ob_get_clean() );
			echo $settings_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>

			<?php $this->display_save_btn(); ?>
		</form>

		<?php
		if ( $this->is_display_pro_banner() ) {
			$this->display_pro_banner();
		}
	}

	/**
	 * License key text for a Lite version of the plugin.
	 *
	 * @since 2.1.0
	 *
	 * @param Options $options
	 */
	public static function display_license_key_field_content( $options ) {
		?>

		<div class="easy-wp-smtp-row">
			<div class="easy-wp-smtp-row__desc">
				<?php esc_html_e( 'You\'re using Easy WP SMTP Lite - no license key required. Enjoy!', 'easy-wp-smtp' ); ?>
			</div>
		</div>

		<div class="easy-wp-smtp-row easy-wp-smtp-row--has-divider">
			<div class="easy-wp-smtp-license-upgrade-notice">
				<p>
					<b>
						<?php
						printf(
							wp_kses( /* translators: %s - EasyWPSMTP.com upgrade URL. */
								__( 'Unlock more features by <strong><a href="%s" target="_blank" rel="noopener noreferrer">upgrading to PRO</a></strong>.', 'easy-wp-smtp' ),
								array(
									'a'      => array(
										'href'   => array(),
										'class'  => array(),
										'target' => array(),
										'rel'    => array(),
									),
									'strong' => array(),
								)
							),
							esc_url( easy_wp_smtp()->get_upgrade_link( 'general-license-key' ) )
						);
						?>
					</b>
				</p>

				<p>
					<?php
					printf(
						wp_kses( /* Translators: %s - discount value 50% */
							__( 'As thanks for being an Easy WP SMTP Lite user, we’re offering you <span>%s off</span>, applied automatically at checkout.', 'easy-wp-smtp' ),
							array(
								'span' => array(),
							)
						),
						'50%'
					);
					?>
				</p>
			</div>
		</div>

		<!-- License Key -->
		<div class="easy-wp-smtp-row easy-wp-smtp-setting-row easy-wp-smtp-setting-row--text">
			<div class="easy-wp-smtp-setting-row__label">
				<label for="easy-wp-smtp-setting-license_key">
					<?php esc_html_e( 'License Key', 'easy-wp-smtp' ); ?>
				</label>
			</div>
			<div class="easy-wp-smtp-setting-row__field">
				<div class="easy-wp-smtp-input-btn-row">
					<input type="password" id="easy-wp-smtp-setting-upgrade-license-key" class="easy-wp-smtp-not-form-input" placeholder="<?php esc_attr_e( 'Paste license key here', 'easy-wp-smtp' ); ?>" value="" />
					<button type="button" class="easy-wp-smtp-btn easy-wp-smtp-btn--primary" id="easy-wp-smtp-setting-upgrade-license-button">
						<?php esc_attr_e( 'Connect', 'easy-wp-smtp' ); ?>
					</button>
				</div>

				<p class="desc">
					<?php esc_html_e( 'Already purchased? Simply enter your license key below to connect with Easy WP SMTP Pro!', 'easy-wp-smtp' ); ?>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Whether to display Easy WP SMTP Pro upgrade banner.
	 *
	 * @since 2.1.0
	 *
	 * @return bool
	 */
	private function is_display_pro_banner() {

		// Display only to site admins. Only site admins can install plugins.
		if ( ! is_super_admin() ) {
			return false;
		}

		// Do not display if Easy WP SMTP Pro already installed.
		if ( easy_wp_smtp()->is_pro() ) {
			return false;
		}

		$is_dismissed = get_user_meta( get_current_user_id(), 'easy_wp_smtp_pro_banner_dismissed', true );

		// Do not display if user dismissed.
		if ( (bool) $is_dismissed === true ) {
			return false;
		}

		return true;
	}

	/**
	 * Display Easy WP SMTP Pro upgrade banner.
	 *
	 * @since 2.1.0
	 */
	protected function display_pro_banner() {

		$assets_url = easy_wp_smtp()->assets_url . '/images/education/';

		$screenshots = [
			[
				'url'           => $assets_url . 'logs/screenshot-01.png',
				'url_thumbnail' => $assets_url . 'logs/thumbnail-01.png',
				'title'         => __( 'Email Logs', 'easy-wp-smtp' ),
			],
			[
				'url'           => $assets_url . 'reports/screenshot-01.png',
				'url_thumbnail' => $assets_url . 'reports/thumbnail-01.png',
				'title'         => __( 'Email Reports', 'easy-wp-smtp' ),
			],
			[
				'url'           => $assets_url . 'reports/screenshot-03.png',
				'url_thumbnail' => $assets_url . 'reports/thumbnail-03.png',
				'title'         => __( 'Weekly Email Report', 'easy-wp-smtp' ),
			],
		];

		$upgrade_link = easy_wp_smtp()->get_upgrade_link(
			[
				'medium'  => 'pro-banner',
				'content' => 'upgrade-today-link',
			]
		);

		$button_upgrade_link = easy_wp_smtp()->get_upgrade_link(
			[
				'medium'  => 'pro-banner',
				'content' => 'upgrade-to-easy-wp-smtp-pro-button-link',
			]
		);
		?>
		<div class="easy-wp-smtp-meta-box easy-wp-smtp-pro-banner">
			<div class="easy-wp-smtp-meta-box__content">
				<a href="#" title="<?php esc_attr_e( 'Dismiss', 'easy-wp-smtp' ); ?>"
					 class="easy-wp-smtp-pro-banner__dismiss js-easy-wp-smtp-pro-banner-dismiss">
					<svg fill="none" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
						<path d="m8 0.25c-4.2812 0-7.75 3.4688-7.75 7.75 0 4.2812 3.4688 7.75 7.75 7.75 4.2812 0 7.75-3.4688 7.75-7.75 0-4.2812-3.4688-7.75-7.75-7.75zm0 14c-3.4688 0-6.25-2.7812-6.25-6.25 0-3.4375 2.7812-6.25 6.25-6.25 3.4375 0 6.25 2.8125 6.25 6.25 0 3.4688-2.8125 6.25-6.25 6.25zm3.1562-8.1875c0.1563-0.125 0.1563-0.375 0-0.53125l-0.6874-0.6875c-0.1563-0.15625-0.4063-0.15625-0.5313 0l-1.9375 1.9375-1.9688-1.9375c-0.125-0.15625-0.375-0.15625-0.53125 0l-0.6875 0.6875c-0.15625 0.15625-0.15625 0.40625 0 0.53125l1.9375 1.9375-1.9375 1.9688c-0.15625 0.12505-0.15625 0.37505 0 0.53125l0.6875 0.6875c0.15625 0.1563 0.40625 0.1563 0.53125 0l1.9688-1.9375 1.9375 1.9375c0.125 0.1563 0.375 0.1563 0.5313 0l0.6874-0.6875c0.1563-0.1562 0.1563-0.4062 0-0.53125l-1.9374-1.9688 1.9374-1.9375z" fill="currentColor"/>
					</svg>
				</a>
				<div class="easy-wp-smtp-row">
					<div class="easy-wp-smtp-row__heading">
						<?php esc_html_e( 'Get Easy WP SMTP Pro and Gain Access to more Powerful Features', 'easy-wp-smtp' ); ?>
					</div>
					<div class="easy-wp-smtp-row__desc">
						<?php
						printf(
							wp_kses( /* translators: %s - sendlayer.com URL. */
								__( 'Learn the full potential of Easy WP SMTP with our Pro version. <a href="%s" target="_blank" rel="noopener noreferrer">Upgrade today</a> and start using advanced features to track and monitor email activity.', 'easy-wp-smtp' ),
								[
									'a' => [
										'href'   => [],
										'target' => [],
										'rel'    => [],
									],
								]
							),
							$upgrade_link
						);
						?>
					</div>
				</div>
				<div class="easy-wp-smtp-row easy-wp-smtp-row--has-divider">
					<div class="easy-wp-smtp-product-education-screenshots easy-wp-smtp-product-education-screenshots--three">
						<?php foreach ( $screenshots as $screenshot ) : ?>
							<div>
								<a href="<?php echo esc_url( $screenshot['url'] ); ?>" data-lity data-lity-desc="<?php echo esc_attr( $screenshot['title'] ); ?>">
									<img src="<?php echo esc_url( $screenshot['url_thumbnail'] ); ?>" alt="<?php esc_attr( $screenshot['title'] ); ?>">
								</a>
								<span><?php echo esc_html( $screenshot['title'] ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="easy-wp-smtp-row">
					<div class="easy-wp-smtp-row__heading easy-wp-smtp-settings-heading">
						<?php esc_html_e( 'Pro Features:', 'easy-wp-smtp' ); ?>
					</div>
					<div class="easy-wp-smtp-product-education-list-v2">
						<ul>
							<li>
								<strong><?php esc_html_e( 'Email Logs', 'easy-wp-smtp' ); ?></strong>
								<ul>
									<li><?php esc_html_e( 'Open and click tracking', 'easy-wp-smtp' ); ?></li>
									<li><?php esc_html_e( 'Status (was the email delivered, sent, pending, or failed)', 'easy-wp-smtp' ); ?></li>
									<li><?php esc_html_e( 'Email log export (.eml, .csv, .xlsl) and bulk exporter', 'easy-wp-smtp' ); ?></li>
									<li><?php esc_html_e( 'Source (which plugin/theme sent the email and it\'s path location)', 'easy-wp-smtp' ); ?></li>
								</ul>
							</li>
						</ul>
						<ul>
							<li><?php esc_html_e( 'Pro mailers: Amazon SES and Microsoft 365 / Outlook', 'easy-wp-smtp' ); ?></li>
							<li><?php esc_html_e( 'Advanced Email Reports', 'easy-wp-smtp' ); ?></li>
							<li><?php esc_html_e( 'Intuitive Dashboard Widget with email stats', 'easy-wp-smtp' ); ?></li>
							<li><?php esc_html_e( 'Weekly Email Summaries delivered to your inbox', 'easy-wp-smtp' ); ?></li>
						</ul>
					</div>
				</div>
				<div class="easy-wp-smtp-row">
					<a href="<?php echo esc_url( $button_upgrade_link ); ?>" target="_blank" rel="noopener noreferrer" class="easy-wp-smtp-btn easy-wp-smtp-btn--lg easy-wp-smtp-btn--green">
						<?php esc_html_e( 'Upgrade to Easy WP SMTP Pro', 'easy-wp-smtp' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Process tab form submission ($_POST).
	 *
	 * @since 2.0.0
	 *
	 * @param array $data Post data specific for the plugin.
	 */
	public function process_post( $data ) {

		$this->check_admin_referer();

		$connection          = easy_wp_smtp()->get_connections_manager()->get_primary_connection();
		$connection_settings = new ConnectionSettings( $connection );

		$old_data = $connection->get_options()->get_all();

		$data = $connection_settings->process( $data, $old_data );

		/**
		 * Filters mail settings before save.
		 *
		 * @since 2.0.0
		 *
		 * @param array $data Settings data.
		 */
		$data = apply_filters( 'easy_wp_smtp_settings_tab_process_post', $data );

		// All the sanitization is done in Options class.
		Options::init()->set( $data, false, false );

		$connection_settings->post_process( $data, $old_data );

		if ( $connection_settings->get_scroll_to() !== false ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			wp_safe_redirect( sanitize_text_field( wp_unslash( $_POST['_wp_http_referer'] ) ) . $connection_settings->get_scroll_to() );
			exit;
		}

		WP::add_admin_notice(
			esc_html__( 'Settings were successfully saved.', 'easy-wp-smtp' ),
			WP::ADMIN_NOTICE_SUCCESS
		);
	}
}
