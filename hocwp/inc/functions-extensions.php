<?php
if ( function_exists( 'hocwp_theme_sanitize_extension_file' ) ) {
	return;
}

function hocwp_theme_sanitize_extension_file( $extension_file ) {
	$extension_file = str_replace( "\\\\", "\\", $extension_file );
	$extension_file = str_replace( "/", "\\", $extension_file );
	$extension_file = str_replace( HOCWP_THEME_CORE_PATH, '', $extension_file );
	$extension_file = str_replace( "\\", "/", $extension_file );
	$extension_file = ltrim( $extension_file, '/' );
	$parts          = explode( '/', $extension_file );
	if ( 2 < count( $parts ) ) {
		$parts          = array_slice( $parts, - 2, 2 );
		$extension_file = implode( '/', $parts );
	}

	return $extension_file;
}

function hocwp_theme_is_extension_active( $extension_file ) {
	global $hocwp_theme;

	if ( ! is_object( $hocwp_theme ) ) {
		$hocwp_theme = new stdClass();
	}

	if ( ! isset( $hocwp_theme->active_extensions ) ) {
		$hocwp_theme->active_extensions = (array) get_option( 'hocwp_theme_active_extensions', array() );
	}

	$extension_file = hocwp_theme_sanitize_extension_file( $extension_file );

	return in_array( $extension_file, $hocwp_theme->active_extensions );
}