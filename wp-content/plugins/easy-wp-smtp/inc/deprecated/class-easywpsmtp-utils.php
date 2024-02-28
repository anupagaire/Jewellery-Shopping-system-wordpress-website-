<?php

use ioncube\phpOpensslCryptor\Cryptor;

class EasyWPSMTP_Utils {

	public $enc_key;
	protected static $instance = null;

	public function __construct($addon=null) {
		require_once EasyWPSMTP::get_instance()->plugin_path . '/inc/deprecated/Cryptor.php';

		$this->enc_key = '';
		$this->addon = $addon;
	}

	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function base64_decode_maybe( $str ) {
		if ( ! function_exists( 'mb_detect_encoding' ) ) {
			return base64_decode( $str ); //phpcs:ignore
		}
		if ( mb_detect_encoding( $str ) === mb_detect_encoding( base64_decode( base64_encode( base64_decode( $str ) ) ) ) ) { //phpcs:ignore
			$str = base64_decode( $str ); //phpcs:ignore
		}
		return $str;
	}

	public function encrypt_password( $password ) {

		if ( empty( $password ) ) {
			return '';
		}

		$key = get_option( 'swpsmtp_enc_key', false );

		if ( empty( $key ) ) {
			$key = wp_salt();
			update_option( 'swpsmtp_enc_key', $key, false );
		}

		try {
			$password = Cryptor::Encrypt( $password, $key );
		} catch ( Exception $e ) {}

		return $password;
	}

	public function decrypt_password( $password ) {

		$key = get_option( 'swpsmtp_enc_key', false );

		try {
			$password = Cryptor::Decrypt( $password, $key );
		} catch ( Exception $e ) {}

		return $password;
	}

	/**
 * Sanitizes textarea. Tries to use wp sanitize_textarea_field() function. If that's not available, uses its own methods
 * @return string
 */
	public static function sanitize_textarea( $str ) {
		if ( function_exists( 'sanitize_textarea_field' ) ) {
			return sanitize_textarea_field( $str );
		}
		$filtered = wp_check_invalid_utf8( $str );

		if ( strpos( $filtered, '<' ) !== false ) {
			$filtered = wp_pre_kses_less_than( $filtered );
			// This will strip extra whitespace for us.
			$filtered = wp_strip_all_tags( $filtered, false );

			// Use html entities in a special case to make sure no later
			// newline stripping stage could lead to a functional tag
			$filtered = str_replace( "<\n", "&lt;\n", $filtered );
		}

		$filtered = trim( $filtered );

		$found = false;
		while ( preg_match( '/%[a-f0-9]{2}/i', $filtered, $match ) ) {
			$filtered = str_replace( $match[0], '', $filtered );
			$found    = true;
		}

		if ( $found ) {
			// Strip out the whitespace that may now exist after removing the octets.
			$filtered = trim( preg_replace( '/ +/', ' ', $filtered ) );
		}

		return $filtered;
	}


	public function check_ver() {


		if ( $this->addon!=null && version_compare( EasyWPSMTP_PLUGIN_VERSION, $this->addon->MIN_EasyWPSMTP_VER ) < 0 ) {
			add_action( 'admin_notices', array( $this, 'display_min_version_error' ) );
			return false;
		}
		return true;
	}

	public function display_min_version_error() {
		$class = 'notice notice-error';
		// translators: %1$s - plugin name, %2$s - min core plugin version, %3$s - installed core plugin version
		$message = sprintf( __( '%1$s requires Easy WP SMTP plugin minimum version to be %2$s (you have version %3$s installed). Please update Easy WP SMTP plugin.', 'easy-wp-smtp' ), $this->addon->ADDON_FULL_NAME, $this->addon->MIN_EasyWPSMTP_VER, EasyWPSMTP_PLUGIN_VERSION );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}

}
