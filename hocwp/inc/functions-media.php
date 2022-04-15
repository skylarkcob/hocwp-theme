<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Check media file exists by ID.
 *
 * @param int $id Media file ID.
 *
 * @return bool
 */
function hocwp_theme_media_file_exists( $id ) {
	if ( HT()->is_positive_number( $id ) && HT()->is_file( get_attached_file( $id ) ) ) {
		return true;
	}

	return false;
}

function hocwp_theme_is_image( $url, $id = 0 ) {
	if ( HT()->is_positive_number( $id ) ) {
		return wp_attachment_is_image( $id );
	}

	return hocwp_theme_is_image_url( $url );
}

function hocwp_theme_is_image_url( $url ) {
	return HT()->is_image_url( $url );
}

function hocwp_theme_attachment_path_to_postid( $path ) {
	global $wpdb;

	$upload = wp_upload_dir();

	$path = str_replace( $upload['basedir'], '', $path );
	$sql  = 'SELECT post_id FROM ';
	$sql  .= $wpdb->postmeta;
	$sql  .= " WHERE meta_key = '_wp_attached_file' AND meta_value = %s";
	$sql  = $wpdb->prepare( $sql, $path );

	return $wpdb->get_var( $sql );
}

add_filter( 'wp_calculate_image_srcset', '__return_false' );

// Allow upload WEBP image mime type
function hocwp_theme_upload_mimes_filter( $mimes ) {
	$mimes['webp'] = 'image/webp';

	if ( defined( 'HOCWP_THEME_ALLOW_MIME_TYPES' ) && HT()->array_has_value( HOCWP_THEME_ALLOW_MIME_TYPES ) ) {
		foreach ( HOCWP_THEME_ALLOW_MIME_TYPES as $mime => $type ) {
			if ( ! isset( $mimes[ $mime ] ) ) {
				$mimes[ $mime ] = $type;
			}
		}
	}

	return $mimes;
}

// Update WEBP image file type
function hocwp_theme_wp_check_filetype_and_ext_filter( $types, $file ) {
	if ( HT_Media()->is_webp_image( $file ) ) {
		$types['ext']  = 'webp';
		$types['type'] = 'image/webp';
	}

	return $types;
}

add_filter( 'wp_check_filetype_and_ext', 'hocwp_theme_wp_check_filetype_and_ext_filter', 10, 2 );

// Mark WEBP image as real image
function hocwp_theme_file_is_displayable_image_filter( $result, $path ) {
	if ( ! $result && HT_Media()->is_webp_image( $path ) ) {
		$result = true;
	}

	return $result;
}

add_filter( 'file_is_displayable_image', 'hocwp_theme_file_is_displayable_image_filter', 10, 2 );

// Re-update WEBP image metadata
function hocwp_theme_wp_get_attachment_metadata_filter( $data, $media_id ) {
	$path = get_attached_file( $media_id );

	if ( HT_Media()->is_webp_image( $path ) ) {
		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$data = wp_generate_attachment_metadata( $media_id, $path );
		wp_update_attachment_metadata( $media_id, $data );
	}

	$sizes = $data['sizes'] ?? '';

	if ( HT()->array_has_value( $sizes ) ) {
		foreach ( (array) $sizes as $key => $tmp ) {
			if ( empty( $tmp ) ) {
				unset( $sizes[ $key ] );
				unset( $data['sizes'][ $key ] );
			}
		}
	}

	unset( $sizes );

	return $data;
}

add_filter( 'wp_get_attachment_metadata', 'hocwp_theme_wp_get_attachment_metadata_filter', 10, 2 );

class HOCWP_Theme_Media {
	protected static $instance;

	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		if ( self::$instance instanceof self ) {
			return;
		}

