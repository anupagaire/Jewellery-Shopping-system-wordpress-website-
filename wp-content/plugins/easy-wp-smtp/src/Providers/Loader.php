<?php

namespace EasyWPSMTP\Providers;

use EasyWPSMTP\ConnectionInterface;
use EasyWPSMTP\Debug;
use EasyWPSMTP\MailCatcherInterface;
use EasyWPSMTP\Options;

/**
 * Class Loader.
 *
 * @since 2.0.0
 */
class Loader {

	/**
	 * Key is the mailer option, value is the path to its classes.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	protected $providers = [
		'sendlayer'  => 'EasyWPSMTP\Providers\Sendlayer\\',
		'amazonses'  => 'EasyWPSMTP\Providers\AmazonSES\\',
		'gmail'      => 'EasyWPSMTP\Providers\Gmail\\',
		'mailgun'    => 'EasyWPSMTP\Providers\Mailgun\\',
		'outlook'    => 'EasyWPSMTP\Providers\Outlook\\',
		'sendgrid'   => 'EasyWPSMTP\Providers\Sendgrid\\',
		'sendinblue' => 'EasyWPSMTP\Providers\Sendinblue\\',
		'smtpcom'    => 'EasyWPSMTP\Providers\SMTPcom\\',
		'smtp'       => 'EasyWPSMTP\Providers\SMTP\\',
		'mail'       => 'EasyWPSMTP\Providers\Mail\\',
	];

	/**
	 * Get all the supported providers.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_providers() {

		return apply_filters( 'easy_wp_smtp_providers_loader_get_providers', $this->providers );
	}

	/**
	 * Get a single provider FQN-path based on its name.
	 *
	 * @since 2.0.0
	 *
	 * @param string $provider
	 *
	 * @return string|null
	 */
	public function get_provider_path( $provider ) {

		$provider = sanitize_key( $provider );

		$providers = $this->get_providers();

		return apply_filters(
			'easy_wp_smtp_providers_loader_get_provider_path',
			isset( $providers[ $provider ] ) ? $providers[ $provider ] : null,
			$provider
		);
	}

	/**
	 * Get the provider options, if exists.
	 *
	 * @since 2.0.0
	 *
	 * @param string              $provider
	 * @param ConnectionInterface $connection The Connection object.
	 *
	 * @return OptionsAbstract|null
	 */
	public function get_options( $provider, $connection = null ) {

		return $this->get_entity( $provider, 'Options', [ $connection ] );
	}

	/**
	 * Get all options of all providers.
	 *
	 * @since 2.0.0
	 *
	 * @param ConnectionInterface $connection The Connection object.
	 *
	 * @return OptionsAbstract[]
	 */
	public function get_options_all( $connection = null ) {

		$options = array();

		foreach ( $this->get_providers() as $provider => $path ) {

			$option = $this->get_options( $provider, $connection );

			if ( ! $option instanceof OptionsAbstract ) {
				continue;
			}

			$slug  = $option->get_slug();
			$title = $option->get_title();

			if ( empty( $title ) || empty( $slug ) ) {
				continue;
			}

			$options[] = $option;
		}

		return apply_filters( 'easy_wp_smtp_providers_loader_get_providers_all', $options );
	}

	/**
	 * Get the provider mailer, if exists.
	 *
	 * @since 2.0.0
	 *
	 * @param string               $provider   The provider name.
	 * @param MailCatcherInterface $phpmailer  The MailCatcher object.
	 * @param ConnectionInterface  $connection The Connection object.
	 *
	 * @return MailerAbstract|null
	 */
	public function get_mailer( $provider, $phpmailer, $connection = null ) {

		return $this->get_entity( $provider, 'Mailer', [ $phpmailer, $connection ] );
	}

	/**
	 * Get the provider auth, if exists.
	 *
	 * @param string              $provider
	 * @param ConnectionInterface $connection The Connection object.
	 *
	 * @return AuthAbstract|null
	 */
	public function get_auth( $provider, $connection = null ) {

		return $this->get_entity( $provider, 'Auth', [ $connection ] );
	}

	/**
	 * Get a generic entity based on the request.
	 *
	 * @uses  \ReflectionClass
	 *
	 * @since 2.0.0
	 *
	 * @param string $provider
	 * @param string $request
	 * @param array  $args     Entity instantiation arguments.
	 *
	 * @return OptionsAbstract|MailerAbstract|AuthAbstract|null
	 */
	protected function get_entity( $provider, $request, $args = []  ) {

		$provider = sanitize_key( $provider );
		$request  = sanitize_text_field( $request );
		$path     = $this->get_provider_path( $provider );
		$entity   = null;

		if ( empty( $path ) ) {
			return $entity;
		}

		try {
			$reflection = new \ReflectionClass( $path . $request );

			if ( file_exists( $reflection->getFileName() ) ) {
				$class  = $path . $request;
				$entity = new $class( ...$args );
			}
		}
		catch ( \Exception $e ) {
			Debug::set( "There was a problem while retrieving {$request} for {$provider}: {$e->getMessage()}" );
			$entity = null;
		}

		return apply_filters( 'easy_wp_smtp_providers_loader_get_entity', $entity, $provider, $request );
	}

	/**
	 * Get supports options for all mailers.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_supports_all() {

		$supports = [];

		foreach ( $this->get_providers() as $provider => $path ) {
			$option = $this->get_options( $provider );

			if ( ! $option instanceof OptionsAbstract ) {
				continue;
			}

			$mailer_slug     = $option->get_slug();
			$mailer_supports = $option->get_supports();

			if ( empty( $mailer_slug ) || empty( $mailer_supports ) ) {
				continue;
			}

			$supports[ $mailer_slug ] = $mailer_supports;
		}

		return apply_filters( 'easy_wp_smtp_providers_loader_get_supports_all', $supports );
	}
}
