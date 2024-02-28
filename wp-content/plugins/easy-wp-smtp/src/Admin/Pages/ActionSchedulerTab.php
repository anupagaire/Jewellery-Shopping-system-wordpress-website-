<?php

namespace EasyWPSMTP\Admin\Pages;

use EasyWPSMTP\Admin\PageAbstract;

/**
 * Class ActionScheduler.
 *
 * @since 2.1.0
 */
class ActionSchedulerTab extends PageAbstract {

	/**
	 * Part of the slug of a tab.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $slug = 'action-scheduler';

	/**
	 * Tab priority.
	 *
	 * @since 2.1.0
	 *
	 * @var int
	 */
	protected $priority = 30;

	/**
	 * Link label of a tab.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Scheduled Actions', 'easy-wp-smtp' );
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
	 * URL to a tab.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_link() {

		return add_query_arg( [ 's' => 'easy_wp_smtp' ], parent::get_link() );
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.1.0
	 */
	public function hooks() {

		add_action( 'current_screen', [ $this, 'init' ], 20 );
	}

	/**
	 * Init.
	 *
	 * @since 2.1.0
	 */
	public function init() {

		if ( $this->is_applicable() ) {
			\ActionScheduler_AdminView::instance()->process_admin_ui();
		}
	}

	/**
	 * Display scheduled actions table.
	 *
	 * @since 2.1.0
	 */
	public function display() {

		if ( ! $this->is_applicable() ) {
			return;
		}
		?>

		<div class="easy-wp-smtp-meta-box">
			<div class="easy-wp-smtp-meta-box__header">
				<div class="easy-wp-smtp-meta-box__heading">
					<?php esc_html_e( 'Scheduled Actions', 'easy-wp-smtp' ); ?>
				</div>
			</div>
			<div class="easy-wp-smtp-meta-box__content">
				<div class="easy-wp-smtp-row">
					<div class="easy-wp-smtp-row__desc">
						<p>
							<?php
							echo sprintf(
								wp_kses( /* translators: %s - Action Scheduler website URL. */
									__( 'Easy WP SMTP uses the <a href="%s" target="_blank" rel="noopener noreferrer">Action Scheduler</a> library, which lets it queue and process large tasks in the background without slowing down your site for visitors. Here you can see the list of all Easy WP SMTP Action Scheduler tasks and their statuses. This table can help with debugging certain issues.', 'easy-wp-smtp' ),
									[
										'a' => [
											'href'   => [],
											'rel'    => [],
											'target' => [],
										],
									]
								),
								'https://actionscheduler.org/'
							);
							?>
						</p>
						<p>
							<?php echo esc_html__( 'The Action Scheduler library is also used by other plugins, such as WPForms and WooCommerce. You might see tasks below that are not related to our plugin.', 'easy-wp-smtp' ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>

		<?php if ( isset( $_GET['s'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div id="easy-wp-smtp-reset-filter">
				<?php
				echo wp_kses(
					sprintf( /* translators: %s - search term. */
						__( 'Search results for <strong>%s</strong>', 'easy-wp-smtp' ),
						sanitize_text_field( wp_unslash( $_GET['s'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					),
					[ 'strong' => [] ]
				);
				?>
				<a href="<?php echo esc_url( remove_query_arg( 's' ) ); ?>">
					<i class="reset dashicons dashicons-dismiss"></i>
				</a>
			</div>
		<?php endif; ?>

		<div class="easy-wp-smtp-wp-list-table">
			<?php \ActionScheduler_AdminView::instance()->render_admin_ui(); ?>
		</div>

		<!-- Remove `.wp-header-end` element from DOM to prevent wrong notices position. -->
		<script>
			(function() {
				const headerEnd = document.querySelector( '.wrap > hr.wp-header-end' );
				if ( headerEnd !== null ) headerEnd.remove();
			})();
		</script>
		<?php
	}

	/**
	 * Check if ActionScheduler_AdminView class exists.
	 *
	 * @since 2.1.0
	 *
	 * @return bool
	 */
	private function is_applicable() {

		return class_exists( 'ActionScheduler_AdminView' );
	}
}
