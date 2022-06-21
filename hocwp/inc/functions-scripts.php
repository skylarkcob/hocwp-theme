<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_style_loader_src_filter( $src, $handle ) {
	if ( defined( 'HOCWP_THEME_BLANK_STYLE' ) && HOCWP_THEME_BLANK_STYLE ) {
		return $src;
	}

	if ( ! is_admin() ) {
		$load = apply_filters( 'hocwp_theme_load_default_style', true );

		if ( $load ) {
			if ( HT()->string_contain( $src, HOCWP_THEME_URL ) ) {
				if ( ! HT()->string_contain( $src, '.min.' ) && 'hocwp-theme-style' == $handle ) {
					$src = HOCWP_THEME_CORE_URL . '/css/default' . HOCWP_THEME_CSS_SUFFIX;
					$src = add_query_arg( array( 'ver' => $GLOBALS['wp_version'] ), $src );
				}
			}
		}
	}

	if ( HT()->string_contain( $src, HOCWP_THEME_URL ) ) {
		if ( ! HT()->string_contain( $src, '.min.' ) ) {
			$src = str_replace( '.css', HOCWP_THEME_CSS_SUFFIX, $src );
		}
	}

	return $src;
}

add_filter( 'style_loader_src', 'hocwp_theme_style_loader_src_filter', 10, 2 );

function hocwp_theme_script_loader_src_filter( $src, $handle ) {
	if ( HT()->string_contain( $src, HOCWP_THEME_URL ) ) {
		if ( ! HT()->string_contain( $src, '.min.' ) ) {
			$src = str_replace( '.js', HOCWP_THEME_JS_SUFFIX, $src );
		}
	}

	return $src;
}

add_filter( 'script_loader_src', 'hocwp_theme_script_loader_src_filter', 10, 2 );

function hocwp_theme_mobile_menu_media_screen_width() {
	$width = defined( 'HOCWP_THEME_MOBILE_SCREEN_WIDTH' ) ? HOCWP_THEME_MOBILE_SCREEN_WIDTH : 980;
	$width = apply_filters( 'hocwp_theme_mobile_menu_media_screen_width', $width );

	if ( 1 > $width ) {
		$width = 999999;
	}

	return $width;
}

function hocwp_theme_load_custom_style_and_script() {
	$fonts = HT_Options()->get_tab( 'fonts', '', 'custom_code' );

	if ( ! empty( $fonts ) ) {
		$fonts = HT()->explode_new_line( $fonts );

		if ( HT()->array_has_value( $fonts ) ) {
			foreach ( $fonts as $url ) {
				$url = esc_url( $url );

				if ( ! empty( $url ) ) {
					if ( str_contains( $url, 'fonts.googleapis.com' ) ) {
						$name = 'google-fonts';
					} else {
						$info = pathinfo( $url );
						$name = $info['filename'] ?? '';

						if ( empty( $name ) ) {
							$domain = HT()->get_domain_name( $url );
							$name   = str_replace( '.', '-', $domain );
						}

						$name = HT()->trim_string( $name, '.min', 'right' );
					}

					if ( ! empty( $name ) ) {
						$name = str_replace( '.', '-', $name );
						$name .= '-';
					}

					$name .= md5( $url ) . '-style';

					wp_enqueue_style( $name, $url );
				}
			}
		}
	}

	$scripts = HT_Options()->get_tab( 'scripts', '', 'custom_code' );

	if ( ! empty( $scripts ) ) {
		$scripts = HT()->explode_new_line( $scripts );

		if ( HT()->array_has_value( $scripts ) ) {
			foreach ( $scripts as $url ) {
				$url = esc_url( $url );

				if ( ! empty( $url ) ) {
					$info = pathinfo( $url );
					$name = $info['filename'] ?? '';

					if ( empty( $name ) ) {
						$domain = HT()->get_domain_name( $url );
						$name   = str_replace( '.', '-', $domain );
					}

					$name = HT()->trim_string( $name, '.min', 'right' );

					if ( ! empty( $name ) ) {
						$name = str_replace( '.', '-', $name );
						$name .= '-';
					}

					$name .= md5( $url );

					wp_enqueue_script( $name, $url, array( 'jquery' ), false, true );
				}
			}
		}
	}
}

