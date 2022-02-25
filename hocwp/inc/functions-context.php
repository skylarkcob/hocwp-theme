<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_body_class_filter( $classes ) {
	global $post;
	$post_type = isset( $post ) ? $post->post_type : false;

	// Check whether we're singular.
	if ( is_singular() ) {
		$classes[] = 'singular';
	}

	// Check whether the current page should have an overlay header.
	if ( is_page_template( array( 'custom/page-templates/template-cover.php' ) ) ) {
		$classes[] = 'overlay-header';
	}

	// Check whether the current page has full-width content.
	if ( is_page_template( array( 'custom/page-templates/template-full-width.php' ) ) || is_page_template( array( 'custom/page-templates/full-width.php' ) ) ) {
		$classes[] = 'has-full-width-content';
		$classes[] = 'full-width';
	}

	// Check for post thumbnail.
	if ( is_singular() && has_post_thumbnail() ) {
		$classes[] = 'has-post-thumbnail';
	} elseif ( is_singular() ) {
		$classes[] = 'missing-post-thumbnail';
	}

	// Check whether we're in the customizer preview.
	if ( is_customize_preview() ) {
		$classes[] = 'customizer-preview';
	}

	// Check if posts have single pagination.
	if ( is_single() && ( get_next_post() || get_previous_post() ) ) {
		$classes[] = 'has-single-pagination';
	} else {
		$classes[] = 'has-no-pagination';
	}

	// Check if we're showing comments.
	if ( $post && ( ( 'post' === $post_type || comments_open() || get_comments_number() ) && ! post_password_required() ) ) {
		$classes[] = 'showing-comments';
	} else {
		$classes[] = 'not-showing-comments';
	}

	// Check if avatars are visible.
	$classes[] = get_option( 'show_avatars' ) ? 'show-avatars' : 'hide-avatars';

	// Slim page template class names (class = name - file suffix).
	if ( is_page_template() ) {
		$template_slug = get_page_template_slug();

		$tmp_class = 'page-template-' . sanitize_html_class( str_replace( '.', '-', $template_slug ) );
		unset( $classes[ array_search( $tmp_class, $classes ) ] );
		$tmp_class = str_replace( '.', '-', $template_slug );
		$tmp_class = str_replace( '/', '-', $tmp_class );
		$tmp_class = 'page-template-' . $tmp_class;
		$classes[] = sanitize_html_class( $tmp_class );

		$classes[] = basename( $template_slug, '.php' );

		unset( $template_slug, $tmp_class );

		$tmp = get_post_meta( get_the_ID(), '_wp_page_template', true );

		if ( $tmp ) {
			$tmp = trailingslashit( get_template_directory() ) . $tmp;

			if ( file_exists( $tmp ) ) {
				$tmp = get_file_data( $tmp, array( 'name' => 'Template Name' ) );

				if ( isset( $tmp['name'] ) && ! empty( $tmp['name'] ) ) {
					$tmp = sanitize_title( $tmp['name'] );
					$tmp = strtolower( $tmp );

					if ( ! empty( $tmp ) ) {
						$classes[] = 'page-' . $tmp;
					}
				}
			}
		}

		unset( $tmp );
	}

	$classes[] = 'hocwp-theme';

	$theme = wp_get_theme();

	if ( defined( 'HOCWP_THEME_NAME' ) ) {
		$classes[] = sanitize_html_class( 'theme-' . HOCWP_THEME_NAME );
	} else {
		if ( $theme instanceof WP_Theme ) {
			$classes[] = sanitize_html_class( 'theme-' . $theme->get( 'Name' ) );
		}
	}

	$classes[] = sanitize_file_name( 'theme-version-' . str_replace( '.', '-', $theme->get( 'Version' ) ) );
	$classes[] = sanitize_file_name( 'theme-core-version-' . str_replace( '.', '-', HOCWP_THEME_CORE_VERSION ) );

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

		$tmp = HT_Frontend()->is_full_width();

		if ( 1 == $tmp ) {
			$classes[] = 'full-width';
			$classes[] = 'has-full-width-content';
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
	$classes = array_map( 'sanitize_html_class', $classes );

	return $classes;
}

add_filter( 'body_class', 'hocwp_theme_body_class_filter' );

function hocwp_theme_post_class_filter( $classes, $class, $post_id ) {
	if ( ! is_admin() || ( defined( 'HOCWP_THEME_LOAD_FRONTEND' ) && HOCWP_THEME_LOAD_FRONTEND ) || HOCWP_THEME_DOING_AJAX ) {
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

/**
 * Open HTML tag.
 *
 * @param string $tag The HTML tag name.
 * @param string $context The HTML tag context.
 * @param string|array $attr The HTML attributes.
 */
function hocwp_theme_html_tag( $tag, $context = '', $attr = '' ) {
	$tag = trim( $tag );
	$tag = strtolower( $tag );

	$atts = hocwp_theme_html_tag_attribute( $tag, $context, $attr, false );

	if ( ! empty( $atts ) ) {
		$tag .= ' ' . $atts;
	}

	printf( "<%s>\n", $tag );
}

/**
 * Close HTML tag.
 *
 * @param string $tag The HTML tag name.
 */
function hocwp_theme_html_tag_close( $tag ) {
	$tag = trim( $tag );
	$tag = strtolower( $tag );

	printf( "</%s>\n", $tag );
}

/**
 * Get HTML tag attributes string.
 *
 * @param string $tag The HTML tag name.
 * @param string $context The HTML tag description.
 * @param string|array $attr Additional attributes.
 * @param bool|true $echo Echo attributes.
 *
 * @return string
 */
function hocwp_theme_html_tag_attribute( $tag, $context = '', $attr = '', $echo = true ) {
	if ( ! empty( $attr ) && ! is_array( $attr ) ) {
		$attr = HT()->attribute_to_array( $attr );
	}

	if ( ! is_array( $attr ) ) {
		$attr = array();
	}

	if ( ! empty( $context ) ) {
		$attr['data_context'] = esc_attr( $context );
	}

	$attributes = apply_filters( 'hocwp_theme_html_tag_with_context_attributes', $attr, $tag, $context );

	if ( is_array( $attributes ) ) {
		$atts = $attributes;

		$attributes = '';

		foreach ( $atts as $att => $attribute ) {
			if ( is_array( $attribute ) ) {
				if ( 'class' == $att ) {
					$attribute = join( ' ', $attribute );
				}
			}

			$sub = substr( $att, 0, 5 );

			if ( 'data_' == $sub ) {
				$att = ltrim( $att, 'data_' );
				$att = 'data-' . $att;
			}

			$attributes .= sprintf( '%s="%s" ', $att, esc_attr( $attribute ) );
		}

		unset( $atts );
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

			if ( HT()->is_positive_number( $screen_width ) ) {
				$atts['data-screen-width'] = $screen_width;
			}

			break;
		case 'body':
			$atts['data-mobile-width'] = hocwp_theme_mobile_menu_media_screen_width();

			$atts['class'] = join( ' ', get_body_class() );

			$atts['data-theme-core-version'] = HOCWP_THEME_CORE_VERSION;

			$browser = HT_Util()->get_browser();

			if ( isset( $browser['name'] ) && ! empty( $browser['name'] ) ) {
				$atts['data-browser'] = $browser['name'];

				if ( isset( $browser['short_name'] ) ) {
					$atts['data-browser-name'] = strtolower( $browser['short_name'] );
				}

				if ( isset( $browser['version'] ) ) {
					$atts['data-browser-version'] = $browser['version'];
				}
			}

			$atts['data-platform'] = $browser['platform'] ?? '';

			$atts['data-is-mobile'] = HT()->bool_to_int( wp_is_mobile() );

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

	$atts = array_unique( $atts );

	return $atts;
}

add_filter( 'hocwp_theme_html_tag_with_context_attributes', 'hocwp_theme_html_tag_with_context_attributes', 10, 3 );