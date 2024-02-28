<?php

namespace EasyWPSMTP\Admin\Pages;

use EasyWPSMTP\Admin\ParentPageAbstract;

/**
 * Class Tools.
 *
 * @since 2.0.0
 */
class Tools extends ParentPageAbstract {

	/**
	 * Slug of a page.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $slug = 'tools';

	/**
	 * Page default tab slug.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $default_tab = 'test';

	/**
	 * Link label of a page.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Tools', 'easy-wp-smtp' );
	}

	/**
	 * Title of a page.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_title() {

		return $this->get_label();
	}
}
