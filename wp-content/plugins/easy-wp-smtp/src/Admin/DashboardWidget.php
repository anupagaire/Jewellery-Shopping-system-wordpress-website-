<?php

namespace EasyWPSMTP\Admin;

use EasyWPSMTP\Helpers\Helpers;
use EasyWPSMTP\Options;
use EasyWPSMTP\WP;
use EasyWPSMTP\Reports\Reports;
use EasyWPSMTP\Reports\Emails\Summary as SummaryReportEmail;

/**
 * Dashboard Widget shows the number of sent emails in WP Dashboard.
 *
 * @since 2.1.0
 */
class DashboardWidget {

	/**
	 * Instance slug.
	 *
	 * @since 2.1.0
	 *
	 * @const string
	 */
	const SLUG = 'dash_widget_lite';

	/**
	 * Constructor.
	 *
	 * @since 2.1.0
	 */
	public function __construct() {

		// Prevent the class initialization, if the dashboard widget hidden setting is enabled.
		if ( Options::init()->get( 'general', 'dashboard_widget_hidden' ) ) {
			return;
		}

		add_action( 'admin_init', [ $this, 'init' ] );
	}

	/**
	 * Init class.
	 *
	 * @since 2.1.0
	 */
	public function init() {

		// This widget should be displayed for certain high-level users only.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		/**
		 * Filters whether the initialization of the dashboard widget should be allowed.
		 *
		 * @since 2.1.0
		 *
		 * @param bool $var If the dashboard widget should be initialized.
		 */
		if ( ! apply_filters( 'easy_wp_smtp_admin_dashboard_widget', '__return_true' ) ) {
			return;
		}

		$this->hooks();
	}

	/**
	 * Widget hooks.
	 *
	 * @since 2.1.0
	 */
	public function hooks() {

		add_action( 'admin_enqueue_scripts', [ $this, 'widget_scripts' ] );
		add_action( 'wp_dashboard_setup', [ $this, 'widget_register' ] );

		add_action( 'wp_ajax_easy_wp_smtp_' . static::SLUG . '_save_widget_meta', [ $this, 'save_widget_meta_ajax' ] );
		add_action(
			'wp_ajax_easy_wp_smtp_' . static::SLUG . '_enable_summary_report_email',
			[
				$this,
				'enable_summary_report_email_ajax',
			]
		);
	}

	/**
	 * Load widget-specific scripts.
	 * Load them only on the admin dashboard page.
	 *
	 * @since 2.1.0
	 */
	public function widget_scripts() {

		$screen = get_current_screen();

		if ( ! isset( $screen->id ) || 'dashboard' !== $screen->id ) {
			return;
		}

		$min = WP::asset_min();

		wp_enqueue_style(
			'easy-wp-smtp-dashboard-widget',
			easy_wp_smtp()->assets_url . '/css/dashboard-widget.min.css',
			[],
			EasyWPSMTP_PLUGIN_VERSION
		);

		wp_enqueue_script(
			'easy-wp-smtp-moment',
			easy_wp_smtp()->assets_url . '/js/vendor/moment.min.js',
			[],
			'2.29.4',
			true
		);

		wp_enqueue_style(
			'easy-wp-smtp-chart',
			easy_wp_smtp()->assets_url . '/css/vendor/apexcharts.css',
			[],
			'3.35.1'
		);
		wp_enqueue_script(
			'easy-wp-smtp-chart',
			easy_wp_smtp()->assets_url . '/js/vendor/apexcharts.min.js',
			[],
			'3.35.1',
			true
		);

		wp_enqueue_script(
			'easy-wp-smtp-dashboard-widget',
			easy_wp_smtp()->assets_url . "/js/smtp-dashboard-widget{$min}.js",
			[ 'jquery', 'easy-wp-smtp-chart' ],
			EasyWPSMTP_PLUGIN_VERSION,
			true
		);

		wp_localize_script(
			'easy-wp-smtp-dashboard-widget',
			'easy_wp_smtp_dashboard_widget',
			[
				'slug'  => static::SLUG,
				'nonce' => wp_create_nonce( 'easy_wp_smtp_' . static::SLUG . '_nonce' ),
			]
		);
	}

