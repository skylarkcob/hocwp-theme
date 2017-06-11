<?php
function hocwp_theme_body_class_filter( $classes ) {
	$classes[] = 'hocwp-theme';
	$classes[] = sanitize_html_class( 'hocwp-theme-core-version-' . HOCWP_THEME_CORE_VERSION );

	return $classes;
}

add_filter( 'body_class', 'hocwp_theme_body_class_filter' );