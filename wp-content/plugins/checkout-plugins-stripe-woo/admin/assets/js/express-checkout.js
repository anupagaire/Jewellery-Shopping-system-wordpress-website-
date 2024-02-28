
( function( $ ) {
	const pubKey = cpsw_express_checkout.public_key;
	let style = cpsw_express_checkout.style;
	const messages = cpsw_express_checkout.messages;
	const icons = cpsw_express_checkout.icons;
	const stripe = Stripe( pubKey );

	// Register stripe app info
	stripe.registerAppInfo( {
		name: 'Checkout Plugins - Stripe Woo',
		partner_id: 'pp_partner_KOjySVEy3ClX6G',
		version: cpsw_express_checkout.cpsw_version,
		url: 'https://wordpress.org/plugins/checkout-plugins-stripe-woo/',
	} );

	function generateExpressCheckoutDemo() {
		try {
			const data = {
				country: 'US',
				currency: 'usd',
				total: {
					label: 'Demo total',
					amount: 1099,
				},
				requestPayerName: true,
				requestPayerEmail: true,
			};

			const paymentRequest = stripe.paymentRequest( data );
			let iconUrl = '';
			let buttonClass = '';
			let requestType = '';

			paymentRequest.canMakePayment().then( function( result ) {
				if ( ! result ) {
					return;
				}

				const prButton = $( '.cpsw-payment-request-custom-button-render' );

				prButton.on( 'click', function( e ) {
					e.preventDefault();
				} );

				if ( $( '.cpsw_express_checkout_preview_wrapper .cpsw_express_checkout_preview' ).length > 0 ) {
					$( '.cpsw-payment-request-custom-button-admin' ).show();
					$( '.cpsw_button_preview_label' ).css( { display: 'block' } );
					$( '.cpsw_preview_notice' ).css( { display: 'block' } );
					$( '.cpsw_express_checkout_preview_wrapper .cpsw_express_checkout_preview' ).fadeIn();
					$( '.cpsw_preview_title' ).html( $( '#cpsw_express_checkout_title' ).val() );
					$( '.cpsw_preview_tagline' ).html( $( '#cpsw_express_checkout_tagline' ).val() );

					const buttonWidth = $( '#cpsw_express_checkout_button_width' ).val() ? $( '#cpsw_express_checkout_button_width' ).val() + 'px' : '100%';
					const buttonWidthOriginVal = $( '#cpsw_express_checkout_button_width' ).val();
					const expressButtonAlignment = $( '#cpsw_express_checkout_button_alignment' ).val();

					if ( buttonWidthOriginVal > 380 ) {
						prButton.css( 'max-width', buttonWidth );
						prButton.css( 'width', '100%' );
					} else if ( '' !== buttonWidthOriginVal && buttonWidthOriginVal < 101 ) {
						prButton.css( 'width', '112px' );
						prButton.css( 'min-width', '112px' );
					} else {
						prButton.css( 'min-width', buttonWidth );
					}

					$( '.cpsw_express_checkout_preview_wrapper' ).css( { textAlign: expressButtonAlignment } );
					if ( expressButtonAlignment === 'center' ) {
						$( '.cpsw-payment-request-custom-button-admin' ).css( { margin: '0 auto', float: 'none' } );
					} else {
						$( '.cpsw-payment-request-custom-button-admin' ).css( { marginBottom: '1em', float: expressButtonAlignment } );
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

					removeAllButtonThemes();
					$( '.cpsw-payment-request-custom-button-render' ).addClass( 'cpsw-express-' + requestType );

					$( '.cpsw-payment-request-custom-button-render' ).addClass( buttonClass + '--' + style.theme );
					$( '.cpsw-payment-request-custom-button-render .cpsw-express-checkout-button-label' ).html( style.text );
					$( '.cpsw-express-checkout-button-icon' ).hide();

					if ( 'cpsw-express-checkout-googlepay-button' === buttonClass ) {
						iconUrl = 'dark' === style.theme ? icons.gpay_light : icons.gpay_gray;
					} else if ( 'cpsw-express-checkout-applepay-button' === buttonClass ) {
						iconUrl = 'dark' === style.theme ? icons.applepay_light : icons.applepay_gray;
					} else {
						iconUrl = icons.payment_request;
					}

					if ( '' !== iconUrl ) {
						$( '.cpsw-express-checkout-button-icon' ).show();
						$( '.cpsw-express-checkout-button-icon' ).attr( 'src', iconUrl );
					}
				}
			} );
		} catch ( e ) {
		}
	}

	function removeAllButtonThemes() {
		$( '.cpsw-payment-request-custom-button-render' ).removeClass( 'cpsw-express-checkout-payment-button--dark' );
		$( '.cpsw-payment-request-custom-button-render' ).removeClass( 'cpsw-express-checkout-payment-button--light' );
		$( '.cpsw-payment-request-custom-button-render' ).removeClass( 'cpsw-express-checkout-payment-button--light-outline' );
		$( '.cpsw-payment-request-custom-button-render' ).removeClass( 'cpsw-express-checkout-googlepay-button--dark' );
		$( '.cpsw-payment-request-custom-button-render' ).removeClass( 'cpsw-express-checkout-googlepay-button--light' );
		$( '.cpsw-payment-request-custom-button-render' ).removeClass( 'cpsw-express-checkout-googlepay-button--light-outline' );
		$( '.cpsw-payment-request-custom-button-render' ).removeClass( 'cpsw-express-checkout-applepay-button--dark' );
		$( '.cpsw-payment-request-custom-button-render' ).removeClass( 'cpsw-express-checkout-applepay-button--light' );
		$( '.cpsw-payment-request-custom-button-render' ).removeClass( 'cpsw-express-checkout-applepay-button--light-outline' );
	}

	function addCheckoutPreviewElement() {
		removeCheckoutPreviewElement();
		$( '.cpsw_express_checkout_preview_wrapper' ).prepend( '<h3 class="cpsw_preview_title"></h3><p class="cpsw_preview_tagline"></p>' );
		$( '.cpsw_express_checkout_preview_wrapper' ).after( '<p class="cpsw_preview_notice">' + messages.checkout_note + '</p>' );

		$( '.cpsw_express_checkout_preview_wrapper' ).css( { textAlign: $( '#cpsw_express_checkout_button_alignment' ).val() } );
		if ( $( '#cpsw_express_checkout_button_alignment' ).val() === 'center' ) {
			$( '.cpsw-payment-request-custom-button-admin' ).css( { margin: '0 auto', float: 'none' } );
		} else {
			$( '.cpsw-payment-request-custom-button-admin' ).css( { marginBottom: '1em', float: $( '#cpsw_express_checkout_button_alignment' ).val() } );
		}
	}

	function removeCheckoutPreviewElement() {
		$( '.cpsw_preview_title, .cpsw_preview_tagline, .cpsw_preview_notice' ).remove();
		$( '.cpsw-payment-request-custom-button-admin' ).css( { margin: '0 auto', float: 'none', width: '100%' } );
	}

	function toggleOptions() {
		const pages = $( '#cpsw_express_checkout_location option:selected' ).toArray().map( ( item ) => item.value );

		if ( jQuery.inArray( 'product', pages ) !== -1 ) {
			$( '.cpsw_product_options' ).each( function() {
				$( this ).parents( 'tr' ).show();
			} );
			$( '#cpsw_express_checkout_product_page-description' ).show();
			$( '#cpsw_express_checkout_product_page-description' ).prev( 'h2' ).show();
		} else {
			$( '.cpsw_product_options' ).each( function() {
				$( this ).parents( 'tr' ).hide();
			} );
			$( '#cpsw_express_checkout_product_page-description' ).hide();
			$( '#cpsw_express_checkout_product_page-description' ).prev( 'h2' ).hide();
		}

		if ( jQuery.inArray( 'cart', pages ) !== -1 ) {
			$( '.cpsw_cart_options' ).each( function() {
				$( this ).parents( 'tr' ).show();
			} );
			$( '#cpsw_express_checkout_cart_page-description' ).show();
			$( '#cpsw_express_checkout_cart_page-description' ).prev( 'h2' ).show();
		} else {
			$( '.cpsw_cart_options' ).each( function() {
				$( this ).parents( 'tr' ).hide();
			} );
			$( '#cpsw_express_checkout_cart_page-description' ).hide();
			$( '#cpsw_express_checkout_cart_page-description' ).prev( 'h2' ).hide();
		}

		if ( jQuery.inArray( 'checkout', pages ) !== -1 ) {
			$( '.cpsw_checkout_options' ).each( function() {
				$( this ).parents( 'tr' ).show();
				addCheckoutPreviewElement();
				$( '#cpsw_express_checkout_title' ).trigger( 'keyup' );
				$( '#cpsw_express_checkout_tagline' ).trigger( 'keyup' );
			} );
			$( '#cpsw_express_checkout_checkout_page-description' ).show();
			$( '#cpsw_express_checkout_checkout_page-description' ).prev( 'h2' ).show();
		} else {
			$( '.cpsw_checkout_options' ).each( function() {
				$( this ).parents( 'tr' ).hide();
				removeCheckoutPreviewElement();
			} );
			$( '#cpsw_express_checkout_checkout_page-description' ).hide();
			$( '#cpsw_express_checkout_checkout_page-description' ).prev( 'h2' ).hide();
		}
	}

	function cpswExpressCheckoutLayoutEffects() {
		const checkoutPageLayout = $( '#cpsw_express_checkout_checkout_page_layout' ).val();

		if ( checkoutPageLayout === 'classic' ) {
			$( '#cpsw_express_checkout_button_alignment' ).parents( 'tr' ).hide();
			$( '.cpsw_express_checkout_preview_wrapper' ).addClass( 'cpsw-classic' );
		} else {
			$( '#cpsw_express_checkout_button_alignment' ).parents( 'tr' ).show();
			$( '.cpsw_express_checkout_preview_wrapper' ).removeClass( 'cpsw-classic' );
		}
	}

	$( document ).ready( function() {
		$( '.cpsw_express_checkout_location' ).selectWoo();
		generateExpressCheckoutDemo( style );
		toggleOptions();
		cpswExpressCheckoutLayoutEffects();

		$( '#cpsw_express_checkout_button_text, #cpsw_express_checkout_button_theme' ).change( function() {
			style = {
				text: '' === $( '#cpsw_express_checkout_button_text' ).val() ? messages.default_text : $( '#cpsw_express_checkout_button_text' ).val(),
				theme: $( '#cpsw_express_checkout_button_theme' ).val(),
			};
			$( '.cpsw_express_checkout_preview_wrapper .cpsw-payment-request-custom-button-admin' ).hide();
			generateExpressCheckoutDemo( style );
		} );

		$( '#cpsw_express_checkout_checkout_page_layout' ).change( function() {
			cpswExpressCheckoutLayoutEffects();
		} );

		$( '#cpsw_express_checkout_location' ).change( function() {
			toggleOptions();
		} );

		if ( $( document ).width() > 1200 ) {
			const buttonPreview = $( '.cpsw_express_checkout_preview_wrapper' ).parents( 'fieldset' );
			buttonPreview.parents( 'tr' ).hide();
			$( '.submit' ).after( '<div class="cpsw_floating_preview"><span class="cpsw_button_preview_label">Button preview</div>' );
			$( '.cpsw_floating_preview' ).append( buttonPreview );

			const maxTop = $( '#cpsw_express_checkout-description' ).offset().top - 60;
			const absoluteLeft = 900 - jQuery( '#adminmenuwrap' ).width();
			$( '.cpsw_floating_preview' ).css(
				{
					position: 'absolute',
					top: maxTop,
					left: absoluteLeft,
				},
			);

			$( window ).scroll( function() {
				if ( maxTop - 110 < $( window ).scrollTop() ) {
					$( '.cpsw_floating_preview' ).css(
						{
							position: 'fixed',
							top: '200px',
							left: 900,
						},
					);
				} else {
					$( '.cpsw_floating_preview' ).css(
						{
							position: 'absolute',
							top: maxTop,
							left: absoluteLeft,
						},
					);
				}
			} );
		}

		$( '#cpsw_express_checkout_title' ).keyup( function() {
			$( '.cpsw_preview_title' ).html( $( this ).val() );
		} );

		$( '#cpsw_express_checkout_tagline' ).keyup( function() {
			$( '.cpsw_preview_tagline' ).html( $( this ).val() );
		} );

		$( '#cpsw_express_checkout_button_width' ).change( function() {
			let buttonWidth = $( this ).val();

			if ( '' === buttonWidth ) {
				buttonWidth = '100%';
			}

			if ( buttonWidth > 380 ) {
				$( '.cpsw-payment-request-custom-button-render' ).css( 'max-width', buttonWidth );
				$( '.cpsw-payment-request-custom-button-render' ).css( 'width', '100%' );
			} else if ( buttonWidth < 100 && '' !== buttonWidth ) {
				$( '.cpsw-payment-request-custom-button-render' ).css( 'width', '112px' );
				$( '.cpsw-payment-request-custom-button-render' ).css( 'min-width', '112px' );
			} else {
				$( '.cpsw-payment-request-custom-button-render' ).width( buttonWidth );
			}
		} );

		$( '#cpsw_express_checkout_button_alignment' ).change( function() {
			$( '.cpsw_express_checkout_preview_wrapper' ).css( { textAlign: $( this ).val() } );
			if ( $( this ).val() === 'center' ) {
				$( '.cpsw-payment-request-custom-button-admin' ).css( { margin: '0 auto', float: 'none' } );
			} else {
				$( '.cpsw-payment-request-custom-button-admin' ).css( { marginBottom: '1em', float: $( this ).val() } );
			}
		} );
	} );
}( jQuery ) );
