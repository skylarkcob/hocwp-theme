<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_settings_page_permalinks_tab( $tabs ) {
	$tabs['permalinks'] = array(
		'text' => __( 'Permalinks', 'hocwp-theme' ),
		'icon' => '<span class="dashicons dashicons-admin-links"></span>'
	);

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_permalinks_tab' );

global $hocwp_theme;
if ( 'permalinks' != $hocwp_theme->option->tab ) {
	return;
}

function hocwp_theme_settings_page_permalinks_section( $sections ) {

	return $sections;
}

add_filter( 'hocwp_theme_settings_page_permalinks_settings_section', 'hocwp_theme_settings_page_permalinks_section' );

function hocwp_theme_settings_page_permalinks_field( $fields ) {

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_permalinks_settings_field', 'hocwp_theme_settings_page_permalinks_field' );

function hocwp_theme_settings_page_permalinks_saved() {
	set_transient( 'hocwp_theme_flush_rewrite_rules', 1 );
}

add_action( 'hocwp_theme_settings_saved', 'hocwp_theme_settings_page_permalinks_saved' );
add_action( 'init', 'hocwp_theme_settings_page_permalinks_saved' );