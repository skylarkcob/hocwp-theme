<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( function_exists( 'hocwp_theme_sanitize_extension_file' ) ) {
	return;
}

function hocwp_theme_load_extension_files( $path, &$arr ) {
	_deprecated_function( __FUNCTION__, '6.4.2', 'HT_Extension()->get_files()' );
	$arr = HT_Extension()->get_files();
}

function hocwp_theme_sanitize_extension_file( $extension_file ) {
	_deprecated_function( __FUNCTION__, '6.4.2', 'HT_Extension()->sanitize_file( $file )' );

	return HT_Extension()->sanitize_file( $extension_file );
}

function hocwp_theme_is_extension_active( $extension_file ) {
	_deprecated_function( __FUNCTION__, '6.4.2', 'HT_Extension()->is_active( $file )' );

	return HT_Extension()->is_active( $extension_file );
}

function hocwp_theme_is_shop_site() {
	return $GLOBALS['hocwp_theme']->is_wc_activated;
}