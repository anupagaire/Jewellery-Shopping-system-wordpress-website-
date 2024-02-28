<?php

namespace EasyWPSMTP;

use EasyWPSMTP\Helpers\Crypto;
use EasyWPSMTP\Migrations\DeprecatedOptionsConverter;
use EasyWPSMTP\Migrations\DeprecatedOptionsMigration;
use EasyWPSMTP\Reports\Emails\Summary as SummaryReportEmail;
use EasyWPSMTP\UsageTracking\UsageTracking;

/**
 * Class Options to handle all options management.
 * WordPress does all the heavy work for caching get_option() data,
 * so we don't have to do that. But we want to minimize cyclomatic complexity
 * of calling a bunch of WP functions, thus we will cache them in a class as well.
 *
 * @since 2.0.0
 */
class Options {

	/**
	 * All the options keys.
	 *
	 * @since 2.0.0
	 *
	 * @var array Map of all the default options of the plugin.
	 */
	private static $map = [
		'mail'       => [
			'from_name',
			'from_email',
			'mailer',
			'from_name_force',
			'from_email_force',
			'from_email_force_exclude_emails',
			'reply_to_email',
			'reply_to_replace_from',
			'bcc_emails',
		],
		'smtp'       => [
			'host',
			'port',
			'encryption',
			'autotls',
			'auth',
			'user',
			'pass',
		],
		'outlook'    => [
			'client_id',
			'client_secret',
		],
		'amazonses'  => [
			'client_id',
			'client_secret',
			'region',
		],
		'mailgun'    => [
			'api_key',
			'domain',
			'region',
		],
		'sendgrid'             => [
			'api_key',
			'domain',
		],
		'smtpcom'    => [
			'api_key',
			'channel',
		],
		'sendinblue' => [
			'api_key',
			'domain',
		],
		'sendlayer'  => [
			'api_key',
		],
		'license'    => [
			'key',
		],
	];

	/**
	 * List of all mailers (except PHP default mailer 'mail').
	 *
	 * @since 2.0.0
	 *
	 * @var string[]
	 */
	public static $mailers = [
		'sendlayer',
		'smtpcom',
		'sendinblue',
		'amazonses',
		'gmail',
		'mailgun',
		'outlook',
		'sendgrid',
		'smtp',
	];

	/**
	 * That's where plugin options are saved in wp_options table.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	const META_KEY = 'easy_wp_smtp';

	/**
	 * All instances of Options class that should be notified about options update.
	 *
	 * @since 2.0.0
	 *
	 * @var Options[]
	 */
	protected static $update_observers;

