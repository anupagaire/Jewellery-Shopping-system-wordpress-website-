( function ( $ ) {
	/**
	 * Checkout Custom Field Validations
	 * This will collect all the present fields in the woocommerce form and adds an class if the field
	 * is blank
	 */
	const wcf_custom_field_validation = function () {
		const custom_field_add_class = function (
			field_value,
			field_row,
			field_wrap,
			field_type
		) {
			if (
				field_value === '' ||
				( 'select' === field_type && field_value === ' ' )
			) {
				if ( field_row.hasClass( 'validate-required' ) ) {
					field_wrap.addClass( 'field-required' );
				}
			} else {
				field_wrap.removeClass( 'field-required' );
				field_row.find( '.wcf-field-required-error' ).remove();
			}
		};

		const numberFieldValidation = function (
			field_value,
			field_row,
			field_wrap,
			minValue,
			maxValue
		) {
			if (
				field_value === '' ||
				field_value < minValue ||
				field_value > maxValue
			) {
				field_wrap.addClass( 'field-required' );
				field_wrap.after(
					'<span class="wcf-field-required-error">' +
						cartflows.field_validation_msgs.number_field +
						minValue +
						' & ' +
						maxValue +
						'</span>'
				);
			} else {
				field_wrap.removeClass( 'field-required' );
				field_row.find( '.wcf-field-required-error' ).remove();
			}
		};

		const fields_wrapper = $(
				'form.woocommerce-checkout #customer_details'
			),
			$all_fields = fields_wrapper.find( 'input, textarea' ),
			$selects = fields_wrapper.find( 'select' );

		$all_fields.on( 'blur', function () {
			const $this = $( this ),
				field_type = $this.attr( 'type' ),
				field_row = $this.closest( 'p.form-row' ),
				field_value = $this.val();

			custom_field_add_class( field_value, field_row, $this, field_type );
			if ( 'number' === field_type ) {
				const minValue = $this.attr( 'min' );
				const maxValue = $this.attr( 'max' );
				numberFieldValidation(
					Number( field_value ),
					field_row,
					$this,
					Number( minValue ),
					Number( maxValue )
				);
			}
		} );

		$selects.on( 'blur', function () {
			const $this = $( this ),
				field_row = $this.closest( 'p.form-row' ),
				field_type = 'select',
				field_wrap = field_row.find( '.select2-container--default' ),
				field_value = field_row.find( 'select' ).val();

			custom_field_add_class(
				field_value,
				field_row,
				field_wrap,
				field_type
			);
		} );
	};

	const wcf_check_is_local_storage = function () {
		const test = 'test';
		try {
			localStorage.setItem( test, test );
			localStorage.removeItem( test );
			return true;
		} catch ( e ) {
			return false;
		}
	};

	const wcf_persistent_data = function () {
		if ( 'yes' !== cartflows.allow_persistence ) {
			return;
		}

		if ( false === wcf_check_is_local_storage() ) {
			return;
		}

		const checkout_cust_form =
			'form.woocommerce-checkout #customer_details';

		const wcf_form_data = {
			set() {
				const checkout_data = [];
				const checkout_form = $(
					'form.woocommerce-checkout #customer_details'
				);

				localStorage.removeItem( 'cartflows_checkout_form' );

				checkout_form
					.find(
						'input[type=text], select, input[type=email], input[type=tel]'
					)
					.each( function () {
						checkout_data.push( {
							name: this.name,
							value: this.value,
						} );
					} );

				cartflows_checkout_form = JSON.stringify( checkout_data );
				localStorage.setItem(
					'cartflows_checkout_form',
					cartflows_checkout_form
				);
			},
			get() {
				if (
					localStorage.getItem( 'cartflows_checkout_form' ) !== null
				) {
					checkout_data = JSON.parse(
						localStorage.getItem( 'cartflows_checkout_form' )
					);

					for ( let i = 0; i < checkout_data.length; i++ ) {
						if (
							$(
								'form.woocommerce-checkout [name=' +
									checkout_data[ i ].name +
									']'
							).hasClass( 'select2-hidden-accessible' )
						) {
							$(
								'form.woocommerce-checkout [name=' +
									checkout_data[ i ].name +
									']'
							).selectWoo( 'val', [ checkout_data[ i ].value ] );
						} else {
							$(
								'form.woocommerce-checkout [name=' +
									checkout_data[ i ].name +
									']'
							).val( checkout_data[ i ].value );
						}
					}
				}
			},
		};

		wcf_form_data.get();

		$(
			checkout_cust_form + ' input, ' + checkout_cust_form + ' select'
		).on( 'change', function () {
			wcf_form_data.set();
		} );
	};

	/**
	 * Floating Labels Animation
	 */
	const wcf_floating_labels = function () {
		if ( $( '.wcf-field-floating-labels' ).length < 1 ) {
			return;
		}

		const wcf_anim_field_label = function () {
			const wrapper = $( '.wcf-field-floating-labels' );
			const $inputs = wrapper.find( 'input' );
			const $select_input = wrapper.find( '.select2' );
			const $textarea = wrapper.find( 'textarea' );

			//Add focus class on clicked on input types
			$inputs.on( 'focus', function () {
				const $this = $( this ),
					field_row = $this.closest( '.form-row' );
				has_class = field_row.hasClass( 'wcf-anim-label' );
				field_value = $this.val();

				if ( field_value === '' ) {
					field_row.addClass( 'wcf-anim-label' );
				}
			} );

			//Remove focus class on clicked outside/other input types
			$inputs.on( 'focusout', function () {
				const $this = $( this ),
					field_row = $this.closest( '.form-row' );
				has_class = field_row.hasClass( 'wcf-anim-label' );
				field_value = $this.val();

				if ( field_value === '' ) {
					field_row.removeClass( 'wcf-anim-label' );
				} else {
					field_row.addClass( 'wcf-anim-label' );
				}
			} );

			//Add focus class on clicked on Select
			$select_input.on( 'click', function () {
				const $this = $( this ),
					field_row = $this.closest( '.form-row' );
				has_class = field_row.hasClass( 'wcf-anim-label' );
				field_value = $this
					.find( '.select2-selection__rendered' )
					.text();

				if ( field_value === '' ) {
					field_row.addClass( 'wcf-anim-label' );
				}
			} );

			//Remove focus class on clicked outside/another Select or fields
			$select_input.on( 'focusout', function () {
				const $this = $( this ),
					field_row = $this.closest( '.form-row' );
				has_class = field_row.hasClass( 'wcf-anim-label' );
				field_value = $this
					.find( '.select2-selection__rendered' )
					.text();

				if ( field_value === '' ) {
					field_row.removeClass( 'wcf-anim-label' );
				} else {
					field_row.addClass( 'wcf-anim-label' );
				}
			} );

			//Add focus class on clicked on textarea
			$textarea.on( 'click', function () {
				const $this = $( this ),
					field_row = $this.closest( '.form-row' );
				has_class = field_row.hasClass( 'wcf-anim-label' );
				field_value = $this.val();

				if ( field_value === '' ) {
					field_row.addClass( 'wcf-anim-label' );
				}
			} );

			//Remove focus class on clicked outside/another textarea or fields
			$textarea.on( 'focusout', function () {
				const $this = $( this ),
					field_row = $this.closest( '.form-row' );
				has_class = field_row.hasClass( 'wcf-anim-label' );
				field_value = $this.val();

				if ( field_value === '' ) {
					field_row.removeClass( 'wcf-anim-label' );
				} else {
					field_row.addClass( 'wcf-anim-label' );
				}
			} );
		};

		const wcf_anim_field_label_event = function () {
			const wrapper = $( '.wcf-field-floating-labels' );

			//Add focus class automatically if value is present in input
			const $all_inputs = wrapper.find( 'input' );

			$( $all_inputs ).each( function () {
				const $this = $( this ),
					field_type = $this.attr( 'type' ),
					form_row = $this.closest( '.form-row' ),
					input_elem_value = $this.val();

				$this.attr( 'placeholder', '' );

				_add_anim_class( input_elem_value, field_type, form_row );
			} );

			//Add focus class automatically if value is present in selects
			const $all_selects = wrapper.find( 'select' );

			$( $all_selects ).each( function () {
				const $this = $( this ),
					form_row = $this.closest( '.form-row' ),
					field_type = 'select',
					input_elem_value = $this.val();

				_add_anim_class( input_elem_value, field_type, form_row );
			} );

			// Common function to add wcf-anim-label
			function _add_anim_class( input_elem_value, field_type, form_row ) {
				if (
					input_elem_value !== '' ||
					( input_elem_value !== ' ' && 'select' === field_type )
				) {
					form_row.addClass( 'wcf-anim-label' );
				}

				if ( field_type === 'checkbox' ) {
					form_row.removeClass( 'wcf-anim-label' );
				}

				if ( field_type === 'hidden' ) {
					form_row.removeClass( 'wcf-anim-label' );
					form_row.addClass( 'wcf-anim-label-fix' );
				}
			}
		};

		wcf_anim_field_label();
		wcf_anim_field_label_event();
	};

	$( function () {
		wcf_persistent_data();

		wcf_custom_field_validation();

		wcf_floating_labels();
	} );
} )( jQuery );
