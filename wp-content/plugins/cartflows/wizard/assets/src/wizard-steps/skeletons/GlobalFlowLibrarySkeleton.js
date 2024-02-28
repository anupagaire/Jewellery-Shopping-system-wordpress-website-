import React from 'react';
import './GlobalFlowLibrarySkeleton.scss';

function GlobalFlowLibrarySkeleton() {
	const createBlocks = () => {
		const block = [];

		for ( let i = 0; i < 4; i++ ) {
			block.push(
				<div className="wcf-item" key={ i }>
					<div className="wcf-item__thumbnail-wrap">
						<div
							className="wcf-skeleton wcf-skeleton--rect wcf-skeleton--wave"
							style={ { height: '240px' } }
						></div>
					</div>
					<div className="wcf-item__heading-wrap">
						<div
							className="wcf-skeleton wcf-skeleton--rect wcf-skeleton--wave"
							style={ { width: '100%', height: '35px' } }
						></div>
					</div>
				</div>
			);
		}
		return block;
	};

	return (
		<>
			<div className="wcf-row mt-12">
				<div className="px-6 py-5 sm:px-9 sm:py-8">
					<div
						className="wcf-skeleton wcf-skeleton--rect wcf-skeleton--wave mx-auto"
						style={ {
							width: '60%',
							height: '35px',
							background: 'rgba(0, 0, 0, 0.11)',
						} }
					></div>
				</div>
			</div>
			<div className="wcf-flow-importer__list wcf-items-list wcf-flow-row is-placeholder grid grid-cols-4 gap-6 overflow-hidden">
				{ createBlocks() }
			</div>
		</>
	);
}

export default GlobalFlowLibrarySkeleton;
