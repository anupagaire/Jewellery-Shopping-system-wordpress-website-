import React from 'react';

function Logo() {
	return (
		<div>
			<div className="relative pt-6 px-4 sm:px-6 lg:px-8">
				<nav className="relative flex items-center justify-center sm:h-10" aria-label="Logo">
					<div className="flex items-center justify-center flex-grow">
						<div className="flex items-center w-auto">
							<img className="h-16 w-full" src={ onboarding_vars.assets_url + 'images/cpsw-logo.svg' } alt="Checkout Plugins - Stripe for WooCommerce" />
						</div>
					</div>
				</nav>
			</div>
		</div>
	);
}

export default Logo;
