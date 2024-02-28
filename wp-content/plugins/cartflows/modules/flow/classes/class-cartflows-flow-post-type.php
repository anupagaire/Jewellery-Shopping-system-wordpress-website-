<?php
/**
 * Flow post type
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
class Cartflows_Flow_Post_Type {


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

		add_action( 'init', array( $this, 'flow_post_type' ) );

		add_action( 'do_meta_boxes', array( $this, 'wcf_change_metabox_position' ) );

		add_filter( 'post_updated_messages', array( $this, 'custom_post_type_post_update_messages' ) );

		add_filter( 'display_post_states', array( $this, 'add_cartflows_post_state' ), 15, 1 );

		add_filter( 'hidden_meta_boxes', array( $this, 'display_flow_slug_meta_box' ), 10, 2 );

		/* View Post URL */
		add_filter( 'post_row_actions', array( $this, 'post_row_actions' ), 10, 2 );
		add_filter( 'preview_post_link', array( $this, 'preview_post_link' ), 10, 2 );
		add_action( 'template_redirect', array( $this, 'redirect_to_step' ), 10 );
	}

	/**
	 * Add CartFlows post status.
	 *
	 * @param array $post_states post data.
	 * @return array
	 */
	public function add_cartflows_post_state( $post_states ) {

		global $post;

		if ( isset( $post->post_type ) && CARTFLOWS_STEP_POST_TYPE === $post->post_type ) {

			$flow_id    = get_post_meta( $post->ID, 'wcf-flow-id', true );
			$flow_title = get_the_title( $flow_id );

			$post_states['cartflows_step'] = '( ' . __( 'Flow: ', 'cartflows' ) . $flow_id . ' | ' . __( 'Name: ', 'cartflows' ) . $flow_title . ')';

		}

		return $post_states;
	}

	/**
	 * Display slugdiv.
	 *
	 * @param array $hidden metaboxes.
	 * @param obj   $screen screen.
	 * @return array
	 */
	public function display_flow_slug_meta_box( $hidden, $screen ) {
		$post_type = $screen->id;
		if ( ! empty( $post_type ) && CARTFLOWS_FLOW_POST_TYPE === $post_type ) {
			$pos = array_search( 'slugdiv', $hidden, true );
			unset( $hidden[ $pos ] );
		}

		return $hidden;
	}

	/**
	 * Create custom post type
	 */
	public function flow_post_type() {

		$labels = array(
			'name'          => esc_html_x( 'Flows', 'flow general name', 'cartflows' ),
			'singular_name' => esc_html_x( 'Flow', 'flow singular name', 'cartflows' ),
			'search_items'  => esc_html__( 'Search Flows', 'cartflows' ),
			'all_items'     => esc_html__( 'All Flows', 'cartflows' ),
			'edit_item'     => esc_html__( 'Edit Flow', 'cartflows' ),
			'view_item'     => esc_html__( 'View Flow', 'cartflows' ),
			'add_new'       => esc_html__( 'Add New', 'cartflows' ),
			'update_item'   => esc_html__( 'Update Flow', 'cartflows' ),
			'add_new_item'  => esc_html__( 'Add New', 'cartflows' ),
			'new_item_name' => esc_html__( 'New Flow Name', 'cartflows' ),
		);

		$args = array(
			'labels'              => $labels,
			'show_in_menu'        => false,
			'public'              => false,  // it's not public, not own permalink.
			'publicly_queryable'  => true,  // you should be able to query it.
			'show_ui'             => true,
			'query_var'           => true,
			'can_export'          => true,
			'show_in_admin_bar'   => false,
			'exclude_from_search' => true,
			'has_archive'         => false,  // it shouldn't have archive page.
			'rewrite'             => false,  // it shouldn't have rewrite rules.
			'supports'            => array( 'title', 'thumbnail', 'slug' ),
			'capability_type'     => 'post',
		);

		if ( ! _is_cartflows_pro() ) {

			$flow_posts = get_posts(
				array(
					'posts_per_page' => 4,
					'post_type'      => CARTFLOWS_FLOW_POST_TYPE,
					'post_status'    => array( 'publish', 'pending', 'draft', 'future', 'private' ),
				)
			);

			if ( is_array( $flow_posts ) ) {

				$flow_count = count( $flow_posts );

				if ( $flow_count > 3 || 3 === $flow_count ) {

					$args['capabilities'] = array(
						'create_posts' => 'do_not_allow',
					);
					$args['map_meta_cap'] = true;

					// Add new notice button.
					add_action( 'admin_print_footer_scripts', array( $this, 'add_new_notice_button' ) );

					// Add the notice popup HTML to admin footer.
					add_action( 'admin_footer', array( $this, 'upgrade_to_pro_notice_popup' ) );
				}
			}
		}

		register_post_type( CARTFLOWS_FLOW_POST_TYPE, $args );
	}

	/**
	 * Show custom add new button.
	 */
	public function add_new_notice_button() {

		$screen = get_current_screen();

		if ( is_object( $screen ) && CARTFLOWS_FLOW_POST_TYPE === $screen->post_type && 'edit-cartflows_flow' === $screen->id ) {
			?>
				<script>
					jQuery('.wrap h1.wp-heading-inline').after('<a type="button" class="wcf-custom-add-new-button button">Add New</a>');
				</script>
			<?php
		}
	}

	/**
	 * Upgrade to pro notice popup.
	 *
	 * @since 1.3.4
	 *
	 * @return void
	 */
	public function upgrade_to_pro_notice_popup() {

		?>
		<div id="cartflows-upgrade-notice-overlay" style="display:none;"></div>
		<div id="cartflows-upgrade-notice-popup" style="display:none;">
			<div class="inner">
				<div class="heading">
					<span><?php esc_html_e( 'Upgrade to CartFlows Pro', 'cartflows' ); ?></span>
					<span class="cartflows-close-popup-button tb-close-icon"></span>
				</div>
				<div class="contents">
					<div class="wcf-notice">
						<p>Upgrade to CartFlows Pro for adding more flows and other features. <a href ="https://cartflows.com/" target="_blank"> Click here</a> to upgrade.</p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Change metabox position.
	 */
	public function wcf_change_metabox_position() {

		remove_meta_box( 'slugdiv', CARTFLOWS_FLOW_POST_TYPE, 'normal' );
		add_meta_box( 'slugdiv', __( 'Slug', 'cartflows' ), 'post_slug_meta_box', CARTFLOWS_FLOW_POST_TYPE, 'side', 'high' );
	}

	/**
	 * Add post raw actions
	 *
	 * @param array $actions actions.
	 * @param array $post post data.
	 * @return array
	 */
	public function post_row_actions( $actions, $post ) {

		$first_step = $this->get_first_step_url( $post );

		if ( $first_step && isset( $actions['view'] ) ) {

			$actions['view'] = '<a href="' . $first_step . '">' . __( 'View', 'cartflows' ) . '</a>';
		}

		return $actions;
	}

	/**
	 * Returns previous post link
	 *
	 * @param string $prev_link previous link.
	 * @param array  $post post data.
	 * @return string
	 */
	public function preview_post_link( $prev_link, $post ) {

		if ( $this->is_flow_post_type( $post ) ) {

			$first_step = $this->get_first_step_url( $post );

			if ( $first_step ) {

				return $first_step;
			}

			return '';
		}

		return $prev_link;
	}

	/**
	 * Check if post type is flow
	 *
	 * @param array $post post data.
	 * @return bool
	 */
	public function is_flow_post_type( $post ) {

		if ( isset( $post ) && CARTFLOWS_FLOW_POST_TYPE === $post->post_type ) {

			return true;
		}

		return false;
	}

	/**
	 * Redirect to first step
	 *
	 * @return void
	 */
	public function redirect_to_step() {

		global $post;

		$first_step = $this->get_first_step_url( $post );

		if ( $first_step ) {

			wp_safe_redirect( $first_step );
			exit;
		}
	}

	/**
	 * Return first step URL
	 *
	 * @param array $post post data.
	 * @return bool
	 */
	public function get_first_step_url( $post ) {

		if ( $this->is_flow_post_type( $post ) ) {

			$flow_id = $post->ID;
			$title   = $post->post_title;
			$steps   = get_post_meta( $flow_id, 'wcf-steps', true );

			if ( is_array( $steps ) && ! empty( $steps ) && isset( $steps[0]['id'] ) ) {

				return get_permalink( $steps[0]['id'] );
			}
		}

		return false;
	}

	/**
	 * Add Update messages for any custom post type
	 *
	 * @param array $messages Array of default messages.
	 */
	public function custom_post_type_post_update_messages( $messages ) {

		$custom_post_type = get_post_type( get_the_ID() );

		if ( CARTFLOWS_FLOW_POST_TYPE == $custom_post_type ) {

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
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Flow_Post_Type::get_instance();
