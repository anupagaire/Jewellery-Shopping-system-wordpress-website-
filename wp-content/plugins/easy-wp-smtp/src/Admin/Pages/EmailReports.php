<?php

namespace EasyWPSMTP\Admin\Pages;

use EasyWPSMTP\Admin\ParentPageAbstract;

/**
 * Class EmailReports.
 *
 * @since 2.1.0
 */
class EmailReports extends ParentPageAbstract {

	/**
	 * Page default tab slug.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $default_tab = 'reports';

	/**
	 * Slug of a page.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $slug = 'reports';

	/**
	 * Link label of a page.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Email Reports', 'easy-wp-smtp' );
	}

	/**
	 * Title of a page.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_title() {

		return $this->get_label();
	}
}
