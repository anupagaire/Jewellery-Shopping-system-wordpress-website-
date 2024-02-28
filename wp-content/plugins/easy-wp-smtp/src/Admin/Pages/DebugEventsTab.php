<?php

namespace EasyWPSMTP\Admin\Pages;

use EasyWPSMTP\Admin\Area;
use EasyWPSMTP\Admin\DebugEvents\DebugEvents;
use EasyWPSMTP\Admin\DebugEvents\Migration;
use EasyWPSMTP\Admin\DebugEvents\Table;
use EasyWPSMTP\Admin\PageAbstract;
use EasyWPSMTP\Admin\ParentPageAbstract;
use EasyWPSMTP\Options;
use EasyWPSMTP\WP;

/**
 * Debug Events settings page.
 *
 * @since 2.0.0
 */
class DebugEventsTab extends PageAbstract {

	/**
	 * Part of the slug of a tab.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $slug = 'debug-events';

	/**
	 * Tab priority.
	 *
	 * @since 2.0.0
	 *
	 * @var int
	 */
	protected $priority = 40;

	/**
	 * Debug events list table.
	 *
	 * @since 2.0.0
	 *
	 * @var Table
	 */
	protected $table = null;

	/**
	 * Plugin options.
	 *
	 * @since 2.0.0
	 *
	 * @var Options
	 */
	protected $options;

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param ParentPageAbstract $parent_page Tab parent page.
	 */
	public function __construct( $parent_page = null ) {

		$this->options = Options::init();

		parent::__construct( $parent_page );

		// Remove unnecessary $_GET parameters and prevent url duplications in _wp_http_referer input.
		$this->remove_get_parameters();
	}