add_action( 'wp_enqueue_scripts', 'hocwp_theme_load_custom_style_and_script', 1 );
add_action( 'login_enqueue_scripts', 'hocwp_theme_load_custom_style_and_script', 1 );

/**
 * Load styles and scripts for front-end only.
 */
function hocwp_theme_enqueue_scripts_action() {
	if ( defined( 'HOCWP_THEME_BLANK_STYLE' ) && HOCWP_THEME_BLANK_STYLE ) {
		return;
	}

	global $wp_scripts;

	wp_dequeue_script( 'hocwp-theme-navigation' );

	$load = apply_filters( 'hocwp_theme_load_default_style', true );

	if ( $load ) {
		wp_enqueue_style( 'hocwp-theme-default-fixed-style', HOCWP_THEME_CORE_URL . '/css/default-fixed' . HOCWP_THEME_CSS_SUFFIX, array( 'hocwp-theme-define-style' ) );
	}

	if ( is_singular() || is_single() || is_page() ) {
		if ( hocwp_theme_comments_open() ) {
			wp_enqueue_style( 'hocwp-theme-comments-style', HOCWP_THEME_CORE_URL . '/css/comments' . HOCWP_THEME_CSS_SUFFIX );
		}
	}

	wp_enqueue_style( 'hocwp-theme-socials-style', HOCWP_THEME_CORE_URL . '/css/socials' . HOCWP_THEME_CSS_SUFFIX );

	$load = apply_filters( 'hocwp_theme_load_pagination_style', ! defined( 'HOCWP_PAGINATION_FILE' ) );

	if ( $load ) {
		wp_enqueue_style( 'hocwp-pagination-style', HOCWP_THEME_CORE_URL . '/css/pagination' . HOCWP_THEME_CSS_SUFFIX );
	}

	$width = hocwp_theme_mobile_menu_media_screen_width();
	wp_enqueue_style( 'hocwp-theme-mobile-menu-style', HOCWP_THEME_CORE_URL . '/css/mobile-menu' . HOCWP_THEME_CSS_SUFFIX, array(), false, 'screen and (max-width: ' . $width . 'px)' );

	wp_enqueue_script( 'hocwp-theme-front-end', HOCWP_THEME_CORE_URL . '/js/front-end' . HOCWP_THEME_JS_SUFFIX, array(), false, true );

	$src = HOCWP_Theme()->core_url . '/js/mobile-menu' . HOCWP_THEME_JS_SUFFIX;

	$mobile_loaded = false;

	if ( isset( $wp_scripts->registered['hocwp-theme-navigation'] ) ) {
		$wp_scripts->registered['hocwp-theme-navigation']->src = $src;

		$mobile_loaded = true;
	}

	wp_enqueue_script( 'hocwp-theme-navigation', $src, array(), false, true );

	if ( ! $mobile_loaded ) {
		wp_enqueue_script( 'hocwp-theme-mobile-menu', HOCWP_THEME_CORE_URL . '/js/mobile-menu' . HOCWP_THEME_JS_SUFFIX, array(), false, true );
	}

	wp_register_script( 'hocwp-theme-pagination', HOCWP_THEME_CORE_URL . '/js/pagination' . HOCWP_THEME_JS_SUFFIX, array( 'jquery' ), false, true );

	if ( function_exists( 'hocwp_theme_get_option' ) ) {
		$sticky = HT_Options()->get_tab( 'sticky_last_widget', '', 'reading' );

		if ( 1 == $sticky ) {
			wp_enqueue_script( 'hocwp-theme-sticky-widget', HOCWP_THEME_CORE_URL . '/js/sticky-widget' . HOCWP_THEME_JS_SUFFIX, array(), false, true );
		}
	}

	$detect = apply_filters( 'hocwp_theme_detect_client_info', true );

	if ( ! HT()->is_google_pagespeed() && $detect ) {
		$src = HOCWP_THEME_CORE_URL . '/js/detect-client-info' . HOCWP_THEME_JS_SUFFIX;
		wp_enqueue_script( 'hocwp-theme-detect-client-info', $src, array( 'hocwp-theme' ), false, true );
	}

	$src = HOCWP_THEME_CORE_URL . '/lib/html5shiv/html5shiv' . HOCWP_THEME_JS_SUFFIX;
	wp_enqueue_script( 'html5shiv', $src );
	wp_script_add_data( 'html5shiv', 'conditional', 'lt IE 9' );

	do_action( 'hocwp_theme_frontend_scripts' );

	wp_enqueue_style( 'hocwp-theme-custom-style', HOCWP_THEME_CUSTOM_URL . '/css/custom' . HOCWP_THEME_CSS_SUFFIX );
	wp_enqueue_script( 'hocwp-theme-custom', HOCWP_THEME_CUSTOM_URL . '/js/custom' . HOCWP_THEME_JS_SUFFIX, array(), false, true );
}

