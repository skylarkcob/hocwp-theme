<?php
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
	$extension_file = hocwp_theme_sanitize_extension_file( $extension_file );

	return in_array( $extension_file, $GLOBALS['hocwp_theme']->active_extensions );
}