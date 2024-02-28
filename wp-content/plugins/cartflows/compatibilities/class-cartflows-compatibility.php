<?php
/**
 * Page builder compatibility
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Cartflows_Compatibility' ) ) {

	/**
	 * Class for page builder compatibility
	 */
	class Cartflows_Compatibility {

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

			$this->load_files();

			add_action( 'init', array( $this, 'load_cartflows_theme_support' ), 100 );

			// Override post meta.
			add_action( 'wp', array( $this, 'override_meta' ), 0 );

			add_action( 'wp_enqueue_scripts', array( $this, 'load_fontawesome' ), 10000 );

			// Let WooCommerce know, CartFlows is compatible with HPOS.
			add_action( 'before_woocommerce_init', array( $this, 'declare_woo_hpos_compatibility' ) );
		}

		/**
		 * Load popular theme fallback files.
		 *
		 * @since X.X.X
		 *
		 * @return void
		 */
		public function load_cartflows_theme_support() {

			if ( defined( 'ASTRA_THEME_VERSION' ) ) {

				/**
				 * Astra
				 */
				include_once CARTFLOWS_DIR . 'compatibilities/themes/astra/class-cartflows-astra-compatibility.php';
			}

		}


		/**
		 *  Declare the woo HPOS compatibility.
		 */
		public function declare_woo_hpos_compatibility() {

			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', CARTFLOWS_FILE, true );
			}
		}

		/**
		 *  Load page builder compatibility files
		 */
		public function load_files() {
			if ( class_exists( '\Elementor\Plugin' ) ) {
				require_once CARTFLOWS_DIR . 'compatibilities/plugins/class-cartflows-elementor-compatibility.php';
			}

			if ( $this->is_divi_enabled() ) {
				require_once CARTFLOWS_DIR . 'compatibilities/plugins/class-cartflows-divi-compatibility.php';
			}

			if ( $this->is_bb_enabled() ) {
				require_once CARTFLOWS_DIR . 'compatibilities/plugins/class-cartflows-bb-compatibility.php';
			}

			if ( class_exists( 'TCB_Post' ) ) {
				require_once CARTFLOWS_DIR . 'compatibilities/plugins/class-cartflows-thrive-compatibility.php';
			}

			if ( defined( 'LEARNDASH_VERSION' ) ) {
				require_once CARTFLOWS_DIR . 'compatibilities/plugins/class-cartflows-learndash-compatibility.php';
			}

			// Compatibility to allow of Modern Cart to redirect the user to flow from single product page.
			if ( class_exists( 'ModernCart\Plugin_Loader' ) ) {
				add_filter(
					'moderncart_redirect_after_add_to_cart',
					function( $allow ) {
						return 'yes';
					}
				);
			}
		}

		/**
		 * Check if it is beaver builder enabled.
		 *
		 * @since 1.1.4
		 */
		public function is_bb_enabled() {

			if ( class_exists( 'FLBuilderModel' ) ) {
				return true;
			}

			return false;
		}

		/**
		 *  Check if elementor preview mode is on.
		 */
		public function is_elementor_preview_mode() {

			if ( class_exists( '\Elementor\Plugin' ) ) {

				if ( \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
					return true;
				}
			}

			return false;
		}

		/**
		 *  Get Current Theme.
		 */
		public function get_current_theme() {

			$theme_name = '';
			$theme      = wp_get_theme();

			if ( isset( $theme->parent_theme ) && '' != $theme->parent_theme || null != $theme->parent_theme ) {
				$theme_name = $theme->parent_theme;
			} else {
				$theme_name = $theme->name;
			}
			return $theme_name;
		}

		/**
		 *  Check if it is beaver builder preview mode
		 */
		public function is_bb_preview_mode() {

			if ( class_exists( 'FLBuilderModel' ) ) {
				if ( FLBuilderModel::is_builder_active() ) {
					return true;
				} else {
					return false;
				}
			}

			return false;
		}

		/**
		 *  Check for page builder preview mode.
		 */
		public function is_page_builder_preview() {

			if ( $this->is_elementor_preview_mode() || $this->is_bb_preview_mode() || $this->is_divi_builder_preview() ) {
				return true;
			}

			return false;
		}

		/**
		 *  Check if divi builder enabled.
		 */
		public function is_divi_builder_preview() {

			if ( isset( $_GET['et_fb'] ) && '1' === $_GET['et_fb'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return true;
			}

			return false;
		}

		/**
		 *  Check if divi builder enabled for post id.
		 *
		 * @param int $post_id post id.
		 */
		public function is_divi_builder_enabled( $post_id ) {

			if ( function_exists( 'et_pb_is_pagebuilder_used' ) && et_pb_is_pagebuilder_used( $post_id ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Check if compatibility theme enabled.
		 */
		public function is_compatibility_theme_enabled() {

			$theme = wp_get_theme();

			$is_compatibility = false;

			if ( $this->is_divi_enabled( $theme ) || $this->is_flatsome_enabled( $theme ) || $this->is_pro_enabled( $theme ) || $this->is_kallyas_enabled( $theme ) ) {

				$is_compatibility = true;
			}

			return apply_filters( 'cartflows_is_compatibility_theme', $is_compatibility );
		}

		/**
		 * Check if pro theme enabled.
		 *
		 * @param object $theme theme data.
		 * @return boolean
		 */
		public function is_pro_enabled( $theme = false ) {

			if ( ! $theme ) {
				$theme = wp_get_theme();
			}

			if ( 'Pro' == $theme->name ) {
				return true;
			}

			return false;
		}

		/**
		 * Check if kallyas theme enabled.
		 *
		 * @param object $theme theme data.
		 * @return boolean
		 */
		public function is_kallyas_enabled( $theme = false ) {

			if ( ! $theme ) {
				$theme = wp_get_theme();
			}

			if ( 'Kallyas' == $theme->name ) {
				return true;
			}

			return false;
		}

		/**
		 * Check if divi builder enabled.
		 *
		 * @param object $theme theme data.
		 * @return boolean
		 */
		public function is_divi_enabled( $theme = false ) {

			if ( ! $theme ) {
				$theme = wp_get_theme();
			}

			if ( 'Divi' == $theme->name || 'Divi' == $theme->parent_theme || 'Extra' == $theme->name || 'Extra' == $theme->parent_theme ) {
				return true;
			}

			return false;
		}

		/**
		 * Check if Divi theme is install status.
		 *
		 * @return boolean
		 */
		public function is_divi_theme_installed() {
			foreach ( (array) wp_get_themes() as $theme_dir => $theme ) {
				if ( 'Divi' == $theme->name || 'Divi' == $theme->parent_theme || 'Extra' == $theme->name || 'Extra' == $theme->parent_theme ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * Check if Flatsome enabled.
		 *
		 * @param object $theme theme data.
		 * @return boolean
		 */
		public function is_flatsome_enabled( $theme = false ) {

			if ( ! $theme ) {
				$theme = wp_get_theme();
			}

			if ( 'Flatsome' == $theme->name || 'Flatsome' == $theme->parent_theme ) {
				return true;
			}

			return false;
		}

		/**
		 * Check if The7 enabled.
		 *
		 * @param object $theme theme data.
		 * @return boolean
		 */
		public function is_the_seven_enabled( $theme = false ) {

			if ( ! $theme ) {
				$theme = wp_get_theme();
			}

			if ( 'The7' == $theme->name || 'The7' == $theme->parent_theme ) {
				return true;
			}

			return false;
		}

		/**
		 * Check if OceanWp enabled.
		 *
		 * @param object $theme theme data.
		 * @return boolean
		 */
		public function is_oceanwp_enabled( $theme = false ) {

			if ( ! $theme ) {
				$theme = wp_get_theme();
			}

			if ( 'OceanWP' == $theme->name || 'OceanWP' == $theme->parent_theme ) {
				return true;
			}

			return false;
		}

		/**
		 * Check if Astra enabled.
		 *
		 * @param object $theme theme data.
		 * @return boolean
		 */
		public function is_astra_enabled( $theme = false ) {

			if ( ! $theme ) {
				$theme = wp_get_theme();
			}

			if ( 'Astra' == $theme->name || 'Astra' == $theme->parent_theme ) {
				return true;
			}

			return false;
		}


		/**
		 *  Check for thrive architect edit page.
		 *
		 * @param int $post_id post id.
		 */
		public function is_thrive_edit_page( $post_id ) {

			if ( true === $this->is_thrive_builder_page( $post_id ) ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Check if the page being rendered is the main ID on the editor page.
		 *
		 * @since 1.0.0
		 * @param String $post_id  Post ID which is to be rendered.
		 * @return boolean True if current if is being rendered is not being edited.
		 */
		private function is_thrive_builder_page( $post_id ) {
			$tve  = ( isset( $_GET['tve'] ) && 'true' == $_GET['tve'] ) ? true : false; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$post = isset( $_GET['post'] ) ? intval( wp_unslash( $_GET['post'] ) ) : false; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			return ( true == $tve && $post_id !== $post );
		}

		/**
		 *  Overwrite meta for page
		 */
		public function override_meta() {

			// don't override meta for `elementor_library` post type.
			if ( 'elementor_library' == get_post_type() ) {
				return;
			}

			if ( ! is_singular() ) {
				return;
			}

			global $post;

			// Return if the Post object is empty. We don't want to throw any errors.
			if ( empty( $post ) ) {
				return;
			}

			$post_id   = $post->ID;
			$post_type = get_post_type();

			if ( 'cartflows_step' == $post_type && ( $this->is_elementor_preview_mode()
			|| $this->is_bb_preview_mode() || $this->is_thrive_edit_page( $post_id )
			|| $this->is_divi_builder_enabled( $post_id ) ) ) {

				if ( '' == $post->post_content ) {

					$this->overwrite_template( $post_id );
				}
			}
		}

		/**
		 *  Assign cartflow canvas template to page.
		 *
		 * @param int $post_id post ID.
		 */
		public function overwrite_template( $post_id ) {

			$template = 'cartflows-canvas';
			$key      = '_wp_page_template';

			$record_exists = get_post_meta( $post_id, $key, true );

			if ( 'cartflows-canvas' == $record_exists ) {
				return;
			}

			// As elementor doesn't allow update post meta using update_post_meta, run wpdb query to update post meta.
			if ( class_exists( '\Elementor\Plugin' ) ) {

				global $wpdb;

				if ( '' == $record_exists || ! $record_exists ) {

					$wpdb->insert(
						$wpdb->prefix . 'postmeta',
						array(
							'post_id'    => $post_id,
							'meta_key'   => $key,
							'meta_value' => $template, //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
						)
					);// db call ok;.

					// alternative query to above query.
					// $table = $wpdb->prefix . 'postmeta';
					// $wpdb->query($wpdb->prepare(  "INSERT INTO { $table } ( `post_id`, `meta_key`, 'meta_value' )
					// VALUES ( '$post_id', '$key', '$template' )" ) );// db call ok; no-cache ok.

				} else {

					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_value = %s WHERE meta_key = %s AND post_id = %s;", $template, $key, $post_id ) ); // db call ok; no-cache ok.
				}
			} else {

				update_post_meta( $post_id, $key, $template );
			}
		}

		/**
		 * Load font awesome style from oceanwp on checkout page.
		 */
		public function load_fontawesome() {

			$theme = get_template();

			if ( 'oceanwp' == strtolower( $theme ) && wcf()->utils->is_step_post_type() ) {

				$load_fa = apply_filters( 'cartflows_maybe_load_font_awesome', true );

				if ( $load_fa ) {

					wp_enqueue_style( 'font-awesome', OCEANWP_CSS_DIR_URI . 'third/font-awesome.min.css', array(), CARTFLOWS_VER );
				}

				$custom_css = '
                #oceanwp-cart-sidebar-wrap,
                #owp-qv-wrap{
                    display: none;
                }';

				wp_add_inline_style( 'wcf-frontend-global', $custom_css );
			}
		}
	}
}

Cartflows_Compatibility::get_instance();

