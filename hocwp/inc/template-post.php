<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_article_before( $args ) {
	$container = $args['container'] ?? 'article';

	$class = $args['class'] ?? '';
	$class .= ' ' . join( ' ', get_post_class() );
	$class = trim( $class );

	echo '<' . $container . ' class="' . $class . '" data-id="' . get_the_ID() . '">';
}

add_action( 'hocwp_theme_article_before', 'hocwp_theme_article_before' );

function hocwp_theme_article_after( $args ) {
	$container = $args['container'] ?? 'article';
	echo '</' . $container . '>';
}

add_action( 'hocwp_theme_article_after', 'hocwp_theme_article_after' );

function hocwp_theme_comments_open() {
	global $post;

	if ( ! ( $post instanceof WP_Post ) ) {
		return false;
	}

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
	$options = ht_options()->get( 'reading' );

	if ( is_array( $options ) && isset( $options['excerpt_more'] ) && ! empty( $options['excerpt_more'] ) ) {
		$more = $options['excerpt_more'];
		$more = str_replace( '[PERMALINK]', get_the_permalink(), $more );
	}

	return $more;
}

add_filter( 'excerpt_more', 'hocwp_theme_excerpt_more_filter' );

function hocwp_theme_excerpt_length_filter( $length ) {
	global $wp_query;

	if ( isset( $wp_query->query_vars['excerpt_length'] ) ) {
		$length = $wp_query->query_vars['excerpt_length'];

		if ( ht()->is_positive_number( $length ) ) {
			return $length;
		}
	}

	$options = ht_options()->get( 'reading' );

	if ( wp_is_mobile() ) {
		if ( is_array( $options ) && isset( $options['excerpt_length_mobile'] ) && ! empty( $options['excerpt_length_mobile'] ) ) {
			$length = $options['excerpt_length_mobile'];
		}
	} elseif ( is_array( $options ) && isset( $options['excerpt_length'] ) && ! empty( $options['excerpt_length'] ) ) {
		$length = $options['excerpt_length'];
	}

	return $length;
}

add_filter( 'excerpt_length', 'hocwp_theme_excerpt_length_filter' );

function hocwp_theme_the_title( $args = array() ) {
	$in_loop = true;
	$query   = hocwp_theme()->get_loop_data( 'query', null );

	if ( $query instanceof WP_Query ) {
		$in_loop = $query->in_the_loop;
	}

	$is_single = hocwp_theme()->get_loop_data( 'is_single', false );

	$list = hocwp_theme()->get_loop_data( 'list' );

	$args = apply_filters( 'hocwp_theme_the_title_args', $args, hocwp_theme_object( 'loop_data' ) );

	$container_tag = $args['container_tag'] ?? '';

	if ( $list || ( isset( hocwp_theme_object()->loop_data['only_link'] ) && hocwp_theme_object()->loop_data['only_link'] ) ) {
		if ( $list ) {
			the_title( '<li><a href="' . get_the_permalink() . '" title="' . get_the_title() . '" class="title">', '</a></li>' );
		} else {
			the_title( '<a href="' . get_the_permalink() . '" title="' . get_the_title() . '" class="title">', '</a>' );
		}
	} else {
		if ( $in_loop && ! $is_single ) {
			if ( empty( $container_tag ) ) {
				$container_tag = 'h2';
			}

			the_title( '<' . $container_tag . ' class="entry-title post-title"><a href="' . get_the_permalink() . '" title="' . get_the_title() . '" class="title">', '</a></' . $container_tag . '>' );
		} else {
			if ( empty( $container_tag ) ) {
				$container_tag = 'h1';
			}

			the_title( '<' . $container_tag . ' class="entry-title post-title">', '</' . $container_tag . '>' );
		}
	}
}

add_action( 'hocwp_theme_the_title', 'hocwp_theme_the_title' );

function hocwp_theme_post_thumbnail_html( $size = 'thumbnail', $attr = '' ) {
	if ( empty( $size ) ) {
		$size = 'thumbnail';
	}

	if ( has_post_thumbnail() ) {
		the_post_thumbnail( $size, $attr );
	} else {
		do_action( 'hocwp_theme_post_thumbnail_default', $size, $attr );
	}

	do_action( 'hocwp_theme_post_thumbnail' );
}

function hocwp_theme_the_post_thumbnail( $args ) {
	if ( ! isset( $args ) || ! $args ) {
		$args = 'post-thumbnail';
	}

	$attributes = '';

	if ( is_array( $args ) ) {
		$attributes = $args['attributes'] ?? '';
		unset( $args['attributes'] );
	}

	hocwp_theme_post_thumbnail_html( $args, $attributes );
}

