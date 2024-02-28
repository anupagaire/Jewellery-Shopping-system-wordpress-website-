/**
 * Gutenberg Blocks
 *
 * All blocks related JavaScript files should be imported here.
 * You can create a new block folder in this dir and include code
 * for that block here as well.
 *
 * All blocks should be included here since this is the file that
 * Webpack is compiling as the input file.
 */

import './editor.scss';

import './blocks/order-detail-form/block.js';
import './blocks/next-step-button/block.js';
import './blocks/checkout-form/block.js';
import './blocks/optin-form/block.js';

import CF_Block_Icons from './block-icons';

const { updateCategory } = wp.blocks;

// wp.WCFSvgIcons = Object.keys( cf_blocks_info.wcf_svg_icons );
wp.UAGBSvgIcons = Object.keys( uagb_blocks_info.uagb_svg_icons );

updateCategory( 'cartflows', {
	icon: CF_Block_Icons.logo,
} );

const addCfResponsiveCondtionBlocks = function ( blocks ) {
	blocks.push( 'wcfb/' );
	return blocks;
};

wp.hooks.addFilter(
	'uag_reponsive_conditions_compatible_blocks',
	'enable_reponsive_condition_for_cf_blocks',
	addCfResponsiveCondtionBlocks
);
