<?php
function hocwp_theme_settings_page_general_tab( $tabs ) {
	$tabs['general'] = __( 'General', 'hocwp-theme' );

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_general_tab' );

global $hocwp_theme;
if ( 'general' != $hocwp_theme->option->tab ) {
	return;
}

function hocwp_theme_settings_page_general_field() {
	$fields = array();

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_general_settings_field', 'hocwp_theme_settings_page_general_field' );