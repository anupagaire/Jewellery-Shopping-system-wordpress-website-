<?php

use EasyWPSMTP\Helpers\Helpers;
use EasyWPSMTP\Migrations\DeprecatedOptionsMigration;
use EasyWPSMTP\Options;
use PHPMailer\PHPMailer\PHPMailer;

class EasyWPSMTP {

	public $opts;
	public $plugin_file;
	public $plugin_url;
	public $plugin_path;
	protected static $instance   = null;
	public static $reset_log_str = "Easy WP SMTP debug log file\r\n\r\n";

	public function __construct() {
		$this->opts        = [];
		$this->plugin_file = EasyWPSMTP_PLUGIN_FILE;
		$this->plugin_url = rtrim( plugin_dir_url( $this->plugin_file ), '/\\' );
		$this->plugin_path = rtrim( plugin_dir_path( $this->plugin_file ), '/\\' );

		require_once $this->plugin_path . '/inc/deprecated/functions.php';
		require_once $this->plugin_path . '/inc/deprecated/class-easywpsmtp-utils.php';

		if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			require_once $this->plugin_path . '/inc/deprecated/class-easywpsmtp-admin.php';
		}

		add_action( 'plugins_loaded', [ $this, 'init' ] );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init() {

		if ( Options::init()->get( 'deprecated', 'debug_log_enabled' ) ) {
			add_filter( 'wp_mail', array( $this, 'wp_mail' ), 2147483647 );
			add_action( 'wp_mail_failed', array( $this, 'wp_mail_failed' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
		}
	}

	public function wp_mail( $args ) {

		$options           = Options::init();
		$is_domain_blocked = Helpers::is_domain_blocked();

		//Check if the "to" field has multiple emails in an array.
		$to_address = "";

		if ( ! empty( $args['to'] ) ) {
			$to_address = is_array( $args['to'] ) ? implode( ' ; ', $args['to'] ) : $args['to'];
		}

		if ( $is_domain_blocked && $options->get( 'general', 'domain_check_do_not_send' ) ) {
			$domain = wp_parse_url( get_site_url(), PHP_URL_HOST );

			$this->log(
				"\r\n------------------------------------------------------------------------------------------------------\r\n" .
				'Domain check failed: website domain (' . $domain . ") is not in allowed domains list.\r\n" .
				"Following email not sent (block all emails option is enabled):\r\n" .
				'To: ' . $to_address . '; Subject: ' . $args['subject'] . "\r\n" .
				"------------------------------------------------------------------------------------------------------\r\n\r\n"
			);
		} else if( $options->get( 'deprecated', 'debug_log_enabled' ) ) {
			//Prepare the debug logging line
			$line = sprintf(
				'Headers: %s, To: %s, Subject: %s',
				! empty( $args['headers'] && is_array( $args['headers'] ) ) ? implode( ' | ', $args['headers'] ) : '',
				! empty( $args['to'] ) ? $to_address : '',
				! empty( $args['subject'] ) ? $args['subject'] : ''
			);
			$this->log( $line . "\r\n" );
		}

		if ( $is_domain_blocked && ! $options->get( 'general', 'domain_check_do_not_send' ) ) {
			$domain = wp_parse_url( get_site_url(), PHP_URL_HOST );

			$this->log(
				"\r\n------------------------------------------------------------------------------------------------------\r\n" .
				'Domain check failed: website domain (' . $domain . ") is not in allowed domains list.\r\n" .
				"SMTP settings won't be used.\r\n" .
				"------------------------------------------------------------------------------------------------------\r\n\r\n"
			);
		}

		return $args;
	}

	public function wp_mail_failed( $wp_error ) {
		if ( ! empty( $wp_error->errors ) && ! empty( $wp_error->errors['wp_mail_failed'] ) && is_array( $wp_error->errors['wp_mail_failed'] ) ) {
			$this->log( '*** ' . implode( ' | ', $wp_error->errors['wp_mail_failed'] ) . " ***\r\n" );
		}
	}

	public function init_smtp( &$phpmailer ) {
		//check if SMTP credentials have been configured.
		if ( ! $this->credentials_configured() ) {
			return;
		}
		//check if Domain Check enabled
		$domain = $this->is_domain_blocked();
		if ( false !== $domain ) {
			//domain check failed
			//let's check if we have block all emails option enabled
			if ( isset( $this->opts['block_all_emails'] ) && 1 === $this->opts['block_all_emails'] ) {
				// it's enabled. Let's use gag mailer class that would prevent emails from being sent out.
				require_once $this->plugin_path . '/inc/deprecated/class-easywpsmtp-gag-mailer.php';
				$phpmailer = new EasyWPSMTP_Gag_Mailer();
			}
			return;
		}

		/* Set the mailer type as per config above, this overrides the already called isMail method */
		$phpmailer->IsSMTP();
		if ( isset( $this->opts['force_from_name_replace'] ) && 1 === $this->opts['force_from_name_replace'] ) {
			$from_name = $this->opts['from_name_field'];
		} else {
			$from_name = ! empty( $phpmailer->FromName ) ? $phpmailer->FromName : $this->opts['from_name_field'];
		}
		$from_email = $this->opts['from_email_field'];
		//set ReplyTo option if needed
		//this should be set before SetFrom, otherwise might be ignored
		if ( ! empty( $this->opts['reply_to_email'] ) ) {
			if ( isset( $this->opts['sub_mode'] ) && 1 === $this->opts['sub_mode'] ) {
				if ( count( $phpmailer->getReplyToAddresses() ) >= 1 ) {
					// Substitute from_email_field with reply_to_email
					if ( array_key_exists( $this->opts['from_email_field'], $phpmailer->getReplyToAddresses() ) ) {
						$reply_to_emails = $phpmailer->getReplyToAddresses();
						unset( $reply_to_emails[ $this->opts['from_email_field'] ] );
						$phpmailer->clearReplyTos();
						foreach ( $reply_to_emails as $reply_to_email => $reply_to_name ) {
							$phpmailer->AddReplyTo( $reply_to_email, $reply_to_name );
						}
						$phpmailer->AddReplyTo( $this->opts['reply_to_email'], $from_name );
					}
				} else { // Reply-to array is empty so add reply_to_email
					$phpmailer->AddReplyTo( $this->opts['reply_to_email'], $from_name );
				}
			} else { // Default behaviour
				$phpmailer->AddReplyTo( $this->opts['reply_to_email'], $from_name );
			}
		}

		if ( ! empty( $this->opts['bcc_email'] ) ) {
			$bcc_emails = explode( ',', $this->opts['bcc_email'] );
			foreach ( $bcc_emails as $bcc_email ) {
				$bcc_email = trim( $bcc_email );
				$phpmailer->AddBcc( $bcc_email );
			}
		}

		// let's see if we have email ignore list populated
		if ( isset( $this->opts['email_ignore_list'] ) && ! empty( $this->opts['email_ignore_list'] ) ) {
			$emails_arr  = explode( ',', $this->opts['email_ignore_list'] );
			$from        = $phpmailer->From;
			$match_found = false;
			foreach ( $emails_arr as $email ) {
				if ( strtolower( trim( $email ) ) === strtolower( trim( $from ) ) ) {
					$match_found = true;
					break;
				}
			}
			if ( $match_found ) {
				//we should not override From and Fromname
				$from_email = $phpmailer->From;
				$from_name  = $phpmailer->FromName;
			}
		}
		$phpmailer->From     = $from_email;
		$phpmailer->FromName = $from_name;
		$phpmailer->SetFrom( $phpmailer->From, $phpmailer->FromName );
		//This should set Return-Path header for servers that are not properly handling it, but needs testing first
		//$phpmailer->Sender	 = $phpmailer->From;
		/* Set the SMTPSecure value */
		if ( 'none' !== $this->opts['smtp_settings']['type_encryption'] ) {
			$phpmailer->SMTPSecure = $this->opts['smtp_settings']['type_encryption'];
		}

		/* Set the other options */
		$phpmailer->Host = $this->opts['smtp_settings']['host'];
		$phpmailer->Port = $this->opts['smtp_settings']['port'];

		/* If we're using smtp auth, set the username & password */
		$phpmailer->SMTPAuth = false;
		if ( 'yes' === $this->opts['smtp_settings']['autentication'] ) {
			$phpmailer->SMTPAuth = true;
			$phpmailer->Username = $this->opts['smtp_settings']['username'];
			$phpmailer->Password = $this->get_password();
		}
		//PHPMailer 5.2.10 introduced this option. However, this might cause issues if the server is advertising TLS with an invalid certificate.
		$phpmailer->SMTPAutoTLS = false;

		if ( isset( $this->opts['smtp_settings']['insecure_ssl'] ) && false !== $this->opts['smtp_settings']['insecure_ssl'] ) {
			// Insecure SSL option enabled
			$phpmailer->SMTPOptions = array(
				'ssl' => array(
					'verify_peer'       => false,
					'verify_peer_name'  => false,
					'allow_self_signed' => true,
				),
			);
		}

		//set reasonable timeout
		$phpmailer->Timeout = 10;
	}

	public function test_mail( $to_email, $subject, $message ) {
		$ret = array();
		if ( ! $this->credentials_configured() ) {
			return false;
		}

		global $wp_version;

		if ( version_compare( $wp_version, '5.4.99' ) > 0 ) {
			require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
			require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
			require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
			$mail = new PHPMailer( true );
		} else {
			require_once ABSPATH . WPINC . '/class-phpmailer.php';
			$mail = new \PHPMailer( true );
		}

		try {

			$charset       = get_bloginfo( 'charset' );
			$mail->CharSet = $charset;

			$from_name  = $this->opts['from_name_field'];
			$from_email = $this->opts['from_email_field'];

			$mail->IsSMTP();

			// send plain text test email
			$mail->ContentType = 'text/plain';
			$mail->IsHTML( false );

			/* If using smtp auth, set the username & password */
			if ( 'yes' === $this->opts['smtp_settings']['autentication'] ) {
				$mail->SMTPAuth = true;
				$mail->Username = $this->opts['smtp_settings']['username'];
				$mail->Password = $this->get_password();
			}

			/* Set the SMTPSecure value, if set to none, leave this blank */
			if ( 'none' !== $this->opts['smtp_settings']['type_encryption'] ) {
				$mail->SMTPSecure = $this->opts['smtp_settings']['type_encryption'];
			}

			/* PHPMailer 5.2.10 introduced this option. However, this might cause issues if the server is advertising TLS with an invalid certificate. */
			$mail->SMTPAutoTLS = false;

			if ( isset( $this->opts['smtp_settings']['insecure_ssl'] ) && false !== $this->opts['smtp_settings']['insecure_ssl'] ) {
				// Insecure SSL option enabled
				$mail->SMTPOptions = array(
					'ssl' => array(
						'verify_peer'       => false,
						'verify_peer_name'  => false,
						'allow_self_signed' => true,
					),
				);
			}

			/* Set the other options */
			$mail->Host = $this->opts['smtp_settings']['host'];
			$mail->Port = $this->opts['smtp_settings']['port'];

			//Add reply-to if set in settings.
			if ( ! empty( $this->opts['reply_to_email'] ) ) {
				$mail->AddReplyTo( $this->opts['reply_to_email'], $from_name );
			}

			//Add BCC if set in settings.
			if ( ! empty( $this->opts['bcc_email'] ) ) {
				$bcc_emails = explode( ',', $this->opts['bcc_email'] );
				foreach ( $bcc_emails as $bcc_email ) {
					$bcc_email = trim( $bcc_email );
					$mail->AddBcc( $bcc_email );
				}
			}

			$mail->SetFrom( $from_email, $from_name );
			//This should set Return-Path header for servers that are not properly handling it, but needs testing first
			//$mail->Sender		 = $mail->From;
			$mail->Subject = $subject;
			$mail->Body    = $message;
			$mail->AddAddress( $to_email );
			global $debug_msg;
			$debug_msg         = '';
			$mail->Debugoutput = function ( $str, $level ) {
				global $debug_msg;
				$debug_msg .= $str;
			};
			$mail->SMTPDebug   = 1;
			//set reasonable timeout
			$mail->Timeout = 10;

			/* Send mail and return result */
			$mail->Send();
			$mail->ClearAddresses();
			$mail->ClearAllRecipients();
		} catch ( \Exception $e ) {
			$ret['error'] = $mail->ErrorInfo;
		} catch ( \Throwable $e ) {
			$ret['error'] = $mail->ErrorInfo;
		}

		$ret['debug_log'] = $debug_msg;

		return $ret;
	}

	public function admin_init() {

		if ( current_user_can( 'manage_options' ) ) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				add_action( 'wp_ajax_swpsmtp_clear_log', array( $this, 'clear_log' ) );
			}
			//view log file
			if ( isset( $_GET['swpsmtp_action'] ) ) {
				$options = Options::init();

				if ( 'view_log' === $_GET['swpsmtp_action'] ) {
					$log_file_root_path = $this->get_log_file_root_path();
					$log_file_name = $options->get('deprecated', 'debug_log_file_name');

					if ( empty( $log_file_name ) ) {
						//Nothing in the log file yet so nothing to show.
						wp_die( 'Nothing in the log file yet.' );
					}

					if ( ! file_exists( $log_file_root_path . $log_file_name ) ) {
						if ( $this->log( self::$reset_log_str ) === false ) {
							wp_die( esc_html( sprintf( 'Can\'t write to log file. Check if plugin directory (%s) is writeable.', $log_file_root_path ) ) );
						};
					}
					$logfile = fopen( $log_file_root_path . $log_file_name, 'rb' ); //phpcs:ignore
					if ( ! $logfile ) {
						wp_die( 'Can\'t open log file.' );
					}
					header( 'Content-Type: text/plain' );
					fpassthru( $logfile );
					die;
				}
			}
		}
	}

