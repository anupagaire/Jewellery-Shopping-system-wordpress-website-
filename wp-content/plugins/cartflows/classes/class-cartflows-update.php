<?php
/**
 * Update Compatibility
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Cartflows_Update' ) ) :

	/**
	 * CartFlows Update initial setup
	 *
	 * @since 1.0.0
	 */
	class Cartflows_Update {

		/**
		 * Class instance.
		 *
		 * @access private
		 * @var $instance Class instance.
		 */
		private static $instance;

		/**
		 * Initiator
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
			add_action( 'admin_init', array( $this, 'init' ) );
		}

		/**
		 * Init
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function init() {

			do_action( 'cartflows_update_before' );

			// Get auto saved version number.
			$saved_version = get_option( 'cartflows-version', false );

			// Update auto saved version number.
			if ( ! $saved_version ) {
				update_option( 'cartflows-version', CARTFLOWS_VER );
				return;
			}

			// If equals then return.
			if ( version_compare( $saved_version, CARTFLOWS_VER, '=' ) ) {
				return;
			}

			$this->logger_files();

			if ( version_compare( $saved_version, '1.1.22', '<' ) ) {
				update_option( 'wcf_setup_skipped', true );
			}

			if ( version_compare( $saved_version, '1.2.0', '<' ) ) {

				$this->changed_wp_templates();
			}

			/* Add legacy admin option */
			if ( version_compare( $saved_version, '1.6.0', '<' ) ) {
				update_option( 'cartflows-old-ui-user', true );
				update_option( 'cartflows-legacy-admin', true );
				update_option( 'cartflows-legacy-meta-show-design-options', true );
			}

			/* Updating meta for global checkout migration & Permalinks */
			if ( version_compare( $saved_version, '1.10.0', '<' ) ) {

				$global_checkout = \Cartflows_Helper::get_common_setting( 'global_checkout' );

				if ( $global_checkout ) {
					$flow_id = wcf()->utils->get_flow_id_from_step_id( $global_checkout );
					if ( $flow_id ) {
						update_option( '_cartflows_store_checkout', $flow_id );
						update_option( '_cartflows_old_global_checkout', $global_checkout );
						delete_post_meta( $global_checkout, 'wcf-checkout-products' );
					}
				}

				update_option( 'cartflows_show_weekly_report_email_notice', 'yes' );

				$permalink_settings = Cartflows_Helper::get_admin_settings_option( '_cartflows_permalink', false, false );

				if ( ! $permalink_settings ) {

					$default_settings = array(
						'permalink'           => CARTFLOWS_STEP_POST_TYPE,
						'permalink_flow_base' => CARTFLOWS_FLOW_POST_TYPE,
						'permalink_structure' => '',

					);

					Cartflows_Helper::update_admin_settings_option( '_cartflows_permalink', $default_settings );

				}
			}
			// Update required license key to lowercase. Can be removed after 3 major update.
			if ( version_compare( $saved_version, '1.11.1', '<' ) && _is_cartflows_pro() ) {

				$api_key      = get_option( 'wc_am_client_CartFlows_api_key' );
				$api_key_data = get_option( 'wc_am_client_CartFlows' );

				// Take backup of options.
				update_option(
					'cartflows_license_backup_data',
					array(
						'wc_am_client_cartflows_api_key' => $api_key,
						'wc_am_client_cartflows'         => $api_key_data,
					)
				);

				delete_option( 'wc_am_client_CartFlows_api_key' );
				delete_option( 'wc_am_client_CartFlows' );

				// Delete the cached value for old user ( before v1.11.0 and after v1.11.0 ) so WP can add new value.
				wp_cache_flush();

				if ( $api_key_data ) {

					$new_data = array(
						'wc_am_client_cartflows_api_key' => isset( $api_key_data['wc_am_client_CartFlows_api_key'] ) ? $api_key_data['wc_am_client_CartFlows_api_key'] : $api_key_data['wc_am_client_cartflows_api_key'],
					);

					update_option( 'wc_am_client_cartflows', $new_data );
				}

				if ( $api_key ) {
					update_option( 'wc_am_client_cartflows_api_key', $api_key );
				}
			}

			// Set default page builder to "Other" for those who have set it as "DIVI".
			if ( version_compare( $saved_version, '2.0.0', '<' ) ) {
				$common_settings = get_option( '_cartflows_common', array() );

				if ( is_array( $common_settings ) && 'divi' === $common_settings['default_page_builder'] ) {

					$common_settings['default_page_builder'] = 'elementor';
					update_option( '_cartflows_common', $common_settings );
				}
			}

			// Update auto saved version number.
			update_option( 'cartflows-version', CARTFLOWS_VER );

			// Update cartflows asset version to regenerate the dynamic css. We are using the time() function to add the random number.
			update_option( 'cartflows-assets-version', time() );

			do_action( 'cartflows_update_after' );
		}

		/**
		 * Loading logger files.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function logger_files() {

			if ( ! defined( 'CARTFLOWS_LOG_DIR' ) ) {

				$upload_dir = wp_upload_dir( null, false );

				define( 'CARTFLOWS_LOG_DIR', $upload_dir['basedir'] . '/cartflows-logs/' );
			}

			wcf()->create_files();
		}

		/**
		 * Init
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function changed_wp_templates() {

			global $wpdb;

			$query_results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT  {$wpdb->posts}.ID FROM {$wpdb->posts}  LEFT JOIN {$wpdb->postmeta} ON ( {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id )
                where {$wpdb->posts}.post_type = %s AND  {$wpdb->postmeta}.meta_key = %s AND {$wpdb->postmeta}.meta_value != %s AND {$wpdb->postmeta}.meta_value != %s",
					'cartflows_step',
					'_wp_page_template',
					'cartflows-canvas',
					'cartflows-default'
				)
			); // db call ok; no-cache ok.

			if ( is_array( $query_results ) && ! empty( $query_results ) ) {

				require_once CARTFLOWS_DIR . 'classes/importer/batch-process/class-cartflows-change-template-batch.php';

				wcf()->logger->log( '(✓) Update Templates BATCH Started!' );

				$change_template_batch = new Cartflows_Change_Template_Batch();

				foreach ( $query_results as $query_result ) {

					wcf()->logger->log( '(✓) POST ID ' . $query_result->ID );
					$change_template_batch->push_to_queue( $query_result->ID );
				}

				$change_template_batch->save()->dispatch();
			}
		}
	}
	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Update::get_instance();

endif;
