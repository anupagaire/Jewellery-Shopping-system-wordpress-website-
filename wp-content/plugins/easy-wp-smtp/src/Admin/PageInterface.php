<?php

namespace EasyWPSMTP\Admin;

/**
 * Class PageInterface defines what should be in each page class.
 *
 * @since 2.0.0
 */
interface PageInterface {

	/**
	 * URL to a tab.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_link();

	/**
	 * Title of a tab.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_title();

	/**
	 * Link label of a tab.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_label();

	/**
	 * Tab content.
	 *
	 * @since 2.0.0
	 */
	public function display();
}
