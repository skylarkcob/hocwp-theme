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

	$args     = array(
		'type'        => 'number',
		'class'       => 'small-text',
		'description' => __( 'The maximum media files upload per day for each member.', 'hocwp-theme' )
	);
	$field    = hocwp_theme_create_setting_field( 'upload_per_day', __( 'Upload Per Day', 'hocwp-theme' ), 'input', $args, 'positive_integer', 'media' );
	$fields[] = $field;

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_media_settings_field', 'hocwp_theme_settings_page_media_field' );