function hocwp_theme_the_post_thumbnail_action( $args ) {
	_deprecated_function( __FUNCTION__, '6.1.9', 'hocwp_theme_post_thumbnail_html' );

	hocwp_theme_the_post_thumbnail( $args );
}

function hocwp_theme_post_thumbnail_html_auto_link( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
	if ( empty( $html ) ) {
		ob_start();
		do_action( 'hocwp_theme_post_thumbnail_default', $size, $attr );
		$html = ob_get_clean();
	} else {
		if ( is_array( $attr ) && isset( $attr['post_link'] ) && $attr['post_link'] ) {
			$before = sprintf( '<a class="img-hyperlink post-link" href="%s" title="%s">', get_the_permalink(), get_the_title() );
			$html   = ht()->wrap_text( $html, $before, '</a>' );
		}
	}

	return $html;
}

add_filter( 'post_thumbnail_html', 'hocwp_theme_post_thumbnail_html_auto_link', 10, 5 );

function hocwp_theme_get_default_post_thumbnail( $size = 'thumbnail', $attr = '', $style = '' ) {
	$thumbnail = ht_options()->get_tab( 'default_thumbnail', '', 'writing' );
	$class     = ( is_array( $attr ) && isset( $attr['class'] ) ) ? $attr['class'] : '';

	$class .= ' default-thumbnail no-thumbnail wp-post-image';
	$class = trim( $class );

	if ( ht()->is_positive_number( $thumbnail ) ) {
		$thumbnail = '<img class="' . $class . '" src="' . wp_get_attachment_image_url( $thumbnail, $size ) . '" alt="" style="' . $style . '">';
	} else {
		$thumbnail = '<span class="' . $class . '" style="' . $style . '" ></span>';
	}

	return apply_filters( 'hocwp_theme_default_post_thumbnail', $thumbnail, $size, $attr );
}

function hocwp_theme_post_thumbnail_default( $size, $attr ) {
	if ( ht_util()->is_amp() ) {
		return;
	}

	if ( is_string( $size ) ) {
		$size = ht_util()->get_image_size( $size );
	}

	$width = ( is_array( $size ) && isset( $size['width'] ) ) ? $size['width'] : '';

	if ( ! is_numeric( $width ) ) {
		$width = ( is_array( $size ) && isset( $size[0] ) ) ? $size[0] : '';
	}

	if ( ! is_numeric( $width ) ) {
		$width = get_option( 'thumbnail_size_w' );
	}

	$height = ( is_array( $size ) && isset( $size['height'] ) ) ? $size['height'] : '';

	if ( ! is_numeric( $height ) ) {
		$height = ( is_array( $size ) && isset( $size[1] ) ) ? $size[1] : '';
	}

	if ( ! is_numeric( $height ) ) {
		$height = get_option( 'thumbnail_size_h' );
	}

	if ( empty( $width ) || empty( $height ) ) {
		return;
	}

	$style = sprintf( 'width:%spx;height:%spx', $width, $height );

	$html = hocwp_theme_get_default_post_thumbnail( $size, $attr, $style );

	if ( is_array( $attr ) && isset( $attr['post_link'] ) && $attr['post_link'] ) {
		$before = sprintf( '<a class="img-hyperlink" href="%s" title="%s">', get_the_permalink(), get_the_title() );
		$html   = ht()->wrap_text( $html, $before, '</a>' );
	}

	$html = apply_filters( 'hocwp_theme_post_thumbnail_default_html', $html, $size, $attr );

	echo $html;

	unset( $width, $height, $style, $html );
}

add_action( 'hocwp_theme_post_thumbnail_default', 'hocwp_theme_post_thumbnail_default', 10, 2 );

function hocwp_theme_wp_get_attachment_image_attributes_filter( $attr ) {
	if ( is_array( $attr ) && isset( $attr['post_link'] ) && 1 == $attr['post_link'] ) {
		unset( $attr['post_link'] );
	}

	return $attr;
}

add_filter( 'wp_get_attachment_image_attributes', 'hocwp_theme_wp_get_attachment_image_attributes_filter', 99 );

function hocwp_theme_the_content() {
	echo '<div class="entry-content">';
	the_content();

	wp_link_pages( array(
		'before'      => '<div class="page-links post-pagination"><span class="pages">' . __( 'Pages:', 'hocwp-theme' ) . '</span>',
		'after'       => '</div>',
		'link_before' => '<span class="page-number">',
		'link_after'  => '</span>'
	) );

	echo '</div>';
}

add_action( 'hocwp_theme_the_content', 'hocwp_theme_the_content' );

