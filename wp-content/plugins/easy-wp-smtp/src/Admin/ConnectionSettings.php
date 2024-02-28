<?php

namespace EasyWPSMTP\Admin;

use EasyWPSMTP\ConnectionInterface;
use EasyWPSMTP\Debug;
use EasyWPSMTP\Options;

/**
 * Class ConnectionSettings.
 *
 * @since 2.0.0
 */
class ConnectionSettings {

	/**
	 * The Connection object.
	 *
	 * @since 2.0.0
	 *
	 * @var ConnectionInterface
	 */
	private $connection;

	/**
	 * After process scroll to anchor.
	 *
	 * @since 2.0.0
	 *
	 * @var false|string
	 */
	private $scroll_to = false;

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param ConnectionInterface $connection The Connection object.
	 */
	public function __construct( $connection ) {

		$this->connection = $connection;
	}

	/**
	 * Display connection settings.
	 *
	 * @since 2.0.0
	 */
	public function display() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded, Generic.Metrics.NestingLevel.MaxExceeded

		$mailer             = $this->connection->get_mailer_slug();
		$connection_options = $this->connection->get_options();

		$disabled_email = in_array( $mailer, [], true ) ? 'disabled' : '';
		$disabled_name  = in_array( $mailer, [ 'outlook' ], true ) ? 'disabled' : '';

		if ( empty( $mailer ) || ! in_array( $mailer, Options::$mailers, true ) ) {
			$mailer = 'mail';
		}

		$mailer_supported_settings = easy_wp_smtp()->get_providers()->get_options( $mailer )->get_supports();
		?>
		<div class="easy-wp-smtp-meta-box">
			<div class="easy-wp-smtp-meta-box__header">
				<div class="easy-wp-smtp-meta-box__heading">
					<?php esc_html_e( 'Mailer Settings', 'easy-wp-smtp' ); ?>
				</div>
			</div>
			<div class="easy-wp-smtp-meta-box__content">
				<!-- Mailer -->
				<div id="easy-wp-smtp-setting-row-mailer" class="easy-wp-smtp-row">
					<div class="easy-wp-smtp-row__desc">
						<p>
							<?php esc_html_e( 'Choose a mailer or use an SMTP server.', 'easy-wp-smtp' ); ?>

							<?php if ( ! is_network_admin() ) : ?>
								<?php
								printf(
									wp_kses( /* translators: %s - URL to Setup Wizard. */
										__( 'If you’d like a guided setup, run through our <a href="%s">Setup Wizard</a>.', 'easy-wp-smtp' ),
										[
											'a' => [
												'href'   => [],
												'rel'    => [],
												'target' => [],
											],
										]
									),
									esc_url( SetupWizard::get_site_url() )
								);
								?>
							<?php endif; ?>
						</p>
					</div>
					<div class="easy-wp-smtp-mailers-picker">
						<?php foreach ( easy_wp_smtp()->get_providers()->get_options_all( $this->connection ) as $provider ) : ?>

							<div class="easy-wp-smtp-mailers-picker__item">
								<?php if ( $provider->is_disabled() ) : ?>
									<input type="radio" name="easy-wp-smtp[mail][mailer]" disabled
												 class="easy-wp-smtp-mailers-picker__input easy-wp-smtp-educate"
												 id="easy-wp-smtp-setting-mailer-<?php echo esc_attr( $provider->get_slug() ); ?>"
												 value="<?php echo esc_attr( $provider->get_slug() ); ?>"
												 data-title="<?php echo esc_attr( $provider->get_title() ); ?>"
									/>
								<?php else : ?>
									<input id="easy-wp-smtp-setting-mailer-<?php echo esc_attr( $provider->get_slug() ); ?>"
												 type="radio" name="easy-wp-smtp[mail][mailer]"
												 value="<?php echo esc_attr( $provider->get_slug() ); ?>"
												 class="easy-wp-smtp-mailers-picker__input"
												 <?php checked( $provider->get_slug(), $mailer ); ?>
												 <?php disabled( $connection_options->is_const_defined( 'mail', 'mailer' ) ); ?>
									/>
								<?php endif; ?>
								<label for="easy-wp-smtp-setting-mailer-<?php echo esc_attr( $provider->get_slug() ); ?>" class="easy-wp-smtp-mailers-picker__mailer <?php echo 'easy-wp-smtp-mailers-picker__mailer--' . esc_attr( $provider->get_slug() ); ?><?php echo $provider->is_recommended() ? ' easy-wp-smtp-mailers-picker__mailer--recommended' : ''; ?><?php echo $provider->is_disabled() ? ' easy-wp-smtp-mailers-picker__mailer--disabled' : ''; ?>"<?php echo $provider->is_recommended() ? ' data-recommended-text="' . esc_html__( 'Recommended', 'easy-wp-smtp' ) . '"' : ''; ?><?php echo $provider->is_disabled() ? ' data-disabled-text="' . esc_html__( 'Pro', 'easy-wp-smtp' ) . '"' : ''; ?>>
									<span class="easy-wp-smtp-mailers-picker__image">
										<img src="<?php echo esc_url( $provider->get_logo_url() ); ?>"
												 alt="<?php echo esc_attr( $provider->get_title() ); ?>">
									</span>

