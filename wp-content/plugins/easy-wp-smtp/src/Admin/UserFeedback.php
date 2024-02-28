<?php

namespace EasyWPSMTP\Admin;

use EasyWPSMTP\Options;

/**
 * Asking users for their experience with this plugin.
 *
 * @since 1.5.3
 */
class UserFeedback {

	/**
	 * The wp option for notice dismissal data.
	 *
	 * @since 1.5.3
	 */
	const OPTION_NAME = 'easy_wp_smtp_user_feedback_notice';

	/**
	 * How many days after activation it should display the user feedback notice.
	 *
	 * @since 1.5.3
	 */
	const DELAY_NOTICE = 14;

	/**
	 * Initialize user feedback notice functionality.
	 *
	 * @since 1.5.3
	 */
	public function init() {

		add_action( 'admin_init', [ $this, 'admin_notices' ] );
		add_action( 'wp_ajax_easy_wp_smtp_feedback_notice_dismiss', [ $this, 'feedback_notice_dismiss' ] );
	}

	/**
	 * Display notices only in Network Admin if in Multisite.
	 * Otherwise, display in Admin Dashboard.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function admin_notices() {

		if ( is_multisite() ) {
			add_action( 'network_admin_notices', [ $this, 'maybe_display' ] );
		} else {
			add_action( 'admin_notices', [ $this, 'maybe_display' ] );
		}
	}

	/**
	 * Maybe display the user feedback notice.
	 *
	 * @since 1.5.3
	 */
	public function maybe_display() {

		// Only admin users should see the feedback notice.
		if ( ! is_super_admin() ) {
			return;
		}

		$options = get_option( self::OPTION_NAME );

		// Set default options.
		if ( empty( $options ) ) {
			$options = [
				'time'      => time(),
				'dismissed' => false,
			];

			update_option( self::OPTION_NAME, $options );
		}

		// Check if the feedback notice was not dismissed already.
		if ( isset( $options['dismissed'] ) && ! $options['dismissed'] ) {
			$this->display();
		}
	}

