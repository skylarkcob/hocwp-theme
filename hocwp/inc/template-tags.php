<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Define the theme name.
 *
 * Data type: string
 */
if ( ! defined( 'HOCWP_THEME_NAME' ) ) {
	$path = get_template_directory();
	$path .= '/style.css';
	$data = get_file_data( $path, array( 'real_theme_name' => 'Real Theme Name' ) );

	$theme_name = ( is_array( $data ) && isset( $data['real_theme_name'] ) ) ? $data['real_theme_name'] : '';

	define( 'HOCWP_THEME_NAME', $theme_name );
}

/**
 * Define theme support microformats or not.
 *
 * Data type: boolean
 */
if ( ! defined( 'HOCWP_THEME_SUPPORT_MICROFORMATS' ) ) {
	define( 'HOCWP_THEME_SUPPORT_MICROFORMATS', false );
}

/*
 * Using Structured Data Markup on your site.
 *
 * Data type: boolean
 *
 * Google Search works hard to understand the content of a page. You can help us by providing explicit clues about
 * the meaning of a page to Google by including structured data on the page. Structured data is a standardized format
 * for providing information about a page and classifying the page content; for example, on a recipe page, what are
 * the ingredients, the cooking time and temperature, the calories, and so on.
 */
if ( ! defined( 'HOCWP_THEME_STRUCTURED_DATA' ) ) {
	define( 'HOCWP_THEME_STRUCTURED_DATA', true );
}