	/**
	 * Options data.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * Init the Options class.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		// Store all class instances that will be notified about options update.
		static::$update_observers[] = $this;

		$this->populate_options();
	}

	/**
	 * Initialize all the options.
	 *
	 * @since 2.0.0
	 *
	 * @return Options
	 */
	public static function init() {

		static $instance;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Whether current class is a main options.
	 *
	 * @since 2.0.0
	 *
	 * @var bool
	 */
	protected function is_main_options() {

		return true;
	}

	/**
	 * Default options that are saved on plugin activation.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public static function get_defaults() {

		return [
			'mail'    => [
				'from_email'       => get_option( 'admin_email' ),
				'from_name'        => get_bloginfo( 'name' ),
				'mailer'           => 'mail',
				'from_email_force' => true,
				'from_name_force'  => false,
			],
			'smtp'    => [
				'autotls' => true,
				'auth'    => true,
			],
			'general' => [
				'domain_check_allowed_domains' => wp_parse_url( get_site_url(), PHP_URL_HOST ),
				SummaryReportEmail::SETTINGS_SLUG => ! is_multisite() ? false : true,
			],
		];
	}

	/**
	 * Retrieve all options of the plugin.
	 *
	 * @since 2.0.0
	 */
	protected function populate_options() {

		$options = get_option( static::META_KEY, [] );

		// Use deprecated options if they were not already migrated.
		if ( empty( $options ) && DeprecatedOptionsMigration::get_current_version() < 1 ) {
			$options = ( new DeprecatedOptionsConverter() )->get_converted_options();
		}

		$this->options = apply_filters( 'easy_wp_smtp_populate_options', $options );
	}

	/**
	 * Get all the options.
	 *
	 * Options::init()->get_all();
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_all() {

		$options = $this->options;

		foreach ( $options as $group => $g_value ) {
			foreach ( $g_value as $key => $value ) {
				$options[ $group ][ $key ] = $this->get( $group, $key );
			}
		}

		return $this->is_main_options() ? apply_filters( 'easy_wp_smtp_options_get_all', $options ) : $options;
	}

	/**
	 * Get all the options for a group.
	 *
	 * Options::init()->get_group('smtp') - will return the array of options for the group, including defaults and constants.
	 *
	 * @since 2.0.0
	 *
	 * @param string $group
	 *
	 * @return array
	 */
	public function get_group( $group ) {

		// Just to feel safe.
		$group = sanitize_key( $group );

		/*
		 * Get the values saved in DB.
		 * If plugin is configured with constants right from the start - this will not have all the values.
		 */
		$options = isset( $this->options[ $group ] ) ? $this->options[ $group ] : array();

		// We need to process certain constants-aware options through actual constants.
		if ( isset( self::$map[ $group ] ) ) {
			foreach ( self::$map[ $group ] as $key ) {
				$options[ $key ] = $this->get( $group, $key );
			}
		}

		return $this->is_main_options() ? apply_filters( 'easy_wp_smtp_options_get_group', $options, $group ) : $options;
	}

	/**
	 * Get options by a group and a key.
	 *
	 * Options::init()->get( 'smtp', 'host' ) - will return only SMTP 'host' option.
	 *
	 * @since 2.0.0
	 *
	 * @param string $group         The option group.
	 * @param string $key           The option key.
	 * @param bool   $strip_slashes If the slashes should be stripped from string values.
	 *
	 * @return mixed|null Null if value doesn't exist anywhere: in constants, in DB, in a map. So it's completely custom or a typo.
	 */
	public function get( $group, $key, $strip_slashes = true ) {

		// Just to feel safe.
		$group = sanitize_key( $group );
		$key   = sanitize_key( $key );
		$value = null;

		// Get the const value if we have one.
		$value = $this->get_const_value( $group, $key, $value );

		// We don't have a const value.
		if ( $value === null ) {
			// Ordinary database or default values.
			if ( isset( $this->options[ $group ] ) ) {
				// Get the options key of a group.
				if ( isset( $this->options[ $group ][ $key ] ) ) {
					$value = $this->get_existing_option_value( $group, $key );
				} else {
					$value = $this->postprocess_key_defaults( $group, $key );
				}
			} else {
				/*
				 * Fallback to default if it doesn't exist in a map.
				 * Allow to retrieve only values from a map.
				 */
				if (
					isset( self::$map[ $group ] ) &&
					in_array( $key, self::$map[ $group ], true )
				) {
					$value = $this->postprocess_key_defaults( $group, $key );
				}
			}
		}

		// Conditionally strip slashes only from values saved in DB. Constants should be processed as is.
		if ( $strip_slashes && is_string( $value ) && ! $this->is_const_defined( $group, $key ) ) {
			$value = stripslashes( $value );
		}

		return $this->is_main_options() ? apply_filters( 'easy_wp_smtp_options_get', $value, $group, $key ) : $value;
	}

	/**
	 * Get the existing cached option value.
	 *
	 * @since 2.0.0
	 *
	 * @param string $group The options group.
	 * @param string $key   The options key.
	 *
	 * @return mixed
	 */
	private function get_existing_option_value( $group, $key ) {

		if ( $group === 'smtp' && $key === 'pass' ) {
			try {
				return Crypto::decrypt( $this->options[ $group ][ $key ] );
			} catch ( \Exception $e ) {
				return $this->options[ $group ][ $key ];
			}
		}

		return $this->options[ $group ][ $key ];
	}

	/**
	 * Some options may be non-empty by default,
	 * so we need to postprocess them to convert.
	 *
	 * @since 2.0.0
	 *
	 * @param string $group
	 * @param string $key
	 *
	 * @return mixed
	 */
	protected function postprocess_key_defaults( $group, $key ) {

		$value = '';

		switch ( $key ) {
			case 'from_email_force':
			case 'from_name_force':
				$value = $group === 'mail' ? false : true;
				break;

			case 'mailer':
				$value = 'mail';
				break;

			case 'encryption':
				$value = $group === 'smtp' ? 'none' : $value;
				break;

			case 'region':
				$value = $group === 'mailgun' ? 'US' : $value;
				break;

			case 'auth':
			case 'autotls':
				$value = $group === 'smtp' ? false : true;
				break;

			case 'pass':
				$value = $this->get_const_value( $group, $key, $value );
				break;

			case 'type':
				$value = $group === 'license' ? 'lite' : '';
				break;
		}

		return apply_filters( 'easy_wp_smtp_options_postprocess_key_defaults', $value, $group, $key );
	}

	/**
	 * Process the options values through the constants check.
	 * If we have defined associated constant - use it instead of a DB value.
	 * Backward compatibility is hard.
	 * General section of options won't have constants, so we are omitting those checks and just return default value.
	 *
	 * @since 2.0.0
	 *
	 * @param string $group
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	protected function get_const_value( $group, $key, $value ) {

		if ( ! $this->is_const_enabled() ) {
			return $value;
		}

		$return = null;

		switch ( $group ) {
			case 'mail':
				switch ( $key ) {
					case 'from_name':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EASY_WP_SMTP_MAIL_FROM_NAME : $value;
						break;
					case 'from_email':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EASY_WP_SMTP_MAIL_FROM : $value;
						break;
					case 'mailer':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EASY_WP_SMTP_MAILER : $value;
						break;
					case 'from_name_force':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EASY_WP_SMTP_MAIL_FROM_NAME_FORCE : $value;
						break;
					case 'from_email_force':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EASY_WP_SMTP_MAIL_FROM_FORCE : $value;
						break;
				}

				break;

			case 'smtp':
				switch ( $key ) {
					case 'host':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EASY_WP_SMTP_SMTP_HOST : $value;
						break;
					case 'port':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EASY_WP_SMTP_SMTP_PORT : $value;
						break;
					case 'encryption':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? ( EASY_WP_SMTP_SSL === '' ? 'none' : EASY_WP_SMTP_SSL ) : $value;
						break;
					case 'auth':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? (bool) EASY_WP_SMTP_SMTP_AUTH : $value;
						break;
					case 'autotls':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? (bool) EASY_WP_SMTP_SMTP_AUTOTLS : $value;
						break;
					case 'user':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EASY_WP_SMTP_SMTP_USER : $value;
						break;
					case 'pass':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EASY_WP_SMTP_SMTP_PASS : $value;
						break;
				}

				break;

			case 'sendlayer':
				switch ( $key ) {
					case 'api_key':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EASY_WP_SMTP_SENDLAYER_API_KEY : $value;
						break;
				}

				break;

			case 'outlook':
				switch ( $key ) {
					case 'client_id':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EasyWPSMTP_OUTLOOK_CLIENT_ID : $value;
						break;
					case 'client_secret':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EasyWPSMTP_OUTLOOK_CLIENT_SECRET : $value;
						break;
				}

				break;

			case 'amazonses':
				switch ( $key ) {
					case 'client_id':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EasyWPSMTP_AMAZONSES_CLIENT_ID : $value;
						break;
					case 'client_secret':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EasyWPSMTP_AMAZONSES_CLIENT_SECRET : $value;
						break;
					case 'region':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EasyWPSMTP_AMAZONSES_REGION : $value;
						break;
				}

				break;

			case 'mailgun':
				switch ( $key ) {
					case 'api_key':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EASY_WP_SMTP_MAILGUN_API_KEY : $value;
						break;
					case 'domain':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EASY_WP_SMTP_MAILGUN_DOMAIN : $value;
						break;
					case 'region':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EASY_WP_SMTP_MAILGUN_REGION : $value;
						break;
				}

				break;

			case 'sendgrid':
				switch ( $key ) {
					case 'api_key':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EasyWPSMTP_SENDGRID_API_KEY : $value;
						break;
					case 'domain':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EasyWPSMTP_SENDGRID_DOMAIN : $value;
						break;
				}

				break;

			case 'smtpcom':
				switch ( $key ) {
					case 'api_key':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EASY_WP_SMTP_SMTPCOM_API_KEY : $value;
						break;
					case 'channel':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EASY_WP_SMTP_SMTPCOM_CHANNEL : $value;
						break;
				}

				break;

			case 'sendinblue':
				switch ( $key ) {
					case 'api_key':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EASY_WP_SMTP_SENDINBLUE_API_KEY : $value;
						break;
					case 'domain':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EASY_WP_SMTP_SENDINBLUE_DOMAIN : $value;
						break;
				}

				break;

			case 'license':
				switch ( $key ) {
					case 'key':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EasyWPSMTP_LICENSE_KEY : $value;
						break;
				}

				break;

			case 'general':
				switch ( $key ) {
					case 'do_not_send':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? EASY_WP_SMTP_DO_NOT_SEND : $value;
						break;
					case SummaryReportEmail::SETTINGS_SLUG:
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ?
							$this->parse_boolean( EasyWPSMTP_SUMMARY_REPORT_EMAIL_DISABLED ) :
							$value;
						break;
				}

				break;

			case 'debug_events':
				switch ( $key ) {
					case 'retention_period':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? intval( EasyWPSMTP_DEBUG_EVENTS_RETENTION_PERIOD ) : $value;
						break;
				}

				break;

			default:
				// Always return the default value if nothing from above matches the request.
				$return = $value;
		}

		return apply_filters( 'easy_wp_smtp_options_get_const_value', $return, $group, $key, $value );
	}

	/**
	 * Whether constants redefinition is enabled or not.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_const_enabled() {

		// Disable constants support.
		return false;
	}

	/**
	 * We need this check to reuse later in admin area,
	 * to distinguish settings fields that were redefined,
	 * and display them differently.
	 *
	 * @since 2.0.0
	 *
	 * @param string $group
	 * @param string $key
	 *
	 * @return bool
	 */
	public function is_const_defined( $group, $key ) {

		if ( ! $this->is_const_enabled() ) {
			return false;
		}

		// Just to feel safe.
		$group  = sanitize_key( $group );
		$key    = sanitize_key( $key );
		$return = false;

		switch ( $group ) {
			case 'mail':
				switch ( $key ) {
					case 'from_name':
						$return = defined( 'EASY_WP_SMTP_MAIL_FROM_NAME' ) && EASY_WP_SMTP_MAIL_FROM_NAME;
						break;
					case 'from_email':
						$return = defined( 'EASY_WP_SMTP_MAIL_FROM' ) && EASY_WP_SMTP_MAIL_FROM;
						break;
					case 'mailer':
						$return = defined( 'EASY_WP_SMTP_MAILER' ) && EASY_WP_SMTP_MAILER;
						break;
					case 'from_name_force':
						$return = defined( 'EASY_WP_SMTP_MAIL_FROM_NAME_FORCE' ) && ( EASY_WP_SMTP_MAIL_FROM_NAME_FORCE === 'true' || EASY_WP_SMTP_MAIL_FROM_NAME_FORCE === true );
						break;
					case 'from_email_force':
						$return = defined( 'EASY_WP_SMTP_MAIL_FROM_FORCE' ) && ( EASY_WP_SMTP_MAIL_FROM_FORCE === 'true' || EASY_WP_SMTP_MAIL_FROM_FORCE === true );
						break;
				}

				break;

			case 'smtp':
				switch ( $key ) {
					case 'host':
						$return = defined( 'EASY_WP_SMTP_SMTP_HOST' ) && EASY_WP_SMTP_SMTP_HOST;
						break;
					case 'port':
						$return = defined( 'EASY_WP_SMTP_SMTP_PORT' ) && EASY_WP_SMTP_SMTP_PORT;
						break;
					case 'encryption':
						$return = defined( 'EASY_WP_SMTP_SSL' );
						break;
					case 'auth':
						$return = defined( 'EASY_WP_SMTP_SMTP_AUTH' );
						break;
					case 'autotls':
						$return = defined( 'EASY_WP_SMTP_SMTP_AUTOTLS' );
						break;
					case 'user':
						$return = defined( 'EASY_WP_SMTP_SMTP_USER' ) && EASY_WP_SMTP_SMTP_USER;
						break;
					case 'pass':
						$return = defined( 'EASY_WP_SMTP_SMTP_PASS' ) && EASY_WP_SMTP_SMTP_PASS;
						break;
				}

				break;

			case 'sendlayer':
				switch ( $key ) {
					case 'api_key':
						$return = defined( 'EASY_WP_SMTP_SENDLAYER_API_KEY' ) && EASY_WP_SMTP_SENDLAYER_API_KEY;
						break;
				}

				break;

			case 'outlook':
				switch ( $key ) {
					case 'client_id':
						$return = defined( 'EasyWPSMTP_OUTLOOK_CLIENT_ID' ) && EasyWPSMTP_OUTLOOK_CLIENT_ID;
						break;
					case 'client_secret':
						$return = defined( 'EasyWPSMTP_OUTLOOK_CLIENT_SECRET' ) && EasyWPSMTP_OUTLOOK_CLIENT_SECRET;
						break;
				}

				break;

			case 'amazonses':
				switch ( $key ) {
					case 'client_id':
						$return = defined( 'EasyWPSMTP_AMAZONSES_CLIENT_ID' ) && EasyWPSMTP_AMAZONSES_CLIENT_ID;
						break;
					case 'client_secret':
						$return = defined( 'EasyWPSMTP_AMAZONSES_CLIENT_SECRET' ) && EasyWPSMTP_AMAZONSES_CLIENT_SECRET;
						break;
					case 'region':
						$return = defined( 'EasyWPSMTP_AMAZONSES_REGION' ) && EasyWPSMTP_AMAZONSES_REGION;
						break;
				}

				break;

			case 'mailgun':
				switch ( $key ) {
					case 'api_key':
						$return = defined( 'EASY_WP_SMTP_MAILGUN_API_KEY' ) && EASY_WP_SMTP_MAILGUN_API_KEY;
						break;
					case 'domain':
						$return = defined( 'EASY_WP_SMTP_MAILGUN_DOMAIN' ) && EASY_WP_SMTP_MAILGUN_DOMAIN;
						break;
					case 'region':
						$return = defined( 'EASY_WP_SMTP_MAILGUN_REGION' ) && EASY_WP_SMTP_MAILGUN_REGION;
						break;
				}

				break;

			case 'sendgrid':
				switch ( $key ) {
					case 'api_key':
						$return = defined( 'EasyWPSMTP_SENDGRID_API_KEY' ) && EasyWPSMTP_SENDGRID_API_KEY;
						break;
					case 'domain':
						$return = defined( 'EasyWPSMTP_SENDGRID_DOMAIN' ) && EasyWPSMTP_SENDGRID_DOMAIN;
						break;
				}

				break;

			case 'smtpcom':
				switch ( $key ) {
					case 'api_key':
						$return = defined( 'EASY_WP_SMTP_SMTPCOM_API_KEY' ) && EASY_WP_SMTP_SMTPCOM_API_KEY;
						break;
					case 'channel':
						$return = defined( 'EASY_WP_SMTP_SMTPCOM_CHANNEL' ) && EASY_WP_SMTP_SMTPCOM_CHANNEL;
						break;
				}

				break;

			case 'sendinblue':
				switch ( $key ) {
					case 'api_key':
						$return = defined( 'EASY_WP_SMTP_SENDINBLUE_API_KEY' ) && EASY_WP_SMTP_SENDINBLUE_API_KEY;
						break;
					case 'domain':
						$return = defined( 'EASY_WP_SMTP_SENDINBLUE_DOMAIN' ) && EASY_WP_SMTP_SENDINBLUE_DOMAIN;
						break;
				}

				break;

			case 'license':
				switch ( $key ) {
					case 'key':
						$return = defined( 'EasyWPSMTP_LICENSE_KEY' ) && EasyWPSMTP_LICENSE_KEY;
						break;
				}

				break;

			case 'general':
				switch ( $key ) {
					case 'do_not_send':
						/** @noinspection PhpUndefinedConstantInspection */
						$return = defined( 'EASY_WP_SMTP_DO_NOT_SEND' ) && EASY_WP_SMTP_DO_NOT_SEND;
						break;
					case SummaryReportEmail::SETTINGS_SLUG:
						$return = defined( 'EasyWPSMTP_SUMMARY_REPORT_EMAIL_DISABLED' );
						break;
				}

				break;

			case 'debug_events';
				switch ( $key ) {
					case 'retention_period':
						$return = defined( 'EASY_WP_SMTP_DEBUG_EVENTS_RETENTION_PERIOD' );
						break;
				}

				break;
		}

		return apply_filters( 'easy_wp_smtp_options_is_const_defined', $return, $group, $key );
	}

	/**
	 * Set plugin options, all at once.
	 *
	 * @since 2.0.0
	 *
	 * @param array $options            Plugin options to save.
	 * @param bool  $once               Whether to update existing options or to add these options only once.
	 * @param bool  $overwrite_existing Whether to overwrite existing settings or merge these passed options with existing ones.
	 */
	public function set( $options, $once = false, $overwrite_existing = true ) {

		// Merge existing settings with new values.
		if ( ! $overwrite_existing ) {
			$options = self::array_merge_recursive( $this->get_all_raw(), $options );
		}

		$options = $this->process_generic_options( $options );
		$options = $this->process_mailer_specific_options( $options );
		$options = apply_filters( 'easy_wp_smtp_options_set', $options );

		$this->save_options( $options, $once );

		do_action( 'easy_wp_smtp_options_set_after', $options );
	}

	/**
	 * Save options to DB.
	 *
	 * @since 2.0.0
	 *
	 * @param array $options Options to save.
	 * @param bool  $once    Whether to update existing options or to add these options only once.
	 */
	protected function save_options( $options, $once ) {

		// Whether to update existing options or to add these options only once if they don't exist yet.
		if ( $once ) {
			add_option( static::META_KEY, $options, '', 'no' ); // Do not autoload these options.
		} else {
			update_option( static::META_KEY, $options, 'no' );
		}

		// Now we need to re-cache values of all instances.
		foreach ( static::$update_observers as $observer ) {
			$observer->populate_options();
		}
	}

	/**
	 * Process the generic plugin options.
	 *
	 * @since 2.0.0
	 *
	 * @param array $options The options array.
	 *
	 * @return array
	 */
	protected function process_generic_options( $options ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded, Generic.Metrics.NestingLevel.MaxExceeded

		foreach ( (array) $options as $group => $keys ) {
			foreach ( $keys as $option_name => $option_value ) {
				switch ( $group ) {
					case 'mail':
						switch ( $option_name ) {
							case 'from_name':
							case 'from_email_force_exclude_emails':
							case 'reply_to_email':
							case 'bcc_emails':
								$options[ $group ][ $option_name ] = sanitize_text_field( $option_value );
								break;
							case 'mailer':
								$mailer = sanitize_text_field( $option_value );

								$mailer = in_array( $mailer, self::$mailers, true ) ? $mailer : 'mail';

								$options[ $group ][ $option_name ] = $mailer;
								break;
							case 'from_email':
								if ( filter_var( $option_value, FILTER_VALIDATE_EMAIL ) ) {
									$options[ $group ][ $option_name ] = sanitize_email( $option_value );
								} else {
									$options[ $group ][ $option_name ] = sanitize_email(
										easy_wp_smtp()->get_processor()->get_default_email()
									);
								}
								break;
							case 'from_name_force':
							case 'from_email_force':
							case 'reply_to_replace_from':
							case 'advanced':
								$options[ $group ][ $option_name ] = (bool) $option_value;
								break;
						}
						break;

					case 'general':
						switch ( $option_name ) {
							case 'domain_check':
							case 'domain_check_do_not_send':
							case 'do_not_send':
							case 'allow_smtp_insecure_ssl':
							case 'am_notifications_hidden':
							case 'email_delivery_errors_hidden':
							case 'top_level_menu_hidden':
							case 'dashboard_widget_hidden':
							case 'uninstall':
							case UsageTracking::SETTINGS_SLUG:
							case SummaryReportEmail::SETTINGS_SLUG:
								$options[ $group ][ $option_name ] = (bool) $option_value;
								break;
							case 'domain_check_allowed_domains':
								$options[ $group ][ $option_name ] = sanitize_text_field( $option_value );
								break;
						}

					case 'debug_events':
						switch ( $option_name ) {
							case 'email_debug':
								$options[ $group ][ $option_name ] = (bool) $option_value;
								break;
							case 'retention_period':
								$options[ $group ][ $option_name ] = (int) $option_value;
								break;
						}

					case 'deprecated':
						switch ( $option_name ) {
							case 'debug_log_enabled':
								$options[ $group ][ $option_name ] = (bool) $option_value;
								break;
						}
				}
			}
		}

		return $options;
	}

	/**
	 * Process mailers-specific plugin options.
	 *
	 * @since 2.0.0
	 *
	 * @param array $options The options array.
	 *
	 * @return array
	 */
	protected function process_mailer_specific_options( $options ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded, Generic.Metrics.NestingLevel.MaxExceeded

		if (
			! empty( $options['mail']['mailer'] ) &&
			isset( $options[ $options['mail']['mailer'] ] ) &&
			in_array( $options['mail']['mailer'], self::$mailers, true )
		) {

			$mailer = $options['mail']['mailer'];

			foreach ( $options[ $mailer ] as $option_name => $option_value ) {
				switch ( $option_name ) {
					case 'host': // smtp.
					case 'user': // smtp.
					case 'encryption': // smtp.
					case 'region': // mailgun/amazonses.
					case 'api_key': // mailgun/sendinblue/smtpcom/sendlayer/sendgrid.
					case 'domain': // mailgun/sendinblue/sendgrid.
					case 'channel': // smtpcom.
					case 'client_id': // outlook/amazonses.
					case 'client_secret': // outlook/amazonses.
					case 'auth_code': // outlook.
						$options[ $mailer ][ $option_name ] = $this->is_const_defined( $mailer, $option_name ) ? '' : sanitize_text_field( $option_value );
						break;
					case 'port': // smtp.
						$options[ $mailer ][ $option_name ] = $this->is_const_defined( $mailer, $option_name ) ? 25 : (int) $option_value;
						break;
					case 'auth': // smtp.
					case 'autotls': // smtp.
						$option_value = (bool) $option_value;

						$options[ $mailer ][ $option_name ] = $this->is_const_defined( $mailer, $option_name ) ? false : $option_value;
						break;

					case 'pass': // smtp.
						// Do not process as they may contain certain special characters, but allow to be overwritten using constants.
						$option_value                       = trim( (string) $option_value );
						$options[ $mailer ][ $option_name ] = $this->is_const_defined( $mailer, $option_name ) ? '' : $option_value;

						if ( $mailer === 'smtp' && ! $this->is_const_defined( 'smtp', 'pass' ) ) {
							try {
								$options[ $mailer ][ $option_name ] = Crypto::encrypt( $option_value );
							} catch ( \Exception $e ) {} // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch, Squiz.Commenting.EmptyCatchComment.Missing, Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace
						}
						break;

					case 'access_token': // outlook, is an array.
					case 'user_details': // gmail/outlook, is an array.
					case 'relay_credentials': // gmail is an array.
						// These options don't support constants.
						$options[ $mailer ][ $option_name ] = $option_value;
						break;
				}
			}
		}

		return $options;
	}

	/**
	 * Merge recursively, including a proper substitution of values in sub-arrays when keys are the same.
	 * It's more like array_merge() and array_merge_recursive() combined.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public static function array_merge_recursive() {

		$arrays = func_get_args();

		if ( count( $arrays ) < 2 ) {
			return isset( $arrays[0] ) ? $arrays[0] : array();
		}

		$merged = array();

		while ( $arrays ) {
			$array = array_shift( $arrays );

			if ( ! is_array( $array ) ) {
				return array();
			}

			if ( empty( $array ) ) {
				continue;
			}

			foreach ( $array as $key => $value ) {
				if ( is_string( $key ) ) {
					if (
						is_array( $value ) &&
						array_key_exists( $key, $merged ) &&
						is_array( $merged[ $key ] )
					) {
						$merged[ $key ] = call_user_func( __METHOD__, $merged[ $key ], $value );
					} else {
						$merged[ $key ] = $value;
					}
				} else {
					$merged[] = $value;
				}
			}
		}

		return $merged;
	}

	/**
	 * Check whether the site is using provided mailer or not.
	 *
	 * @since 2.0.0
	 *
	 * @param string $mailer The mailer slug.
	 *
	 * @return bool
	 */
	public function is_mailer_active( $mailer ) {

		$mailer = sanitize_key( $mailer );

		return apply_filters(
			"easy_wp_smtp_options_is_mailer_active_{$mailer}",
			$this->get( 'mail', 'mailer' ) === $mailer
		);
	}

	/**
	 * Check whether the site is using SMTP as a mailer or not.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_mailer_smtp() {
		return apply_filters( 'easy_wp_smtp_options_is_mailer_smtp', in_array( $this->get( 'mail', 'mailer' ), array( 'smtp' ), true ) );
	}

	/**
	 * Get all the options, but without stripping the slashes.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_all_raw() {

		$options = $this->options;

		foreach ( $options as $group => $g_value ) {
			foreach ( $g_value as $key => $value ) {
				$options[ $group ][ $key ] = $this->get( $group, $key, false );
			}
		}

		return $options;
	}

	/**
	 * Parse boolean value from string.
	 *
	 * @since 2.0.0
	 *
	 * @param string|boolean $value String or boolean value.
	 *
	 * @return boolean
	 */
	public function parse_boolean( $value ) {

		// Return early if it's boolean.
		if ( is_bool( $value ) ) {
			return $value;
		}

		$value = trim( $value );

		return $value === 'true';
	}

	/**
	 * Get a message of a constant that was set inside wp-config.php file.
	 *
	 * @since 2.0.0
	 *
	 * @param string $constant Constant name.
	 *
	 * @return string
	 */
	public function get_const_set_message( $constant ) {

		return sprintf( /* translators: %1$s - constant that was used; %2$s - file where it was used. */
			esc_html__( 'The value of this field was set using a constant %1$s most likely inside %2$s of your WordPress installation.', 'easy-wp-smtp' ),
			'<code>' . esc_html( $constant ) . '</code>',
			'<code>wp-config.php</code>'
		);
	}

	/**
	 * Whether option was changed.
	 * Can be used only before option save to DB.
	 *
	 * @since 2.0.0
	 *
	 * @param string $new_value Submitted value (e.g from $_POST).
	 * @param string $group     Group key.
	 * @param string $key       Option key.
	 *
	 * @return bool
	 */
	public function is_option_changed( $new_value, $group, $key ) {

		$old_value = $this->get( $group, $key );

		return $old_value !== $new_value;
	}

	/**
	 * Whether constant was changed.
	 * Can be used only for insecure options.
	 *
	 * @since 2.0.0
	 *
	 * @param string $group Group key.
	 * @param string $key   Option key.
	 *
	 * @return bool
	 */
	public function is_const_changed( $group, $key ) {

		if ( ! $this->is_const_defined( $group, $key ) ) {
			return false;
		}

		// Prevent double options update on multiple function call for same option.
		static $cache = [];

		$cache_key = $group . '_' . $key;

		if ( isset( $cache[ $cache_key ] ) ) {
			return $cache[ $cache_key ];
		}

		$value = $this->get( $group, $key );

		// Get old value from DB.
		add_filter( 'easy_wp_smtp_options_is_const_enabled', '__return_false', PHP_INT_MAX );
		$old_value = $this->get( $group, $key );
		remove_filter( 'easy_wp_smtp_options_is_const_enabled', '__return_false', PHP_INT_MAX );

		$changed = $value !== $old_value;

		// Save new constant value to DB.
		if ( $changed ) {
			$old_opt = $this->get_all_raw();

			$old_opt[ $group ][ $key ] = $value;
			$this->set( $old_opt );
		}

		$cache[ $cache_key ] = $changed;

		return $changed;
	}
}
