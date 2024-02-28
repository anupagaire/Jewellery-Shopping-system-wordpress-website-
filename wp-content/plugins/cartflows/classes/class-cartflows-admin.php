<?php
/**
 * CartFlows Admin.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class Cartflows_Admin.
 */
class Cartflows_Admin {

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
	 */
	public function __construct() {

		$this->init_hooks();
	}

	/**
	 * Init Hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_hooks() {

		if ( ! is_admin() ) {
			return;
		}

		/* Add lite version class to body */
		add_action( 'admin_body_class', array( $this, 'add_admin_body_class' ) );

		add_filter( 'plugin_action_links_' . CARTFLOWS_BASE, array( $this, 'add_action_links' ) );

		add_action( 'admin_init', array( $this, 'flush_rules_after_save_permalinks' ) );

		add_filter( 'post_row_actions', array( $this, 'remove_flow_actions' ), 99, 2 );

		add_action( 'wp_dashboard_setup', array( $this, 'dashboard_widget' ) );

		add_action( 'init', array( $this, 'run_scheduled_docs_job' ) );
		add_action( 'cartflows_update_knowledge_base_data', array( $this, 'cartflows_update_knowledge_base_data' ) );

		$this->do_not_cache_admin_pages_actions();
	}

	/**
	 * Do not cache admin pages actions.
	 *
	 * @since 1.6.13
	 * @return void
	 */
	public function do_not_cache_admin_pages_actions() {

		add_action( 'admin_init', array( $this, 'add_admin_cache_restrict_const' ) );
		add_action( 'admin_head', array( $this, 'add_admin_cache_restrict_meta' ) );
	}

	/**
	 * Run scheduled job for CartFLows knowledge base data.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function run_scheduled_docs_job() {
		if ( false === as_next_scheduled_action( 'cartflows_update_knowledge_base_data' ) && ! wp_installing() ) {
			as_schedule_recurring_action( time(), WEEK_IN_SECONDS, 'cartflows_update_knowledge_base_data' );
		}
	}

	/**
	 * Cartflows's REST knowledge base data.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public static function cartflows_update_knowledge_base_data() {
		$docs_json = json_decode( wp_remote_retrieve_body( wp_remote_get( 'https://cartflows.com//wp-json/powerful-docs/v1/get-docs' ) ) );
		Cartflows_Helper::update_admin_settings_option( 'cartflows_docs_data', $docs_json );
	}

	/**
	 * Init dashboard widgets.
	 */
	public function dashboard_widget() {

		if ( current_user_can( 'manage_options' ) && '1' === get_option( 'wcf_setup_skipped', false ) && '1' !== get_option( 'wcf_setup_complete', false ) ) {
			wp_add_dashboard_widget(
				'cartFlows_setup_dashboard_widget',
				__( 'CartFlows Setup', 'cartflows' ),
				array( $this, 'status_widget' )
			);
		}
	}

	/**
	 * Show status widget.
	 */
	public function status_widget() {

		$admin_url = admin_url( 'index.php' ) . '?page=cartflow-setup&';

		$steps = array(
			'welcome'         => $admin_url . 'step=welcome',
			'page-builder'    => $admin_url . 'step=page-builder',
			'plugin-install'  => $admin_url . 'step=plugin-install',
			'global-checkout' => $admin_url . 'step=global-checkout',
			'optin'           => $admin_url . 'step=optin',
			'ready'           => $admin_url . 'step=ready',
		);

		$exit_step = get_option( 'wcf_exit_setup_step', 'welcome' );

		$incompleted_steps = array_search( $exit_step, array_keys( $steps ), true );
		$remianing_steps   = array_slice( $steps, 0, $incompleted_steps );

		$completed_tasks_count = count( $remianing_steps ) + 1;
		$tasks_count           = count( $steps );
		$button_link           = ! empty( $exit_step ) ? $steps[ $exit_step ] : '';

		$progress_percentage = ( $completed_tasks_count / $tasks_count ) * 100;
		$circle_r            = 6.5;
		$circle_dashoffset   = ( ( 100 - $progress_percentage ) / 100 ) * ( pi() * ( $circle_r * 2 ) );

		wp_enqueue_style( 'cartflows-dashboard-widget', CARTFLOWS_URL . 'admin/assets/css/admin-widget.css', array(), CARTFLOWS_VER );

		?>

		<div class="cartflows-dashboard-widget-finish-setup">
			<span class='progress-wrapper'>
				<svg class="circle-progress" width="17" height="17" version="1.1" xmlns="http://www.w3.org/2000/svg">
				<circle r="6.5" cx="10" cy="10" fill="transparent" stroke-dasharray="40.859" stroke-dashoffset="0"></circle>
				<circle class="bar" r="6.5" cx="190" cy="10" fill="transparent" stroke-dasharray="40.859" stroke-dashoffset="<?php echo esc_attr( $circle_dashoffset ); ?>" transform='rotate(-90 100 100)'></circle>
				</svg>
				<span><?php echo esc_html_e( 'Step', 'cartflows' ); ?> <?php echo esc_html( $completed_tasks_count ); ?> <?php echo esc_html_e( 'of', 'cartflows' ); ?> <?php echo esc_html( $tasks_count ); ?></span>
			</span>

			<div class="description">
				<div class="wcf-left-column">
					<p>
						<?php echo esc_html_e( 'You\'re almost there! Once you complete CartFlows setup you can start receiving orders from flows.', 'cartflows' ); ?>
					</p>
					<a href='<?php echo esc_url( $button_link ); ?>' class='button button-primary'><?php echo esc_html_e( 'Complete Setup', 'cartflows' ); ?></a>
				</div>
				<div class="wcf-right-column">
					<img src="<?php echo esc_url( CARTFLOWS_URL . 'admin/assets/images/cartflows-home-widget.svg' ); ?>" />
				</div>
			</div>
		</div>
		<?php

	}

	/**
	 * Add the clone link to action list for flows row actions
	 *
	 * @param array  $actions Actions array.
	 * @param object $post Post object.
	 *
	 * @return array
	 */
	public function remove_flow_actions( $actions, $post ) {

		if ( current_user_can( 'edit_posts' ) && isset( $post ) && CARTFLOWS_FLOW_POST_TYPE === $post->post_type ) {

			if ( isset( $actions['duplicate'] ) ) { // Duplicate page plugin remove.
				unset( $actions['duplicate'] );
			}

			if ( isset( $actions['edit_as_new_draft'] ) ) { // Duplicate post plugin remove.
				unset( $actions['edit_as_new_draft'] );
			}
		}

		return $actions;
	}

	/**
	 *  After save of permalinks.
	 */
	public static function flush_rules_after_save_permalinks() {

		$has_saved_permalinks = get_option( 'cartflows_permalink_refresh' );
		if ( $has_saved_permalinks ) {
			// Required to flush rules when permalink changes.
			flush_rewrite_rules(); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.flush_rewrite_rules_flush_rewrite_rules
			delete_option( 'cartflows_permalink_refresh' );
		}

	}

	/**
	 * Show action on plugin page.
	 *
	 * @param  array $links links.
	 * @return array
	 */
	public function add_action_links( $links ) {

		$mylinks = array(
			'<a target="_blank" href="' . esc_url( 'https://cartflows.com/docs/?utm_source=plugin-page&utm_medium=free-cartflows&utm_campaign=go-pro' ) . '">' . __( 'Docs', 'cartflows' ) . '</a>',
		);

		if ( current_user_can( 'cartflows_manage_settings' ) ) {

			$default_url = add_query_arg(
				array(
					'page'     => CARTFLOWS_SLUG,
					'settings' => true,
				),
				admin_url( 'admin.php' )
			);

			array_unshift( $mylinks, '<a href="' . esc_url( $default_url ) . '">' . __( 'Settings', 'cartflows' ) . '</a>' );
		}

		if ( ! _is_cartflows_pro() ) {
			array_push( $mylinks, '<a style="color: #39b54a; font-weight: 700;" target="_blank" href="' . esc_url( 'https://cartflows.com/pricing/?utm_source=plugin-page&utm_medium=free-cartflows&utm_campaign=go-pro' ) . '"> Go Pro </a>' );
		}

		return array_merge( $links, $mylinks );
	}

	/**
	 * Check is flow admin.
	 *
	 * @since 1.0.0
	 * @return boolean
	 */
	public static function is_flow_edit_admin() {

		$current_screen = get_current_screen();

		if (
			is_object( $current_screen ) &&
			isset( $current_screen->post_type ) &&
			( CARTFLOWS_FLOW_POST_TYPE === $current_screen->post_type ) &&
			isset( $current_screen->base ) &&
			( 'post' === $current_screen->base )
		) {
			return true;
		}
		return false;
	}

	/**
	 * Admin body classes.
	 *
	 * Body classes to be added to <body> tag in admin page
	 *
	 * @param String $classes body classes returned from the filter.
	 * @return String body classes to be added to <body> tag in admin page
	 */
	public function add_admin_body_class( $classes ) {

		$classes .= ' cartflows-' . CARTFLOWS_VER;

		if ( isset( $_GET['action'] ) && in_array( sanitize_text_field( wp_unslash( $_GET['action'] ) ), array( 'wcf-log', 'wcf-license' ) ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$classes .= ' wcf-debug-page ';
		}

		return $classes;
	}

	/**
	 * Check allowed screen for notices.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function allowed_screen_for_notices() {

		$screen          = get_current_screen();
		$screen_id       = $screen ? $screen->id : '';
		$allowed_screens = array(
			'toplevel_page_cartflows',
			'edit-cartflows_flow',
		);

		if ( in_array( $screen_id, $allowed_screens, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Function to add the meta and headers for not to cache the CartFlows wp-admin page.
	 *
	 * @return void
	 */
	public function add_admin_cache_restrict_meta() {

		if ( ! $this->allowed_screen_for_notices() ) {
			return;
		}

		echo '<!-- Added by CartFlows -->';
		echo '<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" /> <meta http-equiv="Pragma" content="no-cache" /> <meta http-equiv="Expires" content="0" />';
		echo '<!-- Added by CartFlows -->';
	}

	/**
	 * Function to add the Do Not Cache Constants on the CartFlows admin pages.
	 *
	 * @return void
	 */
	public function add_admin_cache_restrict_const() {
		// Ignoring nonce verification as using SuperGlobal variables on WordPress hooks.
		if ( isset( $_GET['page'] ) && ( 'cartflows' === sanitize_text_field( $_GET['page'] ) || false !== strpos( sanitize_text_field( $_GET['page'] ), 'cartflows_' ) ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wcf()->utils->get_cache_headers();
		}
	}
}

Cartflows_Admin::get_instance();
