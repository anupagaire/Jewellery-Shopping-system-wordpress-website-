import { useState } from 'react';
import { Switch } from '@headlessui/react';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import Spinner from '@Admin/components/Spinner';
import { useNavigate } from 'react-router-dom';

function classNames( ...classes ) {
	return classes.filter( Boolean ).join( ' ' );
}

function Success() {
	const [ clicked, setClicked ] = useState( false );
	const [ gateways, setGateways ] = useState( onboarding_vars.available_gateways );
	const navigate = useNavigate();

	function enableGateways( e ) {
		e.preventDefault();
		setClicked( true );
		const formData = new window.FormData();

		const object = {};
		gateways.forEach( function( value ) {
			object[ value.id ] = document.getElementsByName( value.id )[ 0 ].value;
		} );
		const json = JSON.stringify( object );

		formData.append( 'formdata', json );
		formData.append(
			'action',
			'cpsw_onboarding_enable_gateway',
		);
		formData.append(
			'security',
			onboarding_vars.cpsw_onboarding_enable_gateway,
		);

		apiFetch( {
			url: onboarding_vars.ajax_url,
			method: 'POST',
			body: formData,
		} ).then( ( res ) => {
			if ( res.success ) {
				if ( true === res.data.activated_gateways.cpsw_stripe ) {
					navigate( onboarding_vars.navigator_base + `&cpsw_call=express-checkout` );
				} else {
					navigate( onboarding_vars.navigator_base + `&cpsw_call=webhooks` );
				}
			}
		} );
	}

	return (
		<main className="mt-4 mb-4 mx-auto w-auto max-w-7xl px-4 sm:mt-6 sm:px-6 md:mt-8 lg:mt-10 lg:px-8 xl:mt-16">
			<div className="text-center">
				<p className="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
					<span className="block"><span className="text-gray-700 inline font-bold">{ __( 'Congratulations!!', 'checkout-plugins-stripe-woo' ) } </span>{ __( 'You are connected to Stripe successfully.', 'checkout-plugins-stripe-woo' ) }</span>
					<span className="block">{ __( 'Let\'s enable gateways', 'checkout-plugins-stripe-woo' ) }</span>
				</p>
				<ul role="list" className="divide-y divide-gray-200 bg-white overflow-hidden sm:rounded-md mt-10 max-w-screen-md mx-auto">
					{ gateways.map( ( gateway ) => (
						<li key={ gateway.id }>
							<span href="#" className="block hover:bg-gray-50">
								<div className="flex items-center px-4 py-4 sm:px-6">
									<div className="min-w-0 flex-1 flex items-center">
										<div className="flex-shrink-0">
											<img className="h-12 w-32 max-w-80" src={ gateway.icon } alt={ gateway.name } />
										</div>
										<div className="min-w-0 flex-1 px-4 md:gap-4">
											<div>
												<p className="text-sm font-medium text-cart-500 flex truncate">{ gateway.name } { gateway.recommended ? (
													<span className="ml-2 px-2 py-1 text-green-800 text-xs font-medium bg-green-100 rounded-full">
														{ __( 'Recommended', 'checkout-plugins-stripe-woo' ) }
													</span>
												) : ( '' ) }
												</p>
												<p className="text-sm font-medium text-gray-400 flex">
													<span className="text-left text-sm" >{ 'all' === gateway.currencies ? ( __( 'Works with all currencies', 'checkout-plugins-stripe-woo' ) ) : ( __( 'Works with ', 'checkout-plugins-stripe-woo' ) + gateway.currencies ) }</span>
												</p>
											</div>
										</div>
									</div>
									<div>
										<Switch
											checked={ gateway.enabled }
											value={ gateway.enabled }
											name={ gateway.id }
											onChange={ () => {
												gateway.enabled = ! gateway.enabled;
												setGateways( [ ...gateways ] );
											} }
											className={ classNames(
												gateway.enabled ? 'bg-cart-500 ' : 'bg-gray-200',
												'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cart-500',
											) }
										>
											<span className="sr-only">{ gateway.id }</span>
											<span
												aria-hidden="true"
												className={ classNames(
													gateway.enabled ? 'translate-x-5' : 'translate-x-0',
													'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200',
												) }
											/>
										</Switch>
									</div>
								</div>
							</span>
						</li>
					) ) }
				</ul>
				<div className="mt-5 sm:mt-8 sm:inline-block lg:inline-block sm:justify-center lg:justify-center">
					<div className="rounded-md shadow">
						{ clicked ? (
							<button className="disabled w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-cart-500 hover:bg-cart-700 md:py-4 md:text-lg md:px-10 cursor-wait">
								{ __( 'Enabling…', 'checkout-plugins-stripe-woo' ) }
								<Spinner />
							</button>
						) : (
							<button onClick={ enableGateways } className="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-cart-500 hover:bg-cart-700 md:py-4 md:text-lg md:px-10 cursor-pointer">
								{ __( 'Enable Gateways', 'checkout-plugins-stripe-woo' ) }
							</button> )
						}

					</div>
				</div>
			</div>
		</main>
	);
}

export default Success;
