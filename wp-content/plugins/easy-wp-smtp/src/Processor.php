<?php

namespace EasyWPSMTP;

use EasyWPSMTP\Helpers\Geo;
use EasyWPSMTP\Helpers\Helpers;
use Exception;

/**
 * Class Processor modifies the behaviour of wp_mail() function.
 *
 * @since 2.0.0
 */
class Processor {

	/**
	 * Connections manager.
	 *
	 * @since 2.0.0
	 *
	 * @var ConnectionsManager
	 */
	private $connections_manager;

	/**
	 * Whether to keep original From Name. Original From Name should be preserved if original
	 * From Email is in the exclude force from list.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	private $keep_original_from_name;

	/**
	 * Force processing even if it should be skipped by certain conditions.
	 *
	 * @since 2.0.0
	 *
	 * @var bool
	 */
	private $force_processing = false;

	/**
	 * Class constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param ConnectionsManager $connections_manager Connections manager.
	 */
	public function __construct( $connections_manager = null ) {

		if ( is_null( $connections_manager ) ) {
			$this->connections_manager = easy_wp_smtp()->get_connections_manager();
		} else {
			$this->connections_manager = $connections_manager;
		}
	}

	/**
	 * Assign all hooks to proper places.
	 *
	 * @since 2.0.0
	 */
	public function hooks() {

		add_action( 'phpmailer_init', array( $this, 'phpmailer_init' ) );

		// High priority number tries to ensure our plugin code executes last and respects previous hooks, if not forced.
		add_filter( 'wp_mail_from', array( $this, 'filter_mail_from_email' ), PHP_INT_MAX );
		add_filter( 'wp_mail_from_name', array( $this, 'filter_mail_from_name' ), PHP_INT_MAX );
	}

