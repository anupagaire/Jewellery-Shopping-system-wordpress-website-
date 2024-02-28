( function() {
	const pubKey = cpsw_admin_stripe_elements.public_key;
	const mode = cpsw_admin_stripe_elements.mode;
	const clientSecret = cpsw_admin_stripe_elements.client_secret;

	if ( '' === clientSecret || '' === pubKey || ( 'live' === mode && ! cpsw_admin_stripe_elements.is_ssl ) ) {
		return;
	}

	const stripe = Stripe( pubKey );

	// Register stripe app info
	stripe.registerAppInfo( {
		name: 'Checkout Plugins - Stripe Woo',
		partner_id: 'pp_partner_KOjySVEy3ClX6G',
		version: cpsw_admin_stripe_elements.cpsw_version,
		url: 'https://wordpress.org/plugins/checkout-plugins-stripe-woo/',
	} );

	stripe.confirmSepaDebitPayment( clientSecret, {} ).then( function() {} );
}( jQuery ) );
