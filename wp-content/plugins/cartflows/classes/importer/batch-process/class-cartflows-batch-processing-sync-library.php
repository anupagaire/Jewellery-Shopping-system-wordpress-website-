<?php
/**
 * Batch Processing
 *
 * @package Cartflows
 * @since 1.6.15
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use CartflowsAdmin\AdminCore\Inc\AdminHelper;

if ( ! class_exists( 'Cartflows_Batch_Processing_Sync_Library' ) ) :

	/**
	 * Cartflows_Batch_Processing_Sync_Library
	 *
	 * @since 1.0.14
	 */
	class Cartflows_Batch_Processing_Sync_Library {

		/**
		 * Instance
		 *
		 * @since 1.0.14
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Site slug
		 *
		 * @since 1.6.15
		 * @access private
		 * @var string Site slug.
		 */
		private $site_slug;

		/**
		 * Site url
		 *
		 * @since 1.6.15
		 * @access private
		 * @var string Site url.
		 */
		private $site_url;

		/**
		 * Initiator
		 *
		 * @since 1.0.14
		 * @return object initialized object of class.
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
		 * @since 1.0.14
		 */
		public function __construct() {
			$this->site_url  = wcf()->get_site_url();
			$this->site_slug = wcf()->get_site_slug();
		}

		/**
		 * Generate JSON file.
		 *
		 * @since 1.6.15
		 *
		 * @param  string $filename File name.
		 * @param  array  $data     JSON file data.
		 * @return void.
		 */
		public function generate_file( $filename = '', $data = array() ) {
			$file = CARTFLOWS_DIR . 'admin-core/assets/importer-data/' . $filename . '.json';

			Cartflows_Helper::get_filesystem()->put_contents( $file, wp_json_encode( $data ) );
		}

		/**
		 * Import
		 *
		 * @since 1.0.14
		 * @since 1.6.15 Added page no.
		 *
		 * @param  integer $page Page number.
		 * @param string  $templates templates category to fetch.
		 * @return array
		 */
		public function import_sites( $page = 1, $templates = '' ) {

			$api_args = array(
				'timeout' => 30, //phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
			);

			$sites_and_pages = array();

			wcf()->logger->sync_log( 'Requesting ' . $page );

			update_site_option( 'cartflows-batch-status-string', 'Requesting ' . $page, 'no' );

			$suffix = 'store-checkout' === $templates ? 'store-checkout-' : '';

			$query_args = apply_filters(
				'cartflows_import_query_args',
				array(
					'per_page' => 100,
					'page'     => $page,
				)
			);

			if ( 'store-checkout' === $templates ) {
				$query_args['flow_category'] = array(
					'include_terms' => array( 'store-checkout' ),
				);
			}

			$api_url = add_query_arg( $query_args, $this->site_url . 'wp-json/cartflows-server/v1/flows-and-steps/' );

			$response = wp_remote_get( $api_url, $api_args );

			$is_error = AdminHelper::has_api_error( $response );

			if ( ! $is_error['error'] ) {
				// Retrive the flows from template server.
				$sites_and_pages = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( isset( $sites_and_pages['code'] ) || empty( $sites_and_pages['flows'] ) ) {

					$message = isset( $sites_and_pages['message'] ) ? 'HTTP Request Error: ' . $sites_and_pages['message'] : 'HTTP Request Error!';

					wcf()->logger->sync_log( $message );

				} else {
					$option_name = 'cartflows-' . $suffix . $this->site_slug . '-flows-and-steps-' . $page;

					$all_flows = isset( $sites_and_pages['flows'] ) && ! empty( $sites_and_pages['flows'] ) ? $sites_and_pages['flows'] : '';

					update_site_option( 'cartflows-batch-status-string', 'Storing data for page ' . $page . ' in option ' . $option_name );

					update_site_option( $option_name, $all_flows );

					if ( defined( 'WP_CLI' ) ) {
						$this->generate_file( $option_name, $all_flows );
					}
				}
			} else {
				wcf()->logger->sync_log( 'API Error: ' . $response->get_error_message() );
			}

			wcf()->logger->sync_log( 'Complete storing data for page ' . $page );
			update_site_option( 'cartflows-batch-status-string', 'Complete storing data for page ' . $page, 'no' );

			return $sites_and_pages;
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Batch_Processing_Sync_Library::get_instance();

endif;