function hocwp_theme_add_editor_style() {
	add_editor_style( HOCWP_THEME_CORE_URL . '/css/editor' . HOCWP_THEME_CSS_SUFFIX );
}

add_action( 'init', 'hocwp_theme_add_editor_style' );

function hocwp_theme_localize_script_l10n() {
	ob_start();
	HT_Util()->ajax_overlay();
	$ajax_overlay = ob_get_clean();

	$redirect = '';

	if ( is_singular() ) {
		$redirect = get_the_permalink();
	}

	$args = array(
		'homeUrl'                   => home_url( '/' ),
		'ajaxUrl'                   => HOCWP_Theme()->get_ajax_url(),
		'themeUrl'                  => HOCWP_THEME_URL,
		'themeCustomUrl'            => HOCWP_THEME_CUSTOM_URL,
		'ajaxAction'                => 'hocwp_theme_ajax',
		'ajaxCallback'              => 'hocwp_theme_ajax',
		'customAjaxCallback'        => 'hocwp_theme_custom_ajax_callback',
		'customAjaxPrivateCallback' => 'hocwp_theme_custom_ajax_private_callback',
		'loginUrl'                  => wp_login_url( $redirect ),
		'l10n'                      => array(
			'confirmDeleteMessage'       => __( 'Are you sure you want to delete?', 'hocwp-theme' ),
			'beforeUnloadConfirmMessage' => __( 'Changes you made may not be saved.', 'hocwp-theme' ),
			'themeCreatedBy'             => sprintf( __( 'Theme created by %s', 'hocwp-theme' ), 'HocWP Team - http://hocwp.net' ),
			'save'                       => __( 'Save', 'hocwp-theme' ),
			'saveAlert'                  => __( 'The changes you made will be lost if you navigate away from this page.', 'hocwp-theme' ),
			'waiting'                    => __( 'Waiting...', 'hocwp-theme' )
		),
		'ajaxOverlay'               => $ajax_overlay,
		'nonce'                     => wp_create_nonce( HOCWP_Theme()->get_textdomain() ),
		'loadingGif'                => admin_url( 'images/loading.gif' ),
		'loadMoreButton'            => hocwp_theme_load_more_button(),
		'iconRemove'                => HT_HTML_Field()->icon_remove(),
		'mobileScreenWidth'         => hocwp_theme_mobile_menu_media_screen_width(),
		'is_debug'                  => HT()->bool_to_int( HOCWP_THEME_DEVELOPING )
	);

	if ( is_admin() ) {
		$args = apply_filters( 'hocwp_theme_localize_script_l10n_admin', $args );
	}

	return apply_filters( 'hocwp_theme_localize_script_l10n', $args );
}