									<?php if ( in_array( $provider->get_slug(), [ 'mail', 'smtp' ], true ) ) : ?>
										<span class="easy-wp-smtp-mailers-picker__title">
											<?php echo esc_html( $provider->get_title() ); ?>
										</span>
									<?php endif; ?>
								</label>
							</div>
						<?php endforeach; ?>
						<div class="easy-wp-smtp-mailers-picker__item easy-wp-smtp-mailers-picker__item--double">
							<div class="easy-wp-smtp-mailers-picker__suggest-mailer">
								<?php esc_html_e( 'Don\'t see what you\'re looking for?', 'easy-wp-smtp' ); ?>
								<?php
								printf(
									'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
									esc_url( easy_wp_smtp()->get_utm_url( 'https://easywpsmtp.com/suggest-a-mailer/', 'Suggest a Mailer' ) ),
									esc_html__( 'Suggest a Mailer', 'easy-wp-smtp' )
								);
								?>
							</div>
						</div>
					</div>
				</div>

				<!-- Mailer Options -->
				<?php foreach ( easy_wp_smtp()->get_providers()->get_options_all( $this->connection ) as $provider ) : ?>
					<?php $provider_desc = $provider->get_description(); ?>
					<div class="easy-wp-smtp-mailer-options easy-wp-smtp-mailer-options--<?php echo $mailer === $provider->get_slug() ? 'active' : 'hidden'; ?>" data-mailer="<?php echo esc_attr( $provider->get_slug() ); ?>">

						<?php if ( ! $provider->is_disabled() ) : ?>
							<!-- Mailer Title/Notice/Description -->
							<div class="easy-wp-smtp-row">
								<div class="easy-wp-smtp-row__heading">
									<?php echo esc_html( $provider->get_title() ); ?>
								</div>
								<?php
								$provider_edu_notice = $provider->get_notice( 'educational' );
								$is_dismissed        = (bool) get_user_meta( get_current_user_id(), "easy_wp_smtp_notice_educational_for_{$provider->get_slug()}_dismissed", true );

								if ( ! empty( $provider_edu_notice ) && ! $is_dismissed ) :
									?>
									<div class="easy-wp-smtp-notice easy-wp-smtp-notice--info easy-wp-smtp-notice--dismissible"
										 data-notice="educational"
										 data-mailer="<?php echo esc_attr( $provider->get_slug() ); ?>"
										 style="margin: 25px 0 25px 0;">
										<a href="#" title="<?php esc_attr_e( 'Dismiss this notice', 'easy-wp-smtp' ); ?>"
											 class="easy-wp-smtp-notice__dismiss js-easy-wp-smtp-mailer-notice-dismiss">
											<svg fill="none" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path d="m8 0.25c-4.2812 0-7.75 3.4688-7.75 7.75 0 4.2812 3.4688 7.75 7.75 7.75 4.2812 0 7.75-3.4688 7.75-7.75 0-4.2812-3.4688-7.75-7.75-7.75zm0 14c-3.4688 0-6.25-2.7812-6.25-6.25 0-3.4375 2.7812-6.25 6.25-6.25 3.4375 0 6.25 2.8125 6.25 6.25 0 3.4688-2.8125 6.25-6.25 6.25zm3.1562-8.1875c0.1563-0.125 0.1563-0.375 0-0.53125l-0.6874-0.6875c-0.1563-0.15625-0.4063-0.15625-0.5313 0l-1.9375 1.9375-1.9688-1.9375c-0.125-0.15625-0.375-0.15625-0.53125 0l-0.6875 0.6875c-0.15625 0.15625-0.15625 0.40625 0 0.53125l1.9375 1.9375-1.9375 1.9688c-0.15625 0.12505-0.15625 0.37505 0 0.53125l0.6875 0.6875c0.15625 0.1563 0.40625 0.1563 0.53125 0l1.9688-1.9375 1.9375 1.9375c0.125 0.1563 0.375 0.1563 0.5313 0l0.6874-0.6875c0.1563-0.1562 0.1563-0.4062 0-0.53125l-1.9374-1.9688 1.9374-1.9375z" fill="currentColor"/></svg>
										</a>

