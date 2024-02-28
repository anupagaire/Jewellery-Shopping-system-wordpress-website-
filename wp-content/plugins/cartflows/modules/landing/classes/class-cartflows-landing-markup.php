<?php
/**
 * Markup
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Checkout Markup
 *
 * @since 1.0.0
 */
class Cartflows_Landing_Markup {


	/**
	 * Member Variable
	 *
	 * @var object instance
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

		add_action( 'pre_get_posts', array( $this, 'wcf_pre_get_posts' ) );
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );

		if ( is_admin() ) {
			add_filter( 'wp_dropdown_pages', array( $this, 'add_steps_to_dropdown_pages' ) );
		}
	}

	/**
	 *  Add landing pages in WordPress reading section.
	 *
	 * @param array $output output.
	 *
	 * @return array.
	 */
	public function add_steps_to_dropdown_pages( $output ) {

		global $pagenow;

		if ( ( 'options-reading.php' === $pagenow || 'customize.php' === $pagenow ) && preg_match( '#page_on_front#', $output ) ) {

			$args = array(
				'post_type'   => CARTFLOWS_STEP_POST_TYPE,
				'numberposts' => 100,
				'meta_query'  => array( //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => 'wcf-step-type',
						'value'   => array( 'landing', 'checkout', 'optin' ),
						'compare' => 'IN',
					),
				),
			);

			$landing_pages = get_posts( $args );

			if ( is_array( $landing_pages ) && ! empty( $landing_pages ) ) {

				$cartflows_custom_option = '';

				$front_page_id = get_option( 'page_on_front' );

				foreach ( $landing_pages as $key => $landing_page ) {

					$selected = selected( $front_page_id, $landing_page->ID, false );

					$cartflows_custom_option .= "<option value=\"{$landing_page->ID}\"{$selected}>{$landing_page->post_title} ( #{$landing_page->ID} - CartFlows )</option>";
				}

				$cartflows_custom_option .= '</select>';

				$output = str_replace( '</select>', $cartflows_custom_option, $output );
			}
		}

		return $output;
	}

	/**
	 * Set post query.
	 *
	 * @param WP_Query $query post query.
	 */
	public function wcf_pre_get_posts( $query ) {

		if ( $query->is_main_query() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {

			$post_type = $query->get( 'post_type' );

			$page_id = (int) $query->get( 'page_id' );

			if ( empty( $post_type ) && ! empty( $page_id ) ) {
				$post_type_from_id = get_post_type( $page_id );

				/**
				 * Add the CartFlows post type in the main query.
				 * Skip the rest of the post types which are not of CartFlows.
				 */
				if ( CARTFLOWS_STEP_POST_TYPE === $post_type_from_id ) {
					$query->set( 'post_type', $post_type_from_id );
				}
			}
		}
	}

	/**
	 * Redirect to homepage if landing page set as home page.
	 */
	public function template_redirect() {

		if ( ! wcf()->utils->is_step_post_type() ) {
			return;
		}

		$compatibiliy = Cartflows_Compatibility::get_instance();

		// Do not redirect for page builder preview.
		if ( $compatibiliy->is_page_builder_preview() ) {
			return;
		}

		global $post;

		if ( is_singular() && ! is_front_page() && get_option( 'page_on_front' ) == $post->ID ) {
			wp_safe_redirect( site_url(), 301 );
			exit;
		}
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Landing_Markup::get_instance();
