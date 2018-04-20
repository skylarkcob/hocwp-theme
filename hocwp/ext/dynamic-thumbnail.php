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

	if ( HT_Util()->is_amp() ) {
		return $html;
	}

	$obj = get_post( $post_id );
	$alt = $obj->post_title;

	if ( empty( $size ) || 'post-thumbnail' == $size ) {
		$size = 'thumbnail';
	}

	$sizes = $size;

	if ( is_string( $sizes ) ) {
		$sizes = HT_Util()->get_image_size( $sizes );
	}

	unset( $size );

	$class = 'wp-post-image';
	$attr  = HOCWP_Theme::attribute_to_array( $attr );

	if ( isset( $attr['class'] ) ) {
		$class .= ' ' . $attr['class'];
		$class = trim( $class );
	}

	$lazyload = isset( $attr['lazyload'] ) ? $attr['lazyload'] : false;

	$url = get_post_meta( $post_id, '_thumbnail_url', true );

	if ( ! HOCWP_Theme::is_image_url( $url ) ) {
		$url = HOCWP_Theme::get_first_image_source( $obj->post_content );
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

	if ( $external || HOCWP_Theme::is_image_url( $url ) ) {
		$file_path = '';

		if ( ! $external ) {
			$post_thumbnail_id = HT_Util()->get_attachment_id( $url );

			if ( HT()->is_positive_number( $post_thumbnail_id ) ) {
				$file_path = get_attached_file( $post_thumbnail_id );
			}
		}

		$dirs      = wp_get_upload_dir();
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

		if ( ! file_exists( $file_path ) ) {
			$pos = HT()->string_contain( $url, 'wp-content/uploads' );

			if ( false !== $pos ) {
				$sub       = substr( $url, $pos );
				$file_path = ABSPATH . $sub;

				if ( file_exists( $file_path ) ) {
					$external = false;

					$url = home_url( $sub );
				}
			}

			if ( ! file_exists( $file_path ) ) {
				$src = HOCWP_THEME_CORE_URL . '/ext/thumbnail.php';
				$src = esc_url_raw( $src );

				$params = array(
					'src'     => $url,
					'crop'    => 0,
					'cache'   => isset( $attr['cache'] ) ? $attr['cache'] : 1,
					'quality' => isset( $attr['quality'] ) ? $attr['quality'] : 100
				);

				$udir = $dirs['basedir'];

				$file_path = trailingslashit( $udir ) . 'cache/' . $file_name;
			}
		}

		if ( ! file_exists( $file_path ) ) {
			$src = add_query_arg( 'src', $url, $src );
			HT_Util()->write_all_text( $file_path, HT_Util()->get_contents( $src ) );
		}

		$ext = pathinfo( $file_path, PATHINFO_EXTENSION );

		$new_size = sprintf( '$1-%sx%s.%s', $width, $height, $ext );

		$new_path = preg_replace( '/^(.*)\.' . $ext . '$/', $new_size, $file_path );

		$regen = false;

		if ( HT()->is_file( $file_path, 'readable' ) && ! file_exists( $new_path ) ) {
			$crop    = (bool) $crop;
			$resized = image_make_intermediate_size( $file_path, $width, $height, $crop );

			if ( ! $external && HT()->is_positive_number( $post_thumbnail_id ) && isset( $resized['file'] ) ) {
				$url = wp_get_attachment_url( $post_thumbnail_id );
				$src = dirname( $url );
				$src = trailingslashit( $src );
				$src .= $resized['file'];
			} else {
				$regen = true;
			}
		} elseif ( file_exists( $new_path ) ) {
			$regen = true;
		}

		if ( $regen ) {
			$new_name = basename( $new_path );

			if ( $external ) {
				$src = trailingslashit( $dirs['baseurl'] );

				$src .= 'cache/' . $new_name;
			} else {
				$src = str_replace( $file_name, $new_name, $url );
			}
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

						if ( HT()->string_contain( $tmp, home_url() ) ) {
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