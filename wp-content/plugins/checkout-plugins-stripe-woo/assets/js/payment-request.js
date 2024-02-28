( function( $ ) {
	const pubKey = cpsw_payment_request.public_key;
	const ajaxUrl = cpsw_payment_request.ajax_url;
	const nonce = cpsw_payment_request.nonce;
	const isProductPage = cpsw_payment_request.is_product_page;
	const isCheckoutPage = cpsw_payment_request.is_checkout;
	const isCartPage = cpsw_payment_request.is_cart;
	const currencyCode = cpsw_payment_request.currency_code;
	const countryCode = cpsw_payment_request.country_code;
	const style = cpsw_payment_request.style;
	const jsNonce = cpsw_payment_request.nonce.js_nonce;
	const endpoint = cpsw_payment_request.ajax_endpoint;
	const isResponsive = cpsw_payment_request.is_responsive;
	let addToCartButton = '';
	let addToCartQuantity = '';
	let productWrapper = '';
	let quantityInput = '';
	let addToCartHeight = '';
	let addToCartWidth = '';
	let totalWidth = '';
	let quantityWidth = '';
	let availableWidth = '';
	let smallScreen = true;
	let requestType = '';
	let buttonClass = '';

	if ( '' === pubKey ) {
		return;
	}

	const stripe = Stripe( pubKey );

	// Register stripe app info
	stripe.registerAppInfo( {
		name: 'Checkout Plugins - Stripe Woo',
		partner_id: 'pp_partner_KOjySVEy3ClX6G',
		version: cpsw_payment_request.cpsw_version,
		url: 'https://wordpress.org/plugins/checkout-plugins-stripe-woo/',
	} );

	function getAjaxEndpoint( param ) {
		return endpoint.toString().replace( '%%endpoint%%', param );
	}

	function initializePaymentButton() {
		let data;
		if ( isProductPage ) {
			data = {
				total: cpsw_payment_request.product.total,
				currency: cpsw_payment_request.currency_code,
				country: cpsw_payment_request.country_code,
				requestPayerName: true,
				requestPayerEmail: true,
				requestPayerPhone: true,
				requestShipping: cpsw_payment_request.product.requestShipping,
				displayItems: cpsw_payment_request.product.displayItems,
			};
			generatePaymentButton( data );
		} else {
			const params = {
				cart_nonce: nonce.payment,
			};

			$.ajax( {
				type: 'POST',
				data: params,
				url: getAjaxEndpoint( 'cpsw_get_cart_details' ),
				success( response ) {
					data = {
						total: response.order_data.total,
						currency: currencyCode,
						country: countryCode,
						requestPayerName: true,
						requestPayerEmail: true,
						requestPayerPhone: true,
						requestShipping: response.shipping_required,
						displayItems: response.order_data.displayItems,
					};
					generatePaymentButton( data );
				},
			} );
		}
	}

	function generatePaymentButton( data ) {
		try {
			const paymentRequest = stripe.paymentRequest( data );
			let iconUrl = '';

			if ( $( document ).width() < 600 ) {
				smallScreen = true;
			} else {
				smallScreen = false;
			}

			const prButton = $( '.cpsw-payment-request-custom-button-render' );

			if ( $( document ).width() > 600 ) {
				$( '#cpsw-payment-request-wrapper' ).removeClass( 'sticky' );
			}

			prButton.on( 'click', function() {
				paymentRequest.show();
				return false;
			} );

			paymentRequest.canMakePayment().then( function( result ) {
				if ( ! result ) {
					return;
				}

				if ( result.applePay ) {
					requestType = 'apple_pay';
					buttonClass = 'cpsw-express-checkout-applepay-button';
				} else if ( result.googlePay ) {
					requestType = 'google_pay';
					buttonClass = 'cpsw-express-checkout-googlepay-button';
				} else {
					requestType = 'payment_request_api';
					buttonClass = 'cpsw-express-checkout-payment-button';
				}

				if ( 'google_pay' === requestType ) {
					iconUrl = 'dark' === style.theme ? cpsw_payment_request.icons.gpay_light : cpsw_payment_request.icons.gpay_gray;
				} else if ( 'apple_pay' === requestType ) {
					iconUrl = 'dark' === style.theme ? cpsw_payment_request.icons.applepay_light : cpsw_payment_request.icons.applepay_gray;
				} else {
					iconUrl = cpsw_payment_request.icons.payment_request;
				}

				cpswAdjustButtonOnProductPage();
				cpswAdjustButtonOnCheckoutPage();
				cpswAdjustButtonOnCartPage();
				$( '.cpsw-payment-request-custom-button-render' ).addClass( 'cpsw-express-' + requestType );

				if ( '' !== iconUrl ) {
					$( '.cpsw-express-checkout-button-icon' ).show();
					$( '.cpsw-express-checkout-button-icon' ).attr( 'src', iconUrl );
				}

				$( '.cpsw-payment-request-custom-button-render' ).addClass( buttonClass + '--' + style.theme );
				$( '#cpsw-payment-request-custom-button' ).show();

				attachPaymentRequestButtonEventListeners( prButton, paymentRequest );

				$( '#cpsw-payment-request-wrapper' ).css( 'display', 'inline-block' );
				$( '.cpsw-payment-request-main-wrapper' ).addClass( 'cpsw-payment-request-is-visible' );

				makeButtonInline();

				if ( $( '#cpsw-payment-request-separator' ).hasClass( 'cart' ) ) {
					$( '#cpsw-payment-request-separator' ).show();
				}

				if ( $( '#cpsw-payment-request-separator' ).hasClass( 'checkout' ) ) {
					$( '#cpsw-payment-request-separator' ).show();
				}

				if ( $( '#cpsw-payment-request-separator' ).hasClass( 'cpsw-product' ) ) {
					if ( smallScreen && $( '#cpsw-payment-request-wrapper' ).hasClass( 'sticky' ) ) {
						$( '#cpsw-payment-request-separator' ).hide();
					} else if ( ! $( '#cpsw-payment-request-wrapper' ).hasClass( 'inline' ) ) {
						$( '#cpsw-payment-request-separator' ).show();
					}
				}

				if ( ! $( '.cpsw-express-checkout-button-icon' ).hasClass( 'cpsw-express-checkout-button-icon-top' ) && $( '.cpsw-express-checkout-button-shines' ).height() <= 48 ) {
					let buttonIconWidth = '39px';
					if ( 'payment_request_api' === requestType ) {
						buttonIconWidth = '26px';
					}
					$( '.cpsw-express-checkout-button-icon' ).css( 'width', buttonIconWidth );
					$( '.cpsw-express-checkout-button-icon' ).addClass( 'cpsw-express-checkout-button-icon-top' );
				}
			} );

			paymentRequest.on( 'shippingaddresschange', function( e ) {
				$.when( updateShippingAddress( e.shippingAddress ) ).then( function( response ) {
					if ( 'success' === response.result ) {
						e.updateWith( { status: response.result, shippingOptions: response.shipping_options, total: response.total, displayItems: response.displayItems } );
					}
					if ( 'fail' === response.result ) {
						e.updateWith( { status: 'fail' } );
					}
				} );
			} );

			paymentRequest.on( 'shippingoptionchange', function( e ) {
				$.when( updateShippingOption( e.shippingOption ) ).then( function( response ) {
					if ( 'success' === response.result ) {
						e.updateWith( { status: 'success', total: response.total, displayItems: response.displayItems } );
					}

					if ( 'fail' === response.result ) {
						e.updateWith( { status: 'fail' } );
					}
				} );
			} );

			paymentRequest.on( 'paymentmethod', function( e ) {
				$.when( createPaymentMethod( e, requestType ) ).then( function( response ) {
					if ( 'success' === response.result ) {
						confirmPaymentIntent( e, response.redirect );
					} else {
						abortPayment( e, response.messages );
					}
				} );
			} );
		} catch ( e ) {
			logError( e );
		}
	}

	function updateShippingAddress( address ) {
		const data = {
			shipping_address_nonce: nonce.shipping,
			country: address.country,
			state: address.region,
			postcode: address.postalCode,
			city: address.city,
			address: typeof address.addressLine[ 0 ] === 'undefined' ? '' : address.addressLine[ 0 ],
			address_2: typeof address.addressLine[ 1 ] === 'undefined' ? '' : address.addressLine[ 1 ],
			payment_request_type: requestType,
			is_product_page: isProductPage,
		};

		return $.ajax( {
			type: 'POST',
			data,
			url: getAjaxEndpoint( 'cpsw_update_shipping_address' ),
		} );
	}

	function updateShippingOption( shippingOption ) {
		const data = {
			shipping_option_nonce: nonce.shipping_option,
			shipping_method: [ shippingOption.id ],
			payment_request_type: requestType,
			is_product_page: isProductPage,
		};

		return $.ajax( {
			type: 'POST',
			data,
			url: getAjaxEndpoint( 'cpsw_update_shipping_option' ),
		} );
	}

	function attachPaymentRequestButtonEventListeners( prButton, paymentRequest ) {
		prButton.on( 'click', function() {
			$( 'body' ).addClass( 'cpsw-prButton-clicked' );
		} );

		if ( isProductPage ) {
			listenProductPageEvents( prButton, paymentRequest );
		}

		if ( isCheckoutPage ) {
			$( document.body ).on( 'updated_cart_totals', function() {
				const params = {
					cart_nonce: nonce.payment,
				};
				$.ajax( {
					type: 'POST',
					data: params,
					url: getAjaxEndpoint( 'cpsw_get_cart_details' ),
					beforeSend() {
						blockPaymentRequestButton();
					},
					success( response ) {
						$.when(
							paymentRequest.update( {
								total: response.order_data.total,
								displayItems: response.order_data.displayItems,
							} ),
						).then( function() {
							unblockPaymentRequestButton();
						} );
					},
					error() {
						// TODO: check express checkout status for update failure.
						unblockPaymentRequestButton();
					},
				} );
			} );
		}
	}

	function addProductToCart() {
		let productId = $( '.single_add_to_cart_button' ).val();

		// Check if product is a variable product.
		if ( $( '.single_variation_wrap' ).length ) {
			productId = $( '.single_variation_wrap' ).find( 'input[name="product_id"]' ).val();
		}

		const data = {
			add_to_cart_nonce: nonce.add_to_cart,
			action: 'add_to_cart',
			product_id: productId,
			qty: $( '.quantity .qty' ).val(),
			attributes: $( '.variations_form' ).length ? getAttributes().data : [],
		};

		// add addons data to the POST body
		const formData = $( 'form.cart' ).serializeArray();
		$.each( formData, function( i, field ) {
			if ( /^addon-/.test( field.name ) ) {
				if ( /\[\]$/.test( field.name ) ) {
					const fieldName = field.name.substring( 0, field.name.length - 2 );
					if ( data[ fieldName ] ) {
						data[ fieldName ].push( field.value );
					} else {
						data[ fieldName ] = [ field.value ];
					}
				} else {
					data[ field.name ] = field.value;
				}
			}
		} );

		return $.ajax( {
			type: 'POST',
			data,
			url: getAjaxEndpoint( 'cpsw_add_to_cart' ),
		} );
	}

	function listenProductPageEvents( prButton, paymentRequest ) {
		const addToCart = $( '.single_add_to_cart_button' );

		prButton.on( 'click', function( event ) {
			// First check if product can be added to cart.
			if ( addToCart.is( '.disabled' ) ) {
				event.preventDefault();
				addToCart.trigger( 'click' );
				return;
			}

			addProductToCart();
		} );

		$( document.body ).on( 'woocommerce_variation_has_changed', function() {
			if ( addToCart.is( '.disabled' ) ) {
				$( '#cpsw-payment-request-custom-button' ).addClass( 'payment-method-disabled' );
				return;
			}
			$( '#cpsw-payment-request-custom-button' ).removeClass( 'payment-method-disabled' );
			blockPaymentRequestButton();

			$.when( getSelectedProductData() ).then( function( response ) {
				if ( response.error ) {
					displayErrorMessage( response.error );
				} else {
					$.when(
						paymentRequest.update( {
							total: response.total,
							displayItems: response.displayItems,
						} ),
					).then( function() {
						unblockPaymentRequestButton();
					} );
				}
			} );
		} );

		$( 'form.cart .quantity' ).on( 'input', '.qty', function() {
			if ( addToCart.is( '.disabled' ) ) {
				return;
			}
			blockPaymentRequestButton();
		} );

		$( 'form.cart .quantity' ).on( 'input', '.qty', debounce( 250, function() {
			if ( addToCart.is( '.disabled' ) ) {
				return;
			}
			blockPaymentRequestButton();

			$.when( getSelectedProductData() ).then( function( response ) {
				if ( response.error ) {
					displayErrorMessage( response.error );
				} else {
					$.when(
						paymentRequest.update( {
							total: response.total,
							displayItems: response.displayItems,
						} ),
					).then( function() {
						unblockPaymentRequestButton();
					} );
				}
			} );
		} ) );
	}

	function debounce( wait, func, immediate ) {
		let timeout;

		return function() {
			const context = this;
			const args = arguments;

			const later = function() {
				timeout = null;
				if ( ! immediate ) {
					func.apply( context, args );
				}
			};

			const callNow = immediate && ! timeout;

			clearTimeout( timeout );

			timeout = setTimeout( later, wait );

			if ( callNow ) {
				func.apply( context, args );
			}
		};
	}

	function getSelectedProductData() {
		let productId = $( '.single_add_to_cart_button' ).val();

		// Check if product is a variable product.
		if ( $( '.single_variation_wrap' ).length ) {
			productId = $( '.single_variation_wrap' ).find( 'input[name="product_id"]' ).val();
		}

		const addons = $( '#product-addons-total' ).data( 'price_data' ) || [];
		const addonValue = addons.reduce( function( sum, addon ) {
			return sum + addon.cost;
		}, 0 );

		const data = {
			selected_product_nonce: nonce.selected_product_data,
			product_id: productId,
			qty: $( '.quantity .qty' ).val(),
			attributes: $( '.variations_form' ).length ? getAttributes().data : [],
			addon_value: addonValue,
		};

		return $.ajax( {
			type: 'POST',
			data,
			url: getAjaxEndpoint( 'cpsw_selected_product_data' ),
		} );
	}

	function blockPaymentRequestButton( ) {
		if ( $( '.cpsw-payment-request-custom-button-render' ).data( 'blockUI.isBlocked' ) ) {
			return;
		}

		$( '.cpsw-payment-request-custom-button-render' ).addClass( 'cpsw_request_button_blocked' ).block( { message: null } );
	}

	function unblockPaymentRequestButton() {
		$( '.cpsw-payment-request-custom-button-render' ).removeClass( 'cpsw_request_button_blocked' ).unblock();
	}

	function getAttributes() {
		const select = $( '.variations_form' ).find( '.variations select' ),
			data = {};
		let count = 0,
			chosen = 0;

		select.each( function() {
			const attributeName = $( this ).data( 'attribute_name' ) || $( this ).attr( 'name' );
			const value = $( this ).val() || '';

			if ( value.length > 0 ) {
				chosen++;
			}

			count++;
			data[ attributeName ] = value;
		} );

		return {
			count,
			chosenCount: chosen,
			data,
		};
	}

	function preparePaymentMethod( event ) {
		const paymentMethod = event.paymentMethod;
		const billingDetails = paymentMethod.billing_details;
		const email = billingDetails.email;
		const phone = billingDetails.phone;
		const billing = billingDetails.address;
		const name = billingDetails.name;
		const shipping = event.shippingAddress;
		const data = {
			checkout_nonce: nonce.checkout,
			billing_first_name: null !== name ? name.split( ' ' ).slice( 0, 1 ).join( ' ' ) : '',
			billing_last_name: null !== name ? name.split( ' ' ).slice( 1 ).join( ' ' ) : '',
			billing_company: '',
			billing_email: null !== email ? email : event.payerEmail,
			billing_phone: null !== phone ? phone : event.payerPhone && event.payerPhone.replace( '/[() -]/g', '' ),
			billing_country: null !== billing ? billing.country : '',
			billing_address_1: null !== billing ? billing.line1 : '',
			billing_address_2: null !== billing ? billing.line2 : '',
			billing_city: null !== billing ? billing.city : '',
			billing_state: null !== billing ? billing.state : '',
			billing_postcode: null !== billing ? billing.postal_code : '',
			shipping_first_name: '',
			shipping_last_name: '',
			shipping_company: '',
			shipping_country: '',
			shipping_address_1: '',
			shipping_address_2: '',
			shipping_city: '',
			shipping_state: '',
			shipping_postcode: '',
			shipping_method: [ null === event.shippingOption ? null : event.shippingOption.id ],
			order_comments: '',
			payment_method: 'cpsw_stripe',
			ship_to_different_address: 1,
			terms: 1,
			payment_method_created: paymentMethod.id,
			payment_request_type: requestType,
		};

		if ( shipping ) {
			data.shipping_first_name = shipping.recipient.split( ' ' ).slice( 0, 1 ).join( ' ' );
			data.shipping_last_name = shipping.recipient.split( ' ' ).slice( 1 ).join( ' ' );
			data.shipping_company = shipping.organization;
			data.shipping_country = shipping.country;
			data.shipping_address_1 = typeof shipping.addressLine[ 0 ] === 'undefined' ? '' : shipping.addressLine[ 0 ];
			data.shipping_address_2 = typeof shipping.addressLine[ 1 ] === 'undefined' ? '' : shipping.addressLine[ 1 ];
			data.shipping_city = shipping.city;
			data.shipping_state = shipping.region;
			data.shipping_postcode = shipping.postalCode;
		}

		return data;
	}

	function cpswAdjustButtonOnCheckoutPage() {
		if ( isCheckoutPage && $( '.place-order #place_order' ).length > 0 ) {
			const wcCheckoutButton = $( '.place-order #place_order' );
			const cpswExpressCheckoutButtonWrap = $( '.cpsw-payment-request-custom-button-render' );
			const cpswExpressCheckoutButton = $( '#cpsw-payment-request-custom-button' );
			if ( $( '.place-order #place_order' ).outerHeight() > 30 ) {
				cpswExpressCheckoutButtonWrap.css( 'height', $( '.place-order #place_order' ).outerHeight() + 'px' );
			}
			cpswExpressCheckoutButtonWrap.css( 'font-size', $( '.place-order #place_order' ).css( 'font-size' ) );

			if ( $( '.cpsw-payment-request-button-wrapper' ).width() < style.checkout_button_width ) {
				cpswExpressCheckoutButton.css( 'width', '100%' );
				cpswExpressCheckoutButton.css( 'min-width', '100px' );
				cpswExpressCheckoutButtonWrap.css( 'width', '100%' );
				cpswExpressCheckoutButtonWrap.css( 'min-width', '100px' );
			}

			cpswStyleExpressCheckoutButton( cpswExpressCheckoutButtonWrap, wcCheckoutButton );
		}
	}

	function cpswAdjustButtonOnCartPage() {
		if ( isCartPage && $( '.wc-proceed-to-checkout .checkout-button' ).length > 0 ) {
			const wcCartButton = $( '.wc-proceed-to-checkout .checkout-button' );
			const cpswExpressCheckoutButton = $( '.cpsw-payment-request-custom-button-render' );
			if ( $( '.place-order #place_order' ).outerHeight() > 30 ) {
				$( '.cpsw-payment-request-custom-button-render' ).css( 'height', $( '.wc-proceed-to-checkout .checkout-button' ).outerHeight() + 'px' );
			}
			$( '.cpsw-payment-request-custom-button-render' ).css( 'font-size', $( '.wc-proceed-to-checkout .checkout-button' ).css( 'font-size' ) );

			cpswStyleExpressCheckoutButton( cpswExpressCheckoutButton, wcCartButton );
		}
	}

	function cpswAdjustButtonOnProductPage() {
		if ( isProductPage && $( 'form.cart button.single_add_to_cart_button' ).length > 0 ) {
			const wcAddToCartButton = $( 'form.cart button.single_add_to_cart_button' );
			const cpswExpressCheckoutButton = $( '.cpsw-payment-request-custom-button-render' );
			const cpswExpressCheckoutWrapper = $( '#cpsw-payment-request-wrapper' );
			const makeWidth = style.button_length > 10 ? 'min-width' : 'width';
			let addToCartMinWidthType = 'px';

			if ( 'above' === style.button_position ) {
				cpswExpressCheckoutWrapper.css( 'width', '100%' );
				$( '#cpsw-payment-request-separator' ).css( 'width', '200px' );
				$( '.cpsw-payment-request-button-wrapper' ).css( makeWidth, '200px' );
				wcAddToCartButton.css( makeWidth, '200px' );
				wcAddToCartButton.css( 'float', 'left' );
				$( 'form.cart' ).css( 'display', 'inline-block' );
				cpswExpressCheckoutButton.css( makeWidth, '200px' );
			} else {
				let addToCartMinWidth = wcAddToCartButton.outerWidth() + $( 'form.cart .quantity' ).width() + parseInt( $( 'form.cart .quantity' ).css( 'marginRight' ).replace( 'px', '' ) );

				if ( 'inline' === style.button_position ) {
					addToCartMinWidth = wcAddToCartButton.outerWidth();
					addToCartMinWidth = addToCartMinWidth < 120 ? 150 : addToCartMinWidth;

					if ( $( 'form.cart' ).width() < 500 ) {
						makeButtonInline();
					}
					wcAddToCartButton.css( makeWidth, addToCartMinWidth + 'px' );
				} else {
					$( 'form.grouped_form button.single_add_to_cart_button' ).css( makeWidth, addToCartMinWidth + 'px' );
					const themeKadenceButton = $( '.theme-kadence button.single_add_to_cart_button' );

					if ( themeKadenceButton.length > 0 ) {
						addToCartMinWidth = 100;
						addToCartMinWidthType = '%';
						themeKadenceButton.css( makeWidth, addToCartMinWidth + addToCartMinWidthType );
						themeKadenceButton.css( 'margin-top', '20px' );
						$( '.cpsw-payment-request-button-wrapper' ).css( 'float', 'none' );
					}
				}

				cpswExpressCheckoutWrapper.css( makeWidth, addToCartMinWidth + addToCartMinWidthType );
				cpswExpressCheckoutButton.css( makeWidth, addToCartMinWidth + addToCartMinWidthType );

				if ( 'below' === style.button_position ) {
					$( '.theme-twentytwentytwo .cpsw-payment-request-custom-button-render' ).css( makeWidth, wcAddToCartButton.outerWidth() + 'px' );
					$( '.theme-twentytwentytwo #cpsw-payment-request-separator' ).css( makeWidth, wcAddToCartButton.outerWidth() + 'px' );
				}
			}
			cpswStyleExpressCheckoutButton( cpswExpressCheckoutButton, wcAddToCartButton );
		}
	}

	function cpswStyleExpressCheckoutButton( cpswExpressCheckoutButton, wcDefaultClass ) {
		cpswExpressCheckoutButton.css( 'padding', wcDefaultClass.css( 'padding' ) );
		cpswExpressCheckoutButton.css( 'border-radius', wcDefaultClass.css( 'border-radius' ) );
		cpswExpressCheckoutButton.css( 'box-shadow', wcDefaultClass.css( 'box-shadow' ) );
		cpswExpressCheckoutButton.css( 'font-weight', wcDefaultClass.css( 'font-weight' ) );
		cpswExpressCheckoutButton.css( 'text-shadow', wcDefaultClass.css( 'text-shadow' ) );
		cpswExpressCheckoutButton.css( 'font-size', wcDefaultClass.css( 'font-size' ) );
		cpswExpressCheckoutButton.css( 'padding', wcDefaultClass.css( 'padding' ) );
		cpswExpressCheckoutButton.css( 'line-height', wcDefaultClass.css( 'line-height' ) );
		cpswExpressCheckoutButton.css( 'max-height', wcDefaultClass.outerHeight() + 'px' );
	}

	function createPaymentMethod( event ) {
		const data = preparePaymentMethod( event );
		return $.ajax( {
			type: 'POST',
			data,
			dataType: 'json',
			url: getAjaxEndpoint( 'cpsw_payment_request_checkout' ),
		} );
	}

	function confirmPaymentIntent( event, hash ) {
		const partials = hash.match(
			/^#?confirm-(pi|si)-([^:]+):(.+):(.+)$/,
		);

		if ( ! partials || 4 > partials.length ) {
			return;
		}

		const intentClientSecret = partials[ 2 ];
		const redirectURL = decodeURIComponent( partials[ 3 ] );

		confirmPayment( event, intentClientSecret, redirectURL );
	}

	function confirmPayment( event, clientSecret, redirectURL ) {
		stripe.confirmCardPayment( clientSecret, {} ).then( function( result ) {
			if ( result.error ) {
				// Show error to your customer
				$( '.woocommerce-error' ).remove();
				$( 'form.woocommerce-checkout' ).unblock();
				logError( result.error );
				displayErrorMessage( result.error );
			} else {
				// The payment has been processed!
				if ( result.paymentIntent.status === 'succeeded' || result.paymentIntent.status === 'requires_capture' ) {
					$.blockUI( {
						message: null,
						overlayCSS: {
							background: '#fff',
							opacity: 0.6,
						},
					} );
					event.complete( 'success' );
					window.location = redirectURL;
				}
			}
		} );
	}

	function logError( error ) {
		$.ajax( {
			type: 'POST',
			dataType: 'json',
			url: ajaxUrl,
			data: { action: 'cpsw_js_errors', _security: jsNonce, error },
			beforeSend: () => {
				$( 'body' ).css( 'cursor', 'progress' );
			},
			success( response ) {
				if ( response.success === true ) {
				} else if ( response.success === false ) {
					return response.message;
				}
				$( 'body' ).css( 'cursor', 'default' );
			},
			error() {
				$( 'body' ).css( 'cursor', 'default' );
				alert( 'Something went wrong!' );
			},
		} );
	}

	function displayErrorMessage( message ) {
		$( '.woocommerce-error' ).remove();

		if ( isProductPage ) {
			const element = $( '.product' ).first();
			element.before( message );
			window.scrollTo( { top: 100, behavior: 'smooth' } );
		} else {
			const $form = $( '.shop_table.cart, #cpsw-payment-request-wrapper.checkout' ).closest( 'form' );
			$form.before( message );
			window.scrollTo( { top: 100, behavior: 'smooth' } );
		}
	}

	function abortPayment( event, message ) {
		event.complete( 'fail' );
		displayErrorMessage( message );
	}

	function makeButtonInline() {
		if ( $( '#cpsw-payment-request-wrapper' ).hasClass( 'inline' ) && ! $( '#cpsw-payment-request-wrapper' ).hasClass( 'sticky' ) ) {
			productWrapper = $( '#cpsw-payment-request-wrapper' ).parent();
			addToCartButton = productWrapper.children( '.single_add_to_cart_button' );
			addToCartQuantity = productWrapper.children( '.theme-flatsome .cart .quantity' );
			quantityInput = productWrapper.children( '.quantity' );

			addToCartButton.css( { marginRight: quantityInput.css( 'marginRight' ) } );

			totalWidth = productWrapper.outerWidth();
			addToCartWidth = addToCartButton.outerWidth();
			quantityWidth = quantityInput.outerWidth();

			availableWidth = totalWidth - ( addToCartWidth + quantityWidth + 10 );
			if ( availableWidth > addToCartWidth ) {
				addToCartButton.css( {
					margin: 0,
					marginRight: quantityInput.css( 'marginRight' ),
					clear: 'unset',
				} );

				$( '#cpsw-payment-request-wrapper' ).css( {
					margin:	'0px',
					display: 'inline-block',
				} );
			} else {
				addToCartButton.css( {
					margin:	'10px 0',
					clear: 'both',
					flex: 'initial',
				} );

				addToCartQuantity.css( {
					width: '100%',
					clear: 'both',
				} );

				$( '#cpsw-payment-request-wrapper' ).css( {
					marginTop:	'10px',
					display: 'block',
				} );
			}

			addToCartHeight = addToCartButton.outerHeight();
			if ( addToCartHeight > 60 ) {
				addToCartButton.css( { height: 60 } );
			}

			if ( addToCartHeight < 35 ) {
				addToCartButton.css( { height: 35 } );
			}

			$( '#cpsw-payment-request-wrapper' ).width( addToCartWidth );
		}
	}

	initializePaymentButton();

	$( document.body ).on( 'updated_cart_totals', function() {
		initializePaymentButton();
	} );

	// We need to refresh payment request data when total is updated.
	$( document.body ).on( 'updated_checkout', function() {
		initializePaymentButton();
	} );

	// Responsive behaviour for product page.
	$( window ).on( 'resize', function() {
		if ( $( '#cpsw-payment-request-wrapper' ).hasClass( 'cpsw-product' ) ) {
			debounce( 250, makeButtonInline() );

			if ( $( document ).width() > 600 ) {
				$( '#cpsw-payment-request-wrapper' ).removeClass( 'sticky' );
				$( '#cpsw-payment-request-separator' ).show();
			} else {
				if ( 'yes' === isResponsive ) {
					$( '#cpsw-payment-request-wrapper' ).addClass( 'sticky' );
					$( '#cpsw-payment-request-separator' ).hide();
				}
			}

			cpswAdjustButtonOnProductPage();
		}
	} );
}( jQuery ) );
