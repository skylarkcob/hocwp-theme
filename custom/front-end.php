<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Load theme styles and scripts on front-end.
 */
if ( ! function_exists( 'hocwp_theme_custom_enqueue_scripts' ) ) {
	function hocwp_theme_custom_enqueue_scripts() {

	}
}

add_action( 'wp_enqueue_scripts', 'hocwp_theme_custom_enqueue_scripts' );