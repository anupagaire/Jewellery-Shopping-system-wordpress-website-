<?php
/**
 * Plugin Name: Checkout Plugins - Stripe for WooCommerce
 * Plugin URI: https://www.checkoutplugins.com/
 * Description: Stripe for WooCommerce delivers a simple, secure way to accept credit card payments in your WooCommerce store. Reduce payment friction and boost conversions using this free plugin!
 * Version: 1.5.0
 * Author: Checkout Plugins
 * Author URI: https://checkoutplugins.com/
 * License: GPLv2 or later
 * Text Domain: checkout-plugins-stripe-woo
 * WC requires at least: 3.0
 * WC tested up to: 8.2.0
 *
 * @package checkout-plugins-stripe-woo
 */

/**
 * Set constants
 */

define( 'CPSW_FILE', __FILE__ );
define( 'CPSW_BASE', plugin_basename( CPSW_FILE ) );
define( 'CPSW_DIR', plugin_dir_path( CPSW_FILE ) );
define( 'CPSW_URL', plugins_url( '/', CPSW_FILE ) );
define( 'CPSW_VERSION', '1.5.0' );

require_once 'autoloader.php';
