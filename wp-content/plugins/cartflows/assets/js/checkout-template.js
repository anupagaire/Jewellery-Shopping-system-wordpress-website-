( function ( $ ) {
	const wcf_update_checkout_on_return = function () {
		const vis = ( function () {
			let stateKey, eventKey;
			const keys = {
				hidden: 'visibilitychange',
				webkitHidden: 'webkitvisibilitychange',
				mozHidden: 'mozvisibilitychange',
				msHidden: 'msvisibilitychange',
			};
			for ( stateKey in keys ) {
				if ( stateKey in document ) {
					eventKey = keys[ stateKey ];
					break;
				}
			}

			return function ( c ) {
				if ( c ) {
					document.addEventListener( eventKey, c );
				}
				return ! document[ stateKey ];
			};
		} )();

		function getCookie( name ) {
			const cookieArr = document.cookie.split( ';' );
			for ( let i = 0; i < cookieArr.length; i++ ) {
				const cookiePair = cookieArr[ i ].split( '=' );

				if ( name === cookiePair[ 0 ].trim() ) {
					return decodeURIComponent( cookiePair[ 1 ] );
				}
			}
			return null;
		}

		vis( function () {
			const active_checkout_cookie = getCookie(
				cartflows.active_checkout_cookie
			);

			if ( active_checkout_cookie && vis() ) {
				if (
					parseInt( cartflows.current_step ) !==
					parseInt( active_checkout_cookie )
				) {
					// Add loader.
					$(
						'.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table'
					).block( {
						message: null,
						overlayCSS: {
							background: '#fff',
							opacity: 0.6,
						},
					} );

					console.log( 'Multiple checkouts are open.' );

					$( document.body ).trigger( 'update_checkout' );

					$( document ).ajaxComplete( function ( event, xhr ) {
						if ( ! xhr.hasOwnProperty( 'responseJSON' ) ) {
							return;
						}
						const fragmants = xhr.responseJSON.hasOwnProperty(
							'fragments'
						)
							? xhr.responseJSON.fragments
							: null;

						if (
							fragmants &&
							fragmants.hasOwnProperty( 'wcf_cart_data' )
						) {
							$( document.body ).trigger(
								'wcf_cart_data_restored',
								[ fragmants.wcf_cart_data ]
							);
						}
					} );
				}
			}
		} );
	};

	/**
	 * Checkout Custom Field Validations
	 * This will collect all the present fields in the woocommerce form and adds an class if the field
	 * is blank
	 */
	const wcf_custom_field_validation = function () {
		/**
		 * Controls the display of the error message on the basis of backend setting.
		 *
		 * @param {boolean} field_required
		 * @param {string}  field_row
		 * @param {string}  field_wrap
		 */
		const add_validation_msg = function (
			field_required = false,
			field_row,
			field_wrap
		) {
			field_row.find( '.wcf-field-required-error' ).remove();

			if (
				field_required &&
				'yes' === cartflows.field_validation.is_enabled
			) {
				const label_text = field_row.find( 'label' ).text();
				field_wrap.after(
					'<span class="wcf-field-required-error">' +
						label_text.replace( /\*/g, '' ).trim() +
						' ' +
						cartflows.field_validation.error_msg +
						'</span>'
				);
			} else {
				field_row.find( '.wcf-field-required-error' ).remove();
			}
		};

		const custom_field_add_class = function (
			field_value,
			field_row,
			field_wrap,
			field_type
		) {
			let isError = false;

			if (
				field_value === '' ||
				( 'select' === field_type && field_value === ' ' )
			) {
				if ( field_row.hasClass( 'validate-required' ) ) {
					field_wrap.addClass( 'field-required' );
					isError = true;
				}
			} else {
				field_wrap.removeClass( 'field-required' );
			}

			add_validation_msg( isError, field_row, field_wrap );
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

		const fields_wrapper = $( 'form.woocommerce-checkout' ),
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

	const wcf_checkout_coupons = {
		init() {
			$( document.body ).on(
				'click',
				'.wcf-submit-coupon',
				this.submit_coupon
			);
			$( document.body ).on(
				'click',
				'.wcf-remove-coupon',
				this.remove_coupon
			);
		},

		submit_coupon( e ) {
			e.preventDefault();
			const coupon_wrapper_class = $( '.wcf-custom-coupon-field' ),
				coupon_wrapper = $( this ).closest( coupon_wrapper_class ),
				coupon_field = coupon_wrapper.find( '.wcf-coupon-code-input' ),
				coupon_value = coupon_field.val();

			if ( '' === coupon_value ) {
				coupon_field.addClass( 'field-required' );
				return false;
			}
			coupon_field.removeClass( 'field-required' );

			const data = {
				coupon_code: coupon_value,
				action: 'wcf_woo_apply_coupon',
				security: cartflows.wcf_validate_coupon_nonce,
			};

			$.ajax( {
				type: 'POST',
				url: cartflows.ajax_url,
				data,

				success( code_data ) {
					const coupon_message = $( '.wcf-custom-coupon-field' );
					coupon_message
						.find( '.woocommerce-error, .woocommerce-message' )
						.remove();

					if ( code_data && code_data.status === true ) {
						$( document.body ).trigger( 'update_checkout', {
							update_shipping_method: false,
						} );
						coupon_message.prepend( code_data.msg );
						coupon_wrapper_class.addClass( 'wcf-coupon-applied' );
					} else if ( code_data && code_data.msg ) {
						coupon_message.prepend( code_data.msg );
						coupon_wrapper_class.removeClass(
							'wcf-coupon-applied'
						);
					} else {
						console.log(
							'Error: Error while applying the coupon. Response: ' +
								code_data.data && code_data.data.error
								? code_data.data.error
								: code_data
						);
					}
				},
			} );
		},

		remove_coupon( e ) {
			e.preventDefault();
			const data = {
				coupon_code: $( this ).attr( 'data-coupon' ),
				action: 'wcf_woo_remove_coupon',
				security: cartflows.wcf_validate_remove_coupon_nonce,
			};

			$.ajax( {
				type: 'POST',
				url: cartflows.ajax_url,
				data,

				success( code ) {
					const coupon_message = $( '.wcf-custom-coupon-field' );
					coupon_message
						.find( '.woocommerce-error, .woocommerce-message' )
						.hide();

					$( '.wcf-custom-coupon-field' ).removeClass(
						'wcf-coupon-applied'
					);

					if ( code ) {
						$( document.body ).trigger( 'update_checkout', {
							update_shipping_method: false,
						} );
						coupon_message.prepend( code );
					}
				},
			} );
		},
	};

	const wcf_remove_cart_products = function () {
		$( document.body ).on(
			'click',
			'#wcf-embed-checkout-form .wcf-remove-product',
			function ( e ) {
				e.preventDefault();
				const p_id = $( this ).attr( 'data-id' );
				const data = {
					p_key: $( this ).attr( 'data-item-key' ),
					p_id,
					action: 'wcf_woo_remove_cart_product',
					security: cartflows.wcf_validate_remove_cart_product_nonce,
				};

				$.ajax( {
					type: 'POST',
					url: cartflows.ajax_url,
					data,

					success( response ) {
						const res_data = JSON.parse( response );

						if ( res_data.need_shipping === false ) {
							// $('#wcf-embed-checkout-form').find('#ship-to-different-address-checkbox').hide();
							$( '#wcf-embed-checkout-form' )
								.find( '#ship-to-different-address-checkbox' )
								.attr( 'checked', false );
						}
						$( '#wcf-embed-checkout-form' )
							.find( '.woocommerce-notices-wrapper' )
							.first()
							.html( res_data.msg );
						$( document ).trigger( 'cartflows_remove_product', [
							p_id,
						] );
						$( '#wcf-embed-checkout-form' ).trigger(
							'update_checkout'
						);
					},
				} );
			}
		);
	};

	const wcf_toggle_optimized_fields = function () {
		jQuery.each(
			cartflows_checkout_optimized_fields,
			function ( field, cartflows_optimized_field ) {
				if ( cartflows_optimized_field.is_optimized ) {
					jQuery( '#' + field ).prepend(
						'<a href="#" id="wcf_optimized_' +
							field +
							'">' +
							cartflows_optimized_field.field_label +
							'</a>'
					);
					jQuery( '#wcf_optimized_' + field ).on(
						'click',
						function ( e ) {
							e.preventDefault();
							jQuery( '#' + field ).removeClass(
								'wcf-hide-field'
							);
							// jQuery("#" + field).removeClass('mt20')
							const field_id = field.replace( /_field/g, '' );
							$( '#' + field_id ).trigger( 'focus' );
							jQuery( this ).remove();
						}
					);
				}
			}
		);
	};

	const wcf_anim_field_style_two = function () {
		const $inputs = $(
			'.wcf-field-modern-label .woocommerce input, .wcf-field-modern-label .woocommerce select, .wcf-field-modern-label .woocommerce textarea'
		);

		/**
		 * Add wcf-anim-label class when floating label option is enabled.
		 * The class will be added only when the info is started to type in the checkout fields.
		 *
		 * @param {Object} $this the object of current checkout field
		 */
		const _add_anim_class = function ( $this ) {
			const field_row = $this.closest( '.form-row' ),
				is_select =
					$this.is( 'select' ) ||
					$this.hasClass( 'select2-hidden-accessible' ),
				field_value = is_select
					? $this.find( ':selected' ).text()
					: $this.val(),
				field_type = $this.attr( 'type' );

			if ( '' === field_value ) {
				field_row.removeClass( 'wcf-anim-label' );
			} else if ( 'hidden' === field_type ) {
				field_row.addClass( 'wcf-anim-hidden-label' );
			} else {
				field_row.addClass( 'wcf-anim-label' );
			}
		};

		// Trigger the addition of anim class when focused or info is inputed on the field.
		$inputs.on( 'focusout input', function () {
			const $this = $( this );
			_add_anim_class( $this );
		} );

		// Load the anim class on the page ready.
		$( $inputs ).each( function () {
			_add_anim_class( $( this ) );
		} );
	};

	const validateEmail = function ( email ) {
		const email_reg =
			/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return email_reg.test( email );
	};

	let xhrCountEmail = 0;
	let delayTimerEmail;

	const inline_email_address_validation = function () {
		const email = $( '.wcf-customer-info #billing_email' ).val();

		if ( 'undefined' === typeof email || cartflows.is_logged_in ) {
			return;
		}

		if ( '' !== email ) {
			const email_field = $( '#billing_email' );
			const validation_msg_wrap = $( '.wcf-email-validation-block' ),
				customer_login_wrap = $( '.wcf-customer-login-section' );

			validation_msg_wrap.remove();

			if ( ! validateEmail( email ) ) {
				email_field.after(
					'<span class="wcf-email-validation-block error">' +
						cartflows.email_validation_msgs.error_msg +
						'</span>'
				);
				customer_login_wrap.removeClass( 'wcf-show' );
				return false;
			}

			clearTimeout( delayTimerEmail );

			const seqNumber = ++xhrCountEmail;

			delayTimerEmail = setTimeout( function () {
				$.ajax( {
					url: cartflows.ajax_url,
					type: 'POST',
					data: {
						action: 'wcf_check_email_exists',
						email_address: email,
						security: cartflows.check_email_exist_nonce,
					},
					success( resp ) {
						if ( seqNumber !== xhrCountEmail ) {
							return;
						}

						validation_msg_wrap.remove();

						if (
							resp.data &&
							resp.data.success &&
							customer_login_wrap.hasClass( 'wcf-show' )
						) {
							email_field.after(
								'<span class="wcf-email-validation-block success">' +
									cartflows.email_validation_msgs
										.success_msg +
									'</span>'
							);
							return;
						}

						if ( resp.data && resp.data.success ) {
							if ( resp.data.is_login_allowed ) {
								email_field.after(
									'<span class="wcf-email-validation-block success">' +
										cartflows.email_validation_msgs
											.success_msg +
										'</span>'
								);

								customer_login_wrap
									.slideDown( 400 )
									.addClass( 'wcf-show' );
							}

							$( '.wcf-create-account-section' ).hide();
							$(
								'.woocommerce-billing-fields__customer-login-label'
							).show();
						} else {
							customer_login_wrap
								.slideUp( 400 )
								.removeClass( 'wcf-show' );

							//Learndash Woo integration plugin hides the create aacount checkbox.So need to show it again.
							$(
								'.wcf-create-account-section .create-account label.checkbox'
							).show();

							$( '.wcf-create-account-section' ).show();

							$(
								'.woocommerce-billing-fields__customer-login-label'
							).hide();
						}
					},
				} );
			}, 300 );
		} else {
			$( '.wcf-create-account-section' ).hide();
			$( '.wcf-customer-login-section' ).hide();
			$( '.wcf-email-validation-block' ).hide();
			$( '.woocommerce-billing-fields__customer-login-label' ).show();
		}

		return false;
	};

	const woocommerce_user_login = function () {
		$( '.wcf-customer-login-url' ).on( 'click', function login_form( e ) {
			e.preventDefault();
			const customer_login_wrap = $( '.wcf-customer-login-section' );

			if ( customer_login_wrap.hasClass( 'wcf-show' ) ) {
				customer_login_wrap.slideUp( 400 );
				customer_login_wrap.removeClass( 'wcf-show' );
			} else {
				customer_login_wrap.slideDown( 400 );
				customer_login_wrap.addClass( 'wcf-show' );
			}
		} );

		$( '.wcf-customer-login-section__login-button' ).on(
			'click',
			function name() {
				const email = $( '#billing_email' ).val();
				const password = $( '#billing_password' ).val();

				$.ajax( {
					url: cartflows.ajax_url,
					type: 'POST',
					data: {
						action: 'wcf_woocommerce_login',
						email,
						password,
						security: cartflows.woocommerce_login_nonce,
					},
					success( resp ) {
						if ( resp.data && resp.data.success ) {
							location.reload();
						} else {
							$( '.wcf-customer-info__notice' )
								.addClass( 'wcf-notice' )
								.html( resp.data.error );
						}
					},
				} );
			}
		);
	};

	const wcf_order_review_toggler = function () {
		const mobile_order_review_section = $(
				'.wcf-collapsed-order-review-section'
			),
			mobile_order_review_wrap = $(
				'.wcf-cartflows-review-order-wrapper'
			),
			desktop_order_review_wrap = $( '.wcf-order-wrap' );

		let timeout = false;
		const resizeEvent =
			'onorientationchange' in window ? 'orientationchange' : 'resize';

		$( '.wcf-order-review-toggle' ).on(
			'click',
			function wcf_show_order_summary( e ) {
				e.preventDefault();

				if ( mobile_order_review_section.hasClass( 'wcf-show' ) ) {
					mobile_order_review_wrap.slideUp( 400 );
					mobile_order_review_section.removeClass( 'wcf-show' );
					$( '.wcf-order-review-toggle-text' ).text(
						cartflows.order_review_toggle_texts.toggle_show_text
					);
				} else {
					mobile_order_review_wrap.slideDown( 400 );
					mobile_order_review_section.addClass( 'wcf-show' );
					$( '.wcf-order-review-toggle-text' ).text(
						cartflows.order_review_toggle_texts.toggle_hide_text
					);
				}
			}
		);

		$( window ).on( resizeEvent, function () {
			clearTimeout( timeout );

			timeout = setTimeout( function () {
				const width = window.innerWidth || $( window ).width();

				if ( width >= 769 ) {
					mobile_order_review_wrap.css( { display: 'none' } );
					mobile_order_review_wrap.removeClass( 'wcf-show' );
					$( '.wcf-order-review-toggle' ).removeClass( 'wcf-show' );
					$( '.wcf-order-review-toggle-text' ).text(
						cartflows.order_review_toggle_texts.toggle_show_text
					);
				}
			}, 200 );
		} );

		// Update checkout when shipping methods changes.
		mobile_order_review_wrap.on(
			'change',
			'select.shipping_method, input[name^="shipping_method"]',
			function () {
				/**
				 * Uncheck all shipping radio buttons of desktop. Those will be auto updated by update_checkout action.
				 * While performing the update checkout, it searches for the selected shipping method in whole page.
				 */
				desktop_order_review_wrap
					.find(
						'input[name^="shipping_method"][type="radio"]:checked'
					)
					.each( function () {
						$( this ).removeAttr( 'checked' );
					} );

				$( document.body ).trigger( 'update_checkout', {
					update_shipping_method: true,
				} );
			}
		);
	};

	$( function () {
		wcf_persistent_data();

		wcf_update_checkout_on_return();

		wcf_custom_field_validation();

		wcf_remove_cart_products();

		wcf_checkout_coupons.init();

		wcf_toggle_optimized_fields();

		wcf_anim_field_style_two();

		// On email input field change.
		$( '.wcf-customer-info #billing_email' ).on( 'input', function () {
			inline_email_address_validation();
		} );

		// On page load as we saves the checkout fields values.
		if ( $( '.wcf-customer-info #billing_email' ).length > 0 ) {
			inline_email_address_validation();
		}

		woocommerce_user_login();

		wcf_order_review_toggler();
	} );
} )( jQuery );
