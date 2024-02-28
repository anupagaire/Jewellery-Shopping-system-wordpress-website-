<?php

namespace EasyWPSMTP;

use EasyWPSMTP\Helpers\Helpers;
use Exception;
use EasyWPSMTP\Admin\DebugEvents\DebugEvents;
use EasyWPSMTP\Providers\MailerAbstract;

/**
 * Trait MailCatcherTrait.
 *
 * @since 2.0.0
 */
trait MailCatcherTrait {

	/**
	 * Debug output buffer.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	private $debug_output_buffer = [];

	/**
	 * Debug event ID.
	 *
	 * @since 2.0.0
	 *
	 * @var int
	 */
	private $debug_event_id = false;

	/**
	 * Whether the current email is a test email.
	 *
	 * @since 2.0.0
	 *
	 * @var bool
	 */
	private $is_test_email = false;

	/**
	 * Whether the current email is a Setup Wizard test email.
	 *
	 * @since 2.1.0
	 *
	 * @var bool
	 */
	private $is_setup_wizard_test_email = false;

	/**
	 * Holds the most recent error message.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $latest_error = '';

	/**
	 * Modify the default send() behaviour.
	 * For those mailers, that relies on PHPMailer class - call it directly.
	 * For others - init the correct provider and process it.
	 *
	 * @since 2.0.0
	 *
	 * @throws Exception When sending via PhpMailer fails for some reason.
	 *
	 * @return bool
	 */
	public function send() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded, Generic.Metrics.NestingLevel.MaxExceeded

		$connection         = easy_wp_smtp()->get_connections_manager()->get_mail_connection();
		$connection_options = $connection->get_options();
		$mail_mailer        = $connection->get_mailer_slug();

		// Use the PHPMailer::send method to send an email, since plugin is not configured (From email or From name is not set).
		if (
			empty( $connection_options->get( 'mail', 'from_email' ) ) ||
			empty( $connection_options->get( 'mail', 'from_name' ) )
		) {
			return parent::send();
		}

		// Reset email related variables.
		$this->debug_event_id             = false;
		$this->is_test_email              = false;
		$this->is_setup_wizard_test_email = false;
		$this->latest_error               = '';

		$is_emailing_blocked = false;

		if ( easy_wp_smtp()->is_blocked() ) {
			$is_emailing_blocked = true;
		}

		// Always allow a test email - check for the specific header.
		foreach ( (array) $this->getCustomHeaders() as $header ) {
			if (
				! empty( $header[0] ) &&
				! empty( $header[1] ) &&
				$header[0] === 'X-Mailer-Type'
			) {
				if ( trim( $header[1] ) === 'EasyWPSMTP/Admin/Test' ) {
					$is_emailing_blocked = false;
					$this->is_test_email = true;
				} elseif ( trim( $header[1] ) === 'EasyWPSMTP/Admin/SetupWizard/Test' ) {
					$this->is_setup_wizard_test_email = true;
				}
			}
		}

		// Do not send emails if admin desired that.
		if ( $is_emailing_blocked ) {
			return false;
		}

		if ( Helpers::is_domain_blocked() && ! $this->is_test_email ) {
			return $this->handle_blocked_domain();
		}

