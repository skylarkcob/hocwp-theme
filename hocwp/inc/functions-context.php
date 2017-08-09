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
	$classes = array_unique( $classes );
	$classes = array_map( 'esc_attr', $classes );

	return $classes;
}

add_filter( 'body_class', 'hocwp_theme_body_class_filter' );

function hocwp_theme_post_class_filter( $classes ) {
	if ( ! is_admin() ) {
		$post      = get_post( get_the_ID() );
		$post_type = get_post_type();
		$custom    = array( 'entry' );
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
		$custom  = array_unique( $custom );
		$custom  = array_map( 'esc_attr', $custom );
		$classes = array_merge( $classes, $custom );
		unset( $custom );
	}

	return $classes;
}

add_filter( 'post_class', 'hocwp_theme_post_class_filter' );