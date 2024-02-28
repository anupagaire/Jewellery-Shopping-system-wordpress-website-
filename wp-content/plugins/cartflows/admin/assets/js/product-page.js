( function ( $ ) {
	const wcf_flows_search_init = function () {
		const product_search = $( '.wcf-flows-search' );

		if ( product_search.length > 0 ) {
			$( 'select.wcf-flows-search' ).select2();
			const nonce = $(
				'input[name="wcf_json_search_flows_nonce"]'
			).val();

			$( 'select.wcf-flows-search' )
				.filter( ':not(.enhanced)' )
				.each( function () {
					let select2_args = {
						allowClear: $( this ).data( 'allow_clear' )
							? true
							: false,
						placeholder: $( this ).data( 'placeholder' ),
						minimumInputLength: $( this ).data(
							'minimum_input_length'
						)
							? $( this ).data( 'minimum_input_length' )
							: '3',
						escapeMarkup( m ) {
							return m;
						},

						ajax: {
							url: ajaxurl,
							dataType: 'json',
							quietMillis: 250,
							method: 'post',
							data( params ) {
								return {
									term: params.term,
									action:
										$( this ).data( 'action' ) ||
										'wcf_json_search_flows',

									security: nonce,
								};
							},
							processResults( data ) {
								const terms = [];
								if ( data ) {
									$.each( data, function ( id, text ) {
										terms.push( {
											id,
											text,
										} );
									} );
								}
								return { results: terms };
							},
							cache: true,
						},
					};

					select2_args = $.extend(
						select2_args,
						getEnhancedSelectFormatString()
					);

					$( this ).select2( select2_args ).addClass( 'enhanced' );
				} );
		}
	};

	if ( typeof getEnhancedSelectFormatString === 'undefined' ) {
		// This is the optional function. Not used every time.
		/* eslint-disable */
		function getEnhancedSelectFormatString() {
			const formatString = {
				noResults() {
					return wc_enhanced_select_params.i18n_no_matches;
				},
				errorLoading() {
					return wc_enhanced_select_params.i18n_searching;
				},
				inputTooShort( args ) {
					const remainingChars = args.minimum - args.input.length;

					if ( 1 === remainingChars ) {
						return wc_enhanced_select_params.i18n_input_too_short_1;
					}

					return wc_enhanced_select_params.i18n_input_too_short_n.replace(
						'%qty%',
						remainingChars
					);
				},
				inputTooLong( args ) {
					const overChars = args.input.length - args.maximum;

					if ( 1 === overChars ) {
						return wc_enhanced_select_params.i18n_input_too_long_1;
					}

					return wc_enhanced_select_params.i18n_input_too_long_n.replace(
						'%qty%',
						overChars
					);
				},
				maximumSelected( args ) {
					if ( args.maximum === 1 ) {
						return wc_enhanced_select_params.i18n_selection_too_long_1;
					}

					return wc_enhanced_select_params.i18n_selection_too_long_n.replace(
						'%qty%',
						args.maximum
					);
				},
				loadingMore() {
					return wc_enhanced_select_params.i18n_load_more;
				},
				searching() {
					return wc_enhanced_select_params.i18n_searching;
				},
			};

			const language = { language: formatString };

			return language;
		}
		/* eslint-enable */
	}

	$( function () {
		wcf_flows_search_init();
	} );
} )( jQuery );
