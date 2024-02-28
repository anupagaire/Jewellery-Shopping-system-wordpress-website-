import React from 'react';
import './TemplateLoadingSkeleton.scss';

const TemplateLoadingSkeleton = () => {
	return (
		<div className="wcf-template-loading-skeleton is-placeholder">
			<div className="wcf-loading-container">
				<div className="wcf-loading-nav-menu">
					<div className="wcf-loading-logo wcf-skeleton--wave"></div>
					<div className="wcf-loading-nav__items">
						<span className="wcf-skeleton--wave"></span>
						<span className="wcf-skeleton--wave"></span>
						<span className="wcf-skeleton--wave"></span>
						<span className="wcf-skeleton--wave"></span>
					</div>
				</div>

				<div className="wcf-loading-content">
					<div className="wcf-content-row">
						<div className="wcf-left-content">
							<span className="wcf-loading-heading-block wcf-skeleton--wave"></span>
							<div className="wcf-row wcf-skeleton--wave"></div>
							<div
								className="wcf-row wcf-skeleton--wave"
								style={ { width: '80%' } }
							></div>
							<div
								className="wcf-row wcf-skeleton--wave"
								style={ { width: '90%' } }
							></div>
							<div
								className="wcf-row wcf-skeleton--wave"
								style={ { width: '60%' } }
							></div>
							<div
								className="wcf-row wcf-skeleton--wave"
								style={ { width: '30%' } }
							></div>
							<span className="wcf-loading-button-block"></span>
						</div>
						<div className="wcf-right-content">
							<span className="wcf-loading-image-block wcf-skeleton--wave"></span>
						</div>
					</div>

					<div className="wcf-content-row">
						<div className="wcf-left-content col-3">
							<span className="wcf-loading-image-block wcf-skeleton--wave"></span>
						</div>
						<div className="wcf-left-content col-3">
							<span className="wcf-loading-image-block wcf-skeleton--wave"></span>
						</div>
						<div className="wcf-left-content">
							<span className="wcf-loading-image-block wcf-skeleton--wave"></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
};

export default TemplateLoadingSkeleton;
