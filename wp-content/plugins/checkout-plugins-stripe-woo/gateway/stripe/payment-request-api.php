<?php
/**
 * Stripe Gateway
 *
 * @package checkout-plugins-stripe-woo
 * @since 1.1.0
 */

namespace CPSW\Gateway\Stripe;

use CPSW\Gateway\Stripe\Card_Payments;
use CPSW\Inc\Helper;
use CPSW\Inc\Traits\Get_Instance;
use WC_Data_Store;
use WC_Subscriptions_Product;
use WC_Validation;
use Exception;

/**
 * Payment Request Api.
 */
class Payment_Request_Api extends Card_Payments {

	use Get_Instance;

	/**
	 * Constructor
	 */
	public function __construct() {
		$settings               = Helper::get_gateway_settings();
		$this->express_checkout = $settings['express_checkout_enabled'];
		if ( 'yes' !== $this->express_checkout || 'yes' !== $settings['enabled'] ) {
			return;
		}
		$this->statement_descriptor = $this->clean_statement_descriptor( $this->get_option( 'statement_descriptor' ) );

		$this->capture_method = Helper::get_setting( 'charge_type', 'cpsw_stripe' );

		$product_page_action   = 'woocommerce_after_add_to_cart_quantity';
		$product_page_priority = 10;

		if ( 'below' === $settings['express_checkout_product_page_position'] || 'inline' === $settings['express_checkout_product_page_position'] ) {
			$product_page_action   = 'woocommerce_after_add_to_cart_button';
			$product_page_priority = 1;
		}

		add_action( $product_page_action, [ $this, 'payment_request_button' ], $product_page_priority );
		add_action( 'woocommerce_proceed_to_checkout', [ $this, 'payment_request_button' ], 1 );

		$checkout_page_action   = 'woocommerce_checkout_before_customer_details';
		$checkout_page_priority = 5;

		if ( 'above-billing' === $settings['express_checkout_checkout_page_position'] ) {
			$checkout_page_action   = 'woocommerce_checkout_billing';
			$checkout_page_priority = 1;
		}

		add_action( $checkout_page_action, [ $this, 'payment_request_button' ], $checkout_page_priority );

		add_filter( 'cpsw_payment_request_localization', [ $this, 'localize_product_data' ] );

		add_action( 'wc_ajax_cpsw_payment_request_checkout', [ $this, 'ajax_checkout' ] );
		add_action( 'wc_ajax_cpsw_get_cart_details', [ $this, 'ajax_get_cart_details' ] );
		add_action( 'wc_ajax_cpsw_add_to_cart', [ $this, 'ajax_add_to_cart' ] );
		add_action( 'wc_ajax_cpsw_selected_product_data', [ $this, 'ajax_selected_product_data' ] );
		add_action( 'wc_ajax_cpsw_update_shipping_address', [ $this, 'ajax_update_shipping_address' ] );
		add_action( 'wc_ajax_cpsw_update_shipping_option', [ $this, 'ajax_update_shipping_option' ] );

	}

