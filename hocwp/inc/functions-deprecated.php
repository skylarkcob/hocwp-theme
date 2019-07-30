<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_attribute( $deprecated ) {
	_deprecated_function( __FUNCTION__, '6.2.3', 'hocwp_theme_html_tag_attribute' );
}

/*
 * Added for backwards compatibility to support WordPress versions prior to 5.2.0.
 *
 * @since 6.6.6
 */
if ( ! function_exists( 'wp_body_open' ) ) {
	/**
	 * Fire the wp_body_open action.
	 *
	 * * See {@see 'wp_body_open'}.
	 *
	 * @since 5.2.0
	 */
	function wp_body_open() {
		/**
		 * Triggered after the opening <body> tag.
		 *
		 * @since 5.2.0
		 */
		do_action( 'wp_body_open' );
	}
}