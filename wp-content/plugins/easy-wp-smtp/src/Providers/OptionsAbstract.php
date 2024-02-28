<?php

namespace EasyWPSMTP\Providers;

use EasyWPSMTP\ConnectionInterface;
use EasyWPSMTP\Options;

/**
 * Abstract Class ProviderAbstract to contain common providers functionality.
 *
 * @since 2.0.0
 */
abstract class OptionsAbstract implements OptionsInterface {

	/**
	 * The mailer provider logo URL.
	 *
	 * @since 2.0.0
	 *
	 * @var  string
	 */
	private $logo_url = '';

	/**
	 * The mailer provider slug.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	private $slug = '';

	/**
	 * The mailer provider title (or name).
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	private $title = '';

	/**
	 * The mailer provider description.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	private $description = '';

	/**
	 * Notices above mailer provider options.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	private $notices = [];

	/**
	 * Whether this mailer is recommended or not.
	 *
	 * @since 2.0.0
	 *
	 * @var bool
	 */
	private $recommended = false;

	/**
	 * Whether this mailer is disabled or not.
	 *
	 * @since 2.0.0
	 *
	 * @var bool
	 */
	private $disabled = false;

	/**
	 * Whether to display title for this mailer.
	 *
	 * @since 2.0.0
	 *
	 * @var bool
	 */
	private $show_title = false;

	/**
	 * @var string
	 */
	private $php = EasyWPSMTP_PHP_VERSION;

	/**
	 * @var Options
	 */
	protected $options;

	/**
	 * An array with mailer supported setting fields.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	protected $supports;

	/**
	 * The Connection object.
	 *
	 * @since 2.0.0
	 *
	 * @var ConnectionInterface
	 */
	protected $connection;

	/**
	 * The connection options object.
	 *
	 * @since 2.0.0
	 *
	 * @var Options
	 */
	protected $connection_options;

	/**
	 * ProviderAbstract constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param array               $params     The mailer options parameters.
	 * @param ConnectionInterface $connection The Connection object.
	 */
	public function __construct( $params, $connection = null ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded

		if ( ! is_null( $connection ) ) {
			$this->connection = $connection;
		} else {
			$this->connection = easy_wp_smtp()->get_connections_manager()->get_primary_connection();
		}

		$this->connection_options = $this->connection->get_options();

		if (
			empty( $params['slug'] ) ||
			empty( $params['title'] )
		) {
			return;
		}

		$this->slug  = sanitize_key( $params['slug'] );
		$this->title = sanitize_text_field( $params['title'] );

		if ( ! empty( $params['description'] ) ) {
			$this->description = wp_kses_post( $params['description'] );
		}

		if ( ! empty( $params['notices'] ) ) {
			foreach ( (array) $params['notices'] as $key => $notice ) {
				$key = sanitize_key( $key );
				if ( empty( $key ) ) {
					continue;
				}

				$notice = wp_kses(
					$notice,
					array(
						'p'     => true,
						'br'     => true,
						'strong' => true,
						'em'     => true,
						'a'      => array(
							'href'   => true,
							'rel'    => true,
							'target' => true,
						),
					)
				);
				if ( empty( $notice ) ) {
					continue;
				}

				$this->notices[ $key ] = $notice;
			}
		}

		if ( isset( $params['recommended'] ) ) {
			$this->recommended = (bool) $params['recommended'];
		}
		if ( isset( $params['disabled'] ) ) {
			$this->disabled = (bool) $params['disabled'];
		}

		if ( ! empty( $params['php'] ) ) {
			$this->php = sanitize_text_field( $params['php'] );
		}

		if ( ! empty( $params['logo_url'] ) ) {
			$this->logo_url = esc_url_raw( $params['logo_url'] );
		}

		$this->supports = ( ! empty( $params['supports'] ) ) ? $params['supports'] : $this->get_supports_defaults();

		$this->options = Options::init();
	}

