<?php
/**
 * Spectra theme compatibility
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Cartflows_Spectra_Compatibility' ) ) :

	/**
	 * Class for Spectra compatibility
	 */
	class Cartflows_Spectra_Compatibility {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.5.7
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.5.7
		 */
		public function __construct() {

			// Hook: Editor assets.
			add_action( 'enqueue_block_editor_assets', array( $this, 'spectra_editor_assets' ) );
		}

		/**
		 * Clear theme cached CSS if required.
		 */
		public function spectra_editor_assets() {

			wp_localize_script(
				'CF_block-cartflows-block-js',
				'uagb_blocks_info',
				array(
					'uagb_svg_icons'                => Cartflows_Init_Blocks::get_instance()->backend_load_font_awesome_icons(),
					'collapse_panels'               => 'disabled',
					'load_font_awesome_5'           => 'disabled',
					'uag_select_font_globally'      => array(),
					'uag_load_select_font_globally' => array(),
					'font_awesome_5_polyfill'       => array(),
					'spectra_custom_fonts'          => apply_filters( 'spectra_system_fonts', array() ),
				)
			);
		}
	}
	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Spectra_Compatibility::get_instance();

endif;
