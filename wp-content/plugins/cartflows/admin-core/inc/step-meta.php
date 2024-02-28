<?php
/**
 * CartFlows Step Meta Helper.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\AdminCore\Inc;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class StepMeta.
 */
class StepMeta {


	/**
	 * Get flow meta options.
	 *
	 * @param int    $step_id step id.
	 * @param string $step_type type.
	 */
	public static function get_meta_settings( $step_id, $step_type ) {

		$settings = array(
			'tabs' => array(),
		);

		/**
		 * Set metabox options
		 */
		switch ( $step_type ) {
			case 'landing':
				$settings = \Cartflows_Landing_Meta_Data::get_instance()->get_settings( $step_id );
				break;
			case 'checkout':
				if ( class_exists( 'WooCommerce' ) ) {
					$settings = \Cartflows_Checkout_Meta_Data::get_instance()->get_settings( $step_id );
				}
				break;
			case 'optin':
				if ( class_exists( 'WooCommerce' ) ) {
					$settings = \Cartflows_Optin_Meta_Data::get_instance()->get_settings( $step_id );
				}
				break;
			case 'thankyou':
				if ( class_exists( 'WooCommerce' ) ) {
					$settings = \Cartflows_Thankyou_Meta_Data::get_instance()->get_settings( $step_id );
				}
				break;
			default:
				break;
		}

		// Added WP Fusion filter compatibility.
		$settings = wcf_apply_filters_deprecated( 'cartflows_' . $step_type . '_step_meta_settings', array( $settings, $step_id ), '1.8.0', 'cartflows_admin_' . $step_type . '_step_meta_settings' );
		$settings = apply_filters( 'cartflows_admin_' . $step_type . '_step_meta_settings', $settings, $step_id );
		return $settings;
	}

}
