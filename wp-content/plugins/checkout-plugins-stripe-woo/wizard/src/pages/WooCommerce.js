import React, { useState } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import Spinner from '@Admin/components/Spinner';

function WooCommerce() {
	if ( '' !== onboarding_vars.woocommerce_installed && '' !== onboarding_vars.woocommerce_activated ) {
		window.location.replace(
			onboarding_vars.navigator_base,
		);
	}

	const [ state, setState ] = useState( '' );
	const [ notification, setNotification ] = useState( false );
	const [ notificationError, setErrorNotification ] = useState( false );
	function installWooCommerce() {
		if ( '' === onboarding_vars.woocommerce_installed ) {
			setState( 'installing' );
			setTimeout( () => {
				setNotification( true );
			}, 10000 );
			wp.updates.queue.push( {
				action: 'install-plugin', // Required action.
				data: {
					slug: 'woocommerce',
				},
			} );

			// Required to set queue.
			wp.updates.queueChecker();
		} else {
			setState( 'activating' );
			const formData = new window.FormData();

			formData.append(
				'action',
				'cpsw_onboarding_install_woocommerce',
			);
			formData.append(
				'security',
				onboarding_vars.cpsw_onboarding_install_woocommerce,
			);

			apiFetch( {
				url: onboarding_vars.ajax_url,
				method: 'POST',
				body: formData,
			} ).then( ( res ) => {
				if ( res.success ) {
					window.location.replace(
						onboarding_vars.onboarding_base,
					);
				} else {
					setErrorNotification( true );
				}
			} );
		}
	}

	return (
		<main className="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
			<div className="text-center">
				<h1 className="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
					<span className="block xl">{ '' === onboarding_vars.woocommerce_installed ? __( 'Missing', 'checkout-plugins-stripe-woo' ) : __( 'Inactiave', 'checkout-plugins-stripe-woo' ) }</span>
					<span className="block text-cart-500 xl:inline">{ __( 'WooCoomerce', 'checkout-plugins-stripe-woo' ) }</span>
				</h1>
				<p className="mt-6 text-base justify-center text-gray-500 sm:mt-5 sm:text-lg sm:w-full sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
					<span className="block text-gray-700 xl:inline font-bold">{ __( 'Checkout Plugins - Stripe for WooCoomerce', 'checkout-plugins-stripe-woo' ) }</span> { __( 'requires', 'checkout-plugins-stripe-woo' ) } <span className="block text-gray-700 xl:inline font-bold">{ __( 'WooCommerce', 'checkout-plugins-stripe-woo' ) }</span> { __( 'to be active on your store.', 'checkout-plugins-stripe-woo' ) }
				</p>
				<div className="mt-5 sm:mt-8 sm:flex justify-center">
					<div className="rounded-md shadow">
						{ ( () => {
							if ( 'installing' === state ) {
								return (
									<button className="disabled w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-cart-500 hover:bg-cart-700 md:py-4 md:text-lg md:px-10  cursor-wait">
										{ __( 'Installing…', 'checkout-plugins-stripe-woo' ) }
										<Spinner />
									</button>
								);
							} else if ( 'activating' === state ) {
								return (
									<button className="disabled w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-cart-500 hover:bg-cart-700 md:py-4 md:text-lg md:px-10  cursor-wait">
										{ __( 'Activating…', 'checkout-plugins-stripe-woo' ) }
										<Spinner />
									</button>
								);
							}
							return (
								<button onClick={ installWooCommerce } className="install-dependency w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-cart-500 hover:bg-cart-700 md:py-4 md:text-lg md:px-10 cursor-pointer">
									{ '' === onboarding_vars.woocommerce_installed ? __( 'Install and continue', 'checkout-plugins-stripe-woo' ) : __( 'Activate and continue', 'checkout-plugins-stripe-woo' ) }
								</button>
							);
						} )() }
					</div>
				</div>
			</div>
			{ notification ? (
				<div className="bg-cart-50 p-4 fixed left-0 top-0 right-0 transition ease-in-out delay-150">
					<div className="block">
						<div className="text-center justify-center">
							<p className="text-sm mx-auto w-full text-cart-500 text-center">{ __( 'Installing WooCommerce will take time. Please be patient.', 'checkout-plugins-stripe-woo' ) }</p>
						</div>
					</div>
				</div>
			) : ( '' ) }

			{ notificationError ? (
				<div className="bg-cart-50 p-4 fixed left-0 top-0 right-0 transition ease-in-out delay-150">
					<div className="block">
						<div className="text-center justify-center">
							<p className="text-sm mx-auto w-full text-cart-500 text-center">{ __( 'WooCommerce installing failed. Please try again.', 'checkout-plugins-stripe-woo' ) }</p>
						</div>
					</div>
				</div>
			) : ( '' ) }

		</main>
	);
}

export default WooCommerce;
