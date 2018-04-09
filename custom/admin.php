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

	return $fields;
}

add_filter( 'hocwp_theme_setting_fields', 'hocwp_theme_custom_setting_fields', 99, 2 );

/**
 * Add theme setting fields to Home tab, using for home page.
 *
 * @param $fields
 * @param $options
 *
 * @return array
 */
function hocwp_theme_custom_setting_page_home_fields( $fields, $options ) {

	return $fields;
}

add_filter( 'hocwp_theme_setting_page_home_fields', 'hocwp_theme_custom_setting_page_home_fields', 99, 2 );

/**
 * Add theme setting sections to General tab.
 *
 * @param $sections
 *
 * @return array
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
 * @return array
 */
function hocwp_theme_custom_setting_page_home_sections( $sections ) {

	return $sections;
}

add_filter( 'hocwp_theme_setting_page_home_sections', 'hocwp_theme_custom_setting_page_home_sections' );

function hocwp_theme_custom_slider_posts_columns_filter( $columns ) {
	HT()->debug( $columns );
	$columns['slider_image'] = __( 'Slider Image', 'hocwp-theme' );

	return $columns;
}

add_filter( 'manage_slider_posts_columns', 'hocwp_theme_custom_slider_posts_columns_filter' );

function hocwp_theme_custom_slider_posts_custom_column_action( $column, $post_id ) {
	if ( 'slider_image' == $column ) {
		echo get_the_post_thumbnail( $post_id, 'thumbnail' );
	}
}

add_action( 'manage_slider_posts_custom_column', 'hocwp_theme_custom_slider_posts_custom_column_action', 10, 2 );