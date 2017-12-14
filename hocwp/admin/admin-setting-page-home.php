<?php
function hocwp_theme_settings_page_home_tab( $tabs ) {
	$tabs['home'] = __( 'Home', 'hocwp-theme' );

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_home_tab' );

global $hocwp_theme;
if ( 'home' != $hocwp_theme->option->tab ) {
	return;
}

function hocwp_theme_settings_page_home_section( $sections ) {
	$sections = apply_filters( 'hocwp_theme_setting_page_home_sections', $sections );

	return $sections;
}

add_filter( 'hocwp_theme_settings_page_home_settings_section', 'hocwp_theme_settings_page_home_section' );

function hocwp_theme_settings_page_home_field( $fields ) {
	global $hocwp_theme;
	$options = $hocwp_theme->options['home'];

	$args     = array(
		'type'  => 'number',
		'class' => 'small-text'
	);
	$field    = hocwp_theme_create_setting_field( 'posts_per_page', __( 'Posts Per Page', 'hocwp-theme' ), '', $args, '', 'home' );
	$fields[] = $field;

	$fields = apply_filters( 'hocwp_theme_setting_page_home_fields', $fields, $options );

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_home_settings_field', 'hocwp_theme_settings_page_home_field' );