<?php

namespace EasyWPSMTP\Providers\Mailgun;

use EasyWPSMTP\ConnectionInterface;
use EasyWPSMTP\Providers\OptionsAbstract;

/**
 * Class Option.
 *
 * @since 2.0.0
 */
class Options extends OptionsAbstract {

	/**
	 * Mailgun constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param ConnectionInterface $connection The Connection object.
	 */
	public function __construct( $connection = null ) {

		parent::__construct(
			array(
				'logo_url'    => easy_wp_smtp()->assets_url . '/images/providers/mailgun.svg',
				'slug'        => 'mailgun',
				'title'       => esc_html__( 'Mailgun', 'easy-wp-smtp' ),
				'description' => sprintf(
					wp_kses(
						/* translators: %1$s - URL to mailgun.com; %2$s - URL to Mailgun documentation on easywpsmtp.com */
						__( '<p><a href="%1$s" target="_blank" rel="noopener noreferrer">Mailgun</a> is a transactional email provider that offers a generous 3-month free trial. After that, it offers a \'Pay As You Grow\' plan that allows you to pay for what you use without committing to a fixed monthly rate.</p><p>To get started, read our <a href="%2$s" target="_blank" rel="noopener noreferrer">Mailgun documentation</a>.</p>', 'easy-wp-smtp' ),
						array(
							'p' => array(),
							'a' => array(
								'href'   => array(),
								'rel'    => array(),
								'target' => array(),
							),
						)
					),
					'https://www.mailgun.com',
					esc_url( easy_wp_smtp()->get_utm_url( 'https://easywpsmtp.com/docs/setting-up-the-mailgun-mailer/', 'Mailgun documentation' ) )
				),
			),
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
				<label for="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-api_key"><?php esc_html_e( 'Mailgun API Key', 'easy-wp-smtp' ); ?></label>
			</div>
			<div class="easy-wp-smtp-setting-row__field">
				<?php if ( $this->connection_options->is_const_defined( $this->get_slug(), 'api_key' ) ) : ?>
					<input type="text" disabled value="****************************************"
						id="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-api_key"
					/>
					<?php $this->display_const_set_message( 'EASY_WP_SMTP_MAILGUN_API_KEY' ); ?>
				<?php else : ?>
					<input type="password" spellcheck="false"
						name="easy-wp-smtp[<?php echo esc_attr( $this->get_slug() ); ?>][api_key]"
						value="<?php echo esc_attr( $this->connection_options->get( $this->get_slug(), 'api_key' ) ); ?>"
						id="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-api_key"
					/>
				<?php endif; ?>
				<p class="desc">
					<?php
					echo wp_kses(
						sprintf( /* translators: %s - API key URL. */
							__( 'Follow this link to <a href="%s" target="_blank" rel="noopener noreferrer">get a Mailgun API Key</a>. Generate a key in the "Mailgun API Keys" section.', 'easy-wp-smtp' ),
							'https://app.mailgun.com/settings/api_security'
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
				</p>
			</div>
		</div>

		<!-- Domain -->
		<div id="easy-wp-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-domain" class="easy-wp-smtp-row easy-wp-smtp-setting-row easy-wp-smtp-setting-row--text">
			<div class="easy-wp-smtp-setting-row__label">
				<label for="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-domain"><?php esc_html_e( 'Domain Name', 'easy-wp-smtp' ); ?></label>
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
						/* translators: %s - Domain Name link. */
						esc_html__( 'Follow this link to get a Domain Name from Mailgun: %s.', 'easy-wp-smtp' ),
						'<a href="https://app.mailgun.com/app/sending/domains" target="_blank" rel="noopener noreferrer">' .
						esc_html__( 'Get a Domain Name', 'easy-wp-smtp' ) .
						'</a>'
					);
					?>
				</p>
			</div>
		</div>

		<!-- Region -->
		<div id="easy-wp-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-region" class="easy-wp-smtp-row easy-wp-smtp-setting-row">
			<div class="easy-wp-smtp-setting-row__label">
				<label><?php esc_html_e( 'Region', 'easy-wp-smtp' ); ?></label>
			</div>
			<div class="easy-wp-smtp-setting-row__field">
				<div class="easy-wp-smtp-radio-group">
					<label class="easy-wp-smtp-radio"
								 for="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-region-us">
						<input type="radio" id="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-region-us"
									 name="easy-wp-smtp[<?php echo esc_attr( $this->get_slug() ); ?>][region]" value="US"
									 <?php echo $this->connection_options->is_const_defined( $this->get_slug(), 'region' ) ? 'disabled' : ''; ?>
									 <?php checked( 'US', $this->connection_options->get( $this->get_slug(), 'region' ) ); ?>
						/>
						<span class="easy-wp-smtp-radio__checkmark"></span>
						<span class="easy-wp-smtp-radio__label"><?php esc_html_e( 'US', 'easy-wp-smtp' ); ?></span>
					</label>

					<label class="easy-wp-smtp-radio"
								 for="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-region-eu">
						<input type="radio" id="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-region-eu"
									 name="easy-wp-smtp[<?php echo esc_attr( $this->get_slug() ); ?>][region]" value="EU"
									 <?php echo $this->connection_options->is_const_defined( $this->get_slug(), 'region' ) ? 'disabled' : ''; ?>
									 <?php checked( 'EU', $this->connection_options->get( $this->get_slug(), 'region' ) ); ?>
						/>
						<span class="easy-wp-smtp-radio__checkmark"></span>
						<span class="easy-wp-smtp-radio__label"><?php esc_html_e( 'EU', 'easy-wp-smtp' ); ?></span>
					</label>
				</div>

				<p class="desc">
					<?php esc_html_e( 'Define which endpoint you want to use for sending messages.', 'easy-wp-smtp' ); ?><br>
					<?php esc_html_e( 'If you are operating under EU laws, you may be required to use EU region.', 'easy-wp-smtp' ); ?>
					<?php
					printf(
						wp_kses(
							/* translators: %s - URL to Mailgun.com page. */
							__( '<a href="%s" rel="" target="_blank">More information</a> on Mailgun.com.', 'easy-wp-smtp' ),
							array(
								'a' => array(
									'href'   => array(),
									'rel'    => array(),
									'target' => array(),
								),
							)
						),
						'https://www.mailgun.com/regions'
					);
					?>
				</p>
			</div>
		</div>

		<?php
	}
}
