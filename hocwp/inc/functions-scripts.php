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
			$src = add_query_arg( array( 'ver' => $GLOBALS['wp_version'] ), $src );
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
	wp_enqueue_style( 'hocwp-theme-default-fixed-style', HOCWP_THEME_CORE_URL . '/css/default-fixed' . HOCWP_THEME_CSS_SUFFIX );
	if ( is_singular() || is_single() || is_page() ) {
		if ( hocwp_theme_comments_open() ) {
			wp_enqueue_style( 'hocwp-theme-comments-style', HOCWP_THEME_CORE_URL . '/css/comments' . HOCWP_THEME_CSS_SUFFIX );
		}
	}
	wp_enqueue_style( 'hocwp-theme-custom-style', get_template_directory_uri() . '/custom/css/custom' . HOCWP_THEME_CSS_SUFFIX );
	wp_enqueue_script( 'hocwp-theme-custom', get_template_directory_uri() . '/custom/js/custom' . HOCWP_THEME_JS_SUFFIX, array(), false, true );
}

add_action( 'wp_enqueue_scripts', 'hocwp_theme_enqueue_scripts_action' );

function hocwp_theme_add_editor_style() {
	add_editor_style( HOCWP_THEME_CORE_URL . '/css/editor' . HOCWP_THEME_CSS_SUFFIX );
}

add_action( 'init', 'hocwp_theme_add_editor_style' );

function hocwp_theme_localize_script_l10n() {
	ob_start();
	HOCWP_Theme_Utility::ajax_overlay();
	$ajax_overlay = ob_get_clean();
	$args         = array(
		'ajax_overlay' => $ajax_overlay,
		'ajax_url'     => admin_url( 'admin-ajax.php' )
	);
	if ( is_admin() ) {
		$args = apply_filters( 'hocwp_theme_localize_script_l10n_admin', $args );
	} else {
		$args = apply_filters( 'hocwp_theme_localize_script_l10n', $args );
	}

	return $args;
}

function hocwp_theme_admin_enqueue_scripts_action() {
	wp_register_style( 'hocwp-theme-admin-style', HOCWP_THEME_CORE_URL . '/css/admin' . HOCWP_THEME_CSS_SUFFIX );
	wp_register_script( 'hocwp-theme-admin', HOCWP_THEME_CORE_URL . '/js/admin' . HOCWP_THEME_JS_SUFFIX, array( 'jquery' ), false, true );
	wp_localize_script( 'hocwp-theme-admin', 'hocwp_theme', hocwp_theme_localize_script_l10n() );
}

add_action( 'admin_enqueue_scripts', 'hocwp_theme_admin_enqueue_scripts_action' );

function hocwp_theme_register_global_scripts() {
	wp_register_style( 'hocwp-theme-ajax-overlay-style', HOCWP_THEME_CORE_URL . '/css/ajax-overlay' . HOCWP_THEME_CSS_SUFFIX );
	wp_register_script( 'hocwp-theme-ajax-button', HOCWP_THEME_CORE_URL . '/js/ajax-button' . HOCWP_THEME_JS_SUFFIX, array(), false, true );
}

add_action( 'admin_enqueue_scripts', 'hocwp_theme_register_global_scripts' );
add_action( 'login_enqueue_scripts', 'hocwp_theme_register_global_scripts' );
add_action( 'wp_enqueue_scripts', 'hocwp_theme_register_global_scripts' );