										<?php echo wp_kses_post( $provider_edu_notice ); ?>
									</div>
								<?php endif; ?>

								<?php if ( ! empty( $provider_desc ) ) : ?>
									<div class="easy-wp-smtp-row__desc">
										<?php echo wp_kses_post( $provider_desc ); ?>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php $provider->display_options(); ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="easy-wp-smtp-meta-box">
			<div class="easy-wp-smtp-meta-box__header">
				<div class="easy-wp-smtp-meta-box__heading">
					<?php esc_html_e( 'General Settings', 'easy-wp-smtp' ); ?>
				</div>
			</div>
			<div class="easy-wp-smtp-meta-box__content">

				<!-- From Email -->
				<div id="easy-wp-smtp-setting-row-from_email" class="easy-wp-smtp-row easy-wp-smtp-setting-row easy-wp-smtp-setting-row--text">
					<div class="easy-wp-smtp-setting-row__label">
						<label for="easy-wp-smtp-setting-from_email"><?php esc_html_e( 'From Email Address', 'easy-wp-smtp' ); ?></label>
					</div>
					<div class="easy-wp-smtp-setting-row__field">
						<div class="easy-wp-smtp-setting-row__sub-row js-easy-wp-smtp-setting-from_email" style="display: <?php echo empty( $mailer_supported_settings['from_email'] ) ? 'none' : 'block'; ?>;">
							<input name="easy-wp-smtp[mail][from_email]" type="email"
										 value="<?php echo esc_attr( $connection_options->get( 'mail', 'from_email' ) ); ?>"
										 id="easy-wp-smtp-setting-from_email" spellcheck="false"
										 placeholder="<?php echo esc_attr( easy_wp_smtp()->get_processor()->get_default_email() ); ?>"
										 <?php disabled( $connection_options->is_const_defined( 'mail', 'from_email' ) || ! empty( $disabled_email ) ); ?>
							/>
							<p class="desc">
								<?php esc_html_e( 'The email address that emails are sent from.', 'easy-wp-smtp' ); ?>
							</p>
							<p class="desc">
								<?php esc_html_e( 'Please note that other plugins can change this. Enable the Force From Email setting below to prevent them from doing so.', 'easy-wp-smtp' ); ?>
							</p>
						</div>

						<div class="easy-wp-smtp-setting-row__sub-row js-easy-wp-smtp-setting-from_email_force" style="display: <?php echo empty( $mailer_supported_settings['from_email_force'] ) ? 'none' : 'block'; ?>;">
							<label for="easy-wp-smtp-setting-from_email_force" class="easy-wp-smtp-toggle">
								<input name="easy-wp-smtp[mail][from_email_force]" type="checkbox"
											 value="true" id="easy-wp-smtp-setting-from_email_force"
											 <?php checked( true, (bool) $connection_options->get( 'mail', 'from_email_force' ) ); ?>
											 <?php disabled( $connection_options->is_const_defined( 'mail', 'from_email_force' ) || ! empty( $disabled_email ) ); ?>
								/>
								<span class="easy-wp-smtp-toggle__switch"></span>
								<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--static">
									<?php esc_html_e( 'Force From Email', 'easy-wp-smtp' ); ?>
								</span>
							</label>
							<p class="desc">
								<?php esc_html_e( 'If enabled, your specified From Email Address will be used for all outgoing emails, regardless of values set by other plugins.', 'easy-wp-smtp' ); ?>
							</p>
						</div>
					</div>
				</div>

