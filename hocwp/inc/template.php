<?php
function hocwp_theme_load_template( $_template_file, $include_once = false ) {
	if ( $include_once ) {
		include_once( $_template_file );
	} else {
		include( $_template_file );
	}
}

function hocwp_theme_load_custom_template( $name ) {
	if ( false === strpos( $name, '.php' ) ) {
		$name .= '.php';
	}
	if ( ! file_exists( $name ) ) {
		$name = get_template_directory() . '/custom/' . $name;
	}
	hocwp_theme_load_template( $name );
}

function hocwp_theme_load_custom_module( $name ) {
	$module = substr( $name, 0, 6 );
	if ( 'module' != $module ) {
		$name = 'module-' . $name;
	}
	hocwp_theme_load_custom_template( $name );
}