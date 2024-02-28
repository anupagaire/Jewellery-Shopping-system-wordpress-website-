<?php
/**
 * Modern Checkout.
 *
 * @package CartFlows Checkout
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Modern Checkout
 */
class Cartflows_Modern_Checkout {


	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 *  Constructor
	 */
	public function __construct() {

		// Load Modern Layout Checkout Customization Actions.
		add_action( 'cartflows_checkout_form_before', array( $this, 'modern_checkout_layout_actions' ), 10, 1 );

		add_filter( 'woocommerce_checkout_fields', array( $this, 'unset_fields_for_modern_checkout' ), 10, 1 );

	}

	/**
	 * Modern Checkout Layout Actions.
	 *
	 * @param int $checkout_id checkout ID.
	 * @since 1.10.0
	 */
	public function modern_checkout_layout_actions( $checkout_id ) {

		if ( $this->is_modern_checkout_layout( $checkout_id ) ) {

			$checkout_layout = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-layout' );

			add_action( 'woocommerce_checkout_before_customer_details', array( $this, 'customer_info_parent_wrapper_start' ), 20, 1 );

			add_action( 'woocommerce_checkout_billing', array( $this, 'add_custom_billing_email_field' ), 9, 1 );

			add_action( 'cartflows_woocommerce_review_order_before_payment', array( $this, 'display_custom_payment_heading' ), 12 );

			remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );

			add_action( 'woocommerce_checkout_after_customer_details', array( $this, 'customer_info_parent_wrapper_close' ), 99, 1 );

			/* Add the collapsable order review section at the top of Checkout form */
			add_action( 'woocommerce_before_checkout_form', array( $this, 'add_custom_collapsed_order_review_table' ), 8 );

			// Re-arrange the position of payment section only for two column layout of modern checkout & not for one column.
			if ( 'modern-checkout' === $checkout_layout ) {
				remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
				add_action( 'cartflows_checkout_after_modern_checkout_layout', 'woocommerce_checkout_payment', 21 );
			}
		}
	}


	/**
	 * Return true if checkout layout skin is conditional checkout.
	 *
	 * @param int $checkout_id Checkout ID.
	 *
	 * @return bool
	 */
	public function is_modern_checkout_layout( $checkout_id = '' ) {
		global $post;

		$is_modern_checkout = false;

		if ( ! empty( $post ) && empty( $checkout_id ) ) {
			$checkout_id = $post->ID;
		}

		if ( ! empty( $checkout_id ) ) {
			// Get checkout layout skin.
			$checkout_layout = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-layout' );

			if ( 'modern-checkout' === $checkout_layout || 'modern-one-column' === $checkout_layout ) {
				$is_modern_checkout = true;
			}
		}

		return $is_modern_checkout;
	}

	/**
	 * Add Customer Information Section.
	 *
	 * @param int $checkout_id checkout ID.
	 *
	 * @return void
	 */
	public function customer_info_parent_wrapper_start( $checkout_id ) {

		do_action( 'cartflows_checkout_before_modern_checkout_layout', $checkout_id );
		echo '<div class="wcf-customer-info-main-wrapper">';
	}

	/**
	 * Add Custom Email Field.
	 *
	 * @return void
	 */
	public function add_custom_billing_email_field() {

		$checkout_id = _get_wcf_checkout_id();

		if ( ! $checkout_id ) {
			$checkout_id = isset( $_GET['wcf_checkout_id'] ) && ! empty( $_GET['wcf_checkout_id'] ) ? intval( wp_unslash( $_GET['wcf_checkout_id'] ) ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$default = '';

		if ( is_auto_prefill_checkout_fields_enabled() && ( isset( $_GET['billing_email'] ) && ! empty( $_GET['billing_email'] ) ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$default = sanitize_email( wp_unslash( $_GET['billing_email'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$lost_password_url  = esc_url( wp_lostpassword_url() );
		$current_user_name  = wp_get_current_user()->display_name;
		$current_user_email = wp_get_current_user()->user_email;
		$is_allow_login     = 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder' );
		$fields_skins       = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-fields-skins' );
		$required_mark      = 'modern-label' === $fields_skins ? '&#42;' : '';

		?>
			<div class="wcf-customer-info" id="customer_info">
				<div class="wcf-customer-info__notice"></div>
				<div class="woocommerce-billing-fields-custom">
					<h3><?php echo esc_html( apply_filters( 'cartflows_woo_customer_info_text', __( 'Customer information', 'cartflows' ) ) ); ?>
						<?php if ( ! is_user_logged_in() && $is_allow_login ) { ?>
							<div class="woocommerce-billing-fields__customer-login-label"><?php /* translators: %1$s: Link HTML start, %2$s Link HTML End */ echo wp_kses_post( sprintf( __( 'Already have an account? %1$1s Log in%2$2s', 'cartflows' ), '<a href="#!" class="wcf-customer-login-url">', '</a>' ) ); ?></div>
						<?php } ?>
					</h3>
					<div class="woocommerce-billing-fields__customer-info-wrapper">
					<?php
					if ( ! is_user_logged_in() ) {

							woocommerce_form_field(
								'billing_email',
								array(
									'type'         => 'email',
									'class'        => array( 'form-row-fill' ),
									'required'     => true,
									'label'        => __( 'Email Address', 'cartflows' ),
									'default'      => $default,
									/* translators: %s: asterisk mark */
									'placeholder'  => sprintf( __( 'Email Address %s', 'cartflows' ), $required_mark ),
									'autocomplete' => 'email username',
								)
							);

						if ( 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder' ) ) {
							?>
								<div class="wcf-customer-login-section">
								<?php
									woocommerce_form_field(
										'billing_password',
										array(
											'type'        => 'password',
											'class'       => array( 'form-row-fill', 'wcf-password-field' ),
											'required'    => true,
											'label'       => __( 'Password', 'cartflows' ),
											/* translators: %s: asterisk mark */
											'placeholder' => sprintf( __( 'Password %s', 'cartflows' ), $required_mark ),
										)
									);
								?>
								<div class="wcf-customer-login-actions">
							<?php
									echo "<input type='button' name='wcf-customer-login-btn' class='button wcf-customer-login-section__login-button' id='wcf-customer-login-section__login-button' value='" . esc_attr( __( 'Login', 'cartflows' ) ) . "'>";
									echo '<a href=' . esc_url( $lost_password_url ) . " class='wcf-customer-login-lost-password-url'>" . esc_html( __( 'Lost your password?', 'cartflows' ) ) . '</a>';
							?>
								</div>
							<?php
							if ( 'yes' === get_option( 'woocommerce_enable_guest_checkout', false ) ) {
								echo "<p class='wcf-login-section-message'>" . esc_html( __( 'Login is optional, you can continue with your order below.', 'cartflows' ) ) . '</p>';
							}
							?>
								</div>
						<?php } ?>
						<?php
						if ( 'yes' === get_option( 'woocommerce_enable_signup_and_login_from_checkout' ) ) {
							?>
								<div class="wcf-create-account-section" hidden>
								<?php if ( 'yes' === get_option( 'woocommerce_enable_guest_checkout' ) ) { ?>
										<p class="form-row form-row-wide create-account">
											<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
												<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" type="checkbox" name="createaccount" value="1" /> <span><?php esc_html_e( 'Create an account?', 'cartflows' ); ?></span>
											</label>
										</p>
									<?php } ?>
									<div class="create-account">
									<?php

									if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) {
										woocommerce_form_field(
											'account_username',
											array(
												'type'     => 'text',
												'class'    => array( 'form-row-fill' ),
												'id'       => 'account_username',
												'required' => true,
												'label'    => __( 'Account username', 'cartflows' ),
												/* translators: %s: asterisk mark */
												'placeholder' => sprintf( __( 'Account username %s', 'cartflows' ), $required_mark ),
											)
										);
									}
									if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) {
										woocommerce_form_field(
											'account_password',
											array(
												'type'     => 'password',
												'id'       => 'account_password',
												'class'    => array( 'form-row-fill' ),
												'required' => true,
												'label'    => __( 'Create account password', 'cartflows' ),
												/* translators: %s: asterisk mark */
												'placeholder' => sprintf( __( 'Create account password %s', 'cartflows' ), $required_mark ),
											)
										);
									}
									?>
									</div>
								</div>
						<?php } ?>
					<?php } else { ?>
								<div class="wcf-logged-in-customer-info"> <?php /* translators: %1$s: username, %2$s emailid */ echo esc_html( apply_filters( 'cartflows_logged_in_customer_info_text', sprintf( __( ' Welcome Back %1$s ( %2$s )', 'cartflows' ), $current_user_name, $current_user_email ) ) ); ?>
									<div><input type="hidden" class="wcf-email-address" id="billing_email" name="billing_email" value="<?php echo esc_attr( $current_user_email ); ?>"/></div>
								</div>
					<?php } ?>
					</div>
				</div>
			</div>
		<?php
	}

		/**
		 * Display Payment heading field after coupon code fields.
		 */
	public function display_custom_payment_heading() {

		ob_start();
		?>
		<div class="wcf-payment-option-heading">
			<h3 id="payment_options_heading"><?php echo esc_html( apply_filters( 'cartflows_woo_payment_text', __( 'Payment', 'cartflows' ) ) ); ?></h3>
		</div>
		<?php
		// wp_kses_post will not work as it removing some tags hence ignoring below rule.
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

		/**
		 * Add closing wrapper to customer info and shipping section.
		 *
		 * @param int $checkout_id Checkout ID.
		 *
		 * @return void
		 */
	public function customer_info_parent_wrapper_close( $checkout_id ) {

		do_action( 'cartflows_checkout_after_modern_checkout_layout', $checkout_id );

		echo '</div>';
	}

	/**
	 * Customized order review section used to display in modern checkout responsive devices.
	 *
	 * @return void
	 */
	public function add_custom_collapsed_order_review_table() {

		include CARTFLOWS_CHECKOUT_DIR . 'templates/checkout/collapsed-order-summary.php';
	}

	/**
	 * Prefill the checkout fields if available in url.
	 *
	 * @param array $checkout_fields checkout fields array.
	 */
	public function unset_fields_for_modern_checkout( $checkout_fields ) {


		$checkout_id = _get_wcf_checkout_id();

		if ( ! $checkout_id ) {
			$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
		}

		if ( ! _is_wcf_checkout_type() || ! $this->is_modern_checkout_layout( $checkout_id ) ) {
			return $checkout_fields;
		}

		// No nonce verification required as it is called on woocommerce action.
		if ( wp_doing_ajax() || ( isset( $_GET['wc-ajax'] ) && 'checkout' === $_GET['wc-ajax'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $checkout_fields;
		}

		// Unset defalut billing email from Billing Details.
		unset( $checkout_fields['billing']['billing_email'] );
		unset( $checkout_fields['account']['account_username'] );
		unset( $checkout_fields['account']['account_password'] );

		return $checkout_fields;
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Modern_Checkout::get_instance();
