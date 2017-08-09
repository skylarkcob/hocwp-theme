<?php
function hocwp_theme_load_template( $_template_file, $include_once = false ) {
	if ( $include_once ) {
		include_once( $_template_file );
	} else {
		include( $_template_file );
	}
}

function hocwp_theme_load_views( $name ) {
	$name = HOCWP_Theme::sanitize_extension( $name, 'php' );
	if ( ! file_exists( $name ) ) {
		$name = HOCWP_THEME_CORE_PATH . '/views/' . $name;
	}
	hocwp_theme_load_template( $name );
}

function hocwp_theme_load_custom_template( $name ) {
	$name = HOCWP_Theme::sanitize_extension( $name, 'php' );
	if ( ! file_exists( $name ) ) {
		$name = get_template_directory() . '/custom/views/' . $name;
	}
	hocwp_theme_load_template( $name );
}

function hocwp_theme_load_custom_module( $name ) {
	$name = HOCWP_Theme::sanitize_prefix( $name, 'module' );
	hocwp_theme_load_custom_template( $name );
}

function hocwp_theme_load_custom_loop( $name ) {
	$name = HOCWP_Theme::sanitize_prefix( $name, 'loop' );
	hocwp_theme_load_custom_template( $name );
}

function hocwp_theme_load_template_none() {
	get_template_part( 'template-parts/content', 'none' );
}