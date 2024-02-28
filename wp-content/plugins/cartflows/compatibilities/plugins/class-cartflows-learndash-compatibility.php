<?php
/**
 * LearnDash compatibility
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class for LearnDash compatibility
 */
class Cartflows_Learndash_Compatibility {

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
		add_filter( 'learndash_post_args', array( $this, 'cartflows_course_setting_fields' ) );
		add_action( 'template_redirect', array( $this, 'cartflows_override_course_template' ) );

	}

	/**
	 * Override course cartflows template.
	 *
	 * @return bool
	 */
	public function cartflows_override_course_template() {

		// Don't run any code in admin area.
		if ( is_admin() ) {
			return false;
		}

		// Don't override the template if the post type is not `course`.
		if ( ! is_singular( 'sfwd-courses' ) ) {
			return false;
		}

		$course_id = learndash_get_course_id();
		$user_id   = get_current_user_id();
		if ( is_user_logged_in() && sfwd_lms_has_access( $course_id, $user_id ) ) {
			return false;
		}

		$template = learndash_get_course_meta_setting( get_the_id(), 'wcf_course_template' );

		if ( 'none' !== $template && $template ) {
			$link = get_permalink( $template );
			wp_safe_redirect( $link );
			exit;
		}
	}

	/**
	 * Add settings inside learndash settings.
	 *
	 * @param array $fields fields.
	 * @return mixed
	 */
	public function cartflows_course_setting_fields( $fields ) {
		global $post;

		$all_posts = array(
			'none' => __( 'None', 'cartflows' ),
		);

		$landing_steps = get_posts(
			array(
				'posts_per_page' => -1,
				'post_type'      => CARTFLOWS_STEP_POST_TYPE,
				'post_status'    => 'publish',
				'orderby'        => 'ID',
				'order'          => 'DESC',
				'meta_query'     => array( //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => 'wcf-step-type',
						'value'   => array( 'landing', 'checkout', 'optin' ),
						'compare' => 'IN',
					),
				),
			)
		);

		foreach ( $landing_steps as $landing_step ) {
			$all_posts[ $landing_step->ID ] = get_the_title( $landing_step->ID ) . ' ( #' . $landing_step->ID . ')';
		}

		$selected    = get_post_meta( get_the_ID(), 'wcf_course_template', true );
		$description = sprintf(
			/* translators: 1: anchor start, 2: anchor close */
			__( 'Non-enrolled students will redirect to the selected CartFlows template. If you have not created any Flow already, add new Flow from %1$shere%2$s.', 'cartflows' ),
			'<a href="' . esc_url( admin_url( 'edit.php?post_type=' . CARTFLOWS_FLOW_POST_TYPE . '&add-new-flow' ) ) . '">',
			'</a>'
		);

		$fields['sfwd-courses']['fields']['wcf_course_template'] = array(
			'name'            => __( 'Select CartFlows Template for this Course', 'cartflows' ),
			'type'            => 'select',
			'initial_options' => $all_posts,
			'default'         => 'none',
			'help_text'       => $description,
			'show_in_rest'    => true,
			'rest_args'       => array(
				'schema' => array(
					'type' => 'string',
				),
			),
		);

		return $fields;
	}

}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Learndash_Compatibility::get_instance();
