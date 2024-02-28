<?php

namespace EasyWPSMTP\Providers\Sendlayer;

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
	 *
	 * @var string
	 */
	const SLUG = 'sendlayer';

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
			wp_kses(
			/* translators: %1$s - URL to sendlayer.com; %2$s - URL to SendLayer documentation on easywpsmtp.com. */
				__( '<p><strong><a href="%1$s" target="_blank" rel="noopener noreferrer">SendLayer</a> is our #1 recommended mailer.</strong> It offers affordable pricing and is easy to set up, which makes it an excellent option for WordPress sites. With SendLayer, your domain will be authenticated so all your outgoing emails reach your customers’ inboxes. Detailed documentation will walk you through the entire process, start to finish. When you sign up for a free trial, you can send your first emails at no charge.</p><p>To get started, <a href="%2$s" target="_blank" rel="noopener noreferrer">see our documentation for the SendLayer mailer</a>.</p>', 'easy-wp-smtp' ),
				[
					'strong' => [],
					'p'      => [],
					'a'      => [
						'href'   => [],
						'rel'    => [],
						'target' => [],
					],
				]
			),
			// phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound, WordPress.Security.NonceVerification.Recommended
			esc_url( easy_wp_smtp()->get_utm_url( 'https://sendlayer.com/easy-wp-smtp/', [ 'source' => 'easywpsmtpplugin', 'medium' => 'WordPress', 'content' => isset( $_GET['page'] ) && $_GET['page'] === 'easy-wp-smtp-setup-wizard' ? 'Setup Wizard - Mailer Description' : 'Plugin Settings - Mailer Description' ] ) ),
			// phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			esc_url( easy_wp_smtp()->get_utm_url( 'https://easywpsmtp.com/docs/setting-up-the-sendlayer-mailer/', 'SendLayer Documentation' ) )
		);

		parent::__construct(
			[
				'logo_url'    => easy_wp_smtp()->assets_url . '/images/providers/sendlayer.svg',
				'slug'        => self::SLUG,
				'title'       => esc_html__( 'SendLayer', 'easy-wp-smtp' ),
				'description' => $description,
				'recommended' => true,
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

		// phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound, WordPress.Security.NonceVerification.Recommended
		$get_api_key_url = easy_wp_smtp()->get_utm_url( 'https://app.sendlayer.com/settings/api/', [ 'source' => 'easywpsmtpplugin', 'medium' => 'WordPress', 'content' => 'Plugin Settings - Get API Key' ] );
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
					<?php $this->display_const_set_message( 'EASY_WP_SMTP_SENDLAYER_API_KEY' ); ?>
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
						esc_html__( 'Follow this link to get an API Key from SendLayer: %s.', 'easy-wp-smtp' ),
						'<a href="' . esc_url( $get_api_key_url ) . '" target="_blank" rel="noopener noreferrer">' .
						esc_html__( 'Get API Key', 'easy-wp-smtp' ) .
						'</a>'
					);
					?>
				</p>
			</div>
		</div>
		<?php
	}
}
