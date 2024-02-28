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
class Cartflows_Step_Post_Type {


	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var body_classes
	 */
	private $body_classes = array();

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

		add_action( 'init', array( $this, 'step_post_type' ) );
		add_action( 'init', array( $this, 'add_wp_templates_support' ) );
		add_filter( 'post_updated_messages', array( $this, 'post_update_messages' ) );

		add_filter( 'wp_unique_post_slug', array( $this, 'prevent_slug_duplicates' ), 10, 6 );

		add_filter( 'template_include', array( $this, 'load_page_template' ), 90 );
		add_action( 'template_redirect', array( $this, 'query_fix' ), 3 );

		add_action( 'admin_init', array( $this, 'disallowed_admin_all_steps_view' ) );
	}

	/**
	 * Trys to load page.php for a header, footer or part theme layout.
	 *
	 * @since 1.0.0
	 * @param string $template The current template to be loaded.
	 * @return string
	 */
	public function load_page_template( $template ) {

		global $post;

		if ( 'string' == gettype( $template ) && is_object( $post ) && CARTFLOWS_STEP_POST_TYPE === $post->post_type ) {

			/**
			 * Remove Next/Prev Navigation
			 * add_filter('next_post_link', '__return_empty_string');
			 * add_filter('previous_post_link', '__return_empty_string');
			 *
			 * $page = locate_template( array( 'page.php' ) );
			 *
			 * if ( ! empty( $page ) ) {
			 *  return $page;
			 * }
			 */

			/* Remove Next / Previous Rel Link */
			remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
			add_filter( 'next_post_rel_link', '__return_empty_string' );
			add_filter( 'previous_post_rel_link', '__return_empty_string' );

			$page_template = get_post_meta( _get_wcf_step_id(), '_wp_page_template', true );

			$page_template = apply_filters( 'cartflows_page_template', $page_template );

			$file = '';

			switch ( $page_template ) {

				case 'cartflows-default':
					$file                 = CARTFLOWS_FLOW_DIR . 'templates/template-default.php';
					$this->body_classes[] = $page_template;
					break;
				case 'cartflows-canvas':
					$file                 = CARTFLOWS_FLOW_DIR . 'templates/template-canvas.php';
					$this->body_classes[] = $page_template;
					break;
				default:
					/**
					 * Remove Next/Prev Navigation
					 */
					add_filter( 'next_post_link', '__return_empty_string' );
					add_filter( 'previous_post_link', '__return_empty_string' );

					$page = locate_template( array( 'page.php' ) );

					if ( ! empty( $page ) ) {
						$file = $page;
					}

					break;

				/**
				* Default:
				* $file  = CARTFLOWS_FLOW_DIR . 'templates/template-default.php';
				* $this->body_classes[] = 'cartflows-default';
				* break;
				*/
			}

			// Just to be safe, we check if the file exist first.
			if ( file_exists( $file ) ) {

				/* Add Body Class */
				add_filter( 'body_class', array( $this, 'body_class' ) );

				return $file;
			} else {
				echo wp_kses_post( $file );
			}
		}

		return $template;
	}

	/**
	 * Body classes.
	 *
	 * @since 1.0.0
	 * @param array $classes Body classes.
	 * @return array
	 */
	public function body_class( $classes = array() ) {

		$classes = array_merge( $classes, $this->body_classes );

		return $classes;
	}

	/**
	 * Create custom post type
	 */
	public function step_post_type() {

		$labels = array(
			'name'          => esc_html_x( 'Steps', 'flow step general name', 'cartflows' ),
			'singular_name' => esc_html_x( 'Step', 'flow step singular name', 'cartflows' ),
			'search_items'  => esc_html__( 'Search Steps', 'cartflows' ),
			'all_items'     => esc_html__( 'All Steps', 'cartflows' ),
			'edit_item'     => esc_html__( 'Edit Step', 'cartflows' ),
			'view_item'     => esc_html__( 'View Step', 'cartflows' ),
			'add_new'       => esc_html__( 'Add New', 'cartflows' ),
			'update_item'   => esc_html__( 'Update Step', 'cartflows' ),
			'add_new_item'  => esc_html__( 'Add New', 'cartflows' ),
			'new_item_name' => esc_html__( 'New Step Name', 'cartflows' ),
		);

		$permalink_settings = Cartflows_Helper::get_permalink_settings();

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'query_var'           => true,
			'can_export'          => true,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_admin_bar'   => true,
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'editor', 'elementor', 'revisions' ),
			'capability_type'     => 'post',
			'capabilities'        => array(
				'create_posts' => 'do_not_allow', // Prior to Wordpress 4.5, this was false.
			),
			'map_meta_cap'        => true,
		);

		if ( isset( $permalink_settings['permalink_structure'] ) && ! empty( $permalink_settings['permalink_structure'] ) ) {
			$args['rewrite'] = array(
				'slug'       => $permalink_settings['permalink_structure'],
				'with_front' => false,
			);

		} elseif ( isset( $permalink_settings['permalink'] ) && ! empty( $permalink_settings['permalink'] ) ) {

			$args['rewrite'] = array(
				'slug'       => $permalink_settings['permalink'],
				'with_front' => false,
			);
		}

		register_post_type( CARTFLOWS_STEP_POST_TYPE, $args );

		// Step Type.
		$args = array(
			'label'        => __( 'Step Type', 'cartflows' ),
			'public'       => false,
			'rewrite'      => false,
			'hierarchical' => false,
		);

		register_taxonomy( CARTFLOWS_TAXONOMY_STEP_TYPE, CARTFLOWS_STEP_POST_TYPE, $args );

		// Step Flow.
		$args = array(
			'label'        => __( 'Step Flow', 'cartflows' ),
			'public'       => false,
			'rewrite'      => false,
			'hierarchical' => false,
		);

		register_taxonomy( CARTFLOWS_TAXONOMY_STEP_FLOW, CARTFLOWS_STEP_POST_TYPE, $args );

		if ( is_admin() ) {
			/**
			 * Register 'Elementor' & 'Beaver Builder' site types.
			 *
			 * @see  self::add_terms();
			 */
			$taxonomy = CARTFLOWS_TAXONOMY_STEP_TYPE;

			$terms = array(
				array(
					'name' => __( 'Landing', 'cartflows' ),
					'slug' => 'landing',
					'args' => array(
						'slug' => 'landing',
					),
				),
				array(
					'name' => __( 'Optin (Woo)', 'cartflows' ),
					'slug' => 'optin',
					'args' => array(
						'slug' => 'optin',
					),
				),
				array(
					'name' => __( 'Checkout (Woo)', 'cartflows' ),
					'slug' => 'checkout',
					'args' => array(
						'slug' => 'checkout',
					),
				),
				array(
					'name' => __( 'Thank You (Woo)', 'cartflows' ),
					'slug' => 'thankyou',
					'args' => array(
						'slug' => 'thankyou',
					),
				),
				array(
					'name' => __( 'Upsell (Woo)', 'cartflows' ),
					'slug' => 'upsell',
					'args' => array(
						'slug' => 'upsell',
					),
				),
				array(
					'name' => __( 'Downsell (Woo)', 'cartflows' ),
					'slug' => 'downsell',
					'args' => array(
						'slug' => 'downsell',
					),
				),
			);

			$this->add_terms( $taxonomy, $terms );

		}
	}

	/**
	 * Add WordPress templates.
	 *
	 * Adds Cartflows templates to steps
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function add_wp_templates_support() {
		add_filter( 'theme_' . CARTFLOWS_STEP_POST_TYPE . '_templates', array( $this, 'add_page_templates' ), 99, 4 );
	}

	/**
	 * Add page templates.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array  $page_templates Array of page templates.
	 *
	 * @param object $wp_theme wp theme.
	 * @param object $post post.
	 *
	 * @return array Page templates.
	 */
	public function add_page_templates( $page_templates, $wp_theme, $post ) {

		$cartflows_templates = array(
			'cartflows-default' => _x( 'CartFlows — Boxed', 'cartflows' ),
			'cartflows-canvas'  => _x( 'Template for Page Builders', 'cartflows' ),
		);

		if ( apply_filters( 'cartflows_show_all_page_templates', false ) ) {
			$cartflows_templates = array_merge( $cartflows_templates, $page_templates );
		}

		return $cartflows_templates;
	}

	/**
	 * Query fixe throwing error on 404 page due our post type changes.
	 * We are setting post_type as empty array to fix the issue.
	 * Ther error was throwing due to redirect_canonical function
	 * This fix is apply for 404 page only
	 */
	public function query_fix() {

		global $wp_query;

		if ( $wp_query->is_404() ) {
			$wp_query->set( 'post_type', array() );
		}
	}

	/**
	 * Prevent slug duplicated
	 *
	 * @param string $slug post slug.
	 * @param int    $post_ID post id.
	 * @param string $post_status post status.
	 * @param string $post_type post type.
	 * @param int    $post_parent post parent id.
	 * @param string $original_slug original slug.
	 * @return string
	 */
	public function prevent_slug_duplicates( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {

		$check_post_types = array(
			'post',
			'page',
			CARTFLOWS_STEP_POST_TYPE,
		);

		if ( ! in_array( $post_type, $check_post_types, true ) ) {
			return $slug;
		}

		if ( CARTFLOWS_STEP_POST_TYPE == $post_type ) {
			// Saving a post, check for duplicates in POST or PAGE post types.
			$post_match = get_page_by_path( $slug, 'OBJECT', 'post' );
			$page_match = get_page_by_path( $slug, 'OBJECT', 'page' );

			if ( $post_match || $page_match ) {
				$slug .= '-2';
			}
		} else {

			// Saving a POST or PAGE, check for duplicates in CARTFLOWS_STEP_POST_TYPE post type.
			$custom_post_type_match = get_page_by_path( $slug, 'OBJECT', CARTFLOWS_STEP_POST_TYPE );

			if ( $custom_post_type_match ) {
				$slug .= '-2';
			}
		}

		return $slug;
	}

	/**
	 * Add Update messages for any custom post type
	 *
	 * @param array $messages Array of default messages.
	 */
	public function post_update_messages( $messages ) {

		$custom_post_type = get_post_type( get_the_ID() );

		if ( CARTFLOWS_STEP_POST_TYPE == $custom_post_type ) {

			$obj                           = get_post_type_object( $custom_post_type );
			$singular_name                 = $obj->labels->singular_name;
			$messages[ $custom_post_type ] = array(
				0  => '', // Unused. Messages start at index 1.
				/* translators: %s: singular custom post type name */
				1  => sprintf( __( '%s updated.', 'cartflows' ), $singular_name ),
				/* translators: %s: singular custom post type name */
				2  => sprintf( __( 'Custom %s updated.', 'cartflows' ), $singular_name ),
				/* translators: %s: singular custom post type name */
				3  => sprintf( __( 'Custom %s deleted.', 'cartflows' ), $singular_name ),
				/* translators: %s: singular custom post type name */
				4  => sprintf( __( '%s updated.', 'cartflows' ), $singular_name ),
				/* translators: %1$s: singular custom post type name ,%2$s: date and time of the revision */
				5  => isset( $_GET['revision'] ) ? sprintf( __( '%1$s restored to revision from %2$s', 'cartflows' ), $singular_name, wp_post_revision_title( (int) $_GET['revision'], false ) ) : false, //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				/* translators: %s: singular custom post type name */
				6  => sprintf( __( '%s published.', 'cartflows' ), $singular_name ),
				/* translators: %s: singular custom post type name */
				7  => sprintf( __( '%s saved.', 'cartflows' ), $singular_name ),
				/* translators: %s: singular custom post type name */
				8  => sprintf( __( '%s submitted.', 'cartflows' ), $singular_name ),
				/* translators: %s: singular custom post type name */
				9  => sprintf( __( '%s scheduled for.', 'cartflows' ), $singular_name ),
				/* translators: %s: singular custom post type name */
				10 => sprintf( __( '%s draft updated.', 'cartflows' ), $singular_name ),
			);
		}

		return $messages;
	}

	/**
	 * Add Terms for Taxonomy.
	 *
	 * => Example.
	 *
	 *  $taxonomy = '{taxonomy}';
	 *  $terms    = array(
	 *                  array(
	 *                      'name'  => 'Landing',
	 *                      'slug'  => 'landing',
	 *                  ),
	 *                  array(
	 *                      'name'  => 'Checkout',
	 *                      'slug'  => 'checkout',
	 *                  ),
	 *              );
	 *
	 *  self::add_terms( $taxonomy, $terms );
	 *
	 * @since 1.0.0
	 * @param string $taxonomy Taxonomy Name.
	 * @param array  $terms    Terms list.
	 * @return void
	 */
	public function add_terms( $taxonomy = '', $terms = array() ) {

		foreach ( $terms as $key => $term ) {

			$term_exist = term_exists( $term['slug'], $taxonomy );

			if ( empty( $term_exist ) ) {

				/**
				 * Add additional args if passed from request.
				 *
				 * @see https://codex.wordpress.org/Function_Reference/wp_insert_term
				 */
				if ( array_key_exists( 'args', $term ) ) {
					wp_insert_term( $term['name'], $taxonomy, $term['args'] );
				} else {

					$term['args'] = array( $term['slug'] );

					wp_insert_term( $term['name'], $taxonomy, $term['args'] );
				}
			}
		}
	}

	/**
	 * Redirect admin pages.
	 *
	 * @return void
	 */
	public function disallowed_admin_all_steps_view() {

		global $pagenow;

		// Check current admin page. If step post type view redirect it to flow.
		if ( 'edit.php' === $pagenow && isset( $_GET['post_type'] ) && CARTFLOWS_STEP_POST_TYPE === $_GET['post_type'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( isset( $_GET['debug'] ) && 'true' === sanitize_text_field( $_GET['debug'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return;
			}

			wp_safe_redirect( admin_url( 'edit.php?post_type=' . CARTFLOWS_FLOW_POST_TYPE ) );
			exit;
		}
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Step_Post_Type::get_instance();
