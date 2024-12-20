<?php
/*
 * Name: AMP
 * Description: The AMP Project is an open-source initiative aiming to make the web better for all.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'hocwp_ext_amp_missing_plugin_notice' ) ) {
	function hocwp_ext_amp_missing_plugin_notice() {
		$plugin = '<a href="https://wordpress.org/plugins/amp/" target="_blank">AMP for WordPress</a>';

		$args = array(
			'message' => sprintf( __( 'Please install and activate plugin %s for AMP extension works normally.', 'hocwp-theme' ), $plugin ),
			'type'    => 'warning'
		);

		ht_util()->admin_notice( $args );
	}
}

if ( ! function_exists( 'hocwp_ext_amp_require_plugins' ) ) {
	function hocwp_ext_amp_require_plugins( $plugins ) {
		$plugins[] = 'amp';

		return $plugins;
	}
}

add_filter( 'hocwp_theme_required_plugins', 'hocwp_ext_amp_require_plugins' );

if ( ! defined( 'AMP__VERSION' ) ) {
	add_action( 'admin_notices', 'hocwp_ext_amp_missing_plugin_notice' );

	return;
}

if ( ! function_exists( 'hocwp_theme_load_extension_amp' ) ) {
	function hocwp_theme_load_extension_amp() {
		$load = ht_extension()->is_active( __FILE__ );

		return apply_filters( 'hocwp_theme_load_extension_amp', $load );
	}
}

$load = hocwp_theme_load_extension_amp();

if ( ! $load ) {
	return;
}

require( dirname( __FILE__ ) . '/amp/amp.php' );