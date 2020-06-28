<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Define theme load default styles and scripts or not.
 *
 * Data type: boolean
 */
define( 'HOCWP_THEME_BLANK_STYLE', false );

/**
 * Define theme support microformats or not.
 *
 * Data type: boolean
 */
define( 'HOCWP_THEME_SUPPORT_MICROFORMATS', false );

/**
 * Define the required plugins for current theme.
 *
 * Data type: string
 *
 * Each plugin slug separates by commas.
 */
define( 'HOCWP_THEME_REQUIRED_PLUGINS', '' );

/**
 * Define the required extensions for current theme.
 *
 * Data type: string
 *
 * Each plugin slug separates by commas.
 */
define( 'HOCWP_THEME_REQUIRED_EXTENSIONS', '' );

/**
 * Define the recommended extensions for current theme.
 *
 * Data type: string
 *
 * Each extension slug separates by commas.
 */
define( 'HOCWP_THEME_RECOMMENDED_EXTENSIONS', '' );

/**
 * Skip work time checking.
 *
 * Data type: boolean
 *
 * If you still want to continue working, just define this value to TRUE.
 */
define( 'HOCWP_THEME_OVERTIME', true );

/**
 * Working time interval.
 *
 * Data type: integer
 *
 * You should take a short break every 25 minutes. You can increase this number to work more longer. Define this
 * number to zero to skip this function.
 */
define( 'HOCWP_THEME_BREAK_MINUTES', 0 );

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