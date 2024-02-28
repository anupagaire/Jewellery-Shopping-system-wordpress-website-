<?php
/**
 * CartFlows Step Meta Base
 *
 * @package CartFlows
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * CartFlows_Meta
 *
 * @since 1.0.0
 */
abstract class Cartflows_Step_Meta_Base {

	/**
	 * Step ID
	 *
	 * @var $step_id
	 */
	private $step_id;

	/**
	 * Options
	 *
	 * @var $options
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
	}

	/**
	 * Get step settings
	 *
	 * @param int   $step_id post ID.
	 * @param array $options options.
	 */
	abstract public function get_settings( $step_id, $options = array() );


	/**
	 * Get Common Tabs.
	 */
	public function common_tabs() {

		$tabs = array(
			'design' => array(
				'title'    => __( 'Design', 'cartflows' ),
				'id'       => 'design',
				'class'    => '',
				'icon'     => 'dashicons-info',
				'priority' => 60,
			),
		);

		return $tabs;

	}

	/**
	 * Script Settings
	 *
	 * @param array $options options.
	 * @param int   $post_id post ID.
	 */
	public function custom_script( $options, $post_id ) {

		$fields = array(
			'custom-script' => array(
				'label' => __( 'Custom Script', 'cartflows' ),
				'name'  => 'custom-script',
				'help'  => esc_html__( 'Custom script lets you add your own custom script on front end of this flow page.', 'cartflows' ),
			),
		);

		return $fields;

	}
}
