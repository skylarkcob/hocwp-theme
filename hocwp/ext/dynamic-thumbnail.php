<?php
/*
 * Name: Dynamic Thumbnail
 * Description: Auto detect post thumbnail and displaying it dynamically.
 */
$load = apply_filters( 'hocwp_theme_load_extension_dynamic_thumbnail', hocwp_theme_is_extension_active( __FILE__ ) );
if ( ! $load ) {
	return;
}

function hocwp_theme_post_thumbnail_html_filter( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
	if ( ! empty( $html ) ) {
		return $html;
	}

	if ( empty( $size ) || 'post-thumbnail' == $size ) {
		$size = 'thumbnail';
	}

	$sizes = $size;
	$class = 'wp-post-image';
	$attr  = HOCWP_Theme::attribute_to_array( $attr );

	if ( isset( $attr['class'] ) ) {
		$class .= ' ' . $attr['class'];
		$class = trim( $class );
	}

	$has_thumbnail = ( empty( $html ) ) ? false : true;
	$lazyload      = isset( $attr['lazyload'] ) ? $attr['lazyload'] : false;

	if ( ! $has_thumbnail ) {
		$url = get_post_meta( $post_id, '_thumbnail_url', true );

		if ( ! HOCWP_Theme::is_image_url( $url ) ) {
			$obj = get_post( $post_id );
			$url = HOCWP_Theme::get_first_image_source( $obj->post_content );
		}

		if ( HOCWP_Theme::is_image_url( $url ) ) {
			if ( $lazyload ) {
				$html = sprintf( '<img class="%s" data-original="%s" src="%s" alt="" data-src="%s">', $class, $url, HOCWP_Theme_Utility::get_wp_image_url( 'blank.gif' ), $url );
			} else {
				$html = sprintf( '<img class="%s" src="%s" alt="">', $class, $url );
			}
		}
	} else {
		$url = wp_get_attachment_url( $post_thumbnail_id );
	}

	$width  = ( is_array( $sizes ) && isset( $sizes['width'] ) ) ? $sizes['width'] : '';
	$height = isset( $sizes['height'] ) ? $sizes['height'] : '';

	$external = false;

	if ( ! empty( $url ) ) {
		$root_domain = HT()->get_domain_name( $url, true );
		$blog_domain = HT()->get_domain_name( home_url(), true );

		if ( $root_domain != $blog_domain ) {
			$external = true;
		}
	}

	if ( $external && HOCWP_Theme::is_image_url( $url ) ) {
		$src = HOCWP_THEME_CORE_URL . '/ext/thumbnail.php';
		$src = esc_url_raw( $src );

		$file_name = basename( $url );

		if ( isset( $sizes['crop'] ) ) {
			$crop = (bool) $sizes['crop'];
		} else {
			if ( ! isset( $sizes['width'] ) && ! isset( $sizes['height'] ) && isset( $sizes[2] ) ) {
				$crop = (bool) $sizes[2];
			} else {
				$crop = (bool) get_option( 'thumbnail_crop' );
			}
		}

		$params = array(
			'src'     => $url,
			'crop'    => ( $crop ) ? 1 : 0,
			'width'   => $width,
			'height'  => $height,
			'cache'   => isset( $attr['cache'] ) ? $attr['cache'] : 1,
			'quality' => isset( $attr['quality'] ) ? $attr['quality'] : 60
		);

		$dirs = wp_get_upload_dir();
		$udir = $dirs['basedir'];

		$file_path = trailingslashit( $udir ) . 'cache/' . $file_name;

		if ( ! file_exists( $file_path ) ) {
			$src = add_query_arg( 'src', $url, $src );
			HT_Util()->write_all_text( $file_path, HT_Util()->get_contents( $src ) );
		}

		$ext = pathinfo( $file_path, PATHINFO_EXTENSION );

		$new_path = preg_replace( '/^(.*)\.' . $ext . '$/', sprintf( '$1-%sx%s.%s', $width, $height, $ext ), $file_path );

		if ( HT()->is_file( $file_path, 'readable' ) && ! file_exists( $new_path ) ) {
			$crop    = (bool) $crop;
			$resized = image_make_intermediate_size( $file_path, $width, $height, $crop );
		}

		$doc = new DOMDocument();
		@$doc->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );
		$xpath = new DOMXPath( $doc );
		$alt   = $xpath->evaluate( 'string(//img/@alt)' );

		if ( HT()->is_file( $new_path, 'readable' ) ) {
			$new_name = preg_replace( '/^(.*)\.' . $ext . '$/', sprintf( '$1-%sx%s.%s', $width, $height, $ext ), $file_name );

			$src = trailingslashit( $dirs['baseurl'] );

			$src .= 'cache/' . $new_name;
		} else {
			if ( ! is_numeric( $params['width'] ) ) {
				$width = $xpath->evaluate( 'string(//img/@width)' );

				$params['width'] = $width;
			}

			if ( ! is_numeric( $params['height'] ) ) {
				$height = $xpath->evaluate( 'string(//img/@height)' );

				$params['height'] = $height;
			}

			$params = apply_filters( 'hocwp_theme_dynamic_thumbnail_args', $params );
			$src    = add_query_arg( $params, $src );
		}

		if ( $lazyload ) {
			$html = sprintf( '<img class="%s" data-original="%s" src="%s" alt="%s" data-src="%s">', $class, $src, HOCWP_Theme_Utility::get_wp_image_url( 'blank.gif' ), $alt, $src );
		} else {
			$html = sprintf( '<img class="%s" src="%s" alt="%s">', $class, $src, $alt );
		}
	} else {
		$style = '';
		if ( ! empty( $width ) ) {
			$style .= "width:{$width}px;";
		}
		if ( ! empty( $height ) ) {
			$style .= "height:{$height}px;";
		}
		$html = "<span class='no-thumbnail wp-post-image' style='$style'></span>";
	}

	return $html;
}

