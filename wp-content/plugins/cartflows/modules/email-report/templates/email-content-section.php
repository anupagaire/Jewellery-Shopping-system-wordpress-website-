<?php
/**
 * Template Name: Email content.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-3" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #fff3f0;" width="100%">
	<tbody>
		<tr>
			<td>
				<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000000; width: 600px;" width="600">
					<tbody>
						<tr>
							<td class="column column-1" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: top; border-top: 0px; border-right: 0px; border-bottom: 0px; border-left: 0px;" width="50%">
								<table border="0" cellpadding="0" cellspacing="0" class="heading_block" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
									<tr>
										<td style="padding-bottom:10px;padding-left:10px;padding-top:60px;text-align:center;width:100%;">
											<h1 style="margin: 0; color: #1f2937; direction: ltr; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; font-size: 32px; font-weight: 400; letter-spacing: normal; line-height: 120%; text-align: left; margin-top: 0; margin-bottom: 0;">
												<?php
													/* translators: %s user name */
													echo sprintf( esc_html__( 'Hey %s!', 'cartflows' ), esc_attr( $user_name ) );
												?>
											</h1>
										</td>
									</tr>
								</table>
								<table border="0" cellpadding="0" cellspacing="0" class="text_block" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
									<tr>
										<td style="padding-bottom:55px;padding-left:10px;padding-right:10px;padding-top:10px;">
											<div style="font-family: sans-serif">
												<div style="font-size: 14px; mso-line-height-alt: 25.2px; color: #393d47; line-height: 1.8; font-family: Arial, Helvetica Neue, Helvetica, sans-serif;">
													<p style="margin: 0; font-size: 14px; text-align: left;">
														<?php
														echo sprintf(
															/* translators: %1$s: store name, %2$s: total revenue.  %3$s: total revenue*/
															esc_html__(
																'%1$s has earned a total %2$s in revenue last week by using CartFlows to power your store! And in the last month, it earned %3$s',
																'cartflows'
															),
															esc_attr( $store_name ),
															wp_kses_post( $total_revenue ),
															wp_kses_post( $last_month_revenue )
														);
														?>
													</p>
												</div>
											</div>
										</td>
									</tr>
								</table>
							</td>
							<td class="column column-2" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: top; border-top: 0px; border-right: 0px; border-bottom: 0px; border-left: 0px;" width="50%">
								<table border="0" cellpadding="0" cellspacing="0" class="heading_block" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
									<tr>
										<td style="padding-bottom:10px;padding-top:60px;text-align:center;width:100%;">
											<h1 style="margin: 0; color: #1f2937; direction: ltr; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; font-size: 32px; font-weight: 400; letter-spacing: normal; line-height: 120%; text-align: center; margin-top: 0; margin-bottom: 0;">
												<?php echo wp_kses_post( $total_revenue ); ?>
											</h1>
										</td>
									</tr>
								</table>
								<table border="0" cellpadding="0" cellspacing="0" class="text_block" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
									<tr>
										<td style="padding-bottom:55px;padding-left:10px;padding-right:10px;padding-top:10px;">
											<div style="font-family: sans-serif">
												<div style="font-size: 14px; mso-line-height-alt: 16.8px; color: #393d47; line-height: 1.2; font-family: Arial, Helvetica Neue, Helvetica, sans-serif;">
													<p style="margin: 0; font-size: 14px; text-align: center;">
														<span style="font-size:14px;"><?php echo esc_html( $from_date . ' - ' . $to_date ); ?></span>
													</p>
													<p style="margin: 0; font-size: 14px; text-align: center;">
														<span style="font-size:12px;">
															<?php
																echo esc_html__(
																	'(Last 7 days)',
																	'cartflows'
																);
																?>
														</span>
													</p>
													<p style="margin: 0; font-size: 14px; text-align: center; mso-line-height-alt: 16.8px;">&nbsp;</p>
													<p style="margin: 0; font-size: 22px; text-align: center;">
														<?php echo wp_kses_post( $last_month_revenue ); ?>
													</p>
													<p style="margin: 0; text-align: center;">
														<span style="font-size:12px;">
															<?php
																echo esc_html__(
																	'(Last 30 days)',
																	'cartflows'
																);
																?>
														</span>
													</p>
												</div>
											</div>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>
<?php