	/**
	 * Get the mailer provider logo URL.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_logo_url() {

		return apply_filters( 'easy_wp_smtp_providers_provider_get_logo_url', $this->logo_url, $this );
	}

	/**
	 * Get the mailer provider slug.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_slug() {

		return apply_filters( 'easy_wp_smtp_providers_provider_get_slug', $this->slug, $this );
	}

	/**
	 * Get the mailer provider title (or name).
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_title() {

		return apply_filters( 'easy_wp_smtp_providers_provider_get_title', $this->title, $this );
	}

	/**
	 * Get the mailer provider description.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_description() {

		return apply_filters( 'easy_wp_smtp_providers_provider_get_description', $this->description, $this );
	}

	/**
	 * Some mailers may display a notice above its options.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	public function get_notice( $type ) {

		$type = sanitize_key( $type );

		return apply_filters( 'easy_wp_smtp_providers_provider_get_notice', isset( $this->notices[ $type ] ) ? $this->notices[ $type ] : '', $this );
	}

	/**
	 * Get the mailer provider minimum PHP version.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_php_version() {

		return apply_filters( 'easy_wp_smtp_providers_provider_get_php_version', $this->php, $this );
	}

	/**
	 * Output the mailer provider options.
	 *
	 * @since 2.0.0
	 */
	public function display_options() {
		?>

		<!-- SMTP Host -->
		<div id="easy-wp-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-host" class="easy-wp-smtp-row easy-wp-smtp-setting-row easy-wp-smtp-setting-row--text">
			<div class="easy-wp-smtp-setting-row__label">
				<label for="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-host"><?php esc_html_e( 'SMTP Host', 'easy-wp-smtp' ); ?></label>
			</div>
			<div class="easy-wp-smtp-setting-row__field">
				<input name="easy-wp-smtp[<?php echo esc_attr( $this->get_slug() ); ?>][host]" type="text"
					value="<?php echo esc_attr( $this->connection_options->get( $this->get_slug(), 'host' ) ); ?>"
					<?php echo $this->connection_options->is_const_defined( $this->get_slug(), 'host' ) ? 'disabled' : ''; ?>
					id="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-host" spellcheck="false"
				/>
				<p class="desc">
					<?php esc_html_e( 'Your mail server\'s address.', 'easy-wp-smtp' ); ?>
				</p>
			</div>
		</div>

		<!-- SMTP Encryption -->
		<div id="easy-wp-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-encryption" class="easy-wp-smtp-row easy-wp-smtp-setting-row">
			<div class="easy-wp-smtp-setting-row__label">
				<label><?php esc_html_e( 'Type of Encryption', 'easy-wp-smtp' ); ?></label>
			</div>
			<div class="easy-wp-smtp-setting-row__field">
				<div class="easy-wp-smtp-radio-group">
					<label class="easy-wp-smtp-radio" for="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-enc-none">
						<input type="radio" id="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-enc-none"
									 name="easy-wp-smtp[<?php echo esc_attr( $this->get_slug() ); ?>][encryption]" value="none"
									 <?php echo $this->connection_options->is_const_defined( $this->get_slug(), 'encryption' ) ? 'disabled' : ''; ?>
									 <?php checked( 'none', $this->connection_options->get( $this->get_slug(), 'encryption' ) ); ?>
						/>
						<span class="easy-wp-smtp-radio__checkmark"></span>
						<span class="easy-wp-smtp-radio__label"><?php esc_html_e( 'None', 'easy-wp-smtp' ); ?></span>
					</label>

					<label class="easy-wp-smtp-radio" for="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-enc-ssl">
						<input type="radio" id="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-enc-ssl"
									 name="easy-wp-smtp[<?php echo esc_attr( $this->get_slug() ); ?>][encryption]" value="ssl"
									 <?php echo $this->connection_options->is_const_defined( $this->get_slug(), 'encryption' ) ? 'disabled' : ''; ?>
									 <?php checked( 'ssl', $this->connection_options->get( $this->get_slug(), 'encryption' ) ); ?>
						/>
						<span class="easy-wp-smtp-radio__checkmark"></span>
						<span class="easy-wp-smtp-radio__label"><?php esc_html_e( 'SSL', 'easy-wp-smtp' ); ?></span>
					</label>

					<label class="easy-wp-smtp-radio" for="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-enc-tls">
						<input type="radio" id="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-enc-tls"
									 name="easy-wp-smtp[<?php echo esc_attr( $this->get_slug() ); ?>][encryption]" value="tls"
									 <?php echo $this->connection_options->is_const_defined( $this->get_slug(), 'encryption' ) ? 'disabled' : ''; ?>
									 <?php checked( 'tls', $this->connection_options->get( $this->get_slug(), 'encryption' ) ); ?>
						/>
						<span class="easy-wp-smtp-radio__checkmark"></span>
						<span class="easy-wp-smtp-radio__label"><?php esc_html_e( 'TLS', 'easy-wp-smtp' ); ?></span>
					</label>
				</div>

				<p class="desc">
					<?php esc_html_e( 'If your SMTP provider offers both SSL and TLS encryption, we recommend using TLS. For most servers, this is the more secure option.', 'easy-wp-smtp' ); ?>
				</p>
			</div>
		</div>

		<!-- SMTP Port -->
		<div id="easy-wp-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-port" class="easy-wp-smtp-row easy-wp-smtp-setting-row easy-wp-smtp-setting-row--text">
			<div class="easy-wp-smtp-setting-row__label">
				<label for="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-port"><?php esc_html_e( 'SMTP Port', 'easy-wp-smtp' ); ?></label>
			</div>
			<div class="easy-wp-smtp-setting-row__field">
				<input name="easy-wp-smtp[<?php echo esc_attr( $this->get_slug() ); ?>][port]" type="number"
							 value="<?php echo esc_attr( $this->connection_options->get( $this->get_slug(), 'port' ) ); ?>"
					<?php echo $this->connection_options->is_const_defined( $this->get_slug(), 'port' ) ? 'disabled' : ''; ?>
							 id="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-port" class="small-text" spellcheck="false"
				/>
				<p class="desc">
					<?php esc_html_e( 'The port to your mail server.', 'easy-wp-smtp' ); ?>
				</p>
			</div>
		</div>

		<!-- PHPMailer SMTPAutoTLS -->
		<div id="easy-wp-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-autotls" class="easy-wp-smtp-row easy-wp-smtp-setting-row <?php echo $this->connection_options->is_const_defined( $this->get_slug(), 'encryption' ) || 'tls' === $this->connection_options->get( $this->get_slug(), 'encryption' ) ? 'easy-wp-smtp-hidden' : ''; ?>">
			<div class="easy-wp-smtp-setting-row__label">
				<label for="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-autotls"><?php esc_html_e( 'Auto TLS', 'easy-wp-smtp' ); ?></label>
			</div>
			<div class="easy-wp-smtp-setting-row__field">
				<label class="easy-wp-smtp-toggle" for="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-autotls">
					<input type="checkbox" id="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-autotls"
						name="easy-wp-smtp[<?php echo esc_attr( $this->get_slug() ); ?>][autotls]" value="yes"
						<?php echo $this->connection_options->is_const_defined( $this->get_slug(), 'autotls' ) ? 'disabled' : ''; ?>
						<?php checked( true, (bool) $this->connection_options->get( $this->get_slug(), 'autotls' ) ); ?>
					/>
					<span class="easy-wp-smtp-toggle__switch"></span>
					<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--checked"><?php esc_html_e( 'On', 'easy-wp-smtp' ); ?></span>
					<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--unchecked"><?php esc_html_e( 'Off', 'easy-wp-smtp' ); ?></span>
				</label>
				<p class="desc">
					<?php esc_html_e( 'By default, TLS encryption is automatically used if the server supports it (recommended). In some cases, due to server misconfigurations, this can cause issues and may need to be disabled.', 'easy-wp-smtp' ); ?>
				</p>
			</div>
		</div>

		<!-- SMTP Authentication -->
		<div id="easy-wp-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-auth" class="easy-wp-smtp-row easy-wp-smtp-setting-row">
			<div class="easy-wp-smtp-setting-row__label">
				<label for="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-auth"><?php esc_html_e( 'SMTP Authentication', 'easy-wp-smtp' ); ?></label>
			</div>
			<div class="easy-wp-smtp-setting-row__field">
				<label class="easy-wp-smtp-toggle" for="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-auth">
					<input type="checkbox" id="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-auth"
						name="easy-wp-smtp[<?php echo esc_attr( $this->get_slug() ); ?>][auth]" value="yes"
						<?php echo $this->connection_options->is_const_defined( $this->get_slug(), 'auth' ) ? 'disabled' : ''; ?>
						<?php checked( true, (bool) $this->connection_options->get( $this->get_slug(), 'auth' ) ); ?>
					/>
					<span class="easy-wp-smtp-toggle__switch"></span>
					<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--checked"><?php esc_html_e( 'On', 'easy-wp-smtp' ); ?></span>
					<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--unchecked"><?php esc_html_e( 'Off', 'easy-wp-smtp' ); ?></span>
				</label>
				<p class="desc">
					<?php esc_html_e( 'Enable mail server authentication. This option should be enabled in most cases.', 'easy-wp-smtp' ); ?>
				</p>
			</div>
		</div>

		<!-- SMTP Username -->
		<div id="easy-wp-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-user" class="easy-wp-smtp-row easy-wp-smtp-setting-row easy-wp-smtp-setting-row--text<?php echo ! $this->connection_options->is_const_defined( $this->get_slug(), 'auth' ) && ! $this->connection_options->get( $this->get_slug(), 'auth' ) ? ' easy-wp-smtp-hidden' : ''; ?>">
			<div class="easy-wp-smtp-setting-row__label">
				<label for="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-user"><?php esc_html_e( 'SMTP Username', 'easy-wp-smtp' ); ?></label>
			</div>
			<div class="easy-wp-smtp-setting-row__field">
				<input name="easy-wp-smtp[<?php echo esc_attr( $this->get_slug() ); ?>][user]" type="text"
					value="<?php echo esc_attr( $this->connection_options->get( $this->get_slug(), 'user' ) ); ?>"
					<?php echo $this->connection_options->is_const_defined( $this->get_slug(), 'user' ) ? 'disabled' : ''; ?>
					id="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-user" spellcheck="false" autocomplete="new-password"
				/>
				<p class="desc">
					<?php esc_html_e( 'The username to log in to your mail server.', 'easy-wp-smtp' ); ?>
				</p>
			</div>
		</div>

		<!-- SMTP Password -->
		<div id="easy-wp-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-pass" class="easy-wp-smtp-row easy-wp-smtp-setting-row easy-wp-smtp-setting-row--text<?php echo ! $this->connection_options->is_const_defined( $this->get_slug(), 'auth' ) && ! $this->connection_options->get( $this->get_slug(), 'auth' ) ? ' easy-wp-smtp-hidden' : ''; ?>">
			<div class="easy-wp-smtp-setting-row__label">
				<label for="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-pass"><?php esc_html_e( 'SMTP Password', 'easy-wp-smtp' ); ?></label>
			</div>
			<div class="easy-wp-smtp-setting-row__field">
				<?php if ( $this->connection_options->is_const_defined( $this->get_slug(), 'pass' ) ) : ?>
					<input type="text" value="*************" disabled id="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-pass"/>

					<?php $this->display_const_set_message( 'EASY_WP_SMTP_SMTP_PASS' ); ?>

					<p class="desc">
						<?php
						printf(
							/* translators: %s - constant name: EASY_WP_SMTP_SMTP_PASS. */
							esc_html__( 'To change the password you need to change the value of the constant there: %s', 'easy-wp-smtp' ),
							'<code>define( \'EASY_WP_SMTP_SMTP_PASS\', \'your_old_password\' );</code>'
						);
						?>
						<br>
						<?php
						printf(
							/* translators: %1$s - wp-config.php file, %2$s - EASY_WP_SMTP_ON constant name. */
							esc_html__( 'If you want to disable the use of constants, find in %1$s file the constant %2$s and turn if off:', 'easy-wp-smtp' ),
							'<code>wp-config.php</code>',
							'<code>EASY_WP_SMTP_ON</code>'
						);
						?>
					</p>
					<pre>
						define( 'EASY_WP_SMTP_ON', false );
					</pre>
					<p class="desc">
						<?php esc_html_e( 'All the defined constants will stop working and you will be able to change all the values on this page.', 'easy-wp-smtp' ); ?>
					</p>
				<?php else : ?>
					<input name="easy-wp-smtp[<?php echo esc_attr( $this->get_slug() ); ?>][pass]" type="password"
						value="<?php echo esc_attr( $this->connection_options->get( $this->get_slug(), 'pass' ) ); ?>"
						id="easy-wp-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-pass" spellcheck="false" autocomplete="new-password"
					/>
					<p class="desc">
						<?php esc_html_e( 'The password to log in to your mail server. The password will be encrypted in the database.', 'easy-wp-smtp' ); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>

		<?php
	}