				<!-- From Name -->
				<div id="easy-wp-smtp-setting-row-from_name" class="easy-wp-smtp-row easy-wp-smtp-setting-row easy-wp-smtp-setting-row--text">
					<div class="easy-wp-smtp-setting-row__label">
						<label for="easy-wp-smtp-setting-from_name"><?php esc_html_e( 'From Name', 'easy-wp-smtp' ); ?></label>
					</div>
					<div class="easy-wp-smtp-setting-row__field">
						<div class="easy-wp-smtp-setting-row__sub-row js-easy-wp-smtp-setting-from_name" style="display: <?php echo empty( $mailer_supported_settings['from_name'] ) ? 'none' : 'block'; ?>;">
							<input name="easy-wp-smtp[mail][from_name]" type="text"
										 value="<?php echo esc_attr( $connection_options->get( 'mail', 'from_name' ) ); ?>"
										 id="easy-wp-smtp-setting-from_name" spellcheck="false"
										 placeholder="<?php echo esc_attr( easy_wp_smtp()->get_processor()->get_default_name() ); ?>"
										 <?php disabled( $connection_options->is_const_defined( 'mail', 'from_name' ) || ! empty( $disabled_name ) ); ?>
							/>

							<?php if ( empty( $disabled_name ) ) : ?>
								<p class="desc">
									<?php esc_html_e( 'The name that emails are sent from.', 'easy-wp-smtp' ); ?>
								</p>
							<?php endif; ?>
						</div>

						<div class="easy-wp-smtp-setting-row__sub-row js-easy-wp-smtp-setting-from_name_force" style="display: <?php echo empty( $mailer_supported_settings['from_name_force'] ) ? 'none' : 'block'; ?>;">
							<label for="easy-wp-smtp-setting-from_name_force" class="easy-wp-smtp-toggle">
								<input name="easy-wp-smtp[mail][from_name_force]" type="checkbox"
											 value="true" id="easy-wp-smtp-setting-from_name_force"
											 <?php checked( true, (bool) $connection_options->get( 'mail', 'from_name_force' ) ); ?>
											 <?php disabled( $connection_options->is_const_defined( 'mail', 'from_name_force' ) || ! empty( $disabled_name ) ); ?>
								/>
								<span class="easy-wp-smtp-toggle__switch"></span>
								<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--static">
									<?php esc_html_e( 'Force From Name Replacement', 'easy-wp-smtp' ); ?>
								</span>
							</label>

							<?php if ( ! empty( $disabled_name ) ) : ?>
								<p class="desc">
									<?php esc_html_e( 'Current provider doesn\'t support setting and forcing From Name. Emails will be sent on behalf of the account name used to setup the OAuth connection below.', 'easy-wp-smtp' ); ?>
								</p>
							<?php else : ?>
								<p class="desc">
									<?php esc_html_e( 'If enabled, your specified From Name will be used for all outgoing emails, regardless of values set by other plugins.', 'easy-wp-smtp' ); ?>
								</p>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<!-- Advanced options -->
				<div id="easy-wp-smtp-setting-row-advanced" class="easy-wp-smtp-row easy-wp-smtp-setting-row">
					<div class="easy-wp-smtp-setting-row__label">
						<label for="easy-wp-smtp-setting-advanced">
							<?php esc_html_e( 'Advanced Settings', 'easy-wp-smtp' ); ?>
						</label>
					</div>
					<div class="easy-wp-smtp-setting-row__field">
						<label class="easy-wp-smtp-toggle" for="easy-wp-smtp-setting-advanced">
							<input name="easy-wp-smtp[mail][advanced]" type="checkbox"
										 value="true" <?php checked( true, $connection_options->get( 'mail', 'advanced' ) ); ?>
										 id="easy-wp-smtp-setting-advanced"
							/>
							<span class="easy-wp-smtp-toggle__switch"></span>
							<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--checked"><?php esc_html_e( 'Show', 'easy-wp-smtp' ); ?></span>
							<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--unchecked"><?php esc_html_e( 'Hide', 'easy-wp-smtp' ); ?></span>
						</label>
					</div>
				</div>

