<?php

namespace EasyWPSMTP\Admin;

use EasyWPSMTP\Helpers\Helpers;
use EasyWPSMTP\Options;
use EasyWPSMTP\Tasks\Tasks;
use EasyWPSMTP\WP;

/**
 * Notifications.
 *
 * @since 2.0.0
 */
class Notifications {

	/**
	 * Source of notifications content.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	const SOURCE_URL = 'https://plugin.easywpsmtp.com/wp-content/notifications.json';

	/**
	 * The WP option key for storing the notification options.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	const OPTION_KEY = 'easy_wp_smtp_notifications';

	/**
	 * Option value.
	 *
	 * @since 2.0.0
	 *
	 * @var bool|array
	 */
	public $option = false;

	/**
	 * Initialize class.
	 *
	 * @since 2.0.0
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function hooks() {

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'easy_wp_smtp_admin_pages_before_content', [ $this, 'output' ] );
		add_action( 'easy_wp_smtp_admin_notifications_update', [ $this, 'update' ] );
		add_action( 'wp_ajax_easy_wp_smtp_notification_dismiss', [ $this, 'dismiss' ] );
	}

	/**
	 * Check if user has access and is enabled.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function has_access() {

		$access = false;

		if (
			current_user_can( 'manage_options' ) &&
			! Options::init()->get( 'general', 'am_notifications_hidden' )
		) {
			$access = true;
		}

		return apply_filters( 'easy_wp_smtp_admin_notifications_has_access', $access );
	}

	/**
	 * Get option value.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $cache Reference property cache if available.
	 *
	 * @return array
	 */
	public function get_option( $cache = true ) {

		if ( $this->option && $cache ) {
			return $this->option;
		}

		$option = get_option( self::OPTION_KEY, [] );

		$this->option = [
			'update'    => ! empty( $option['update'] ) ? $option['update'] : 0,
			'events'    => ! empty( $option['events'] ) ? $option['events'] : [],
			'feed'      => ! empty( $option['feed'] ) ? $option['feed'] : [],
			'dismissed' => ! empty( $option['dismissed'] ) ? $option['dismissed'] : [],
		];

		return $this->option;
	}

