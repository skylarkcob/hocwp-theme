<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Theme core version.
 */
const HOCWP_THEME_CORE_VERSION = '6.9.5';

$theme = wp_get_theme();

$require_version = $theme->get( 'RequiresPHP' );

if ( empty( $require_version ) ) {
	$require_version = '8.0';
}

/**
 * Requires PHP version.
 */
define( 'HOCWP_THEME_REQUIRE_PHP_VERSION', $require_version );

/**
 * Theme developing mode.
 */
define( 'HOCWP_THEME_DEVELOPING', ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) );

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
const HOCWP_THEME_CSS_SUFFIX = '.css';

/**
 * Javascript suffix.
 */
const HOCWP_THEME_JS_SUFFIX = '.js';

/**
 * Theme custom path.
 */
const HOCWP_THEME_CUSTOM_PATH = HOCWP_THEME_PATH . '/custom';

/**
 * Theme custom base url.
 */
const HOCWP_THEME_CUSTOM_URL = HOCWP_THEME_URL . '/custom';

/**
 * Detect doing ajax or not.
 */
define( 'HOCWP_THEME_DOING_AJAX', ( defined( 'DOING_AJAX' ) && true === DOING_AJAX ) );

/**
 * Detect doing cron or not.
 */
define( 'HOCWP_THEME_DOING_CRON', ( defined( 'DOING_CRON' ) && true === DOING_CRON ) );

/*
 * Check current PHP version.
 */
$php_version = phpversion();

if ( version_compare( $php_version, $require_version, '<' ) ) {
	$dir = get_template_directory();
	$dir = dirname( $dir );

	$dirs = array_filter( glob( $dir . '/*' ), 'is_dir' );

	if ( ! empty( $dirs ) ) {
		$msg   = sprintf( __( '<strong>Error:</strong> You are using PHP version %s, please upgrade PHP version to at least %s.', 'hocwp-theme' ), $php_version, $require_version );
		$title = __( 'Invalid PHP Version', 'hocwp-theme' );

		$args = array(
			'back_link' => admin_url( 'themes.php' )
		);

		$has = false;

		foreach ( $dirs as $dir ) {
			$folder = basename( $dir );

			if ( $folder == get_option( 'stylesheet' ) ) {
				continue;
			}

			$has = true;

			$theme = wp_get_theme( $folder );
			$uri   = $theme->get( 'ThemeURI' );

			if ( str_contains( $uri, 'wordpress.org' ) ) {
				switch_theme( $folder );
				wp_die( $msg, $title, $args );
			}
		}

		if ( $has ) {
			$dir    = array_shift( $dirs );
			$folder = basename( $dir );
			switch_theme( $folder );
			wp_die( $msg, $title, $args );
		}

		unset( $args, $title, $has, $msg );
	}

	unset( $dir, $dirs );
}

unset( $theme, $php_version, $require_version );

/*
 * Load Theme Controller Class.
 */
require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-controller.php';