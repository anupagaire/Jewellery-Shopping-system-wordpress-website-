import React from 'react';
import { useLocation } from 'react-router-dom';

import HomePage from '@Admin/pages/HomePage';
import Success from '@Admin/pages/Success';
import ExpressCheckout from '@Admin/pages/ExpressCheckout';
import Failed from '@Admin/pages/Failed';
import ThankYou from '@Admin/pages/ThankYou';
import WooCommerce from '@Admin/pages/WooCommerce';
import Logo from '@Admin/components/Logo';

function Settings() {
	const query = new URLSearchParams( useLocation().search );
	const status = query.get( 'cpsw_call' );

	let routePage = <p></p>;
	switch ( status ) {
		case 'success':
			routePage = <Success />;
			break;
		case 'failed':
			routePage = <Failed />;
			break;
		case 'express-checkout':
			routePage = <ExpressCheckout />;
			break;
		case 'thank-you':
			routePage = <ThankYou />;
			break;
		case 'setup-woocommerce':
			routePage = <WooCommerce />;
			break;
		default:
			routePage = <HomePage />;
			break;
	}

	return (
		<div className="relative bg-white overflow-hidden w-10/12 mx-auto my-0 rounded-xl mt-12">
			<div className="max-w-7xl mx-auto overflow-x-hidden">
				<div className="relative z-10 bg-white lg:w-full">
					<Logo />
					{ routePage }
				</div>
			</div>
		</div>
	);
}

export default Settings;
