import React, { useState } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import Spinner from '@Admin/components/Spinner';
import { useNavigate } from 'react-router-dom';

function ExpressCheckout() {
	const [ clicked, setClicked ] = useState( false );
	const navigate = useNavigate();

	function enableExpressCheckout() {
		setClicked( true );
		const formData = new window.FormData();

		formData.append(
			'action',
			'cpsw_onboarding_enable_express_checkout',
		);
		formData.append(
			'security',
			onboarding_vars.cpsw_onboarding_enable_express_checkout,
		);

		apiFetch( {
			url: onboarding_vars.ajax_url,
			method: 'POST',
			body: formData,
		} ).then( ( res ) => {
			if ( res.success ) {
				navigate(
					onboarding_vars.navigator_base + `&cpsw_call=thank-you`,
				);
			}
		} );
	}

	return (
		<main className="mt-10 mx-auto w-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
			<div className="text-center">
				<h1 className="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
					<span className="block xl">{ __( 'Wooho!!', 'checkout-plugins-stripe-woo' ) }</span>
					<span className="block text-cart-500 xl:inline">{ __( 'You are almost done.', 'checkout-plugins-stripe-woo' ) }</span>
				</h1>
				<p className="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
					<span className="block"> { __( 'Since you have enabled', 'checkout-plugins-stripe-woo' ) } <span className="block text-gray-700 xl:inline font-bold">{ __( 'Stripe Card Processing', 'checkout-plugins-stripe-woo' ) },</span>{ __( ' We recommend you to enable', 'checkout-plugins-stripe-woo' ) } <span className="block text-gray-700 xl:inline font-bold">{ __( 'Express Checkout', 'checkout-plugins-stripe-woo' ) }</span> { __( 'feature', 'checkout-plugins-stripe-woo' ) }.</span>
					<span>{ __( 'Express Checkout generates more conversions!!', 'checkout-plugins-stripe-woo' ) }</span>
				</p>
				<div className="block mx-auto mt-1 mb-1">
					<img className="inline mx-4 py-5 h-24" src={ onboarding_vars.assets_url + 'images/apple-pay.svg' } alt="Express Checkout" />
					<img className="inline mx-4 py-5 h-24" src={ onboarding_vars.assets_url + 'images/gpay.svg' } alt="Express Checkout" />
				</div>
				<div className="sm:inline-block lg:inline-block sm:justify-center lg:justify-center">
					<div className="rounded-md shadow">
						{ clicked ? (
							<button className="disabled w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-cart-500 hover:bg-cart-700 md:py-4 md:text-lg md:px-10 cursor-wait">
								{ __( 'Enabling…', 'checkout-plugins-stripe-woo' ) }
								<Spinner />
							</button>
						) : (
							<button onClick={ enableExpressCheckout } className="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-cart-500 hover:bg-cart-700 md:py-4 md:text-lg md:px-10 cursor-pointer">
								{ __( 'Enable Express Checkout', 'checkout-plugins-stripe-woo' ) }
							</button> )
						}
					</div>

					<div className="mt-3 sm:mt-0 sm:ml-3">
						<a href={ onboarding_vars.base_url + `&cpsw_call=thank-you` } className="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-slate-300 md:py-4 md:text-lg md:px-10">
							{ __( 'Skip Express Checkout', 'checkout-plugins-stripe-woo' ) }
						</a>
					</div>
				</div>
			</div>
		</main>
	);
}

export default ExpressCheckout;
