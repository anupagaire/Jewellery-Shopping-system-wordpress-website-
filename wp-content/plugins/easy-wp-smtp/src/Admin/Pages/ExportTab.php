<?php

namespace EasyWPSMTP\Admin\Pages;

use EasyWPSMTP\Admin\PageAbstract;

/**
 * Class ExportTab is a placeholder for Pro email logs export.
 * Displays product education.
 *
 * @since 2.1.0
 */
class ExportTab extends PageAbstract {

	/**
	 * Part of the slug of a tab.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $slug = 'export';

	/**
	 * Tab priority.
	 *
	 * @since 2.1.0
	 *
	 * @var int
	 */
	protected $priority = 20;

	/**
	 * Link label of a tab.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Export', 'easy-wp-smtp' );
	}

	/**
	 * Title of a tab.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_title() {

		return $this->get_label();
	}

	/**
	 * Output HTML of the email logs export form preview.
	 *
	 * @since 2.1.0
	 */
	public function display() {

		$button_upgrade_link = easy_wp_smtp()->get_upgrade_link(
			[
				'medium'  => 'tools-export',
				'content' => 'upgrade-to-easy-wp-smtp-pro-button',
			]
		);

		?>

		<div class="easy-wp-smtp-meta-box">
			<div class="easy-wp-smtp-meta-box__header">
				<div class="easy-wp-smtp-meta-box__heading">
					<?php esc_html_e( 'Export Email Logs', 'easy-wp-smtp' ); ?>
				</div>

				<a href="<?php echo esc_url( $button_upgrade_link ); ?>" target="_blank" rel="noopener noreferrer" class="easy-wp-smtp-btn easy-wp-smtp-btn--sm easy-wp-smtp-btn--green">
					<?php esc_html_e( 'Upgrade to Pro', 'easy-wp-smtp' ); ?>
				</a>
			</div>
			<div class="easy-wp-smtp-meta-box__content">

				<div class="easy-wp-smtp-row easy-wp-smtp-row--has-divider">
					<div class="easy-wp-smtp-row__desc">
						<?php
						echo wp_kses(
							sprintf( /* translators: %s - EasyWPSMTP.com Upgrade page URL. */
								__( 'Easily export your logs to CSV or Excel. Filter the logs before you export and only download the data you need. This feature lets you easily create your own deliverability reports. You can also use the data in 3rd party dashboards to track deliverability along with your other website statistics. <a href="%s" target="_blank" rel="noopener noreferrer">Upgrade to Easy WP SMTP Pro!</a>', 'easy-wp-smtp' ),
								esc_url(
									easy_wp_smtp()->get_upgrade_link(
										[
											'medium'  => 'tools-export',
											'content' => 'upgrade-to-easy-wp-smtp-pro-text-link',
										]
									)
								)
							),
							[
								'a' => [
									'href'   => [],
									'rel'    => [],
									'target' => [],
								],
							]
						);
						?>
					</div>
				</div>

				<div class="easy-wp-smtp-row easy-wp-smtp-setting-row--inactive easy-wp-smtp-setting-row">
					<div class="easy-wp-smtp-setting-row__label">
						<?php esc_html_e( 'Export Type', 'easy-wp-smtp' ); ?>
					</div>
					<div class="easy-wp-smtp-setting-row__field">
						<div class="easy-wp-smtp-radio-group">
							<label class="easy-wp-smtp-radio">
								<input type="radio" checked>
								<span class="easy-wp-smtp-radio__checkmark"></span>
								<span class="easy-wp-smtp-radio__label">
									<?php esc_html_e( 'Export in CSV (.csv)', 'easy-wp-smtp' ); ?>
								</span>
							</label>
							<label class="easy-wp-smtp-radio">
								<span class="easy-wp-smtp-radio__checkmark"></span>
								<span class="easy-wp-smtp-radio__label">
									<?php esc_html_e( 'Export in Microsoft Excel (.xlsx)', 'easy-wp-smtp' ); ?>
								</span>
							</label>
							<label class="easy-wp-smtp-radio">
								<span class="easy-wp-smtp-radio__checkmark"></span>
								<span class="easy-wp-smtp-radio__label">
									<?php esc_html_e( 'Export in EML (.eml)', 'easy-wp-smtp' ); ?>
								</span>
							</label>
						</div>
					</div>
				</div>

				<div class="easy-wp-smtp-row easy-wp-smtp-setting-row--inactive easy-wp-smtp-setting-row easy-wp-smtp-setting-row--text">
					<div class="easy-wp-smtp-setting-row__label">
						<?php esc_html_e( 'Custom Date Range', 'easy-wp-smtp' ); ?>
					</div>
					<div class="easy-wp-smtp-setting-row__field">
						<input class="easy-wp-smtp-date-selector form-control input" placeholder="Select a date range" tabindex="0" type="text">
					</div>
				</div>

				<div class="easy-wp-smtp-row easy-wp-smtp-setting-row--inactive easy-wp-smtp-setting-row">
					<div class="easy-wp-smtp-setting-row__label">
						<?php esc_html_e( 'Search', 'easy-wp-smtp' ); ?>
					</div>
					<div class="easy-wp-smtp-setting-row__field">

						<div class="easy-wp-smtp-setting-row__sub-row easy-wp-smtp-radio-group">
							<label class="easy-wp-smtp-radio">
								<input type="radio" checked>
								<span class="easy-wp-smtp-radio__checkmark"></span>
								<span class="easy-wp-smtp-radio__label">
									<?php esc_html_e( 'Email Addresses', 'easy-wp-smtp' ); ?>
								</span>
							</label>
							<label class="easy-wp-smtp-radio">
								<span class="easy-wp-smtp-radio__checkmark"></span>
								<span class="easy-wp-smtp-radio__label">
									<?php esc_html_e( 'Subject & Headers', 'easy-wp-smtp' ); ?>
								</span>
							</label>
							<label class="easy-wp-smtp-radio">
								<span class="easy-wp-smtp-radio__checkmark"></span>
								<span class="easy-wp-smtp-radio__label">
									<?php esc_html_e( 'Content', 'easy-wp-smtp' ); ?>
								</span>
							</label>
						</div>
						<div class="easy-wp-smtp-setting-row__sub-row">
							<input type="text" class="easy-wp-smtp-search-box-term">
						</div>
					</div>
				</div>

				<div class="easy-wp-smtp-row easy-wp-smtp-setting-row--inactive easy-wp-smtp-setting-row">
					<div class="easy-wp-smtp-setting-row__label">
						<?php esc_html_e( 'Common Information', 'easy-wp-smtp' ); ?>
					</div>
					<div class="easy-wp-smtp-setting-row__field">
						<div class="easy-wp-smtp-checkbox-group">
							<label class="easy-wp-smtp-checkbox">
								<input type="checkbox" checked>
								<span class="easy-wp-smtp-checkbox__checkmark"></span>
								<span class="easy-wp-smtp-checkbox__label">
									<?php esc_html_e( 'To Address', 'easy-wp-smtp' ); ?>
								</span>
							</label>
							<label class="easy-wp-smtp-checkbox">
								<input type="checkbox" checked>
								<span class="easy-wp-smtp-checkbox__checkmark"></span>
								<span class="easy-wp-smtp-checkbox__label">
									<?php esc_html_e( 'From Address', 'easy-wp-smtp' ); ?>
								</span>
							</label>
							<label class="easy-wp-smtp-checkbox">
								<input type="checkbox" checked>
								<span class="easy-wp-smtp-checkbox__checkmark"></span>
								<span class="easy-wp-smtp-checkbox__label">
									<?php esc_html_e( 'From Name', 'easy-wp-smtp' ); ?>
								</span>
							</label>
							<label class="easy-wp-smtp-checkbox">
								<input type="checkbox" checked>
								<span class="easy-wp-smtp-checkbox__checkmark"></span>
								<span class="easy-wp-smtp-checkbox__label">
									<?php esc_html_e( 'Subject', 'easy-wp-smtp' ); ?>
								</span>
							</label>
							<label class="easy-wp-smtp-checkbox">
								<input type="checkbox" checked>
								<span class="easy-wp-smtp-checkbox__checkmark"></span>
								<span class="easy-wp-smtp-checkbox__label">
									<?php esc_html_e( 'Body', 'easy-wp-smtp' ); ?>
								</span>
							</label>
							<label class="easy-wp-smtp-checkbox">
								<input type="checkbox" checked>
								<span class="easy-wp-smtp-checkbox__checkmark"></span>
								<span class="easy-wp-smtp-checkbox__label">
									<?php esc_html_e( 'Created Date', 'easy-wp-smtp' ); ?>
								</span>
							</label>
							<label class="easy-wp-smtp-checkbox">
								<input type="checkbox" checked>
								<span class="easy-wp-smtp-checkbox__checkmark"></span>
								<span class="easy-wp-smtp-checkbox__label">
									<?php esc_html_e( 'Number of Attachments', 'easy-wp-smtp' ); ?>
								</span>
							</label>
							<label class="easy-wp-smtp-checkbox">
								<input type="checkbox" checked>
								<span class="easy-wp-smtp-checkbox__checkmark"></span>
								<span class="easy-wp-smtp-checkbox__label">
									<?php esc_html_e( 'Attachments', 'easy-wp-smtp' ); ?>
								</span>
							</label>
						</div>
					</div>
				</div>

				<div class="easy-wp-smtp-row easy-wp-smtp-setting-row--inactive easy-wp-smtp-setting-row">
					<div class="easy-wp-smtp-setting-row__label">
						<?php esc_html_e( 'Additional Information', 'easy-wp-smtp' ); ?>
					</div>
					<div class="easy-wp-smtp-setting-row__field">
						<div class="easy-wp-smtp-checkbox-group">
							<label class="easy-wp-smtp-checkbox">
								<span class="easy-wp-smtp-checkbox__checkmark"></span>
								<span class="easy-wp-smtp-checkbox__label">
									<?php esc_html_e( 'Status', 'easy-wp-smtp' ); ?>
								</span>
							</label>
							<label class="easy-wp-smtp-checkbox">
								<span class="easy-wp-smtp-checkbox__checkmark"></span>
								<span class="easy-wp-smtp-checkbox__label">
									<?php esc_html_e( 'Carbon Copy (CC)', 'easy-wp-smtp' ); ?>
								</span>
							</label>
							<label class="easy-wp-smtp-checkbox">
								<span class="easy-wp-smtp-checkbox__checkmark"></span>
								<span class="easy-wp-smtp-checkbox__label">
									<?php esc_html_e( 'Blind Carbon Copy (BCC)', 'easy-wp-smtp' ); ?>
								</span>
							</label>
							<label class="easy-wp-smtp-checkbox">
								<span class="easy-wp-smtp-checkbox__checkmark"></span>
								<span class="easy-wp-smtp-checkbox__label">
									<?php esc_html_e( 'Headers', 'easy-wp-smtp' ); ?>
								</span>
							</label>
							<label class="easy-wp-smtp-checkbox">
								<span class="easy-wp-smtp-checkbox__checkmark"></span>
								<span class="easy-wp-smtp-checkbox__label">
									<?php esc_html_e( 'Mailer', 'easy-wp-smtp' ); ?>
								</span>
							</label>
							<label class="easy-wp-smtp-checkbox">
								<span class="easy-wp-smtp-checkbox__checkmark"></span>
								<span class="easy-wp-smtp-checkbox__label">
									<?php esc_html_e( 'Error Details', 'easy-wp-smtp' ); ?>
								</span>
							</label>
							<label class="easy-wp-smtp-checkbox">
								<span class="easy-wp-smtp-checkbox__checkmark"></span>
								<span class="easy-wp-smtp-checkbox__label">
									<?php esc_html_e( 'Email log ID', 'easy-wp-smtp' ); ?>
								</span>
							</label>
							<label class="easy-wp-smtp-checkbox">
								<span class="easy-wp-smtp-checkbox__checkmark"></span>
								<span class="easy-wp-smtp-checkbox__label">
									<?php esc_html_e( 'Opened', 'easy-wp-smtp' ); ?>
								</span>
							</label>
							<label class="easy-wp-smtp-checkbox">
								<span class="easy-wp-smtp-checkbox__checkmark"></span>
								<span class="easy-wp-smtp-checkbox__label">
									<?php esc_html_e( 'Clicked', 'easy-wp-smtp' ); ?>
								</span>
							</label>
							<label class="easy-wp-smtp-checkbox">
								<span class="easy-wp-smtp-checkbox__checkmark"></span>
								<span class="easy-wp-smtp-checkbox__label">
									<?php esc_html_e( 'Source', 'easy-wp-smtp' ); ?>
								</span>
							</label>
						</div>
					</div>
				</div>
			</div>
		</div>

		<a href="<?php echo esc_url( $button_upgrade_link ); ?>" target="_blank" rel="noopener noreferrer" class="easy-wp-smtp-btn easy-wp-smtp-btn--lg easy-wp-smtp-btn--green">
			<?php esc_html_e( 'Upgrade to Easy WP SMTP Pro', 'easy-wp-smtp' ); ?>
		</a>
		<?php
	}
}