function hocwp_theme_fix_empty_paragraph_and_new_line_in_post_content( $content ) {
	if ( empty( $content ) && ! is_page_template() ) {
		// Display default content if post has empty content.
		$default = ht_options()->get_tab( 'default_content', '', 'reading' );

		if ( ! empty( $default ) ) {
			$default = do_shortcode( $default );
			$default = wpautop( $default );

			$content = $default;
		}
	} else {
		if ( str_contains( $content, '[' ) ) {
			$data = array(
				'<p>['    => '[',
				']</p>'   => ']',
				']<br />' => ']',
				']<br>'   => ']',
				']<br/>'  => ']'
			);

			$content = strtr( $content, $data );
		}
	}

	// Remove empty paragraph in post content.
	$content = force_balance_tags( $content );

	$content = preg_replace( '#<p>\s*+(<br\s*/*>)?\s*</p>#i', '', $content );

	$code = get_post_meta( get_the_ID(), 'custom_code', true );

	if ( ! empty( $code ) ) {
		$content .= PHP_EOL;
		$content .= '<div class="custom-code">' . PHP_EOL;
		$content .= $code;
		$content .= PHP_EOL;
		$content .= '</div>' . PHP_EOL;
	}

	return $content;
}

add_filter( 'the_content', 'hocwp_theme_fix_empty_paragraph_and_new_line_in_post_content' );

function hocwp_theme_the_excerpt() {
	echo '<div class="entry-summary">';
	the_excerpt();
	echo '</div>';
}

add_action( 'hocwp_theme_the_excerpt', 'hocwp_theme_the_excerpt' );

function hocwp_theme_related_posts( $args ) {
	$defaults = array(
		'posts_per_page' => 6
	);

	$args = (array) $args;
	$args = array_filter( $args );
	$args = wp_parse_args( $args, $defaults );

	$box_title = $args['box_title'] ?? '';

	unset( $args['box_title'] );

	$args  = apply_filters( 'hocwp_theme_related_posts_args', $args );
	$query = ht_query()->related_posts( $args );

	if ( $query->have_posts() ) {
		if ( empty( $box_title ) ) {
			$box_title = __( 'Related posts', 'hocwp-theme' );
		}

		echo '<div class="related-posts">';
		echo ht()->wrap_text( $box_title, '<h3 class="box-title">', '</h3>' );

		hocwp_theme()->add_loop_data( 'template', 'related' );
		hocwp_theme()->add_loop_data( 'pagination_args', false );
		hocwp_theme()->add_loop_data( 'content_none', false );
		hocwp_theme()->add_loop_data( 'only_link', true );
		hocwp_theme()->add_loop_data( 'list', true );
		do_action( 'hocwp_theme_loop', $query );
		echo '</div>';
	}

	ht_util()->display_ads( 'related_posts' );
}

add_action( 'hocwp_theme_related_posts', 'hocwp_theme_related_posts' );