	/**
	 * Link label of a tab.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Debug Events', 'easy-wp-smtp' );
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
	 * @since 2.0.0
	 */
	public function hooks() {

		add_action( 'easy_wp_smtp_admin_area_enqueue_assets', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Enqueue required JS and CSS.
	 *
	 * @since 2.0.0
	 */
	public function enqueue_assets() {

		$min = WP::asset_min();

		wp_enqueue_style(
			'easy-wp-smtp-flatpickr',
			easy_wp_smtp()->assets_url . '/css/vendor/flatpickr.min.css',
			[],
			'4.6.9'
		);
		wp_enqueue_script(
			'easy-wp-smtp-flatpickr',
			easy_wp_smtp()->assets_url . '/js/vendor/flatpickr.min.js',
			[ 'jquery' ],
			'4.6.9',
			true
		);

		wp_enqueue_script(
			'easy-wp-smtp-tools-debug-events',
			easy_wp_smtp()->assets_url . "/js/smtp-tools-debug-events{$min}.js",
			[ 'jquery', 'easy-wp-smtp-flatpickr' ],
			EasyWPSMTP_PLUGIN_VERSION,
			true
		);

		wp_localize_script(
			'easy-wp-smtp-tools-debug-events',
			'easy_wp_smtp_tools_debug_events',
			[
				'lang_code'  => sanitize_key( WP::get_language_code() ),
				'plugin_url' => easy_wp_smtp()->plugin_url,
				'loader'     => easy_wp_smtp()->prepare_loader(),
				'texts'      => [
					'delete_all_notice' => esc_html__( 'Are you sure you want to permanently delete all debug events?', 'easy-wp-smtp' ),
					'cancel'            => esc_html__( 'Cancel', 'easy-wp-smtp' ),
					'close'             => esc_html__( 'Close', 'easy-wp-smtp' ),
					'yes'               => esc_html__( 'Yes', 'easy-wp-smtp' ),
					'ok'                => esc_html__( 'OK', 'easy-wp-smtp' ),
					'notice_title'      => esc_html__( 'Heads up!', 'easy-wp-smtp' ),
					'error_occurred'    => esc_html__( 'An error occurred!', 'easy-wp-smtp' ),
				],
			]
		);
	}

	/**
	 * Get email logs list table.
	 *
	 * @since 2.0.0
	 *
	 * @return Table
	 */
	public function get_table() {

		if ( $this->table === null ) {
			$this->table = new Table();
		}

		return $this->table;
	}

	/**
	 * Display scheduled actions table.
	 *
	 * @since 2.0.0
	 */
	public function display() {

		?>
		<form method="POST" action="<?php echo esc_url( $this->get_link() ); ?>">
			<?php $this->wp_nonce_field(); ?>

			<div class="easy-wp-smtp-meta-box">
				<div class="easy-wp-smtp-meta-box__header">
					<div class="easy-wp-smtp-meta-box__heading">
						<?php esc_html_e( 'Debug Events', 'easy-wp-smtp' ); ?>
					</div>
				</div>
				<div class="easy-wp-smtp-meta-box__content">
					<div class="easy-wp-smtp-row">
						<div class="easy-wp-smtp-row__desc">
							<?php esc_html_e( 'Here, you can view and configure plugin debugging events to find and resolve email sending issues. You’ll also see any email sending errors that occur.', 'easy-wp-smtp' ); ?>
						</div>
					</div>

					<!-- Debug Events -->
					<div id="easy-wp-smtp-setting-row-debug_event_types" class="easy-wp-smtp-row easy-wp-smtp-setting-row">
						<div class="easy-wp-smtp-setting-row__label">
							<label for="easy-wp-smtp-setting-debug_event_types">
								<?php esc_html_e( 'Event Types', 'easy-wp-smtp' ); ?>
							</label>
						</div>
						<div class="easy-wp-smtp-setting-row__field">
							<div class="easy-wp-smtp-setting-row__sub-row">
								<label class="easy-wp-smtp-toggle" for="easy-wp-smtp-setting-debug_events_email_errors">
									<input name="easy-wp-smtp[debug_events][email_errors]" type="checkbox"
												 value="true" checked disabled id="easy-wp-smtp-setting-debug_events_email_errors"
									/>
									<span class="easy-wp-smtp-toggle__switch"></span>
									<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--static"><?php esc_html_e( 'Email Sending Errors', 'easy-wp-smtp' ); ?></span>
								</label>
								<p class="desc">
									<?php esc_html_e( 'The Email Sending Errors debug event is always enabled and records any email sending errors in the table below.', 'easy-wp-smtp' ); ?>
								</p>
							</div>

							<div class="easy-wp-smtp-setting-row__sub-row">
								<label class="easy-wp-smtp-toggle" for="easy-wp-smtp-setting-debug_events_email_debug">
									<input name="easy-wp-smtp[debug_events][email_debug]" type="checkbox"
												 value="true" <?php checked( true, $this->options->get( 'debug_events', 'email_debug' ) ); ?>
												 id="easy-wp-smtp-setting-debug_events_email_debug"
									/>
									<span class="easy-wp-smtp-toggle__switch"></span>
									<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--static"><?php esc_html_e( 'Debug Email Sending', 'easy-wp-smtp' ); ?></span>
								</label>
								<p class="desc">
									<?php esc_html_e( 'Enable this setting to debug the email sending process. All debug events will be logged in the table below. This setting is recommended only for shorter debugging periods. Please disable it once you’re done troubleshooting.', 'easy-wp-smtp' ); ?>
								</p>
							</div>
						</div>
					</div>

					<div id="easy-wp-smtp-setting-row-debug_events_retention_period" class="easy-wp-smtp-row easy-wp-smtp-setting-row easy-wp-smtp-setting-row--text">
						<div class="easy-wp-smtp-setting-row__label">
							<label for="easy-wp-smtp-setting-debug_events_retention_period">
								<?php esc_html_e( 'Events Retention Period', 'easy-wp-smtp' ); ?>
							</label>
						</div>
						<div class="easy-wp-smtp-setting-row__field">
							<select name="easy-wp-smtp[debug_events][retention_period]" id="easy-wp-smtp-setting-debug_events_retention_period"
								<?php disabled( $this->options->is_const_defined( 'debug_events', 'retention_period' ) ); ?>>
								<option value=""><?php esc_html_e( 'Forever', 'easy-wp-smtp' ); ?></option>
								<?php foreach ( $this->get_debug_events_retention_period_options() as $value => $label ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $this->options->get( 'debug_events', 'retention_period' ), $value ); ?>>
										<?php echo esc_html( $label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="desc">
								<?php
								esc_html_e( 'Debug events that fall outside the chosen period will be permanently deleted from the database.', 'easy-wp-smtp' );

								if ( $this->options->is_const_defined( 'debug_events', 'retention_period' ) ) {
									//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo '<br>' . $this->options->get_const_set_message( 'EASY_WP_SMTP_DEBUG_EVENTS_RETENTION_PERIOD' );
								}
								?>
							</p>
						</div>
					</div>
				</div>
			</div>

			<?php $this->display_save_btn(); ?>
		</form>
		<?php

		if ( ! DebugEvents::is_valid_db() ) {
			$this->display_debug_events_not_installed();
		} else {
			$table = $this->get_table();
			$table->prepare_items();

			?>
			<form  action="<?php echo esc_url( $this->get_link() ); ?>" method="get" class="easy-wp-smtp-debug-events-table easy-wp-smtp-wp-list-table">
				<input type="hidden" name="page" value="<?php echo esc_attr( Area::SLUG . '-tools' ); ?>" />
				<input type="hidden" name="tab" value="<?php echo esc_attr( $this->get_slug() ); ?>" />
			<?php

			// State of status filter for submission with other filters.
			if ( $table->get_filtered_types() !== false ) {
				printf( '<input type="hidden" name="type" value="%s">', esc_attr( $table->get_filtered_types() ) );
			}

			if ( $this->get_filters_html() ) {
				?>
				<div id="easy-wp-smtp-reset-filter">
					<?php
					$type = $table->get_filtered_types();

					echo wp_kses(
						sprintf( /* translators: %1$s - number of debug events found; %2$s - filtered type. */
							_n(
								'Found <strong>%1$s %2$s event</strong>',
								'Found <strong>%1$s %2$s events</strong>',
								absint( $table->get_pagination_arg( 'total_items' ) ),
								'easy-wp-smtp'
							),
							absint( $table->get_pagination_arg( 'total_items' ) ),
							$type !== false && isset( $table->get_types()[ $type ] ) ? $table->get_types()[ $type ] : ''
						),
						[
							'strong' => [],
						]
					);
					?>

					<?php foreach ( $this->get_filters_html() as $id => $html ) : ?>
						<?php
						echo wp_kses(
							$html,
							[ 'em' => [] ]
						);
						?>
						<i class="reset dashicons dashicons-dismiss" data-scope="<?php echo esc_attr( $id ); ?>"></i>
					<?php endforeach; ?>
				</div>
				<?php
			}

			$table->search_box(
				esc_html__( 'Search Events', 'easy-wp-smtp' ),
				Area::SLUG . '-debug-events-search-input'
			);

			$table->views();
			$table->display();
			?>
			</form>
			<?php
		}
	}

	/**
	 * Process tab form submission ($_POST ).
	 *
	 * @since 2.0.0
	 *
	 * @param array $data Post data specific for the plugin.
	 */
	public function process_post( $data ) {

		$this->check_admin_referer();

		// Unchecked checkboxes doesn't exist in $_POST, so we need to ensure we actually have them in data to save.
		if ( empty( $data['debug_events']['email_debug'] ) ) {
			$data['debug_events']['email_debug'] = false;
		}

		// All the sanitization is done there.
		$this->options->set( $data, false, false );

		WP::add_admin_notice(
			esc_html__( 'Settings were successfully saved.', 'easy-wp-smtp' ),
			WP::ADMIN_NOTICE_SUCCESS
		);
	}

	/**
	 * Return an array with information (HTML and id) for each filter for this current view.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	private function get_filters_html() {

		$filters = [
			'.search-box'               => $this->get_filter_search_html(),
			'.easy-wp-smtp-filter-date' => $this->get_filter_date_html(),
		];

		return array_filter( $filters );
	}

	/**
	 * Return HTML with information about the search filter.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	private function get_filter_search_html() {

		$table = $this->get_table();
		$term  = $table->get_filtered_search();

		if ( $term === false ) {
			return '';
		}

		return sprintf( /* translators: %s The searched term. */
			__( 'where event contains "%s"', 'easy-wp-smtp' ),
			'<em>' . esc_html( $term ) . '</em>'
		);
	}

	/**
	 * Return HTML with information about the date filter.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	private function get_filter_date_html() {

		$table = $this->get_table();
		$dates = $table->get_filtered_dates();

		if ( $dates === false ) {
			return '';
		}

		$dates = array_map(
			function ( $date ) {
				return date_i18n( 'M j, Y', strtotime( $date ) );
			},
			$dates
		);

		$html = '';

		switch ( count( $dates ) ) {
			case 1:
				$html = sprintf( /* translators: %s - Date. */
					esc_html__( 'on %s', 'easy-wp-smtp' ),
					'<em>' . $dates[0] . '</em>'
				);
				break;
			case 2:
				$html = sprintf( /* translators: %1$s - Date. %2$s - Date. */
					esc_html__( 'between %1$s and %2$s', 'easy-wp-smtp' ),
					'<em>' . $dates[0] . '</em>',
					'<em>' . $dates[1] . '</em>'
				);
				break;
		}

		return $html;
	}

	/**
	 * Display a message when debug events DB table is missing.
	 *
	 * @since 2.0.0
	 */
	private function display_debug_events_not_installed() {

		$error_message = get_option( Migration::ERROR_OPTION_NAME );
		?>

		<div class="notice-inline notice-error">
			<h3><?php esc_html_e( 'Debug Events are Not Installed Correctly', 'easy-wp-smtp' ); ?></h3>

			<p>
				<?php
				if ( ! empty( $error_message ) ) {
					esc_html_e( 'The database table was not installed correctly. Please contact plugin support to diagnose and fix the issue. Provide them the error message below:', 'easy-wp-smtp' );
					echo '<br><br>';
					echo '<code>' . esc_html( $error_message ) . '</code>';
				} else {
					esc_html_e( 'For some reason the database table was not installed correctly. Please contact plugin support team to diagnose and fix the issue.', 'easy-wp-smtp' );
				}
				?>
			</p>
		</div>

		<?php
	}

	/**
	 * Remove unnecessary $_GET parameters for shorter URL.
	 *
	 * @since 2.0.0
	 */
	protected function remove_get_parameters() {

		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$_SERVER['REQUEST_URI'] = remove_query_arg(
				[
					'_wp_http_referer',
					'_wpnonce',
					'easy-wp-smtp-debug-events-nonce',
				],
				$_SERVER['REQUEST_URI'] // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			);
		}
	}

	/**
	 * Get debug events retention period options.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_debug_events_retention_period_options() {

		$options = [
			604800   => esc_html__( '1 Week', 'easy-wp-smtp' ),
			2628000  => esc_html__( '1 Month', 'easy-wp-smtp' ),
			7885000  => esc_html__( '3 Months', 'easy-wp-smtp' ),
			15770000 => esc_html__( '6 Months', 'easy-wp-smtp' ),
			31540000 => esc_html__( '1 Year', 'easy-wp-smtp' ),
		];

		$debug_event_retention_period = $this->options->get( 'debug_events', 'retention_period' );

		// Check if defined value already in list and add it if not.
		if (
			! empty( $debug_event_retention_period ) &&
			! isset( $options[ $debug_event_retention_period ] )
		) {
			$debug_event_retention_period_days = floor( $debug_event_retention_period / DAY_IN_SECONDS );

			$options[ $debug_event_retention_period ] = sprintf(
				/* translators: %d - days count. */
				_n( '%d Day', '%d Days', $debug_event_retention_period_days, 'easy-wp-smtp' ),
				$debug_event_retention_period_days
			);

			ksort( $options );
		}

		/**
		 * Filter debug events retention period options.
		 *
		 * @since 2.0.0
		 *
		 * @param array $options Debug Events retention period options.
		 *                       Option key in seconds.
		 */
		return apply_filters(
			'easy_wp_smtp_admin_pages_debug_events_tab_get_debug_events_retention_period_options',
			$options
		);
	}
}
