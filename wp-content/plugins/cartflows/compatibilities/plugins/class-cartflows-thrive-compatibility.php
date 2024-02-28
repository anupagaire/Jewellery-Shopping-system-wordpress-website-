<?php
/**
 * Thrive Visual Editor Compatibility
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class for Thrive Visual Editor Compatibility
 */
class Cartflows_Thrive_Compatibility {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 *  Constructor
	 */
	public function __construct() {

		// Add CartFlows post type in the thrive page editor.
		// tve_landing_page_post_types.
		add_filter( 'tve_post_type_can_use_landing_page', array( $this, 'send_post_type_to_thrive' ) );

		add_filter( 'tcb_can_use_landing_pages', array( $this, 'display_change_template_option' ) );

		add_filter( 'tcb_has_templates_tab', array( $this, 'display_change_template_option' ) );
	}

	/**
	 * Return step post type for Thrive Architect.
	 *
	 * @since 1.0.0
	 * @param array $post_type_pt the current step post type.
	 * @return array $post_type_pt current step post type.
	 */
	public function send_post_type_to_thrive( $post_type_pt ) {

		$post_type_pt[] = CARTFLOWS_STEP_POST_TYPE;

		return $post_type_pt;
	}

	/**
	 * Return true/false to show change template option.
	 *
	 * @since 1.0.0
	 * @param array $bool true/false.
	 * @return array $bool true/false.
	 */
	public function display_change_template_option( $bool ) {

		if ( wcf()->utils->is_step_post_type() ) {
			$bool = true;
		}
		return $bool;
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Thrive_Compatibility::get_instance();
