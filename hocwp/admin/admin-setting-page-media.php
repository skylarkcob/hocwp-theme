<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_settings_page_media_tab( $tabs ) {
	$tabs['media'] = array(
		'text' => __( 'Media', 'hocwp-theme' ),
		'icon' => '<span class="dashicons dashicons-paperclip"></span>'
	);

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_media_tab' );

global $hocwp_theme;
if ( 'media' != $hocwp_theme->option->tab ) {
	return;
}

function hocwp_theme_settings_page_media_field() {
	$fields = array();

	$args = array(
		'type'        => 'number',
		'class'       => 'small-text',
		'description' => __( 'The maximum media files upload per day for each member.', 'hocwp-theme' )
	);

	$field    = hocwp_theme_create_setting_field( 'upload_per_day', __( 'Upload Per Day', 'hocwp-theme' ), 'input', $args, 'positive_integer', 'media' );
	$fields[] = $field;

	$args['min'] = 0;
	$args['max'] = 100;

	$args['description'] = __( 'The right JPEG quality will ensure your images look great, your pages load fast and even help you rank well in search engines.', 'hocwp-theme' );

	$field    = hocwp_theme_create_setting_field( 'jpeg_quality', __( 'JPEG Quality', 'hocwp-theme' ), 'input', $args, 'positive_integer', 'media' );
	$fields[] = $field;

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_media_settings_field', 'hocwp_theme_settings_page_media_field' );