				<!-- Reply-To Email Address -->
				<div class="easy-wp-smtp-row easy-wp-smtp-setting-row easy-wp-smtp-setting-row--text<?php echo ! $connection_options->get( 'mail', 'advanced' ) ? ' easy-wp-smtp-hidden' : ''; ?>">
					<div class="easy-wp-smtp-setting-row__label">
						<label for="easy-wp-smtp-setting-reply_to_email">
							<?php esc_html_e( 'Reply-To Email Address', 'easy-wp-smtp' ); ?>
						</label>
					</div>
					<div class="easy-wp-smtp-setting-row__field">
						<div class="easy-wp-smtp-setting-row__sub-row">
							<input name="easy-wp-smtp[mail][reply_to_email]" type="text"
										 value="<?php echo esc_attr( $connection_options->get( 'mail', 'reply_to_email' ) ); ?>"
										 <?php echo $connection_options->is_const_defined( 'mail', 'reply_to_email' ) ? 'disabled' : ''; ?>
										 id="easy-wp-smtp-setting-reply_to_email" spellcheck="false"
							/>
							<p class="desc">
								<?php esc_html_e( '(Optional) This email address will be used in the Reply-To field of emails sent from your site. Leave it blank to use the From Email Address as the reply-to value.', 'easy-wp-smtp' ); ?>
							</p>
						</div>
						<div class="easy-wp-smtp-setting-row__sub-row">
							<label class="easy-wp-smtp-toggle" for="easy-wp-smtp-setting-reply_to_replace_from">
								<input name="easy-wp-smtp[mail][reply_to_replace_from]" type="checkbox" value="true"
											 id="easy-wp-smtp-setting-reply_to_replace_from"
											 <?php echo $connection_options->is_const_defined( 'mail', 'reply_to_replace_from' ) ? 'disabled' : ''; ?>
											 <?php checked( true, $connection_options->get( 'mail', 'reply_to_replace_from' ) ); ?>
								/>
								<span class="easy-wp-smtp-toggle__switch"></span>
								<span class="easy-wp-smtp-toggle__label easy-wp-smtp-toggle__label--static"><?php esc_html_e( 'Substitute Mode', 'easy-wp-smtp' ); ?></span>
							</label>
							<p class="desc">
								<?php esc_html_e( 'When enabled, this setting will replace the From Email Address with the Reply-To Email Address if the From Email Address is found in the reply-to header. This can prevent conflicts with other plugins that specify their own reply-to email addresses.', 'easy-wp-smtp' ); ?>
							</p>
							<p class="desc">
								<?php esc_html_e( 'If no Reply-To Email Address has been set or if the reply-to header of an email is empty, this setting has no effect.', 'easy-wp-smtp' ); ?>
							</p>
						</div>
					</div>
				</div>

				<!-- BCC Email Address -->
				<div class="easy-wp-smtp-row easy-wp-smtp-setting-row easy-wp-smtp-setting-row--text<?php echo ! $connection_options->get( 'mail', 'advanced' ) ? ' easy-wp-smtp-hidden' : ''; ?>">
					<div class="easy-wp-smtp-setting-row__label">
						<label for="easy-wp-smtp-setting-bcc_emails">
							<?php esc_html_e( 'BCC Email Address', 'easy-wp-smtp' ); ?>
						</label>
					</div>
					<div class="easy-wp-smtp-setting-row__field">
						<input name="easy-wp-smtp[mail][bcc_emails]" type="text"
									 value="<?php echo esc_attr( $connection_options->get( 'mail', 'bcc_emails' ) ); ?>"
									 <?php echo $connection_options->is_const_defined( 'mail', 'bcc_emails' ) ? 'disabled' : ''; ?>
									 id="easy-wp-smtp-setting-bcc_emails" spellcheck="false"
						/>
						<p class="desc">
							<?php esc_html_e( '(Optional) This email address will be used in the BCC field of all outgoing emails. You can enter multiple email addresses separated by commas. Please use this setting carefully, as the email address(es) entered above will be included on every email your site sends.', 'easy-wp-smtp' ); ?>
						</p>
					</div>
				</div>

