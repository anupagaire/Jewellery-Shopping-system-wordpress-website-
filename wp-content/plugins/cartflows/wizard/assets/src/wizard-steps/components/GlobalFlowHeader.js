import React from 'react';
import { __ } from '@wordpress/i18n';

function GlobalFlowHeader() {
	return (
		<div className="overflow-hidden max-w-full text-center">
			<div className="px-6 py-5 sm:px-9 sm:py-8">
				<span className="text-sm font-medium text-primary-600 mb-10 text-center block tracking-[.24em] uppercase">
					{ __( 'Step 4 of 6', 'cartflows' ) }
				</span>
				<h1 className="wcf-step-heading mb-4">
					<span className="flex items-center justify-center gap-3">
						{ __( 'Awesome', 'cartflows' ) }
						<svg
							xmlns="http://www.w3.org/2000/svg"
							className="h-8 w-8 align-middle text-2xl mr-1.5 fill-[#ffc83d]"
							viewBox="0 0 20 20"
							fill="currentColor"
						>
							<path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
						</svg>
					</span>

					{ __(
						"Now let's setup your new store checkout.",
						'cartflows'
					) }
				</h1>
				<p className="text-center overflow-hidden max-w-2xl mb-10 mx-auto text-lg font-normal text-slate-500 block">
					{ __(
						'Choose one of the store checkout designs below. After setup you can change the text and color or even choose an entirely new store checkout design.',
						'cartflows'
					) }
				</p>
			</div>
		</div>
	);
}

export default GlobalFlowHeader;