	/**
	 * Redefine certain PHPMailer options with our custom ones.
	 *
	 * @since 2.0.0
	 *
	 * @param \PHPMailer $phpmailer It's passed by reference, so no need to return anything.
	 */
	public function phpmailer_init( $phpmailer ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded

		if ( $this->skip_processing() ) {
			return;
		}

		$connection         = $this->connections_manager->get_mail_connection();
		$connection_options = $connection->get_options();
		$mailer             = $connection->get_mailer_slug();

		// Check that mailer is not blank, and if mailer=smtp, host is not blank.
		if (
			! $mailer ||
			( 'smtp' === $mailer && ! $connection_options->get( 'smtp', 'host' ) )
		) {
			return;
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		// Set the mailer type as per config above, this overrides the already called isMail method.
		// It's basically always 'smtp'.
		$phpmailer->Mailer = $mailer;

		// Set the SMTPSecure value, if set to none, leave this blank. Possible values: 'ssl', 'tls', ''.
		if ( 'none' === $connection_options->get( $mailer, 'encryption' ) ) {
			$phpmailer->SMTPSecure = '';
		} else {
			$phpmailer->SMTPSecure = $connection_options->get( $mailer, 'encryption' );
		}

		// Check if user has disabled SMTPAutoTLS.
		if ( $connection_options->get( $mailer, 'encryption' ) !== 'tls' && ! $connection_options->get( $mailer, 'autotls' ) ) {
			$phpmailer->SMTPAutoTLS = false;
		}

		// If we're sending via SMTP, set the host.
		if ( $mailer === 'smtp' ) {
			// Set the other options.
			$phpmailer->Host = $connection_options->get( $mailer, 'host' );
			$phpmailer->Port = $connection_options->get( $mailer, 'port' );

			// If we're using smtp auth, set the username & password.
			if ( $connection_options->get( $mailer, 'auth' ) ) {
				$phpmailer->SMTPAuth = true;
				$phpmailer->Username = $connection_options->get( $mailer, 'user' );
				$phpmailer->Password = $connection_options->get( $mailer, 'pass' );
			}
		}

		// Set reply-to header.
		$this->set_reply_to( $phpmailer );

		// Set bcc header.
		$this->set_bcc( $phpmailer );

		// Insecure SSL option enabled.
		if ( Options::init()->get( 'general', 'allow_smtp_insecure_ssl' ) ) {
			$phpmailer->SMTPOptions = [
				'ssl' => [
					'verify_peer'       => false,
					'verify_peer_name'  => false,
					'allow_self_signed' => true,
				],
			];
		}

		$phpmailer->Timeout = 30;

		// phpcs:enable

		// You can add your own options here.
		// See the phpmailer documentation for more info: https://github.com/PHPMailer/PHPMailer/tree/5.2-stable.
		/* @noinspection PhpUnusedLocalVariableInspection It's passed by reference. */
		$phpmailer = apply_filters( 'easy_wp_smtp_custom_options', $phpmailer );
	}

	/**
	 * This method will be called every time 'smtp' and 'mail' mailers will be used to send emails.
	 *
	 * @since 2.0.0
	 *
	 * @param bool   $is_sent If the email was sent.
	 * @param array  $to      To email address.
	 * @param array  $cc      CC email addresses.
	 * @param array  $bcc     BCC email addresses.
	 * @param string $subject The email subject.
	 * @param string $body    The email body.
	 * @param string $from    The from email address.
	 */
	public static function send_callback( $is_sent, $to, $cc, $bcc, $subject, $body, $from ) {

		if ( ! $is_sent ) {
			// Add mailer to the beginning and save to display later.
			Debug::set(
				'Mailer: ' . esc_html( easy_wp_smtp()->get_providers()->get_options( easy_wp_smtp()->get_connections_manager()->get_mail_connection()->get_mailer_slug() )->get_title() ) . "\r\n" .
				'PHPMailer was able to connect to SMTP server but failed while trying to send an email.'
			);
		} else {
			Debug::clear();
		}

		do_action( 'easy_wp_smtp_mailcatcher_smtp_send_after', $is_sent, $to, $cc, $bcc, $subject, $body, $from );
	}

	/**
	 * Validate the email address.
	 *
	 * @since 2.0.0
	 *
	 * @param string $email The email address.
	 *
	 * @return boolean True if email address is valid, false on failure.
	 */
	public static function is_email_callback( $email ) {

		return (bool) is_email( $email );
	}

	/**
	 * Modify the email address that is used for sending emails.
	 *
	 * @since 2.0.0
	 *
	 * @param string $wp_email The email address passed by the filter.
	 *
	 * @return string
	 */
	public function filter_mail_from_email( $wp_email ) {

		if ( $this->skip_processing() ) {
			return $wp_email;
		}

		$connection         = $this->connections_manager->get_mail_connection();
		$connection_options = $connection->get_options();
		$forced             = $connection_options->get( 'mail', 'from_email_force' );
		$from_email         = $connection_options->get( 'mail', 'from_email' );
		$def_email          = WP::get_default_email();

		$forced_exclude_emails         = $connection_options->get( 'mail', 'from_email_force_exclude_emails' );
		$this->keep_original_from_name = false;

		// Return unchanged FROM EMAIL if it's in the exclude list.
		if ( $forced && ! empty( $forced_exclude_emails ) ) {
			$forced_exclude_emails = array_map( 'trim', explode( ',', $forced_exclude_emails ) );

			if ( in_array( trim( $wp_email ), $forced_exclude_emails, true ) ) {
				$this->keep_original_from_name = true;

				return $wp_email;
			}
		}

		// Return FROM EMAIL if forced in settings.
		if ( $forced && ! empty( $from_email ) ) {
			return $from_email;
		}

		// If the FROM EMAIL is not the default, return it unchanged.
		if ( ! empty( $def_email ) && $wp_email !== $def_email ) {
			return $wp_email;
		}

		return ! empty( $from_email ) ? $from_email : $wp_email;
	}

	/**
	 * Modify the sender name that is used for sending emails.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name The from name passed through the filter.
	 *
	 * @return string
	 */
	public function filter_mail_from_name( $name ) {

		if ( $this->skip_processing() ) {
			return $name;
		}

		$connection         = $this->connections_manager->get_mail_connection();
		$connection_options = $connection->get_options();
		$forced             = $connection_options->get( 'mail', 'from_name_force' );

		// If the original FROM NAME should be preserved.
		if ( $this->keep_original_from_name ) {
			return $name;
		}

		// Return FROM EMAIL from the settings if forced.
		if ( $forced || empty( $name ) ) {
			return $connection_options->get( 'mail', 'from_name' );
		}

		return $name;
	}

	/**
	 * Get the default email address based on domain name.
	 *
	 * @since 2.0.0
	 *
	 * @return string Empty string when we aren't able to get the site domain (CLI, misconfigured server etc).
	 */
	public function get_default_email() {

		$server_name = Geo::get_site_domain();

		if ( empty( $server_name ) ) {
			return '';
		}

		// Get rid of www.
		$sitename = strtolower( $server_name );
		if ( substr( $sitename, 0, 4 ) === 'www.' ) {
			$sitename = substr( $sitename, 4 );
		}

		return 'wordpress@' . $sitename;
	}

	/**
	 * Get the default email FROM NAME generated by WordPress.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_default_name() {

		return 'WordPress';
	}

	/**
	 * Get or create the phpmailer.
	 *
	 * @since 2.0.0
	 *
	 * @return MailCatcherInterface
	 */
	public function get_phpmailer() {

		global $phpmailer;

		// Make sure the PHPMailer class has been instantiated.
		if ( ! is_object( $phpmailer ) || ! is_a( $phpmailer, 'PHPMailer' ) ) {
			$phpmailer = easy_wp_smtp()->generate_mail_catcher( true ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}

		return $phpmailer;
	}

	/**
	 * Set the reply_to header that configured in the settings.
	 *
	 * @since 2.0.0
	 *
	 * @param MailCatcherInterface $phpmailer The PHPMailer object.
	 */
	private function set_reply_to( $phpmailer ) {

		$connection         = $this->connections_manager->get_mail_connection();
		$connection_options = $connection->get_options();
		$reply_to_email     = $connection_options->get( 'mail', 'reply_to_email' );

		if ( empty( $reply_to_email ) ) {
			return;
		}

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$from_name = $phpmailer->FromName;

		if (
			$connection_options->get( 'mail', 'reply_to_replace_from' ) &&
			! empty( $phpmailer->getReplyToAddresses() )
		) {
			$reply_to_emails = $phpmailer->getReplyToAddresses();
			$from_email      = $phpmailer->From;

			if ( array_key_exists( $from_email, $reply_to_emails ) ) {
				$phpmailer->clearReplyTos();

				try {
					$phpmailer->AddReplyTo( $reply_to_email, $from_name );

					unset( $reply_to_emails[ $from_email ] );
				} catch ( Exception $e ) {}

				foreach ( $reply_to_emails as $email => $reply_to ) {
					try {
						$phpmailer->AddReplyTo( $email, $reply_to[1] );
					} catch ( Exception $e ) {
						continue;
					}
				}
			}
		} else {
			try {
				$phpmailer->AddReplyTo( $reply_to_email, $from_name );
			} catch ( Exception $e ) {}
		}
	}

	/**
	 * Set the bcc header that configured in the settings.
	 *
	 * @since 2.0.0
	 *
	 * @param MailCatcherInterface $phpmailer The PHPMailer object.
	 */
	private function set_bcc( $phpmailer ) {

		$connection         = $this->connections_manager->get_mail_connection();
		$connection_options = $connection->get_options();
		$bcc_emails         = $connection_options->get( 'mail', 'bcc_emails' );

		if ( empty( $bcc_emails ) ) {
			return;
		}

		$bcc_emails = explode( ',', $bcc_emails );

		foreach ( $bcc_emails as $bcc_email ) {
			try {
				$phpmailer->AddBcc( trim( $bcc_email ) );
			} catch ( Exception $e ) {
				continue;
			}
		}
	}

	/**
	 * Set whether to force processing even if it should be skipped by certain conditions.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $force_processing Whether to force processing.
	 */
	public function set_force_processing( $force_processing ) {

		$this->force_processing = $force_processing;
	}

	/**
	 * Whether to skip processing.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	private function skip_processing() {

		if ( $this->force_processing ) {
			return false;
		}

		$connection         = $this->connections_manager->get_mail_connection();
		$connection_options = $connection->get_options();

		$from_email = $connection_options->get( 'mail', 'from_email' );
		$from_name  = $connection_options->get( 'mail', 'from_name' );

		return empty( $from_email ) || empty( $from_name ) || Helpers::is_domain_blocked();
	}
}