				<!-- Don't Replace "From" Field -->
				<div class="easy-wp-smtp-row easy-wp-smtp-setting-row easy-wp-smtp-setting-row--text<?php echo ! $connection_options->get( 'mail', 'advanced' ) ? ' easy-wp-smtp-hidden' : ''; ?>">
					<div class="easy-wp-smtp-setting-row__label">
						<label for="easy-wp-smtp-setting-from_email_force_exclude_emails">
							<?php esc_html_e( 'Don\'t Replace in From Field', 'easy-wp-smtp' ); ?>
						</label>
					</div>
					<div class="easy-wp-smtp-setting-row__field">
						<input name="easy-wp-smtp[mail][from_email_force_exclude_emails]" type="text"
									 value="<?php echo esc_attr( $connection_options->get( 'mail', 'from_email_force_exclude_emails' ) ); ?>"
									 <?php echo $connection_options->is_const_defined( 'mail', 'from_email_force_exclude_emails' ) ? 'disabled' : ''; ?>
									 id="easy-wp-smtp-setting-from_email_force_exclude_emails" spellcheck="false"
						/>
						<p class="desc">
							<?php esc_html_e( 'Comma separated emails list. (Example value: email1@domain.com, email2@domain.com)', 'easy-wp-smtp' ); ?>
						</p>
						<p class="desc">
							<?php esc_html_e( '(Optional) This option is useful when you are using several email aliases on your SMTP server. If you don\'t want your aliases to be replaced by the address specified in From Email Address setting, enter them in this field.', 'easy-wp-smtp' ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Process connection settings. Should be called before options save.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data     Connection data.
	 * @param array $old_data Old connection data.
	 */
	public function process( $data, $old_data ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded, Generic.Metrics.CyclomaticComplexity.TooHigh

		// When checkbox is unchecked - it's not submitted at all, so we need to define its default false value.
		if ( ! isset( $data['mail']['from_email_force'] ) ) {
			$data['mail']['from_email_force'] = false;
		}
		if ( ! isset( $data['mail']['from_name_force'] ) ) {
			$data['mail']['from_name_force'] = false;
		}
		if ( ! isset( $data['mail']['advanced'] ) ) {
			$data['mail']['advanced'] = false;
		}
		if ( ! isset( $data['mail']['reply_to_replace_from'] ) ) {
			$data['mail']['reply_to_replace_from'] = false;
		}
		if ( ! isset( $data['smtp']['autotls'] ) ) {
			$data['smtp']['autotls'] = false;
		}
		if ( ! isset( $data['smtp']['auth'] ) ) {
			$data['smtp']['auth'] = false;
		}

		// When switching mailers.
		if (
			! empty( $old_data['mail']['mailer'] ) &&
			! empty( $data['mail']['mailer'] ) &&
			$old_data['mail']['mailer'] !== $data['mail']['mailer']
		) {
			// Remove all debug messages when switching mailers.
			Debug::clear();
		}

		// Prevent redirect to setup wizard from settings page after successful auth.
		if (
			! empty( $data['mail']['mailer'] ) &&
			in_array( $data['mail']['mailer'], [ 'gmail', 'outlook' ], true )
		) {
			$data[ $data['mail']['mailer'] ]['is_setup_wizard_auth'] = false;
		}

		return $data;
	}

	/**
	 * Post process connection settings. Should be called after options save.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data     Connection data.
	 * @param array $old_data Old connection data.
	 */
	public function post_process( $data, $old_data ) {}

	/**
	 * Get connection settings admin page URL.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_admin_page_url() {

		/**
		 * Filters connection settings admin page URL.
		 *
		 * @since 2.0.0
		 *
		 * @param string              $admin_page_url Connection settings admin page URL.
		 * @param ConnectionInterface $connection     The Connection object.
		 */
		return apply_filters(
			'easy_wp_smtp_admin_connection_settings_get_admin_page_url',
			easy_wp_smtp()->get_admin()->get_admin_page_url(),
			$this->connection
		);
	}

	/**
	 * Get after process scroll to anchor. Returns `false` if scroll is not needed.
	 *
	 * @since 2.0.0
	 */
	public function get_scroll_to() {

		return $this->scroll_to;
	}
}
