<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_media_file_exists( $id ) {
	if ( HT()->is_file( get_attached_file( $id ) ) ) {
		return true;
	}

	return false;
}

function hocwp_theme_is_image( $url, $id = 0 ) {
	if ( HOCWP_Theme::is_positive_number( $id ) ) {
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
	$path   = str_replace( $upload['basedir'], '', $path );
	$sql    = 'SELECT post_id FROM ';
	$sql .= $wpdb->postmeta;
	$sql .= " WHERE meta_key = '_wp_attached_file' AND meta_value = %s";
	$sql     = $wpdb->prepare( $sql, $path );
	$post_id = $wpdb->get_var( $sql );

	return $post_id;
}

add_filter( 'wp_calculate_image_srcset', '__return_false' );

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

	public function convert_image_size_to_name( $size, &$sizes = array() ) {
		$name = '';

		if ( is_array( $size ) ) {
			if ( ! HT()->array_has_value( $sizes ) ) {
				$sizes = HT_Util()->get_image_sizes();
			}

			if ( HT()->array_has_value( $sizes ) ) {
				$w = isset( $size[0] ) ? $size[0] : '';

				if ( empty( $w ) ) {
					$w = isset( $size['width'] ) ? $size['width'] : '';
				}

				$h = isset( $size[1] ) ? $size[1] : '';

				if ( empty( $h ) ) {
					$h = isset( $size['height'] ) ? $size['height'] : '';
				}

				$c = isset( $size['crop'] ) ? $size['crop'] : '';

				foreach ( $sizes as $key => $data ) {
					if ( is_array( $data ) ) {
						$width = isset( $data[0] ) ? $data[0] : '';

						if ( empty( $width ) ) {
							$width = isset( $data['width'] ) ? $data['width'] : '';
						}

						$height = isset( $data[1] ) ? $data[1] : '';

						if ( empty( $height ) ) {
							$height = isset( $data['height'] ) ? $data['height'] : '';
						}

						$crop = isset( $data['crop'] ) ? $data['crop'] : '';

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

	public function download_image( $url, $name = null ) {
		if ( ! $url || empty ( $url ) ) {
			return false;
		}

		if ( ! function_exists( 'download_url' ) || ! function_exists( 'media_handle_sideload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
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
				$file_array['name'] = uniqid( 'downloaded-' ) . '.jpeg';
			}
		}

		$id = media_handle_sideload( $file_array, 0 );

		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] );

			return false;
		}

		unset( $file_array );

		return $id;
	}
}

function HT_Media() {
	return HOCWP_Theme_Media::get_instance();
}