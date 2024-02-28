<?php

namespace EasyWPSMTP\Migrations;

use EasyWPSMTP_Utils;
use Exception;

/**
 * Class DeprecatedOptionsConverter helps convert deprecated options structure to new one.
 *
 * @since 2.0.0
 */
class DeprecatedOptionsConverter extends MigrationAbstract {

	/**
	 * Convert old values.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_converted_options() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded

		$old_options = get_option( 'swpsmtp_options', [] );

		if ( empty( $old_options ) ) {
			return [];
		}

		$converted = [];

		if ( isset( $old_options['from_email_field'] ) ) {
			$converted['mail']['from_email'] = $old_options['from_email_field'];
		}

		if ( isset( $old_options['from_name_field'] ) ) {
			$converted['mail']['from_name'] = $old_options['from_name_field'];
		}

		if ( isset( $old_options['force_from_name_replace'] ) ) {
			$converted['mail']['from_name_force'] = (bool) $old_options['force_from_name_replace'];
		}

		if ( isset( $old_options['reply_to_email'] ) ) {
			$converted['mail']['reply_to_email'] = $old_options['reply_to_email'];
		}

		if ( isset( $old_options['sub_mode'] ) ) {
			$converted['mail']['reply_to_replace_from'] = (bool) $old_options['sub_mode'];
		}

		if ( isset( $old_options['bcc_email'] ) ) {
			$converted['mail']['bcc_emails'] = $old_options['bcc_email'];
		}

		if ( isset( $old_options['email_ignore_list'] ) ) {
			$converted['mail']['from_email_force_exclude_emails'] = $old_options['email_ignore_list'];
		}

		if ( isset( $old_options['smtp_settings'] ) ) {
			$old_smtp_settings = $old_options['smtp_settings'];

			if ( isset( $old_smtp_settings['host'] ) ) {
				$converted['smtp']['host'] = $old_smtp_settings['host'];
			}

			if ( isset( $old_smtp_settings['type_encryption'] ) ) {
				$converted['smtp']['encryption'] = $old_smtp_settings['type_encryption'];
			}

			if ( isset( $old_smtp_settings['port'] ) ) {
				$converted['smtp']['port'] = $old_smtp_settings['port'];
			}

			if ( isset( $old_smtp_settings['autentication'] ) ) {
				$converted['smtp']['auth'] = $old_smtp_settings['autentication'] === 'yes';
			}

			if ( isset( $old_smtp_settings['username'] ) ) {
				$converted['smtp']['user'] = $old_smtp_settings['username'];
			}

			$converted['smtp']['pass'] = $this->get_smtp_password();

			if ( isset( $old_smtp_settings['insecure_ssl'] ) ) {
				$converted['general']['allow_smtp_insecure_ssl'] = (bool) $old_smtp_settings['insecure_ssl'];
			}

			if ( isset( $old_smtp_settings['enable_debug'] ) ) {
				$converted['deprecated']['debug_log_enabled'] = (bool) $old_smtp_settings['enable_debug'];
			}
		}

		if ( isset( $old_options['enable_domain_check'] ) ) {
			$converted['general']['domain_check'] = (bool) $old_options['enable_domain_check'];
		}

		if ( isset( $old_options['allowed_domains'] ) ) {
			$converted['general']['domain_check_allowed_domains'] = EasyWPSMTP_Utils::base64_decode_maybe( $old_options['allowed_domains'] );
		}

		if ( isset( $old_options['block_all_emails'] ) ) {
			$converted['general']['domain_check_do_not_send'] = (bool) $old_options['block_all_emails'];
		}

		if ( empty( $old_options['from_email_field'] ) || empty( $old_options['from_name_field'] ) ) {
			// Switch to default mailer.
			$converted['mail']['mailer'] = 'mail';
		} else {
			// Switch to SMTP mailer.
			$converted['mail']['mailer'] = 'smtp';
		}

		// Force from email, since it's forced in previous version.
		$converted['mail']['from_email_force'] = true;

		// Disable auto TLS, since it's disabled in previous version.
		$converted['smtp']['autotls'] = false;

		return $converted;
	}

	/**
	 * Get SMTP password from deprecated options.
	 *
	 * @since 2.0.0
	 */
	private static function get_smtp_password() {

		$old_options = get_option( 'swpsmtp_options' );

		if ( empty( $old_options['smtp_settings']['password'] ) ) {
			return '';
		}

		$temp_password = $old_options['smtp_settings']['password'];

		try {
			if ( get_option( 'swpsmtp_pass_encrypted' ) ) {
				// This is encrypted password.
				$cryptor   = EasyWPSMTP_Utils::get_instance();
				$decrypted = $cryptor->decrypt_password( $temp_password );

				return $decrypted;
			}
		} catch ( Exception $e ) {
			return '';
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$decoded_pass = base64_decode( $temp_password );

		// No additional checks for servers that aren't configured with mbstring enabled.
		if ( ! function_exists( 'mb_detect_encoding' ) ) {
			return $decoded_pass;
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		if ( base64_encode( $decoded_pass ) === $temp_password ) {
			// It might be encoded.
			if ( mb_detect_encoding( $decoded_pass ) === false ) { // Could not find character encoding.
				$password = $temp_password;
			} else {
				// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
				$password = base64_decode( $temp_password );
			}
		} else { // Not encoded.
			$password = $temp_password;
		}

		return stripslashes( $password );
	}
}
