<?php
/**
 * Cartflows Checkout Field.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Checkout_Fields.
 */
class Cartflows_Checkout_Fields {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Checkout ID
	 *
	 * @var checkout_id
	 */
	public static $cached_checkout_id = null;

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
	 * Constructor
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'init_actions' ) );
		add_action( 'wp', array( $this, 'wp_actions' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'add_frontend_scripts' ) );

		add_filter( 'cartflows_show_coupon_field', array( $this, 'show_hide_coupon_field_on_checkout' ), 10, 2 );
	}

	/**
	 * Trigger action on init.
	 */
	public function init_actions() {

		if ( wp_doing_ajax() && isset( $_GET['wcf_checkout_id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$this->checkout_field_actions();
		}
	}

	/**
	 * Trigger action on wp.
	 */
	public function wp_actions() {

		if ( _is_wcf_checkout_type() ) {
			$this->checkout_field_actions();
		}
	}

		/**
		 *  Add frontend scripts.
		 */
	public function add_frontend_scripts() {

		if ( ! _is_wcf_checkout_type() ) {
			return;
		}

		global $post;

		$checkout_id = $post->ID;

		$this->add_frontend_localize_optimized_scripts( $checkout_id );
	}

		/**
		 * Add localize variables.
		 *
		 * @param int $checkout_id Checkout id.
		 */
	public function add_frontend_localize_optimized_scripts( $checkout_id ) {

		$is_custom_fields_enabled = get_post_meta( $checkout_id, 'wcf-custom-checkout-fields', true );

		$localize = array();

		if ( 'yes' === $is_custom_fields_enabled ) {

			$get_ordered_billing_fields  = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf_field_order_billing' );
			$get_ordered_shipping_fields = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf_field_order_shipping' );

			if ( ! is_array( $get_ordered_billing_fields ) ) {
				$get_ordered_billing_fields = array();
			}

			if ( ! is_array( $get_ordered_shipping_fields ) ) {
				$get_ordered_shipping_fields = array();
			}

			$order_checkout_fields = array_merge( $get_ordered_billing_fields, $get_ordered_shipping_fields );

			foreach ( $order_checkout_fields as $field_key => $order_checkout_field ) {

				$is_enabled   = isset( $order_checkout_field['enabled'] ) ? $order_checkout_field['enabled'] : false;
				$is_required  = isset( $order_checkout_field['required'] ) ? $order_checkout_field['required'] : false;
				$is_optimized = isset( $order_checkout_field['optimized'] ) ? $order_checkout_field['optimized'] : false;

				$localize[ $field_key . '_field' ] = array(
					'is_optimized' => ( $is_enabled && ! $is_required && $is_optimized ),

					/* Translators: %1$s & %2$s is replaced with Field Name */
					'field_label'  => sprintf( __( '%1$s Add %2$s', 'cartflows' ), '<div class="dashicons dashicons-arrow-right"></div>', $order_checkout_field['label'] ),
				);
			}
		}

		$show_coupon_field     = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-show-coupon-field' );
		$optimize_coupon_field = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-optimize-coupon-field' );

		$localize['wcf_custom_coupon_field'] = array(
			'is_optimized' => ( 'yes' === $show_coupon_field && 'yes' === $optimize_coupon_field ? true : false ),
			/* Translators: %s is replaced with Field Icon */
			'field_label'  => sprintf( __( '%s Have a coupon?', 'cartflows' ), '<div class="dashicons dashicons-arrow-right"></div>' ),
		);

		$show_order_field     = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-additional-fields' );
		$optimize_order_field = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-optimize-order-note-field' );

		$localize['order_comments_field'] = array(
			'is_optimized' => ( 'yes' === $show_order_field && 'yes' === $optimize_order_field ? true : false ),
			/* Translators: %s is replaced with Field Icon */
			'field_label'  => sprintf( __( '%s Add Order Notes', 'cartflows' ), '<div class="dashicons dashicons-arrow-right"></div>' ),
		);

		$localize = apply_filters( 'cartflows_checkout_optimized_fields', $localize, $checkout_id );

		$localize_script  = '<!-- script to print the admin localized variables -->';
		$localize_script .= '<script type="text/javascript">';
		$localize_script .= 'var cartflows_checkout_optimized_fields = ' . wp_json_encode( $localize ) . ';';
		$localize_script .= '</script>';

		echo $localize_script; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Trigger actions.
	 */
	public function checkout_field_actions() {

		add_filter( 'woocommerce_checkout_fields', array( $this, 'add_three_column_layout_fields' ) );
		add_filter( 'woocommerce_checkout_fields', array( $this, 'label_skins_fields_customization' ), 1000 );

		add_filter( 'woocommerce_checkout_fields', array( $this, 'additional_fields_customization' ), 1000 );

		add_filter( 'woocommerce_billing_fields', array( $this, 'billing_fields_customization' ), 1000, 2 );
		add_filter( 'woocommerce_shipping_fields', array( $this, 'shipping_fields_customization' ), 1000, 2 );

		add_filter( 'woocommerce_get_country_locale_default', array( $this, 'prepare_country_locale' ) );

		add_filter( 'woocommerce_default_address_fields', array( $this, 'woo_default_address_fields' ), 1000 );

	}

		/**
		 * Prepare default country locale.
		 *
		 * @param array $fields country locale fields.
		 * @return array
		 */
	public function woo_default_address_fields( $fields ) {

		$checkout_id = $this->get_conditional_checkout_id();

		if ( ! $checkout_id ) {

			return $fields;
		}

		$is_custom_fields_enabled = get_post_meta( $checkout_id, 'wcf-custom-checkout-fields', true );

		if ( 'yes' === $is_custom_fields_enabled ) {

			$sname = apply_filters( 'wcf_address_field_override_with', 'billing' );

			if ( 'billing' === $sname || 'shipping' === $sname ) {

				$address_fields = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf_field_order_billing' );

				if ( '' === $address_fields ) {

					$address_fields = Cartflows_Helper::get_checkout_fields( $sname, $checkout_id );
				}

				if ( is_array( $address_fields ) && ! empty( $address_fields ) && ! empty( $fields ) ) {
					$override_required = apply_filters( 'wcf_address_field_override_required', true );

					foreach ( $fields as $name => $field ) {
						$fname = $sname . '_' . $name;

						if ( $this->is_locale_field( $fname ) && $override_required ) {
							$custom_field = isset( $address_fields[ $fname ] ) ? $address_fields[ $fname ] : false;

							if ( $custom_field && ! ( isset( $custom_field['enabled'] ) && false == $custom_field['enabled'] ) ) {
								$fields[ $name ]['required'] = isset( $custom_field['required'] ) && $custom_field['required'] ? true : false;
							}
						}
					}
				}
			}
		}

		return $fields;
	}


	/**
	 * Set locale fields.
	 *
	 * @param string $field_name field name.
	 * @return bool
	 */
	public function is_locale_field( $field_name ) {
		if ( ! empty( $field_name ) && in_array(
			$field_name,
			array(
				'billing_country',
				'billing_address_1',
				'billing_address_2',
				'billing_state',
				'billing_postcode',
				'billing_city',
				'shipping_country',
				'shipping_address_1',
				'shipping_address_2',
				'shipping_state',
				'shipping_postcode',
				'shipping_city',
			),
			true
		)
		) {
			return true;
		}
		return false;
	}

	/**
	 * Additional fields customization.
	 *
	 * @param array $fields fields.
	 *
	 * @return array fields
	 */
	public function additional_fields_customization( $fields ) {

		$checkout_id = $this->get_conditional_checkout_id();

		if ( ! $checkout_id ) {
			return $fields;
		}

		$show_shipto_diff_addr = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-shipto-diff-addr-fields' );
		$show_order_field      = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-additional-fields' );

		if ( isset( $fields['order']['order_comments'] ) ) {

			if ( 'no' === $show_order_field ) {
				unset( $fields['order']['order_comments'] );
				add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );
			} else {

				$is_order_note_optimized = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-optimize-order-note-field' );

				if ( 'yes' === $is_order_note_optimized ) {
					$fields['order']['order_comments']['class'][] = 'wcf-hide-field';
					add_filter( 'cartflows_show_additional_field', '__return_true' );
				}
			}
		}

		if ( 'no' === $show_shipto_diff_addr ) {
			add_filter( 'woocommerce_cart_needs_shipping_address', '__return_false' );
		}

		return $fields;
	}


	/**
	 * Prepare country locale.
	 *
	 * @param array $fields country locale fields.
	 * @return array
	 */
	public function prepare_country_locale( $fields ) {

		$checkout_id = $this->get_conditional_checkout_id();

		if ( ! $checkout_id ) {

			return $fields;
		}

		if ( is_array( $fields ) ) {

			$is_custom_fields_enabled = get_post_meta( $checkout_id, 'wcf-custom-checkout-fields', true );
			$override_placeholder     = apply_filters( 'wcf_field_override_placeholder', true );
			$override_label           = apply_filters( 'wcf_field_override_label', true );

			if ( 'yes' === $is_custom_fields_enabled ) {
				foreach ( $fields as $key => $props ) {
					if ( isset( $props['priority'] ) ) {
						unset( $fields[ $key ]['priority'] );
					}

					if ( $override_placeholder && isset( $props['placeholder'] ) ) {
						unset( $fields[ $key ]['placeholder'] );
					}
					if ( $override_label && isset( $props['label'] ) ) {
						unset( $fields[ $key ]['label'] );
					}
				}
			} else {

				$fields_skins = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-fields-skins' );

				// Change the field style to modern labels for older users who are using style one i:e floating labels.
				if ( 'style-one' === $fields_skins ) {
					$fields_skins = 'modern-label';
				}

				if ( 'modern-label' === $fields_skins ) {

					foreach ( $fields as $key => $props ) {

						// Add label as placeholder if the placeholder value is empty.
						if ( empty( $fields[ $key ]['placeholder'] ) && ! empty( $fields[ $key ]['label'] ) ) {
							$fields[ $key ]['placeholder'] = $fields[ $key ]['label'];
						}

						if ( isset( $fields[ $key ]['label_class'] ) ) {
							$fields[ $key ]['label_class'] = array_diff( $fields[ $key ]['label_class'], array( 'screen-reader-text' ) );
						}

						// Add Asterisk mark to the placeholder text if the field is required.
						if ( ! empty( $fields[ $key ]['placeholder'] ) && ( isset( $fields[ $key ]['required'] ) && $fields[ $key ]['required'] ) ) {
							$fields[ $key ]['placeholder'] = $fields[ $key ]['placeholder'] . '&nbsp;*';
						}
					}
				}
			}
		}
		return $fields;
	}

	/**
	 * Billing field customization.
	 *
	 * @param array  $fields fields data.
	 * @param string $country country name.
	 * @return array
	 */
	public function billing_fields_customization( $fields, $country ) {

		$checkout_id = $this->get_conditional_checkout_id();

		if ( ! $checkout_id ) {

			return $fields;
		}

		if ( ! _is_wcf_meta_custom_checkout( $checkout_id ) || is_wc_endpoint_url( 'edit-address' ) ) {
			return $fields;
		}

		$saved_fields = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf_field_order_billing' );

		if ( '' === $saved_fields ) {
			$saved_fields = Cartflows_Helper::get_checkout_fields( 'billing', $checkout_id );
		}

		return $this->prepare_address_fields( $saved_fields, $country, $checkout_id, $fields, 'billing' );
	}

		/**
		 * Shipping field customization.
		 *
		 * @param array  $fields fields data.
		 * @param string $country country name.
		 * @return array
		 */
	public function shipping_fields_customization( $fields, $country ) {

		$checkout_id = $this->get_conditional_checkout_id();

		if ( ! $checkout_id ) {
			return $fields;
		}

		if ( ! _is_wcf_meta_custom_checkout( $checkout_id ) || is_wc_endpoint_url( 'edit-address' ) ) {
			return $fields;
		}

		$saved_fields = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf_field_order_shipping' );

		if ( '' === $saved_fields ) {
			$saved_fields = Cartflows_Helper::get_checkout_fields( 'shipping', $checkout_id );
		}

		return $this->prepare_address_fields( $saved_fields, $country, $checkout_id, $fields, 'shipping' );
	}

	/**
	 * Prepare address fields.
	 *
	 * @param array  $fieldset fieldset data.
	 * @param string $country country name.
	 * @param int    $checkout_id checkout ID.
	 * @param bool   $original_fieldset is original fieldset.
	 * @param string $type address type.
	 *
	 * @return array
	 */
	public function prepare_address_fields( $fieldset, $country, $checkout_id, $original_fieldset = false, $type = 'billing' ) {

		if ( is_array( $fieldset ) && ! empty( $fieldset ) ) {
			$priority = 0;

			$locale = WC()->countries->get_country_locale();

			if ( isset( $locale[ $country ] ) && is_array( $locale[ $country ] ) ) {
				foreach ( $locale[ $country ] as $key => $value ) {
					if ( is_array( $value ) && isset( $fieldset[ $type . '_' . $key ] ) ) {
						if ( isset( $value['required'] ) ) {
							$fieldset[ $type . '_' . $key ]['required'] = $value['required'];
						}
					}
				}
			}

			$all_original_fields = array_merge( $original_fieldset, $fieldset );
			$original_fieldset   = $this->prepare_checkout_fields_lite( $fieldset, $all_original_fields, $checkout_id );

			if ( ! empty( $original_fieldset ) ) {
				foreach ( $original_fieldset as $fieldset_key => $fieldset_value ) {
					if ( ! isset( $fieldset_value['priority'] ) ) {
						$new_priority                                   = $priority + 10;
						$original_fieldset[ $fieldset_key ]['priority'] = $new_priority;
						$priority                                       = $new_priority;
					} else {
						$priority = $fieldset_value['priority'];
					}

					if ( isset( $fieldset_value['optimized'] ) && $fieldset_value['optimized'] && ! $fieldset_value['required'] ) {
						$original_fieldset[ $fieldset_key ]['class'][] = 'wcf-hide-field';
					}

					if ( isset( $fieldset_value['type'] ) && 'radio' === $fieldset_value['type'] ) {
						$original_fieldset[ $fieldset_key ]['label_class'][] = 'input-radio';
						$original_fieldset[ $fieldset_key ]['class'][]       = 'wcf-input-radio-field-wrapper';
					}
				}
			}
		}
		return $original_fieldset;
	}

	/**
	 * Prepare checkout fields.
	 *
	 * @param array $fields fields data.
	 * @param bool  $original_fields is original fields.
	 * @param int   $checkout_id checkout ID.
	 * @return array
	 */
	public function prepare_checkout_fields_lite( $fields, $original_fields, $checkout_id ) {

		if ( is_array( $fields ) && ! empty( $fields ) ) {

			$get_ordered_billing_fields  = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf_field_order_billing' );
			$get_ordered_shipping_fields = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf_field_order_shipping' );

			/* Check for array before merged. */
			$order_checkout_fields = array_merge( $get_ordered_billing_fields, $get_ordered_shipping_fields );

			foreach ( $fields as $name => $field ) {

				// Backword compatibility with field enabled.
				if ( isset( $order_checkout_fields[ $name ]['enabled'] ) ) {
					$is_enabled = $order_checkout_fields[ $name ]['enabled'];
				} else {
					$is_enabled = get_post_meta( $checkout_id, 'wcf-' . $name, true );
					$is_enabled = 'yes' === $is_enabled ? true : false;
				}

				// Backword compatibility with field width.
				if ( isset( $order_checkout_fields[ $name ]['width'] ) ) {
					$field_widths = $order_checkout_fields[ $name ]['width'];
				} else {
					$field_widths = get_post_meta( $checkout_id, 'wcf-field-width_' . $name, true );
				}

				// Set/Unset field if checked/unchecked.
				if ( ! $is_enabled ) {
					unset( $original_fields[ $name ] );
					unset( $fields[ $name ] );
				} else {
					if ( ! isset( $original_fields[ $name ] ) ) {
						$original_fields[ $name ] = $field;
					}

					$field_widths = apply_filters( 'cartflows_checkout_fields_width', $field_widths, $name );

					// Add Custom class if set.
					if ( '' !== $field_widths ) {

						$original_fields[ $name ]['class'][] = 'wcf-column-' . $field_widths;

					}
				}
			}
		}

		return $original_fields;
	}

	/**
	 * Get checkout id based on condition.
	 *
	 * @return int|bool
	 */
	public function get_conditional_checkout_id() {
		//phpcs:disable WordPress.Security.NonceVerification
		if ( empty( self::$cached_checkout_id ) ) {

			self::$cached_checkout_id = false;

			if ( _is_wcf_checkout_type() ) {

				global $post;

				self::$cached_checkout_id = $post->ID;
			} elseif ( wp_doing_ajax() ) {

				if ( isset( $_GET['wcf_checkout_id'] ) ) {

					self::$cached_checkout_id = intval( wp_unslash( $_GET['wcf_checkout_id'] ) );
				} elseif ( isset( $_POST['id'] ) ) {

					$is_checkout = wcf()->utils->check_is_checkout_page( intval( wp_unslash( $_POST['id'] ) ) );

					if ( $is_checkout ) {
						self::$cached_checkout_id = intval( wp_unslash( $_POST['id'] ) );
					}
				}
			}
		}
		//phpcs:enable WordPress.Security.NonceVerification

		return self::$cached_checkout_id;
	}


	/**
	 * Change order comments placeholder and label, and set billing phone number to not required.
	 *
	 * @param array $fields checkout fields.
	 * @return array
	 */
	public function label_skins_fields_customization( $fields ) {

		$checkout_id = $this->get_conditional_checkout_id();

		if ( ! $checkout_id ) {
			return $fields;
		}

		$fields_skins = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-fields-skins' );

		// Change the field style to modern labels for older users who are using style one i:e floating labels.
		if ( 'style-one' === $fields_skins ) {
			$fields_skins = 'modern-label';
		}

		$field_types = array(
			'billing',
			'shipping',
			'account',
			'order',
		);

		if ( 'modern-label' === $fields_skins ) {

			foreach ( $field_types as $type ) {

				if ( isset( $fields[ $type ] ) && is_array( $fields[ $type ] ) ) {

					foreach ( $fields[ $type ] as $key => $field ) {
						// Add label as placeholder if the placeholder value is empty.
						if ( empty( $fields[ $type ][ $key ]['placeholder'] ) ) {
							$fields[ $type ][ $key ]['placeholder'] = $fields[ $type ][ $key ]['label'];
						}

						// Remove screen-reader-text class from labels.
						if ( isset( $fields[ $type ][ $key ]['label_class'] ) ) {
							$fields[ $type ][ $key ]['label_class'] = array_diff( $fields[ $type ][ $key ]['label_class'], array( 'screen-reader-text' ) );
						}

						// Add Asterisk mark to the placeholder text if the field is required.
						if ( ! empty( $fields[ $type ][ $key ]['placeholder'] ) && ( isset( $fields[ $type ][ $key ]['required'] ) && $fields[ $type ][ $key ]['required'] ) ) {
							$fields[ $type ][ $key ]['placeholder'] = $fields[ $type ][ $key ]['placeholder'] . '&nbsp;*';
						}
					}
				}
			}
		}

		return $fields;
	}

	/**
	 * Add custom class to the fields to change the UI to three column.
	 *
	 * @param array $fields fields.
	 *
	 * @return array
	 */
	public function add_three_column_layout_fields( $fields ) {

		$checkout_id = $this->get_conditional_checkout_id();

		if ( ! $checkout_id ) {
			return $fields;
		}

		if ( _is_wcf_meta_custom_checkout( $checkout_id ) ) {

			// Remove screen-reader-text class from the label even if the field style is set to Default.
			if ( isset( $fields['billing']['billing_address_2'] ) ) {

				if ( isset( $fields['billing']['billing_address_2']['label_class'] ) ) {
					$fields['billing']['billing_address_2']['label_class'] = array_diff( $fields['billing']['billing_address_2']['label_class'], array( 'screen-reader-text' ) );
				}
			}

			if ( isset( $fields['shipping']['shipping_address_2'] ) ) {

				if ( isset( $fields['shipping']['shipping_address_2']['label_class'] ) ) {
					$fields['shipping']['shipping_address_2']['label_class'] = array_diff( $fields['shipping']['shipping_address_2']['label_class'], array( 'screen-reader-text' ) );
				}
			}
			// Remove screen-reader-text class from the label even if the field style is set to Default.

			return $fields;
		}

		if ( empty( $fields['billing']['billing_address_2'] ) ) {

			if ( isset( $fields['billing']['billing_address_1'] ) && is_array( $fields['billing']['billing_address_1'] ) ) {
				$fields['billing']['billing_address_1']['class'][] = 'form-row-full';
			}
		}

		if ( ! empty( $fields['billing']['billing_company'] ) ) {

			if ( isset( $fields['billing']['billing_company'] ) && is_array( $fields['billing']['billing_company'] ) ) {
				$fields['billing']['billing_company']['class'][] = 'form-row-full';
			}
		}

		if ( ! empty( $fields['shipping']['shipping_company'] ) ) {

			if ( isset( $fields['shipping']['shipping_company'] ) && is_array( $fields['shipping']['shipping_company'] ) ) {
				$fields['shipping']['shipping_company']['class'][] = 'form-row-full';
			}
		}

		if ( ! empty( $fields['billing']['billing_country'] ) ) {

			if ( isset( $fields['billing']['billing_country'] ) && is_array( $fields['billing']['billing_country'] ) ) {
				$fields['billing']['billing_country']['class'][] = 'form-row-full';
			}
		}

		if ( ! empty( $fields['shipping']['shipping_country'] ) ) {

			if ( isset( $fields['shipping']['shipping_country'] ) && is_array( $fields['shipping']['shipping_country'] ) ) {
				$fields['shipping']['shipping_country']['class'][] = 'form-row-full';
			}
		}

		if ( ! empty( $fields['billing']['billing_phone'] ) ) {

			if ( isset( $fields['billing']['billing_phone'] ) && is_array( $fields['billing']['billing_phone'] ) ) {
				$fields['billing']['billing_phone']['class'][] = 'form-row-full';
			}
		}

		if ( ! empty( $fields['billing']['billing_email'] ) ) {

			if ( isset( $fields['billing']['billing_email'] ) && is_array( $fields['billing']['billing_email'] ) ) {
				$fields['billing']['billing_email']['class'][] = 'form-row-full';
			}
		}

		if ( empty( $fields['shipping']['shipping_address_2'] ) ) {

			if ( isset( $fields['shipping']['shipping_address_1'] ) && is_array( $fields['shipping']['shipping_address_1'] ) ) {
				$fields['shipping']['shipping_address_1']['class'][] = 'form-row-full';
			}
		}

		/* Billing address classes for 50% width */
		if (
			isset( $fields['billing']['billing_address_1'] ) &&
			isset( $fields['billing']['billing_address_2'] )
		) {
			$fields['billing']['billing_address_1']['class'][] = 'wcf-column-50';
			$fields['billing']['billing_address_2']['class'][] = 'wcf-column-50';

			if ( isset( $fields['billing']['billing_address_2']['label_class'] ) ) {
				$fields['billing']['billing_address_2']['label_class'] = array_diff( $fields['billing']['billing_address_2']['label_class'], array( 'screen-reader-text' ) );
			}
		}

		/* Shipping address classes for 50% width */
		if (
			isset( $fields['shipping']['shipping_address_1'] ) &&
			isset( $fields['shipping']['shipping_address_2'] )
		) {
			$fields['shipping']['shipping_address_1']['class'][] = 'wcf-column-50';
			$fields['shipping']['shipping_address_2']['class'][] = 'wcf-column-50';

			if ( isset( $fields['shipping']['shipping_address_2']['label_class'] ) ) {
				$fields['shipping']['shipping_address_2']['label_class'] = array_diff( $fields['shipping']['shipping_address_2']['label_class'], array( 'screen-reader-text' ) );
			}
		}

		/* Billing address classes for 33% width */
		if (
			isset( $fields['billing']['billing_city'] ) &&
			isset( $fields['billing']['billing_state'] ) && isset( $fields['billing']['billing_postcode'] )
		) {

			$fields['billing']['billing_city']['class'][]     = 'wcf-column-33';
			$fields['billing']['billing_state']['class'][]    = 'wcf-column-33';
			$fields['billing']['billing_postcode']['class'][] = 'wcf-column-33';
		}

		/* Billing address classes for 33% width */
		if (
			isset( $fields['shipping']['shipping_city'] ) &&
			isset( $fields['shipping']['shipping_state'] ) && isset( $fields['shipping']['shipping_postcode'] )
		) {

			$fields['shipping']['shipping_city']['class'][]     = 'wcf-column-33';
			$fields['shipping']['shipping_state']['class'][]    = 'wcf-column-33';
			$fields['shipping']['shipping_postcode']['class'][] = 'wcf-column-33';
		}

		return $fields;
	}

	/**
	 * Optimize coupon field.
	 *
	 * @param array $coupon_field coupon fields.
	 * @return mixed
	 */
	public function optimize_coupon_field( $coupon_field ) {

		$coupon_field['class'] = 'wcf-hide-field';

		return $coupon_field;
	}

	/**
	 * Show/Hide coupon field on checkout page
	 *
	 * @param bool $is_field true.
	 *
	 * @return bool
	 */
	public function show_hide_coupon_field_on_checkout( $is_field ) {

		$checkout_id = $this->get_conditional_checkout_id();

		if ( ! $checkout_id ) {
			return $is_field;
		}

		$show_coupon_field = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-show-coupon-field' );
		$is_field          = false;

		if ( 'yes' === $show_coupon_field ) {

			$is_field = true;

			$optimize_coupon_field = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-optimize-coupon-field' );

			if ( 'yes' === $optimize_coupon_field ) {

				add_filter( 'cartflows_coupon_field_options', array( $this, 'optimize_coupon_field' ), 10 );
			}
		}

		return $is_field;
	}
}

/**
 *  Prepare if class 'Cartflows_Checkout_Fields' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Checkout_Fields::get_instance();
