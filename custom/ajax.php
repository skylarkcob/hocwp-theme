<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 * Default AJAX callback function.
 * Use this function name as callback property for data script input.
 */
if ( ! function_exists( 'hocwp_theme_custom_ajax_callback' ) ) {
	function hocwp_theme_custom_ajax_callback() {
		HT_Custom()->ajax_callback();
	}
}

/*
 * Default AJAX Private callback function.
 * Use this function name as callback property for data script input.
 */
if ( ! function_exists( 'hocwp_theme_custom_ajax_private_callback' ) ) {
	function hocwp_theme_custom_ajax_private_callback() {
		HT_Custom()->ajax_private_callback();
	}
}