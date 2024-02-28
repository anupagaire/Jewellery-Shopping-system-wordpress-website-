import React from 'react';
import { useHistory } from 'react-router-dom';
import { __ } from '@wordpress/i18n';

function NoFlowsFound() {
	const history = useHistory();

	const redirectNextStep = function () {
		history.push( {
			pathname: 'index.php',
			search: `?page=cartflow-setup&step=optin`,
		} );
	};

	return (
		<div className="wcf-row mt-12">
			<div className="bg-white rounded mx-auto max-w-2xl px-11 text-center py-14 drop-shadow-sm">
				<h1 className="text-3xl font-semibold flex justify-center items-center">
					<svg
						xmlns="http://www.w3.org/2000/svg"
						className="h-8 w-8 align-middle text-2xl mr-1.5 fill-[#ffc83d]"
						viewBox="0 0 20 20"
						fill="currentColor"
					>
						<path
							fillRule="evenodd"
							d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
							clipRule="evenodd"
						/>
					</svg>
					{ __( 'Oops!!! No templates found', 'cartflows' ) }
				</h1>
				<p className="mt-6 text-[#1F2937] text-base">
					{ __(
						"Seems like no templates are available for chosen page editor. Don't worry, you can always import the store checkout template from the CartFlows setting menu.",
						'cartflows'
					) }
				</p>
				<div className="mt-[40px] flex justify-center">
					<div
						className="wcf-wizard--button"
						onClick={ redirectNextStep }
					>
						{ __( 'Skip to next', 'cartflows' ) }
						<svg
							xmlns="http://www.w3.org/2000/svg"
							className="w-5 mt-0.5 ml-1.5 fill-[#243c5a]"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
							strokeWidth={ 2 }
						>
							<path
								strokeLinecap="round"
								strokeLinejoin="round"
								d="M17 8l4 4m0 0l-4 4m4-4H3"
							/>
						</svg>
					</div>
				</div>
			</div>
		</div>
	);
}

export default NoFlowsFound;
