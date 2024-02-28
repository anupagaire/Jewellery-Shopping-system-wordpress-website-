<?php

namespace EasyWPSMTP\Providers\Sendinblue;

use EasyWPSMTP\ConnectionInterface;

/**
 * Class Api is a wrapper for Sendinblue library with handy methods.
 *
 * @since 2.0.0
 */
class Api {

	/**
	 * The Connection object.
	 *
	 * @since 2.0.0
	 *
	 * @var ConnectionInterface
	 */
	private $connection;

	/**
	 * Contains mailer options, constants + DB values.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	private $options;

	/**
	 * API constructor that inits defaults and retrieves options.
	 *
	 * @since 2.0.0
	 *
	 * @param ConnectionInterface $connection The Connection object.
	 */
	public function __construct( $connection = null ) {

		if ( ! is_null( $connection ) ) {
			$this->connection = $connection;
		} else {
			$this->connection = easy_wp_smtp()->get_connections_manager()->get_primary_connection();
		}

		$this->options = $this->connection->get_options()->get_group( Options::SLUG );
	}

	/**
	 * Configure API key authorization: api-key.
	 *
	 * @since 2.0.0
	 * @deprecated 2.2.0 We are no longer using the Sendinblue SDK.
	 *
	 * @return null
	 */
	protected function get_api_config() {

		_deprecated_function( __METHOD__, '2.2.0' );

		return null;
	}

	/**
	 * Get the mailer client instance for Account API.
	 *
	 * @since 2.0.0
	 * @deprecated 2.2.0 We are no longer using the Sendinblue SDK.
	 */
	public function get_account_client() {

		_deprecated_function( __METHOD__, '2.2.0' );

		return null;
	}

	/**
	 * Get the mailer client instance for Sender API.
	 *
	 * @since 2.0.0
	 * @deprecated 2.2.0 We are no longer using the Sendinblue SDK.
	 */
	public function get_sender_client() {

		_deprecated_function( __METHOD__, '2.2.0' );

		return null;
	}

	/**
	 * Get the mailer client instance for SMTP API.
	 *
	 * @since 2.0.0
	 * @deprecated 2.2.0 We are no longer using the Sendinblue SDK.
	 */
	public function get_smtp_client() {

		_deprecated_function( __METHOD__, '2.2.0' );

		return null;
	}

	/**
	 * Whether the mailer is ready to be used in API calls.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_ready() {

		return ! empty( $this->options['api_key'] );
	}
}
