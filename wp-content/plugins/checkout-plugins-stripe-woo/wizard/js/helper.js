( function( $ ) {
	function installError() {
		$( window ).off( 'beforeunload' );
		alert( 'Failed to install WooCommerce. Try Again' );
		location.reload();
	}

	function installSuccess( ) {
		activatePlugin();
	}

	function activatePlugin() {
		$.ajax( {
			type: 'POST',
			dataType: 'json',
			url: onboarding_vars.ajax_url,
			data: { action: 'cpsw_onboarding_install_woocommerce', security: onboarding_vars.cpsw_onboarding_install_woocommerce },
			success( response ) {
				if ( response.success === true ) {
					window.location.replace(
						onboarding_vars.base_url,
					);
				}
			},
			error() {
				$( 'body' ).css( 'cursor', 'default' );
				alert( 'Something went wrong!' );
			},
		} );
	}

	$( document ).on( 'wp-plugin-install-error', installError )
		.on( 'wp-plugin-install-success', installSuccess );
}( jQuery ) );