function hocwp_theme_post_date() {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';

	if ( get_the_time( 'G' ) !== get_the_modified_time( 'G' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf( $time_string,
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( DATE_W3C ) ),
		esc_html( get_the_modified_date() )
	);

	$posted_on = sprintf(
		esc_html_x( 'Posted on %s', 'post date', 'hocwp-theme' ),
		'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
	);

	echo '<span class="posted-on">' . $posted_on . '</span>';
}

function hocwp_theme_post_modified_date() {
	$time_string = '<time datetime="%1$s" class="updated">%2$s</time>';

	$time_string = sprintf( $time_string,
		esc_attr( get_the_modified_date( DATE_W3C ) ),
		esc_html( get_the_modified_date() )
	);

	$posted_on = sprintf(
		esc_html_x( 'Last updated on %s', 'post date', 'hocwp-theme' ),
		'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
	);

	echo '<span class="updated-on">' . $posted_on . '</span>';
}

function hocwp_theme_post_author() {
	$byline = sprintf(
		esc_html_x( 'by %s', 'post author', 'hocwp-theme' ),
		'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
	);

	echo '<span class="byline"> ' . $byline . '</span>';
}

function hocwp_theme_image_downsize_filter( $downsize, $id, $size ) {
	if ( ! isset( hocwp_theme_object()->image_downsizes ) ) {
		global $_wp_additional_image_sizes;

		hocwp_theme_object()->image_downsizes = array();

		$sizes = get_intermediate_image_sizes();

		foreach ( $sizes as $size_name ) {
			if ( in_array( $size_name, array( 'thumbnail', 'medium', 'large' ) ) ) {
				hocwp_theme_object()->image_downsizes[ $size_name ]['width']  = get_option( $size_name . '_size_w' );
				hocwp_theme_object()->image_downsizes[ $size_name ]['height'] = get_option( $size_name . '_size_h' );
				hocwp_theme_object()->image_downsizes[ $size_name ]['crop']   = (bool) get_option( $size_name . '_crop' );
			} elseif ( isset( $_wp_additional_image_sizes[ $size_name ] ) ) {
				hocwp_theme_object()->image_downsizes[ $size_name ] = $_wp_additional_image_sizes[ $size_name ];
			}
		}
	}

	$sizes = hocwp_theme_object()->image_downsizes;

	$imagedata = wp_get_attachment_metadata( $id );

	if ( ! is_array( $imagedata ) ) {
		return false;
	}

	$image_path = get_attached_file( $id );

	if ( is_string( $size ) ) {
		if ( empty( $sizes[ $size ] ) ) {
			return false;
		}

		$current_size = $sizes[ $size ];

		$image_size = $imagedata['sizes'][ $size ] ?? '';

		if ( ! empty( $image_size ) ) {
			if ( $current_size['width'] == $image_size['width'] && $current_size['height'] == $image_size['height'] ) {
				return false;
			}

			if ( ! empty( $image_size['width_query'] ) && ! empty( $image_size['height_query'] ) ) {
				if ( $image_size['width_query'] == $current_size['width'] && $image_size['height_query'] == $current_size['height'] ) {
					return false;
				}
			}

		}

		$resized = image_make_intermediate_size( $image_path, $current_size['width'], $current_size['height'], $current_size['crop'] );

		if ( ! $resized ) {
			return false;
		}

		$image_size = $resized;

		$image_size['width_query']  = $current_size['width'];
		$image_size['height_query'] = $current_size['height'];

		$imagedata['sizes'][ $size ] = $image_size;

		wp_update_attachment_metadata( $id, $imagedata );

		$att_url = wp_get_attachment_url( $id );

		return array( dirname( $att_url ) . '/' . $resized['file'], $resized['width'], $resized['height'], true );
	} else if ( is_array( $size ) ) {
		$crop = isset( $size['crop'] ) ? (bool) $size['crop'] : ( array_key_exists( 2, $size ) ? $size[2] : true );

		$new_width = $size['width'] ?? '';

		if ( empty( $new_width ) ) {
			$new_width = $size[0] ?? '';
		}

		$new_height = $size['height'] ?? '';

		if ( empty( $new_height ) ) {
			$new_height = ( $size[1] ?? $new_width );
		}

		if ( ! $crop ) {
			if ( class_exists( 'Jetpack' ) && Jetpack::is_module_active( 'photon' ) ) {
				add_filter( 'jetpack_photon_override_image_downsize', '__return_true' );
				$data = wp_get_attachment_image_src( $id, 'full' );
				remove_filter( 'jetpack_photon_override_image_downsize', '__return_true' );
			} else {
				$data = wp_get_attachment_image_src( $id, 'full' );
			}

			if ( $data ) {
				if ( $data[1] == $data[2] ) {
					$new_width = $new_height = $data[1];
				} elseif ( $data[1] > $data[2] && 0 != $new_width ) {
					$ratio      = $data[1] / $new_width;
					$new_height = round( $data[2] / $ratio );
				} elseif ( 0 != $new_height ) {
					$ratio     = $data[2] / $new_height;
					$new_width = round( $data[1] / $ratio );
				}

				$new_width  = abs( $new_width );
				$new_height = abs( $new_height );
			}
		}

		$ext      = pathinfo( $image_path, PATHINFO_EXTENSION );
		$new_path = preg_replace( '/^(.*)\.' . $ext . '$/', sprintf( '$1-%sx%s.%s', $new_width, $new_height, $ext ), $image_path );
		$att_url  = wp_get_attachment_url( $id );

		if ( file_exists( $new_path ) ) {
			return array( dirname( $att_url ) . '/' . basename( $new_path ), $new_width, $new_height, $crop );
		}

		$resized = image_make_intermediate_size( $image_path, $new_width, $new_height, $crop );

		$imagedata = wp_get_attachment_metadata( $id );

		$imagedata['sizes'][ $new_width . 'x' . $new_height ] = $resized;

		wp_update_attachment_metadata( $id, $imagedata );

		if ( ! $resized ) {
			return false;
		}

		return array( dirname( $att_url ) . '/' . $resized['file'], $resized['width'], $resized['height'], $crop );
	}

	return $downsize;
}

add_filter( 'image_downsize', 'hocwp_theme_image_downsize_filter', 10, 3 );