( function ( $ ) {
	FLBuilder.registerModuleHelper( 'cartflows-bb-order-details', {
		init() {
			const form = $( '.fl-builder-settings' ),
				order_details_field = form.find(
					'select[name=show_order_details]'
				);

			// Init validation events.
			this._toggle_orderdetails();

			// Validation events.
			order_details_field.on(
				'change',
				$.proxy( this._toggle_orderdetails, this )
			);
		},

		_toggle_orderdetails() {
			const form = $( '.fl-builder-settings' ),
				show_order_details = form
					.find( 'select[name=show_order_details]' )
					.val();

			if ( show_order_details === 'yes' ) {
				form.find(
					'#fl-builder-settings-section-section_order_details'
				).show();
			} else if ( show_order_details === 'no' ) {
				form.find(
					'#fl-builder-settings-section-section_order_details'
				).hide();
			}
		},
	} );
} )( jQuery );
