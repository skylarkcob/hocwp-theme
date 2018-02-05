<?php
/**
 * Add theme setting fields to General tab.
 *
 * @param $fields
 *
 * @param $options
 *
 * @return array
 */
function hocwp_theme_custom_setting_fields( $fields, $options ) {
	$field    = hocwp_theme_create_setting_field( 'slider', __( 'Slider', 'hocwp-theme' ), 'editor', '', 'html' );
	$fields[] = $field;

	$args     = array(
		'rows' => 4
	);
	$field    = hocwp_theme_create_setting_field( 'maps', __( 'Maps', 'hocwp-theme' ), 'textarea', $args, 'html' );
	$fields[] = $field;

	return $fields;
}

add_filter( 'hocwp_theme_setting_fields', 'hocwp_theme_custom_setting_fields', 99, 2 );

/**
 * Add theme setting fields to Home tab, using for home page.
 *
 * @param $fields
 * @param $options
 *
 * @return mixed
 */
function hocwp_theme_custom_setting_page_home_fields( $fields, $options ) {
	$field    = hocwp_theme_create_setting_field_for_home( 'sortable', __( 'Sortable Categories', 'hocwp-theme' ), 'sortable_term' );
	$fields[] = $field;

	return $fields;
}

add_filter( 'hocwp_theme_setting_page_home_fields', 'hocwp_theme_custom_setting_page_home_fields', 99, 2 );

/**
 * Add theme setting sections to General tab.
 *
 * @param $sections
 *
 * @return mixed
 */
function hocwp_theme_custom_setting_sections( $sections ) {

	return $sections;
}

add_filter( 'hocwp_theme_setting_sections', 'hocwp_theme_custom_setting_sections' );

/**
 * Add theme setting sections to Home tab.
 *
 * @param $sections
 *
 * @return mixed
 */
function hocwp_theme_custom_setting_page_home_sections( $sections ) {

	return $sections;
}

add_filter( 'hocwp_theme_setting_page_home_sections', 'hocwp_theme_custom_setting_page_home_sections' );