function hocwp_theme_load_more_button() {
	ob_start();
	?>
    <a class="load-more-button btn" href="#"><?php _e( 'Load more', 'hocwp-theme' ); ?></a>
	<?php
	$html = ob_get_clean();

	return apply_filters( 'hocwp_theme_load_more_button_html', $html );
}

function hocwp_theme_admin_enqueue_scripts_action() {
	global $pagenow, $post_type;

	$post_type_object = get_post_type_object( $post_type );

	$screen = get_current_screen();

	wp_register_style( 'hocwp-theme-admin-style', HOCWP_THEME_CORE_URL . '/css/admin' . HOCWP_THEME_CSS_SUFFIX );
	$src = HOCWP_THEME_CORE_URL . '/js/admin' . HOCWP_THEME_JS_SUFFIX;
	wp_register_script( 'hocwp-theme-admin', $src, array( 'jquery', 'hocwp-theme' ), false, true );

	if ( 'widgets.php' == $pagenow || 'customize.php' == $pagenow ) {
		HT_Enqueue()->chosen();
		HT_Enqueue()->media_upload();
		HT_Enqueue()->sortable();

		$src = HOCWP_THEME_CORE_URL . '/js/admin-widgets' . HOCWP_THEME_JS_SUFFIX;

		wp_enqueue_script( 'hocwp-theme-admin-widgets', $src, array(
			'jquery',
			'chosen-select',
			'hocwp-theme-autocomplete'
		), false, true );
	} elseif ( HT_Admin()->is_theme_option_page() ) {
		$src = HOCWP_THEME_CORE_URL . '/css/admin-theme-options' . HOCWP_THEME_CSS_SUFFIX;
		wp_enqueue_editor();
		wp_enqueue_style( 'hocwp-theme-options-style', $src );
		wp_enqueue_script( 'hocwp-theme-admin' );
	}

	$load = ( 'widgets.php' == $pagenow || 'customize.php' == $pagenow || 'appearance_page_hocwp_theme' == $screen->id || 'link.php' == $pagenow || 'link-add.php' == $pagenow );

	if ( ! $load ) {
		$load = ( class_exists( 'Mega_Menu' ) && 'nav-menus.php' == $pagenow );
	}

	if ( $load || 'index.php' == $pagenow ) {
		wp_enqueue_style( 'hocwp-theme-admin-style' );
	}

	if ( 'post.php' == $pagenow || 'post-new.php' == $pagenow ) {
		wp_enqueue_script( 'hocwp-theme-quicktags' );
		$src = HOCWP_THEME_CORE_URL . '/js/admin-edit-post' . HOCWP_THEME_JS_SUFFIX;
		wp_enqueue_script( 'hocwp-theme-admin-edit-post', $src, array( 'jquery' ), false, true );
	}

	if ( 'nav-menus.php' == $pagenow ) {
		$src = HOCWP_THEME_CORE_URL . '/js/admin-menus' . HOCWP_THEME_JS_SUFFIX;
		wp_enqueue_script( 'hocwp-theme-admin-menus', $src, array( 'jquery', 'hocwp-theme' ), false, true );
	}

	wp_register_style( 'hocwp-theme-admin-manage-column-style', HOCWP_THEME_CORE_URL . '/css/admin-manage-column' . HOCWP_THEME_CSS_SUFFIX, array(
		'hocwp-theme-admin-style',
		'hocwp-theme-ajax-overlay-style'
	) );

	wp_register_script( 'hocwp-theme-admin-manage-column', HOCWP_THEME_CORE_URL . '/js/admin-manage-column' . HOCWP_THEME_JS_SUFFIX, array(
		'jquery',
		'hocwp-theme',
		'hocwp-theme-ajax-button',
		'hocwp-theme-boolean-meta'
	), false, true );

	if ( 'edit.php' == $pagenow ) {
		global $post_type;

		if ( ! ( 'product' == $post_type && $GLOBALS['hocwp_theme']->is_wc_activated ) ) {
			wp_enqueue_style( 'hocwp-theme-admin-manage-column-style' );
			wp_enqueue_script( 'hocwp-theme-admin-manage-column' );
		}
	}

	$src = HOCWP_THEME_CORE_URL . '/js/code-editor' . HOCWP_THEME_JS_SUFFIX;
	wp_register_script( 'hocwp-theme-code-editor', $src, array( 'jquery', 'code-editor' ), false, true );

	$colors = HT_Util()->get_admin_colors( get_user_option( 'admin_color' ) );

	if ( is_object( $colors ) ) {
		$bg     = end( $colors->colors );
		$border = $colors->colors[2] ?? end( $colors->colors );

		$css = '.hocwp-theme .settings-box .header {
				background: ' . $bg . ';
				border-bottom-color: ' . $border . ';
			}
		';

		wp_add_inline_style( 'hocwp-theme-admin-style', $css );

		unset( $bg, $border, $css );
	}

	if ( 'term.php' == $pagenow ) {
		HT_Enqueue()->code_editor();
	}

	if ( $post_type_object instanceof WP_Post_Type && ! $post_type_object->public ) {
		wp_enqueue_script( 'hocwp-theme-admin' );
	}

	if ( 'themes.php' == $pagenow && 'hocwp_theme_delete_posts' == HT_Admin()->get_plugin_page() ) {
		wp_enqueue_script( 'suggest' );
		HT_Enqueue()->autocomplete();
		wp_enqueue_script( 'hocwp-theme' );
		wp_enqueue_script( 'hocwp-theme-administration-tools', HOCWP_Theme()->core_url . '/js/admin-administration-tools.js', array( 'jquery' ), false, true );
	}

	unset( $colors );
}

