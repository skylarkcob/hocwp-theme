<?php
function hocwp_theme_settings_page_permalinks_tab( $tabs ) {
	$tabs['permalinks'] = __( 'Permalinks', 'hocwp-theme' );

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_permalinks_tab' );

global $hocwp_theme;
if ( 'permalinks' != $hocwp_theme->option->tab ) {
	return;
}

function hocwp_theme_settings_page_permalinks_field() {
	$fields = array();

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_permalinks_settings_field', 'hocwp_theme_settings_page_permalinks_field' );