<?php
function hocwp_theme_style_loader_src_filter( $src, $handle ) {
	if ( false !== strpos( $src, get_template_directory_uri() ) ) {
		if ( false === strpos( $src, '.min.' ) && 'hocwp-theme-style' == $handle ) {
			$src = str_replace( 'style.css', 'custom/css/default' . HOCWP_THEME_CSS_SUFFIX, $src );
		}
	}

	return $src;
}

add_filter( 'style_loader_src', 'hocwp_theme_style_loader_src_filter', 10, 2 );

function hocwp_theme_script_loader_src_filter( $src, $handle ) {
	if ( false !== strpos( $src, get_template_directory_uri() ) ) {
		if ( false === strpos( $src, '.min.' ) ) {
			$src = str_replace( '.js', HOCWP_THEME_JS_SUFFIX, $src );
		}
	}

	return $src;
}

add_filter( 'script_loader_src', 'hocwp_theme_script_loader_src_filter', 10, 2 );

$using = apply_filters( 'hocwp_theme_using_emoji', false );
if ( ! $using ) {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
}