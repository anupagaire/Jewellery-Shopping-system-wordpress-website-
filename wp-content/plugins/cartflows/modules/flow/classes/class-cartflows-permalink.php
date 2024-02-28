<?php
/**
 * Step post type.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Initialization
 *
 * @since 1.0.0
 */
class Cartflows_Permalink {


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

		add_filter( 'post_type_link', array( $this, 'post_type_permalinks' ), 10, 3 );
		add_action( 'init', array( $this, 'rewrite_step_rule' ) );

		if ( ! is_admin() ) {

			add_action( 'pre_get_posts', array( $this, 'add_cpt_post_names_to_main_query' ), 20 );
		}
	}

	/**
	 * Modify permalink
	 *
	 * @param string $post_link post link.
	 * @param array  $post post data.
	 * @param string $leavename leave name.
	 * @return string
	 */
	public function post_type_permalinks( $post_link, $post, $leavename ) {

		if ( isset( $post->post_type ) && CARTFLOWS_STEP_POST_TYPE == $post->post_type ) {

			$flow_id      = get_post_meta( $post->ID, 'wcf-flow-id', true );
			$flow_name    = get_post_field( 'post_name', $flow_id );
			$cf_permalink = Cartflows_Helper::get_permalink_settings();

			if ( isset( $cf_permalink['permalink_structure'] ) && ! empty( $cf_permalink['permalink_structure'] ) ) {

				$sep       = '/';
				$search    = array( $sep . 'cartflows_flow', $sep . '%flowname%', $sep . 'cartflows_step' );
				$replace   = array( $sep . $cf_permalink['permalink_flow_base'], $sep . $flow_name, $sep . $cf_permalink['permalink'] );
				$post_link = str_replace( $search, $replace, $post_link );
			} else {

				// If elementor page preview, return post link as it is.
				if ( isset( $_REQUEST['elementor-preview'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
					return $post_link;
				}

				$structure = get_option( 'permalink_structure' );

				if ( '/%postname%/' === $structure ) {

					$post_link = str_replace( '/' . $post->post_type . '/', '/', $post_link );

				}
			}
		}

		return $post_link;
	}

	/**
	 * Reqrite rules for acrtflows step.
	 */
	public function rewrite_step_rule() {

		$cf_permalink = Cartflows_Helper::get_permalink_settings();

		if ( isset( $cf_permalink['permalink_structure'] ) ) {
			switch ( $cf_permalink['permalink_structure'] ) {
				case '/cartflows_flow/%flowname%/cartflows_step':
					add_rewrite_rule( '^' . $cf_permalink['permalink_flow_base'] . '/([^/]*)/' . $cf_permalink['permalink'] . '/([^\/]*)/?', 'index.php?cartflows_step=$matches[2]', 'top' );
					break;

				case '/cartflows_flow/%flowname%':
					add_rewrite_rule( '^' . $cf_permalink['permalink_flow_base'] . '/([^/]*)/([^/]*)/?', 'index.php?cartflows_step=$matches[2]', 'top' );
					break;

				case '/%flowname%/cartflows_step':
					add_rewrite_rule( '([^/]*)/' . $cf_permalink['permalink'] . '/([^\/]*)/?', 'index.php?cartflows_step=$matches[2]', 'top' );

					break;

				default:
					break;
			}
		}

	}

	/**
	 * Have WordPress match post name to any of our public post types.
	 * All of our public post types can have /post-name/ as the slug, so they need to be unique across all posts.
	 * By default, WordPress only accounts for posts and pages where the slug is /post-name/.
	 *
	 * @param WP_Query $query query statement.
	 */
	public function add_cpt_post_names_to_main_query( $query ) {

		// Bail if this is not the main query.
		if ( ! $query->is_main_query() || is_home() ) {
			return;
		}

		// Bail if post_type is already set in query.
		if ( isset( $query->query['post_type'] ) ) {
			return;
		}

		// Bail if this query doesn't match our very specific rewrite rule.
		if ( ! isset( $query->query['thrive-variations'] )
			&& ! isset( $query->query['page'] )
			&& ! isset( $query->query_vars['page_id'] )
		) {
			return;
		}

		// Bail if we're not querying based on the post name.
		if ( empty( $query->query['name'] ) && empty( $query->query_vars['page_id'] ) ) {
			return;
		}

		// Bail if name is set and query coutt is not 2 and page is not set.
		if ( ! empty( $query->query['name'] ) ) {

			if ( 2 !== count( $query->query ) ) {
				return;
			} else {
				if ( ! isset( $query->query['page'] ) ) {
					return;
				}
			}
		}

		// Bail if page_id is set but name is empty and doing ajax. Case - when step is set as homepage.
		if ( ! empty( $query->query_vars['page_id'] ) && empty( $query->query['name'] ) && ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}

		// Bail if page_id is set but page is not true. Added when step is set as homepage.
		if ( ! empty( $query->query_vars['page_id'] ) && empty( $query->is_page ) && ! empty( $query->query['name'] ) ) {
			return;
		}

		// Add cartflows step post type to existing post type array.
		if ( isset( $query->query_vars['post_type'] ) && is_array( $query->query_vars['post_type'] ) ) {

			$post_types = $query->query_vars['post_type'];

			$post_types[] = CARTFLOWS_STEP_POST_TYPE;

			$query->set( 'post_type', $post_types );

		}

		/**
		 * Add the post type in the main query only if the post type in the query is empty.
		 */
		if ( empty( $query->query_vars['post_type'] ) ) {
			$query->set( 'post_type', array( 'post', 'page', CARTFLOWS_STEP_POST_TYPE ) );
		}
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Permalink::get_instance();
