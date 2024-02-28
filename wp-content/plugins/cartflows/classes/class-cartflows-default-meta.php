<?php
/**
 * Cartflow default options.
 *
 * @package Cartflows
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
class Cartflows_Default_Meta {



	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var checkout_fields
	 */
	private static $checkout_fields = null;

	/**
	 * Member Variable
	 *
	 * @var checkout_fields
	 */
	private static $thankyou_fields = null;

	/**
	 * Member Variable
	 *
	 * @var flow_fields
	 */
	private static $flow_fields = null;

	/**
	 * Member Variable
	 *
	 * @var landing_fields
	 */
	private static $landing_fields = null;

	/**
	 * Member Variable
	 *
	 * @var optin_fields
	 */
	private static $optin_fields = null;

	/**
	 * Member Variable
	 *
	 * @var show_design_meta
	 */
	private static $show_design_meta = null;

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
	}

	/**
	 * Show design meta options.
	 *
	 * @return string
	 */
	public function get_show_design_meta_value() {

		if ( null === self::$show_design_meta ) {

			$show_design = get_option( 'cartflows-legacy-meta-show-design-options', false );

			self::$show_design_meta = $show_design ? 'yes' : 'no';
		}

		return self::$show_design_meta;
	}

	/**
	 *  Checkout Default fields.
	 *
	 * @param int $post_id post id.
	 * @return array
	 */
	public function get_checkout_fields( $post_id ) {

		if ( null === self::$checkout_fields ) {
			self::$checkout_fields = array(
				'wcf-enable-design-settings'           => array(
					'default'  => $this->get_show_design_meta_value(),
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-field-google-font-url'            => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_URL',
				),
				'wcf-checkout-products'                => array(
					'default'  => array(),
					'sanitize' => 'FILTER_CARTFLOWS_CHECKOUT_PRODUCTS',
				),
				'wcf-checkout-layout'                  => array(
					'default'  => 'modern-checkout',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-input-font-family'                => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_FONT_FAMILY',
				),
				'wcf-input-font-weight'                => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-heading-font-family'              => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_FONT_FAMILY',
				),
				'wcf-heading-font-weight'              => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-base-font-family'                 => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_FONT_FAMILY',
				),
				'wcf-advance-options-fields'           => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-remove-product-field'             => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-order-review-show-product-images' => array(
					'default'  => 'yes',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-checkout-place-order-button-text' => array(
					'default'  => __( 'Place Order', 'cartflows' ),
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-checkout-place-order-button-lock' => array(
					'default'  => 'yes',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-checkout-place-order-button-price-display' => array(
					'default'  => 'yes',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-base-font-weight'                 => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-button-font-family'               => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_FONT_FAMILY',
				),
				'wcf-button-font-weight'               => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-primary-color'                    => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-heading-color'                    => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-section-bg-color'                 => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-hl-bg-color'                      => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-field-tb-padding'                 => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-field-lr-padding'                 => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-fields-skins'                     => array(
					'default'  => 'modern-label',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-input-field-size'                 => array(
					'default'  => '33px',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-field-color'                      => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-field-bg-color'                   => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-field-border-color'               => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-box-border-color'                 => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-field-label-color'                => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-submit-tb-padding'                => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-submit-lr-padding'                => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-input-button-size'                => array(
					'default'  => '33px',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-submit-color'                     => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-submit-hover-color'               => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-submit-bg-color'                  => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-submit-bg-hover-color'            => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-submit-border-color'              => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-submit-border-hover-color'        => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-header-logo-image'                => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_URL',
				),
				'wcf-header-logo-width'                => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-custom-script'                    => array(
					'default'  => '',
					'sanitize' => 'FILTER_SCRIPT',
				),
				'wcf-step-note'                        => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),

				'wcf-custom-checkout-fields'           => array(
					'default'  => 'no',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-show-coupon-field'                => array(
					'default'  => 'yes',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-optimize-coupon-field'            => array(
					'default'  => 'no',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf_field_order_billing'              => array(
					'default'  => array(),
					'sanitize' => 'FILTER_CARTFLOWS_CHECKOUT_FIELDS',
				),
				'wcf_field_order_shipping'             => array(
					'default'  => array(),
					'sanitize' => 'FILTER_CARTFLOWS_CHECKOUT_FIELDS',
				),
				'wcf-google-autoaddress'               => array(
					'default'  => 'yes',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),

				'wcf-checkout-additional-fields'       => array(
					'default'  => 'yes',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-optimize-order-note-field'        => array(
					'default'  => 'no',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),

				'wcf-shipto-diff-addr-fields'          => array(
					'default'  => 'yes',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-checkout-customer-info-text'      => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-checkout-billing-details-text'    => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-checkout-shipping-details-text'   => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-checkout-your-order-text'         => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-checkout-payment-text'            => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-enable-checkout-field-validation-text' => array(
					'default'  => 'yes',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-checkout-field-validation-text'   => array(
					'default'  => __( 'is required', 'cartflows' ),
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),

			);

			self::$checkout_fields = apply_filters( 'cartflows_checkout_meta_options', self::$checkout_fields, $post_id );
		}

		return self::$checkout_fields;
	}

	/**
	 *  Flow Default fields.
	 *
	 * @param int $post_id post id.
	 * @return array
	 */
	public function get_flow_fields( $post_id ) {

		if ( null === self::$flow_fields ) {
			self::$flow_fields = array(
				'wcf-steps'                        => array(
					'default'  => array(),
					'sanitize' => '', // This key always updated from prepared array. Not from user input and save button click.
				),
				'wcf-flow-indexing'                => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-testing'                      => array(
					'default'  => 'no',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-enable-analytics'             => array(
					'default'  => 'no',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-flow-custom-script'           => array(
					'default'  => '',
					'sanitize' => 'FILTER_SCRIPT',
				),
				// Global Color Pallet color options.
				'wcf-enable-gcp-styling'           => array(
					'default'  => 'no',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-gcp-primary-color'            => array(
					'default'  => '#f16334',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-gcp-secondary-color'          => array(
					'default'  => '#000000',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-gcp-text-color'               => array(
					'default'  => '#4B5563',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-gcp-accent-color'             => array(
					'default'  => '#1F2937',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-gcp-primary-base-font-family' => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_FONT_FAMILY',
				),
			);
		}

		return apply_filters( 'cartflows_flow_meta_options', self::$flow_fields );
	}

	/**
	 *  Get checkout meta.
	 *
	 * @param int    $post_id post id.
	 * @param string $key options key.
	 * @param mix    $default options default value.
	 * @return string
	 */
	public function get_flow_meta_value( $post_id, $key, $default = false ) {

		$value = $this->get_save_meta( $post_id, $key );

		if ( ! $value ) {
			if ( $default ) {
				$value = $default;
			} else {
				$fields = $this->get_flow_fields( $post_id );

				if ( isset( $fields[ $key ]['default'] ) ) {
					$value = $fields[ $key ]['default'];
				}
			}
		}

		return $value;
	}

	/**
	 *  Get checkout meta.
	 *
	 * @param int    $post_id post id.
	 * @param string $key options key.
	 * @param mix    $default options default value.
	 * @return string
	 */
	public function get_checkout_meta_value( $post_id = 0, $key = '', $default = false ) {

		$value = $this->get_save_meta( $post_id, $key );

		if ( ! $value ) {
			if ( false !== $default ) {
				$value = $default;
			} else {
				$fields = $this->get_checkout_fields( $post_id );

				if ( isset( $fields[ $key ]['default'] ) ) {
					$value = $fields[ $key ]['default'];
				}
			}
		}

		return apply_filters( "cartflows_checkout_meta_{$key}", $value );

	}

	/**
	 *  Get post meta.
	 *
	 * @param int    $post_id post id.
	 * @param string $key options key.
	 * @return string
	 */
	public function get_save_meta( $post_id, $key ) {

		return get_post_meta( $post_id, $key, true );
	}

	/**
	 *  Thank You Default fields.
	 *
	 * @param int $post_id post id.
	 * @return array
	 */
	public function get_thankyou_fields( $post_id ) {

		if ( null === self::$thankyou_fields ) {
			self::$thankyou_fields = array(
				'wcf-enable-design-settings'    => array(
					'default'  => $this->get_show_design_meta_value(),
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-field-google-font-url'     => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_URL',
				),
				'wcf-tq-text-color'             => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-tq-font-family'            => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_FONT_FAMILY',
				),
				'wcf-tq-font-size'              => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-tq-heading-color'          => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-tq-heading-font-family'    => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_FONT_FAMILY',
				),
				'wcf-tq-heading-font-wt'        => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-tq-container-width'        => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-tq-section-bg-color'       => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-tq-advance-options-fields' => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-show-overview-section'     => array(
					'default'  => 'yes',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-show-details-section'      => array(
					'default'  => 'yes',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-show-billing-section'      => array(
					'default'  => 'yes',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-show-shipping-section'     => array(
					'default'  => 'yes',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-show-tq-redirect-section'  => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-tq-redirect-link'          => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_URL',
				),
				'wcf-tq-text'                   => array(
					'default'  => __( 'Thank you. Your order has been received.', 'cartflows' ),
					'sanitize' => 'FILTER_WP_KSES_POST',
				),
				'wcf-custom-script'             => array(
					'default'  => '',
					'sanitize' => 'FILTER_SCRIPT',
				),
				'wcf-step-note'                 => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
			);
		}

		return apply_filters( 'cartflows_thankyou_meta_options', self::$thankyou_fields, $post_id );
	}

	/**
	 *  Get Thank you section meta.
	 *
	 * @param int    $post_id post id.
	 * @param string $key options key.
	 * @param mix    $default options default value.
	 * @return string
	 */
	public function get_thankyou_meta_value( $post_id, $key, $default = false ) {

		$value = $this->get_save_meta( $post_id, $key );

		if ( ! $value ) {
			if ( $default ) {
				$value = $default;
			} else {
				$fields = $this->get_thankyou_fields( $post_id );

				if ( isset( $fields[ $key ]['default'] ) ) {
					$value = $fields[ $key ]['default'];
				}
			}
		}

		return apply_filters( "cartflows_thankyou_meta_{$key}", $value );
	}

	/**
	 *  Get Landing section meta.
	 *
	 * @param int    $post_id post id.
	 * @param string $key options key.
	 * @param mix    $default options default value.
	 * @return string
	 */
	public function get_landing_meta_value( $post_id, $key, $default = false ) {

		$value = $this->get_save_meta( $post_id, $key );
		if ( ! $value ) {
			if ( $default ) {
				$value = $default;
			} else {
				$fields = $this->get_landing_fields( $post_id );

				if ( isset( $fields[ $key ]['default'] ) ) {
					$value = $fields[ $key ]['default'];
				}
			}
		}

		return $value;
	}

	/**
	 *  Landing Default fields.
	 *
	 * @param int $post_id post id.
	 * @return array
	 */
	public function get_landing_fields( $post_id ) {

		if ( null === self::$landing_fields ) {
			self::$landing_fields = array(
				'wcf-custom-script' => array(
					'default'  => '',
					'sanitize' => 'FILTER_SCRIPT',
				),
				'wcf-step-note'     => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
			);
		}
		return apply_filters( 'cartflows_landing_meta_options', self::$landing_fields, $post_id );
	}

	/**
	 *  Optin Default fields.
	 *
	 * @param int $post_id post id.
	 * @return array
	 */
	public function get_optin_fields( $post_id ) {

		if ( null === self::$optin_fields ) {
			self::$optin_fields = array(

				'wcf-optin-product'              => array(
					'default'  => array(),
					'sanitize' => 'FILTER_CARTFLOWS_ARRAY',
				),

				/* Style */
				'wcf-enable-design-settings'     => array(
					'default'  => $this->get_show_design_meta_value(),
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-field-google-font-url'      => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_URL',
				),
				'wcf-primary-color'              => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-base-font-family'           => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_FONT_FAMILY',
				),
				'wcf-input-fields-skins'         => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-input-font-family'          => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_FONT_FAMILY',
				),
				'wcf-input-font-weight'          => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-input-field-size'           => array(
					'default'  => '33px',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-field-tb-padding'           => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-field-lr-padding'           => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-field-color'                => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-field-bg-color'             => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-field-border-color'         => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-field-label-color'          => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-submit-button-text'         => array(
					'default'  => __( 'Submit', 'cartflows' ),
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-submit-font-size'           => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-button-font-family'         => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_FONT_FAMILY',
				),
				'wcf-button-font-weight'         => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-submit-button-size'         => array(
					'default'  => '33px',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-submit-tb-padding'          => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-submit-lr-padding'          => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-submit-button-position'     => array(
					'default'  => 'center',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-submit-color'               => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-submit-hover-color'         => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-submit-bg-color'            => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-submit-bg-hover-color'      => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-submit-border-color'        => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),
				'wcf-submit-border-hover-color'  => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_COLOR',
				),

				/* Settings */
				'wcf-optin-pass-fields'          => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-optin-pass-specific-fields' => array(
					'default'  => 'first_name',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),

				/* Script */
				'wcf-custom-script'              => array(
					'default'  => '',
					'sanitize' => 'FILTER_SCRIPT',
				),

				'wcf-step-note'                  => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),

				/* Custom Fields Options*/
				'wcf-optin-enable-custom-fields' => array(
					'default'  => 'no',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),

				'wcf-optin-fields-billing'       => array(
					'default'  => \Cartflows_Helper::get_optin_default_fields(),
					'sanitize' => 'FILTER_CARTFLOWS_OPTIN_FIELDS',
				),
			);
		}
		return apply_filters( 'cartflows_optin_meta_options', self::$optin_fields, $post_id );
	}

	/**
	 *  Get optin meta.
	 *
	 * @param int    $post_id post id.
	 * @param string $key options key.
	 * @param mix    $default options default value.
	 * @return string
	 */
	public function get_optin_meta_value( $post_id = 0, $key = '', $default = false ) {

		$value = $this->get_save_meta( $post_id, $key );

		if ( ! $value ) {
			if ( false !== $default ) {
				$value = $default;
			} else {
				$fields = $this->get_optin_fields( $post_id );

				if ( isset( $fields[ $key ]['default'] ) ) {
					$value = $fields[ $key ]['default'];
				}
			}
		}

		return apply_filters( "cartflows_optin_meta_{$key}", $value );
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Default_Meta::get_instance();
