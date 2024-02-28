( function ( $ ) {
	let autocompleteShipping;
	let autocompleteBilling;

	const componentForm = {
		street_number: 'long_name',
		route: 'long_name',
		locality: 'long_name',
		postal_town: 'long_name',
		sublocality_level_2: 'long_name',
		administrative_area_level_1: 'short_name',
		country: 'short_name',
		postal_code: 'short_name',
	};

	function init_google_billing_address( country ) {
		// Create the autocomplete object, restricting the search to geographical
		// location types.

		if ( country === undefined || country === null ) {
			country = $( '#billing_country :selected' ).val();
		}
		const options = {
			componentRestrictions: { country: [ country ] },
			types: [ 'geocode' ],
		};

		// Remove country restriction if country field is disabled or not present in checkout form.
		if ( '' === country || undefined === country ) {
			delete options.componentRestrictions;
		}

		autocompleteBilling = new google.maps.places.Autocomplete(
			document.getElementById( 'billing_address_1' ),
			options
		);

		// When the user selects an address from the dropdown, populate the address
		// fields in the form.
		autocompleteBilling.addListener(
			'place_changed',
			autoFillParseAddressBilling
		);
	}

	function init_google_shipping_address( country ) {
		// Create the autocomplete object, restricting the search to geographical
		// location types.
		if ( country === undefined || country === null ) {
			country = $( '#shipping_country :selected' ).val();
		}
		const options = {
			componentRestrictions: { country: [ country ] },
			types: [ 'geocode' ],
		};

		// Remove country restriction if country field is disabled or not present in checkout form.
		if ( '' === country || undefined === country ) {
			delete options.componentRestrictions;
		}

		autocompleteShipping = new google.maps.places.Autocomplete(
			document.getElementById( 'shipping_address_1' ),
			options
		);

		// When the user selects an address from the dropdown, populate the address
		// fields in the form.
		autocompleteShipping.addListener(
			'place_changed',
			autoFillParseAddressShipping
		);
	}

	// Initialize places on billing country change.
	$( document ).on( 'change', '#billing_country', function () {
		const new_country = $( this ).val();
		init_google_billing_address( new_country );
	} );

	// Initialize places on shipping country change.
	$( document ).on( 'change', '#shipping_country', function () {
		const new_country = $( this ).val();
		init_google_shipping_address( new_country );
	} );

	// Function to parse the autofill value for billing fields.
	function autoFillParseAddressBilling() {
		$( '#billing_address_1' ).val( '' );
		$( '#billing_country' ).val( '' );
		$( '#billing_address_2' ).val( '' );
		$( '#billing_city' ).val( '' );
		$( '#billing_state' ).val( '' );
		$( '#billing_postcode' ).val( '' );
		// Get the place details from the autocomplete object.
		const place = autocompleteBilling.getPlace();
		const billing_add1_attr = [ 'street_number', 'route' ];
		const billing_add2_attr = [ 'sublocality_level_2' ];

		let billing_country = '',
			state = '',
			city = '',
			billing_address_1 = '',
			billing_address_2 = '',
			postal_code = '';

		// Get each component of the address from the place details
		// and fill the corresponding field on the form.
		if ( place && place.address_components.length > 0 ) {
			for ( const address_value of place.address_components ) {
				const addressType = address_value.types[ 0 ];

				if ( componentForm[ addressType ] ) {
					const fieldVal =
						address_value[ componentForm[ addressType ] ];

					if ( addressType === 'country' ) {
						billing_country = fieldVal;
					}

					if ( addressType === 'administrative_area_level_1' ) {
						state = fieldVal;
					}

					if (
						addressType === 'locality' ||
						addressType === 'postal_town'
					) {
						city = fieldVal;
					}

					if ( addressType === 'postal_code' ) {
						postal_code = fieldVal;
					}

					if ( billing_add1_attr.indexOf( addressType ) !== -1 ) {
						billing_address_1 += fieldVal + ' ';
					}

					if ( billing_add2_attr.indexOf( addressType ) !== -1 ) {
						billing_address_2 += fieldVal + ' ';
					}
				}
			}

			if ( $( '#billing_address_1' ).length > 0 ) {
				if ( billing_address_1.length > 0 ) {
					$( '#billing_address_1' )
						.val( billing_address_1.trimEnd() )
						.trigger( 'focus' );
				}
			}

			if ( $( '#billing_address_2' ).length > 0 ) {
				$( '#billing_address_2' )
					.val( billing_address_2.trimEnd() )
					.trigger( 'focus' );
			}

			if ( $( '#billing_postcode' ).length > 0 ) {
				$( '#billing_postcode' ).val( postal_code ).trigger( 'focus' );
			}

			if ( $( '#billing_city' ).length > 0 ) {
				$( '#billing_city' ).val( city ).trigger( 'focus' );
			}

			if ( $( '#billing_state' ).length > 0 ) {
				$( '#billing_state' )
					.val( state )
					.trigger( 'focus' )
					.trigger( 'change' );
			}

			if ( $( '#billing_country' ).length > 0 ) {
				$( '#billing_country' )
					.val( billing_country )
					.trigger( 'focus' )
					.trigger( 'change' );
			}

			/**
			 * Replace the billing_address_1 field value depending on the country's address format.
			 * Example:
			 * For US the format is street_number and street_route and
			 * for Netherlands the format is street_route and street_number.
			 * So replace it with the value if available.
			 */
			if (
				place.name &&
				( '' !== place.name || 'undefined' !== typeof place.name )
			) {
				$( '#billing_address_1' ).val( place.name );
			}
		}
	}

	// Function to parse the autofill value for billing fields.
	function autoFillParseAddressShipping() {
		if ( $( '#ship-to-different-address-checkbox' ).is( ':checked' ) ) {
			$( '#shipping_address_1' ).val( '' );
			$( '#shipping_country' ).val( '' );
			$( '#shipping_address_2' ).val( '' );
			$( '#shipping_city' ).val( '' );
			$( '#shipping_state' ).val( '' );
			$( '#shipping_postcode' ).val( '' );
			// Get the place details from the autocomplete object.
			const place = autocompleteShipping.getPlace();
			const shipping_add1_attr = [ 'street_number', 'route' ];
			const shipping_add2_attr = [ 'sublocality_level_2' ];

			let shipping_country = '',
				state = '',
				city = '',
				shipping_address_1 = '',
				shipping_address_2 = '',
				postal_code = '';
			// Get each component of the address from the place details
			// and fill the corresponding field on the form.
			if ( place && place.address_components.length > 0 ) {
				for ( const address_value of place.address_components ) {
					const addressType = address_value.types[ 0 ];
					if ( componentForm[ addressType ] ) {
						const fieldVal =
							address_value[ componentForm[ addressType ] ];

						if ( addressType === 'country' ) {
							shipping_country = fieldVal;
						}

						if ( addressType === 'administrative_area_level_1' ) {
							state = fieldVal;
						}

						if ( addressType === 'locality' ) {
							city = fieldVal;
						}

						if ( addressType === 'postal_code' ) {
							postal_code = fieldVal;
						}

						if (
							shipping_add1_attr.indexOf( addressType ) !== -1
						) {
							shipping_address_1 += fieldVal + ' ';
						}

						if (
							shipping_add2_attr.indexOf( addressType ) !== -1
						) {
							shipping_address_2 += fieldVal + ' ';
						}
					}
				}

				if ( $( '#shipping_address_1' ).length > 0 ) {
					if ( shipping_address_1.length > 0 ) {
						$( '#shipping_address_1' )
							.val( shipping_address_1.trimEnd() )
							.trigger( 'focus' );
					}
				}

				if ( $( '#shipping_address_2' ).length > 0 ) {
					$( '#shipping_address_2' )
						.val( shipping_address_2.trimEnd() )
						.trigger( 'focus' );
				}

				if ( $( '#shipping_postcode' ).length > 0 ) {
					$( '#shipping_postcode' )
						.val( postal_code )
						.trigger( 'focus' );
				}

				if ( $( '#shipping_city' ).length > 0 ) {
					$( '#shipping_city' ).val( city ).trigger( 'focus' );
				}

				if ( $( '#shipping_state' ).length > 0 ) {
					$( '#shipping_state' )
						.val( state )
						.trigger( 'focus' )
						.trigger( 'change' );
				}

				if ( $( '#shipping_country' ).length > 0 ) {
					$( '#shipping_country' )
						.val( shipping_country )
						.trigger( 'focus' )
						.trigger( 'change' );
				}

				/**
				 * Replace the shipping_address_1 field value depending on the country's address format.
				 * Example:
				 * For US the format is street_number and street_route and
				 * for Netherlands the format is street_route and street_number.
				 * So replace it with the value if available.
				 */
				if (
					place.name &&
					( '' !== place.name || 'undefined' !== typeof place.name )
				) {
					$( '#shipping_address_1' ).val( place.name );
				}
			}
		}
	}

	$( document ).on( 'ready', function () {
		init_google_billing_address();
		init_google_shipping_address();
	} );
} )( jQuery );
