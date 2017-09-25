<?php
$using = apply_filters( 'hocwp_theme_using_emoji', false );
if ( ! $using ) {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
}

function hocwp_theme_style_loader_src_filter( $src, $handle ) {
	$load = apply_filters( 'hocwp_theme_load_default_style', true );
	if ( $load ) {
		if ( false !== strpos( $src, get_template_directory_uri() ) ) {
			if ( false === strpos( $src, '.min.' ) && 'hocwp-theme-style' == $handle ) {
				$src = HOCWP_THEME_CORE_URL . '/css/default' . HOCWP_THEME_CSS_SUFFIX;
				$src = add_query_arg( array( 'ver' => $GLOBALS['wp_version'] ), $src );
			}
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
	$load = apply_filters( 'hocwp_theme_load_default_style', true );
	if ( $load ) {
		wp_enqueue_style( 'hocwp-theme-default-fixed-style', HOCWP_THEME_CORE_URL . '/css/default-fixed' . HOCWP_THEME_CSS_SUFFIX );
	}
	if ( is_singular() || is_single() || is_page() ) {
		if ( hocwp_theme_comments_open() ) {
			wp_enqueue_style( 'hocwp-theme-comments-style', HOCWP_THEME_CORE_URL . '/css/comments' . HOCWP_THEME_CSS_SUFFIX );
		}
	}
	wp_enqueue_style( 'hocwp-theme-custom-style', get_template_directory_uri() . '/custom/css/custom' . HOCWP_THEME_CSS_SUFFIX );
	wp_enqueue_script( 'hocwp-theme-custom', get_template_directory_uri() . '/custom/js/custom' . HOCWP_THEME_JS_SUFFIX, array(), false, true );
}

add_action( 'wp_enqueue_scripts', 'hocwp_theme_enqueue_scripts_action', 11 );

function hocwp_theme_add_editor_style() {
	add_editor_style( HOCWP_THEME_CORE_URL . '/css/editor' . HOCWP_THEME_CSS_SUFFIX );
}

add_action( 'init', 'hocwp_theme_add_editor_style' );

function hocwp_theme_localize_script_l10n() {
	ob_start();
	HOCWP_Theme_Utility::ajax_overlay();
	$ajax_overlay = ob_get_clean();
	$args         = array(
		'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
		'l10n'        => array(
			'confirmDeleteMessage' => __( 'Are you sure you want to delete?', 'hocwp-theme' )
		),
		'ajaxOverlay' => $ajax_overlay
	);
	if ( is_admin() ) {
		$args = apply_filters( 'hocwp_theme_localize_script_l10n_admin', $args );
	} else {
		$args = apply_filters( 'hocwp_theme_localize_script_l10n', $args );
	}

	return $args;
}

function hocwp_theme_admin_enqueue_scripts_action() {
	global $pagenow;
	wp_register_style( 'hocwp-theme-admin-style', HOCWP_THEME_CORE_URL . '/css/admin' . HOCWP_THEME_CSS_SUFFIX );
	wp_register_script( 'hocwp-theme-admin', HOCWP_THEME_CORE_URL . '/js/admin' . HOCWP_THEME_JS_SUFFIX, array(
		'jquery',
		'hocwp-theme'
	), false, true );
	if ( 'post.php' == $pagenow || 'post-new.php' == $pagenow ) {
		wp_enqueue_script( 'hocwp-theme-quicktags' );
	}
}

add_action( 'admin_enqueue_scripts', 'hocwp_theme_admin_enqueue_scripts_action', 11 );

function hocwp_theme_localize_script_l10n_media_upload() {
	$l10n = array(
		'multiple'               => 0,
		'removeImageButton'      => '<p class="hide-if-no-js remove"><a href="javascript:" class="remove-media">' . __( 'Remove %s', 'hocwp-theme' ) . '</a></p>',
		'updateImageDescription' => '<p class="hide-if-no-js howto">' . __( 'Click the %s to edit or update', 'hocwp-theme' ) . '</p>',
		'l10n'                   => array(
			'title'      => __( 'Select %s', 'hocwp-theme' ),
			'buttonText' => __( 'Choose %s', 'hocwp-theme' )
		)
	);

	return apply_filters( 'hocwp_theme_localize_script_l10n_media_upload', $l10n );
}

function hocwp_theme_frontend_and_backend_scripts() {
	wp_register_style( 'hocwp-theme-media-upload-style', HOCWP_THEME_CORE_URL . '/css/media-upload' . HOCWP_THEME_CSS_SUFFIX );
	wp_register_script( 'hocwp-theme-media-upload', HOCWP_THEME_CORE_URL . '/js/media-upload' . HOCWP_THEME_JS_SUFFIX, array( 'jquery' ), false, true );
	$l10n = hocwp_theme_localize_script_l10n_media_upload();
	wp_localize_script( 'hocwp-theme-media-upload', 'hocwpThemeMediaUpload', $l10n );
	wp_register_script( 'hocwp-theme-sortable', HOCWP_THEME_CORE_URL . '/js/sortable' . HOCWP_THEME_JS_SUFFIX, array( 'jquery' ), false, true );
	wp_register_script( 'hocwp-theme-relationship-control', HOCWP_THEME_CORE_URL . '/js/relationship-control' . HOCWP_THEME_JS_SUFFIX, array( 'jquery' ), false, true );
	wp_register_script( 'hocwp-theme-datepicker', HOCWP_THEME_CORE_URL . '/js/datepicker' . HOCWP_THEME_JS_SUFFIX, array(
		'jquery',
		'jquery-ui-datepicker'
	), false, true );
	wp_register_script( 'hocwp-theme-quicktags', HOCWP_THEME_CORE_URL . '/js/quicktags' . HOCWP_THEME_JS_SUFFIX, array( 'jquery' ), false, true );
	$args = array(
		'description' => array(
			'hr' => _x( 'Horizontal rule line', 'quicktags description', 'hocwp-theme' ),
			'dl' => _x( 'HTML Description List Element', 'quicktags description', 'hocwp-theme' ),
			'dt' => _x( 'HTML Definition Term Element', 'quicktags description', 'hocwp-theme' ),
			'dd' => _x( 'HTML Description Element', 'quicktags description', 'hocwp-theme' )
		)
	);
	wp_localize_script( 'hocwp-theme-quicktags', 'hocwpThemeQuickTags', $args );
	do_action( 'hocwp_theme_frontend_and_backend_enqueue_scripts' );
}

add_action( 'wp_enqueue_scripts', 'hocwp_theme_frontend_and_backend_scripts', 10 );
add_action( 'admin_enqueue_scripts', 'hocwp_theme_frontend_and_backend_scripts', 10 );

function hocwp_theme_register_global_scripts() {
	wp_register_script( 'hocwp-theme', HOCWP_THEME_CORE_URL . '/js/core' . HOCWP_THEME_JS_SUFFIX, array(), false, true );
	wp_localize_script( 'hocwp-theme', 'hocwpTheme', hocwp_theme_localize_script_l10n() );
	wp_register_style( 'hocwp-theme-ajax-overlay-style', HOCWP_THEME_CORE_URL . '/css/ajax-overlay' . HOCWP_THEME_CSS_SUFFIX );
	wp_register_script( 'hocwp-theme-ajax-button', HOCWP_THEME_CORE_URL . '/js/ajax-button' . HOCWP_THEME_JS_SUFFIX, array(), false, true );
	do_action( 'hocwp_theme_global_enqueue_scripts' );
}

add_action( 'admin_enqueue_scripts', 'hocwp_theme_register_global_scripts', 10 );
add_action( 'login_enqueue_scripts', 'hocwp_theme_register_global_scripts', 10 );
add_action( 'wp_enqueue_scripts', 'hocwp_theme_register_global_scripts', 10 );
