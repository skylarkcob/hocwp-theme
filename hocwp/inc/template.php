<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_load_template( $_template_file, $include_once = false ) {
	if ( ! HT()->string_contain( $_template_file, '.php' ) ) {
		$_template_file .= '.php';
	}
	if ( HT()->is_file( $_template_file ) ) {
		load_template( $_template_file, $include_once );
	}
}

function hocwp_theme_load_views( $name ) {
	$name = HOCWP_Theme_Sanitize::extension( $name, 'php' );
	if ( ! HT()->is_file( $name ) ) {
		$name = HOCWP_THEME_CORE_PATH . '/views/' . $name;
	}
	hocwp_theme_load_template( $name );
}

function hocwp_theme_load_custom_template( $name ) {
	$name = HOCWP_Theme_Sanitize::extension( $name, 'php' );
	if ( ! HT()->is_file( $name ) ) {
		$name = HOCWP_THEME_CUSTOM_PATH . '/views/' . $name;
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

function hocwp_theme_load_content_none() {
	hocwp_theme_load_template_none();
}

function hocwp_theme_load_content_404() {
	get_template_part( 'hocwp/views/content', '404' );
}