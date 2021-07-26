<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! defined( 'HOCWP_THEME_BLANK_STYLE' ) ) {
	/**
	 * Define theme load default styles and scripts or not.
	 *
	 * Data type: boolean
	 */
	define( 'HOCWP_THEME_BLANK_STYLE', false );
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

if ( ! defined( 'HOCWP_THEME_REQUIRED_PLUGINS' ) ) {
	/**
	 * Define the required plugins for current theme.
	 *
	 * Data type: string
	 *
	 * Each plugin slug separates by commas.
	 */
	define( 'HOCWP_THEME_REQUIRED_PLUGINS', '' );
}

if ( ! defined( 'HOCWP_THEME_REQUIRED_EXTENSIONS' ) ) {
	/**
	 * Define the required extensions for current theme.
	 *
	 * Data type: string
	 *
	 * Each plugin slug separates by commas.
	 */
	define( 'HOCWP_THEME_REQUIRED_EXTENSIONS', '' );
}

if ( ! defined( 'HOCWP_THEME_RECOMMENDED_EXTENSIONS' ) ) {
	/**
	 * Define the recommended extensions for current theme.
	 *
	 * Data type: string
	 *
	 * Each extension slug separates by commas.
	 */
	define( 'HOCWP_THEME_RECOMMENDED_EXTENSIONS', '' );
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
	define( 'HOCWP_THEME_STRUCTURED_DATA', false );
}

if ( ! function_exists( 'hocwp_theme_post_thumbnail' ) ) {
	function hocwp_theme_post_thumbnail( $size = 'thumbnail', $attr = '' ) {
		hocwp_theme_post_thumbnail_html( $size, $attr );
	}
}

if ( ! defined( 'HOCWP_THEME_OVERTIME' ) ) {
	/**
	 * Skip work time checking.
	 *
	 * Data type: boolean
	 *
	 * If you still want to continue working, just define this value to TRUE.
	 */
	define( 'HOCWP_THEME_OVERTIME', false );
}

if ( ! defined( 'HOCWP_THEME_BREAK_MINUTES' ) ) {
	/**
	 * Working time interval.
	 *
	 * Data type: integer
	 *
	 * You should take a short break every 25 minutes. You can increase this number to work more longer. Define this
	 * number to zero to skip this function.
	 */
	define( 'HOCWP_THEME_BREAK_MINUTES', 25 );
}

if ( ! defined( 'HOCWP_THEME_SUPPORTS' ) ) {
	/*
	 * Custom theme supports using for add_theme_support function. You can apply default site background color, default
	 * background image, custom logo width and height, custom color for specific element etc.
	 *
	 * With custom colors like this: [custom-color][type_name][HEX color]
	 */
	define( 'HOCWP_THEME_SUPPORTS', array(
		'custom-background' => array(
			'default-color' => '#ffffff',
			'default-image' => ''
		),
		'custom-logo'       => array(
			'height'      => 40,
			'width'       => 120,
			'flex-height' => true,
			'flex-width'  => true
		),
		'custom-color'      => array(
			'primary'   => '#0073aa',
			'secondary' => '#23282d',
			'link'      => '#0073aa',
			'footer'    => '#f7f7f7'
		),
		'custom-header'     => array(
			'default-image'          => '',
			'width'                  => 0,
			'height'                 => 0,
			'flex-height'            => false,
			'flex-width'             => false,
			'uploads'                => true,
			'random-default'         => false,
			'header-text'            => true,
			'default-text-color'     => '',
			'wp-head-callback'       => '',
			'admin-head-callback'    => '',
			'admin-preview-callback' => ''
		)
	) );
}

if ( ! defined( 'HOCWP_THEME_DEFAULT_COLORS' ) ) {
	/*
	 * Setting default colors to apply for accent hue color picker. Each key contains child key: text, accent, secondary
	 * and borders.
	 *
	 * [type_name][text or accent or secondary or borders][HEX color]
	 */
	define( 'HOCWP_THEME_DEFAULT_COLORS', array(
		'content'       => array(
			'text'      => '#444444',
			'accent'    => '#ffffff',
			'secondary' => '#4ca6cf',
			'borders'   => '#dadada'
		),
		'header-footer' => array(
			'text'      => '#ffffff',
			'accent'    => '#23282d',
			'secondary' => '#f7f7f7',
			'borders'   => '#dfdfdf'
		)
	) );
}

if ( ! defined( 'HOCWP_THEME_CSS_ELEMENT_SELECTORS' ) ) {
	/*
 * Setting default CSS selectors for apply Default colors above. The key name (type_name) must same with keys in
 * array HOCWP_THEME_DEFAULT_COLORS.
 *
 * You can fill like this: [type_name][text or accent or secondary or borders][css property] = [elements]
 */
	define( 'HOCWP_THEME_CSS_ELEMENT_SELECTORS', array(
		'content'      => array(
			'accent'    => array(
				'background-color' => array(),
				'color'            => array(),
				'border-color'     => array()
			),
			'secondary' => array(
				'background-color' => array(),
				'color'            => array()
			),
			'text'      => array(
				'color' => array()
			),
			'borders'   => array(
				'border-color' => array()
			)
		),
		'notice'       => array(
			'text'      => array(
				'color' => array()
			),
			'accent'    => array(
				'background-color' => array()
			),
			'secondary' => array(),
			'borders'   => array(
				'border-color' => array(),
				'color'        => array()
			)
		),
		'custom-color' => array(
			'primary'         => array(
				'background-color' => array(),
				'color'            => array(),
				'border-color'     => array()
			),
			'secondary'       => array(
				'background-color' => array(),
				'color'            => array()
			),
			'link'            => array(
				'color' => array()
			),
			'footer'          => array(
				'background-color' => array()
			),
			'breadcrumb'      => array(
				'color' => array()
			),
			'breadcrumb-link' => array(
				'color' => array()
			)
		)
	) );
}