	public function admin_notices() {
		if ( ! $this->credentials_configured() ) {
			$settings_url = admin_url() . 'options-general.php?page=swpsmtp_settings';
			?>
			<div class="error">
				<p>
					<?php // translators: %s URL to the plugin's settings page.
					printf( __( 'Please configure your SMTP credentials in the <a href="%s">settings menu</a> in order to send email using Easy WP SMTP plugin.', 'easy-wp-smtp' ), esc_url( $settings_url ) );
					?>
				</p>
			</div>
			<?php
		}
	}

	public function get_log_file_path() {
		$log_file_name = 'logs' . DIRECTORY_SEPARATOR . '.' . uniqid( '', true ) . '.txt';
		$log_file_name = apply_filters( 'swpsmtp_log_file_path_override', $log_file_name );
		return $log_file_name;
	}

	private function get_log_file_root_path() {
		return apply_filters( 'easy_wp_mail_get_log_file_root_path', $this->plugin_path . '/' );
	}

	public function clear_log() {
		if ( ! check_ajax_referer( 'easy-wp-smtp-admin', 'nonce', false ) ) {
			echo esc_html( __( 'Nonce check failed.', 'easy-wp-smtp' ) );
			exit;
		};
		if ( $this->log( self::$reset_log_str, true ) !== false ) {
			echo '1';
		} else {
			echo esc_html( __( "Can't clear log - file is not writeable.", 'easy-wp-smtp' ) );
		}
		die;
	}

