<?php
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
 * Skip work time checking.
 *
 * Data type: boolean
 *
 * If you still want to continue working, just define this value to TRUE.
 */
define( 'HOCWP_THEME_OVERTIME', false );

/**
 * Working time interval.
 *
 * Data type: integer
 *
 * You should take a short break every 25 minutes. You can increase this number to work more longer. Define this
 * number to zero to skip this function.
 */
define( 'HOCWP_THEME_BREAK_MINUTES', 25 );