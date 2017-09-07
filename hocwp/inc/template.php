<?php
function hocwp_theme_load_template( $_template_file, $include_once = false ) {
	load_template( $_template_file, $include_once );
}

function hocwp_theme_load_views( $name ) {
	$name = HOCWP_Theme_Sanitize::extension( $name, 'php' );
	if ( ! file_exists( $name ) ) {
		$name = HOCWP_THEME_CORE_PATH . '/views/' . $name;
	}
	hocwp_theme_load_template( $name );
}

function hocwp_theme_load_custom_template( $name ) {
	$name = HOCWP_Theme_Sanitize::extension( $name, 'php' );
	if ( ! file_exists( $name ) ) {
		$name = get_template_directory() . '/custom/views/' . $name;
	}
	hocwp_theme_load_template( $name );
}

function hocwp_theme_load_custom_module( $name ) {
	$name = HOCWP_Theme_Sanitize::prefix( $name, 'module' );
	hocwp_theme_load_custom_template( $name );
}

function hocwp_theme_load_custom_loop( $name ) {
	$name = HOCWP_Theme_Sanitize::prefix( $name, 'loop' );
	hocwp_theme_load_custom_template( $name );
}

function hocwp_theme_load_template_none() {
	get_template_part( 'template-parts/content', 'none' );
}