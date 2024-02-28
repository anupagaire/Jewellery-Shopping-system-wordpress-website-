<?php
/**
 * CartFlows API
 *
 * @package CartFlows
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'CartFlows_API' ) ) :

	/**
	 * CartFlows API
	 *
	 * @since 1.0.0
	 */
	class CartFlows_API {

		/**
		 * Instance
		 *
		 * @access private
		 * @var object Class object.
		 * @since 1.0.0
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
		}

		/**
		 * Get site URL.
		 *
		 * @since 1.0.0
		 *
		 * @return string Site URL.
		 */
		public static function get_site_url() {
			return apply_filters( 'cartflows_templates_url', CARTFLOWS_TEMPLATES_URL . Cartflows_Helper::get_common_setting( 'default_page_builder' ) . '/' );
		}

		/**
		 * Get Client Site Templates Rest API URL.
		 *
		 * @since 1.0.0
		 *
		 * @return string API site URL.
		 */
		public static function get_flow_endpoint_url() {
			return self::get_site_url() . 'wp-json/wp/v2/' . CARTFLOWS_FLOW_POST_TYPE . '/';
		}

		/**
		 * Get Client Site Templates Rest API URL.
		 *
		 * @since 1.0.0
		 *
		 * @return string API site URL.
		 */
		public static function get_step_endpoint_url() {
			return self::get_site_url() . 'wp-json/wp/v2/' . CARTFLOWS_STEP_POST_TYPE . '/';
		}

		/**
		 * Get Client Site Category Rest API URL.
		 *
		 * @since 1.0.0
		 *
		 * @return string API site URL.
		 */
		public static function get_category_endpoint_url() {
			return self::get_site_url() . 'wp-json/wp/v2/' . CARTFLOWS_TAXONOMY_STEP_PAGE_BUILDER . '/';
		}

		/**
		 * Get API request URL.
		 *
		 * @since 1.0.0
		 *
		 * @param string $api_base base of api request.
		 * @return string API site URL.
		 */
		public static function get_request_api_url( $api_base = '' ) {
			return self::get_site_url() . 'wp-json/' . CARTFLOWS_STEP_POST_TYPE . '/v1/' . $api_base;
		}

		/**
		 * License Args.
		 *
		 * @return array License arguments.
		 */
		public static function get_licence_args() {
			return apply_filters( 'cartflows_licence_args', array() );
		}

		/**
		 * License Args.
		 *
		 * @param int $site_id  Site ID.
		 * @return array License arguments.
		 */
		public static function get_flow( $site_id ) {
			$request_params = array(
				'licence_args' => self::get_licence_args(),
				'_fields'      => 'id,slug,status,type,link,title,featured_media,template,cartflows_flow_page_builder,cartflows_flow_type,cartflows_flow_flow,featured_image_url,licence_status,flow_type,flow_type,page_builder,divi_content,post_meta,content',
			);

			$url = add_query_arg( $request_params, self::get_flow_endpoint_url() . $site_id );
			wcf()->logger->import_log( PHP_EOL . 'Template Import URL: ' . $url . PHP_EOL );

			$api_args = array(
				'timeout' => 15, //phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
			);

			$response = self::remote_get( $url, $api_args );

			if ( $response['success'] ) {
				$template = $response['data'];
				return array(
					'title'            => ( isset( $template['title']->rendered ) ) ? $template['title']->rendered : '',
					'post_meta'        => ( isset( $template['post_meta'] ) ) ? $template['post_meta'] : '',
					'data'             => $template,
					'original_content' => isset( $response['data']['divi_content'] ) ? $response['data']['divi_content'] : '',
					'divi_content'     => isset( $response['data']['divi_content'] ) ? $response['data']['divi_content'] : '',
					'message'          => $response['message'], // Your API Key is not valid. Please add valid API Key.
					'success'          => $response['success'],
				);
			}

			return array(
				'title'        => '',
				'post_meta'    => array(),
				'message'      => $response['message'],
				'data'         => $response['data'],
				'divi_content' => '',
				'success'      => $response['success'],
			);
		}

		/**
		 * Get single demo.
		 *
		 * @since 1.0.0
		 *
		 * @param  string $site_id  Template ID of the site.
		 * @return array            Template data.
		 */
		public static function get_template( $site_id ) {
			$request_params = array(
				'licence_args' => self::get_licence_args(),
				'_fields'      => 'id,slug,status,type,link,title,featured_media,template,cartflows_step_page_builder,cartflows_step_type,cartflows_step_flow,featured_image_url,licence_status,flow_type,step_type,page_builder,divi_content,post_meta,content',
			);

			$url = add_query_arg( $request_params, self::get_step_endpoint_url() . $site_id );
			wcf()->logger->import_log( $url );

			$api_args = array(
				'timeout' => 15, //phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
			);

			$response = self::remote_get( $url, $api_args );

			if ( $response['success'] ) {
				$template = $response['data'];
				return array(
					'title'            => ( isset( $template['title']->rendered ) ) ? $template['title']->rendered : '',
					'post_meta'        => ( isset( $template['post_meta'] ) ) ? $template['post_meta'] : '',
					'data'             => $template,
					'original_content' => isset( $response['data']['divi_content'] ) ? $response['data']['divi_content'] : '',
					'divi_content'     => isset( $response['data']['divi_content'] ) ? $response['data']['divi_content'] : '',
					'message'          => $response['message'], // Your API Key is not valid. Please add valid API Key.
					'success'          => $response['success'],
				);
			}

			return array(
				'title'        => '',
				'post_meta'    => array(),
				'message'      => $response['message'],
				'data'         => $response['data'],
				'divi_content' => '',
				'success'      => $response['success'],
			);
		}

		/**
		 * Get Cloud Templates
		 *
		 * @since 1.0.0
		 *
		 * @param  array $args For selecting the demos (Search terms, pagination etc).
		 * @return array        CartFlows list.
		 */
		public static function get_templates( $args = array() ) {

			$request_params = wp_parse_args(
				$args,
				array(
					'page'     => '1',
					'per_page' => '100',
				)
			);

			$url = add_query_arg( $request_params, self::get_step_endpoint_url() );

			$api_args = array(
				'timeout' => 15, //phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
			);

			$response = self::remote_get( $url, $api_args );

			if ( $response['success'] ) {
				$templates_data = $response['data'];
				$templates      = array();
				foreach ( $templates_data as $key => $template ) {

					if ( ! isset( $template->id ) ) {
						continue;
					}

					$templates[ $key ]['id']                 = isset( $template->id ) ? esc_attr( $template->id ) : '';
					$templates[ $key ]['slug']               = isset( $template->slug ) ? esc_attr( $template->slug ) : '';
					$templates[ $key ]['link']               = isset( $template->link ) ? esc_url( $template->link ) : '';
					$templates[ $key ]['date']               = isset( $template->date ) ? esc_attr( $template->date ) : '';
					$templates[ $key ]['title']              = isset( $template->title->rendered ) ? esc_attr( $template->title->rendered ) : '';
					$templates[ $key ]['featured_image_url'] = isset( $template->featured_image_url ) ? esc_url( $template->featured_image_url ) : '';
					$templates[ $key ]['content']            = isset( $template->content->rendered ) ? $template->content->rendered : '';
					$templates[ $key ]['divi_content']       = isset( $template->divi_content ) ? $template->divi_content : '';
					$templates[ $key ]['post_meta']          = isset( $template->post_meta ) ? $template->post_meta : '';
				}

				return array(
					'templates'       => $templates,
					'templates_count' => $response['count'],
					'data'            => $response,
				);
			}

			return array(
				'templates'       => array(),
				'templates_count' => 0,
				'data'            => $response,
			);

		}

		/**
		 * Get categories.
		 *
		 * @since 1.0.0
		 * @param  array $args Arguments.
		 * @return array        Category data.
		 */
		public static function get_categories( $args = array() ) {

			$request_params = apply_filters(
				'cartflows_categories_api_params',
				wp_parse_args(
					$args,
					array(
						'page'     => '1',
						'per_page' => '100',
					)
				)
			);

			$url = add_query_arg( $request_params, self::get_category_endpoint_url() );

			$api_args = apply_filters(
				'cartflows_api_args',
				array(
					'timeout' => 15, //phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
				)
			);

			$response = self::remote_get( $url, $api_args );

			if ( $response['success'] ) {
				$categories_data = $response['data'];
				$categories      = array();

				foreach ( $categories_data as $key => $category ) {
					if ( isset( $category->count ) && ! empty( $category->count ) ) {
						$categories[] = array(
							'id'          => isset( $category->id ) ? absint( $category->id ) : 0,
							'count'       => isset( $category->count ) ? absint( $category->count ) : 0,
							'description' => isset( $category->description ) ? $category->description : '',
							'link'        => isset( $category->link ) ? esc_url( $category->link ) : '',
							'name'        => isset( $category->name ) ? $category->name : '',
							'slug'        => isset( $category->slug ) ? sanitize_text_field( $category->slug ) : '',
							'taxonomy'    => isset( $category->taxonomy ) ? $category->taxonomy : '',
							'parent'      => isset( $category->parent ) ? $category->parent : '',
						);
					}
				}

				return array(
					'categories'       => $categories,
					'categories_count' => $response['count'],
					'data'             => $response,
				);
			}

			return array(
				'categories'       => array(),
				'categories_count' => 0,
				'data'             => $response,
			);
		}

		/**
		 * Remote GET API Request
		 *
		 * @since 1.0.0
		 *
		 * @param  string $url      Target server API URL.
		 * @param  array  $args    Array of arguments for the API request.
		 * @return mixed            Return the API request result.
		 */
		public static function remote_get( $url = '', $args = array() ) {
			$request = wp_remote_get( $url, $args );
			return self::request( $request );
		}

		/**
		 * Remote POST API Request
		 *
		 * @since 1.0.0
		 *
		 * @param  string $url      Target server API URL.
		 * @param  array  $args    Array of arguments for the API request.
		 * @return mixed            Return the API request result.
		 */
		public static function remote_post( $url = '', $args = array() ) {
			$request = wp_remote_post( $url, $args );

			return self::request( $request );
		}

		/**
		 * Site API Request
		 *
		 * @since 1.0.0
		 *
		 * @param  boolean $api_base Target server API URL.
		 * @param  array   $args    Array of arguments for the API request.
		 * @return mixed           Return the API request result.
		 */
		public static function site_request( $api_base = '', $args = array() ) {

			$api_url = self::get_request_api_url( $api_base );

			return self::remote_post( $api_url, $args );
		}

		/**
		 * API Request
		 *
		 * Handle the API request and return the result.
		 *
		 * @since 1.0.0
		 *
		 * @param  array $request    Array of arguments for the API request.
		 * @return mixed           Return the API request result.
		 */
		public static function request( $request ) {

			// Is WP Error?
			if ( is_wp_error( $request ) ) {
				return array(
					'success' => false,
					'message' => $request->get_error_message(),
					'data'    => $request,
					'count'   => 0,
				);
			}

			// Invalid response code.
			if ( wp_remote_retrieve_response_code( $request ) != 200 ) {
				return array(
					'success' => false,
					'message' => $request['response'],
					'data'    => $request,
					'count'   => 0,
				);
			}

			// Get body data.
			$body = wp_remote_retrieve_body( $request );

			// Is WP Error?
			if ( is_wp_error( $body ) ) {
				return array(
					'success' => false,
					'message' => $body->get_error_message(),
					'data'    => $request,
					'count'   => 0,
				);
			}

			// Decode body content.
			$body_decoded = json_decode( $body );

			return array(
				'success' => true,
				'message' => __( 'Request successfully processed!', 'cartflows' ),
				'data'    => (array) $body_decoded,
				'count'   => wp_remote_retrieve_header( $request, 'x-wp-total' ),
			);
		}

	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	CartFlows_API::get_instance();

endif;