	/**
	 * Whether this mailer is recommended or not.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_recommended() {

		return (bool) apply_filters( 'easy_wp_smtp_providers_provider_is_recommended', $this->recommended, $this );
	}

	/**
	 * Whether this mailer is disabled or not.
	 * Used for displaying Pro mailers inside Lite plugin.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_disabled() {

		return (bool) apply_filters( 'easy_wp_smtp_providers_provider_is_disabled', $this->disabled, $this );
	}

	/**
	 * Check whether we can use this provider based on the PHP version.
	 * Valid for those, that use SDK.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_php_correct() {

		return version_compare( phpversion(), $this->php, '>=' );
	}

	/**
	 * Display a helpful message to those users, that are using an outdated version of PHP,
	 * which is not supported by the currently selected Provider.
	 *
	 * @since 2.0.0
	 */
	protected function display_php_warning() {
		?>

		<blockquote>
			<p>
				<?php
				printf(
				/* translators: %1$s - Provider name; %2$s - PHP version required by Provider; %3$s - current PHP version. */
					esc_html__( '%1$s requires PHP %2$s to work and does not support your current PHP version %3$s. Please contact your host and request a PHP upgrade to the latest one.', 'easy-wp-smtp' ),
					esc_html( $this->get_title() ),
					esc_html( $this->php ),
					esc_html( phpversion() )
				);
				?>
			</p>
			<p>
				<?php esc_html_e( 'Meanwhile you can switch to some other mailers.', 'easy-wp-smtp' ); ?>
			</p>
		</blockquote>

		<?php
	}

