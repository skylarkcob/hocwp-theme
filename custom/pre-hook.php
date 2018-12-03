<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

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