		add_filter( 'jpeg_quality', array( $this, 'jpeg_quality_filter' ) );
		add_filter( 'big_image_size_threshold', array( $this, 'big_image_size_threshold_filter' ) );
	}

	public function big_image_size_threshold_filter( $threshold ) {
		$size = HT_Options()->get_tab( 'big_image_size_threshold', 2560, 'media' );

		if ( HT()->is_positive_number( $size ) && $size != $threshold ) {
			$threshold = $size;
		}

		unset( $size );

		return $threshold;
	}

	public function is_webp_image( $file ) {
		if ( is_file( $file ) && HT()->is_image_url( $file ) ) {
			$mime = wp_get_image_mime( $file );

			if ( 'image/webp' == $mime ) {
				return true;
			}
		}

		return false;
	}

	public function jpeg_quality_filter( $quality ) {
		$number = HT_Options()->get_tab( 'jpeg_quality', '', 'media' );

		if ( HT()->is_positive_number( $number ) && $number != $quality ) {
			$number  = min( $number, 100 );
			$number  = max( 1, $number );
			$quality = $number;
		}

		return $quality;
	}

	public function sanitize_value( $value ) {
		if ( is_array( $value ) ) {
			$id  = $value['id'] ?? '';
			$url = $value['url'] ?? '';
		} elseif ( $this->exists( $value ) ) {
			$id  = $value;
			$url = wp_get_attachment_url( $id );
		} else {
			$url = $value;
			$id  = 0;
		}

		return array(
			'id'   => $id,
			'url'  => $url,
			'mime' => get_post_mime_type( $id )
		);
	}

	public function exists( $id ) {
		return hocwp_theme_media_file_exists( $id );
	}

	public function is_image( $url, $id = null ) {
		return hocwp_theme_is_image( $url, $id );
	}

	public function is_image_url( $url ) {
		return hocwp_theme_is_image_url( $url );
	}

	public function path_to_id( $path ) {
		return hocwp_theme_attachment_path_to_postid( $path );
	}

	public function url_to_id( $url ) {
		return attachment_url_to_postid( $url );
	}

	public function convert_image_size_to_name( $size, &$sizes = array() ) {
		$name = '';

		if ( is_array( $size ) ) {
			if ( ! HT()->array_has_value( $sizes ) ) {
				$sizes = HT_Util()->get_image_sizes();
			}

			if ( HT()->array_has_value( $sizes ) ) {
				$w = $size[0] ?? '';

				if ( empty( $w ) ) {
					$w = $size['width'] ?? '';
				}

				$h = $size[1] ?? '';

				if ( empty( $h ) ) {
					$h = $size['height'] ?? '';
				}

				$c = $size['crop'] ?? '';

				foreach ( $sizes as $key => $data ) {
					if ( is_array( $data ) ) {
						$width = $data[0] ?? '';

						if ( empty( $width ) ) {
							$width = $data['width'] ?? '';
						}

						$height = $data[1] ?? '';

						if ( empty( $height ) ) {
							$height = $data['height'] ?? '';
						}

						$crop = $data['crop'] ?? '';

						if ( $width == $w && $height == $h && $c == $crop ) {
							$name = $key;
							break;
						}
					}
				}
			}
		}

		return $name;
	}

	public function download_image( $url, $name = null, $check_exist = false ) {
		if ( empty ( $url ) ) {
			return false;
		}

		if ( $check_exist ) {
			$args = array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'meta_query'     => array(
					array(
						'key'   => 'source_url',
						'value' => $url
					)
				),
				'fields'         => 'ids',
				'posts_per_page' => 1,
				'order'          => 'asc'
			);

			$query = new WP_Query( $args );

			if ( $query->have_posts() ) {
				$ids = $query->get_posts();
				$id  = array_shift( $ids );

				if ( HT_Media()->exists( $id ) ) {
					return $id;
				}
			}
		}

		if ( ! function_exists( 'download_url' ) || ! function_exists( 'media_handle_sideload' ) ) {
			load_template( ABSPATH . 'wp-admin/includes/image.php' );
			load_template( ABSPATH . 'wp-admin/includes/file.php' );
			load_template( ABSPATH . 'wp-admin/includes/media.php' );
		}

		$info = pathinfo( $url );

		$source_name = $info['filename'] ?? '';

		$prefix = 'downloaded-';

		if ( ! empty( $source_name ) ) {
			$prefix .= $source_name;
			$prefix .= '-';
		}

		$file_array = array();

		$file_array['tmp_name'] = download_url( $url );

		if ( empty( $file_array['tmp_name'] ) || is_wp_error( $file_array['tmp_name'] ) ) {
			return false;
		}

		if ( $name ) {
			$file_array['name'] = $name;
		} else {
			preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file_array['tmp_name'], $matches );

			if ( ! empty( $matches ) ) {
				$file_array['name'] = basename( $matches[0] );
			} else {
				$file_array['name'] = uniqid( $prefix ) . '.jpeg';
			}
		}

		$id = media_handle_sideload( $file_array );

		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] );

			return false;
		}

		unset( $file_array );

		update_post_meta( $id, 'source_url', $url );

		return $id;
	}

	public function get_default_image_url( $size = false ) {
		$thumbnail = HT_Options()->get_tab( 'default_thumbnail', '', 'writing' );

		if ( $this->exists( $thumbnail ) ) {
			if ( $size ) {
				$thumbnail = wp_get_attachment_image_url( $thumbnail, $size );
			} else {
				$thumbnail = wp_get_original_image_url( $thumbnail );
			}
		}

		if ( empty( $thumbnail ) ) {
			$thumbnail = HT_Util()->get_my_image_url( 'no-thumbnail.webp' );
		}

		return apply_filters( 'hocwp_theme_default_image_url', $thumbnail );
	}

	public function get_image_size( $size ) {
		return HT_Util()->get_image_size( $size );
	}

	public function convert_size_to_array( $thumbnail_size, $crop = true ) {
		if ( is_array( $thumbnail_size ) ) {
			return $thumbnail_size;
		}

		$size = HT_Media()->get_image_size( $thumbnail_size );

		if ( isset( $size['width'] ) && ! empty( $size['width'] ) && isset( $size['height'] ) && ! empty( $size['height'] ) ) {
			$thumbnail_size = array( $size['width'], $size['height'], $crop );
		}

		return $thumbnail_size;
	}
}

function HT_Media() {
	return HOCWP_Theme_Media::get_instance();
}