	/**
	 * Fetch notifications from feed.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function fetch_feed() {

		$response = wp_remote_get(
			self::SOURCE_URL,
			[
				'user-agent' => Helpers::get_default_user_agent(),
			]
		);

		if ( is_wp_error( $response ) ) {
			return [];
		}

		$body = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			return [];
		}

		return $this->verify( json_decode( $body, true ) );
	}

	/**
	 * Verify notification data before it is saved.
	 *
	 * @since 2.0.0
	 *
	 * @param array $notifications Array of notification items to verify.
	 *
	 * @return array
	 */
	protected function verify( $notifications ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$data = [];

		if ( ! is_array( $notifications ) || empty( $notifications ) ) {
			return $data;
		}

		$option = $this->get_option();

		foreach ( $notifications as $notification ) {

			// The message should never be empty, if they are, ignore.
			if ( empty( $notification['content'] ) ) {
				continue;
			}

			// Ignore if license type does not match.
			if ( ! in_array( easy_wp_smtp()->get_license_type(), $notification['type'], true ) ) {
				continue;
			}

			// Ignore if expired.
			if ( ! empty( $notification['end'] ) && time() > strtotime( $notification['end'] ) ) {
				continue;
			}

			// Ignore if notification has already been dismissed.
			if ( ! empty( $option['dismissed'] ) && in_array( $notification['id'], $option['dismissed'] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				continue;
			}

			// Ignore if notification existed before installing WPForms.
			// Prevents bombarding the user with notifications after activation.
			$activated = get_option( 'easy_wp_smtp_activated_time' );

			if (
				! empty( $activated ) &&
				! empty( $notification['start'] ) &&
				$activated > strtotime( $notification['start'] )
			) {
				continue;
			}

			$data[] = $notification;
		}

		return $data;
	}

	/**
	 * Verify saved notification data for active notifications.
	 *
	 * @since 2.0.0
	 *
	 * @param array $notifications Array of notification items to verify.
	 *
	 * @return array
	 */
	protected function verify_active( $notifications ) {

		if ( ! is_array( $notifications ) || empty( $notifications ) ) {
			return [];
		}

		// Remove notifications that are not active.
		foreach ( $notifications as $key => $notification ) {
			if (
				( ! empty( $notification['start'] ) && time() < strtotime( $notification['start'] ) ) ||
				( ! empty( $notification['end'] ) && time() > strtotime( $notification['end'] ) )
			) {
				unset( $notifications[ $key ] );
			}
		}

		return $notifications;
	}

	/**
	 * Get notification data.
	 *
	 * @since 2.0.0
	 * @since 2.2.0 Make the AS a recurring task.
	 *
	 * @return array
	 */
	public function get() {

		if ( ! $this->has_access() ) {
			return [];
		}

		$option = $this->get_option();

		// Update notifications a recurring task.
		if ( Tasks::is_scheduled( 'easy_wp_smtp_admin_notifications_update' ) === false ) {
			easy_wp_smtp()->get_tasks()
				->create( 'easy_wp_smtp_admin_notifications_update' )
				->recurring(
					strtotime( '+1 minute' ),
					$this->get_notification_update_task_interval()
				)
				->params()
				->register();
		}

		$events = ! empty( $option['events'] ) ? $this->verify_active( $option['events'] ) : [];
		$feed   = ! empty( $option['feed'] ) ? $this->verify_active( $option['feed'] ) : [];

		return array_merge( $events, $feed );
	}

	/**
	 * Get the update notifications interval.
	 *
	 * @since 2.2.0
	 *
	 * @return int
	 */
	private function get_notification_update_task_interval() {

		/**
		 * Filters the interval for the notifications update task.
		 *
		 * @since 2.2.0
		 *
		 * @param int $interval The interval in seconds. Default to a day (in seconds).
		 */
		return (int) apply_filters( 'easy_wp_smtp_admin_notifications_get_notification_update_task_interval', DAY_IN_SECONDS );
	}

	/**
	 * Get notification count.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_count() {

		return count( $this->get() );
	}

	/**
	 * Add a manual notification event.
	 *
	 * @since 2.0.0
	 *
	 * @param array $notification Notification data.
	 */
	public function add( $notification ) {

		if ( empty( $notification['id'] ) ) {
			return;
		}

		$option = $this->get_option();

		if ( in_array( $notification['id'], $option['dismissed'] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			return;
		}

		foreach ( $option['events'] as $item ) {
			if ( $item['id'] === $notification['id'] ) {
				return;
			}
		}

		$notification = $this->verify( [ $notification ] );

		update_option(
			self::OPTION_KEY,
			[
				'update'    => $option['update'],
				'feed'      => $option['feed'],
				'events'    => array_merge( $notification, $option['events'] ),
				'dismissed' => $option['dismissed'],
			]
		);
	}

	/**
	 * Update notification data from feed.
	 *
	 * @since 2.0.0
	 */
	public function update() {

		$feed   = $this->fetch_feed();
		$option = $this->get_option();

		update_option(
			self::OPTION_KEY,
			[
				'update'    => time(),
				'feed'      => $feed,
				'events'    => $option['events'],
				'dismissed' => $option['dismissed'],
			]
		);
	}

	/**
	 * Admin area assets.
	 *
	 * @since 2.0.0
	 *
	 * @param string $hook Hook suffix for the current admin page.
	 */
	public function enqueue_assets( $hook ) {

		if ( strpos( $hook, Area::SLUG ) === false ) {
			return;
		}

		if ( ! $this->has_access() ) {
			return;
		}

		$notifications = $this->get();

		if ( empty( $notifications ) ) {
			return;
		}

		wp_enqueue_style(
			'easy-wp-smtp-admin-notifications',
			easy_wp_smtp()->assets_url . '/css/admin-notifications.min.css',
			[],
			EasyWPSMTP_PLUGIN_VERSION
		);

		wp_enqueue_script(
			'easy-wp-smtp-admin-notifications',
			easy_wp_smtp()->assets_url . '/js/smtp-notifications' . WP::asset_min() . '.js',
			[ 'jquery' ],
			EasyWPSMTP_PLUGIN_VERSION,
			true
		);
	}

	/**
	 * Output notifications.
	 *
	 * @since 2.0.0
	 */
	public function output() { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded

		$notifications = $this->get();

		if ( empty( $notifications ) ) {
			return;
		}

		$notifications_html   = '';
		$current_class        = ' current';
		$content_allowed_tags = [
			'em'     => [],
			'i'      => [],
			'strong' => [],
			'span'   => [
				'style' => [],
			],
			'a'      => [
				'href'   => [],
				'target' => [],
				'rel'    => [],
			],
			'br'     => [],
			'p'      => [
				'id'    => [],
				'class' => [],
			],
		];

		foreach ( $notifications as $notification ) {

			// Buttons HTML.
			$buttons_html = '';
			if ( ! empty( $notification['btns'] ) && is_array( $notification['btns'] ) ) {
				foreach ( $notification['btns'] as $btn_type => $btn ) {
					if ( empty( $btn['text'] ) ) {
						continue;
					}
					$buttons_html .= sprintf(
						'<a href="%1$s" class="easy-wp-smtp-btn easy-wp-smtp-btn--sm easy-wp-smtp-btn--%2$s"%3$s>%4$s</a>',
						! empty( $btn['url'] ) ? esc_url( $btn['url'] ) : '',
						$btn_type === 'main' ? 'primary' : 'secondary',
						! empty( $btn['target'] ) && $btn['target'] === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '',
						sanitize_text_field( $btn['text'] )
					);
				}
				$buttons_html = ! empty( $buttons_html ) ? '<div class="easy-wp-smtp-notifications-buttons">' . $buttons_html . '</div>' : '';
			}

			// Notification HTML.
			$notifications_html .= sprintf(
				'<div class="easy-wp-smtp-notifications-message%5$s" data-message-id="%4$s">
					<h3 class="easy-wp-smtp-notifications-title">%1$s</h3>
					<div class="easy-wp-smtp-notifications-content">%2$s</div>
					%3$s
				</div>',
				! empty( $notification['title'] ) ? sanitize_text_field( $notification['title'] ) : '',
				! empty( $notification['content'] ) ? wp_kses( wpautop( $notification['content'] ), $content_allowed_tags ) : '',
				$buttons_html,
				! empty( $notification['id'] ) ? esc_attr( sanitize_text_field( $notification['id'] ) ) : 0,
				$current_class
			);

			// Only first notification is current.
			$current_class = '';
		}
		?>

		<div id="easy-wp-smtp-notifications">

			<div class="easy-wp-smtp-notifications-header">
				<div class="easy-wp-smtp-notifications-bell">
					<svg width="17" height="19" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.434 13.012c-.668-.739-1.97-1.828-1.97-5.45 0-2.707-1.898-4.886-4.5-5.449v-.738C9.965.777 9.474.25 8.876.25 8.242.25 7.75.777 7.75 1.375v.738c-2.602.563-4.5 2.742-4.5 5.45 0 3.62-1.3 4.71-1.969 5.449-.21.21-.316.492-.281.738 0 .598.422 1.125 1.125 1.125H15.59c.703 0 1.125-.527 1.16-1.125 0-.246-.105-.527-.316-.738zm-13.079.175c.739-.949 1.547-2.601 1.583-5.59v-.035c0-2.144 1.757-3.937 3.937-3.937 2.145 0 3.938 1.793 3.938 3.938 0 .035-.036.035-.036.035.036 2.988.844 4.64 1.582 5.59H3.355zm5.52 5.063c1.23 0 2.215-.984 2.215-2.25H6.625c0 1.266.984 2.25 2.25 2.25z" fill="#DF2A4A"/></svg>
				</div>
				<div class="easy-wp-smtp-notifications-title"><?php esc_html_e( 'Notifications', 'easy-wp-smtp' ); ?></div>
			</div>

			<div class="easy-wp-smtp-notifications-body">
				<a class="dismiss" title="<?php echo esc_attr__( 'Dismiss this message', 'easy-wp-smtp' ); ?>"><svg width="16" height="16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 .25A7.749 7.749 0 0 0 .25 8 7.749 7.749 0 0 0 8 15.75 7.749 7.749 0 0 0 15.75 8 7.749 7.749 0 0 0 8 .25zm0 14A6.228 6.228 0 0 1 1.75 8 6.248 6.248 0 0 1 8 1.75c3.438 0 6.25 2.813 6.25 6.25A6.248 6.248 0 0 1 8 14.25zm3.156-8.188c.156-.125.156-.375 0-.53l-.687-.688c-.156-.157-.406-.157-.531 0L8 6.78 6.031 4.844c-.125-.157-.375-.157-.531 0l-.688.687c-.156.157-.156.407 0 .532L6.75 8 4.812 9.969c-.156.125-.156.375 0 .531l.688.688c.156.156.406.156.531 0L8 9.25l1.938 1.938c.124.156.374.156.53 0l.688-.688c.156-.156.156-.406 0-.531L9.22 8l1.937-1.938z" fill="currentColor"/></svg></a>

				<?php if ( count( $notifications ) > 1 ) : ?>
					<div class="navigation">
						<a class="prev">
							<span class="screen-reader-text"><?php esc_attr_e( 'Previous message', 'easy-wp-smtp' ); ?></span>
							<span aria-hidden="true">‹</span>
						</a>
						<a class="next">
							<span class="screen-reader-text"><?php esc_attr_e( 'Next message', 'easy-wp-smtp' ); ?>"></span>
							<span aria-hidden="true">›</span>
						</a>
					</div>
				<?php endif; ?>

				<div class="easy-wp-smtp-notifications-messages">
					<?php echo $notifications_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Dismiss notification via AJAX.
	 *
	 * @since 2.0.0
	 */
	public function dismiss() {

		// Run a security check.
		check_ajax_referer( 'easy-wp-smtp-admin', 'nonce' );

		// Check for access and required param.
		if ( ! current_user_can( 'manage_options' ) || empty( $_POST['id'] ) ) {
			wp_send_json_error();
		}

		$id     = sanitize_text_field( wp_unslash( $_POST['id'] ) );
		$option = $this->get_option();
		$type   = is_numeric( $id ) ? 'feed' : 'events';

		$option['dismissed'][] = $id;
		$option['dismissed']   = array_unique( $option['dismissed'] );

		// Remove notification.
		if ( is_array( $option[ $type ] ) && ! empty( $option[ $type ] ) ) {
			foreach ( $option[ $type ] as $key => $notification ) {
				if ( $notification['id'] == $id ) { // phpcs:ignore WordPress.PHP.StrictComparisons
					unset( $option[ $type ][ $key ] );
					break;
				}
			}
		}

		update_option( self::OPTION_KEY, $option );

		wp_send_json_success();
	}
}