	public function log( $str, $overwrite = false ) {

		// Skip logging if deprecated options migration was not performed to prevent `easy_wp_smtp` option creation in DB.
		if ( DeprecatedOptionsMigration::get_current_version() < 1 ) {
			return true;
		}

		try {
			$options = Options::init();

			$log_file_root_path = $this->get_log_file_root_path();
			$log_file_name = '';
			if ( ! empty( $options->get( 'deprecated', 'debug_log_file_name' ) ) ) {
				$log_file_name = $options->get( 'deprecated', 'debug_log_file_name' );
			}
			if ( empty( $log_file_name ) || $overwrite ) {
				if ( ! empty( $log_file_name ) && file_exists( $log_file_root_path . $log_file_name ) ) {
					unlink( $log_file_root_path . $log_file_name );
				}
				$log_file_name = $this->get_log_file_path();

				$options->set(
					[
						'deprecated' => [
							'debug_log_file_name' => $log_file_name
						]
					],
					false,
					false
				);

				file_put_contents( $log_file_root_path . $log_file_name, self::$reset_log_str ); //phpcs:ignore
			}
			//Timestamp the log output
			$str = '[' . date( 'm/d/Y g:i:s A' ) . '] - ' . $str;
			//Write to the log file
			return ( file_put_contents( $log_file_root_path . $log_file_name, $str, ( ! $overwrite ? FILE_APPEND : 0 ) ) ); //phpcs:ignore
		} catch ( \Exception $e ) {
			return false;
		}
	}