	/**
	 * Checks wheter current page is supported for express checkout
	 *
	 * @return boolean
	 */
	private function is_page_supported() {
		return apply_filters(
			'cpsw_express_checkout_allow_custom_pages',
			$this->is_product()
			|| is_cart()
			|| is_checkout()
			|| isset( $_GET['pay_for_order'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		);
	}

	/**
	 * Checks if current location is chosen to display express checkout button
	 *
	 * @return boolean
	 */
	private function is_selected_location() {
		$location = Helper::get_setting( 'express_checkout_location', 'cpsw_stripe' );
		if ( is_array( $location ) && ! empty( $location ) ) {
			if ( $this->is_product() && in_array( 'product', $location, true ) ) {
				return true;
			}

			if ( is_cart() && in_array( 'cart', $location, true ) ) {
				return true;
			}

			if ( is_checkout() && in_array( 'checkout', $location, true ) ) {
				return true;
			}
		}

		return apply_filters( 'cpsw_express_checkout_selected_location_status', false );
	}

	/**
	 * Creates container for payment request button
	 *
	 * @return void
	 */
	public function payment_request_button() {
		$gateways = WC()->payment_gateways->get_available_payment_gateways();

		if ( ! isset( $gateways['cpsw_stripe'] ) ) {
			return;
		}

		if ( ! $this->is_page_supported() ) {
			return;
		}

		if ( ! $this->is_selected_location() ) {
			return;
		}

		if ( 'yes' !== $this->express_checkout ) {
			return;
		}

		$container_class = 'cpsw-product';
		$button_tag      = 'button';
		$button_class    = 'button alt';

		if ( $this->is_product() ) {
			$container_class = 'cpsw-product';
		} elseif ( is_checkout() ) {
			$container_class = 'checkout';
		} elseif ( is_cart() ) {
			$container_class = 'cart';
			$button_class    = 'button alt wc-forward';
			$button_tag      = 'a';
		}

		$options            = Helper::get_gateway_settings( 'cpsw_stripe' );
		$separator_below    = true;
		$position_class     = 'above';
		$alignment_class    = '';
		$button_width       = '';
		$button_inner_width = '';
		$button_label       = '' === $options['express_checkout_button_text'] ? __( 'Pay now', 'checkout-plugins-stripe-woo' ) : ucfirst( $options['express_checkout_button_text'] );

		if ( 'checkout' === $container_class ) {
			$alignment_class = $options['express_checkout_button_alignment'];
			if ( ! empty( $options['express_checkout_button_width'] && $options['express_checkout_button_width'] > 0 ) ) {
				$button_width = 'min-width:' . (int) $options['express_checkout_button_width'] . 'px';

				if ( (int) $options['express_checkout_button_width'] > 500 ) {
					$button_width       = 'max-width:' . (int) $options['express_checkout_button_width'] . 'px;';
					$button_inner_width = 'width:100%;';
				}
			} else {
				$button_width = 'width: 100%';
			}

			if ( 'classic' === $options['express_checkout_checkout_page_layout'] ) {
				$alignment_class = 'center cpsw-classic';
			}
		}

		if ( 'cpsw-product' === $container_class ) {
			if ( 'below' === $options['express_checkout_product_page_position'] ) {
				$separator_below = false;
				$position_class  = 'below';
			}

			if ( 'inline' === $options['express_checkout_product_page_position'] ) {
				$separator_below = false;
				$position_class  = 'inline';
			}

			if ( 'yes' === $options['express_checkout_product_sticky_footer'] ) {
				$container_class .= ' sticky';
			}
		}

		?>
		<div id="cpsw-payment-request-wrapper" class="cpsw-payment-request-main-wrapper <?php echo esc_attr( $container_class . ' ' . $position_class . ' ' . $alignment_class ); ?>" style="display: none;">
			<?php
			if ( ! $separator_below ) {
				$this->payment_request_button_separator();
			}

			/**
			 * Express checkout button before action
			 *
			 * @since 1.4.5
			 */
			do_action( 'cpsw_payment_request_button_before' );
			?>
			<div class="cpsw-payment-request-button-wrapper">
			<?php
			if ( ! empty( trim( $options['express_checkout_title'] ) ) ) {
				?>
				<h3 id="cpsw-payment-request-title"><?php echo esc_html( Helper::get_setting( 'express_checkout_title', 'cpsw_stripe' ) ); ?></h3>
					<?php
			}
			if ( ! empty( trim( $options['express_checkout_tagline'] ) ) ) {
				?>
				<p id="cpsw-payment-request-tagline"><?php echo wp_kses_post( Helper::get_setting( 'express_checkout_tagline', 'cpsw_stripe' ) ); ?></p>
				<?php
			}
			?>
				<div id="cpsw-payment-request-custom-button" style="<?php echo esc_attr( $button_width ); ?>">
					<<?php echo esc_attr( $button_tag ); ?> lang="auto" class="cpsw-payment-request-custom-button-render cpsw_express_checkout_button cpsw-express-checkout-button <?php echo esc_attr( $button_class ); ?>" style="<?php echo esc_attr( $button_width ); ?> <?php echo esc_attr( $button_inner_width ); ?>">
						<div class="cpsw-express-checkout-button-inner" tabindex="-1">
							<div class="cpsw-express-checkout-button-shines">
								<div class="cpsw-express-checkout-button-shine cpsw-express-checkout-button-shine--scroll"></div>
								<div class="cpsw-express-checkout-button-shine cpsw-express-checkout-button-shine--hover"></div>
							</div>
							<div class="cpsw-express-checkout-button-content">
								<?php echo esc_html( $button_label ); ?>
								<img src="" class="cpsw-express-checkout-button-icon">
							</div>
							<div class="cpsw-express-checkout-button-overlay"></div>
							<div class="cpsw-express-checkout-button-border"></div>
						</div>
				</<?php echo esc_attr( $button_tag ); ?>>
				</div>
			</div>
		<?php

		/**
		 * Express checkout button after action
		 *
		 * @since 1.4.5
		 */
		do_action( 'cpsw_payment_request_button_after' );

		if ( $separator_below ) {
			$this->payment_request_button_separator();
		}
		?>
		</div>
		<?php
	}

	/**
	 * Creates separator for payment request button
	 *
	 * @return void
	 */
	public function payment_request_button_separator() {
		if ( 'yes' !== $this->express_checkout ) {
			return;
		}

		$container_class = '';

		if ( $this->is_product() ) {
			$container_class = 'cpsw-product';
		} elseif ( is_checkout() ) {
			$container_class = 'checkout';
		} elseif ( is_cart() ) {
			$container_class = 'cart';
		}

		$options           = Helper::get_gateway_settings( 'cpsw_stripe' );
		$alignment_class   = '';
		$separator_text    = $options['express_checkout_separator_product'];
		$display_separator = true;

		if ( 'checkout' === $container_class ) {
			$alignment_class = $options['express_checkout_button_alignment'];
			if ( ! empty( $options['express_checkout_separator_checkout'] ) ) {
				$separator_text = $options['express_checkout_separator_checkout'];
			}

			if ( 'classic' === $options['express_checkout_checkout_page_layout'] ) {
				$alignment_class = 'center';
			}
		}

		if ( 'cart' === $container_class && ! empty( $options['express_checkout_separator_cart'] ) ) {
			$separator_text = $options['express_checkout_separator_cart'];
		}

		if ( 'cpsw-product' === $container_class && 'inline' === $options['express_checkout_product_page_position'] ) {
			$display_separator = false;
		}

		if ( ! empty( $separator_text ) && $display_separator ) {
			?>
			<div id="cpsw-payment-request-separator" class="<?php echo esc_attr( $container_class . ' ' . $alignment_class ); ?>">
				<?php echo esc_html( $separator_text ); ?>
			</div>
			<?php
		}
	}

	/**
	 * Process chekout on payment request button click
	 *
	 * @return void
	 */
	public function ajax_checkout() {
		check_ajax_referer( 'cpsw_checkout', 'checkout_nonce' );

		if ( WC()->cart->is_empty() ) {
			wp_send_json_error( __( 'Empty cart', 'checkout-plugins-stripe-woo' ) );
		}

		if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
			define( 'WOOCOMMERCE_CHECKOUT', true );
		}
		// setting the checkout nonce to avoid exception.
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'woocommerce-process_checkout' );
		$_POST['_wpnonce']    = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		WC()->checkout()->process_checkout();
		exit();
	}

