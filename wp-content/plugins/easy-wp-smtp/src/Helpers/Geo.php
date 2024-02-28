<?php

namespace EasyWPSMTP\Helpers;

/**
 * Class Geo to work with location, domain, IPs etc.
 *
 * @since 2.0.0
 */
class Geo {

	/**
	 * Get the current site hostname.
	 * In case of CLI we don't have SERVER_NAME, so use host name instead, may be not a domain name.
	 * Examples: example.com, localhost.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public static function get_site_domain() {

		return ! empty( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : wp_parse_url( get_home_url( get_current_blog_id() ), PHP_URL_HOST );
	}
}
