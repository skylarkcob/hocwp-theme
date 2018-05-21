<?php
/*
 * Name: AMP
 * Description: The AMP Project is an open-source initiative aiming to make the web better for all.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_ext_amp_missing_plugin_notice() {
	$plugin = '<a href="https://wordpress.org/plugins/amp/" target="_blank">AMP for WordPress</a>';

	$args = array(
		'message' => sprintf( __( 'Please install and activate plugin %s for AMP extension works normally.', 'hocwp-theme' ), $plugin ),
		'type'    => 'warning'
	);

	HT_Util()->admin_notice( $args );
}

function hocwp_ext_amp_require_plugins( $plugins ) {
	$plugins[] = 'amp';

	return $plugins;
}

add_filter( 'hocwp_theme_required_plugins', 'hocwp_ext_amp_require_plugins' );

if ( ! defined( 'AMP__VERSION' ) ) {
	add_action( 'admin_notices', 'hocwp_ext_amp_missing_plugin_notice' );

	return;
}

function hocwp_theme_load_extension_amp() {
	$load = HT_Extension()->is_active( __FILE__ );
	$load = apply_filters( 'hocwp_theme_load_extension_amp', $load );

	return $load;
}

$load = hocwp_theme_load_extension_amp();

if ( ! $load ) {
	return;
}

require dirname( __FILE__ ) . '/amp/amp.php';