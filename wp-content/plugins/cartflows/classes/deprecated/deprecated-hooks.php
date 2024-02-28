<?php
/**
 * Deprecated Hooks of CartFlows Plugin.
 *
 * @package     CartFlows
 * @author      CartFlows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wcf_do_action_deprecated' ) ) {
	/**
	 * Cartlows Action Deprecated
	 *
	 * @since 1.1.1
	 * @param string $tag         The name of the Action hook.
	 * @param array  $args        Array of additional function arguments to be passed to apply_filters().
	 * @param string $version     The version of WordPress that deprecated the hook.
	 * @param string $replacement Optional. The hook that should have been used. Default false.
	 * @param string $message     Optional. A message regarding the change. Default null.
	 */
	function wcf_do_action_deprecated( $tag, $args, $version, $replacement = false, $message = null ) {
		if ( function_exists( 'do_action_deprecated' ) ) { /* WP >= 4.6 */
			do_action_deprecated( $tag, $args, $version, $replacement, $message );
		} else {
			do_action_ref_array( $tag, $args );
		}
	}
}

if ( ! function_exists( 'wcf_apply_filters_deprecated' ) ) {
	/**
	 * Cartlows Apply Filters Deprecated
	 *
	 * @since 1.6.0
	 * @param string $tag         The name of the Action hook.
	 * @param array  $args        Array of additional function arguments to be passed to apply_filters().
	 * @param string $version     The version of WordPress that deprecated the hook.
	 * @param string $replacement Optional. The hook that should have been used. Default false.
	 * @param string $message     Optional. A message regarding the change. Default null.
	 */
	function wcf_apply_filters_deprecated( $tag, $args, $version, $replacement = false, $message = null ) {
		if ( function_exists( 'apply_filters_deprecated' ) ) { /* WP >= 4.6 */
			return apply_filters_deprecated( $tag, $args, $version, $replacement, $message );
		} else {
			return apply_filters_ref_array( $tag, $args );
		}
	}
}
