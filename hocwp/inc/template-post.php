<?php
function hocwp_theme_article_before() {
	echo '<article ' . 'class="' . join( ' ', get_post_class() ) . '">';
}

add_action( 'hocwp_theme_article_before', 'hocwp_theme_article_before' );

function hocwp_theme_article_after() {
	echo '</article>';
}

add_action( 'hocwp_theme_article_after', 'hocwp_theme_article_after' );

function hocwp_theme_comments_open() {
	return ( ! post_password_required() && ( comments_open() || get_comments_number() ) );
}

function hocwp_theme_comments_popup_link() {
	if ( hocwp_theme_comments_open() ) {
		$class = apply_filters( 'hocwp_theme_comments_popup_link_class', 'comments-link' );
		$class = explode( ' ', $class );
		$class = array_map( 'sanitize_html_class', $class );
		echo '<span class="' . implode( ' ', $class ) . '">';
		comments_popup_link(
			sprintf(
				wp_kses(
					__( '0 comment<span class="screen-reader-text"> on %s</span>', 'hocwp-theme' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				get_the_title()
			)
		);
		echo '</span>';
		unset( $class );
	}
}

function hocwp_theme_excerpt_more_filter( $more ) {
	global $hocwp_theme;
	$options = isset( $hocwp_theme->options['reading'] ) ? $hocwp_theme->options['reading'] : '';
	if ( is_array( $options ) && isset( $options['excerpt_more'] ) && ! empty( $options['excerpt_more'] ) ) {
		$more = $options['excerpt_more'];
		$more = str_replace( '[PERMALINK]', get_the_permalink(), $more );
	}

	return $more;
}

add_filter( 'excerpt_more', 'hocwp_theme_excerpt_more_filter' );

function hocwp_theme_excerpt_length_filter( $length ) {
	global $hocwp_theme;
	$options = isset( $hocwp_theme->options['reading'] ) ? $hocwp_theme->options['reading'] : '';
	if ( is_array( $options ) && isset( $options['excerpt_length'] ) && ! empty( $options['excerpt_length'] ) ) {
		$length = $options['excerpt_length'];
	}

	return $length;
}

add_filter( 'excerpt_length', 'hocwp_theme_excerpt_length_filter' );

function hocwp_theme_the_title() {
	global $hocwp_theme;
	$in_loop = true;
	$query   = isset( $hocwp_theme->loop_data['query'] ) ? $hocwp_theme->loop_data['query'] : null;
	if ( $query instanceof WP_Query ) {
		$in_loop = $query->in_the_loop;
	}
	$is_single = isset( $hocwp_theme->loop_data['is_single'] ) ? $hocwp_theme->loop_data['is_single'] : false;
	if ( $in_loop && ! $is_single ) {
		the_title( '<h2 class="entry-title post-title"><a href="' . get_the_permalink() . '" title="' . get_the_title() . '">', '</a></h2>' );
	} else {
		the_title( '<h1 class="entry-title post-title">', '</h1>' );
	}
}

add_action( 'hocwp_theme_the_title', 'hocwp_theme_the_title' );

function hocwp_theme_post_thumbnail( $size = 'thumbnail', $attr = '' ) {
	if ( has_post_thumbnail() ) {
		the_post_thumbnail( $size, $attr );
	} else {
		do_action( 'hocwp_theme_post_thumbnail_default' );
	}
	do_action( 'hocwp_theme_post_thumbnail' );
}

function hocwp_theme_the_post_thumbnail( $args ) {
	_deprecated_function( __FUNCTION__, '6.1.9', 'hocwp_theme_post_thumbnail' );
	if ( ! isset( $args ) || ! $args ) {
		$args = 'post-thumbnail';
	}
	$attributes = '';
	if ( is_array( $args ) ) {
		$attributes = isset( $args['attributes'] ) ? $args['attributes'] : '';
		unset( $args['attributes'] );
	}
	hocwp_theme_post_thumbnail( $args, $attributes );
}

add_action( 'hocwp_theme_the_post_thumbnail', 'hocwp_theme_the_post_thumbnail' );

function hocwp_theme_post_thumbnail_html_auto_link( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
	if ( empty( $html ) ) {
		do_action( 'hocwp_theme_post_thumbnail_default' );
	} else {
		if ( is_array( $attr ) && isset( $attr['post_link'] ) && (bool) $attr['post_link'] ) {
			$before = sprintf( '<a class="bump-view img-hyperlink" href="%s" title="%s">', get_the_permalink(), get_the_title() );
			$html   = HT()->wrap_text( $html, $before, '</a>' );
		}
	}

	return $html;
}

add_filter( 'post_thumbnail_html', 'hocwp_theme_post_thumbnail_html_auto_link', 10, 5 );

function hocwp_theme_the_content() {
	echo '<div class="entry-content">';
	the_content();
	echo '</div>';
}

add_action( 'hocwp_theme_the_content', 'hocwp_theme_the_content' );

function hocwp_theme_the_excerpt() {
	echo '<div class="entry-summary">';
	the_excerpt();
	echo '</div>';
}

add_action( 'hocwp_theme_the_excerpt', 'hocwp_theme_the_excerpt' );

function hocwp_theme_related_posts( $args ) {
	$defaults  = array();
	$args      = (array) $args;
	$args      = wp_parse_args( $args, $defaults );
	$box_title = isset( $args['box_title'] ) ? $args['box_title'] : '';
	unset( $args['box_title'] );
	$query = HT_Query()->related_posts( $args );
	if ( $query->have_posts() ) {
		if ( ! isset( $box_title ) || empty( $box_title ) ) {
			$box_title = __( 'Related posts', 'hocwp-theme' );
		}
		echo '<div class="related-posts">';
		echo HT()->wrap_text( $box_title, '<h3 class="box-title">', '</h3>' );
		global $hocwp_theme;
		$hocwp_theme->loop_data['template']        = 'related';
		$hocwp_theme->loop_data['pagination_args'] = null;
		$hocwp_theme->loop_data['content_none']    = false;
		do_action( 'hocwp_theme_loop', $query );
		echo '</div>';
	}
}

add_action( 'hocwp_theme_related_posts', 'hocwp_theme_related_posts' );

function hocwp_theme_post_date() {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf( $time_string,
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( 'c' ) ),
		esc_html( get_the_modified_date() )
	);

	$posted_on = sprintf(
		esc_html_x( 'Posted on %s', 'post date', 'hocwp-theme' ),
		'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
	);
	echo '<span class="posted-on">' . $posted_on . '</span>';
}

function hocwp_theme_post_author() {
	$byline = sprintf(
		esc_html_x( 'by %s', 'post author', 'hocwp-theme' ),
		'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
	);
	echo '<span class="byline"> ' . $byline . '</span>';
}