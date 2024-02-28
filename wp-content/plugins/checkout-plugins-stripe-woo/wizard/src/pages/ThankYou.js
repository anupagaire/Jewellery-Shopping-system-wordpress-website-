import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import Spinner from '@Admin/components/Spinner';

function ThankYou() {
	const [ clicked, setClicked ] = useState( false );

	function letsCustomize() {
		setClicked( true );
		window.location.replace(
			onboarding_vars.gateways_url,
		);
	}

	return (
		<main className="mt-10 mx-auto w-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-14 lg:mt-16 lg:px-8 xl:mt-18">
			<div className="text-center">
				<h1 className="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
					<span className="block text-cart-500 xl:inline">{ __( 'Great!!', 'checkout-plugins-stripe-woo' ) }</span>
				</h1>
				<p className="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
					<span className="block">{ __( 'Your store is all set to accept payment.', 'checkout-plugins-stripe-woo' ) }</span>
					<span>{ __( 'We provide lots of customization options to match your needs, don\'t forget to explore them.', 'checkout-plugins-stripe-woo' ) }</span>
				</p>
				<div className="mt-5 sm:mt-8 sm:inline-block lg:inline-block sm:justify-center lg:justify-center">
					<div className="rounded-md shadow">
						{ clicked ? (
							<button className="disabled w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-cart-500 hover:bg-cart-700 md:py-4 md:text-lg md:px-10 cursor-wait">
								{ __( 'Let\'s Customize…', 'checkout-plugins-stripe-woo' ) }
								<Spinner />
							</button>
						) : (
							<button onClick={ letsCustomize } className="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-cart-500 hover:bg-cart-700 md:py-4 md:text-lg md:px-10 cursor-pointer">
								{ __( 'Let\'s Customize', 'checkout-plugins-stripe-woo' ) }
							</button> )
						}
					</div>
				</div>
			</div>
		</main>
	);
}

export default ThankYou;