	/**
	 * Gets product data either form product page or page where shortcode is used
	 *
	 * @return mixed
	 */
	public function get_product() {
		global $post;
		if ( is_product() ) {
			return wc_get_product( $post->ID );
		} elseif ( wc_post_content_has_shortcode( 'product_page' ) ) {
			// Get id from product_page shortcode.
			preg_match( '/\[product_page id="(?<id>\d+)"\]/', $post->post_content, $shortcode_match );

			if ( ! isset( $shortcode_match['id'] ) ) {
				return false;
			}

			return wc_get_product( $shortcode_match['id'] );
		}

		return false;
	}

	/**
	 * Get price of selected product
	 *
	 * @param object $product Selected product data.
	 * @return string
	 */
	public function get_product_price( $product ) {
		$product_price = $product->get_price();
		// Add subscription sign-up fees to product price.
		if ( 'subscription' === $product->get_type() && class_exists( 'WC_Subscriptions_Product' ) ) {
			$product_price = $product->get_price() + WC_Subscriptions_Product::get_sign_up_fee( $product );
		}

		return $product_price;
	}

	/**
	 * Get data of selected product
	 *
	 * @return mixed
	 */
	public function get_product_data() {
		if ( ! $this->is_product() ) {
			return false;
		}

		$product = $this->get_product();

		if ( 'variable' === $product->get_type() ) {
			$variation_attributes = $product->get_variation_attributes();
			$attributes           = [];

			foreach ( $variation_attributes as $attribute_name => $attribute_values ) {
				$attribute_key = 'attribute_' . sanitize_title( $attribute_name );

				$attributes[ $attribute_key ] = isset( $_GET[ $attribute_key ] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					? wc_clean( wp_unslash( $_GET[ $attribute_key ] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					: $product->get_variation_default_attribute( $attribute_name );
			}

			$data_store   = WC_Data_Store::load( 'product' );
			$variation_id = $data_store->find_matching_product_variation( $product, $attributes );

			if ( ! empty( $variation_id ) ) {
				$product = wc_get_product( $variation_id );
			}
		}

		$data  = [];
		$items = [];

		if ( 'subscription' === $product->get_type() && class_exists( 'WC_Subscriptions_Product' ) ) {
			$items[] = [
				'label'  => $product->get_name(),
				'amount' => $this->get_formatted_amount( $product->get_price() ),
			];

			$items[] = [
				'label'  => __( 'Sign up Fee', 'checkout-plugins-stripe-woo' ),
				'amount' => $this->get_formatted_amount( WC_Subscriptions_Product::get_sign_up_fee( $product ) ),
			];
		} else {
			$items[] = [
				'label'  => $product->get_name(),
				'amount' => $this->get_formatted_amount( $this->get_product_price( $product ) ),
			];
		}

		if ( wc_tax_enabled() ) {
			$items[] = [
				'label'   => __( 'Tax', 'checkout-plugins-stripe-woo' ),
				'amount'  => 0,
				'pending' => true,
			];
		}

		if ( wc_shipping_enabled() && $product->needs_shipping() ) {
			$items[] = [
				'label'   => __( 'Shipping', 'checkout-plugins-stripe-woo' ),
				'amount'  => 0,
				'pending' => true,
			];

			$data['shippingOptions'] = [
				'id'     => 'pending',
				'label'  => __( 'Pending', 'checkout-plugins-stripe-woo' ),
				'detail' => '',
				'amount' => 0,
			];
		}

		$data['displayItems']    = $items;
		$data['total']           = [
			'label'   => apply_filters( 'cpsw_payment_request_total_label', $this->statement_descriptor ),
			'amount'  => $this->get_formatted_amount( $this->get_product_price( $product ) ),
			'pending' => true,
		];
		$data['requestShipping'] = ( wc_shipping_enabled() && $product->needs_shipping() && 0 !== wc_get_shipping_method_count( true ) );
		return apply_filters( 'cpsw_payment_request_product_data', $data, $product );
	}

	/**
	 * Adds product data to localized data via filter
	 *
	 * @param array $localized_data localized data.
	 * @return array
	 */
	public function localize_product_data( $localized_data ) {
		return array_merge(
			$localized_data,
			[ 'product' => $this->get_product_data() ]
		);
	}

	/**
	 * Format data to display in payment request cart form
	 *
	 * @param boolean $display_items show detailed view or not.
	 * @return array
	 */
	protected function build_display_items( $display_items = true ) {
		if ( ! defined( 'WOOCOMMERCE_CART' ) ) {
			define( 'WOOCOMMERCE_CART', true );
		}

		$items     = [];
		$lines     = [];
		$subtotal  = 0;
		$discounts = 0;

		foreach ( WC()->cart->get_cart() as $item ) {
			$subtotal      += $item['line_subtotal'];
			$amount         = $item['line_subtotal'];
			$quantity_label = 1 < $item['quantity'] ? ' (' . $item['quantity'] . ')' : '';
			$product_name   = $item['data']->get_name();

			$lines[] = [
				'label'  => $product_name . $quantity_label,
				'amount' => $this->get_formatted_amount( $amount ),
			];
		}

		if ( $display_items ) {
			$items = array_merge( $items, $lines );
		} else {
			// Default show only subtotal instead of itemization.

			$items[] = [
				'label'  => 'Subtotal',
				'amount' => $this->get_formatted_amount( $subtotal ),
			];
		}

		if ( version_compare( WC_VERSION, '3.2', '<' ) ) {
			$discounts = wc_format_decimal( WC()->cart->get_cart_discount_total(), WC()->cart->dp );
		} else {
			$applied_coupons = array_values( WC()->cart->get_coupon_discount_totals() );

			foreach ( $applied_coupons as $amount ) {
				$discounts += (float) $amount;
			}
		}

		$discounts   = wc_format_decimal( $discounts, WC()->cart->dp );
		$tax         = wc_format_decimal( WC()->cart->tax_total + WC()->cart->shipping_tax_total, WC()->cart->dp );
		$shipping    = wc_format_decimal( WC()->cart->shipping_total, WC()->cart->dp );
		$items_total = wc_format_decimal( WC()->cart->cart_contents_total, WC()->cart->dp ) + $discounts;
		$order_total = version_compare( WC_VERSION, '3.2', '<' ) ? wc_format_decimal( $items_total + $tax + $shipping - $discounts, WC()->cart->dp ) : WC()->cart->get_total( false );

		if ( wc_tax_enabled() ) {
			$items[] = [
				'label'  => esc_html( __( 'Tax', 'checkout-plugins-stripe-woo' ) ),
				'amount' => $this->get_formatted_amount( $tax ),
			];
		}

		if ( WC()->cart->needs_shipping() ) {
			$items[] = [
				'label'  => esc_html( __( 'Shipping', 'checkout-plugins-stripe-woo' ) ),
				'amount' => $this->get_formatted_amount( $shipping ),
			];
		}

		if ( WC()->cart->has_discount() ) {
			$items[] = [
				'label'  => esc_html( __( 'Discount', 'checkout-plugins-stripe-woo' ) ),
				'amount' => $this->get_formatted_amount( $discounts ),
			];
		}

		if ( version_compare( WC_VERSION, '3.2', '<' ) ) {
			$cart_fees = WC()->cart->fees;
		} else {
			$cart_fees = WC()->cart->get_fees();
		}

		// Include fees and taxes as display items.
		foreach ( $cart_fees as $fee ) {
			$items[] = [
				'label'  => $fee->name,
				'amount' => $this->get_formatted_amount( $fee->amount ),
			];
		}

		return [
			'displayItems' => $items,
			'total'        => [
				'label'   => apply_filters( 'cpsw_payment_request_total_label', $this->statement_descriptor ),
				'amount'  => max( 0, apply_filters( 'cpsw_stripe_calculated_total', $this->get_formatted_amount( $order_total ), $order_total, WC()->cart ) ),
				'pending' => false,
			],
		];
	}

	/**
	 * Fetch cart details
	 *
	 * @return void
	 */
	public function ajax_get_cart_details() {
		check_ajax_referer( 'cpsw_payment_request', 'cart_nonce' );

		if ( ! defined( 'WOOCOMMERCE_CART' ) ) {
			define( 'WOOCOMMERCE_CART', true );
		}

		WC()->cart->calculate_totals();

		$currency = get_woocommerce_currency();

		// Set mandatory payment details.
		$data = [
			'shipping_required' => WC()->cart->needs_shipping(),
			'order_data'        => [
				'currency'     => strtolower( $currency ),
				'country_code' => substr( get_option( 'woocommerce_default_country' ), 0, 2 ),
			],
		];

		$data['order_data'] += $this->build_display_items( true );

		wp_send_json( $data );
	}

	/**
	 * Updates cart on product variant change
	 *
	 * @return void
	 */
	public function ajax_add_to_cart() {
		check_ajax_referer( 'cpsw_add_to_cart', 'add_to_cart_nonce' );

		if ( ! defined( 'WOOCOMMERCE_CART' ) ) {
			define( 'WOOCOMMERCE_CART', true );
		}

		WC()->shipping->reset_shipping();

		$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$qty          = ! isset( $_POST['qty'] ) ? 1 : absint( $_POST['qty'] );
		$product      = wc_get_product( $product_id );
		$product_type = $product->get_type();

		// First empty the cart to prevent wrong calculation.
		WC()->cart->empty_cart();

		if ( ( 'variable' === $product_type || 'variable-subscription' === $product_type ) && isset( $_POST['attributes'] ) ) {
			$attributes = wc_clean( wp_unslash( $_POST['attributes'] ) );

			$data_store   = WC_Data_Store::load( 'product' );
			$variation_id = $data_store->find_matching_product_variation( $product, $attributes );

			WC()->cart->add_to_cart( $product->get_id(), $qty, $variation_id, $attributes );
		}

		if ( 'simple' === $product_type || 'subscription' === $product_type ) {
			WC()->cart->add_to_cart( $product->get_id(), $qty );
		}

		WC()->cart->calculate_totals();

		$data           = [];
		$data          += $this->build_display_items( true );
		$data['result'] = 'success';

		wp_send_json( $data );
	}

	/**
	 * Updates data as per selected product variant
	 *
	 * @throws Exception Error messages.
	 * @return void
	 */
	public function ajax_selected_product_data() {
		check_ajax_referer( 'cpsw_selected_product_data', 'selected_product_nonce' );

		try {
			$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
			$qty          = ! isset( $_POST['qty'] ) ? 1 : apply_filters( 'woocommerce_add_to_cart_quantity', absint( $_POST['qty'] ), $product_id );
			$addon_value  = isset( $_POST['addon_value'] ) ? max( floatval( $_POST['addon_value'] ), 0 ) : 0;
			$product      = wc_get_product( $product_id );
			$variation_id = null;

			if ( ! is_a( $product, 'WC_Product' ) ) {
				/* translators: %d is the product Id */
				throw new Exception( sprintf( __( 'Product with the ID (%d) cannot be found.', 'checkout-plugins-stripe-woo' ), $product_id ) );
			}

			$product_type = $product->get_type();
			if ( ( 'variable' === $product_type || 'variable-subscription' === $product_type ) && isset( $_POST['attributes'] ) ) {
				$attributes = wc_clean( wp_unslash( $_POST['attributes'] ) );

				$data_store   = WC_Data_Store::load( 'product' );
				$variation_id = $data_store->find_matching_product_variation( $product, $attributes );

				if ( ! empty( $variation_id ) ) {
					$product = wc_get_product( $variation_id );
				}
			}

			if ( $product->is_sold_individually() ) {
				$qty = apply_filters( 'cpsw_payment_request_add_to_cart_sold_individually_quantity', 1, $qty, $product_id, $variation_id );
			}

			if ( ! $product->has_enough_stock( $qty ) ) {
				/* translators: 1: product name 2: quantity in stock */
				throw new Exception( sprintf( __( 'You cannot add that amount of "%1$s"; to the cart because there is not enough stock (%2$s remaining).', 'checkout-plugins-stripe-woo' ), $product->get_name(), wc_format_stock_quantity_for_display( $product->get_stock_quantity(), $product ) ) );
			}

			$total = $qty * $this->get_product_price( $product ) + $addon_value;

			$quantity_label = 1 < $qty ? ' (' . $qty . ')' : '';

			$data  = [];
			$items = [];

			$items[] = [
				'label'  => $product->get_name() . $quantity_label,
				'amount' => $this->get_formatted_amount( $total ),
			];

			if ( wc_tax_enabled() ) {
				$items[] = [
					'label'   => __( 'Tax', 'checkout-plugins-stripe-woo' ),
					'amount'  => 0,
					'pending' => true,
				];
			}

			if ( wc_shipping_enabled() && $product->needs_shipping() ) {
				$items[] = [
					'label'   => __( 'Shipping', 'checkout-plugins-stripe-woo' ),
					'amount'  => 0,
					'pending' => true,
				];

				$data['shippingOptions'] = [
					'id'     => 'pending',
					'label'  => __( 'Pending', 'checkout-plugins-stripe-woo' ),
					'detail' => '',
					'amount' => 0,
				];
			}

			$data['displayItems'] = $items;
			$data['total']        = [
				'label'   => apply_filters( 'cpsw_payment_request_total_label', $this->statement_descriptor ),
				'amount'  => $this->get_formatted_amount( $total ),
				'pending' => true,
			];

			$data['requestShipping'] = ( wc_shipping_enabled() && $product->needs_shipping() );
			$data['currency']        = strtolower( get_woocommerce_currency() );
			$data['country_code']    = substr( get_option( 'woocommerce_default_country' ), 0, 2 );

			wp_send_json( $data );
		} catch ( Exception $e ) {
			wp_send_json( [ 'error' => wp_strip_all_tags( $e->getMessage() ) ] );
		}
	}

	/**
	 * Updates shipping address
	 *
	 * @return void
	 */
	public function ajax_update_shipping_address() {
		check_ajax_referer( 'cpsw_shipping_address', 'shipping_address_nonce' );

		$shipping_address          = filter_input_array(
			INPUT_POST,
			[
				'country'   => FILTER_SANITIZE_STRING,
				'state'     => FILTER_SANITIZE_STRING,
				'postcode'  => FILTER_SANITIZE_STRING,
				'city'      => FILTER_SANITIZE_STRING,
				'address'   => FILTER_SANITIZE_STRING,
				'address_2' => FILTER_SANITIZE_STRING,
			]
		);
		$product_view_options      = filter_input_array( INPUT_POST, [ 'is_product_page' => FILTER_SANITIZE_STRING ] );
		$should_show_itemized_view = ! isset( $product_view_options['is_product_page'] ) ? true : filter_var( $product_view_options['is_product_page'], FILTER_VALIDATE_BOOLEAN );

		$data = $this->get_shipping_options( $shipping_address, $should_show_itemized_view );
		wp_send_json( $data );
	}

	/**
	 * Updates shipping option
	 *
	 * @return void
	 */
	public function ajax_update_shipping_option() {
		check_ajax_referer( 'cpsw_shipping_option', 'shipping_option_nonce' );

		if ( ! defined( 'WOOCOMMERCE_CART' ) ) {
			define( 'WOOCOMMERCE_CART', true );
		}

		$shipping_methods = ! empty( $_POST['shipping_method'] ) && is_array( $_POST['shipping_method'] ) ? wc_clean( $_POST['shipping_method'] ) : [];
		$this->update_shipping_method( $shipping_methods );

		WC()->cart->calculate_totals();

		$product_view_options      = filter_input_array( INPUT_POST, [ 'is_product_page' => FILTER_SANITIZE_STRING ] );
		$should_show_itemized_view = ! isset( $product_view_options['is_product_page'] ) ? true : filter_var( $product_view_options['is_product_page'], FILTER_VALIDATE_BOOLEAN );

		$data           = [];
		$data          += $this->build_display_items( $should_show_itemized_view );
		$data['result'] = 'success';

		wp_send_json( $data );
	}

	/**
	 * Fetch shipping options for selected shipping address
	 *
	 * @param array   $shipping_address selected shipping address.
	 * @param boolean $itemized_display_items shows descriptive view.
	 * @throws Exception If shipping method is not found.
	 * @return array
	 */
	public function get_shipping_options( $shipping_address, $itemized_display_items = true ) {
		try {
			$data = [];

			$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
			$this->calculate_shipping( apply_filters( 'cpsw_payment_request_shipping_posted_values', $shipping_address ) );

			$packages          = WC()->shipping->get_packages();
			$shipping_rate_ids = [];

			if ( ! empty( $packages ) && WC()->customer->has_calculated_shipping() ) {
				foreach ( $packages as $package ) {
					if ( empty( $package['rates'] ) ) {
						throw new Exception( __( 'Unable to find shipping method for address.', 'checkout-plugins-stripe-woo' ) );
					}

					foreach ( $package['rates'] as $key => $rate ) {
						if ( in_array( $rate->id, $shipping_rate_ids, true ) ) {
							throw new Exception( __( 'Unable to provide shipping options for Payment Requests.', 'checkout-plugins-stripe-woo' ) );
						}
						$shipping_rate_ids[]        = $rate->id;
						$data['shipping_options'][] = [
							'id'     => $rate->id,
							'label'  => $rate->label,
							'detail' => '',
							'amount' => $this->get_formatted_amount( $rate->cost ),
						];
					}
				}
			} else {
				throw new Exception( __( 'Unable to find shipping method for address.', 'checkout-plugins-stripe-woo' ) );
			}

			if ( isset( $data['shipping_options'][0] ) ) {
				if ( isset( $chosen_shipping_methods[0] ) ) {
					$chosen_method_id         = $chosen_shipping_methods[0];
					$compare_shipping_options = function ( $a, $b ) use ( $chosen_method_id ) {
						if ( $a['id'] === $chosen_method_id ) {
							return -1;
						}

						if ( $b['id'] === $chosen_method_id ) {
							return 1;
						}

						return 0;
					};
					usort( $data['shipping_options'], $compare_shipping_options );
				}

				$first_shipping_method_id = $data['shipping_options'][0]['id'];
				$this->update_shipping_method( [ $first_shipping_method_id ] );
			}

			WC()->cart->calculate_totals();

			$data          += $this->build_display_items( $itemized_display_items );
			$data['result'] = 'success';
		} catch ( Exception $e ) {
			$data          += $this->build_display_items( $itemized_display_items );
			$data['result'] = 'invalid_shipping_address';
		}

		return $data;
	}

	/**
	 * Calculated shipping charges
	 *
	 * @param array $address updated address.
	 * @return void
	 */
	protected function calculate_shipping( $address = [] ) {
		$country   = $address['country'];
		$state     = $address['state'];
		$postcode  = $address['postcode'];
		$city      = $address['city'];
		$address_1 = $address['address'];
		$address_2 = $address['address_2'];

		WC()->shipping->reset_shipping();

		if ( $postcode && WC_Validation::is_postcode( $postcode, $country ) ) {
			$postcode = wc_format_postcode( $postcode, $country );
		}

		if ( $country ) {
			WC()->customer->set_location( $country, $state, $postcode, $city );
			WC()->customer->set_shipping_location( $country, $state, $postcode, $city );
		} else {
			WC()->customer->set_billing_address_to_base();
			WC()->customer->set_shipping_address_to_base();
		}

		WC()->customer->set_calculated_shipping( true );
		WC()->customer->save();

		$packages = [];

		$packages[0]['contents']                 = WC()->cart->get_cart();
		$packages[0]['contents_cost']            = 0;
		$packages[0]['applied_coupons']          = WC()->cart->applied_coupons;
		$packages[0]['user']['ID']               = get_current_user_id();
		$packages[0]['destination']['country']   = $country;
		$packages[0]['destination']['state']     = $state;
		$packages[0]['destination']['postcode']  = $postcode;
		$packages[0]['destination']['city']      = $city;
		$packages[0]['destination']['address']   = $address_1;
		$packages[0]['destination']['address_2'] = $address_2;

		foreach ( WC()->cart->get_cart() as $item ) {
			if ( $item['data']->needs_shipping() ) {
				if ( isset( $item['line_total'] ) ) {
					$packages[0]['contents_cost'] += $item['line_total'];
				}
			}
		}

		$packages = apply_filters( 'woocommerce_cart_shipping_packages', $packages );

		WC()->shipping->calculate_shipping( $packages );
	}

	/**
	 * Updates shipping method
	 *
	 * @param array $shipping_methods available shipping methods array.
	 * @return void
	 */
	public function update_shipping_method( $shipping_methods ) {
		$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

		if ( is_array( $shipping_methods ) ) {
			foreach ( $shipping_methods as $i => $value ) {
				$chosen_shipping_methods[ $i ] = wc_clean( $value );
			}
		}

		WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
	}
}
