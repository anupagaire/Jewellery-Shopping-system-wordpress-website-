<?php
/**
 * Dynamic Flow shortcodes
 *
 * @package CartFlows
 */

/**
 * Initialization
 *
 * @since 1.0.0
 */
class Cartflows_Wd_Flow_Shortcodes {


	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var object _product
	 */
	private static $main_product = array();

	/**
	 * Member Variable
	 *
	 * @var string Add to cart text.
	 */
	private static $add_to_cart_text = '';

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

		add_shortcode( 'cartflows_product_title', array( $this, 'product_title' ) );
		add_shortcode( 'cartflows_product_add_to_cart', array( $this, 'add_to_cart' ) );
	}

	/**
	 * Get all selected products
	 *
	 * @param int $id product id.
	 */
	public function get_product_obj( $id = false ) {

		if ( $id ) {

			if ( empty( self::$main_product[ $id ] ) ) {

				self::$main_product[ $id ] = wc_get_product( $id );
			}

			return self::$main_product[ $id ];
		}

		return null;
	}

	/**
	 * Product title.
	 *
	 * @param array $atts attributes.
	 */
	public function product_title( $atts ) {

		if ( empty( $atts['id'] ) ) {
			return '';
		}

		$product_id = intval( $atts['id'] );
		$product    = $this->get_product_obj( $product_id );

		if ( ! $product ) {
			return '';
		}

		return $product->get_title();
	}

	/**
	 * Show a single product add to card.
	 *
	 * @param array $atts Attributes.
	 */
	public function add_to_cart( $atts ) {

		if ( empty( $atts['id'] ) ) {
			return '';
		}

		$args = array(
			'posts_per_page'      => 1,
			'post_type'           => 'product',
			'post_status'         => ( ! empty( $atts['status'] ) ) ? $atts['status'] : 'publish',
			'ignore_sticky_posts' => 1,
			'no_found_rows'       => 1,
		);

		if ( isset( $atts['id'] ) ) {
			$args['p'] = absint( $atts['id'] );
		}

		// Update the add to cart button text.
		if ( isset( $atts['text'] ) && ! empty( $atts['text'] ) ) {

			self::$add_to_cart_text = sanitize_text_field( $atts['text'] );

			add_filter( 'woocommerce_product_single_add_to_cart_text', array( $this, 'replace_add_to_cart_text' ), 40, 2 );
		}

		// Don't render titles if desired.
		if ( isset( $atts['show_title'] ) && ! $atts['show_title'] ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
		}

		// Change form action to avoid redirect.
		add_filter( 'woocommerce_add_to_cart_form_action', '__return_empty_string' );

		$single_product = new WP_Query( $args );

		// For "is_single" to always make load comments_template() for reviews.
		$single_product->is_single = true;

		ob_start();

		global $product;
		global $wp_query;

		// Backup query object so following loops think this is a product page.
		$previous_wp_query = $wp_query;
		$wp_query          = $single_product; //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		wp_enqueue_script( 'wc-single-product' );

		while ( $single_product->have_posts() ) {

			$single_product->the_post();

			woocommerce_template_single_add_to_cart();

			/*
			?>
			<div class="single-product" data-product-page-preselected-id="<?php echo esc_attr( $preselected_id ); ?>">
				<?php wc_get_template_part( 'content', 'single-product' ); ?>
			</div>
			<?php
			*/
		}

		// Restore $previous_wp_query and reset post data.
		$wp_query = $previous_wp_query; //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		wp_reset_postdata();

		// Remove text change action.
		if ( isset( $atts['text'] ) && ! empty( $atts['text'] ) ) {

			self::$add_to_cart_text = '';

			remove_filter( 'woocommerce_product_single_add_to_cart_text', array( $this, 'replace_add_to_cart_text' ), 40, 2 );
		}

		// Re-enable titles if they were removed.
		if ( isset( $atts['show_title'] ) && ! $atts['show_title'] ) {
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
		}

		remove_filter( 'woocommerce_add_to_cart_form_action', '__return_empty_string' );

		return '<div class="woocommerce wcf-product-atc">' . ob_get_clean() . '</div>';
	}

	/**
	 * Show a single product add to card.
	 *
	 * @param string $add_to_cart_text Button text.
	 * @param obj    $product Product object.
	 *
	 * @return string Add to cart button text.
	 */
	public function replace_add_to_cart_text( $add_to_cart_text, $product ) {

		if ( ! empty( self::$add_to_cart_text ) ) {

			$add_to_cart_text = self::$add_to_cart_text;
		}

		return $add_to_cart_text;
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Wd_Flow_Shortcodes::get_instance();