		// Define a custom header, that will be used to identify the plugin and the mailer.
		$this->XMailer = 'EasyWPSMTP/Mailer/' . $mail_mailer . ' ' . EasyWPSMTP_PLUGIN_VERSION; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		// Use the default PHPMailer, as we inject our settings there for certain providers.
		if (
			$mail_mailer === 'mail' ||
			$mail_mailer === 'smtp'
		) {
			try {
				if ( DebugEvents::is_debug_enabled() && ! $this->is_test_email ) {
					$this->SMTPDebug   = 3; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					$this->Debugoutput = [ $this, 'debug_output_callback' ]; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				}

				/**
				 * Fires before email pre send via SMTP.
				 *
				 * Allow to hook early to catch any early failed emails.
				 *
				 * @since 2.0.0
				 *
				 * @param MailCatcherInterface $mailcatcher The MailCatcher object.
				 */
				do_action( 'easy_wp_smtp_mailcatcher_smtp_pre_send_before', $this );

				// Prepare all the headers.
				if ( ! $this->preSend() ) {
					return false;
				}

				/**
				 * Fires before email send via SMTP.
				 *
				 * Allow to hook after all the preparation before the actual sending.
				 *
				 * @since 2.0.0
				 *
				 * @param MailCatcherInterface $mailcatcher The MailCatcher object.
				 */
				do_action( 'easy_wp_smtp_mailcatcher_smtp_send_before', $this );

				$post_send = $this->postSend();

				DebugEvents::add_debug(
					esc_html__( 'An email request was sent.', 'easy-wp-smtp' )
				);

				return $post_send;
			} catch ( Exception $e ) {

				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$this->mailHeader = '';

				$this->setError( $e->getMessage() );

				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$error_message = 'Mailer: ' . esc_html( easy_wp_smtp()->get_providers()->get_options( $mail_mailer )->get_title() ) . "\r\n" . $this->ErrorInfo;

				// Set the debug error, but not for default PHP mailer.
				if ( $mail_mailer !== 'mail' ) {
					$this->debug_event_id = Debug::set( $error_message );
					$this->latest_error   = $error_message;

					if ( DebugEvents::is_debug_enabled() && ! empty( $this->debug_output_buffer ) ) {
						$debug_message  = $error_message . "\r\n" . esc_html__( 'Debug Output:', 'easy-wp-smtp' ) . "\r\n";
						$debug_message .= implode( "\r\n", $this->debug_output_buffer );

						$this->debug_event_id = DebugEvents::add_debug( $debug_message );
					}
				}

				/**
				 * Fires after email sent failure via SMTP.
				 *
				 * @since 2.0.0
				 *
				 * @param string               $error_message Error message.
				 * @param MailCatcherInterface $mailcatcher   The MailCatcher object.
				 * @param string               $mail_mailer   Current mailer name.
				 */
				do_action( 'easy_wp_smtp_mailcatcher_send_failed', $error_message, $this, $mail_mailer );

				if ( $this->exceptions ) {
					throw $e;
				}

				return false;
			} finally {

				// Clear debug output buffer.
				$this->debug_output_buffer = [];
			}
		}

		// We need this so that the \PHPMailer class will correctly prepare all the headers.
		$this->Mailer = 'mail'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		/**
		 * Fires before email pre send.
		 *
		 * Allow to hook early to catch any early failed emails.
		 *
		 * @since 2.0.0
		 *
		 * @param MailCatcherInterface $mailcatcher The MailCatcher object.
		 */
		do_action( 'easy_wp_smtp_mailcatcher_pre_send_before', $this );

		// Prepare everything (including the message) for sending.
		if ( ! $this->preSend() ) {
			return false;
		}

		$mailer = easy_wp_smtp()->get_providers()->get_mailer( $mail_mailer, $this, $connection );

		if ( ! $mailer ) {
			return false;
		}

		if ( ! $mailer->is_php_compatible() ) {
			return false;
		}

		/**
		 * Fires before email send.
		 *
		 * Allows to hook after all the preparation before the actual sending.
		 *
		 * @since 2.0.0
		 *
		 * @param MailerAbstract $mailer The Mailer object.
		 */
		do_action( 'easy_wp_smtp_mailcatcher_send_before', $mailer );

		/*
		 * Send the actual email.
		 * We reuse everything, that was preprocessed for usage in \PHPMailer.
		 */
		$mailer->send();

		$is_sent = $mailer->is_email_sent();

