<?php

namespace EasyWPSMTP\Providers;

/**
 * Interface ProviderInterface, shared between all current and future providers.
 * Defines required methods across all providers.
 *
 * @since 2.0.0
 */
interface OptionsInterface {

	/**
	 * Get the mailer provider slug.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_slug();

	/**
	 * Get the mailer provider title (or name).
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_title();

	/**
	 * Get the mailer provider description.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_description();

	/**
	 * Get the mailer provider minimum PHP version.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_php_version();

	/**
	 * Get the mailer provider logo URL.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_logo_url();

	/**
	 * Output the mailer provider options.
	 *
	 * @since 2.0.0
	 */
	public function display_options();

	/**
	 * Get the mailer supported settings.
	 *
	 * @since 2.0.0
	 */
	public function get_supports();
}
