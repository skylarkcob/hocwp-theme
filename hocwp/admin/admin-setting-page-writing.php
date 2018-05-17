<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_settings_page_writing_tab( $tabs ) {
	$tabs['writing'] = array(
		'text' => __( 'Writing', 'hocwp-theme' ),
		'icon' => '<span class="dashicons dashicons-edit"></span>'
	);

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_writing_tab' );

global $hocwp_theme;
if ( 'writing' != $hocwp_theme->option->tab ) {
	return;
}

function hocwp_theme_settings_page_writing_field() {
	$fields = array();

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_writing_settings_field', 'hocwp_theme_settings_page_writing_field' );