	/**
	 * Register the widget.
	 *
	 * @since 2.1.0
	 */
	public function widget_register() {

		global $wp_meta_boxes;

		$widget_key = 'easy_wp_smtp_reports_widget_lite';

		wp_add_dashboard_widget(
			$widget_key,
			esc_html__( 'Easy WP SMTP', 'easy-wp-smtp' ),
			[ $this, 'widget_content' ]
		);

		// Attempt to place the widget at the top.
		$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
		$widget_instance  = [ $widget_key => $normal_dashboard[ $widget_key ] ];
		unset( $normal_dashboard[ $widget_key ] );
		$sorted_dashboard = array_merge( $widget_instance, $normal_dashboard );

		$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard; //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}

	/**
	 * Save a widget meta for a current user using AJAX.
	 *
	 * @since 2.1.0
	 */
	public function save_widget_meta_ajax() {

		check_admin_referer( 'easy_wp_smtp_' . static::SLUG . '_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		$meta  = ! empty( $_POST['meta'] ) ? sanitize_key( $_POST['meta'] ) : '';
		$value = ! empty( $_POST['value'] ) ? sanitize_key( $_POST['value'] ) : 0;

		$this->widget_meta( 'set', $meta, $value );

		wp_send_json_success();
	}

	/**
	 * Enable summary report email using AJAX.
	 *
	 * @since 2.1.0
	 */
	public function enable_summary_report_email_ajax() {

		check_admin_referer( 'easy_wp_smtp_' . static::SLUG . '_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		$options = Options::init();

		$data = [
			'general' => [
				SummaryReportEmail::SETTINGS_SLUG => false,
			],
		];

		$options->set( $data, false, false );

		wp_send_json_success();
	}

	/**
	 * Load widget content.
	 *
	 * @since 2.1.0
	 */
	public function widget_content() {

		echo '<div class="easy-wp-smtp-dash-widget easy-wp-smtp-dash-widget--lite">';

		$this->widget_content_html();

		echo '</div>';
	}

	/**
	 * Widget content HTML.
	 *
	 * @since 2.1.0
	 */
	private function widget_content_html() {

		$hide_graph                      = (bool) $this->widget_meta( 'get', 'hide_graph' );
		$hide_summary_report_email_block = (bool) $this->widget_meta( 'get', 'hide_summary_report_email_block' );
		?>

		<?php if ( ! $hide_graph ) : ?>
		<div class="easy-wp-smtp-dash-widget-chart-block-container">
			<div class="easy-wp-smtp-dash-widget-block easy-wp-smtp-dash-widget-chart-block">
				<div class="easy-wp-smtp-apexcharts" id="easy-wp-smtp-dash-widget-chart"></div>
				<div class="easy-wp-smtp-dash-widget-chart-upgrade">
					<div class="easy-wp-smtp-dash-widget-modal">
						<a href="#" class="easy-wp-smtp-dash-widget-dismiss-chart-upgrade">
							<span class="dashicons dashicons-no-alt"></span>
						</a>
						<h2><?php esc_html_e( 'View Detailed Email Stats', 'easy-wp-smtp' ); ?></h2>
						<p><?php esc_html_e( 'Automatically keep track of every email sent from your WordPress site and view valuable statistics right here in your dashboard.', 'easy-wp-smtp' ); ?></p>
						<p>
							<a href="<?php echo esc_url( easy_wp_smtp()->get_upgrade_link( [ 'medium' => 'dashboard-widget', 'content' => 'upgrade-to-easy-wp-smtp-pro' ] ) ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound ?>" target="_blank" rel="noopener noreferrer" class="button button-primary button-hero">
								<?php esc_html_e( 'Upgrade to Easy WP SMTP Pro', 'easy-wp-smtp' ); ?>
							</a>
						</p>
					</div>
				</div>
				<div class="easy-wp-smtp-dash-widget-overlay"></div>
			</div>
		</div>
		<?php endif; ?>

		<div class="easy-wp-smtp-dash-widget-block easy-wp-smtp-dash-widget-block-settings">
			<div>
				<?php
					$this->timespan_select_html();
				?>
			</div>
		</div>

		<div id="easy-wp-smtp-dash-widget-email-stats-block" class="easy-wp-smtp-dash-widget-block easy-wp-smtp-dash-widget-email-stats-block">
			<?php $this->email_stats_block(); ?>
		</div>

		<?php if ( SummaryReportEmail::is_disabled() && ! $hide_summary_report_email_block ) : ?>
			<div id="easy-wp-smtp-dash-widget-summary-report-email-block" class="easy-wp-smtp-dash-widget-block easy-wp-smtp-dash-widget-summary-report-email-block">
				<div>
					<div class="easy-wp-smtp-dash-widget-summary-report-email-block-setting">
						<label for="easy-wp-smtp-dash-widget-summary-report-email-enable">
							<input type="checkbox" id="easy-wp-smtp-dash-widget-summary-report-email-enable">
							<i class="easy-wp-smtp-dash-widget-loader"></i>
							<span>
								<?php
								echo wp_kses(
									__( '<b>NEW!</b> Enable Weekly Email Summaries', 'easy-wp-smtp' ),
									[
										'b' => [],
									]
								);
								?>
							</span>
						</label>
						<a href="<?php echo esc_url( SummaryReportEmail::get_preview_link() ); ?>" target="_blank">
							<?php esc_html_e( 'View Example', 'easy-wp-smtp' ); ?>
						</a>
						<i class="dashicons dashicons-dismiss easy-wp-smtp-dash-widget-summary-report-email-dismiss"></i>
					</div>
					<div class="easy-wp-smtp-dash-widget-summary-report-email-block-applied hidden">
						<i class="easy-wp-smtp-dashicons-yes-alt-green"></i>
						<span><?php esc_attr_e( 'Weekly Email Summaries have been enabled', 'easy-wp-smtp' ); ?></span>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<div id="easy-wp-smtp-dash-widget-upgrade-footer" class="easy-wp-smtp-dash-widget-block easy-wp-smtp-dash-widget-upgrade-footer easy-wp-smtp-dash-widget-upgrade-footer--<?php echo ! $hide_graph ? 'hide' : 'show'; ?>">
			<p>
				<?php
				printf(
					wp_kses( /* translators: %s - URL to EasyWPSMTP.com. */
						__( '<a href="%s" target="_blank" rel="noopener noreferrer">Upgrade to Pro</a> for detailed stats, email logs, and more!', 'easy-wp-smtp' ),
						[
							'a' => [
								'href'   => [],
								'rel'    => [],
								'target' => [],
							],
						]
					),
					esc_url( easy_wp_smtp()->get_upgrade_link( [ 'medium' => 'dashboard-widget', 'content' => 'upgrade-to-pro' ] ) ) // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Timespan select HTML.
	 *
	 * @since 2.1.0
	 */
	private function timespan_select_html() {

		?>
		<select id="easy-wp-smtp-dash-widget-timespan" class="easy-wp-smtp-dash-widget-select-timespan" title="<?php esc_attr_e( 'Select timespan', 'easy-wp-smtp' ); ?>">
			<option value="all">
				<?php esc_html_e( 'All Time', 'easy-wp-smtp' ); ?>
			</option>
			<?php foreach ( [ 7, 14, 30 ] as $option ) : ?>
				<option value="<?php echo absint( $option ); ?>" disabled>
					<?php /* translators: %d - Number of days. */ ?>
					<?php echo esc_html( sprintf( _n( 'Last %d day', 'Last %d days', absint( $option ), 'easy-wp-smtp' ), absint( $option ) ) ); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<?php
	}

	/**
	 * Email statistics block.
	 *
	 * @since 2.1.0
	 */
	private function email_stats_block() {

		$output_data = $this->get_email_stats_data();
		?>

		<table id="easy-wp-smtp-dash-widget-email-stats-table" cellspacing="0">
			<tr>
				<?php
				$count   = 0;
				$per_row = 2;

				foreach ( array_values( $output_data ) as $stats ) :
					if ( ! is_array( $stats ) ) {
						continue;
					}

					if ( ! isset( $stats['icon'], $stats['title'] ) ) {
						continue;
					}

					// Make some exceptions for mailers without send confirmation functionality.
					if ( Helpers::mailer_without_send_confirmation() ) {
						$per_row = 3;
					}

					// Create new row after every $per_row cells.
					if ( $count !== 0 && $count % $per_row === 0 ) {
						echo '</tr><tr>';
					}

					$count++;
					?>
					<td class="easy-wp-smtp-dash-widget-email-stats-table-cell easy-wp-smtp-dash-widget-email-stats-table-cell--<?php echo esc_attr( $stats['type'] ); ?> easy-wp-smtp-dash-widget-email-stats-table-cell--3">
						<div class="easy-wp-smtp-dash-widget-email-stats-table-cell-container">
							<div class="easy-wp-smtp-dash-widget-email-stats-table-cell-heading">
								<?php
								echo wp_kses(
									$stats['icon'],
									[
										'svg'  => [
											'xmlns'   => [],
											'viewbox' => [],
											'fill'    => [],
											'width'   => [],
											'height'  => [],
										],
										'path' => [
											'd'    => [],
											'fill' => [],
										],
									]
								);
								?>

								<h6><?php echo esc_html( $stats['title'] ); ?></h6>
							</div>
							<div class="easy-wp-smtp-dash-widget-email-stats-table-cell-value">
								<?php echo esc_html( $stats['count'] ); ?>
							</div>
						</div>
					</td>
				<?php endforeach; ?>
			</tr>
		</table>

		<?php
	}

	/**
	 * Prepare the email stats data.
	 * The text and counts of the email stats.
	 *
	 * @since 2.1.0
	 *
	 * @return array[]
	 */
	private function get_email_stats_data() {

		$reports    = new Reports();
		$total_sent = $reports->get_total_emails_sent();

		$output_data = [
			'all'       => [
				'type'  => 'all',
				'icon'  => '<svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.26552 3.99976H16.7345C17.9115 3.99976 18.5 4.56374 18.5 5.69171V13.6365C18.5 14.7645 17.9115 15.3285 16.7345 15.3285H4.26552C3.08851 15.3285 2.5 14.7645 2.5 13.6365V5.69171C2.5 4.56374 3.08851 3.99976 4.26552 3.99976ZM10.5184 12.1285L16.8448 6.9055C16.9674 6.80742 17.0533 6.66029 17.1023 6.46412C17.1513 6.26796 17.1023 6.08405 16.9552 5.9124C16.8326 5.71623 16.6609 5.60589 16.4402 5.58137C16.2441 5.55684 16.0479 5.60589 15.8517 5.72849L10.5184 9.36987L5.14828 5.72849C4.97663 5.60589 4.78046 5.55684 4.55977 5.58137C4.33908 5.60589 4.16743 5.71623 4.04483 5.9124C3.92222 6.08405 3.87318 6.26796 3.8977 6.46412C3.94674 6.66029 4.03257 6.80742 4.15517 6.9055L10.5184 12.1285Z" fill="#211FA6"/></svg>',
				'title' => esc_html__( 'Total Emails', 'easy-wp-smtp' ),
				'count' => $total_sent,
			],
			'delivered' => [
				'type'  => 'delivered',
				'icon'  => '<svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18.25 10C18.25 5.75 14.75 2.25 10.5 2.25C6.21875 2.25 2.75 5.75 2.75 10C2.75 14.2812 6.21875 17.75 10.5 17.75C14.75 17.75 18.25 14.2812 18.25 10ZM9.59375 14.125C9.40625 14.3125 9.0625 14.3125 8.875 14.125L5.625 10.875C5.4375 10.6875 5.4375 10.3438 5.625 10.1562L6.34375 9.46875C6.53125 9.25 6.84375 9.25 7.03125 9.46875L9.25 11.6562L13.9375 6.96875C14.125 6.75 14.4375 6.75 14.625 6.96875L15.3438 7.65625C15.5312 7.84375 15.5312 8.1875 15.3438 8.375L9.59375 14.125Z" fill="#0F8A56"/></svg>',
				'title' => esc_html__( 'Confirmed', 'easy-wp-smtp' ),
				'count' => 'N/A',
			],
			'sent'      => [
				'type'  => 'sent',
				'icon'  => '<svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18.25 10C18.25 5.75 14.75 2.25 10.5 2.25C6.21875 2.25 2.75 5.75 2.75 10C2.75 14.2812 6.21875 17.75 10.5 17.75C14.75 17.75 18.25 14.2812 18.25 10ZM9.59375 14.125C9.40625 14.3125 9.0625 14.3125 8.875 14.125L5.625 10.875C5.4375 10.6875 5.4375 10.3438 5.625 10.1562L6.34375 9.46875C6.53125 9.25 6.84375 9.25 7.03125 9.46875L9.25 11.6562L13.9375 6.96875C14.125 6.75 14.4375 6.75 14.625 6.96875L15.3438 7.65625C15.5312 7.84375 15.5312 8.1875 15.3438 8.375L9.59375 14.125Z" fill="#8B8B9D"/></svg>',
				'title' => esc_html__( 'Unconfirmed', 'easy-wp-smtp' ),
				'count' => 'N/A',
			],
			'unsent'    => [
				'type'  => 'unsent',
				'icon'  => '<svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.5 2.25C6.21875 2.25 2.75 5.71875 2.75 10C2.75 14.2812 6.21875 17.75 10.5 17.75C14.7812 17.75 18.25 14.2812 18.25 10C18.25 5.71875 14.7812 2.25 10.5 2.25ZM14.2812 12.0625C14.4375 12.1875 14.4375 12.4375 14.2812 12.5938L13.0625 13.8125C12.9062 13.9688 12.6562 13.9688 12.5312 13.8125L10.5 11.75L8.4375 13.8125C8.3125 13.9688 8.0625 13.9688 7.90625 13.8125L6.6875 12.5625C6.53125 12.4375 6.53125 12.1875 6.6875 12.0312L8.75 10L6.6875 7.96875C6.53125 7.84375 6.53125 7.59375 6.6875 7.4375L7.9375 6.21875C8.0625 6.0625 8.3125 6.0625 8.46875 6.21875L10.5 8.25L12.5312 6.21875C12.6562 6.0625 12.9062 6.0625 13.0625 6.21875L14.2812 7.4375C14.4375 7.59375 14.4375 7.84375 14.2812 7.96875L12.25 10L14.2812 12.0625Z" fill="#DF2A4A"/></svg>',
				'title' => esc_html__( 'Failed', 'easy-wp-smtp' ),
				'count' => 'N/A',
			],
		];

		if ( Helpers::mailer_without_send_confirmation() ) {
			// Skip the 'unconfirmed sent' section.
			unset( $output_data['sent'] );

			// Change the 'confirmed sent' section into a general 'sent' section.
			$output_data['delivered']['title'] = esc_html__( 'Sent', 'easy-wp-smtp' );
		}

		return $output_data;
	}

	/**
	 * Get/set a widget meta.
	 *
	 * @since 2.1.0
	 *
	 * @param string $action Possible value: 'get' or 'set'.
	 * @param string $meta   Meta name.
	 * @param int    $value  Value to set.
	 *
	 * @return mixed
	 */
	protected function widget_meta( $action, $meta, $value = 0 ) {

		$allowed_actions = [ 'get', 'set' ];

		if ( ! in_array( $action, $allowed_actions, true ) ) {
			return false;
		}

		$defaults = [
			'hide_graph'                      => 0,
			'hide_summary_report_email_block' => 0,
		];

		if ( ! array_key_exists( $meta, $defaults ) ) {
			return false;
		}

		$meta_key = 'easy_wp_smtp_' . static::SLUG . '_' . $meta;

		if ( 'get' === $action ) {
			$meta_value = get_user_meta( get_current_user_id(), $meta_key, true );

			return empty( $meta_value ) ? $defaults[ $meta ] : $meta_value;
		}

		$value = sanitize_key( $value );

		if ( 'set' === $action && ! empty( $value ) ) {
			return update_user_meta( get_current_user_id(), $meta_key, $value );
		}

		if ( 'set' === $action && empty( $value ) ) {
			return delete_user_meta( get_current_user_id(), $meta_key );
		}

		return false;
	}
}
