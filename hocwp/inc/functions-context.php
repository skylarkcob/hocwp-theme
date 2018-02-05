<?php
function hocwp_theme_body_class_filter( $classes ) {
	$classes[] = 'hocwp-theme';
	$classes[] = sanitize_html_class( 'hocwp-theme-core-version-' . HOCWP_THEME_CORE_VERSION );
	if ( defined( 'HOCWP_THEME_NAME' ) ) {
		$classes[] = sanitize_html_class( 'theme-' . HOCWP_THEME_NAME );
	} else {
		$theme = wp_get_theme();
		if ( $theme instanceof WP_Theme ) {
			$classes[] = sanitize_html_class( 'theme-' . $theme->get( 'Name' ) );
		}
	}
	if ( isset( $GLOBALS['is_iphone'] ) && $GLOBALS['is_iphone'] ) {
		$classes[] = 'iphone';
	} elseif ( isset( $GLOBALS['is_opera'] ) && $GLOBALS['is_opera'] ) {
		$classes[] = 'opera';
	} elseif ( isset( $GLOBALS['is_chrome'] ) && $GLOBALS['is_chrome'] ) {
		$classes[] = 'chrome';
	} elseif ( isset( $GLOBALS['is_edge'] ) && $GLOBALS['is_edge'] ) {
		$classes[] = 'edge';
	} elseif ( isset( $GLOBALS['is_safari'] ) && $GLOBALS['is_safari'] ) {
		$classes[] = 'safari';
	} elseif ( isset( $GLOBALS['is_NS4'] ) && $GLOBALS['is_NS4'] ) {
		$classes[] = 'ns4';
	} elseif ( isset( $GLOBALS['is_macIE'] ) && $GLOBALS['is_macIE'] ) {
		$classes[] = 'macie';
	} elseif ( isset( $GLOBALS['is_winIE'] ) && $GLOBALS['is_winIE'] ) {
		$classes[] = 'winie';
	} elseif ( isset( $GLOBALS['is_gecko'] ) && $GLOBALS['is_gecko'] ) {
		$classes[] = 'gecko';
		$classes[] = 'firefox';
	} elseif ( isset( $GLOBALS['is_lynx'] ) && $GLOBALS['is_lynx'] ) {
		$classes[] = 'lynx';
	} elseif ( isset( $GLOBALS['is_IE'] ) && $GLOBALS['is_IE'] ) {
		$classes[] = 'ie';
	}
	if ( wp_is_mobile() ) {
		$classes[] = 'mobile';
	}
	$classes = array_unique( $classes );
	$classes = array_map( 'esc_attr', $classes );

	return $classes;
}

add_filter( 'body_class', 'hocwp_theme_body_class_filter' );

function hocwp_theme_post_class_filter( $classes, $class, $post_id ) {
	if ( ! is_admin() || ( defined( 'HOCWP_THEME_LOAD_FRONTEND' ) && HOCWP_THEME_LOAD_FRONTEND ) ) {
		$post = get_post( get_the_ID() );

		$post_type = get_post_type();
		$custom    = array( 'entry', 'clearfix', 'hocwp-post' );
		$custom[]  = 'author-' . sanitize_html_class( get_the_author_meta( 'user_nicename' ), get_the_author_meta( 'ID' ) );

		if ( post_password_required() ) {
			$custom[] = 'protected';
		}

		if ( post_type_supports( $post_type, 'excerpt' ) && has_excerpt() ) {
			$custom[] = 'has-excerpt';
		}

		if ( ! is_singular() && false !== strpos( $post->post_content, '<!--more' ) ) {
			$custom[] = 'has-more-link';
		}

		if ( false !== strpos( $post->post_content, '<!--nextpage' ) ) {
			$custom[] = 'has-pages';
		}

		$featured = get_post_meta( $post_id, 'featured', true );

		if ( 1 == $featured ) {
			$custom[] = 'featured';
		}

		$custom  = array_unique( $custom );
		$custom  = array_map( 'esc_attr', $custom );
		$classes = array_merge( $classes, $custom );

		unset( $custom );

		if ( defined( 'HOCWP_THEME_SUPPORT_MICROFORMATS' ) && ! HOCWP_THEME_SUPPORT_MICROFORMATS ) {
			$classes = array_diff( $classes, array( 'hentry', 'h-entry' ) );
		}
	}

	return $classes;
}

add_filter( 'post_class', 'hocwp_theme_post_class_filter', 10, 3 );

function hocwp_theme_attribute( $tag, $context = '' ) {
	if ( 'body' == $tag ) {
		$width = hocwp_theme_mobile_menu_media_screen_width();
		echo ' data-mobile-width="' . $width . '"';
	}
}