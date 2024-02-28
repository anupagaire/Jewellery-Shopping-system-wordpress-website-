import React from 'react';
import { __ } from '@wordpress/i18n';

function Failed() {
	return (
		<main className="mt-10 mx-auto w-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-14 lg:mt-16 lg:px-8 xl:mt-18">
			<div className="text-center">
				<h1 className="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
					<span className="block text-red-600 xl:inline">{ __( 'Failed!!', 'checkout-plugins-stripe-woo' ) }</span>
				</h1>
				<p className="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
					<span className="block">{ __( 'Unfortunately Connection to Stripe failed.', 'checkout-plugins-stripe-woo' ) }</span>
				</p>
				<div className="mt-5 sm:mt-8 sm:inline-block lg:inline-block sm:justify-center lg:justify-center">
					<div className="rounded-md shadow">
						<a href={ onboarding_vars.authorization_url } className="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-cart-500 hover:bg-cart-700 md:py-4 md:text-lg md:px-10">
							{ __( 'Try Again', 'checkout-plugins-stripe-woo' ) }
						</a>
					</div>

					<div className="mt-3 sm:mt-0 sm:ml-3">
						<a href={ onboarding_vars.manual_connect_url } className="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-slate-300 md:py-4 md:text-lg md:px-10">
							{ __( 'Manage API keys manually', 'checkout-plugins-stripe-woo' ) }
						</a>
					</div>
				</div>
			</div>
		</main>
	);
}

export default Failed;