function hocwp_theme_localize_script_l10n_media_upload() {
	global $wp_version;

	$l10n = array(
		'wp_version'             => $wp_version,
		'multiple'               => 0,
		'removeImageButton'      => '<p class="hide-if-no-js remove"><a href="javascript:" class="remove-media">' . __( 'Remove %s', 'hocwp-theme' ) . '</a></p>',
		'updateImageDescription' => '<p class="hide-if-no-js howto">' . __( 'Click the %s to edit or update', 'hocwp-theme' ) . '</p>',
		'l10n'                   => array(
			'title'       => __( 'Select %s', 'hocwp-theme' ),
			'buttonText'  => __( 'Choose %s', 'hocwp-theme' ),
			'changeTitle' => __( 'Change %s', 'hocwp-theme' )
		)
	);

	return apply_filters( 'hocwp_theme_localize_script_l10n_media_upload', $l10n );
}

function hocwp_theme_load_google_maps_script( $google_api_key = '' ) {
	if ( empty( $google_api_key ) ) {
		$google_api_key = HT_Options()->get_google_api_key();
	}

	if ( ! empty( $google_api_key ) ) {
		$src = 'https://maps.googleapis.com/maps/api/js';
		$src = add_query_arg( 'key', $google_api_key, $src );

		wp_register_script( 'google-maps', $src, array(), false, true );

		$src = HOCWP_THEME_CORE_URL . '/js/google-maps' . HOCWP_THEME_JS_SUFFIX;

		wp_enqueue_script( 'hocwp-theme-google-maps', $src, array(
			'jquery',
			'google-maps'
		), false, true );
	}
}

