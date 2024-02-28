<?php
/**
 * Batch Processing
 *
 * @package CartFlows
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'CartFlows_Batch_Process' ) ) :

	/**
	 * CartFlows_Batch_Process
	 *
	 * @since 1.0.0
	 */
	class CartFlows_Batch_Process {

		/**
		 * Instance
		 *
		 * @since 1.0.0
		 * @var object Class object.
		 * @access private
		 */
		private static $instance;

		/**
		 * Elementor Batch Instance
		 *
		 * @since 1.1.1 Updated instance name with elementor specific.
		 *
		 * @since 1.0.0
		 * @var object Class object.
		 * @access public
		 */
		public static $batch_instance_elementor;

		/**
		 * Beaver Builder Batch Instance
		 *
		 * @since 1.1.1
		 * @var object Class object.
		 * @access public
		 */
		public static $batch_instance_bb;

		/**
		 * Divi Batch Instance
		 *
		 * @since 1.1.1
		 * @var object Class object.
		 * @access public
		 */
		public static $batch_instance_divi;

		/**
		 * Gutenberg Batch Instance
		 *
		 * @since 1.5.9
		 * @var object Class object.
		 * @access public
		 */
		public static $batch_instance_gb;

		/**
		 * Sites Importer
		 *
		 * @since 1.6.15
		 * @var object Class object.
		 * @access public
		 */
		public static $process_site_importer;

		/**
		 * Last Export Checksums
		 *
		 * @since 1.6.15
		 * @var object Class object.
		 * @access public
		 */
		public $last_export_checksums;

		/**
		 * Initiator
		 *
		 * @since 1.0.0
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
		 * @since 1.0.0
		 */
		public function __construct() {

			// Not BB or Elementor then avoid importer.
			// if ( ! class_exists( '\Elementor\Plugin' ) && ! class_exists( 'FLBuilder' ) ) {
			// return;
			// }
			// Core Helpers - Image.
			require_once ABSPATH . 'wp-admin/includes/image.php';

			// Core Helpers - Batch Processing.
			require_once CARTFLOWS_DIR . 'classes/importer/batch-process/helpers/class-cartflows-importer-image.php';
			require_once CARTFLOWS_DIR . 'classes/importer/batch-process/helpers/class-wp-async-request.php';
			require_once CARTFLOWS_DIR . 'classes/importer/batch-process/helpers/class-wp-background-process.php';

			$default_page_builder = Cartflows_Helper::get_common_setting( 'default_page_builder' );

			// Elementor.
			if ( ( 'elementor' === $default_page_builder ) && class_exists( '\Elementor\Plugin' ) ) {
				// Add "elementor" in import [queue].
				// @todo Remove required `allow_url_fopen` support.
				if ( ini_get( 'allow_url_fopen' ) ) {
					require_once CARTFLOWS_DIR . 'classes/importer/batch-process/class-cartflows-importer-elementor.php';
					require_once CARTFLOWS_DIR . 'classes/importer/batch-process/class-cartflows-importer-elementor-batch.php';
					self::$batch_instance_elementor = new Cartflows_Importer_Elementor_Batch();
				}
			}

			// Beaver Builder.
			if ( ( 'beaver-builder' === $default_page_builder ) && class_exists( 'FLBuilder' ) ) {
				require_once CARTFLOWS_DIR . 'classes/importer/batch-process/class-cartflows-importer-beaver-builder.php';
				require_once CARTFLOWS_DIR . 'classes/importer/batch-process/class-cartflows-importer-beaver-builder-batch.php';
				self::$batch_instance_bb = new Cartflows_Importer_Beaver_Builder_Batch();
			}

			// Divi.
			if ( ( 'divi' === $default_page_builder ) && ( class_exists( 'ET_Builder_Plugin' ) || Cartflows_Compatibility::get_instance()->is_divi_enabled() ) ) {
				require_once CARTFLOWS_DIR . 'classes/importer/batch-process/class-cartflows-importer-divi.php';
				require_once CARTFLOWS_DIR . 'classes/importer/batch-process/class-cartflows-importer-divi-batch.php';
				self::$batch_instance_divi = new Cartflows_Importer_Divi_Batch();
			}

			// Gutenberg.
			if ( ( 'gutenberg' === $default_page_builder ) ) {
				require_once CARTFLOWS_DIR . 'classes/importer/batch-process/class-cartflows-importer-gutenberg.php';
				require_once CARTFLOWS_DIR . 'classes/importer/batch-process/class-cartflows-importer-gutenberg-batch.php';
				self::$batch_instance_gb = new Cartflows_Importer_Gutenberg_Batch();
			}

			require_once CARTFLOWS_DIR . 'classes/importer/batch-process/class-cartflows-batch-processing-sync-library.php';
			require_once CARTFLOWS_DIR . 'classes/importer/batch-process/helpers/class-wp-background-process-cartflows-sync-library.php';
			self::$process_site_importer = new WP_Background_Process_Cartflows_Sync_Library();

			// Start image importing after site import complete.
			add_action( 'cartflows_after_template_import', array( $this, 'start_batch_process' ) );
			add_action( 'cartflows_import_complete', array( $this, 'complete_batch_import' ) );
			add_filter( 'upload_mimes', array( $this, 'custom_upload_mimes' ) ); //phpcs:ignore WordPressVIPMinimum.Hooks.RestrictedHooks.upload_mimes
			add_filter( 'wp_prepare_attachment_for_js', array( $this, 'add_svg_image_support' ), 10, 3 );
			add_action( 'cartflows_before_elementor_import_single_template', array( $this, 'enable_unfiltered_upload_elementor' ) );
			add_action( 'admin_head', array( $this, 'start_importer' ) );
		}

		/**
		 * Start Importer
		 *
		 * @since 1.6.15
		 * @return void
		 */
		public function start_importer() {

			$is_fresh_site = get_site_option( 'cartflows-fresh-site', '' );

			// Process initially for the fresh user.
			if ( isset( $_GET['reset'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				// Process import.
				$this->process_batch();

			} elseif ( empty( $is_fresh_site ) ) {

				// First time user save the data of sites, pages, categories etc from the JSON file.
				$dir        = CARTFLOWS_DIR . 'admin-core/assets/importer-data';
				$list_files = list_files( $dir );
				if ( ! empty( $list_files ) ) {
					$list_files = array_map( 'basename', $list_files );

					foreach ( $list_files as $key => $file_name ) {
						$data = file_get_contents( $dir . '/' . $file_name );
						if ( ! empty( $data ) ) {
							$option_name = str_replace( '.json', '', $file_name );
							update_site_option( $option_name, json_decode( $data, true ) );
						}
					}
				}

				// Also, Trigger the batch to get latest data.
				// If batch failed then user have at least the data from the JSON file.
				$this->process_batch();

				update_site_option( 'cartflows-fresh-site', 'yes', 'no' );

				// If not fresh user then trigger batch import on the transient and option
				// Only on the Astra Sites page.
			} else {

				$current_screen = get_current_screen();

				// Bail if not on Astra Sites screen.
				if ( ! is_object( $current_screen ) && null === $current_screen ) {
					return;
				}

				if ( 'cartflows_page_cartflows_settings' === $current_screen->id ) {

					// Process import.
					$this->process_batch();
				}
			}
		}

		/**
		 * Update Latest Checksums
		 *
		 * Store latest checksum after batch complete.
		 *
		 * @param string $templates templates category to fetch.
		 * @since 1.6.15
		 * @return void
		 */
		public function update_latest_checksums( $templates = '' ) {
			$suffix = 'store-checkout' === $templates ? 'store-checkout-' : '';
			wcf()->logger->sync_log( 'Checkusms updated' );
			$latest_checksums = get_site_option( 'cartflows-' . $suffix . 'last-export-checksums-latest', '' );
			update_site_option( 'cartflows-' . $suffix . 'last-export-checksums', $latest_checksums, 'no' );
		}

		/**
		 * Added .svg files as supported format in the uploader.
		 *
		 * @since 1.1.4
		 *
		 * @param array $mimes Already supported mime types.
		 */
		public function custom_upload_mimes( $mimes ) {

			// Allow SVG files.
			$mimes['svg']  = 'image/svg+xml';
			$mimes['svgz'] = 'image/svg+xml';

			// Allow XML files.
			$mimes['xml'] = 'text/xml';

			return $mimes;
		}

		/**
		 * Add SVG image support
		 *
		 * @since 1.1.4
		 *
		 * @param array  $response    Attachment response.
		 * @param object $attachment Attachment object.
		 * @param array  $meta        Attachment meta data.
		 */
		public function add_svg_image_support( $response, $attachment, $meta ) {
			if ( ! function_exists( 'simplexml_load_file' ) ) {
				return $response;
			}

			if ( ! empty( $response['sizes'] ) ) {
				return $response;
			}

			if ( 'image/svg+xml' !== $response['mime'] ) {
				return $response;
			}

			$svg_path = get_attached_file( $attachment->ID );

			$dimensions = self::get_svg_dimensions( $svg_path );

			$response['sizes'] = array(
				'full' => array(
					'url'         => $response['url'],
					'width'       => $dimensions->width,
					'height'      => $dimensions->height,
					'orientation' => $dimensions->width > $dimensions->height ? 'landscape' : 'portrait',
				),
			);

			return $response;
		}

		/**
		 * Get SVG Dimensions
		 *
		 * @since 1.1.4.
		 *
		 * @param  string $svg SVG file path.
		 * @return array      Return SVG file height & width for valid SVG file.
		 */
		public static function get_svg_dimensions( $svg ) {

			$svg = simplexml_load_file( $svg );

			if ( false === $svg ) {
				$width  = '0';
				$height = '0';
			} else {
				$attributes = $svg->attributes();
				$width      = (string) $attributes->width;
				$height     = (string) $attributes->height;
			}

			return (object) array(
				'width'  => $width,
				'height' => $height,
			);
		}

		/**
		 * Batch Process Complete.
		 *
		 * @return void
		 */
		public function complete_batch_import() {
			wcf()->logger->import_log( '(✓) BATCH Process Complete!' );
		}

		/**
		 * Start Image Import
		 *
		 * @param integer $post_id Post Id.
		 *
		 * @return void
		 */
		public function start_batch_process( $post_id = '' ) {

			$default_page_builder = Cartflows_Helper::get_common_setting( 'default_page_builder' );

			wcf()->logger->import_log( '(✓) BATCH Started!' );
			wcf()->logger->import_log( '(✓) Step ID ' . $post_id );

			// Add "elementor" in import [queue].
			if ( 'beaver-builder' === $default_page_builder && self::$batch_instance_bb ) {

				// Add to queue.
				self::$batch_instance_bb->push_to_queue( $post_id );

				// Dispatch Queue.
				self::$batch_instance_bb->save()->dispatch();

				wcf()->logger->import_log( '(✓) Dispatch "Beaver Builder" Request..' );

			} elseif ( 'elementor' === $default_page_builder && self::$batch_instance_elementor ) {

				// Add to queue.
				self::$batch_instance_elementor->push_to_queue( $post_id );

				// Dispatch Queue.
				self::$batch_instance_elementor->save()->dispatch();

				wcf()->logger->import_log( '(✓) Dispatch "Elementor" Request..' );
			} elseif ( 'divi' === $default_page_builder && self::$batch_instance_divi ) {

				// Add to queue.
				self::$batch_instance_divi->push_to_queue( $post_id );

				// Dispatch Queue.
				self::$batch_instance_divi->save()->dispatch();

				wcf()->logger->import_log( '(✓) Dispatch "Divi" Request..' );
			} elseif ( 'gutenberg' === $default_page_builder && self::$batch_instance_gb ) {

				// Add to queue.
				self::$batch_instance_gb->push_to_queue( $post_id );

				// Dispatch Queue.
				self::$batch_instance_gb->save()->dispatch();

				wcf()->logger->import_log( '(✓) Dispatch "Gutenberg" Request..' );
			} else {
				wcf()->logger->import_log( '(✕) Could not import image due to allow_url_fopen() is disabled!' );
			}
		}

		/**
		 * Set Last Exported Checksum
		 *
		 * @param string $templates templates category to fetch.
		 * @since 1.6.15
		 * @return string Checksums Status.
		 */
		public function set_last_export_checksums( $templates = '' ) {

			if ( ! empty( $this->last_export_checksums ) ) {
				return $this->last_export_checksums;
			}

			$api_args = array(
				'timeout' => 60, //phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
			);

			wcf()->logger->sync_log( wcf()->get_site_url() . 'wp-json/cartflows-server/v1/get-last-export-checksums' );

			$response = wp_remote_get( wcf()->get_site_url() . 'wp-json/cartflows-server/v1/get-last-export-checksums', $api_args );
			if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {
				$result = json_decode( wp_remote_retrieve_body( $response ), true );

				// Set last export checksums.
				if ( ! empty( $result['last_export_checksums'] ) ) {
					$suffix = 'store-checkout' === $templates ? 'store-checkout-' : '';
					update_site_option( 'cartflows-' . $suffix . 'last-export-checksums-latest', $result['last_export_checksums'], 'no' );

					$this->last_export_checksums = $result['last_export_checksums'];
				}
			}

			return $this->last_export_checksums;
		}

		/**
		 * Get Last Exported Checksum Status
		 *
		 * @param string $templates templates category to fetch.
		 * @since 1.6.15
		 * @return string Checksums Status.
		 */
		public function get_last_export_checksums( $templates = '' ) {
			$suffix = 'store-checkout' === $templates ? 'store-checkout-' : '';

			$old_last_export_checksums = get_site_option( 'cartflows-' . $suffix . 'last-export-checksums', '' );

			$new_last_export_checksums = $this->set_last_export_checksums( $templates );

			$checksums_status = 'no';

			if ( empty( $old_last_export_checksums ) ) {
				$checksums_status = 'yes';
			}

			if ( $new_last_export_checksums != $old_last_export_checksums ) {
				$checksums_status = 'yes';
			}

			return apply_filters( 'cartflows_checksums_status', $checksums_status );
		}

		/**
		 * Check Cron Status
		 *
		 * Gets the current cron status by performing a test spawn. Cached for one hour when all is well.
		 *
		 * @since 1.6.15
		 *
		 * @param bool $cache Whether to use the cached result from previous calls.
		 * @return true|WP_Error Boolean true if the cron spawner is working as expected, or a WP_Error object if not.
		 */
		public function test_cron( $cache = true ) {
			global $wp_version;

			if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
				return new WP_Error( 'wp_portfolio_cron_error', esc_html__( 'ERROR! Cron schedules are disabled by setting constant DISABLE_WP_CRON to true.<br/>To start the import process please enable the cron by setting the constant to false. E.g. define( \'DISABLE_WP_CRON\', false );', 'cartflows' ) );
			}

			if ( defined( 'ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON ) {
				return new WP_Error( 'wp_portfolio_cron_error', esc_html__( 'ERROR! Cron schedules are disabled by setting constant ALTERNATE_WP_CRON to true.<br/>To start the import process please enable the cron by setting the constant to false. E.g. define( \'ALTERNATE_WP_CRON\', false );', 'cartflows' ) );
			}

			$cached_status = get_transient( 'cartflows-cron-test-ok' );

			if ( $cache && $cached_status ) {
				return true;
			}

			$sslverify     = version_compare( $wp_version, 4.0, '<' );
			$doing_wp_cron = sprintf( '%.22F', microtime( true ) );

			$cron_request = apply_filters(
				'cron_request',
				array(
					'url'  => site_url( 'wp-cron.php?doing_wp_cron=' . $doing_wp_cron ),
					'key'  => $doing_wp_cron,
					'args' => array(
						'timeout'   => 3,
						'blocking'  => true,
						'sslverify' => apply_filters( 'https_local_ssl_verify', $sslverify ),
					),
				)
			);

			$cron_request['args']['blocking'] = true;

			$result = wp_remote_post( $cron_request['url'], $cron_request['args'] );

			if ( is_wp_error( $result ) ) {
				return $result;
			} elseif ( wp_remote_retrieve_response_code( $result ) >= 300 ) {
				return new WP_Error(
					'unexpected_http_response_code',
					sprintf(
						/* translators: 1: The HTTP response code. */
						__( 'Unexpected HTTP response code: %s', 'cartflows' ),
						intval( wp_remote_retrieve_response_code( $result ) )
					)
				);
			} else {
				set_transient( 'cartflows-cron-test-ok', 1, 3600 );
				return true;
			}

		}

		/**
		 * Get Blocks Total Requests
		 *
		 * @since 1.6.15
		 * @param  string $page_builder_slug  Page builder slug.
		 * @param string $templates templates category to fetch.
		 * @return integer
		 */
		public function get_total_requests( $page_builder_slug = '', $templates = '' ) {
			$page_builder_slug = ( ! empty( $page_builder_slug ) ) ? $page_builder_slug : wcf()->get_site_slug();

			update_site_option( 'cartflows-batch-status-string', 'Getting Total Blocks', 'no' );

			$api_args = array(
				'timeout' => 60, //phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
			);

			$url = wcf()->get_site_url( $page_builder_slug ) . 'wp-json/cartflows-server/v1/get-total-flows/';
			wcf()->logger->sync_log( $url );

			$response = wp_remote_get( $url, $api_args );

			/* Check one more time */
			if ( is_wp_error( $response ) ) {
				$response = wp_remote_get( $url, $api_args );
			}

			if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {
				$total_requests = json_decode( wp_remote_retrieve_body( $response ), true );

				$suffix = 'store-checkout' === $templates ? 'store-checkout-' : '';

				if ( isset( $total_requests['pages'] ) ) {
					update_site_option( 'cartflows-batch-status-string', 'Updated requests ' . $total_requests['pages'], 'no' );

					update_site_option( 'cartflows-' . $suffix . $page_builder_slug . '-requests', $total_requests['pages'], 'no' );

					return $total_requests['pages'];
				}
			} else {

				wcf()->logger->sync_log( 'Client\'s server is blocking requests. May be client\'s server firewall is blocking request. Make sure a firewall or any kind of secuirty pluging is blocking requests or not?' );

				/* Fallback default data if client server is blocking requests */
				$total_requests = array(
					'no_of_items' => 15,
					'pages'       => 1,
					'per_page'    => 100,
				);

				update_site_option( 'cartflows-batch-status-string', 'Updated requests ' . $total_requests['pages'], 'no' );

				update_site_option( 'cartflows-' . $page_builder_slug . '-requests', $total_requests['pages'], 'no' );

				return $total_requests['pages'];
			}
		}

		/**
		 * Process Batch
		 *
		 * @since 1.6.15
		 * @param string $templates templates category to fetch.
		 * @return mixed
		 */
		public function process_batch( $templates = '' ) {

			if ( 'other' === wcf()->get_site_slug() ) {
				if ( defined( 'WP_CLI' ) ) {
					WP_CLI::error( 'Invalid page builder! Please select the page builder to start sync process.' );
				} else {
					return;
				}
			}

			if ( 'no' === $this->get_last_export_checksums( $templates ) ) {
				if ( defined( 'WP_CLI' ) ) {
					WP_CLI::line( 'Library is up to date!' );
				}
				return;
			}

			$status = $this->test_cron();
			if ( is_wp_error( $status ) ) {
				if ( defined( 'WP_CLI' ) ) {
					WP_CLI::line( 'Error! Batch Not Start due to disabled cron events!' );
				}
				update_site_option( 'cartflows-batch-status-string', 'Error! Batch Not Start due to disabled cron events!', 'no' );

				if ( defined( 'WP_CLI' ) ) {
					WP_CLI::line( 'Error! Batch Not Start due to disabled cron events!' );
				} else {
					// For non- WP CLI request return to prevent the request.
					return;
				}
			}

			// Get count.
			$total_requests = $this->get_total_requests();
			if ( $total_requests ) {
				if ( defined( 'WP_CLI' ) ) {
					WP_CLI::line( 'Total Requests ' . $total_requests );
				}

				for ( $page = 1; $page <= $total_requests; $page++ ) {

					if ( defined( 'WP_CLI' ) ) {
						WP_CLI::line( 'Added page ' . $page . ' in queue.' );
					}

					if ( defined( 'WP_CLI' ) ) {
						Cartflows_Batch_Processing_Sync_Library::get_instance()->import_sites( $page, $templates );
					} else {
						self::$process_site_importer->push_to_queue(
							array(
								'page'     => $page,
								'template' => $templates,
								'instance' => Cartflows_Batch_Processing_Sync_Library::get_instance(),
								'method'   => 'import_sites',
							)
						);
					}
				}
			}

			if ( defined( 'WP_CLI' ) ) {
				WP_CLI::line( 'Sync Process Complete.' );
			} else {
				// Dispatch Queue.
				self::$process_site_importer->save()->dispatch();
			}

		}

		/**
		 * Enable the unfiltered upload of the elementor to upload the SVG files.
		 *
		 * @since 1.10.0
		 */
		public function enable_unfiltered_upload_elementor() {

			add_filter( 'elementor/files/allow_unfiltered_upload', '__return_true' );
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	CartFlows_Batch_Process::get_instance();

endif;
