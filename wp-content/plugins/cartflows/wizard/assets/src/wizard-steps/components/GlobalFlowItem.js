// SCSS.
import './GlobalFlowItem.scss';

function GlobalFlowItem( { item, isChecked, isActive } ) {
	const { thumbnail_image_url, title, type } = item;

	return (
		<>
			{ 'pro' === type ? (
				<span className={ `wcf-item__type wcf-item__type--${ type }` }>
					{ type }
				</span>
			) : (
				''
			) }

			<div
				className={ `wcf-item__inner bg-white border shadow-sm relative overflow-hidden rounded-lg cursor-pointer transition-all block group hover:-translate-y-px hover:border-primary-400 hover:shadow-xl hover:shadow-primary-50 ${
					isChecked || isActive
						? 'border-primary-400'
						: 'border-slate-200'
				}` }
			>
				<div className="wcf-item__thumbnail-wrap transition-none">
					<div
						className="wcf-item__thumbnail group-hover:transform-none bg-white relative position bg-top bg-cover bg-no-repeat overflow-hidden before:block before:pt-[100%]"
						style={ {
							backgroundImage: `url("${ thumbnail_image_url }")`,
						} }
					></div>
				</div>
				<div className="wcf-item__heading-wrap py-2.5 px-4 text-center border-t border-slate-200">
					<div className="wcf-item__heading text-slate-600 text-center text-base font-semibold">
						{ title }
					</div>
				</div>
			</div>
		</>
	);
}

export default GlobalFlowItem;
