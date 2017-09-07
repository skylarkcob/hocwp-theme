<?php
function hocwp_theme_settings_page_reading_tab( $tabs ) {
	$tabs['reading'] = __( 'Reading', 'hocwp-theme' );

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_reading_tab' );

global $hocwp_theme;
if ( 'reading' != $hocwp_theme->option->tab ) {
	return;
}

function hocwp_theme_settings_page_reading_field() {
	$fields = array();

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_reading_settings_field', 'hocwp_theme_settings_page_reading_field' );