<?php

namespace EasyWPSMTP\Admin\Pages;

use EasyWPSMTP\Admin\PageAbstract;
use EasyWPSMTP\Admin\ParentPageAbstract;

/**
 * Class LogsTab is a placeholder for Lite users and redirects them to Email Log page.
 *
 * @since 2.1.0
 */
class LogsTab extends PageAbstract {

	/**
	 * Part of the slug of a tab.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $slug = 'logs';

	/**
	 * Constructor.
	 *
	 * @since 2.1.0
	 *
	 * @param ParentPageAbstract $parent_page Tab parent page.
	 */
	public function __construct( $parent_page = null ) {

		parent::__construct( $parent_page );

		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( easy_wp_smtp()->get_admin()->is_admin_page() && $current_tab === 'logs' ) {
			$this->hooks();
		}
	}

	/**
	 * Link label of a tab.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Email Log', 'easy-wp-smtp' );
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
	 * Display the upsell content for the Email Log feature.
	 *
	 * @since 2.1.0
	 */
	public function display() {

		$button_upgrade_link = add_query_arg(
			[ 'discount' => 'LITEUPGRADE' ],
			easy_wp_smtp()->get_upgrade_link(
				[
					'medium'  => 'logs',
					'content' => 'Upgrade to Pro Button',
				]
			)
		);
		$link_upgrade_link   = add_query_arg(
			[ 'discount' => 'LITEUPGRADE' ],
			easy_wp_smtp()->get_upgrade_link(
				[
					'medium'  => 'logs',
					'content' => 'upgrade-to-easy-wp-smtp-pro-text-link',
				]
			)
		);

		$assets_url  = easy_wp_smtp()->assets_url . '/images/education/logs/';
		$screenshots = [
			[
				'url'           => $assets_url . 'screenshot-01.png',
				'url_thumbnail' => $assets_url . 'thumbnail-01.png',
				'title'         => __( 'Email Logs', 'easy-wp-smtp' ),
			],
			[
				'url'           => $assets_url . 'screenshot-02.png',
				'url_thumbnail' => $assets_url . 'thumbnail-02.png',
				'title'         => __( 'Detailed Email Log', 'easy-wp-smtp' ),
			],
		];
		?>

		<div class="easy-wp-smtp-meta-box">
			<div class="easy-wp-smtp-meta-box__header">
				<div class="easy-wp-smtp-meta-box__heading">
					<?php echo esc_html( $this->get_title() ); ?>
				</div>

				<a href="<?php echo esc_url( $button_upgrade_link ); ?>" target="_blank" rel="noopener noreferrer" class="easy-wp-smtp-btn easy-wp-smtp-btn--sm easy-wp-smtp-btn--green">
					<?php esc_html_e( 'Upgrade to Pro', 'easy-wp-smtp' ); ?>
				</a>
			</div>
			<div class="easy-wp-smtp-meta-box__content">
				<div class="easy-wp-smtp-row">
					<div class="easy-wp-smtp-row__desc">
						<?php
						echo wp_kses(
							sprintf( /* translators: %s - EasyWPSMTP.com page URL. */
								__( 'Email Logging saves information about all the emails sent from your WordPress site. Search and filter the email log to find specific emails and check their delivery statuses. When you enable email logging, you’ll also be able to resend emails, save attachments, and export logs as a CSV, Excel, or EML file. <a href="%s" target="_blank" rel="noopener noreferrer">Upgrade to Easy WP SMTP Pro</a> to start using email logs today.', 'easy-wp-smtp' ),
								esc_url( $link_upgrade_link )
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
					<div class="easy-wp-smtp-product-education-screenshots easy-wp-smtp-product-education-screenshots--two">
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
						<?php esc_html_e( 'Unlock these awesome logging features:', 'easy-wp-smtp' ); ?>
					</div>
					<div class="easy-wp-smtp-product-education-list">
						<ul>
							<li><?php esc_html_e( 'Save detailed email headers', 'easy-wp-smtp' ); ?></li>
							<li><?php esc_html_e( 'View email delivery status (sent or failed)', 'easy-wp-smtp' ); ?></li>
						</ul>
						<ul>
							<li><?php esc_html_e( 'Resend emails and attachments', 'easy-wp-smtp' ); ?></li>
							<li><?php esc_html_e( 'Track email opens and clicks', 'easy-wp-smtp' ); ?></li>
						</ul>
						<ul>
							<li><?php esc_html_e( 'Print or save email logs as PDFs', 'easy-wp-smtp' ); ?></li>
							<li><?php esc_html_e( 'Export logs to CSV, XLSX, or EML', 'easy-wp-smtp' ); ?></li>
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

	/**
	 * Not used as we are simply redirecting users.
	 *
	 * @since 2.1.0
	 *
	 * @param array $data Post data specific for the plugin.
	 */
	public function process_post( $data ) { }
}