add_filter( 'post_thumbnail_html', 'hocwp_theme_post_thumbnail_html_filter', 10, 5 );

/*
 * Recheck post has thumbnail
 */
function hocwp_theme_check_post_has_thumbnail( $check, $post_id, $meta_key ) {
	if ( '_thumbnail_id' == $meta_key ) {
		remove_filter( 'get_post_metadata', 'hocwp_theme_check_post_has_thumbnail', 10 );
		$result = get_post_meta( $post_id, $meta_key, true );
		add_filter( 'get_post_metadata', 'hocwp_theme_check_post_has_thumbnail', 10, 3 );
		if ( empty( $result ) ) {
			remove_filter( 'get_post_metadata', 'hocwp_theme_check_post_has_thumbnail', 10 );
			$result = get_post_meta( $post_id, '_thumbnail_url', true );
			add_filter( 'get_post_metadata', 'hocwp_theme_check_post_has_thumbnail', 10, 3 );
			if ( empty( $result ) ) {
				$obj    = get_post( $post_id );
				$images = HOCWP_Theme::get_all_image_from_string( $obj->post_content, 'src' );
				if ( HOCWP_Theme::array_has_value( $images ) ) {
					$src = '';
					foreach ( $images as $image ) {
						$tmp = HOCWP_Theme::get_first_image_source( $image );
						if ( false !== strpos( $tmp, home_url() ) ) {
							$src   = $image;
							$check = - 1;
							break;
						}
					}
					if ( empty( $src ) ) {
						$src   = array_shift( $images );
						$check = - 1;
					}
					$src = HOCWP_Theme::get_first_image_source( $src );
					update_post_meta( $post_id, '_thumbnail_url', $src );
				}
			} else {
				$check = - 1;
			}
		}
	}

	return $check;
}

add_filter( 'get_post_metadata', 'hocwp_theme_check_post_has_thumbnail', 10, 3 );

function hocwp_theme_post_thumbnail_size_filter( $size ) {
	$size = HOCWP_Theme_Utility::get_image_size( $size );

	return $size;
}

add_filter( 'post_thumbnail_size', 'hocwp_theme_post_thumbnail_size_filter' );