	public function plugin_action_links( $links, $file ) {
		if ( plugin_basename( $this->plugin_file ) === $file ) {
			$settings_link = '<a href="options-general.php?page=swpsmtp_settings">' . __( 'Settings', 'easy-wp-smtp' ) . '</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}

	public function register_plugin_links( $links, $file ) {
		if ( plugin_basename( $this->plugin_file ) === $file ) {
			$links[] = '<a href="options-general.php?page=swpsmtp_settings">' . __( 'Settings', 'easy-wp-smtp' ) . '</a>';
		}
		return $links;
	}

	public function plugins_loaded_handler() {
		load_plugin_textdomain( 'easy-wp-smtp', false, dirname( plugin_basename( $this->plugin_file ) ) . '/languages/' );
	}

	public function is_domain_blocked() {

		return Helpers::is_domain_blocked();
	}

	public function get_password() {

		return Options::init()->get( 'smtp', 'pass' );
	}

	public function encrypt_password( $pass ) {
		if ( '' === $pass ) {
			return '';
		}

		return $this->get_password();
	}

	public function credentials_configured() {
		$credentials_configured = true;
		if ( empty( Options::init()->get( 'mail', 'from_email' ) ) ) {
			$credentials_configured = false;
		}
		if ( empty( Options::init()->get( 'mail', 'from_name' ) ) ) {
			$credentials_configured = false;
		}
		return $credentials_configured;
	}

	public function activate() {
		$swpsmtp_options_default = array(
			'from_email_field'        => '',
			'from_name_field'         => '',
			'force_from_name_replace' => 0,
			'sub_mode'                => 0,
			'smtp_settings'           => array(
				'host'            => 'smtp.example.com',
				'type_encryption' => 'none',
				'port'            => 25,
				'autentication'   => 'yes',
				'username'        => '',
				'password'        => '',
			),
		);

		/* install the default plugin options if needed */
		if ( empty( $this->opts ) ) {
			$this->opts = $swpsmtp_options_default;
		}
		$this->opts = array_merge( $swpsmtp_options_default, $this->opts );
		// reset log file
		$this->log( self::$reset_log_str, true );
		update_option( 'swpsmtp_options', $this->opts, 'yes' );
		//add current domain to allowed domains list
		if ( ! isset( $this->opts['allowed_domains'] ) ) {
			//TODO: Change parse_url() to wp_parse_url() and bump required WP version to 4.4.0
			$domain = parse_url( get_site_url(), PHP_URL_HOST ); //phpcs:ignore
			if ( $domain ) {
				$this->opts['allowed_domains'] = base64_encode( $domain ); //phpcs:ignore
				update_option( 'swpsmtp_options', $this->opts );
			}
		} else { // let's check if existing value should be base64 encoded
			if ( ! empty( $this->opts['allowed_domains'] ) ) {
				if ( EasyWPSMTP_Utils::base64_decode_maybe( $this->opts['allowed_domains'] ) === $this->opts['allowed_domains'] ) {
					$this->opts['allowed_domains'] = base64_encode( $this->opts['allowed_domains'] ); //phpcs:ignore
					update_option( 'swpsmtp_options', $this->opts );
				}
			}
		}
		// Encrypt password if needed
		if ( ! get_option( 'swpsmtp_pass_encrypted' ) ) {
			if ( extension_loaded( 'openssl' ) ) {
				if ( '' !== $this->opts['smtp_settings']['password'] ) {
					$this->opts['smtp_settings']['password'] = $this->encrypt_password( $this->get_password() );
					update_option( 'swpsmtp_options', $this->opts );
				}
			}
		}
	}

	public function deactivate() {
		// reset log file
		$this->log( self::$reset_log_str, true );
	}

	public function self_destruct_handler() {
		$err_msg = __( 'Please refresh the page and try again.', 'easy-wp-smtp' );
		$trans   = get_transient( 'easy_wp_smtp_sd_code' );
		if ( empty( $trans ) ) {
			echo esc_html( $err_msg );
			exit;
		}
		$sd_code = isset( $_POST['sd_code'] ) ? sanitize_text_field( stripslashes ( $_POST['sd_code'] ) ) : '';
		if ( $trans !== $sd_code ) {
			echo esc_html( $err_msg );
			exit;
		}
		$this->log( self::$reset_log_str, true );
		delete_site_option( 'swpsmtp_options' );
		delete_option( 'swpsmtp_options' );
		delete_site_option( 'smtp_test_mail' );
		delete_option( 'smtp_test_mail' );
		delete_site_option( 'swpsmtp_pass_encrypted' );
		delete_option( 'swpsmtp_pass_encrypted' );
		delete_option( 'swpsmtp_enc_key' );
		echo 1;
		deactivate_plugins( __FILE__ );
		exit;
	}
}

// Init.
EasyWPSMTP::get_instance();
