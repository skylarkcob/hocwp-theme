<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_body_class_filter( $classes ) {
	$classes[] = 'hocwp-theme';
	$classes[] = sanitize_html_class( 'hocwp-theme-core-version-' . HOCWP_THEME_CORE_VERSION );

	$theme = wp_get_theme();

	if ( defined( 'HOCWP_THEME_NAME' ) ) {
		$classes[] = sanitize_html_class( 'theme-' . HOCWP_THEME_NAME );
	} else {
		if ( $theme instanceof WP_Theme ) {
			$classes[] = sanitize_html_class( 'theme-' . $theme->get( 'Name' ) );
		}
	}

	$classes[] = sanitize_file_name( 'theme-version-' . $theme->get( 'Version' ) );

	unset( $theme );

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

	$sidebar_position = HT_Util()->get_theme_option( 'sidebar_position', '', 'reading' );

	if ( is_single() || is_page() || is_singular() ) {
		$tmp = get_post_meta( get_the_ID(), 'sidebar_position', true );

		if ( ! empty( $tmp ) ) {
			$sidebar_position = $tmp;
		}

		unset( $tmp );
	}

	if ( empty( $sidebar_position ) ) {
		$sidebar_position = 'default';
	}

	$classes[] = 'sidebar-position-' . $sidebar_position;

	unset( $sidebar_position );

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

		if ( ! is_singular() && HT()->string_contain( $post->post_content, '<!--more' ) ) {
			$custom[] = 'has-more-link';
		}

		if ( HT()->string_contain( $post->post_content, '<!--nextpage' ) ) {
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

function hocwp_theme_html_tag( $tag, $context = '', $attr = '' ) {
	$tag = trim( $tag );
	$tag = strtolower( $tag );

	$atts = hocwp_theme_html_tag_attribute( $tag, $context, $attr, false );

	if ( ! empty( $atts ) ) {
		$tag .= ' ' . $atts;
	}

	printf( "<%s>\n", $tag );
}

function hocwp_theme_html_tag_close( $tag ) {
	$tag = trim( $tag );
	$tag = strtolower( $tag );

	printf( "</%s>\n", $tag );
}

function hocwp_theme_html_tag_attribute( $tag, $context = '', $attr = '', $echo = true ) {
	$attributes = apply_filters( 'hocwp_theme_html_tag_with_context_attributes', array(), $tag, $context );

	if ( is_array( $attributes ) ) {
		$atts = $attributes;

		$attributes = '';

		foreach ( $atts as $att => $attribute ) {
			$attributes .= sprintf( '%s="%s" ', $att, $attribute );
		}

		unset( $atts );
	}

	if ( ! empty( $attr ) ) {
		$attributes .= ' ' . $attr;
	}

	$attributes = trim( $attributes );

	if ( $echo ) {
		echo $attributes;
	}

	return $attributes;
}

function hocwp_theme_html_tag_with_context_attributes( $atts, $tag, $context ) {
	$tag = strtolower( $tag );

	$atts = (array) $atts;

	switch ( $tag ) {
		case 'html':
			$client_info = HT_Util()->get_client_info();

			$screen_width = isset( $client_info['screen_width'] ) ? $client_info['screen_width'] : 'unknown';

			$atts['data-screen-width'] = $screen_width;

			break;
		case 'body':
			$atts['data-mobile-width'] = hocwp_theme_mobile_menu_media_screen_width();

			$atts['class'] = join( ' ', get_body_class() );

			if ( HOCWP_THEME_STRUCTURED_DATA ) {
				$atts['itemscope'] = 'itemscope';
				$atts['itemtype']  = 'http://schema.org/WebSite';
			}

			break;
		case 'footer':
			switch ( $context ) {
				case 'site_footer':
					$atts['id']    = 'colophon';
					$atts['class'] = 'site-footer';
					break;
			}

			break;
		case 'div':
			switch ( $context ) {
				case 'site_container':
					$atts['id']    = 'page';
					$atts['class'] = 'site';
					break;
				case 'site_content':
					$atts['id']    = 'content';
					$atts['class'] = 'site-content';
					break;
			}

			break;
	}

	$atts = array_filter( $atts );
	$atts = array_unique( $atts );

	return $atts;
}

add_filter( 'hocwp_theme_html_tag_with_context_attributes', 'hocwp_theme_html_tag_with_context_attributes', 10, 3 );