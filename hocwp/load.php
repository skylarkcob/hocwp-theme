<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Theme core version.
 */
define( 'HOCWP_THEME_CORE_VERSION', '6.4.3' );

/**
 * Theme developing mode.
 */
define( 'HOCWP_THEME_DEVELOPING', ( ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) ? true : false ) );

/**
 * Theme root path.
 */
define( 'HOCWP_THEME_PATH', get_template_directory() );

/**
 * Theme base url.
 */
define( 'HOCWP_THEME_URL', get_template_directory_uri() );

/**
 * Theme core path.
 */
define( 'HOCWP_THEME_CORE_PATH', untrailingslashit( dirname( __FILE__ ) ) );

/**
 * Theme core base url.
 */
define( 'HOCWP_THEME_CORE_URL', untrailingslashit( HOCWP_THEME_URL . '/hocwp' ) );

/**
 * CSS suffix.
 */
define( 'HOCWP_THEME_CSS_SUFFIX', ( HOCWP_THEME_DEVELOPING ) ? '.css' : '.min.css' );

/**
 * Javascript suffix.
 */
define( 'HOCWP_THEME_JS_SUFFIX', ( HOCWP_THEME_DEVELOPING ) ? '.js' : '.min.js' );

/**
 * Theme custom path.
 */
define( 'HOCWP_THEME_CUSTOM_PATH', HOCWP_THEME_PATH . '/custom' );

/**
 * Theme custom base url.
 */
define( 'HOCWP_THEME_CUSTOM_URL', HOCWP_THEME_URL . '/custom' );

/**
 * Detect doing ajax or not.
 */
define( 'HOCWP_THEME_DOING_AJAX', ( ( defined( 'DOING_AJAX' ) && true === DOING_AJAX ) ? true : false ) );

/**
 * Detect doing cron or not.
 */
define( 'HOCWP_THEME_DOING_CRON', ( ( defined( 'DOING_CRON' ) && true === DOING_CRON ) ? true : false ) );

/*
 * Load Theme Controller Class.
 */
require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-controller.php';