( function ( $ ) {
	/**
	 * Function to add the custom Class to the color picker to add a separator.
	 * This is used to add the add a heading to display the CartFlows Colors.
	 */
	const add_color_pallet_separator = function () {
		$( document ).on(
			'click',
			'.block-editor-panel-color-gradient-settings__dropdown, .component-color-indicator',
			function () {
				$( '.components-circular-option-picker__option' ).each(
					function () {
						const color_separator = $( this );
						let color_separator_wrapper = '';

						if (
							color_separator.attr( 'aria-label' ) ===
							'Color: CartFlows Separator'
						) {
							color_separator_wrapper = color_separator.closest(
								'.components-circular-option-picker__option-wrapper'
							);

							if (
								! color_separator_wrapper.hasClass(
									'wcf-color-pallet-separator'
								)
							) {
								color_separator_wrapper.addClass(
									'wcf-color-pallet-separator'
								);
								color_separator.addClass(
									'wcf-color-pallet-separator--button'
								);
								color_separator.append(
									'<span class="wcf-color-pallet-separator--heading">CartFlows Global Colors</span>'
								);
							}
						}
					}
				);
			}
		);
	};

	$( function () {
		add_color_pallet_separator();
	} );
} )( jQuery );
