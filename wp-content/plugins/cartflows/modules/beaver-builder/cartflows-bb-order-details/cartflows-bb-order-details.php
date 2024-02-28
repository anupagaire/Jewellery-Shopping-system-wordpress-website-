<?php
/**
 * Order details Form Module for Beaver Builder
 *
 * @package cartflows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Order details Form Module for Beaver Builder
 *
 * @since 1.6.15
 */
class Cartflows_BB_Order_Details_Form extends FLBuilderModule {
	/**
	 * Constructor function for the module. You must pass the
	 * name, description, dir and url in an array to the parent class.
	 *
	 * @method __construct
	 */
	public function __construct() {

		$step_type          = Cartflows_BB_Helper::cartflows_bb_step_type();
		$is_bb_setting_page = Cartflows_BB_Helper::wcf_is_bb_setting_page();
		$is_enabled         = ( wcf()->is_woo_active && ( 'thankyou' === $step_type || $is_bb_setting_page ) ) ? true : false;

		parent::__construct(
			array(
				'name'            => __( 'Order Details Form', 'cartflows' ),
				'description'     => __( 'Order Details Form.', 'cartflows' ),
				'category'        => __( 'Cartflows Modules', 'cartflows' ),
				'group'           => __( 'Cartflows Modules', 'cartflows' ),
				'dir'             => CARTFLOWS_DIR . 'modules/beaver-builder/cartflows-bb-order-details/',
				'url'             => CARTFLOWS_URL . 'modules/beaver-builder/cartflows-bb-order-details/',
				'partial_refresh' => false, // Defaults to false and can be omitted.
				'icon'            => 'bb-order-details.svg',
				'enabled'         => $is_enabled,
			)
		);
	}

