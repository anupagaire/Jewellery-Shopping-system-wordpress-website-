<?php

namespace EasyWPSMTP\Providers\SMTPcom;

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
	const SLUG = 'smtpcom';

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

		$allowed_kses_html = array(
			'strong' => array(),
			'p'      => array(),
			'a'      => array(
				'href'   => array(),
				'rel'    => array(),
				'target' => array(),
			),
		);

		$description  = sprintf(
			wp_kses( /* translators: %s - URL to smtp.com site. */
				__( '<p><a href="%s" target="_blank" rel="noopener noreferrer">SMTP.com</a> is a popular transactional email provider. It’s been providing reliable email services for over 2 decades, and is a trusted brand for more than 100,000 businesses. You can try it for free for up to 30 days and send up to 50,000 emails. </p>', 'easy-wp-smtp' ),
				$allowed_kses_html
			),
			'https://easywpsmtp.com/go/smtp/'
		);
		$description .= sprintf(
			wp_kses( /* translators: %s - URL to easywpsmtp.com doc page for stmp.com. */
				__( '<p>To get started, <a href="%s" target="_blank" rel="noopener noreferrer">see our documentation for the SMTP.com mailer</a>.</p>', 'easy-wp-smtp' ),
				$allowed_kses_html
			),
			esc_url( easy_wp_smtp()->get_utm_url( 'https://easywpsmtp.com/docs/setting-up-the-smtp-com-mailer/', 'SMTP.com documentation' ) )
		);

		$description .= '<p class="easy-wp-smtp-tooltip">' .
			esc_html__( 'Transparency and Disclosure', 'easy-wp-smtp' ) .
			'<span class="easy-wp-smtp-tooltip-text">' .
			esc_html__( 'We believe in full transparency. The SMTP.com links above are tracking links as part of our partnership with SMTP (j2 Global). We can recommend just about any SMTP service, but we only recommend products that we believe will add value to our users.', 'easy-wp-smtp' ) .
			'</span></p>';

		parent::__construct(
			[
				'logo_url'    => easy_wp_smtp()->assets_url . '/images/providers/smtp-com.svg',
				'slug'        => self::SLUG,
				'title'       => esc_html__( 'SMTP.com', 'easy-wp-smtp' ),
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
					<?php $this->display_const_set_message( 'EASY_WP_SMTP_SMTPCOM_API_KEY' ); ?>
				<?php else : ?>
					<input type="password" spellcheck="false"
						name="easy-wp-smtp[<?php echo esc_attr( $this->get_slug() ); ?>][api_key]"
						value="<?php echo esc_attr( $this->connection_options->get( $this->get_slug(), 'api_key' ) ); ?>"
						id="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-api_key"
					/>
				<?php endif; ?>
				<p class="desc">
					<?php
					printf( /* translators: %s - API key link. */
						esc_html__( 'Follow this link to get an API Key from SMTP.com: %s.', 'easy-wp-smtp' ),
						'<a href="https://my.smtp.com/settings/api" target="_blank" rel="noopener noreferrer">' .
						esc_html__( 'Get API Key', 'easy-wp-smtp' ) .
						'</a>'
					);
					?>
				</p>
			</div>
		</div>

		<!-- Channel/Sender -->
		<div id="easy-wp-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-channel" class="easy-wp-smtp-row easy-wp-smtp-setting-row easy-wp-smtp-setting-row--text">
			<div class="easy-wp-smtp-setting-row__label">
				<label for="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-channel"><?php esc_html_e( 'Sender Name', 'easy-wp-smtp' ); ?></label>
			</div>
			<div class="easy-wp-smtp-setting-row__field">
				<input name="easy-wp-smtp[<?php echo esc_attr( $this->get_slug() ); ?>][channel]" type="text"
					value="<?php echo esc_attr( $this->connection_options->get( $this->get_slug(), 'channel' ) ); ?>"
					<?php echo $this->connection_options->is_const_defined( $this->get_slug(), 'channel' ) ? 'disabled' : ''; ?>
					id="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-channel" spellcheck="false"
				/>
				<?php
				if ( $this->connection_options->is_const_defined( $this->get_slug(), 'channel' ) ) {
					$this->display_const_set_message( 'EASY_WP_SMTP_SMTPCOM_CHANNEL' );
				}
				?>
				<p class="desc">
					<?php
					printf( /* translators: %s - Channel/Sender Name link for smtp.com documentation. */
						esc_html__( 'Follow this link to get a Sender Name from SMTP.com: %s.', 'easy-wp-smtp' ),
						'<a href="https://my.smtp.com/senders/" target="_blank" rel="noopener noreferrer">' .
						esc_html__( 'Get Sender Name', 'easy-wp-smtp' ) .
						'</a>'
					);
					?>
				</p>
			</div>
		</div>

		<?php
	}
}
