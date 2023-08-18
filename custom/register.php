<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Register sidebar and widget for using in theme.
 */
if ( ! function_exists( 'hocwp_theme_custom_widgets_init' ) ) {
	function hocwp_theme_custom_widgets_init() {

	}
}

add_action( 'widgets_init', 'hocwp_theme_custom_widgets_init' );