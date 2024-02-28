<?php

namespace EasyWPSMTP\Admin\Pages;

use EasyWPSMTP\Admin\PageAbstract;

/**
 * Class EmailTrackingReportsTab is a placeholder for Pro email tracking reports.
 * Displays product education.
 *
 * @since 2.1.0
 */
class EmailReportsTab extends PageAbstract {

	/**
	 * Part of the slug of a tab.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $slug = 'reports';

	/**
	 * Tab priority.
	 *
	 * @since 2.1.0
	 *
	 * @var int
	 */
	protected $priority = 10;

	/**
	 * Link label of a tab.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Email Reports', 'easy-wp-smtp' );
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
	 * Register hooks.
	 *
	 * @since 2.1.0
	 */
	public function hooks() {

		add_action( 'easy_wp_smtp_admin_area_enqueue_assets', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Enqueue required JS and CSS.
	 *
	 * @since 2.1.0
	 */
	public function enqueue_assets() {

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

	/**
	 * Output HTML of the email reports education.
	 *
	 * @since 2.1.0
	 */
	public function display() {

		$button_upgrade_link = easy_wp_smtp()->get_upgrade_link(
			[
				'medium'  => 'email-reports',
				'content' => 'upgrade-to-easy-wp-smtp-pro-button-link',
			]
		);

		$assets_url  = easy_wp_smtp()->assets_url . '/images/education/reports/';
		$screenshots = [
			[
				'url'           => $assets_url . 'screenshot-01.png',
				'url_thumbnail' => $assets_url . 'thumbnail-01.png',
				'title'         => __( 'Stats at a Glance', 'easy-wp-smtp' ),
			],
			[
				'url'           => $assets_url . 'screenshot-02.png',
				'url_thumbnail' => $assets_url . 'thumbnail-02.png',
				'title'         => __( 'Detailed Stats by Subject Line', 'easy-wp-smtp' ),
			],
			[
				'url'           => $assets_url . 'screenshot-03.png',
				'url_thumbnail' => $assets_url . 'thumbnail-03.png',
				'title'         => __( 'Weekly Email Report', 'easy-wp-smtp' ),
			],
		];
		?>

		<div class="easy-wp-smtp-meta-box">
			<div class="easy-wp-smtp-meta-box__content">

				<div class="easy-wp-smtp-row">
					<div class="easy-wp-smtp-row__desc">
						<?php
						echo wp_kses(
							sprintf( /* translators: %s - EasyWPSMTP.com page URL. */
								__( 'With Email Reports, you can track email deliverability and engagement from your WordPress dashboard. Open and click-through rates are grouped by subject line for quick and simple campaign performance analysis. The report will also show how many emails you successfully sent and how many emails failed to send each week so you can find and resolve problems with ease. <a href="%s" target="_blank" rel="noopener noreferrer">Upgrade to Easy WP SMTP Pro</a> now and we’ll add your email report to your WordPress dashboard.', 'easy-wp-smtp' ),
								esc_url(
									easy_wp_smtp()->get_upgrade_link(
										[
											'medium'  => 'email-reports',
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

				<div class="easy-wp-smtp-row">
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

				<div class="easy-wp-smtp-row easy-wp-smtp-row--has-bg-color easy-wp-smtp-product-education-cta-row">
					<div class="easy-wp-smtp-row__heading easy-wp-smtp-settings-heading">
						<?php esc_html_e( 'Unlock these awesome reporting features:', 'easy-wp-smtp' ); ?>
					</div>
					<div class="easy-wp-smtp-product-education-list">
						<ul>
							<li><?php esc_html_e( 'Receive weekly deliverability reports', 'easy-wp-smtp' ); ?></li>
							<li><?php esc_html_e( 'See stats grouped by subject line', 'easy-wp-smtp' ); ?></li>
						</ul>
						<ul>
							<li><?php esc_html_e( 'Track total sent emails each week', 'easy-wp-smtp' ); ?></li>
							<li><?php esc_html_e( 'Monitor open and click-through rates', 'easy-wp-smtp' ); ?></li>
						</ul>
						<ul>
							<li><?php esc_html_e( 'Identify failed emails quickly', 'easy-wp-smtp' ); ?></li>
							<li><?php esc_html_e( 'View email report charts in WordPress', 'easy-wp-smtp' ); ?></li>
						</ul>
					</div>
					<a href="<?php echo esc_url( $button_upgrade_link ); ?>" target="_blank" rel="noopener noreferrer" class="easy-wp-smtp-btn easy-wp-smtp-btn--lg easy-wp-smtp-btn--green">
						<?php esc_html_e( 'Upgrade to Easy WP SMTP Pro', 'easy-wp-smtp' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php
	}
}
