import React, { useState, useEffect } from 'react';
import { useHistory } from 'react-router-dom';
import { RadioGroup } from '@headlessui/react';
import { __ } from '@wordpress/i18n';
import { useStateValue } from '../utils/StateProvider';
import Elementor from '@WizardImages/elementor.svg';
import BeaverBuilder from '@WizardImages/beaver-builder.svg';
import BlockEditor from '@WizardImages/block-editor.png';
import otherPageBuilders from '@WizardImages/other.svg';
import { ArrowRightIcon } from '@heroicons/react/24/outline';

const mailingLists = [
	{
		id: 1,
		slug: 'gutenberg',
		title: 'Block Builder',
		image: BlockEditor,
	},
	{
		id: 2,
		slug: 'elementor',
		title: 'Elementor',
		image: Elementor,
	},
	{
		id: 3,
		slug: 'beaver-builder',
		title: 'Beaver Builder',
		image: BeaverBuilder,
	},
	{
		id: 5,
		slug: 'other',
		title: 'Other',
		image: otherPageBuilders,
	},
];

function classNames( ...classes ) {
	return classes.filter( Boolean ).join( ' ' );
}

function PageBuilderStep() {
	const [ selectedMailingLists, setSelectedMailingLists ] = useState(
		mailingLists[ 0 ].slug
	);
	const [ { action_button }, dispatch ] = useStateValue();
	const history = useHistory();

	const handleChange = ( value ) => {
		setSelectedMailingLists( value );

		dispatch( {
			status: 'SET_WIZARD_PAGE_BUILDER',
			selected_page_builder: value,
		} );
	};

	useEffect( () => {
		dispatch( {
			status: 'SET_NEXT_STEP',
			action_button: {
				button_text: __( 'Save & Continue', 'cartflows' ),
				button_class: 'install-page-builder-plugins',
			},
		} );

		const installPbPluginsProcess = document.addEventListener(
			'wcf-page-builder-plugins-install-processing',
			function () {
				dispatch( {
					status: 'SET_NEXT_STEP',
					action_button: {
						button_text: __( 'Saving', 'cartflows' ),
						button_class: 'install-page-builder-plugins is-loading',
					},
				} );

				dispatch( {
					status: 'PROCESSING',
				} );
			},
			false
		);

		const installPbPluginsSuccess = document.addEventListener(
			'wcf-page-builder-plugins-install-success',
			function () {
				// Stop the processing.
				dispatch( {
					status: 'RESET',
				} );

				history.push( {
					pathname: 'index.php',
					search: `?page=cartflow-setup&step=plugin-install`,
				} );
			},
			false
		);

		return () => {
			document.removeEventListener(
				'wcf-page-builder-plugins-install-processing',
				installPbPluginsProcess
			);

			document.removeEventListener(
				'wcf-page-builder-plugins-install-success',
				installPbPluginsSuccess
			);
		};
	}, [] );

	return (
		<div className="wcf-container">
			<div className="wcf-row text-center mt-12">
				<div className="bg-white rounded mx-auto px-11">
					<span className="text-sm font-medium text-primary-600 mb-10 text-center block tracking-[.24em] uppercase">
						{ __( 'Step 2 of 6', 'cartflows' ) }
					</span>
					<h1 className="wcf-step-heading mb-4">
						{ __(
							'Hi there! Tell us which page builder you use.',
							'cartflows'
						) }
					</h1>

					<div className="flex justify-center mb-10">
						<RadioGroup
							value={ selectedMailingLists }
							onChange={ handleChange }
						>
							<RadioGroup.Label className="text-center overflow-hidden max-w-2xl mb-10 mx-auto text-lg font-normal text-slate-500 block">
								{ __(
									"CartFlows works with all page builders, so don't worry if your page builder is not in the list. ",
									'cartflows'
								) }
							</RadioGroup.Label>

							<div className="wcf-pb-list-wrapper flex justify-center items-center gap-8">
								{ mailingLists.map( ( mailingList ) => (
									<RadioGroup.Option
										key={ mailingList.id }
										value={ mailingList.slug }
										data-key={ mailingList.slug }
										className={ ( { checked, active } ) =>
											classNames(
												'wcf-pb-list--option relative border rounded shadow-sm flex justify-center cursor-pointer h-[9rem] w-[130px] transition-all duration-300 focus:outline-none hover:drop-shadow-lg hover:translate-y-[-1px] hover:shadow-[0px 4px 8px -2px rgb(9 30 66 / 25%), 0px 0px 1px rgb(9 30 66 / 31%)]',
												checked
													? 'border-transparent'
													: 'border-gray-300',
												active ? '' : ''
											)
										}
									>
										{ ( { checked, active } ) => (
											<>
												<div className="flex-auto flex justify-center">
													<div className="text-center">
														<RadioGroup.Description
															as="img"
															className="block text-sm font-normal text-[#4B5563] h-[45%] rounded-full m-5"
															src={
																mailingList.image
															}
														></RadioGroup.Description>
														<RadioGroup.Label
															as="div"
															className="block text-sm font-normal text-[#4B5563] mt-4"
														>
															{
																mailingList.title
															}
														</RadioGroup.Label>
													</div>
												</div>

												<div
													className={ classNames(
														active
															? 'border-2'
															: 'border-2',
														checked
															? 'border-orange-500'
															: 'border-transparent',
														'absolute -inset-px rounded pointer-events-none'
													) }
													aria-hidden="true"
												/>
											</>
										) }
									</RadioGroup.Option>
								) ) }
							</div>
						</RadioGroup>

						<span
							id="wcf-selected-page-builder"
							data-selected-pb={ selectedMailingLists }
						></span>
					</div>

					<div className="flex justify-center">
						<div
							className={ `wcf-wizard--button ${
								action_button.button_class
									? action_button.button_class
									: ''
							}` }
						>
							{ action_button.button_text }
							<ArrowRightIcon
								className="w-5 mt-0.5 ml-1.5 stroke-2"
								aria-hidden="true"
							/>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
}

export default PageBuilderStep;