function hocwp_theme_frontend_and_backend_scripts() {
	wp_enqueue_script( 'utils' );
	wp_register_script( 'lozad', HOCWP_THEME_CORE_URL . '/lib/lozad/lozad.js', array(), false, true );

	//hocwp_theme_load_google_maps_script();

	wp_register_style( 'chosen-style', HOCWP_THEME_CORE_URL . '/lib/chosen/chosen.min.css' );
	wp_register_script( 'chosen', HOCWP_THEME_CORE_URL . '/lib/chosen/chosen.jquery.min.js', array( 'jquery' ), false, true );
	wp_register_script( 'chosen-select', HOCWP_THEME_CORE_URL . '/js/chosen-select' . HOCWP_THEME_JS_SUFFIX, array( 'chosen' ), false, true );
	wp_register_style( 'hocwp-theme-media-upload-style', HOCWP_THEME_CORE_URL . '/css/media-upload' . HOCWP_THEME_CSS_SUFFIX );
	wp_register_script( 'hocwp-theme-media-upload', HOCWP_THEME_CORE_URL . '/js/media-upload' . HOCWP_THEME_JS_SUFFIX, array( 'jquery' ), false, true );
	$src = HOCWP_THEME_CORE_URL . '/js/autocomplete' . HOCWP_THEME_JS_SUFFIX;

	wp_register_script( 'hocwp-theme-autocomplete', $src, array(
		'jquery',
		'jquery-ui-autocomplete',
		'hocwp-theme'
	), false, true );

	$l10n = hocwp_theme_localize_script_l10n_media_upload();

	wp_localize_script( 'hocwp-theme-media-upload', 'hocwpThemeMediaUpload', $l10n );
	wp_register_style( 'hocwp-theme-sortable-style', HOCWP_THEME_CORE_URL . '/css/sortable' . HOCWP_THEME_CSS_SUFFIX );

	wp_register_script( 'hocwp-theme-sortable', HOCWP_THEME_CORE_URL . '/js/sortable' . HOCWP_THEME_JS_SUFFIX, array(
		'jquery',
		'jquery-ui-sortable'
	), false, true );

	wp_register_script( 'hocwp-theme-relationship-control', HOCWP_THEME_CORE_URL . '/js/relationship-control' . HOCWP_THEME_JS_SUFFIX, array( 'jquery' ), false, true );

	wp_register_script( 'hocwp-theme-datepicker', HOCWP_THEME_CORE_URL . '/js/datepicker' . HOCWP_THEME_JS_SUFFIX, array(
		'jquery',
		'jquery-ui-datepicker'
	), false, true );

	wp_register_script( 'hocwp-theme-color-picker', HOCWP_THEME_CORE_URL . '/js/color-picker' . HOCWP_THEME_JS_SUFFIX, array(
		'jquery',
		'wp-color-picker'
	), false, true );

	wp_register_script( 'hocwp-theme-quicktags', HOCWP_THEME_CORE_URL . '/js/quicktags' . HOCWP_THEME_JS_SUFFIX, array( 'jquery' ), false, true );

	$args = array(
		'description' => array(
			'hr'       => _x( 'Horizontal rule line', 'quicktags description', 'hocwp-theme' ),
			'dl'       => _x( 'HTML Description List Element', 'quicktags description', 'hocwp-theme' ),
			'dt'       => _x( 'HTML Definition Term Element', 'quicktags description', 'hocwp-theme' ),
			'dd'       => _x( 'HTML Description Element', 'quicktags description', 'hocwp-theme' ),
			'tab'      => _x( 'Indentation', 'quicktags description', 'hocwp-theme' ),
			'h2'       => _x( 'Heading 2', 'quicktags description', 'hocwp-theme' ),
			'h3'       => _x( 'Heading 3', 'quicktags description', 'hocwp-theme' ),
			'h4'       => _x( 'Heading 4', 'quicktags description', 'hocwp-theme' ),
			'nextpage' => _x( 'Split the article into multiple pages.', 'quicktags description', 'hocwp-theme' )
		)
	);

	wp_localize_script( 'hocwp-theme-quicktags', 'hocwpThemeQuickTags', $args );

	if ( ! HOCWP_THEME_BLANK_STYLE && is_user_logged_in() && is_admin_bar_showing() ) {
		wp_enqueue_style( 'hocwp-theme-user-logged-in-style', HOCWP_Theme()->core_url . '/css/user-logged-in' . HOCWP_THEME_CSS_SUFFIX );
	}

	do_action( 'hocwp_theme_frontend_and_backend_enqueue_scripts' );
}