	/**
	 * Function to get the icon for the module
	 *
	 * @method get_icons
	 * @param string $icon gets the icon for the module.
	 */
	public function get_icon( $icon = '' ) {

		if ( '' !== $icon && file_exists( CARTFLOWS_DIR . 'modules/beaver-builder/cartflows-bb-order-details/icon/' . $icon ) ) {
			// file_get_contents is fine for local files. https://github.com/WordPress/WordPress-Coding-Standards/pull/1374/files#diff-400e43bc09c24262b43f26fce487fdabR43-R52.
			return file_get_contents( CARTFLOWS_DIR . 'modules/beaver-builder/cartflows-bb-order-details/icon/' . $icon ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		}

		return '';
	}

	/**
	 * Dynamic options of module and add filters.
	 *
	 * @since 1.6.15
	 */
	public function dynamic_option_filters() {

		if ( ! empty( $this->settings->thankyou_text ) ) {

			add_filter(
				'cartflows_thankyou_meta_wcf-tq-text',
				function( $text ) {

					$text = $this->settings->thankyou_text;

					return $text;
				},
				10,
				1
			);
		}

	}

}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module(
	'Cartflows_BB_Order_Details_Form',
	array(
		'general' => array(
			'title'    => __( 'General', 'cartflows' ),
			'sections' => array(
				'general' => array(
					'title'  => '',
					'fields' => array(
						'thankyou_text'         => array(
							'type'        => 'text',
							'label'       => __( 'Thank You Text', 'cartflows' ),
							'placeholder' => __( 'Thank you. Your order has been received.', 'cartflows' ),
							'preview'     => array(
								'type'     => 'text',
								'selector' => '.wcf-thankyou-wrap .woocommerce-order .woocommerce-thankyou-order-received',
							),
							'connections' => array( 'string' ),
						),
						'show_order_overview'   => array(
							'type'    => 'select',
							'label'   => __( 'Order Overview', 'cartflows' ),
							'default' => 'yes',
							'options' => array(
								'yes' => __( 'Yes', 'cartflows' ),
								'no'  => __( 'No', 'cartflows' ),
							),
						),
						'show_order_details'    => array(
							'type'    => 'select',
							'label'   => __( 'Order Details', 'cartflows' ),
							'default' => 'yes',
							'options' => array(
								'yes' => __( 'Yes', 'cartflows' ),
								'no'  => __( 'No', 'cartflows' ),
							),
						),
						'show_billing_address'  => array(
							'type'    => 'select',
							'label'   => __( 'Billing Address', 'cartflows' ),
							'default' => 'yes',
							'options' => array(
								'yes' => __( 'Yes', 'cartflows' ),
								'no'  => __( 'No', 'cartflows' ),
							),
						),
						'show_shipping_address' => array(
							'type'    => 'select',
							'label'   => __( 'Shipping Address', 'cartflows' ),
							'default' => 'yes',
							'options' => array(
								'yes' => __( 'Yes', 'cartflows' ),
								'no'  => __( 'No', 'cartflows' ),
							),
						),
					),
				),
			),
		),
		'style'   => array(
			'title'    => __( 'Style', 'cartflows' ),
			'sections' => array(
				'spacing'                  => array(
					'title'  => __( 'Spacing', 'cartflows' ),
					'fields' => array(
						'heading_spacing'  => array(
							'type'        => 'unit',
							'label'       => __( 'Heading Bottom Spacing', 'cartflows' ),
							'slider'      => true,
							'units'       => array( 'px' ),
							'maxlength'   => '50',
							'size'        => '',
							'placeholder' => '',
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-thankyou-wrap .woocommerce-order .woocommerce-thankyou-order-received',
								'property' => 'margin-bottom',
								'unit'     => 'px',
							),
						),
						'sections_spacing' => array(
							'type'        => 'unit',
							'label'       => __( 'Spacing Between Sections', 'cartflows' ),
							'slider'      => true,
							'units'       => array( 'px' ),
							'maxlength'   => '50',
							'size'        => '',
							'placeholder' => '',
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.woocommerce-order ul.order_details,
								.woocommerce-order .woocommerce-customer-details,
								.woocommerce-order .woocommerce-order-details,
								.woocommerce-order .woocommerce-order-downloads,
								.woocommerce-order .woocommerce-bacs-bank-details,
								.woocommerce-order-details.mollie-instructions',
								'property' => 'margin-bottom',
								'unit'     => 'px',
							),
						),
					),
				),
				'heading'                  => array(
					'title'  => __( 'Heading', 'cartflows' ),
					'fields' => array(
						'heading_color'      => array(
							'type'        => 'color',
							'label'       => __( 'Text Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.woocommerce-thankyou-order-received',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'heading_typography' => array(
							'type'       => 'typography',
							'label'      => __( 'Typography', 'cartflows' ),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.woocommerce-thankyou-order-received',
							),
						),
					),
				),
				'sections_heading'         => array(
					'title'  => __( 'Sections Heading', 'cartflows' ),
					'fields' => array(
						'sections_heading_color' => array(
							'type'        => 'color',
							'label'       => __( 'Text Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-thankyou-wrap .woocommerce-order h2',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'sections_heading_typo'  => array(
							'type'       => 'typography',
							'label'      => __( 'Typography', 'cartflows' ),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.wcf-thankyou-wrap .woocommerce-order h2',
							),
						),
					),
				),
				'sections_content'         => array(
					'title'  => __( 'Sections Content', 'cartflows' ),
					'fields' => array(
						'sections_content_color' => array(
							'type'        => 'color',
							'label'       => __( 'Text Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.woocommerce-thankyou-order-details.order_details li, .woocommerce-order-details .woocommerce-table, .woocommerce-order .woocommerce-customer-details address, .woocommerce-order-downloads table.shop_table,
								.wcf-thankyou-wrap p:not( .woocommerce-thankyou-order-received )',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'sections_bg_color'      => array(
							'type'        => 'color',
							'label'       => __( 'Background Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-thankyou-wrap .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details,
								.wcf-thankyou-wrap .woocommerce-order-details,
								.wcf-thankyou-wrap .woocommerce-customer-details,
								.wcf-thankyou-wrap .woocommerce-order-downloads',
								'property' => 'background-color',
								'unit'     => 'px',
							),
						),
						'sections_content_typo'  => array(
							'type'       => 'typography',
							'label'      => __( 'Typography', 'cartflows' ),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.woocommerce-thankyou-order-details.order_details li, .woocommerce-order-details .woocommerce-table, .woocommerce-order .woocommerce-customer-details address, .woocommerce-order-downloads table.shop_table,
								.wcf-thankyou-wrap p:not( .woocommerce-thankyou-order-received )',
							),
						),
					),
				),
				'section_order_overview'   => array(
					'title'  => __( 'Order Overview', 'cartflows' ),
					'fields' => array(
						'order_overview_text_color'       => array(
							'type'        => 'color',
							'label'       => __( 'Text Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details li',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'order_overview_background_color' => array(
							'type'        => 'color',
							'label'       => __( 'Background Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details',
								'property' => 'background-color',
								'unit'     => 'px',
							),
						),
						'order_overview_text_typography'  => array(
							'type'       => 'typography',
							'label'      => __( 'Text Typography', 'cartflows' ),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details li',
							),
						),
					),
				),
				'section_downloads'        => array(
					'title'  => __( 'Downloads', 'cartflows' ),
					'fields' => array(
						'downloads_heading_color'      => array(
							'type'        => 'color',
							'label'       => __( 'Heading Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => 'h2.woocommerce-order-downloads__title, .wcf-thankyou-wrap .woocommerce-order h2.woocommerce-order-downloads__title',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'downloads_heading_typography' => array(
							'type'       => 'typography',
							'label'      => __( 'Heading Typography', 'cartflows' ),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => 'h2.woocommerce-order-downloads__title',
							),
						),
						'downloads_text_color'         => array(
							'type'        => 'color',
							'label'       => __( 'Text Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.woocommerce-order-downloads table.shop_table',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'downloads_background_color'   => array(
							'type'        => 'color',
							'label'       => __( 'Background Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.woocommerce-order-downloads',
								'property' => 'background-color',
								'unit'     => 'px',
							),
						),
						'downloads_text_typography'    => array(
							'type'       => 'typography',
							'label'      => __( 'Text Typography', 'cartflows' ),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.woocommerce-order-downloads table.shop_table',
							),
						),
					),
				),
				'section_order_details'    => array(
					'title'  => __( 'Order Details', 'cartflows' ),
					'fields' => array(
						'order_details_heading_color'      => array(
							'type'        => 'color',
							'label'       => __( 'Heading Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-thankyou-wrap .woocommerce-order h2.woocommerce-order-details__title,
								.woocommerce-order-details .woocommerce-order-details__title',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'order_details_heading_typography' => array(
							'type'       => 'typography',
							'label'      => __( 'Heading Typography', 'cartflows' ),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.woocommerce-order-details .woocommerce-order-details__title',
							),
						),
						'order_details_text_color'         => array(
							'type'        => 'color',
							'label'       => __( 'Text Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.woocommerce-order .woocommerce-order-details .woocommerce-table, .woocommerce-order .woocommerce-order-details p.order-again',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'order_details_background_color'   => array(
							'type'        => 'color',
							'label'       => __( 'Background Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.woocommerce-order .woocommerce-order-details',
								'property' => 'background-color',
								'unit'     => 'px',
							),
						),
						'order_details_text_typography'    => array(
							'type'       => 'typography',
							'label'      => __( 'Text Typography', 'cartflows' ),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.woocommerce-order .woocommerce-order-details .woocommerce-table, .woocommerce-order .woocommerce-order-details p.order-again',
							),
						),
					),
				),
				'section_customer_details' => array(
					'title'  => __( 'Customer Details', 'cartflows' ),
					'fields' => array(
						'customer_details_heading_color'   => array(
							'type'        => 'color',
							'label'       => __( 'Heading Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-thankyou-wrap .woocommerce-order h2.woocommerce-column__title,
								.woocommerce-customer-details .woocommerce-column__title',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'customer_details_heading_typography' => array(
							'type'       => 'typography',
							'label'      => __( 'Heading Typography', 'cartflows' ),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.woocommerce-customer-details .woocommerce-column__title',
							),
						),
						'customer_details_text_color'      => array(
							'type'        => 'color',
							'label'       => __( 'Text Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details address, .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details address p',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'customer_details_background_color' => array(
							'type'        => 'color',
							'label'       => __( 'Background Color', 'cartflows' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details',
								'property' => 'background-color',
								'unit'     => 'px',
							),
						),
						'customer_details_text_typography' => array(
							'type'       => 'typography',
							'label'      => __( 'Text Typography', 'cartflows' ),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details address, .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details address p',
							),
						),
					),
				),
			),
		),
	)
);
