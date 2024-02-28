<?php
/**
 * CartFlows Admin
 *
 * @package CartFlows
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'CartFlows_Importer' ) ) :

	/**
	 * CartFlows Import
	 *
	 * @since 1.0.0
	 */
	class CartFlows_Importer {

		/**
		 * Instance
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

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

			add_action( 'cartflows_import_complete', array( $this, 'clear_cache' ) );
		}

		/**
		 * Get flow export data
		 *
		 * @since 1.1.4
		 *
		 * @param  integer $flow_id Flow ID.
		 * @return array
		 */
		public function get_flow_export_data( $flow_id ) {

			$export_all = apply_filters( 'cartflows_export_all', true );

			$new_steps = array();
			$steps     = get_post_meta( $flow_id, 'wcf-steps', true );
			if ( is_array( $steps ) ) {
				foreach ( $steps as $key => $step ) {

					// Add step post meta.
					$all_meta = get_post_meta( $step['id'] );

					$exclude_step_meta_keys = Cartflows_Helper::get_instance()->get_meta_keys_to_exclude_from_import( $step['id'] );

					if ( is_array( $all_meta ) ) {

						foreach ( $exclude_step_meta_keys as $meta_key ) {

							if ( in_array( $meta_key, array_keys( $all_meta ), true ) ) {
								unset( $all_meta[ $meta_key ] );
							}
						}
					}

					// Add single step.
					$step_data_arr = array(
						'title'        => get_the_title( $step['id'] ),
						'type'         => $step['type'],
						'meta'         => $all_meta,
						'post_content' => '',
					);

					if ( $export_all ) {

						$step_post_obj = get_post( $step['id'] );

						$step_data_arr['post_content'] = $step_post_obj->post_content;
					}

					$new_steps[] = $step_data_arr;
				}
			}

			// Add single flow.
			return array(
				'title' => get_the_title( $flow_id ),
				'steps' => $new_steps,
			);
		}

		/**
		 * Get all flow export data
		 *
		 * @since 1.1.4
		 */
		public function get_all_flow_export_data() {

			$query_args = array(
				'post_type'      => CARTFLOWS_FLOW_POST_TYPE,

				// Query performance optimization.
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'posts_per_page' => -1,
			);

			$query = new WP_Query( $query_args );
			$flows = array();
			if ( $query->posts ) {
				foreach ( $query->posts as $key => $post_id ) {
					$flows[] = $this->get_flow_export_data( $post_id );
				}
			}

			return $flows;
		}


		/**
		 * Import flow from the JSON data
		 *
		 * @since 1.6.15
		 * @param  array $flows JSON array.
		 * @return void
		 */
		public function import_from_json_data( $flows ) {
			if ( $flows ) {

				foreach ( $flows as $key => $flow ) {
					$this->import_single_flow_from_json( $flow );
				}
			}
		}

		/**
		 * Import Single Flow from JSON
		 *
		 * @param array $flow flow_data.
		 * @param bool  $return return value.
		 */
		public function import_single_flow_from_json( $flow = array(), $return = false ) {

			$default_page_builder = Cartflows_Helper::get_common_setting( 'default_page_builder' );

			$flow_title = $flow['title'];
			if ( post_exists( $flow['title'] ) ) {
				$flow_title = $flow['title'] . ' Copy';
			}

			// Create post object.
			$new_flow_args = apply_filters(
				'cartflows_flow_importer_args',
				array(
					'post_type'   => CARTFLOWS_FLOW_POST_TYPE,
					'post_title'  => $flow_title,
					'post_status' => 'publish',
				)
			);

			// Insert the post into the database.
			$flow_id = wp_insert_post( $new_flow_args );

			/**
			 * Fire after flow import
			 *
			 * @since 1.6.15
			 * @param int $flow_id Flow ID.
			 * @param array $new_flow_args Flow post args.
			 * @param array $flows Flow JSON data.
			 */
			do_action( 'cartflows_flow_imported', $flow_id, $new_flow_args, $flow );

			if ( $flow['steps'] ) {

				$exclude_meta_keys = Cartflows_Helper::get_instance()->get_meta_keys_to_exclude_from_import();

				foreach ( $flow['steps'] as $key => $step ) {

					$new_all_meta = array();
					if ( is_array( $step['meta'] ) ) {

						foreach ( $step['meta'] as $meta_key => $mvalue ) {

							if ( in_array( $meta_key, $exclude_meta_keys, true ) ) {
								continue;
							}

							if ( is_serialized( $mvalue[0], true ) ) {
								$meta_value = maybe_unserialize( stripslashes( $mvalue[0] ) );
							} else {
								$meta_value = $mvalue[0];
							}

							if ( '_elementor_data' === $meta_key ) {

								if ( is_array( $meta_value ) ) {
									$meta_value = wp_slash( wp_json_encode( $meta_value ) );
								} else {
									$meta_value = wp_slash( $meta_value );
								}
							}

							/**
							 * Commented the below code and added the above code check and convert the elementor data into the proper format.
							 * Kept this code for future code reference. Remove it after two updates.
							 * $meta_value = maybe_unserialize( $mvalue[0] );
							 * */

							$new_all_meta[ $meta_key ] = $meta_value;

						}
					}
					$new_step_args = apply_filters(
						'cartflows_step_importer_args',
						array(
							'post_type'    => CARTFLOWS_STEP_POST_TYPE,
							'post_title'   => $step['title'],
							'post_status'  => 'publish',
							'meta_input'   => $new_all_meta,
							'post_content' => isset( $step['post_content'] ) ? wp_slash( wp_json_encode( $step['post_content'] ) ) : '',
						)
					);

					$new_step_id = wp_insert_post( $new_step_args );

					/**
					 * Fire after step import
					 *
					 * @since 1.6.15
					 * @param int $new_step_id step ID.
					 * @param int $flow_id flow ID.
					 * @param array $new_step_args Step post args.
					 * @param array $flow_steps Flow steps.
					 * @param array $flows All flows JSON data.
					 */
					do_action( 'cartflows_step_imported', $new_step_id, $flow_id, $new_step_args, $flow['steps'] );

					// Insert post meta.
					update_post_meta( $new_step_id, 'wcf-flow-id', $flow_id );

					$step_taxonomy = CARTFLOWS_TAXONOMY_STEP_TYPE;
					$current_term  = term_exists( $step['type'], $step_taxonomy );

					$step_slug = $step['type'];

					if ( isset( $current_term['term_id'] ) ) {
						// Set type object.
						$data      = get_term( $current_term['term_id'], $step_taxonomy );
						$step_slug = $data->slug;
					}

					// Set term step type.
					wp_set_object_terms( $new_step_id, $step_slug, $step_taxonomy );

					// Set type.
					update_post_meta( $new_step_id, 'wcf-step-type', $step_slug );

					// Set flow.
					wp_set_object_terms( $new_step_id, 'flow-' . $flow_id, CARTFLOWS_TAXONOMY_STEP_FLOW );

					self::get_instance()->set_step_to_flow( $flow_id, $new_step_id, $step['title'], $step_slug );

					if ( apply_filters( 'cartflows_enable_imported_content_processing', true ) ) {
						if ( isset( $step['post_content'] ) && ! empty( $step['post_content'] ) ) {

							// Download and replace images.
							$content = $this->get_content( $step['post_content'] );

							// Update post content.
							wp_update_post(
								array(
									'ID'           => $new_step_id,
									'post_content' => wp_slash( wp_json_encode( $content ) ),
								)
							);
						}

						// Elementor Data.
						if ( ( 'elementor' === $default_page_builder ) && class_exists( '\Elementor\Plugin' ) ) {
							// Add "elementor" in import [queue].
							// @todo Remove required `allow_url_fopen` support.
							if ( ini_get( 'allow_url_fopen' ) && isset( $step['meta']['_elementor_data'] ) ) {
								$obj = new \Elementor\TemplateLibrary\CartFlows_Importer_Elementor();
								$obj->import_single_template( $new_step_id );
							}
						}

						// Beaver Builder.
						if ( ( 'beaver-builder' === $default_page_builder ) && class_exists( 'FLBuilder' ) ) {
							if ( isset( $step['meta']['_fl_builder_data'] ) ) {
								CartFlows_Importer_Beaver_Builder::get_instance()->import_single_post( $new_step_id );
							}
						}
					}
				}
			}

			if ( $return ) {
				return array(
					'flow_id'   => $flow_id,
					'edit_link' => admin_url( 'post.php?action=edit&post=' . $flow_id ),
				);
			}
		}

		/**
		 * Download and Replace hotlink images
		 *
		 * @since 1.6.15
		 *
		 * @param  string $content Mixed post content.
		 * @return array           Hotlink image array.
		 */
		public function get_content( $content = '' ) {

			$content = stripslashes( $content );

			// Extract all links.
			$all_links = wp_extract_urls( $content );

			// Not have any link.
			if ( empty( $all_links ) ) {
				return $content;
			}

			$link_mapping = array();
			$image_links  = array();
			$other_links  = array();

			// Extract normal and image links.
			foreach ( $all_links as $key => $link ) {
				if ( preg_match( '/^((https?:\/\/)|(www\.))([a-z0-9-].?)+(:[0-9]+)?\/[\w\-]+\.(jpg|png|gif|jpeg)\/?$/i', $link ) ) {

					// Get all image links.
					// Avoid *-150x, *-300x and *-1024x images.
					if (
						false === strpos( $link, '-150x' ) &&
						false === strpos( $link, '-300x' ) &&
						false === strpos( $link, '-1024x' )
					) {
						$image_links[] = $link;
					}
				} else {

					// Collect other links.
					$other_links[] = $link;
				}
			}

			// Step 1: Download images.
			if ( ! empty( $image_links ) ) {
				foreach ( $image_links as $key => $image_url ) {
					// Download remote image.
					$image            = array(
						'url' => $image_url,
						'id'  => 0,
					);
					$downloaded_image = CartFlows_Import_Image::get_instance()->import( $image );

					// Old and New image mapping links.
					$link_mapping[ $image_url ] = $downloaded_image['url'];
				}
			}

			// Step 3: Replace mapping links.
			foreach ( $link_mapping as $old_url => $new_url ) {
				$content = str_replace( $old_url, $new_url, $content );

				// Replace the slashed URLs if any exist.
				$old_url = str_replace( '/', '/\\', $old_url );
				$new_url = str_replace( '/', '/\\', $new_url );
				$content = str_replace( $old_url, $new_url, $content );
			}

			return $content;
		}

		/**
		 * Clear Cache.
		 *
		 * @since 1.0.0
		 */
		public function clear_cache() {
			// Clear 'Elementor' file cache.
			if ( class_exists( '\Elementor\Plugin' ) ) {
				\Elementor\Plugin::$instance->files_manager->clear_cache();
			}
		}

		/**
		 * Set steps to the flow
		 *
		 * @param integer $flow_id     Flow ID.
		 * @param integer $new_step_id New step ID.
		 * @param string  $step_title    Flow Type.
		 * @param string  $step_slug Flow Type.
		 */
		public function set_step_to_flow( $flow_id, $new_step_id, $step_title, $step_slug ) {
			// Update steps for the current flow.
			$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );

			if ( ! is_array( $flow_steps ) ) {
				$flow_steps = array();
			}

			$flow_steps[] = array(
				'id'    => $new_step_id,
				'title' => $step_title,
				'type'  => $step_slug,
			);
			update_post_meta( $flow_id, 'wcf-steps', $flow_steps );
			wcf()->logger->import_log( '(✓) Updated flow steps post meta key \'wcf-steps\' ' . wp_json_encode( $flow_steps ) );
		}

		/**
		 * Create step for given flow.
		 *
		 * @param int $flow_id flow ID.
		 * @param int $step_type step type.
		 * @param int $step_title step title.
		 * @since 1.0.0
		 *
		 * @return mixed
		 */
		public function create_step( $flow_id, $step_type, $step_title ) {

			$args = array(
				'post_type'   => CARTFLOWS_STEP_POST_TYPE,
				'post_title'  => $step_title,
				'post_status' => 'publish',
			);

			$new_step_id = wp_insert_post( $args );

			if ( $new_step_id ) {

				$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );

				if ( ! is_array( $flow_steps ) ) {
					$flow_steps = array();
				}

				$flow_steps[] = array(
					'id'    => $new_step_id,
					'title' => $step_title,
					'type'  => $step_type,
				);

				$flow_steps = Cartflows_Helper::get_instance()->maybe_update_flow_steps( $flow_id, $flow_steps );

				// insert post meta.
				update_post_meta( $new_step_id, 'wcf-flow-id', $flow_id );
				update_post_meta( $new_step_id, 'wcf-step-type', $step_type );

				wp_set_object_terms( $new_step_id, $step_type, CARTFLOWS_TAXONOMY_STEP_TYPE );
				wp_set_object_terms( $new_step_id, 'flow-' . $flow_id, CARTFLOWS_TAXONOMY_STEP_FLOW );
			}

			update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

			return $new_step_id;
		}
	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	CartFlows_Importer::get_instance();

endif;
