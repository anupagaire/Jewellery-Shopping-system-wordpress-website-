<?php

namespace EasyWPSMTP\Admin\Pages;

use EasyWPSMTP\Admin\Area;
use EasyWPSMTP\Admin\PageAbstract;
use EasyWPSMTP\Options;
use EasyWPSMTP\UsageTracking\UsageTracking;
use EasyWPSMTP\Reports\Emails\Summary as SummaryReportEmail;
use EasyWPSMTP\Tasks\Reports\SummaryEmailTask as SummaryReportEmailTask;
use EasyWPSMTP\WP;

/**
 * Class MiscTab is part of Area, displays different plugin-related settings of the plugin (not related to emails).
 *
 * @since 2.0.0
 */
class MiscTab extends PageAbstract {

	/**
	 * Slug of a tab.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $slug = 'misc';

	/**
	 * Link label of a tab.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Misc', 'easy-wp-smtp' );
	}

	/**
	 * Title of a tab.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_title() {

		return esc_html__( 'Miscellaneous', 'easy-wp-smtp' );
	}

	/**
	 * Output HTML of the misc settings.
	 *
	 * @since 2.0.0
	 */
	public function display() {

		$options = Options::init();

		/**
		 * Filters whether to show Debug Log settings.
		 *
		 * @since 2.0.0
		 *
		 * @param bool $show_debug_log_setting Whether to show Debug Log settings.
		 */
		$show_debug_log_settings = apply_filters( 'easy_wp_smtp_admin_pages_misc_tab_show_debug_log_settings', false );
		?>

		<form method="POST" action="">
			<?php $this->wp_nonce_field(); ?>

			<div class="easy-wp-smtp-meta-box">
				<div class="easy-wp-smtp-meta-box__header">
					<div class="easy-wp-smtp-meta-box__heading">
						<?php echo esc_html( $this->get_title() ); ?>
					</div>
				</div>
				<div class="easy-wp-smtp-meta-box__content">

					<!-- Domain check -->
					<div class="easy-wp-smtp-row easy-wp-smtp-setting-row">
						<div class="easy-wp-smtp-setting-row__label">
							<label for="easy-wp-smtp-setting-do_not_send">
								<?php esc_html_e( 'Enable Domain Check', 'easy-wp-smtp' ); ?>
							</label>
						</div>
						<div class="easy-wp-smtp-setting-row__field">
							<div class="easy-wp-smtp-setting-row__sub-row">
								<label class="easy-wp-smtp-toggle" for="easy-wp-smtp-setting-domain_check">
									<input name="easy-wp-smtp[general][domain_check]" type="checkbox" value="true"
												 id="easy-wp-smtp-setting-domain_check"
												 <?php echo $options->is_const_defined( 'general', 'domain_check' ) ? 'disabled' : ''; ?>
												 <?php checked( true, $options->get( 'general', 'domain_check' ) ); ?>
									/>
									<span class="easy-wp-smtp-toggle__switch"></span>
									<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--checked"><?php esc_html_e( 'On', 'easy-wp-smtp' ); ?></span>
									<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--unchecked"><?php esc_html_e( 'Off', 'easy-wp-smtp' ); ?></span>
								</label>
								<p class="desc">
									<?php esc_html_e( 'Easy WP SMTP settings will be used only if the site is running on following domain(s):', 'easy-wp-smtp' ); ?>
								</p>
							</div>

							<div class="easy-wp-smtp-setting-row__sub-row">
								<input name="easy-wp-smtp[general][domain_check_allowed_domains]" type="text"
											 value="<?php echo esc_attr( $options->get( 'general', 'domain_check_allowed_domains' ) ); ?>"
											 <?php echo $options->is_const_defined( 'general', 'domain_check_allowed_domains' ) || ! $options->get( 'general', 'domain_check' ) ? 'disabled' : ''; ?>
											 id="easy-wp-smtp-setting-domain_check_allowed_domains" spellcheck="false"
								/>
								<p class="desc">
									<?php esc_html_e( 'Comma separated domains list. (Example: domain1.com, domain2.com)', 'easy-wp-smtp' ); ?>
								</p>
							</div>

							<div class="easy-wp-smtp-setting-row__sub-row">
								<label class="easy-wp-smtp-toggle" for="easy-wp-smtp-setting-domain_check_do_not_send">
									<input name="easy-wp-smtp[general][domain_check_do_not_send]" type="checkbox" value="true"
												 id="easy-wp-smtp-setting-domain_check_do_not_send"
												 <?php echo $options->is_const_defined( 'general', 'domain_check_do_not_send' ) || ! $options->get( 'general', 'domain_check' ) ? 'disabled' : ''; ?>
												 <?php checked( true, $options->get( 'general', 'domain_check_do_not_send' ) ); ?>
									/>
									<span class="easy-wp-smtp-toggle__switch"></span>
									<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--static"><?php esc_html_e( 'Block all emails', 'easy-wp-smtp' ); ?></span>
								</label>
								<p class="desc">
									<?php esc_html_e( 'When enabled, the plugin will attempt to block ALL emails from being sent out if a domain mismatch occurs.', 'easy-wp-smtp' ); ?>
								</p>
							</div>
						</div>
					</div>

					<!-- Do not send -->
					<div id="easy-wp-smtp-setting-row-do_not_send" class="easy-wp-smtp-row easy-wp-smtp-setting-row">
						<div class="easy-wp-smtp-setting-row__label">
							<label for="easy-wp-smtp-setting-do_not_send">
								<?php esc_html_e( 'Do Not Send', 'easy-wp-smtp' ); ?>
							</label>
						</div>
						<div class="easy-wp-smtp-setting-row__field">
							<label class="easy-wp-smtp-toggle" for="easy-wp-smtp-setting-do_not_send">
								<input name="easy-wp-smtp[general][do_not_send]" type="checkbox" value="true" id="easy-wp-smtp-setting-do_not_send"
											 <?php echo $options->is_const_defined( 'general', 'do_not_send' ) ? 'disabled' : ''; ?>
											 <?php checked( true, $options->get( 'general', 'do_not_send' ) ); ?>
								/>
								<span class="easy-wp-smtp-toggle__switch"></span>
								<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--static"><?php esc_html_e( 'Stop sending all emails', 'easy-wp-smtp' ); ?></span>
							</label>

							<p class="desc">
								<?php
								esc_html_e( 'Enable to stop your site from sending emails. Test emails are allowed to be sent, regardless of whether this option is enabled.', 'easy-wp-smtp' );
								?>
							</p>
							<p class="desc">
								<?php
								esc_html_e( 'Some plugins, like BuddyPress and Events Manager, use their own email delivery solutions. By default, this option does not block their emails, as those plugins do not use the default wp_mail() function to send emails. You will need to consult the documentation of any such plugins to switch them to use default WordPress email delivery for this setting to have an effect. ', 'easy-wp-smtp' );
								?>
							</p>
						</div>
					</div>

					<!-- Allow Insecure SSL Certificates -->
					<div id="easy-wp-smtp-setting-row-allow_smtp_insecure_ssl" class="easy-wp-smtp-row easy-wp-smtp-setting-row">
						<div class="easy-wp-smtp-setting-row__label">
							<label for="easy-wp-smtp-setting-allow_smtp_insecure_ssl">
								<?php esc_html_e( 'Allow Insecure SSL Certificates', 'easy-wp-smtp' ); ?>
							</label>
						</div>
						<div class="easy-wp-smtp-setting-row__field">
							<label class="easy-wp-smtp-toggle" for="easy-wp-smtp-setting-allow_smtp_insecure_ssl">
								<input name="easy-wp-smtp[general][allow_smtp_insecure_ssl]" type="checkbox" value="true" id="easy-wp-smtp-setting-allow_smtp_insecure_ssl"
											 <?php echo $options->is_const_defined( 'general', 'allow_smtp_insecure_ssl' ) ? 'disabled' : ''; ?>
											 <?php checked( true, $options->get( 'general', 'allow_smtp_insecure_ssl' ) ); ?>
								/>
								<span class="easy-wp-smtp-toggle__switch"></span>
								<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--checked"><?php esc_html_e( 'On', 'easy-wp-smtp' ); ?></span>
								<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--unchecked"><?php esc_html_e( 'Off', 'easy-wp-smtp' ); ?></span>
							</label>

							<p class="desc">
								<?php
								esc_html_e( 'Allow insecure and self-signed SSL certificates on SMTP server. It\'s highly recommended to keep this option disabled.', 'easy-wp-smtp' );
								?>
							</p>
						</div>
					</div>

					<?php if ( ! empty( $options->get( 'deprecated', 'debug_log_enabled' ) ) || $show_debug_log_settings ) : ?>
						<!-- Debug Log -->
						<div id="easy-wp-smtp-setting-row-debug_log_enabled" class="easy-wp-smtp-row easy-wp-smtp-setting-row">
							<div class="easy-wp-smtp-setting-row__label">
								<label for="easy-wp-smtp-setting-debug_log_enabled">
									<?php esc_html_e( 'Enable Debug Log', 'easy-wp-smtp' ); ?>
								</label>
							</div>
							<div class="easy-wp-smtp-setting-row__field">
								<label class="easy-wp-smtp-toggle" for="easy-wp-smtp-setting-debug_log_enabled">
									<input name="easy-wp-smtp[deprecated][debug_log_enabled]" type="checkbox"
												 value="true" <?php checked( true, $options->get( 'deprecated', 'debug_log_enabled' ) ); ?>
												 id="easy-wp-smtp-setting-debug_log_enabled"
									/>
									<span class="easy-wp-smtp-toggle__switch"></span>
									<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--checked"><?php esc_html_e( 'On', 'easy-wp-smtp' ); ?></span>
									<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--unchecked"><?php esc_html_e( 'Off', 'easy-wp-smtp' ); ?></span>
								</label>

								<p class="desc">
									<?php
									echo wp_kses(
										__( '<b>Note:</b> The debug log is reset when the plugin is activated, deactivated, or updated.', 'easy-wp-smtp' ),
										[
											'b' => [],
										]
									);
									?>
								</p>

								<p class="easy-wp-smtp-btn-group desc">
									<a href="<?php echo esc_url( add_query_arg( 'swpsmtp_action', 'view_log', easy_wp_smtp()->get_admin()->get_admin_page_url() ) ); ?>" target="_blank" class="easy-wp-smtp-btn easy-wp-smtp-btn--secondary easy-wp-smtp-btn--sm"><?php esc_html_e( 'View Log', 'easy-wp-smtp' ); ?></a>
									<a id="easy-wp-smtp-clean-debug-log" href="#0" class="easy-wp-smtp-btn easy-wp-smtp-btn--tertiary easy-wp-smtp-btn--sm"><?php esc_html_e( 'Clear Log', 'easy-wp-smtp' ); ?></a>
								</p>
							</div>
						</div>
					<?php endif; ?>

					<!-- Hide Announcements -->
					<div id="easy-wp-smtp-setting-row-am_notifications_hidden" class="easy-wp-smtp-row easy-wp-smtp-setting-row">
						<div class="easy-wp-smtp-setting-row__label">
							<label for="easy-wp-smtp-setting-am_notifications_hidden">
								<?php esc_html_e( 'Announcements', 'easy-wp-smtp' ); ?>
							</label>
						</div>
						<div class="easy-wp-smtp-setting-row__field">
							<label class="easy-wp-smtp-toggle" for="easy-wp-smtp-setting-am_notifications_hidden">
								<input name="easy-wp-smtp[general][am_notifications_hidden]" type="checkbox"
											 value="true" <?php checked( true, ! $options->get( 'general', 'am_notifications_hidden' ) ); ?>
											 id="easy-wp-smtp-setting-am_notifications_hidden"
								/>
								<span class="easy-wp-smtp-toggle__switch"></span>
								<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--checked"><?php esc_html_e( 'On', 'easy-wp-smtp' ); ?></span>
								<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--unchecked"><?php esc_html_e( 'Off', 'easy-wp-smtp' ); ?></span>
							</label>

							<div class="desc">
								<?php esc_html_e( 'Show plugin announcements and update details in the WordPress dashboard.', 'easy-wp-smtp' ); ?>
							</div>
						</div>
					</div>

					<!-- Hide Email Delivery Errors -->
					<div id="easy-wp-smtp-setting-row-email_delivery_errors_hidden" class="easy-wp-smtp-row easy-wp-smtp-setting-row">
						<div class="easy-wp-smtp-setting-row__label">
							<label for="easy-wp-smtp-setting-email_delivery_errors_hidden">
								<?php esc_html_e( 'Email Delivery Errors', 'easy-wp-smtp' ); ?>
							</label>
						</div>
						<div class="easy-wp-smtp-setting-row__field">
							<?php
							$is_hard_disabled = has_filter( 'easy_wp_smtp_admin_is_error_delivery_notice_enabled' ) && ! easy_wp_smtp()->get_admin()->is_error_delivery_notice_enabled();
							?>

							<label class="easy-wp-smtp-toggle" for="easy-wp-smtp-setting-email_delivery_errors_hidden">
								<?php if ( $is_hard_disabled ) : ?>
									<input type="checkbox" disabled id="easy-wp-smtp-setting-email_delivery_errors_hidden">
								<?php else : ?>
									<input name="easy-wp-smtp[general][email_delivery_errors_hidden]" type="checkbox" value="true"
												 <?php checked( true, ! $options->get( 'general', 'email_delivery_errors_hidden' ) ); ?>
												 id="easy-wp-smtp-setting-email_delivery_errors_hidden"
									/>
								<?php endif; ?>
								<span class="easy-wp-smtp-toggle__switch"></span>
								<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--checked"><?php esc_html_e( 'On', 'easy-wp-smtp' ); ?></span>
								<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--unchecked"><?php esc_html_e( 'Off', 'easy-wp-smtp' ); ?></span>
							</label>

							<p class="desc">
								<?php esc_html_e( 'Show email delivery errors, warnings, and alerts in the WordPress dashboard.', 'easy-wp-smtp' ); ?>
							</p>

							<?php if ( $is_hard_disabled ) : ?>
								<p class="desc">
									<?php
									printf( /* translators: %s - filter that was used to disabled. */
										esc_html__( 'Email Delivery Errors were disabled using a %s filter.', 'easy-wp-smtp' ),
										'<code>easy_wp_smtp_admin_is_error_delivery_notice_enabled</code>'
									);
									?>
								</p>
							<?php else : ?>
								<p class="desc">
									<?php esc_html_e( 'Disabling this setting is not recommended and should only be done for staging or development sites.', 'easy-wp-smtp' ); ?>
								</p>
							<?php endif; ?>
						</div>
					</div>

					<!-- Hide Top Level Menu -->
					<div id="easy-wp-smtp-setting-row-top_level_menu_hidden" class="easy-wp-smtp-row easy-wp-smtp-setting-row">
						<div class="easy-wp-smtp-setting-row__label">
							<label for="easy-wp-smtp-setting-top_level_menu_hidden">
								<?php esc_html_e( 'Compact Mode', 'easy-wp-smtp' ); ?>
							</label>
						</div>
						<div class="easy-wp-smtp-setting-row__field">
							<label class="easy-wp-smtp-toggle" for="easy-wp-smtp-setting-top_level_menu_hidden">
								<input name="easy-wp-smtp[general][top_level_menu_hidden]" type="checkbox"
											 value="true" <?php checked( true, $options->get( 'general', 'top_level_menu_hidden' ) ); ?>
											 id="easy-wp-smtp-setting-top_level_menu_hidden"
								/>
								<span class="easy-wp-smtp-toggle__switch"></span>
								<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--checked"><?php esc_html_e( 'On', 'easy-wp-smtp' ); ?></span>
								<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--unchecked"><?php esc_html_e( 'Off', 'easy-wp-smtp' ); ?></span>
							</label>

							<div class="desc">
								<?php esc_html_e( 'Enabling this will condense navigation and move Easy WP SMTP under the WordPress Settings menu.', 'easy-wp-smtp' ); ?>
							</div>
						</div>
					</div>

					<?php if ( apply_filters( 'easy_wp_smtp_admin_pages_misc_tab_show_usage_tracking_setting', true ) ) : ?>
						<!-- Usage Tracking -->
						<div id="easy-wp-smtp-setting-row-usage-tracking" class="easy-wp-smtp-row easy-wp-smtp-setting-row">
							<div class="easy-wp-smtp-setting-row__label">
								<label for="easy-wp-smtp-setting-usage-tracking">
									<?php esc_html_e( 'Allow Usage Tracking', 'easy-wp-smtp' ); ?>
								</label>
							</div>
							<div class="easy-wp-smtp-setting-row__field">
								<label class="easy-wp-smtp-toggle" for="easy-wp-smtp-setting-usage-tracking">
									<input name="easy-wp-smtp[general][<?php echo esc_attr( UsageTracking::SETTINGS_SLUG ); ?>]"
												 type="checkbox" value="true" id="easy-wp-smtp-setting-usage-tracking"
												 <?php checked( true, $options->get( 'general', UsageTracking::SETTINGS_SLUG ) ); ?>
									/>
									<span class="easy-wp-smtp-toggle__switch"></span>
									<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--checked"><?php esc_html_e( 'On', 'easy-wp-smtp' ); ?></span>
									<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--unchecked"><?php esc_html_e( 'Off', 'easy-wp-smtp' ); ?></span>
								</label>

								<p class="desc">
									<?php esc_html_e( 'By allowing us to track usage data we can better help you because we know with which WordPress configurations, themes and plugins we should test.', 'easy-wp-smtp' ); ?>
								</p>
							</div>
						</div>
					<?php endif; ?>

					<!-- Hide Dashboard Widget -->
					<div id="easy-wp-smtp-setting-row-dashboard_widget_hidden" class="easy-wp-smtp-row easy-wp-smtp-setting-row">
						<div class="easy-wp-smtp-setting-row__label">
							<label for="easy-wp-smtp-setting-dashboard_widget_hidden">
								<?php esc_html_e( 'Hide Dashboard Widget', 'easy-wp-smtp' ); ?>
							</label>
						</div>
						<div class="easy-wp-smtp-setting-row__field">
							<label class="easy-wp-smtp-toggle" for="easy-wp-smtp-setting-dashboard_widget_hidden">
								<input name="easy-wp-smtp[general][dashboard_widget_hidden]" type="checkbox"
											 value="true" <?php checked( true, $options->get( 'general', 'dashboard_widget_hidden' ) ); ?>
											 id="easy-wp-smtp-setting-dashboard_widget_hidden"
								/>
								<span class="easy-wp-smtp-toggle__switch"></span>
								<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--checked"><?php esc_html_e( 'On', 'easy-wp-smtp' ); ?></span>
								<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--unchecked"><?php esc_html_e( 'Off', 'easy-wp-smtp' ); ?></span>
							</label>

							<p class="desc">
								<?php esc_html_e( 'Hide the Easy WP SMTP Dashboard Widget.', 'easy-wp-smtp' ); ?>
							</p>
						</div>
					</div>

					<!-- Summary Report Email -->
					<div id="easy-wp-smtp-setting-row-summary-report-email" class="easy-wp-smtp-row easy-wp-smtp-setting-row">
						<div class="easy-wp-smtp-setting-row__label">
							<label for="easy-wp-smtp-setting-summary-report-email">
								<?php esc_html_e( 'Disable Email Summaries', 'easy-wp-smtp' ); ?>
							</label>
						</div>
						<div class="easy-wp-smtp-setting-row__field">
							<label class="easy-wp-smtp-toggle" for="easy-wp-smtp-setting-summary-report-email">
								<input name="easy-wp-smtp[general][<?php echo esc_attr( SummaryReportEmail::SETTINGS_SLUG ); ?>]"
											 type="checkbox" id="easy-wp-smtp-setting-summary-report-email" value="true"
											 <?php checked( true, SummaryReportEmail::is_disabled() ); ?>
											 <?php disabled( $options->is_const_defined( 'general', SummaryReportEmail::SETTINGS_SLUG ) || ( easy_wp_smtp()->is_pro() && empty( Options::init()->get( 'logs', 'enabled' ) ) ) ); ?>
								/>
								<span class="easy-wp-smtp-toggle__switch"></span>
								<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--checked"><?php esc_html_e( 'On', 'easy-wp-smtp' ); ?></span>
								<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--unchecked"><?php esc_html_e( 'Off', 'easy-wp-smtp' ); ?></span>
							</label>

							<p class="desc">
								<?php esc_html_e( 'Disable Email Summaries weekly delivery.', 'easy-wp-smtp' ); ?>
							</p>
							<p class="desc">
								<?php
								if ( easy_wp_smtp()->is_pro() && empty( Options::init()->get( 'logs', 'enabled' ) ) ) {
									echo wp_kses(
										sprintf( /* translators: %s - Email Log settings url. */
											__( 'Please enable <a href="%s">Email Logging</a> first, before this setting can be configured.', 'easy-wp-smtp' ),
											esc_url( easy_wp_smtp()->get_admin()->get_admin_page_url( Area::SLUG . '&tab=logs' ) )
										),
										[
											'a' => [
												'href' => [],
											],
										]
									);
								} else {
									printf(
										'<a href="%1$s" target="_blank">%2$s</a>',
										esc_url( SummaryReportEmail::get_preview_link() ),
										esc_html__( 'View Email Summary Example', 'easy-wp-smtp' )
									);
								}

								if ( $options->is_const_defined( 'general', SummaryReportEmail::SETTINGS_SLUG ) ) {
									echo '<br>' . $options->get_const_set_message( 'EasyWPSMTP_SUMMARY_REPORT_EMAIL_DISABLED' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
								?>
							</p>
						</div>
					</div>

					<!-- Uninstall -->
					<div id="easy-wp-smtp-setting-row-uninstall" class="easy-wp-smtp-row easy-wp-smtp-setting-row">
						<div class="easy-wp-smtp-setting-row__label">
							<label for="easy-wp-smtp-setting-uninstall">
								<?php esc_html_e( 'Uninstall Easy WP SMTP', 'easy-wp-smtp' ); ?>
							</label>
						</div>
						<div class="easy-wp-smtp-setting-row__field">
							<label class="easy-wp-smtp-toggle" for="easy-wp-smtp-setting-uninstall">
								<input name="easy-wp-smtp[general][uninstall]" type="checkbox"
											 value="true" <?php checked( true, $options->get( 'general', 'uninstall' ) ); ?>
											 id="easy-wp-smtp-setting-uninstall"
								/>
								<span class="easy-wp-smtp-toggle__switch"></span>
								<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--checked"><?php esc_html_e( 'On', 'easy-wp-smtp' ); ?></span>
								<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--unchecked"><?php esc_html_e( 'Off', 'easy-wp-smtp' ); ?></span>
							</label>

							<p class="desc">
								<?php esc_html_e( 'Enabling this will REMOVE ALL Easy WP SMTP data upon plugin deletion. All settings will be unrecoverable.', 'easy-wp-smtp' ); ?>
							</p>
						</div>
					</div>
				</div>
			</div>

			<?php $this->display_save_btn(); ?>

		</form>

		<?php
	}

	/**
	 * Process tab form submission ($_POST).
	 *
	 * @since 2.0.0
	 *
	 * @param array $data Tab data specific for the plugin ($_POST).
	 */
	public function process_post( $data ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$this->check_admin_referer();

		$options = Options::init();

		$bool_options = [
			'domain_check',
			'domain_check_do_not_send',
			'do_not_send',
			'allow_smtp_insecure_ssl',
			'top_level_menu_hidden',
			'uninstall',
			UsageTracking::SETTINGS_SLUG,
			SummaryReportEmail::SETTINGS_SLUG,
			'dashboard_widget_hidden',
		];

		// Unchecked checkboxes doesn't exist in $_POST, so we need to ensure we actually have them in data to save.
		foreach ( $bool_options as $option_key ) {
			if ( empty( $data['general'][ $option_key ] ) ) {
				$data['general'][ $option_key ] = false;
			}
		}

		$data['general']['am_notifications_hidden']      = empty( $data['general']['am_notifications_hidden'] );
		$data['general']['email_delivery_errors_hidden'] = empty( $data['general']['email_delivery_errors_hidden'] );

		if ( empty( $data['deprecated']['debug_log_enabled'] ) ) {
			$data['deprecated']['debug_log_enabled'] = false;
		}

		$is_summary_report_email_opt_changed = $options->is_option_changed(
			$options->parse_boolean( $data['general'][ SummaryReportEmail::SETTINGS_SLUG ] ),
			'general',
			SummaryReportEmail::SETTINGS_SLUG
		);

		// If this option was changed, cancel summary report email task.
		if ( $is_summary_report_email_opt_changed ) {
			( new SummaryReportEmailTask() )->cancel();
		}

		// All the sanitization is done there.
		$options->set( $data, false, false );

		WP::add_admin_notice(
			esc_html__( 'Settings were successfully saved.', 'easy-wp-smtp' ),
			WP::ADMIN_NOTICE_SUCCESS
		);
	}
}