function hocwp_theme_register_global_scripts() {
	wp_register_style( 'hocwp-theme-define-style', HOCWP_THEME_CORE_URL . '/css/define' . HOCWP_THEME_CSS_SUFFIX );
	wp_register_script( 'hocwp-theme', HOCWP_THEME_CORE_URL . '/js/core' . HOCWP_THEME_JS_SUFFIX, array(), false, true );
	wp_localize_script( 'hocwp-theme', 'hocwpTheme', hocwp_theme_localize_script_l10n() );
	wp_register_style( 'hocwp-theme-ajax-overlay-style', HOCWP_THEME_CORE_URL . '/css/ajax-overlay' . HOCWP_THEME_CSS_SUFFIX );
	wp_register_script( 'hocwp-theme-ajax-button', HOCWP_THEME_CORE_URL . '/js/ajax-button' . HOCWP_THEME_JS_SUFFIX, array( 'hocwp-theme' ), false, true );

	$src  = HOCWP_THEME_CORE_URL . '/js/update-meta' . HOCWP_THEME_JS_SUFFIX;
	$deps = array( 'jquery', 'hocwp-theme', 'hocwp-theme-ajax-button' );
	wp_register_script( 'hocwp-theme-update-meta', $src, $deps, false, true );

	$src = HOCWP_THEME_CORE_URL . '/js/boolean-meta' . HOCWP_THEME_JS_SUFFIX;

	wp_register_script( 'hocwp-theme-boolean-meta', $src, $deps, false, true );

	$src  = HOCWP_THEME_CORE_URL . '/js/combobox' . HOCWP_THEME_JS_SUFFIX;
	$deps = array( 'jquery-ui-core' );
	wp_register_script( 'hocwp-theme-combobox', $src, $deps, false, true );

	do_action( 'hocwp_theme_global_enqueue_scripts' );
}

if ( is_admin() ) {
	add_action( 'admin_enqueue_scripts', 'hocwp_theme_register_global_scripts', 10 );
	add_action( 'admin_enqueue_scripts', 'hocwp_theme_frontend_and_backend_scripts', 10 );
	add_action( 'admin_enqueue_scripts', 'hocwp_theme_admin_enqueue_scripts_action', 11 );
} else {
	add_action( 'wp_enqueue_scripts', 'hocwp_theme_register_global_scripts', 10 );
	add_action( 'wp_enqueue_scripts', 'hocwp_theme_frontend_and_backend_scripts', 10 );
	add_action( 'wp_enqueue_scripts', 'hocwp_theme_enqueue_scripts_action', 11 );
}

if ( is_customize_preview() ) {
	add_action( 'customize_controls_enqueue_scripts', 'hocwp_theme_register_global_scripts', 10 );
	add_action( 'customize_controls_enqueue_scripts', 'hocwp_theme_frontend_and_backend_scripts', 10 );
}

add_action( 'login_enqueue_scripts', 'hocwp_theme_register_global_scripts', 10 );

function hocwp_theme_script_loader_tag_async_filter( $tag, $handle ) {
	if ( ! is_customize_preview() ) {
		foreach ( array( 'async', 'defer' ) as $attr ) {
			if ( ! wp_scripts()->get_data( $handle, $attr ) ) {
				continue;
			}

			if ( ! preg_match( ":\s$attr(=|>|\s):", $tag ) ) {
				$tag = preg_replace( ':(?=></script>):', " $attr", $tag, 1 );
			}
		}
	}

	return $tag;
}