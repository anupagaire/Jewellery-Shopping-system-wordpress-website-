import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import Spinner from '@Admin/components/Spinner';
import { useNavigate } from 'react-router-dom';

function HomePage() {
	const [ clicked, setClicked ] = useState( false );
	const navigate = useNavigate();
	function connectWithStripe() {
		setClicked( true );
		if ( '' === onboarding_vars.woocommerce_installed || '' === onboarding_vars.woocommerce_activated ) {
			navigate( onboarding_vars.navigator_base + '&cpsw_call=setup-woocommerce' );
		} else {
			window.location.replace(
				onboarding_vars.authorization_url,
			);
		}
	}

	return (
		<main className="mt-10 mx-auto w-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
			<div className="text-center">
				<h1 className="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
					<span className="block xl"> { __( 'Let\'s Connect', 'checkout-plugins-stripe-woo' ) }</span>
					<span className="block text-cart-500 xl:inline">{ __( 'with Stripe', 'checkout-plugins-stripe-woo' ) }</span>
				</h1>
				<p className="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
					<span className="block"><span className="block text-gray-700 inline font-bold">{ __( 'Checkout Plugins', 'checkout-plugins-stripe-woo' ) }</span> { __( 'recommends to connect with', 'checkout-plugins-stripe-woo' ) } <span className="block text-gray-700 xl:inline font-bold">{ __( 'Stripe connect.', 'checkout-plugins-stripe-woo' ) }</span></span>
					<span>{ __( 'One click onboarding solution provided by', 'checkout-plugins-stripe-woo' ) } <span className="block text-gray-700 xl:inline font-bold">{ __( 'Stripe.', 'checkout-plugins-stripe-woo' ) }</span></span>
				</p>
				<div className="mt-5 sm:mt-8 sm:inline-block lg:inline-block sm:justify-center lg:justify-center">
					<div className="rounded-md shadow">

						{ clicked ? (
							<button className="disabled w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-cart-500 hover:bg-cart-700 md:py-4 md:text-lg md:px-10 cursor-wait">
								{ __( 'Connecting…', 'checkout-plugins-stripe-woo' ) }
								<Spinner />
							</button>
						) : (
							<button onClick={ connectWithStripe } className="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-cart-500 hover:bg-cart-700 md:py-4 md:text-lg md:px-10 cursor-pointer">
								{ __( 'Connect with Stripe', 'checkout-plugins-stripe-woo' ) }
							</button> )
						}
					</div>
					<div className="mt-3 sm:mt-0 sm:ml-3">
						<a href={ onboarding_vars.settings_url } className="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-slate-300 md:py-4 md:text-lg md:px-10">
							{ __( 'Leave onboarding process', 'checkout-plugins-stripe-woo' ) }
						</a>
					</div>
				</div>
			</div>
		</main>
	);
}

export default HomePage;
