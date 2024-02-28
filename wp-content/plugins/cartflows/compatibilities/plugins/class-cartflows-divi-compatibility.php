<?php
/**
 * Divi page builder compatibility
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class for divi page builder compatibility
 */
class Cartflows_Divi_Compatibility {

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

		add_filter( 'cartflows_container_atts', array( $this, 'add_id_for_cartflows_container' ) );
	}

	/**
	 * Add id attribute to cartflows container which is needed to apply style to divi elements.
	 *
	 * @param array $atts container HTML attributes.
	 * @return array
	 */
	public function add_id_for_cartflows_container( $atts ) {

		$atts['id'] = 'page-container';

		return $atts;
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Divi_Compatibility::get_instance();
