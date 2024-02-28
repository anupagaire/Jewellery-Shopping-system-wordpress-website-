<?php
/**
 * Cartflows Block Helper.
 *
 * @package Cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Block_Helper' ) ) {

	/**
	 * Class Cartflows_Block_Helper.
	 */
	class Cartflows_Block_Helper {

		/**
		 * Get Next Step Button CSS
		 *
		 * @since 1.6.15
		 * @param array  $attr The block attributes.
		 * @param string $id The selector ID.
		 * @return array The Widget List.
		 */
		public static function get_next_step_button_css( $attr, $id ) {

			$defaults = Cartflows_Gb_Helper::$block_list['wcfb/next-step-button']['attributes'];
			$attr     = array_merge( $defaults, (array) $attr );
			$bg_type  = ( isset( $attr['backgroundType'] ) ) ? $attr['backgroundType'] : 'none';

			$m_selectors = array();
			$t_selectors = array();

			$border_css        = self::generate_border_css( $attr, 'btn' );
			$border_css        = self::generate_deprecated_border_css(
				$border_css,
				( isset( $attr['borderWidth'] ) ? $attr['borderWidth'] : '' ),
				( isset( $attr['borderRadius'] ) ? $attr['borderRadius'] : '' ),
				( isset( $attr['borderColor'] ) ? $attr['borderColor'] : '' ),
				( isset( $attr['borderStyle'] ) ? $attr['borderStyle'] : '' ),
				( isset( $attr['borderHColor'] ) ? $attr['borderHColor'] : '' )
			);
			$border_css_tablet = self::generate_border_css( $attr, 'btn', 'tablet' );
			$border_css_mobile = self::generate_border_css( $attr, 'btn', 'mobile' );

			$selectors = array(

				' .wpcf__next-step-button-wrap'       => array(
					'text-align' => $attr['align'],
				),
				' .wpcf__next-step-button-link'       => array_merge(
					array(
						'text-align'       => $attr['textAlignment'],
						'color'            => $attr['textColor'],
						'background-color' => $attr['backgroundColor'],
						'opacity'          => ( isset( $attr['backgroundOpacity'] ) && '' !== $attr['backgroundOpacity'] ) ? $attr['backgroundOpacity'] / 100 : '',
						'padding-top'      => Cartflows_Gb_Helper::get_css_value( $attr['paddingBtnTop'], $attr['paddingTypeDesktop'] ),
						'padding-bottom'   => Cartflows_Gb_Helper::get_css_value( $attr['paddingBtnBottom'], $attr['paddingTypeDesktop'] ),
						'padding-left'     => Cartflows_Gb_Helper::get_css_value( $attr['paddingBtnLeft'], $attr['paddingTypeDesktop'] ),
						'padding-right'    => Cartflows_Gb_Helper::get_css_value( $attr['paddingBtnRight'], $attr['paddingTypeDesktop'] ),
					),
					$border_css
				),
				' .wpcf__next-step-button-link:hover' => array(
					'background-color' => $attr['buttonHoverColor'],
					'color'            => $attr['textHoverColor'],
					'border-color'     => $attr['btnBorderHColor'],
				),
				' .wpcf__next-step-button-link .wpcf__next-step-button-content-wrap .wpcf__next-step-button-title-wrap' => array(
					'text-transform' => $attr['titletextTransform'],
					'letter-spacing' => Cartflows_Gb_Helper::get_css_value( $attr['titleletterSpacing'], 'px' ),
				),
				' .wpcf__next-step-button-link .wpcf__next-step-button-content-wrap .wpcf__next-step-button-sub-title' => array(
					'margin-top'     => Cartflows_Gb_Helper::get_css_value( $attr['titleBottomSpacing'], 'px' ),
					'text-transform' => $attr['subtitletextTransform'],
					'letter-spacing' => Cartflows_Gb_Helper::get_css_value( $attr['subtitleletterSpacing'], 'px' ),
				),
				' .wpcf__next-step-button-icon svg'   => array(
					'width'  => Cartflows_Gb_Helper::get_css_value( $attr['iconSize'], 'px' ),
					'height' => Cartflows_Gb_Helper::get_css_value( $attr['iconSize'], 'px' ),
					'fill'   => $attr['iconColor'],
				),
				' .wpcf__next-step-button-link:hover .wpcf__next-step-button-icon svg' => array(
					'fill' => $attr['iconHoverColor'],
				),
			);
			if ( 'full' === $attr['align'] ) {
				$selectors[' a.wpcf__next-step-button-link'] = array(
					'width'           => '100%',
					'justify-content' => 'center',
				);
			}

			$position = str_replace( '-', ' ', $attr['backgroundPosition'] );

			if ( 'image' == $bg_type ) {

					$selectors[' .wpcf__next-step-button-link']['background-color'] = $attr['backgroundImageColor'];

					$selectors[' .wpcf__next-step-button-link']['background-image']      = isset( $attr['backgroundImage'] ) && isset( $attr['backgroundImage']['url'] ) ? "url('" . $attr['backgroundImage']['url'] . "' )" : null;
					$selectors[' .wpcf__next-step-button-link']['background-position']   = $position;
					$selectors[' .wpcf__next-step-button-link']['background-attachment'] = $attr['backgroundAttachment'];
					$selectors[' .wpcf__next-step-button-link']['background-repeat']     = $attr['backgroundRepeat'];
					$selectors[' .wpcf__next-step-button-link']['background-size']       = $attr['backgroundSize'];

			} elseif ( 'gradient' === $bg_type ) {

				$selectors[' .wpcf__next-step-button-link']['background-color'] = 'transparent';
				$selectors[' .wpcf__next-step-button-link']['opacity']          = ( isset( $attr['backgroundOpacity'] ) && '' !== $attr['backgroundOpacity'] ) ? $attr['backgroundOpacity'] / 100 : '';
				if ( $attr['gradientValue'] ) {
					$selectors[' .wpcf__next-step-button-link']['background-image'] = $attr['gradientValue'];

				} else {
					if ( 'linear' === $attr['gradientType'] ) {

						$selectors[' .wpcf__next-step-button-link']['background-image'] = 'linear-gradient(' . $attr['gradientAngle'] . 'deg, ' . $attr['gradientColor1'] . ' ' . $attr['gradientLocation1'] . '%, ' . $attr['gradientColor2'] . ' ' . $attr['gradientLocation2'] . '%)';

					} else {

						$selectors[' .wpcf__next-step-button-link']['background-image'] = 'radial-gradient( at ' . $attr['gradientPosition'] . ', ' . $attr['gradientColor1'] . ' ' . $attr['gradientLocation1'] . '%, ' . $attr['gradientColor2'] . ' ' . $attr['gradientLocation2'] . '%)';
					}
				}
			}

			$margin_type = ( 'after_title' === $attr['iconPosition'] || 'after_title_sub_title' === $attr['iconPosition'] ) ? 'margin-left' : 'margin-right';

			$selectors[' .wpcf__next-step-button-icon svg'][ $margin_type ] = Cartflows_Gb_Helper::get_css_value( $attr['iconSpacing'], 'px' );

			$t_selectors = array(
				' .wpcf__next-step-button-wrap' => array(
					'text-align' => $attr['talign'],
				),
				' .wpcf__next-step-button-link' => array(
					'padding-top'    => Cartflows_Gb_Helper::get_css_value( $attr['paddingBtnTopTablet'], $attr['paddingTypeTablet'] ),
					'padding-bottom' => Cartflows_Gb_Helper::get_css_value( $attr['paddingBtnBottomTablet'], $attr['paddingTypeTablet'] ),
					'padding-left'   => Cartflows_Gb_Helper::get_css_value( $attr['paddingBtnLeftTablet'], $attr['paddingTypeTablet'] ),
					'padding-right'  => Cartflows_Gb_Helper::get_css_value( $attr['paddingBtnRightTablet'], $attr['paddingTypeTablet'] ),
				),
			);

			if ( 'full' === $attr['talign'] ) {
				$t_selectors[' a.wpcf__next-step-button-link'] = array(
					'width'           => '100%',
					'justify-content' => 'center',
				);
			}

			$merged_tablet_css                            = array_merge( $t_selectors[' .wpcf__next-step-button-link'], $border_css_tablet );
			$t_selectors[' .wpcf__next-step-button-link'] = $merged_tablet_css;

			$m_selectors = array(
				' .wpcf__next-step-button-wrap' => array(
					'text-align' => $attr['malign'],
				),
				' .wpcf__next-step-button-link' => array(
					'padding-top'    => Cartflows_Gb_Helper::get_css_value( $attr['paddingBtnTopMobile'], $attr['paddingTypeMobile'] ),
					'padding-bottom' => Cartflows_Gb_Helper::get_css_value( $attr['paddingBtnBottomMobile'], $attr['paddingTypeMobile'] ),
					'padding-left'   => Cartflows_Gb_Helper::get_css_value( $attr['paddingBtnLeftMobile'], $attr['paddingTypeMobile'] ),
					'padding-right'  => Cartflows_Gb_Helper::get_css_value( $attr['paddingBtnRightMobile'], $attr['paddingTypeMobile'] ),
				),
			);

			if ( 'full' === $attr['malign'] ) {
				$m_selectors[' a.wpcf__next-step-button-link'] = array(
					'width'           => '100%',
					'justify-content' => 'center',
				);
			}

			$merged_mobile_css                            = array_merge( $m_selectors[' .wpcf__next-step-button-link'], $border_css_mobile );
			$m_selectors[' .wpcf__next-step-button-link'] = $merged_mobile_css;

			$combined_selectors = array(
				'desktop' => $selectors,
				'tablet'  => $t_selectors,
				'mobile'  => $m_selectors,
			);

			$combined_selectors = Cartflows_Gb_Helper::get_typography_css( $attr, 'title', ' .wpcf__next-step-button-link .wpcf__next-step-button-content-wrap .wpcf__next-step-button-title-wrap', $combined_selectors );
			$combined_selectors = Cartflows_Gb_Helper::get_typography_css( $attr, 'subTitle', ' .wpcf__next-step-button-link .wpcf__next-step-button-content-wrap .wpcf__next-step-button-sub-title', $combined_selectors );

			return Cartflows_Gb_Helper::generate_all_css( $combined_selectors, ' .cf-block-' . $id );
		}

			/**
			 * Get Order Detail Form Block CSS
			 *
			 * @since 1.6.15
			 * @param array  $attr The block attributes.
			 * @param string $id The selector ID.
			 * @return array The Widget List.
			 */
		public static function get_order_detail_form_css( $attr, $id ) {

			$defaults = Cartflows_Gb_Helper::$block_list['wcfb/order-detail-form']['attributes'];
			$bg_type  = ( isset( $attr['backgroundType'] ) ) ? $attr['backgroundType'] : 'none';

			$attr = array_merge( $defaults, $attr );

			$t_selectors = array();
			$m_selectors = array();
			$selectors   = array();

			$order_overview            = ( $attr['orderOverview'] ) ? 'block' : 'none';
			$order_details             = ( $attr['orderDetails'] ) ? 'block' : 'none';
			$billing_address           = ( $attr['billingAddress'] ) ? 'block' : 'none';
			$shipping_address          = ( $attr['shippingAddress'] ) ? 'block' : 'none';
			$shipping_address_position = ( $attr['billingAddress'] ) ? 'right' : 'left';
			$customer_details          = ( $attr['billingAddress'] || $attr['shippingAddress'] ) ? 'block' : 'none';

			$selectors = array(
				// Genaral.
				' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order ul.order_details'       => array(
					'display' => $order_overview,
				),
				' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order section.woocommerce-order-details'       => array(
					'display' => $order_details,
				),
				' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details .woocommerce-column--billing-address'       => array(
					'display' => $billing_address,
				),
				' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details .woocommerce-column--shipping-address'       => array(
					'display' => $shipping_address,
					'float'   => $shipping_address_position,
				),
				// Spacing.
				' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order p.woocommerce-thankyou-order-received'       => array(
					'margin-bottom' => Cartflows_Gb_Helper::get_css_value( $attr['headingBottomSpacing'], 'px' ),
				),
				' .wpcf__order-detail-form .woocommerce-order ul.order_details, .wpcf__order-detail-form .woocommerce-order .woocommerce-customer-details, .wpcf__order-detail-form .woocommerce-order .woocommerce-order-details, .wpcf__order-detail-form .woocommerce-order .woocommerce-order-downloads, .wpcf__order-detail-form .woocommerce-order .woocommerce-bacs-bank-details, .wpcf__order-detail-form .woocommerce-order-details.mollie-instructions'       => array(
					'margin-bottom' => Cartflows_Gb_Helper::get_css_value( $attr['sectionSpacing'], 'px' ),
				),
				// Heading.
				' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-thankyou-order-received'       => array(
					'text-align' => $attr['headingAlignment'],
					'color'      => $attr['headingColor'],
				),
				// Sections.
				' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order h2'       => array(
					'color' => $attr['sectionHeadingColor'],
				),
				' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order, .wpcf__order-detail-form .woocommerce-order-downloads table.shop_table'       => array(
					'color' => $attr['sectionContentColor'],
				),
				' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details, .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details, .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details, .wpcf__order-detail-form .woocommerce-order-downloads'       => array(
					'background-color' => $attr['sectionBackgroundColor'],
				),

				// Order Overview.
				' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details'       => array(
					'color'            => $attr['orderOverviewTextColor'],
					'background-color' => $attr['orderOverviewBackgroundColor'],
				),
				' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order ul.woocommerce-order-overview.woocommerce-thankyou-order-details.order_details li'       => array(
					'color' => $attr['orderOverviewTextColor'],
				),
				// Downloads.
				' .wpcf__order-detail-form .woocommerce-order h2.woocommerce-order-downloads__title, .wpcf__order-detail-form .woocommerce-order .woocommerce-order-downloads h2.woocommerce-order-downloads__title'       => array(
					'color' => $attr['downloadHeadingColor'],
				),
				' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-downloads table.shop_table'       => array(
					'color' => $attr['downloadContentColor'],
				),
				' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-downloads'       => array(
					'background-color' => $attr['downloadBackgroundColor'],
				),
				// Order Details.
				' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details .woocommerce-order-details__title'       => array(
					'color' => $attr['orderDetailHeadingColor'],
				),
				' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details .woocommerce-table'       => array(
					'color' => $attr['orderDetailContentColor'],
				),
				' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details'       => array(
					'background-color' => $attr['orderDetailBackgroundColor'],
				),
				// Customer Details.
				' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details .woocommerce-column__title'       => array(
					'color' => $attr['customerDetailHeadingColor'],
				),
				' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details address'       => array(
					'color' => $attr['customerDetailContentColor'],
				),
				' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order section.woocommerce-customer-details'       => array(
					'background-color' => $attr['customerDetailBackgroundColor'],
					'display'          => $customer_details,
				),

			);

				$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details, .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details, .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details, .wpcf__order-detail-form .woocommerce-order-downloads'] = array(
					'opacity'          => ( isset( $attr['backgroundOpacity'] ) && '' !== $attr['backgroundOpacity'] ) ? $attr['backgroundOpacity'] / 100 : '',
					'background-color' => $attr['backgroundColor'],
				);

				$position = str_replace( '-', ' ', $attr['backgroundPosition'] );

				if ( 'image' == $bg_type ) {
					$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details, .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details, .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details, .wpcf__order-detail-form .woocommerce-order-downloads'] = array(
						'opacity'               => ( isset( $attr['backgroundOpacity'] ) && '' !== $attr['backgroundOpacity'] ) ? $attr['backgroundOpacity'] / 100 : 0,
						'background-color'      => $attr['backgroundImageColor'],
						'background-image'      => ( isset( $attr['backgroundImage'] ) && isset( $attr['backgroundImage']['url'] ) ) ? "url('" . $attr['backgroundImage']['url'] . "' )" : null,
						'background-position'   => $position,
						'background-attachment' => $attr['backgroundAttachment'],
						'background-repeat'     => $attr['backgroundRepeat'],
						'background-size'       => $attr['backgroundSize'],
					);
				} elseif ( 'gradient' === $bg_type ) {

					$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details, .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details, .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details, .wpcf__order-detail-form .woocommerce-order-downloads']['background-color'] = 'transparent';
					$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details, .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details, .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details, .wpcf__order-detail-form .woocommerce-order-downloads']['opacity']          = ( isset( $attr['backgroundOpacity'] ) && '' !== $attr['backgroundOpacity'] ) ? $attr['backgroundOpacity'] / 100 : '';
					if ( $attr['gradientValue'] ) {
						$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details, .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details, .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details, .wpcf__order-detail-form .woocommerce-order-downloads']['background-image'] = $attr['gradientValue'];

					} else {
						if ( 'linear' === $attr['gradientType'] ) {

							$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details, .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details, .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details, .wpcf__order-detail-form .woocommerce-order-downloads']['background-image'] = 'linear-gradient(' . $attr['gradientAngle'] . 'deg, ' . $attr['gradientColor1'] . ' ' . $attr['gradientLocation1'] . '%, ' . $attr['gradientColor2'] . ' ' . $attr['gradientLocation2'] . '%)';

						} else {

							$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details, .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details, .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details, .wpcf__order-detail-form .woocommerce-order-downloads']['background-image'] = 'radial-gradient( at ' . $attr['gradientPosition'] . ', ' . $attr['gradientColor1'] . ' ' . $attr['gradientLocation1'] . '%, ' . $attr['gradientColor2'] . ' ' . $attr['gradientLocation2'] . '%)';
						}
					}
				}
				// Order review.
				$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details'] = array(
					'opacity'          => ( isset( $attr['odbackgroundOpacity'] ) && '' !== $attr['odbackgroundOpacity'] ) ? $attr['odbackgroundOpacity'] / 100 : 0.79,
					'background-color' => $attr['odbackgroundColor'],
				);

				$position = str_replace( '-', ' ', $attr['backgroundPosition'] );

				if ( 'image' == $attr['odbackgroundType'] ) {
					$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details'] = array(
						'opacity'               => ( isset( $attr['odbackgroundOpacity'] ) && '' !== $attr['odbackgroundOpacity'] ) ? $attr['odbackgroundOpacity'] / 100 : 0,
						'background-color'      => $attr['odbackgroundImageColor'],
						'background-image'      => ( isset( $attr['odbackgroundImage'] ) && isset( $attr['odbackgroundImage']['url'] ) ) ? "url('" . $attr['odbackgroundImage']['url'] . "' )" : null,
						'background-position'   => $position,
						'background-attachment' => $attr['odbackgroundAttachment'],
						'background-repeat'     => $attr['odbackgroundRepeat'],
						'background-size'       => $attr['odbackgroundSize'],
					);
				} elseif ( 'gradient' === $attr['odbackgroundType'] ) {

					$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details']['background-color'] = 'transparent';
					$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details']['opacity']          = ( isset( $attr['backgroundOpacity'] ) && '' !== $attr['backgroundOpacity'] ) ? $attr['backgroundOpacity'] / 100 : '';
					if ( $attr['odgradientValue'] ) {
						$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details']['background-image'] = $attr['odgradientValue'];

					} else {
						if ( 'linear' === $attr['gradientType'] ) {

							$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details']['background-image'] = 'linear-gradient(' . $attr['gradientAngle'] . 'deg, ' . $attr['gradientColor1'] . ' ' . $attr['gradientLocation1'] . '%, ' . $attr['gradientColor2'] . ' ' . $attr['gradientLocation2'] . '%)';
						} else {

							$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details']['background-image'] = 'radial-gradient( at ' . $attr['gradientPosition'] . ', ' . $attr['gradientColor1'] . ' ' . $attr['gradientLocation1'] . '%, ' . $attr['gradientColor2'] . ' ' . $attr['gradientLocation2'] . '%)';
						}
					}
				}
				// Downloads.
				$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-downloads'] = array(
					'opacity'          => ( isset( $attr['dbackgroundOpacity'] ) && '' !== $attr['dbackgroundOpacity'] ) ? $attr['dbackgroundOpacity'] / 100 : 0.79,
					'background-color' => $attr['dbackgroundType'],
				);

				$dposition = str_replace( '-', ' ', $attr['dbackgroundPosition'] );

				if ( 'image' == $attr['dbackgroundType'] ) {
					$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-downloads'] = array(
						'opacity'               => ( isset( $attr['dbackgroundOpacity'] ) && '' !== $attr['dbackgroundOpacity'] ) ? $attr['dbackgroundOpacity'] / 100 : 0,
						'background-color'      => $attr['dbackgroundImageColor'],
						'background-image'      => ( isset( $attr['dbackgroundImage'] ) && isset( $attr['dbackgroundImage']['url'] ) ) ? "url('" . $attr['dbackgroundImage']['url'] . "' )" : null,
						'background-position'   => $dposition,
						'background-attachment' => $attr['dbackgroundAttachment'],
						'background-repeat'     => $attr['dbackgroundRepeat'],
						'background-size'       => $attr['dbackgroundSize'],
					);
				} elseif ( 'gradient' === $attr['dbackgroundType'] ) {

					$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-downloads']['background-color'] = 'transparent';
					$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-downloads']['opacity']          = ( isset( $attr['backgroundOpacity'] ) && '' !== $attr['backgroundOpacity'] ) ? $attr['backgroundOpacity'] / 100 : '';
					if ( $attr['dgradientValue'] ) {
						$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-downloads']['background-image'] = $attr['dgradientValue'];

					} else {
						if ( 'linear' === $attr['gradientType'] ) {

							$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-downloads']['background-image'] = 'linear-gradient(' . $attr['gradientAngle'] . 'deg, ' . $attr['gradientColor1'] . ' ' . $attr['gradientLocation1'] . '%, ' . $attr['gradientColor2'] . ' ' . $attr['gradientLocation2'] . '%)';
						} else {

							$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-downloads']['background-image'] = 'radial-gradient( at ' . $attr['gradientPosition'] . ', ' . $attr['gradientColor1'] . ' ' . $attr['gradientLocation1'] . '%, ' . $attr['gradientColor2'] . ' ' . $attr['gradientLocation2'] . '%)';
						}
					}
				}
				// Order details.
				$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details'] = array(
					'opacity'          => ( isset( $attr['odetailbackgroundOpacity'] ) && '' !== $attr['odetailbackgroundOpacity'] ) ? $attr['odetailbackgroundOpacity'] / 100 : 0.79,
					'background-color' => $attr['odetailbackgroundColor'],
				);

				$odetailposition = str_replace( '-', ' ', $attr['odetailbackgroundPosition'] );

				if ( 'image' == $attr['odetailbackgroundType'] ) {
					$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details'] = array(
						'opacity'               => ( isset( $attr['odetailbackgroundOpacity'] ) && '' !== $attr['odetailbackgroundOpacity'] ) ? $attr['odetailbackgroundOpacity'] / 100 : 0,
						'background-color'      => $attr['odetailbackgroundImageColor'],
						'background-image'      => ( isset( $attr['odetailbackgroundImage'] ) && isset( $attr['odetailbackgroundImage']['url'] ) ) ? "url('" . $attr['odetailbackgroundImage']['url'] . "' )" : null,
						'background-position'   => $odetailposition,
						'background-attachment' => $attr['odetailbackgroundAttachment'],
						'background-repeat'     => $attr['odetailbackgroundRepeat'],
						'background-size'       => $attr['odetailbackgroundSize'],
					);
				} elseif ( 'gradient' === $attr['odetailbackgroundType'] ) {

					$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details']['background-color'] = 'transparent';
					$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details']['opacity']          = ( isset( $attr['backgroundOpacity'] ) && '' !== $attr['backgroundOpacity'] ) ? $attr['backgroundOpacity'] / 100 : '';
					if ( $attr['odetailgradientValue'] ) {
						$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details']['background-image'] = $attr['odetailgradientValue'];

					} else {
						if ( 'linear' === $attr['gradientType'] ) {

							$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details']['background-image'] = 'linear-gradient(' . $attr['gradientAngle'] . 'deg, ' . $attr['gradientColor1'] . ' ' . $attr['gradientLocation1'] . '%, ' . $attr['gradientColor2'] . ' ' . $attr['gradientLocation2'] . '%)';
						} else {

							$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details']['background-image'] = 'radial-gradient( at ' . $attr['gradientPosition'] . ', ' . $attr['gradientColor1'] . ' ' . $attr['gradientLocation1'] . '%, ' . $attr['gradientColor2'] . ' ' . $attr['gradientLocation2'] . '%)';
						}
					}
				}
				// Customer details.
				$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details'] = array(
					'opacity'          => ( isset( $attr['cdetailsbackgroundOpacity'] ) && '' !== $attr['cdetailsbackgroundOpacity'] ) ? $attr['cdetailsbackgroundOpacity'] / 100 : 0.79,
					'background-color' => $attr['cdetailbackgroundColor'],
				);

				$cdetailposition = str_replace( '-', ' ', $attr['cdetailbackgroundPosition'] );

				if ( 'image' == $attr['cdetailbackgroundType'] ) {
					$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details'] = array(
						'opacity'               => ( isset( $attr['cdetailsbackgroundOpacity'] ) && '' !== $attr['cdetailsbackgroundOpacity'] ) ? $attr['cdetailsbackgroundOpacity'] / 100 : 0,
						'background-color'      => $attr['cdetailsbackgroundImageColor'],
						'background-image'      => ( isset( $attr['cdetailbackgroundImage'] ) && isset( $attr['cdetailbackgroundImage']['url'] ) ) ? "url('" . $attr['cdetailbackgroundImage']['url'] . "' )" : null,
						'background-position'   => $cdetailposition,
						'background-attachment' => $attr['cdetailbackgroundAttachment'],
						'background-repeat'     => $attr['cdetailbackgroundRepeat'],
						'background-size'       => $attr['cdetailbackgroundSize'],
					);
				} elseif ( 'gradient' === $attr['cdetailbackgroundType'] ) {

					$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details']['background-color'] = 'transparent';
					$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details']['opacity']          = ( isset( $attr['backgroundOpacity'] ) && '' !== $attr['backgroundOpacity'] ) ? $attr['backgroundOpacity'] / 100 : '';
					if ( $attr['cdetailgradientValue'] ) {
						$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details']['background-image'] = $attr['cdetailgradientValue'];

					} else {
						if ( 'linear' === $attr['gradientType'] ) {

							$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details']['background-image'] = 'linear-gradient(' . $attr['gradientAngle'] . 'deg, ' . $attr['gradientColor1'] . ' ' . $attr['gradientLocation1'] . '%, ' . $attr['gradientColor2'] . ' ' . $attr['gradientLocation2'] . '%)';
						} else {

							$selectors[' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details']['background-image'] = 'radial-gradient( at ' . $attr['gradientPosition'] . ', ' . $attr['gradientColor1'] . ' ' . $attr['gradientLocation1'] . '%, ' . $attr['gradientColor2'] . ' ' . $attr['gradientLocation2'] . '%)';
						}
					}
				}
				$combined_selectors = array(
					'desktop' => $selectors,
					'tablet'  => $t_selectors,
					'mobile'  => $m_selectors,
				);

				// Heading.
				$combined_selectors = Cartflows_Gb_Helper::get_typography_css( $attr, 'heading', ' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-thankyou-order-received', $combined_selectors );
				// Sections.
				$combined_selectors = Cartflows_Gb_Helper::get_typography_css( $attr, 'sectionHeading', ' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order h2', $combined_selectors );
				$combined_selectors = Cartflows_Gb_Helper::get_typography_css( $attr, 'sectionContent', ' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details li, .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order p, .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details .woocommerce-table, .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details address, .wpcf__order-detail-form .woocommerce-order-downloads table.shop_table', $combined_selectors );
				// Order Overview.
				$combined_selectors = Cartflows_Gb_Helper::get_typography_css( $attr, 'orderOverview', ' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details li', $combined_selectors );
				// Downloads.
				$combined_selectors = Cartflows_Gb_Helper::get_typography_css( $attr, 'downloadHeading', ' .wpcf__order-detail-form .woocommerce-order h2.woocommerce-order-downloads__title, .wpcf__order-detail-form .woocommerce-order .woocommerce-order-downloads h2.woocommerce-order-downloads__title', $combined_selectors );
				$combined_selectors = Cartflows_Gb_Helper::get_typography_css( $attr, 'downloadContent', ' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-downloads table.shop_table', $combined_selectors );
				// Order Details.
				$combined_selectors = Cartflows_Gb_Helper::get_typography_css( $attr, 'orderDetailHeading', ' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details .woocommerce-order-details__title', $combined_selectors );
				$combined_selectors = Cartflows_Gb_Helper::get_typography_css( $attr, 'orderDetailContent', ' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details .woocommerce-table', $combined_selectors );
				// Customer Details.
				$combined_selectors = Cartflows_Gb_Helper::get_typography_css( $attr, 'customerDetailHeading', ' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details .woocommerce-column__title', $combined_selectors );
				$combined_selectors = Cartflows_Gb_Helper::get_typography_css( $attr, 'customerDetailContent', ' .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details address, .wpcf__order-detail-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details address p', $combined_selectors );

				return Cartflows_Gb_Helper::generate_all_css( $combined_selectors, ' .cf-block-' . $id );
		}

			/**
			 * Get Checkout form CSS
			 *
			 * @since 1.6.15
			 * @param array  $attr The block attributes.
			 * @param string $id The selector ID.
			 * @return array The Widget List.
			 */
		public static function get_checkout_form_css( $attr, $id ) {

			$defaults = Cartflows_Gb_Helper::$block_list['wcfb/checkout-form']['attributes'];

			$attr = array_merge( $defaults, (array) $attr );

			$bg_type      = ( isset( $attr['backgroundType'] ) ) ? $attr['backgroundType'] : 'none';
			$overlay_type = ( isset( $attr['overlayType'] ) ) ? $attr['overlayType'] : 'none';

			$box_shadow_position_css = $attr['boxShadowPosition'];

			if ( 'outset' === $attr['boxShadowPosition'] ) {
				$box_shadow_position_css = '';
			}

			$input_field_border_css = self::generate_border_css( $attr, 'field' );

			$input_field_border_css        = self::generate_deprecated_border_css(
				$input_field_border_css,
				( isset( $attr['fieldBorderWidth'] ) ? $attr['fieldBorderWidth'] : '' ),
				( isset( $attr['fieldBorderRadius'] ) ? $attr['fieldBorderRadius'] : '' ),
				( isset( $attr['fieldBorderColor'] ) ? $attr['fieldBorderColor'] : '' ),
				( isset( $attr['fieldBorderStyle'] ) ? $attr['fieldBorderStyle'] : '' ),
				( isset( $attr['fieldBorderHColor'] ) ? $attr['fieldBorderHColor'] : '' )
			);
			$input_field_border_css_tablet = self::generate_border_css( $attr, 'field', 'tablet' );
			$input_field_border_css_mobile = self::generate_border_css( $attr, 'field', 'mobile' );

			$button_border_css        = self::generate_border_css( $attr, 'button' );
			$button_border_css_tablet = self::generate_border_css( $attr, 'button', 'tablet' );
			$button_border_css_mobile = self::generate_border_css( $attr, 'button', 'mobile' );

			$m_selectors = array(
				' .wcf-embed-checkout-form .woocommerce-checkout #payment ul.payment_methods' => array(
					'padding-top'    => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionpaddingTopMobile'], $attr['paymentSectionpaddingTypeMobile'] ),
					'padding-right'  => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionpaddingRightMobile'], $attr['paymentSectionpaddingTypeMobile'] ),
					'padding-bottom' => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionpaddingBottomMobile'], $attr['paymentSectionpaddingTypeMobile'] ),
					'padding-left'   => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionpaddingLeftMobile'], $attr['paymentSectionpaddingTypeMobile'] ),
					'margin-top'     => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionMarginTopMobile'], $attr['paymentSectionMarginTypeMobile'] ),
					'margin-right'   => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionMarginRightMobile'], $attr['paymentSectionMarginTypeMobile'] ),
					'margin-bottom'  => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionMarginBottomMobile'], $attr['paymentSectionMarginTypeMobile'] ),
					'margin-left'    => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionMarginLeftMobile'], $attr['paymentSectionMarginTypeMobile'] ),
					'border-radius'  => Cartflows_Gb_Helper::get_css_value( $attr['sectionBorderRadius'], 'px' ),
				),
				' .wcf-embed-checkout-form .woocommerce #order_review button,  .wcf-embed-checkout-form .woocommerce #payment button, .wcf-embed-checkout-form .woocommerce .wcf-customer-login-section__login-button, .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button, .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button,  .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn' => $button_border_css_tablet,
				' .wcf-embed-checkout-form .woocommerce form .form-row input.input-text, .wcf-embed-checkout-form .woocommerce form .form-row select, .wcf-embed-checkout-form .woocommerce form .form-row textarea, .wcf-embed-checkout-form .select2-container--default .select2-selection--single, .wcf-embed-checkout-form #order_review .wcf-custom-coupon-field input[type="text"], .wcf-embed-checkout-form .woocommerce form .form-row input.input-text, .wcf-embed-checkout-form .woocommerce form .form-row textarea, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .woocommerce form .form-row textarea, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .woocommerce #order_review .input-text, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .select2-container--default .select2-selection--single, .wcf-embed-checkout-form .woocommerce form .form-row select.select, .wcf-embed-checkout-form .woocommerce form .form-row select' => $input_field_border_css_tablet,
				' .wcf-embed-checkout-form .woocommerce #order_review button,  .wcf-embed-checkout-form .woocommerce #payment button, .wcf-embed-checkout-form .woocommerce .wcf-customer-login-section__login-button, .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button, .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button' => array(
					'letter-spacing' => Cartflows_Gb_Helper::get_css_value( $attr['buttonLetterSpacingMobile'], $attr['buttonLetterSpacingType'] ),
				),
			);
			$t_selectors = array(
				' .wcf-embed-checkout-form .woocommerce-checkout #payment ul.payment_methods' => array(
					'padding-top'    => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionpaddingTopTablet'], $attr['paymentSectionpaddingTypeTablet'] ),
					'padding-right'  => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionpaddingRightTablet'], $attr['paymentSectionpaddingTypeTablet'] ),
					'padding-bottom' => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionpaddingBottomTablet'], $attr['paymentSectionpaddingTypeTablet'] ),
					'padding-left'   => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionpaddingLeftTablet'], $attr['paymentSectionpaddingTypeTablet'] ),
					'margin-top'     => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionMarginTopTablet'], $attr['paymentSectionMarginTypeTablet'] ),
					'margin-right'   => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionMarginRightTablet'], $attr['paymentSectionMarginTypeTablet'] ),
					'margin-bottom'  => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionMarginBottomTablet'], $attr['paymentSectionMarginTypeTablet'] ),
					'margin-left'    => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionMarginLeftTablet'], $attr['paymentSectionMarginTypeTablet'] ),
					'border-radius'  => Cartflows_Gb_Helper::get_css_value( $attr['sectionBorderRadius'], 'px' ),
				),
				' .wcf-embed-checkout-form .woocommerce #order_review button,  .wcf-embed-checkout-form .woocommerce #payment button, .wcf-embed-checkout-form .woocommerce .wcf-customer-login-section__login-button, .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button, .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button,  .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn' => $button_border_css_mobile,
				' .wcf-embed-checkout-form .woocommerce form .form-row input.input-text, .wcf-embed-checkout-form .woocommerce form .form-row select, .wcf-embed-checkout-form .woocommerce form .form-row textarea, .wcf-embed-checkout-form .select2-container--default .select2-selection--single, .wcf-embed-checkout-form #order_review .wcf-custom-coupon-field input[type="text"], .wcf-embed-checkout-form .woocommerce form .form-row input.input-text, .wcf-embed-checkout-form .woocommerce form .form-row textarea, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .woocommerce form .form-row textarea, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .woocommerce #order_review .input-text, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .select2-container--default .select2-selection--single, .wcf-embed-checkout-form .woocommerce form .form-row select.select, .wcf-embed-checkout-form .woocommerce form .form-row select' => $input_field_border_css_mobile,
				' .wcf-embed-checkout-form .woocommerce #order_review button,  .wcf-embed-checkout-form .woocommerce #payment button, .wcf-embed-checkout-form .woocommerce .wcf-customer-login-section__login-button, .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button, .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button' => array(
					'letter-spacing' => Cartflows_Gb_Helper::get_css_value( $attr['buttonLetterSpacingTablet'], $attr['buttonLetterSpacingType'] ),
				),
			);

			$selectors = array(
				' .wcf-embed-checkout-form' => array(
					'--wcf-primary-color'                 => $attr['globalbgColor'],
					'--wcf-text-color'                    => $attr['globaltextColor'],
					'--wcf-heading-color'                 => $attr['headBgColor'],
					'--wcf-field-label-color'             => $attr['fieldLabelColor'],
					'--wcf-field-bg-color'                => $attr['fieldBgColor'],
					'--wcf-field-text-color'              => $attr['fieldInputColor'],
					'--wcf-field-border-color'            => $attr['fieldBorderColor'],
					'--wcf-field-error-label-color'       => $attr['errorLabelColor'],
					'--wcf-field-error-border-color'      => $attr['errorFieldBorderColor'],
					'--wcf-btn-text-color'                => $attr['buttonTextColor'],
					'--wcf-btn-bg-color'                  => $attr['backgroundColor'],
					'--wcf-btn-hover-text-color'          => $attr['buttonTextHoverColor'],
					'--wcf-btn-bg-hover-color'            => $attr['backgroundHoverColor'],
					'--wcf-payment-section-label-color'   => $attr['paymenttitleColor'],
					'--wcf-payment-section-desc-text-color' => $attr['paymentdescriptionColor'],
					'--wcf-payment-section-desc-bg-color' => $attr['informationbgColor'],
					'--wcf-payment-section-bg-color'      => $attr['sectionbgColor'],
				),
				' .wcf-embed-checkout-form .woocommerce .woocommerce-checkout input[type="checkbox"]:focus, .wcf-embed-checkout-form .woocommerce .woocommerce-checkout #order_review input[type="checkbox"]:focus, .wcf-embed-checkout-form .woocommerce .woocommerce-checkout .woocommerce-account-fields input[type="checkbox"]:focus, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .woocommerce .woocommerce-checkout input[type="checkbox"]:focus, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .woocommerce #order_review input[type="checkbox"]:focus ' => array(
					'box-shadow' => '0 0 2px',
				),
				' .wcf-embed-checkout-form .woocommerce #order_review button, .wcf-embed-checkout-form .woocommerce #payment button, .wcf-embed-checkout-form .woocommerce .wcf-customer-login-section__login-button, .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button, .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn' => array_merge(
					array(
						'background-color' => $attr['buttonBgColor'],
						'text-transform'   => $attr['buttonTransform'],
						'letter-spacing'   => Cartflows_Gb_Helper::get_css_value( $attr['buttonLetterSpacing'], $attr['buttonLetterSpacingType'] ),
					),
					$button_border_css
				),
				' .wcf-embed-checkout-form .woocommerce #payment #place_order:hover' => array(
					'border-color'     => $attr['buttonBorderHColor'],
					'background-color' => $attr['buttonBgHoverColor'],
				),
				' .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small:hover, .wcf-embed-checkout-form .woocommerce .wcf-customer-login-section__login-button:hover, .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn:hover' => array(
					'border-color'     => $attr['buttonBorderHColor'],
					'background-color' => $attr['buttonBgHoverColor'],
				),
				' .wcf-embed-checkout-form .woocommerce-checkout #payment ul.payment_methods' => array(
					'padding-top'    => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionpaddingTop'], $attr['paymentSectionpaddingTypeDesktop'] ),
					'padding-right'  => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionpaddingRight'], $attr['paymentSectionpaddingTypeDesktop'] ),
					'padding-bottom' => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionpaddingBottom'], $attr['paymentSectionpaddingTypeDesktop'] ),
					'padding-left'   => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionpaddingLeft'], $attr['paymentSectionpaddingTypeDesktop'] ),
					'margin-top'     => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionMarginTop'], $attr['paymentSectionMarginTypeDesktop'] ),
					'margin-right'   => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionMarginRight'], $attr['paymentSectionMarginTypeDesktop'] ),
					'margin-bottom'  => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionMarginBottom'], $attr['paymentSectionMarginTypeDesktop'] ),
					'margin-left'    => Cartflows_Gb_Helper::get_css_value( $attr['paymentSectionMarginLeft'], $attr['paymentSectionMarginTypeDesktop'] ),
					'border-radius'  => Cartflows_Gb_Helper::get_css_value( $attr['sectionBorderRadius'], 'px' ),
				),
				' .wcf-embed-checkout-form #payment .woocommerce-privacy-policy-text p' => array(
					'color' => $attr['paymentdescriptionColor'],
				),
				' .wcf-embed-checkout-form .woocommerce form .form-row input.input-text, .wcf-embed-checkout-form .woocommerce form .form-row select, .wcf-embed-checkout-form .woocommerce form .form-row textarea, .wcf-embed-checkout-form .select2-container--default .select2-selection--single, .wcf-embed-checkout-form #order_review .wcf-custom-coupon-field input[type="text"], .wcf-embed-checkout-form .woocommerce form .form-row input.input-text, .wcf-embed-checkout-form .woocommerce form .form-row textarea, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .woocommerce form .form-row textarea, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .woocommerce #order_review .input-text, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .select2-container--default .select2-selection--single, .wcf-embed-checkout-form .woocommerce form .form-row select.select, .wcf-embed-checkout-form .woocommerce form .form-row select' => array(
					$input_field_border_css,
				),
				' .wcf-embed-checkout-form .woocommerce form .form-row input.input-text:hover, .wcf-embed-checkout-form .woocommerce form .form-row select:hover, .wcf-embed-checkout-form .woocommerce form .form-row textarea:hover, .wcf-embed-checkout-form #order_review .wcf-custom-coupon-field input[type="text"]:hover, .wcf-embed-checkout-form .woocommerce form .form-row select#billing_country:hover, .wcf-embed-checkout-form .woocommerce form .form-row select:hover, .wcf-embed-checkout-form .woocommerce form .form-row select:hover, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .select2-container--default .select2-selection--single:hover, .wcf-embed-checkout-form .woocommerce form .form-row select.select:hover' => array(
					'border-color' => $attr['fieldBorderHColor'],
				),
				' .woocommerce-NoticeGroup.woocommerce-NoticeGroup-checkout li, .wcf-embed-checkout-form .woocommerce .wcf-custom-coupon-field .woocommerce-error li' => array(
					'color' => $attr['errorMsgColor'],
				),
				' .wcf-embed-checkout-form .woocommerce .woocommerce-NoticeGroup .woocommerce-error, .wcf-embed-checkout-form .woocommerce .woocommerce-notices-wrapper .woocommerce-error' => array(
					'background-color' => $attr['errorMsgBgColor'],
					'border-radius'    => Cartflows_Gb_Helper::get_css_value( $attr['msgBorderRadius'], 'px' ),
					'border-color'     => $attr['errorMsgBorderColor'],
					'padding-top'      => Cartflows_Gb_Helper::get_css_value( $attr['msgHrPadding'], 'px' ),
					'padding-right'    => Cartflows_Gb_Helper::get_css_value( $attr['msgVrPadding'], 'px' ),
					'padding-bottom'   => Cartflows_Gb_Helper::get_css_value( $attr['msgHrPadding'], 'px' ),
					'padding-left'     => Cartflows_Gb_Helper::get_css_value( $attr['msgVrPadding'], 'px' ),
					'border-width'     => Cartflows_Gb_Helper::get_css_value( $attr['msgBorderSize'], 'px' ),
				),
				' .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .woocommerce-checkout table.shop_table' => array(
					'background-color' => $attr['orderReviewColumnColor'],
				),
				' .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout table.shop_table th, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout table.shop_table td' => array(
					'color' => $attr['orderReviewColumnTextColor'],
				),
				' .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .woocommerce form .form-row input.input-text:focus, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .woocommerce form .form-row textarea:focus, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .woocommerce #order_review .wcf-custom-coupon-field input.input-text:focus' => array(
					'box-shadow' => $attr['globalbgColor'] ? '0 0 0 1px ' . $attr['globalbgColor'] : '',
				),
			);
			if ( 'color' == $bg_type ) {
				$selectors[' .wcf-embed-checkout-form .woocommerce #order_review button, .wcf-embed-checkout-form .woocommerce #payment button, .wcf-embed-checkout-form .woocommerce .wcf-customer-login-section__login-button, .wcf-embed-checkout-form .woocommerce form.woocommerce-form-login .form-row button, .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small, .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon, .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button, .wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button, .wcf-embed-checkout-form form.checkout_coupon .button, .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button,  .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn'] = array(
					'opacity'    => ( isset( $attr['backgroundOpacity'] ) && '' !== $attr['backgroundOpacity'] ) ? $attr['backgroundOpacity'] / 100 : 0.79,
					'box-shadow' => Cartflows_Gb_Helper::get_css_value( $attr['boxShadowHOffset'], 'px' ) . ' ' . Cartflows_Gb_Helper::get_css_value( $attr['boxShadowVOffset'], 'px' ) . ' ' . Cartflows_Gb_Helper::get_css_value( $attr['boxShadowBlur'], 'px' ) . ' ' . Cartflows_Gb_Helper::get_css_value( $attr['boxShadowSpread'], 'px' ) . ' ' . $attr['boxShadowColor'] . ' ' . $box_shadow_position_css,
				);
			}

			if ( 'gradient' == $bg_type ) {
				$selectors[' .wcf-embed-checkout-form .woocommerce #payment button, .wcf-embed-checkout-form .woocommerce #order_review button, .wcf-embed-checkout-form .woocommerce form.woocommerce-form-login .form-row button, .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small, .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon, .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button, .wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button, .wcf-embed-checkout-form form.checkout_coupon .button, .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn'] = array(
					'box-shadow' => Cartflows_Gb_Helper::get_css_value( $attr['boxShadowHOffset'], 'px' ) . ' ' . Cartflows_Gb_Helper::get_css_value( $attr['boxShadowVOffset'], 'px' ) . ' ' . Cartflows_Gb_Helper::get_css_value( $attr['boxShadowBlur'], 'px' ) . ' ' . Cartflows_Gb_Helper::get_css_value( $attr['boxShadowSpread'], 'px' ) . ' ' . $attr['boxShadowColor'] . ' ' . $box_shadow_position_css,
				);
			}

			$position = str_replace( '-', ' ', $attr['backgroundPosition'] );

			if ( 'image' == $bg_type ) {
				$selectors[' .wcf-embed-checkout-form .woocommerce #order_review button, .wcf-embed-checkout-form .woocommerce form.woocommerce-form-login .form-row button, .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small, .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon, .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button, .wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button, .wcf-embed-checkout-form form.checkout_coupon .button, .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn'] = array(
					'opacity'               => ( isset( $attr['backgroundOpacity'] ) && '' !== $attr['backgroundOpacity'] ) ? $attr['backgroundOpacity'] / 100 : 0,
					'background-color'      => $attr['backgroundImageColor'],
					'background-image'      => ( isset( $attr['backgroundImage'] ) && isset( $attr['backgroundImage']['url'] ) ) ? "url('" . $attr['backgroundImage']['url'] . "' )" : null,
					'background-position'   => $position,
					'background-attachment' => $attr['backgroundAttachment'],
					'background-repeat'     => $attr['backgroundRepeat'],
					'background-size'       => $attr['backgroundSize'],
					'box-shadow'            => Cartflows_Gb_Helper::get_css_value( $attr['boxShadowHOffset'], 'px' ) . ' ' . Cartflows_Gb_Helper::get_css_value( $attr['boxShadowVOffset'], 'px' ) . ' ' . Cartflows_Gb_Helper::get_css_value( $attr['boxShadowBlur'], 'px' ) . ' ' . Cartflows_Gb_Helper::get_css_value( $attr['boxShadowSpread'], 'px' ) . ' ' . $attr['boxShadowColor'] . ' ' . $box_shadow_position_css,
				);
			} elseif ( 'gradient' === $bg_type ) {

				$selectors[' .wcf-embed-checkout-form .woocommerce #order_review button, .wcf-embed-checkout-form .woocommerce form.woocommerce-form-login .form-row button, .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small, .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon, .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button, .wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button, .wcf-embed-checkout-form form.checkout_coupon .button, .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn']['background-color'] = 'transparent';
				$selectors[' .wcf-embed-checkout-form .woocommerce #order_review button, .wcf-embed-checkout-form .woocommerce form.woocommerce-form-login .form-row button, .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small, .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon, .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button, .wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button, .wcf-embed-checkout-form form.checkout_coupon .button, .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn']['opacity']          = ( isset( $attr['backgroundOpacity'] ) && '' !== $attr['backgroundOpacity'] ) ? $attr['backgroundOpacity'] / 100 : 0;
				if ( $attr['gradientValue'] ) {
					$selectors[' .wcf-embed-checkout-form .woocommerce #order_review button, .wcf-embed-checkout-form .woocommerce #payment button, .wcf-embed-checkout-form .woocommerce #wcf-customer-login-section__login-button, .wcf-embed-checkout-form .woocommerce form.woocommerce-form-login .form-row button, .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small, .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon, .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button, .wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button, .wcf-embed-checkout-form form.checkout_coupon .button, .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn']['background-image'] = $attr['gradientValue'];

				} else {
					if ( 'linear' === $attr['gradientType'] ) {

						$selectors[' .wcf-embed-checkout-form .woocommerce #order_review button, .wcf-embed-checkout-form .woocommerce form.woocommerce-form-login .form-row button, .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small, .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon, .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button, .wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button, .wcf-embed-checkout-form form.checkout_coupon .button, .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn']['background-image'] = 'linear-gradient(' . $attr['gradientAngle'] . 'deg, ' . $attr['gradientColor1'] . ' ' . $attr['gradientLocation1'] . '%, ' . $attr['gradientColor2'] . ' ' . $attr['gradientLocation2'] . '%)';
					} else {

						$selectors[' .wcf-embed-checkout-form .woocommerce #order_review button, .wcf-embed-checkout-form .woocommerce form.woocommerce-form-login .form-row button, .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small, .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon, .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button, .wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button, .wcf-embed-checkout-form form.checkout_coupon .button, .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn']['background-image'] = 'radial-gradient( at ' . $attr['gradientPosition'] . ', ' . $attr['gradientColor1'] . ' ' . $attr['gradientLocation1'] . '%, ' . $attr['gradientColor2'] . ' ' . $attr['gradientLocation2'] . '%)';
					}
				}
			}

			$combined_selectors = array(
				'desktop' => $selectors,
				'tablet'  => $t_selectors,
				'mobile'  => $m_selectors,
			);

			$combined_selectors = Cartflows_Gb_Helper::get_typography_css( $attr, 'global', ' .wcf-embed-checkout-form .woocommerce', $combined_selectors );
			$combined_selectors = Cartflows_Gb_Helper::get_typography_css( $attr, 'head', ' .wcf-embed-checkout-form .woocommerce h3, .wcf-embed-checkout-form .woocommerce-checkout #order_review_heading, .wcf-embed-checkout-form .woocommerce h3, .wcf-embed-checkout-form .woocommerce h3 span', $combined_selectors );
			$combined_selectors = Cartflows_Gb_Helper::get_typography_css( $attr, 'button', ' .wcf-embed-checkout-form .woocommerce #order_review button, .wcf-embed-checkout-form .woocommerce #payment button, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .woocommerce #payment button, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .woocommerce #order_review button.wcf-btn-small, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn', $combined_selectors );
			$combined_selectors = Cartflows_Gb_Helper::get_typography_css( $attr, 'input', ' .wcf-embed-checkout-form .woocommerce form p.form-row label, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .woocommerce form .form-row input.input-text, .wcf-embed-checkout-form .woocommerce form .form-row input.input-text, .wcf-embed-checkout-form .woocommerce form .form-row textarea, .wcf-embed-checkout-form .woocommerce form .form-row select#billing_country, .wcf-embed-checkout-form .woocommerce form .form-row select#billing_state,  .wcf-embed-checkout-form .woocommerce form .form-row select#shipping_country, .wcf-embed-checkout-form .woocommerce form .form-row select#shipping_state, span#select2-billing_country-container, .wcf-embed-checkout-form .select2-container--default .select2-selection--single .select2-selection__rendered, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .select2-container--default .select2-selection--single .select2-selection__rendered, .wcf-embed-checkout-form #order_review .wcf-custom-coupon-field input[type="text"], .wcf-embed-checkout-form .woocommerce form .form-row input.input-text, .wcf-embed-checkout-form .woocommerce form .form-row textarea, .wcf-embed-checkout-form .select2-container--default .select2-selection--single, .wcf-embed-checkout-form .woocommerce form .form-row select, .wcf-embed-checkout-form .woocommerce form .form-row select, .wcf-embed-checkout-form ::placeholder, .wcf-embed-checkout-form ::-webkit-input-placeholder, .wcf-embed-checkout-form .woocommerce #payment [type="radio"]:checked + label, .wcf-embed-checkout-form .woocommerce #payment [type="radio"]:not(:checked) + label, .wcf-embed-checkout-form.wcf-field-modern-label .woocommerce #customer_details .form-row:not( .wcf-anim-hidden-label ) label:not( .woocommerce-form__label-for-checkbox, .checkbox, .input-radio)', $combined_selectors );

			return Cartflows_Gb_Helper::generate_all_css( $combined_selectors, ' .cf-block-' . $id );
		}

			/**
			 * Get Optin Form Block CSS
			 *
			 * @since 1.6.15
			 * @param array  $attr The block attributes.
			 * @param string $id The selector ID.
			 * @return array The Widget List.
			 */
		public static function get_optin_form_css( $attr, $id ) {

			$defaults = Cartflows_Gb_Helper::$block_list['wcfb/optin-form']['attributes'];

			$attr = array_merge( $defaults, $attr );

			$t_selectors = array();
			$m_selectors = array();
			$selectors   = array();

			$input_field_border_css        = self::generate_border_css( $attr, 'inputField' );
			$input_field_border_css        = self::generate_deprecated_border_css(
				$input_field_border_css,
				( isset( $attr['inputFieldBorderWidth'] ) ? $attr['inputFieldBorderWidth'] : '' ),
				( isset( $attr['inputFieldBorderRadius'] ) ? $attr['inputFieldBorderRadius'] : '' ),
				( isset( $attr['inputFieldBorderColor'] ) ? $attr['inputFieldBorderColor'] : '' ),
				( isset( $attr['inputFieldBorderStyle'] ) ? $attr['inputFieldBorderStyle'] : '' ),
				( isset( $attr['inputFieldBorderHColor'] ) ? $attr['inputFieldBorderHColor'] : '' )
			);
			$input_field_border_css_tablet = self::generate_border_css( $attr, 'inputField', 'tablet' );
			$input_field_border_css_mobile = self::generate_border_css( $attr, 'inputField', 'mobile' );

			$submit_button_border_css        = self::generate_border_css( $attr, 'submitButton' );
			$submit_button_border_css        = self::generate_deprecated_border_css(
				$submit_button_border_css,
				( isset( $attr['submitButtonBorderWidth'] ) ? $attr['submitButtonBorderWidth'] : '' ),
				( isset( $attr['submitButtonBorderRadius'] ) ? $attr['submitButtonBorderRadius'] : '' ),
				( isset( $attr['submitButtonBorderColor'] ) ? $attr['submitButtonBorderColor'] : '' ),
				( isset( $attr['submitButtonBorderStyle'] ) ? $attr['submitButtonBorderStyle'] : '' ),
				( isset( $attr['submitButtonBorderHColor'] ) ? $attr['submitButtonBorderHColor'] : '' )
			);
			$submit_button_border_css_tablet = self::generate_border_css( $attr, 'submitButton', 'tablet' );
			$submit_button_border_css_mobile = self::generate_border_css( $attr, 'submitButton', 'mobile' );

			$box_shadow_position_css = $attr['boxShadowPosition'];

			if ( 'outset' === $attr['boxShadowPosition'] ) {
				$box_shadow_position_css = '';
			}

			$selectors = array(
				// General.
				' .wcf-optin-form .checkout.woocommerce-checkout #order_review .woocommerce-checkout-payment button#place_order' => array(
					'background-color' => $attr['generalPrimaryColor'],
					'border-color'     => $attr['generalPrimaryColor'],
				),

				// Input Fields.
				' .wcf-optin-form .checkout.woocommerce-checkout label' => array(
					'color' => $attr['inputFieldLabelColor'],
				),
				' .wcf-optin-form .checkout.woocommerce-checkout span input.input-text' => array_merge(
					array(
						'color'            => $attr['inputFieldTextPlaceholderColor'],
						'background-color' => $attr['inputFieldBackgroundColor'],
					),
					$input_field_border_css
				),

				' .wcf-optin-form .checkout.woocommerce-checkout span input.input-text:hover' => array(
					'border-color' => $attr['inputFieldBorderHColor'],
				),

				// Submit Button.
				' .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order' => array_merge(
					array(
						'color'            => $attr['submitButtonTextColor'],
						'background-color' => $attr['submitButtonBackgroundColor'],
						'box-shadow'       => Cartflows_Gb_Helper::get_css_value( $attr['boxShadowHOffset'], 'px' ) . ' ' . Cartflows_Gb_Helper::get_css_value( $attr['boxShadowVOffset'], 'px' ) . ' ' . Cartflows_Gb_Helper::get_css_value( $attr['boxShadowBlur'], 'px' ) . ' ' . Cartflows_Gb_Helper::get_css_value( $attr['boxShadowSpread'], 'px' ) . ' ' . $attr['boxShadowColor'] . ' ' . $box_shadow_position_css,
					),
					$submit_button_border_css
				),
				' .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order:hover' => array(
					'color'            => $attr['submitButtonTextHoverColor'],
					'background-color' => $attr['submitButtonBackgroundHoverColor'],
					'border-color'     => $attr['submitButtonBorderHColor'],
				),

			);

			$t_selectors = array(
				' .wcf-optin-form .checkout.woocommerce-checkout span input.input-text' => $input_field_border_css_tablet,
				' .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order' => $submit_button_border_css_tablet,
			);
			$m_selectors = array(
				' .wcf-optin-form .checkout.woocommerce-checkout span input.input-text' => $input_field_border_css_mobile,
				' .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order' => $submit_button_border_css_mobile,
			);

			$combined_selectors = array(
				'desktop' => $selectors,
				'tablet'  => $t_selectors,
				'mobile'  => $m_selectors,
			);

			// General.
			$combined_selectors = Cartflows_Gb_Helper::get_typography_css( $attr, 'general', ' .wcf-optin-form .checkout.woocommerce-checkout label, .wcf-optin-form .checkout.woocommerce-checkout span input.input-text, .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order', $combined_selectors );

			// Input Fields.
			$combined_selectors = Cartflows_Gb_Helper::get_typography_css( $attr, 'inputField', ' .wcf-optin-form .checkout.woocommerce-checkout label, .wcf-optin-form .checkout.woocommerce-checkout span input.input-text', $combined_selectors );

			// Submit Button.
			$combined_selectors = Cartflows_Gb_Helper::get_typography_css( $attr, 'submitButton', ' .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order', $combined_selectors );

			return Cartflows_Gb_Helper::generate_all_css( $combined_selectors, ' .cf-block-' . $id );
		}

		/**
		 * Border CSS generation Function.
		 *
		 * @since 2.0.0-beta.3
		 * @param  array  $attr   Attribute List.
		 * @param  string $prefix Attribuate prefix .
		 * @param  string $device Responsive.
		 * @return array         border css array.
		 */
		public static function generate_border_css( $attr, $prefix, $device = 'desktop' ) {
			$gen_border_css = array();
			if ( 'tablet' === $device ) {
				if ( 'none' !== $attr[ $prefix . 'BorderStyle' ] ) {
					$gen_border_css['border-top-width']    = self::get_css_value( $attr[ $prefix . 'BorderTopWidthTablet' ], 'px' );
					$gen_border_css['border-left-width']   = self::get_css_value( $attr[ $prefix . 'BorderLeftWidthTablet' ], 'px' );
					$gen_border_css['border-right-width']  = self::get_css_value( $attr[ $prefix . 'BorderRightWidthTablet' ], 'px' );
					$gen_border_css['border-bottom-width'] = self::get_css_value( $attr[ $prefix . 'BorderBottomWidthTablet' ], 'px' );
				}
				$gen_border_unit_tablet                       = isset( $attr[ $prefix . 'BorderRadiusUnitTablet' ] ) ? $attr[ $prefix . 'BorderRadiusUnitTablet' ] : 'px';
				$gen_border_css['border-top-left-radius']     = self::get_css_value( $attr[ $prefix . 'BorderTopLeftRadiusTablet' ], $gen_border_unit_tablet );
				$gen_border_css['border-top-right-radius']    = self::get_css_value( $attr[ $prefix . 'BorderTopRightRadiusTablet' ], $gen_border_unit_tablet );
				$gen_border_css['border-bottom-left-radius']  = self::get_css_value( $attr[ $prefix . 'BorderBottomLeftRadiusTablet' ], $gen_border_unit_tablet );
				$gen_border_css['border-bottom-right-radius'] = self::get_css_value( $attr[ $prefix . 'BorderBottomRightRadiusTablet' ], $gen_border_unit_tablet );
			} elseif ( 'mobile' === $device ) {
				if ( 'none' !== $attr[ $prefix . 'BorderStyle' ] ) {
					$gen_border_css['border-top-width']    = self::get_css_value( $attr[ $prefix . 'BorderTopWidthMobile' ], 'px' );
					$gen_border_css['border-left-width']   = self::get_css_value( $attr[ $prefix . 'BorderLeftWidthMobile' ], 'px' );
					$gen_border_css['border-right-width']  = self::get_css_value( $attr[ $prefix . 'BorderRightWidthMobile' ], 'px' );
					$gen_border_css['border-bottom-width'] = self::get_css_value( $attr[ $prefix . 'BorderBottomWidthMobile' ], 'px' );
				}
				$gen_border_unit_mobile                       = isset( $attr[ $prefix . 'BorderTopLeftRadiusMobile' ] ) ? $attr[ $prefix . 'BorderTopLeftRadiusMobile' ] : 'px';
				$gen_border_css['border-top-left-radius']     = self::get_css_value( $attr[ $prefix . 'BorderTopLeftRadiusMobile' ], $gen_border_unit_mobile );
				$gen_border_css['border-top-right-radius']    = self::get_css_value( $attr[ $prefix . 'BorderTopRightRadiusMobile' ], $gen_border_unit_mobile );
				$gen_border_css['border-bottom-left-radius']  = self::get_css_value( $attr[ $prefix . 'BorderBottomLeftRadiusMobile' ], $gen_border_unit_mobile );
				$gen_border_css['border-bottom-right-radius'] = self::get_css_value( $attr[ $prefix . 'BorderBottomRightRadiusMobile' ], $gen_border_unit_mobile );
			} else {
				if ( 'none' !== $attr[ $prefix . 'BorderStyle' ] ) {
					$gen_border_css['border-top-width']    = self::get_css_value( $attr[ $prefix . 'BorderTopWidth' ], 'px' );
					$gen_border_css['border-left-width']   = self::get_css_value( $attr[ $prefix . 'BorderLeftWidth' ], 'px' );
					$gen_border_css['border-right-width']  = self::get_css_value( $attr[ $prefix . 'BorderRightWidth' ], 'px' );
					$gen_border_css['border-bottom-width'] = self::get_css_value( $attr[ $prefix . 'BorderBottomWidth' ], 'px' );
				}
				$gen_border_unit                              = isset( $attr[ $prefix . 'BorderRadiusUnit' ] ) ? $attr[ $prefix . 'BorderRadiusUnit' ] : 'px';
				$gen_border_css['border-top-left-radius']     = self::get_css_value( $attr[ $prefix . 'BorderTopLeftRadius' ], $gen_border_unit );
				$gen_border_css['border-top-right-radius']    = self::get_css_value( $attr[ $prefix . 'BorderTopRightRadius' ], $gen_border_unit );
				$gen_border_css['border-bottom-left-radius']  = self::get_css_value( $attr[ $prefix . 'BorderBottomLeftRadius' ], $gen_border_unit );
				$gen_border_css['border-bottom-right-radius'] = self::get_css_value( $attr[ $prefix . 'BorderBottomRightRadius' ], $gen_border_unit );
			}
			$border_style                   = $attr[ $prefix . 'BorderStyle' ];
			$border_color                   = $attr[ $prefix . 'BorderColor' ];
			$gen_border_css['border-style'] = $border_style;
			$gen_border_css['border-color'] = $border_color;
			return $gen_border_css;
		}

		/**
		 * Get CSS value
		 *
		 * Syntax:
		 *
		 *  get_css_value( VALUE, UNIT );
		 *
		 * E.g.
		 *
		 *  get_css_value( VALUE, 'em' );
		 *
		 * @param string $value  CSS value.
		 * @param string $unit  CSS unit.
		 * @since 1.13.4
		 */
		public static function get_css_value( $value = '', $unit = '' ) {

			if ( '' == $value ) {
				return $value;
			}

			$css_val = '';

			if ( isset( $value ) ) {
				$css_val = esc_attr( $value ) . $unit;
			}

			return $css_val;
		}

		/**
		 * Deprecated Border CSS generation Function.
		 *
		 * @since 2.0.0-beta.3
		 * @param  array  $current_css   Current style list.
		 * @param  string $border_width   Border Width.
		 * @param  string $border_radius Border Radius.
		 * @param  string $border_color Border Color.
		 * @param string $border_style Border Style.
		 */
		public static function generate_deprecated_border_css( $current_css, $border_width, $border_radius, $border_color = '', $border_style = '' ) {
			$gen_border_css = array();
			if ( is_numeric( $border_width ) ) {
				$gen_border_css['border-top-width']    = self::get_css_value( $border_width, 'px' );
				$gen_border_css['border-left-width']   = self::get_css_value( $border_width, 'px' );
				$gen_border_css['border-right-width']  = self::get_css_value( $border_width, 'px' );
				$gen_border_css['border-bottom-width'] = self::get_css_value( $border_width, 'px' );
			}

			if ( is_numeric( $border_radius ) ) {
				$gen_border_css['border-top-left-radius']     = self::get_css_value( $border_radius, 'px' );
				$gen_border_css['border-top-right-radius']    = self::get_css_value( $border_radius, 'px' );
				$gen_border_css['border-bottom-left-radius']  = self::get_css_value( $border_radius, 'px' );
				$gen_border_css['border-bottom-right-radius'] = self::get_css_value( $border_radius, 'px' );
			}

			if ( $border_color ) {
				$gen_border_css['border-color'] = $border_color;
			}

			if ( $border_style ) {
				$gen_border_css['border-style'] = $border_style;
			}
			return wp_parse_args( $gen_border_css, $current_css );
		}

	}
}
