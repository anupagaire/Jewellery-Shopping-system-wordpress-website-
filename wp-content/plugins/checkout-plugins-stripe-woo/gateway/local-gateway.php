<?php
/**
 * Local Gateway
 *
 * @package checkout-plugins-stripe-woo
 * @since 1.2.0
 */

namespace CPSW\Gateway;

use CPSW\Inc\Traits\Get_Instance;
use CPSW\Gateway\Abstract_Payment_Gateway;
use CPSW\Gateway\Stripe\Stripe_Api;
use CPSW\Inc\Notice;
use CPSW\Inc\Helper;
use CPSW\Inc\Logger;
use Exception;
use WP_Error;
use WC_AJAX;

/**
 * Local Gateway
 *
 * @since 1.2.0
 */
class Local_Gateway extends Abstract_Payment_Gateway {

	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 1.2.0
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'admin_init', [ $this, 'add_notice' ] );
		add_action( 'wc_ajax_' . $this->id . '_verify_payment_intent', [ $this, 'verify_intent' ] );
		add_filter( 'woocommerce_payment_successful_result', [ $this, 'modify_successful_payment_result' ], 999, 2 );
	}

	/**
	 * Registers supported filters for payment gateway
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function init_supports() {
		$this->supports = apply_filters( 'cpsw_local_payment_supports_' . $this->id, [ 'products', 'refunds' ] );
	}

	/**
	 * Get gateway form fields
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = apply_filters( 'cpsw_stripe_form_fields_' . $this->id, $this->get_default_settings() );
	}

	/**
	 * Checks whether this gateway is available.
	 *
	 * @since 1.2.0
	 *
	 * @return boolean
	 */
	public function is_available() {
		if ( 'yes' !== $this->enabled ) {
			return false;
		}

		if ( ! empty( $this->get_option( 'allowed_countries' ) ) && 'all_except' === $this->get_option( 'allowed_countries' ) ) {
			return ! in_array( $this->get_billing_country(), $this->get_option( 'except_countries', array() ), true );
		} elseif ( ! empty( $this->get_option( 'allowed_countries' ) ) && 'specific' === $this->get_option( 'allowed_countries' ) ) {
			return in_array( $this->get_billing_country(), $this->get_option( 'specific_countries', array() ), true );
		}

		return parent::is_available();
	}

	/**
	 * Return a description for (for admin sections) describing the required currency & or billing country(s).
	 *
	 * @since 1.2.0
	 *
	 * @param string $desc Payment description.
	 *
	 * @return string
	 */
	protected function get_payment_description( $desc ) {
		if ( 'all_except' === $this->get_option( 'allowed_countries' ) ) {
			// translators: %s: except countries.
			$desc .= sprintf( __( ' & billing country is not <strong>%s</strong>', 'checkout-plugins-stripe-woo' ), implode( ', ', $this->get_option( 'except_countries', array() ) ) );
		} elseif ( 'specific' === $this->get_option( 'allowed_countries' ) ) {
			// translators: %s: specificcountries.
			$desc .= sprintf( __( ' & billing country is <strong>%s</strong>', 'checkout-plugins-stripe-woo' ), implode( ', ', $this->get_option( 'specific_countries', array() ) ) );
		}

		return apply_filters( 'cpsw_local_payment_description', $desc );
	}

	/**
	 * Return an array of form fields for the gateway.
	 *
	 * @since 1.2.0
	 *
	 * @return array
	 */
	public function get_default_settings() {
		$method_title = $this->method_title;

		$settings = [
			'enabled'            => [
				'label'   => ' ',
				'type'    => 'checkbox',
				// translators: %s: Method title.
				'title'   => sprintf( __( 'Enable %s', 'checkout-plugins-stripe-woo' ), $method_title ),
				'default' => 'no',
			],
			'title'              => [
				'title'       => __( 'Title', 'checkout-plugins-stripe-woo' ),
				'type'        => 'text',
				// translators: %s: Method title.
				'description' => sprintf( __( 'Title of the %s gateway.', 'checkout-plugins-stripe-woo' ), $method_title ),
				'default'     => $method_title,
				'desc_tip'    => true,
			],
			'description'        => [
				'title'       => __( 'Description', 'checkout-plugins-stripe-woo' ),
				'type'        => 'textarea',
				'css'         => 'width:25em',
				/* translators: gateway title */
				'description' => sprintf( __( 'Description of the %1s gateway.', 'checkout-plugins-stripe-woo' ), $method_title ),
				'desc_tip'    => true,
			],
			'order_button_text'  => [
				'title'       => __( 'Order button label', 'checkout-plugins-stripe-woo' ),
				'type'        => 'text',
				'description' => __( 'Customize label for order button.', 'checkout-plugins-stripe-woo' ),
				// translators: %s: Method title.
				'default'     => sprintf( __( 'Pay with %s', 'checkout-plugins-stripe-woo' ), $method_title ),
				'desc_tip'    => true,
			],
			'allowed_countries'  => [
				'title'       => __( 'Selling location(s)', 'checkout-plugins-stripe-woo' ),
				'default'     => 'all',
				'type'        => 'select',
				'class'       => 'wc-enhanced-select wc-stripe-allowed-countries',
				'css'         => 'min-width: 350px;',
				'desc_tip'    => true,
				/* translators: gateway title */
				'description' => sprintf( __( 'This option lets you limit the %1$s gateway to which countries you are willing to sell to.', 'checkout-plugins-stripe-woo' ), $method_title ),
				'options'     => array(
					'all'        => __( 'Sell to all countries', 'checkout-plugins-stripe-woo' ),
					'all_except' => __( 'Sell to all countries, except for&hellip;', 'checkout-plugins-stripe-woo' ),
					'specific'   => __( 'Sell to specific countries', 'checkout-plugins-stripe-woo' ),
				),
			],
			'except_countries'   => [
				'title'             => __( 'Sell to all countries, except for&hellip;', 'checkout-plugins-stripe-woo' ),
				'type'              => 'multi_select_countries',
				'options'           => [],
				'default'           => [],
				'desc_tip'          => true,
				'css'               => 'min-width: 350px;',
				'description'       => __( 'If any of the selected countries matches with the customer\'s billing country, then this payment method will not be visible on the checkout page.', 'checkout-plugins-stripe-woo' ),
				'sanitize_callback' => function ( $value ) {
					return is_array( $value ) ? $value : array();
				},
			],
			'specific_countries' => [
				'title'             => __( 'Sell to specific countries', 'checkout-plugins-stripe-woo' ),
				'type'              => 'multi_select_countries',
				'options'           => [],
				'default'           => [],
				'desc_tip'          => true,
				'css'               => 'min-width: 350px;',
				'description'       => __( 'If any of the selected countries matches with the customer\'s billing country, then this payment method will be visible on the checkout page.', 'checkout-plugins-stripe-woo' ),
				'sanitize_callback' => function ( $value ) {
					return is_array( $value ) ? $value : array();
				},
			],
		];

		return apply_filters( 'cpsw_local_methods_default_settings', $settings );
	}

	/**
	 * Generate multi select countries form field
	 *
	 * @since 1.2.0
	 *
	 * @param string $key Selling location field key.
	 * @param array  $data Selling location field data.
	 *
	 * @return html
	 */
	public function generate_multi_select_countries_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$value     = (array) $this->get_option( $key );
		$data      = wp_parse_args(
			$data,
			array(
				'title'       => '',
				'class'       => '',
				'style'       => '',
				'description' => '',
				'desc_tip'    => false,
				'id'          => $field_key,
				'options'     => [],
			)
		);
		ob_start();
		$selections = (array) $value;

		if ( ! empty( $data['options'] ) ) {
			$countries = array_intersect_key( WC()->countries->countries, array_flip( $data['options'] ) );
		} else {
			$countries = WC()->countries->countries;
		}

		asort( $countries );
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $data['id'] ); ?>"><?php echo esc_html( $data['title'] ); ?>
					<?php
					// Data returned by get_tooltip_html is already escaped.
					echo $this->get_tooltip_html( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</label>
			</th>
			<td class="forminp">
				<select multiple="multiple" name="<?php echo esc_attr( $data['id'] ); ?>[]" style="width:350px"
						data-placeholder="<?php esc_attr_e( 'Choose countries / regions&hellip;', 'checkout-plugins-stripe-woo' ); ?>"
						aria-label="<?php esc_attr_e( 'Country / Region', 'checkout-plugins-stripe-woo' ); ?>" class="wc-enhanced-select"
					>
					<?php
					if ( ! empty( $countries ) ) {
						foreach ( $countries as $key => $val ) {
							// wc_selected returns a static string selected="selected", escaping may not be required.
							echo '<option value="' . esc_attr( $key ) . '"' . wc_selected( $key, $selections ) . '>' . esc_html( $val ) . '</option>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
					}
					?>
				</select>
				<?php
					// get_description_html is a woocommerce function which returns escaped html.
					echo $this->get_description_html( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
				<br/>
				<a class="select_all button" href="#"><?php esc_html_e( 'Select all', 'checkout-plugins-stripe-woo' ); ?></a>
				<a class="select_none button" href="#"><?php esc_html_e( 'Select none', 'checkout-plugins-stripe-woo' ); ?></a>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	/**
	 * Validate multi select countries form field
	 *
	 * @since 1.2.0
	 *
	 * @param string $key Selling location field key.
	 * @param array  $value Selling location field data.
	 *
	 * @return array
	 */
	public function validate_multi_select_countries_field( $key, $value ) {
		return is_array( $value ) ? array_map( 'wc_clean', array_map( 'stripslashes', $value ) ) : '';
	}

	/**
	 * Gets payment gateway icons for local gateways.
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public function get_icon() {
		return $this->payment_icons( $this->id );
	}

	/**
	 * Check current section is cpsw section.
	 *
	 * @since 1.3.0
	 *
	 * @return boolean
	 */
	public function is_current_section() {
		$notice = Notice::get_instance();
		return $notice->is_cpsw_section( $this->id );
	}

	/**
	 * Add notices
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function add_notice() {
		if ( 'yes' !== $this->enabled ) {
			return;
		}

		$notice = Notice::get_instance();

		if ( ! $this->is_current_section() ) {
			return;
		}

		// Add notice if currency not supported.
		if (
			method_exists( $this, 'get_supported_currency' ) &&
			! in_array( get_woocommerce_currency(), $this->get_supported_currency(), true ) &&
			'no' !== get_option( 'cpsw_show_' . $this->id . '_currency_notice' )
		) {
			/* translators: %1$s Payment method, %2$s List of supported currencies */
			$notice->add( $this->id . '_currency', 'notice notice-error', sprintf( __( '%1$s is enabled - it requires store currency to be set to %2$s.', 'checkout-plugins-stripe-woo' ), ucfirst( str_replace( 'cpsw_', '', $this->id ) ), implode( ', ', $this->get_supported_currency() ) ), true );
		}

		// Add notice if currency not supported.
		$default_currency = $this->get_stripe_default_currency();
		if (
			! empty( $default_currency ) &&
			! in_array( strtolower( get_woocommerce_currency() ), $default_currency, true ) &&
			'no' !== get_option( 'cpsw_show_' . $this->id . '_stripe_currency_notice' )
		) {
			/* translators: %1$s Payment method, %2$s List of supported currencies */
			$notice->add( $this->id . '_stripe_currency', 'notice notice-error', sprintf( __( '%1$s is enabled - your store currency %2$s does not match with your stripe account supported currency %3$s.', 'checkout-plugins-stripe-woo' ), ucfirst( str_replace( 'cpsw_', '', $this->id ) ), get_woocommerce_currency(), strtoupper( implode( ', ', $default_currency ) ) ), true );
		}

		/**
		 * Action for add more notices for local gateways.
		 *
		 * @since 1.3.0
		 *
		 * @param obj $notice Notice object.
		 * @param obj $this Current full class object.
		 */
		do_action( 'cpsw_add_notices_for_local_gateways', $notice, $this );
	}

	/**
	 * Return a description for (for admin sections) describing the required currency & or billing country(s).
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public function payment_description() {
		$desc = '';
		if ( method_exists( $this, 'get_supported_currency' ) && $this->get_supported_currency() ) {
			// translators: %s: supported currency.
			$desc .= sprintf( __( 'This gateway supports the following currencies only : <strong>%s</strong>.', 'checkout-plugins-stripe-woo' ), implode( ', ', $this->get_supported_currency() ) );
		}

		return $this->get_payment_description( $desc );
	}

	/**
	 * Get test mode description for local gateways
	 *
	 * @return string
	 * @since 1.2.0
	 */
	public function get_test_mode_description() {
		/* translators: HTML Entities. */
		return apply_filters( 'cpsw_local_gateway_test_description', sprintf( esc_html__( '%1$1sTest Mode Enabled :%2$2s You will be redirected to an authorization page hosted by Stripe.', 'checkout-plugins-stripe-woo' ), '<strong>', '</strong>' ) );
	}

	/**
	 * Get request data
	 *
	 * @since 1.3.0
	 *
	 * @param WC_Order $order wooCommerce order.
	 *
	 * @return array $data to for create payment intent.
	 */
	public function get_data( $order ) {
		$data = [
			'amount'               => $this->get_formatted_amount( $order->get_total() ),
			'currency'             => $this->get_currency(),
			'description'          => $this->get_order_description( $order ),
			'metadata'             => $this->get_metadata( $order->get_id() ),
			'payment_method_types' => [ $this->payment_method_types ],
			'customer'             => $this->get_customer_id( $order ),
		];

		if ( ! empty( $this->capture_method ) ) {
			$data['capture_method'] = $this->capture_method;
		}

		return apply_filters( 'cpsw_local_gateways_payment_intent_data', $data );
	}

	/**
	 * Process woocommerce orders after payment is done
	 *
	 * @since 1.3.0
	 *
	 * @param int $order_id wooCommerce order id.
	 *
	 * @return array data to redirect after payment processing.
	 */
	public function process_payment( $order_id ) {
		try {
			$order           = wc_get_order( $order_id );
			$idempotency_key = $order->get_order_key() . time();
			$data            = $this->get_data( $order );

			/* translators: %1$1s method title, %2$2s order id, %3$3s order total amount  */
			Logger::info( sprintf( __( 'Begin processing payment with %1$1s for order %2$2s for the amount of %3$3s', 'checkout-plugins-stripe-woo' ), $this->method_title, $order_id, $order->get_total() ) );
			$intent_data = $this->get_payment_intent( $order_id, $idempotency_key, $data, true );

			/**
			 * Action when process payment.
			 *
			 * @since 1.3.0
			 *
			 * @param obj $intent_data Payment intent data.
			 * @param obj $order WooCommerce main order.
			 */
			do_action( 'cpsw_local_gateways_process_payment', $intent_data, $order );

			if ( $intent_data ) {
				if ( isset( $intent_data['success'] ) && false === $intent_data['success'] ) {
					$error = '';
					if ( 'currency' === $intent_data['type'] ) {
						$error = __( 'Contact seller. ', 'checkout-plugins-stripe-woo' );

						if ( 'test' === Helper::get_payment_mode() ) {
							$error = __( 'Store currency doesn\'t match stripe currency. ', 'checkout-plugins-stripe-woo' );
						}
					}

					wc_add_notice( $error . $intent_data['message'], 'error' );

					return [
						'result'   => 'fail',
						'redirect' => '',
					];
				}

				return [
					'result'        => 'success',
					'redirect'      => false,
					'intent_secret' => $intent_data['client_secret'],
				];
			} else {
				return [
					'result'   => 'fail',
					'redirect' => '',
				];
			}
		} catch ( Exception $e ) {
			Logger::error( $e->getMessage(), true );
			return new WP_Error( 'order-error', '<div class="woocommerce-error">' . $e->getMessage() . '</div>', [ 'status' => 200 ] );
		}
	}

	/**
	 * Modify redirect url
	 *
	 * @since 1.3.0
	 *
	 * @param array $result redirect url array.
	 * @param int   $order_id woocommerce order id.
	 *
	 * @return array modified redirect url array.
	 */
	public function modify_successful_payment_result( $result, $order_id ) {
		if ( empty( $order_id ) ) {
			return $result;
		}

		$order = wc_get_order( $order_id );

		if ( $this->id !== $order->get_payment_method() ) {
			return $result;
		}

		if ( ! isset( $result['intent_secret'] ) ) {
			return $result;
		}

		// Put the final thank you page redirect into the verification URL.
		$verification_url = add_query_arg(
			[
				'order'                 => $order_id,
				'confirm_payment_nonce' => wp_create_nonce( 'cpsw_confirm_payment_intent' ),
				'redirect_to'           => rawurlencode( $result['redirect'] ),
			],
			WC_AJAX::get_endpoint( $this->id . '_verify_payment_intent' )
		);

		// Combine into a hash.
		$redirect = sprintf( '#confirm-pi-%s:%s:%s', $result['intent_secret'], rawurlencode( $verification_url ), $this->id );

		/**
		 * Action modify mayment successful result.
		 *
		 * @since 1.3.0
		 *
		 * @param obj $result Payment successful result.
		 * @param obj $order WooCommerce main order.
		 */
		do_action( 'cpsw_local_gateways_modify_successful_payment_result', $result, $order );

		return [
			'result'   => 'success',
			'redirect' => $redirect,
		];
	}

	/**
	 * Verify intent state and redirect.
	 *
	 * @since 1.3.0
	 *
	 * @return void
	 */
	public function verify_intent() {
		$order_id = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order    = wc_get_order( $order_id );

		$intent_secret = $order->get_meta( '_cpsw_intent_secret' );
		$stripe_api    = new Stripe_Api();
		$response      = $stripe_api->payment_intents( 'retrieve', [ $intent_secret['id'] ] );
		$intent        = $response['success'] ? $response['data'] : false;

		/**
		 * Action when verify intent.
		 *
		 * @since 1.3.0
		 *
		 * @param obj $intent Payment intent data.
		 * @param obj $order WooCommerce main order.
		 */
		do_action( 'cpsw_local_gateways_process_order', $intent, $order );

		if ( 'succeeded' === $intent->status || 'requires_capture' === $intent->status ) {
			$redirect_to  = $this->process_order( end( $intent->charges->data ), $order_id );
			$redirect_url = apply_filters( 'cpsw_redirect_order_url', $redirect_to, $order );
		} elseif ( isset( $response['data']->last_payment_error ) ) {
			$message = isset( $response['data']->last_payment_error->message ) ? $response['data']->last_payment_error->message : '';
			$code    = isset( $response['data']->last_payment_error->code ) ? $response['data']->last_payment_error->code : '';
			$order->update_status( 'wc-failed' );

			// translators: %s: payment fail message.
			wc_add_notice( sprintf( __( 'Payment failed. %s', 'checkout-plugins-stripe-woo' ), Helper::get_localized_messages( $code, $message ) ), 'error' );
			$redirect_url = wc_get_checkout_url();
		}
		wp_safe_redirect( $redirect_url );
		exit();
	}
}
