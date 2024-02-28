<?php
/**
 * Checkout markup.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Checkout Markup
 *
 * @since 1.0.0
 */
class Cartflows_Optin_Fields {



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

		add_filter( 'cartflows_billing_optin_fields', array( $this, 'optin_billing_fields_customization' ), 10, 3 );

	}

	/**
	 * Is custom optin?
	 *
	 * @param int $optin_id optin ID.
	 * @since 1.0.0
	 */
	public function is_wcf_optin_custom_fields( $optin_id ) {

		$is_custom = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-optin-enable-custom-fields' );

		if ( 'yes' === $is_custom ) {

			return true;
		}

		return false;
	}

	/**
	 * Billing field customization.
	 *
	 * @param array  $fields fields data.
	 * @param string $country country name.
	 * @param int    $optin_id checkout id.
	 * @return array
	 */
	public function optin_billing_fields_customization( $fields, $country, $optin_id ) {

		if ( ! $this->is_wcf_optin_custom_fields( $optin_id ) ) {
			return $fields;
		}

		if ( is_wc_endpoint_url( 'edit-address' ) ) {
			return $fields;
		}

		$saved_fields = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-optin-fields-billing' );

		if ( ! is_array( $saved_fields ) ) {
			return $fields;
		}

		return $this->prepare_custom_fields( $saved_fields, $country, $optin_id, $fields, 'billing' );
	}


	/**
	 * Prepare custom fields.
	 *
	 * @param array  $fieldset fieldset data.
	 * @param string $country country name.
	 * @param int    $optin_id checkout ID.
	 * @param bool   $original_fieldset is original fieldset.
	 * @param string $type address type.
	 * @return array
	 */
	public function prepare_custom_fields( $fieldset, $country, $optin_id, $original_fieldset = false, $type = 'billing' ) {

		if ( is_array( $fieldset ) && ! empty( $fieldset ) ) {

			$priority = 0;

			$all_original_fields = array_merge( $original_fieldset, $fieldset );

			$original_fieldset = $this->prepare_custom_fields_data( $fieldset, $all_original_fields, $optin_id );

			if ( ! empty( $original_fieldset ) ) {
				foreach ( $original_fieldset as $fieldset_key => $fieldset_value ) {
					if ( ! isset( $fieldset_value['priority'] ) ) {
						$new_priority                                   = $priority + 10;
						$original_fieldset[ $fieldset_key ]['priority'] = $new_priority;
						$priority                                       = $new_priority;
					} else {
						$priority = $fieldset_value['priority'];
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
	 * @param int   $optin_id checkout ID.
	 * @return array
	 */
	public function prepare_custom_fields_data( $fields, $original_fields, $optin_id ) {

		if ( is_array( $fields ) && ! empty( $fields ) ) {

			$order_optin_fields = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-optin-fields-billing' );

			foreach ( $fields as $name => $field ) {

				// Backword compatibility with field enabled.
				if ( isset( $order_optin_fields[ $name ]['enabled'] ) ) {
					$is_enabled = $order_optin_fields[ $name ]['enabled'];
				}

				// Backword compatibility with field width.
				if ( isset( $order_optin_fields[ $name ]['width'] ) ) {
					$field_widths = $order_optin_fields[ $name ]['width'];
				}

				// Set/Unset field if checked/unchecked.
				if ( ! $is_enabled ) {
					unset( $original_fields[ $name ] );
					unset( $fields[ $name ] );
				} else {
					if ( ! isset( $original_fields[ $name ] ) ) {
						$original_fields[ $name ] = $field;
					}

					$field_widths = apply_filters( 'cartflows_optin_billing_fields_width', $field_widths, $name );

					// Add Custom class if set.
					if ( '' != $field_widths ) {
						$original_fields[ $name ]['class'][] = 'wcf-column-' . $field_widths;
					}
				}
			}
		}

		return $original_fields;
	}

}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Optin_Fields::get_instance();
