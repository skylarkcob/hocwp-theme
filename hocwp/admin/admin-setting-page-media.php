<?php
function hocwp_theme_settings_page_media_tab( $tabs ) {
	$tabs['media'] = __( 'Media', 'hocwp-theme' );

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_media_tab' );

global $hocwp_theme;
if ( 'media' != $hocwp_theme->option->tab ) {
	return;
}

function hocwp_theme_settings_page_media_field() {
	$fields = array();

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_media_settings_field', 'hocwp_theme_settings_page_media_field' );