<?php
$using = apply_filters( 'hocwp_theme_using_emoji', false );
if ( ! $using ) {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
}

function hocwp_theme_style_loader_src_filter( $src, $handle ) {
	if ( false !== strpos( $src, get_template_directory_uri() ) ) {
		if ( false === strpos( $src, '.min.' ) && 'hocwp-theme-style' == $handle ) {
			$src = HOCWP_THEME_CORE_URL . '/css/default' . HOCWP_THEME_CSS_SUFFIX;
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

function hocwp_theme_enqueue_scripts_action() {
	wp_enqueue_style( 'hocwp-theme-default-fixed', HOCWP_THEME_CORE_URL . '/css/default-fixed' . HOCWP_THEME_CSS_SUFFIX );
	if ( is_singular() || is_single() || is_page() ) {
		if ( hocwp_theme_comments_open() ) {
			wp_enqueue_style( 'hocwp-theme-comments', HOCWP_THEME_CORE_URL . '/css/comments' . HOCWP_THEME_CSS_SUFFIX );
		}
	}
	wp_enqueue_style( 'hocwp-theme-custom-style', get_template_directory_uri() . '/custom/css/custom' . HOCWP_THEME_CSS_SUFFIX );
	wp_enqueue_script( 'hocwp-theme-custom', get_template_directory_uri() . '/custom/js/custom' . HOCWP_THEME_JS_SUFFIX, array( 'jquery' ), false, true );
}

add_action( 'wp_enqueue_scripts', 'hocwp_theme_enqueue_scripts_action' );