	/**
	 * Display the user feedback notice.
	 *
	 * @since 1.5.3
	 */
	private function display() {

		// Skip if SMTP settings are not configured.
		if ( ! $this->is_smtp_configured() ) {
			return;
		}

		// Fetch when plugin was initially activated.
		$activated = get_option( 'easy_wp_smtp_activated_time' );

		// Skip if the plugin is active for less than a defined number of days.
		if ( empty( $activated ) || ( $activated + ( DAY_IN_SECONDS * self::DELAY_NOTICE ) ) > time() ) {
			return;
		}

		?>
		<div class="notice notice-info is-dismissible easy-wp-smtp-review-notice">
			<div class="easy-wp-smtp-review-step easy-wp-smtp-review-step-1">
				<p><?php esc_html_e( 'Are you enjoying Easy WP SMTP?', 'easy-wp-smtp' ); ?></p>
				<p>
					<a href="#" class="easy-wp-smtp-review-switch-step"
						 data-step="3"><?php esc_html_e( 'Yes', 'easy-wp-smtp' ); ?></a><br/>
					<a href="#" class="easy-wp-smtp-review-switch-step"
						 data-step="2"><?php esc_html_e( 'Not Really', 'easy-wp-smtp' ); ?></a>
				</p>
			</div>
			<div class="easy-wp-smtp-review-step easy-wp-smtp-review-step-2" style="display: none">
				<p><?php esc_html_e( 'We\'re sorry to hear you aren\'t enjoying Easy WP SMTP. We would love a chance to improve. Could you take a minute and let us know what we can do better?', 'easy-wp-smtp' ); ?></p>
				<p>
					<?php
					printf(
						'<a href="https://easywpsmtp.com/plugin-feedback/" class="easy-wp-smtp-dismiss-review-notice easy-wp-smtp-review-out" target="_blank" rel="noopener noreferrer">%s</a>',
						esc_html__( 'Give Feedback', 'easy-wp-smtp' )
					);
					?>
					<br>
					<a href="#" class="easy-wp-smtp-dismiss-review-notice" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'No thanks', 'easy-wp-smtp' ); ?>
					</a>
				</p>
			</div>
			<div class="easy-wp-smtp-review-step easy-wp-smtp-review-step-3" style="display: none">
				<p><?php esc_html_e( 'That’s awesome! Could you please do me a BIG favor and give it a 5-star rating on WordPress to help us spread the word and boost our motivation?', 'easy-wp-smtp' ); ?></p>
				<p><strong><?php esc_html_e( '~ Easy WP SMTP team', 'easy-wp-smtp' ); ?></strong></p>
				<p>
					<a href="https://wordpress.org/support/plugin/easy-wp-smtp/reviews/?filter=5#new-post"
						 class="easy-wp-smtp-dismiss-review-notice easy-wp-smtp-review-out" target="_blank"
						 rel="noopener noreferrer">
						<?php esc_html_e( 'OK, you deserve it', 'easy-wp-smtp' ); ?>
					</a><br>
					<a href="#" class="easy-wp-smtp-dismiss-review-notice" target="_blank"
						 rel="noopener noreferrer"><?php esc_html_e( 'Nope, maybe later', 'easy-wp-smtp' ); ?></a><br>
					<a href="#" class="easy-wp-smtp-dismiss-review-notice" target="_blank"
						 rel="noopener noreferrer"><?php esc_html_e( 'I already did', 'easy-wp-smtp' ); ?></a>
				</p>
			</div>
		</div>
		<script type="text/javascript">
      jQuery(document).ready(function ($) {
        $(document).on('click', '.easy-wp-smtp-dismiss-review-notice, .easy-wp-smtp-review-notice button', function (e) {
          if (!$(this).hasClass('easy-wp-smtp-review-out')) {
            e.preventDefault();
          }
          $.post(ajaxurl, {action: 'easy_wp_smtp_feedback_notice_dismiss'});
          $('.easy-wp-smtp-review-notice').remove();
        });

        $(document).on('click', '.easy-wp-smtp-review-switch-step', function (e) {
          e.preventDefault();
          var target = parseInt($(this).attr('data-step'), 10);

          if (target) {
            var $notice = $(this).closest('.easy-wp-smtp-review-notice');
            var $review_step = $notice.find('.easy-wp-smtp-review-step-' + target);

            if ($review_step.length > 0) {
              $notice.find('.easy-wp-smtp-review-step:visible').fadeOut(function () {
                $review_step.fadeIn();
              });
            }
          }
        });
      });
		</script>
		<?php
	}

	/**
	 * Check if the mailer is configured.
	 *
	 * @since 1.5.3
	 *
	 * @return bool
	 */
	public function is_smtp_configured() {

		// Get the currently selected mailer.
		$mailer = Options::init()->get( 'mail', 'mailer' );

		// Skip if no or the default mailer is selected.
		if ( empty( $mailer ) || $mailer === 'mail' ) {
			return false;
		}

		$mailer_object = easy_wp_smtp()
			->get_providers()
			->get_mailer( $mailer, easy_wp_smtp()->get_processor()->get_phpmailer() );

		// Check if mailer setup is complete.
		return ! empty( $mailer_object ) ? $mailer_object->is_mailer_complete() : false;
	}

	/**
	 * Dismiss the user feedback admin notice.
	 *
	 * @since 1.5.3
	 */
	public function feedback_notice_dismiss() {

		$options              = get_option( self::OPTION_NAME, [] );
		$options['time']      = time();
		$options['dismissed'] = true;

		update_option( self::OPTION_NAME, $options );

		if ( is_super_admin() && is_multisite() ) {
			$site_list = get_sites();

			foreach ( (array) $site_list as $site ) {
				switch_to_blog( $site->blog_id );

				update_option( self::OPTION_NAME, $options );

				restore_current_blog();
			}
		}

		wp_send_json_success();
	}
}
