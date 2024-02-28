<?php

namespace EasyWPSMTP\Providers\Sendinblue;

use EasyWPSMTP\ConnectionInterface;
use EasyWPSMTP\Providers\OptionsAbstract;

/**
 * Class Options.
 *
 * @since 2.0.0
 */
class Options extends OptionsAbstract {

	/**
	 * Mailer slug.
	 *
	 * @since 2.0.0
	 */
	const SLUG = 'sendinblue';

	/**
	 * Options constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param ConnectionInterface $connection The Connection object.
	 */
	public function __construct( $connection = null ) {

		if ( is_null( $connection ) ) {
			$connection = easy_wp_smtp()->get_connections_manager()->get_primary_connection();
		}

		$description = sprintf(
			wp_kses( /* translators: %1$s - URL to brevo.com site. */
				__( '<p><a href="%1$s" target="_blank" rel="noopener noreferrer">Brevo</a> (formerly Sendinblue) is a transactional email provider and email marketing platform. It’s suitable for businesses of all sizes, as it offers scalable pricing plans that can grow with you. New business owners can use the free plan to send up to 300 emails a day without providing credit card details. As your needs change, you can upgrade to increase your sending limits.</p>', 'easy-wp-smtp' ) .
				/* translators: %2$s - URL to easywpsmtp.com doc. */
				__( '<p>To get started, <a href="%2$s" target="_blank" rel="noopener noreferrer">see our documentation for the Brevo mailer</a>.</p>', 'easy-wp-smtp' ),
				[
					'strong' => true,
					'p'      => true,
					'a'      => [
						'href'   => true,
						'rel'    => true,
						'target' => true,
					],
				]
			),
			'https://easywpsmtp.com/go/sendinblue/',
			esc_url( easy_wp_smtp()->get_utm_url( 'https://easywpsmtp.com/docs/setting-up-the-sendinblue-mailer/', 'Brevo documentation' ) )
		);

		$description .= '<p class="easy-wp-smtp-tooltip">' .
			esc_html__( 'Transparency and Disclosure', 'easy-wp-smtp' ) .
			'<span class="easy-wp-smtp-tooltip-text">' .
			esc_html__( 'For full transparency, we want you to know that the Brevo (formerly Sendinblue) links above are tracking links as part of our partnership with Brevo. Although we can choose to recommend any SMTP service, we only partner with products we believe will provide value to our users.', 'easy-wp-smtp' ) .
			'</span></p>';

		parent::__construct(
			[
				'logo_url'    => easy_wp_smtp()->assets_url . '/images/providers/brevo.svg',
				'slug'        => self::SLUG,
				'title'       => esc_html__( 'Brevo', 'easy-wp-smtp' ),
				'php'         => '5.6',
				'description' => $description,
				'supports'    => [
					'from_email'       => true,
					'from_name'        => true,
					'from_email_force' => true,
					'from_name_force'  => true,
				],
			],
			$connection
		);
	}

	/**
	 * Output the mailer provider options.
	 *
	 * @since 2.0.0
	 */
	public function display_options() {

		// Do not display options if PHP version is not correct.
		if ( ! $this->is_php_correct() ) {
			$this->display_php_warning();

			return;
		}
		?>

		<!-- API Key -->
		<div id="easy-wp-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-client_id"
			class="easy-wp-smtp-row easy-wp-smtp-setting-row easy-wp-smtp-setting-row--text">
			<div class="easy-wp-smtp-setting-row__label">
				<label for="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-api_key"><?php esc_html_e( 'API Key', 'easy-wp-smtp' ); ?></label>
			</div>
			<div class="easy-wp-smtp-setting-row__field">
				<?php if ( $this->connection_options->is_const_defined( $this->get_slug(), 'api_key' ) ) : ?>
					<input type="text" disabled value="****************************************"
						id="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-api_key"
					/>
					<?php $this->display_const_set_message( 'EASY_WP_SMTP_SENDINBLUE_API_KEY' ); ?>
				<?php else : ?>
					<input type="password" spellcheck="false"
						name="easy-wp-smtp[<?php echo esc_attr( $this->get_slug() ); ?>][api_key]"
						value="<?php echo esc_attr( $this->connection_options->get( $this->get_slug(), 'api_key' ) ); ?>"
						id="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-api_key"
					/>
				<?php endif; ?>

				<p class="desc">
					<?php
					printf( /* translators: %s - link to get an API Key. */
						esc_html__( 'Follow this link to get an API Key: %s.', 'easy-wp-smtp' ),
						'<a href="https://app.brevo.com/settings/keys/api" target="_blank" rel="noopener noreferrer">' .
						esc_html__( 'Get v3 API Key', 'easy-wp-smtp' ) .
						'</a>'
					);
					?>
				</p>
			</div>
		</div>

		<!-- Sending Domain -->
		<div id="easy-wp-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-domain" class="easy-wp-smtp-row easy-wp-smtp-setting-row easy-wp-smtp-setting-row--text">
			<div class="easy-wp-smtp-setting-row__label">
				<label for="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-domain"><?php esc_html_e( 'Sending Domain', 'easy-wp-smtp' ); ?></label>
			</div>
			<div class="easy-wp-smtp-setting-row__field">
				<input name="easy-wp-smtp[<?php echo esc_attr( $this->get_slug() ); ?>][domain]" type="text"
					   value="<?php echo esc_attr( $this->connection_options->get( $this->get_slug(), 'domain' ) ); ?>"
					<?php echo $this->connection_options->is_const_defined( $this->get_slug(), 'domain' ) ? 'disabled' : ''; ?>
					   id="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-domain" spellcheck="false"
				/>
				<p class="desc">
					<?php
					printf(
						wp_kses(
							/* translators: %s - URL to Brevo documentation on easywpsmtp.com */
							__( 'Please input the sending domain/subdomain you configured in your Brevo (formerly Sendinblue) dashboard. More information can be found in our <a href="%s" target="_blank" rel="noopener noreferrer">Brevo documentation</a>.', 'easy-wp-smtp' ),
							[
								'br' => [],
								'a'  => [
									'href'   => [],
									'rel'    => [],
									'target' => [],
								],
							]
						),
						esc_url( easy_wp_smtp()->get_utm_url( 'https://easywpsmtp.com/docs/setting-up-the-sendinblue-mailer/#setup-smtp', 'Brevo documentation' ) )
					);
					?>
				</p>
			</div>
		</div>

		<?php
	}
}
