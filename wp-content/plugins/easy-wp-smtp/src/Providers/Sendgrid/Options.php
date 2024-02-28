<?php

namespace EasyWPSMTP\Providers\Sendgrid;

use EasyWPSMTP\ConnectionInterface;
use EasyWPSMTP\Providers\OptionsAbstract;

/**
 * Class Option.
 *
 * @since 2.2.0
 */
class Options extends OptionsAbstract {

	/**
	 * Options constructor.
	 *
	 * @since 2.2.0
	 *
	 * @param ConnectionInterface $connection The Connection object.
	 */
	public function __construct( $connection = null ) {

		parent::__construct(
			[
				'logo_url'    => easy_wp_smtp()->assets_url . '/images/providers/sendgrid.svg',
				'slug'        => 'sendgrid',
				'title'       => esc_html__( 'SendGrid', 'easy-wp-smtp' ),
				'description' => sprintf(
					wp_kses(
						/* translators: %1$s - URL to sendgrid.com; %2$s - URL to Sendgrid documentation on easywpsmtp.com */
						__( '<p><a href="%1$s" target="_blank" rel="noopener noreferrer">SendGrid</a> is a popular transactional email provider that sends more than 35 billion emails every month. If you\'re just starting out, the free plan allows you to send up to 100 emails each day without entering your credit card details.</p><p>To get started, read our <a href="%2$s" target="_blank" rel="noopener noreferrer">SendGrid documentation</a>.</p>', 'easy-wp-smtp' ),
						[
							'p' => [],
							'a' => [
								'href'   => [],
								'rel'    => [],
								'target' => [],
							],
						]
					),
					'https://sendgrid.com',
					esc_url( easy_wp_smtp()->get_utm_url( 'https://easywpsmtp.com/docs/setting-up-the-sendgrid-mailer/', 'SendGrid documentation' ) )
				),
				'supports'    => [
					'from_email'       => true,
					'from_name'        => true,
					'return_path'      => false,
					'from_email_force' => true,
					'from_name_force'  => true,
				],
			],
			$connection
		);
	}

	/**
	 * @inheritdoc
	 */
	public function display_options() {
		?>

		<!-- API Key -->
		<div id="easy-wp-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-api_key" class="easy-wp-smtp-row easy-wp-smtp-setting-row easy-wp-smtp-setting-row--text">
			<div class="easy-wp-smtp-setting-row__label">
				<label for="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-api_key"><?php esc_html_e( 'API Key', 'easy-wp-smtp' ); ?></label>
			</div>
			<div class="easy-wp-smtp-setting-row__field">
				<?php if ( $this->connection_options->is_const_defined( $this->get_slug(), 'api_key' ) ) : ?>
					<input type="text" disabled value="****************************************"
						id="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-api_key"
					/>
					<?php $this->display_const_set_message( 'EasyWPSMTP_SENDGRID_API_KEY' ); ?>
				<?php else : ?>
					<input type="password" spellcheck="false"
						name="easy-wp-smtp[<?php echo esc_attr( $this->get_slug() ); ?>][api_key]"
						value="<?php echo esc_attr( $this->connection_options->get( $this->get_slug(), 'api_key' ) ); ?>"
						id="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-api_key"
					/>
				<?php endif; ?>
				<p class="desc">
					<?php
					printf(
						/* translators: %s - API key link. */
						esc_html__( 'Follow this link to get an API Key from SendGrid: %s.', 'easy-wp-smtp' ),
						'<a href="https://app.sendgrid.com/settings/api_keys" target="_blank" rel="noopener noreferrer">' .
						esc_html__( 'Create API Key', 'easy-wp-smtp' ) .
						'</a>'
					);
					?>
					<br/>
					<?php
					printf(
						/* translators: %s - SendGrid access level. */
						esc_html__( 'To send emails you will need only a %s access level for this API key.', 'easy-wp-smtp' ),
						'<code>Mail Send</code>'
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
							/* translators: %s - URL to SendGrid documentation on easywpsmtp.com */
							__( 'Please input the sending domain/subdomain you configured in your SendGrid dashboard. More information can be found in our <a href="%s" target="_blank" rel="noopener noreferrer">SendGrid documentation</a>.', 'easy-wp-smtp' ),
							[
								'br' => [],
								'a'  => [
									'href'   => [],
									'rel'    => [],
									'target' => [],
								],
							]
						),
						esc_url( easy_wp_smtp()->get_utm_url( 'https://easywpsmtp.com/docs/setting-up-the-sendgrid-mailer/#setup', 'SendGrid documentation - setup' ) )
					);
					?>
				</p>
			</div>
		</div>

		<?php
	}
}
