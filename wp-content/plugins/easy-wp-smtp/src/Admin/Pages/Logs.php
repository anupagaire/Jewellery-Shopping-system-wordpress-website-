<?php

namespace EasyWPSMTP\Admin\Pages;

use EasyWPSMTP\Admin\Area;
use EasyWPSMTP\Admin\PageAbstract;
use EasyWPSMTP\WP;

/**
 * Class Logs
 */
class Logs extends PageAbstract {

	/**
	 * Slug of a page.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $slug = 'logs';

	/**
	 * Get the page/tab link.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_link() {

		return add_query_arg(
			'tab',
			$this->slug,
			WP::admin_url( 'admin.php?page=' . Area::SLUG )
		);
	}

	/**
	 * Link label of a tab.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_label() {
		return esc_html__( 'Email Log', 'easy-wp-smtp' );
	}

	/**
	 * Title of a tab.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->get_label();
	}

	/**
	 * Tab content.
	 *
	 * @since 2.1.0
	 */
	public function display() {}
}