		if ( ! $is_sent ) {
			$error         = $mailer->get_response_error();
			$error_message = '';

			if ( ! empty( $error ) ) {
				// Add mailer to the beginning and save to display later.
				$message = 'Mailer: ' . esc_html( easy_wp_smtp()->get_providers()->get_options( $mailer->get_mailer_name() )->get_title() ) . "\r\n";

				$conflicts = new Conflicts();

				if ( $conflicts->is_detected() ) {
					$conflict_plugin_names = implode( ', ', $conflicts->get_all_conflict_names() );

					$message .= 'Conflicts: ' . esc_html( $conflict_plugin_names ) . "\r\n";
				}

				$error_message = $message . $error;

				$this->debug_event_id = Debug::set( $error_message );
				$this->latest_error   = $error_message;
			}

			/**
			 * Fires after email sent failure.
			 *
			 * @since 2.0.0
			 *
			 * @param string               $error_message Error message.
			 * @param MailCatcherInterface $mailcatcher   The MailCatcher object.
			 * @param string               $mail_mailer   Current mailer name.
			 */
			do_action( 'easy_wp_smtp_mailcatcher_send_failed', $error_message, $this, $mail_mailer );
		} else {

			// Clear debug messages if email is successfully sent.
			Debug::clear();
		}

		/**
		 * Fires after email send.
		 *
		 * Allow to perform any actions with the data.
		 *
		 * @since 2.0.0
		 *
		 * @param MailerAbstract       $mailer      The Mailer object.
		 * @param MailCatcherInterface $mailcatcher The MailCatcher object.
		 */
		do_action( 'easy_wp_smtp_mailcatcher_send_after', $mailer, $this );

		return $is_sent;
	}

	/**
	 * Create a unique ID to use for multipart email boundaries.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function generate_id() {

		return $this->generateId();
	}

	/**
	 * Debug output callback.
	 * Save debugging info to buffer array.
	 *
	 * @since 2.0.0
	 *
	 * @param string $str   Message.
	 * @param int    $level Debug level.
	 */
	public function debug_output_callback( $str, $level ) {

		/*
		 * Filter out all higher levels than 3.
		 * SMTPDebug level 3 is commands, data and connection status.
		 */
		if ( $level > 3 ) {
			return;
		}

		$this->debug_output_buffer[] = trim( $str, "\r\n" );
	}

	/**
	 * Get debug event ID.
	 *
	 * @since 2.0.0
	 *
	 * @return bool|int
	 */
	public function get_debug_event_id() {

		return $this->debug_event_id;
	}

	/**
	 * Whether the current email is a test email.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_test_email() {

		return $this->is_test_email;
	}

	/**
	 * Process email sending if it's not allowed to send emails from the website's current domain.
	 *
	 * @since 2.0.0
	 *
	 * @throws Exception When sending via PhpMailer fails for some reason.
	 *
	 * @return bool
	 */
	private function handle_blocked_domain() {

		$options = Options::init();
		$domain  = wp_parse_url( get_site_url(), PHP_URL_HOST );

		if ( $options->get( 'general', 'domain_check_do_not_send' ) ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$subject          = $this->Subject;
			$to_email_address = implode( ', ', array_column( $this->getToAddresses(), 0 ) );

			DebugEvents::add(
				'Domain check failed: website domain (' . $domain . ') is not in allowed domains list.' . WP::EOL .
				'Following email not sent (block all emails option is enabled):' . WP::EOL .
				'To: ' . $to_email_address . '; Subject: ' . $subject . WP::EOL
			);

			return true;
		} else {
			DebugEvents::add(
				'Domain check failed: website domain (' . $domain . ') is not in allowed domains list.' . WP::EOL .
				'SMTP settings won\'t be used.'
			);

			// Use the PHPMailer::send method to send an email (same behaviour as WP does).
			return parent::send();
		}
	}

	/**
	 * Whether the current email is a Setup Wizard test email.
	 *
	 * @since 2.1.0
	 *
	 * @return bool
	 */
	public function is_setup_wizard_test_email() {

		return $this->is_setup_wizard_test_email;
	}
}