	/**
	 * Display a helpful message to those users, that don't have SSL certificate.
	 *
	 * @since 2.0.0
	 */
	protected function display_ssl_warning() {
		?>

		<blockquote>
			<p>
				<?php
				printf(
					wp_kses( /* translators: %s - Provider name */
						__( '%s requires an SSL certificate, and so is not currently compatible with your site. Please contact your host to request a SSL certificate, or check out <a href="https://www.wpbeginner.com/wp-tutorials/how-to-add-ssl-and-https-in-wordpress/" target="_blank">WPBeginner\'s tutorial on how to set up SSL</a>.', 'easy-wp-smtp' ),
						[
							'a' => [
								'href'   => [],
								'target' => [],
							],
						]
					),
					esc_html( $this->get_title() )
				);
				?>
			</p>
			<p>
				<?php esc_html_e( 'If you\'d prefer not to set up SSL, or need an SMTP solution in the meantime, please select a different mailer option.', 'easy-wp-smtp' ); ?>
			</p>
		</blockquote>

		<?php
	}

	/**
	 * Display a message of a constant that was set inside wp-config.php file.
	 *
	 * @since 2.0.0
	 *
	 * @param string $constant Constant name.
	 */
	protected function display_const_set_message( $constant ) {

		printf( '<p class="desc">%s</p>', $this->options->get_const_set_message( $constant ) ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Return the defaults for the mailer supported settings.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_supports_defaults() {

		return [
			'from_email'       => true,
			'from_name'        => true,
			'from_email_force' => true,
			'from_name_force'  => true,
		];
	}

	/**
	 * Get the mailer supported settings.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_supports() {

		return apply_filters( 'easy_wp_smtp_providers_provider_get_supports', $this->supports, $this );
	}
}
