<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

final class HOCWP_Theme_Sanitize {
	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {
	}

	public static function extension( $file, $extension ) {
		$extension = trim( $extension, '' );
		$extension = trim( $extension, '.' );
		$parts     = pathinfo( $file );
		if ( ! isset( $parts['extension'] ) || $extension != $parts['extension'] ) {
			$file .= '.' . $extension;
		}

		return $file;
	}

	public static function prefix( $string, $prefix, $sep = '-' ) {
		$pre_len = mb_strlen( $prefix );
		$sub     = mb_substr( $string, 0, $pre_len );
		if ( $prefix != $sub ) {
			$string = $prefix . $sep . $string;
		}

		return $string;
	}

	public static function html_class( $classes, $add = '' ) {
		if ( ! is_array( $classes ) ) {
			$classes = explode( ' ', $classes );
		}
		if ( ! empty( $add ) ) {
			if ( is_array( $add ) ) {
				$classes = wp_parse_args( $classes, $add );
			} elseif ( ! in_array( $add, $classes ) ) {
				$classes[] = $add;
			}
		}
		$classes = array_unique( $classes );
		$classes = array_filter( $classes );
		$classes = array_map( 'sanitize_html_class', $classes );

		return implode( ' ', $classes );
	}

	public static function media_url( $url, $media_id ) {
		if ( HOCWP_Theme::is_positive_number( $media_id ) && hocwp_theme_media_file_exists( $media_id ) ) {
			$details = wp_get_attachment_image_src( $media_id, 'full' );
			$url     = isset( $details[0] ) ? $details[0] : '';
		}

		return $url;
	}

	public static function media_value( $value ) {
		$id   = 0;
		$url  = '';
		$icon = '';
		$size = '';

		if ( ! is_array( $value ) ) {
			if ( is_numeric( $value ) ) {
				$id = $value;
			} else {
				$url = $value;
			}
		} else {
			$url = isset( $value['url'] ) ? $value['url'] : '';
			$id  = isset( $value['id'] ) ? $value['id'] : '';
			$id  = absint( $id );
		}

		if ( ! HOCWP_Theme::is_positive_number( $id ) ) {
			$id = attachment_url_to_postid( $url );
		}

		if ( HOCWP_Theme::is_positive_number( $id ) ) {
			$url  = self::media_url( $url, $id );
			$icon = wp_mime_type_icon( $id );
			$size = filesize( get_attached_file( $id ) );
		}

		$result = array(
			'id'          => $id,
			'url'         => $url,
			'type_icon'   => $icon,
			'is_image'    => hocwp_theme_is_image( $url, $id ),
			'size'        => $size,
			'size_format' => size_format( $size, 2 ),
			'mime_type'   => get_post_mime_type( $id )
		);

		return $result;
	}

	public function form_post( $key, $type, $data = null ) {
		if ( null == $data ) {
			$data = $_POST;
		}
		$value = ( is_array( $data ) && isset( $data[ $key ] ) ) ? $data[ $key ] : '';

		return HT_Sanitize()->data( $value, $type );
	}

	public static function data( $value, $type ) {
		if ( ! empty( $value ) && ! empty( $type ) ) {
			switch ( $type ) {
				case 'text':
				case 'string':
					$value = maybe_serialize( $value );
					$value = wp_strip_all_tags( $value );
					break;
				case 'url':
					$value = esc_url_raw( $value );
					break;
				case 'bool':
				case 'boolean':
					$value = ( 1 == $value ) ? 1 : 0;
					break;
				case 'positive_integer':
					$value = absint( $value );
					if ( ! HOCWP_Theme::is_positive_number( $value ) ) {
						$value = '';
					}
					break;
				case 'integer':
					$value = intval( $value );
					break;
				case 'nonnegative_number':
				case 'non_negative_number':
					$value = abs( $value );
					break;
				case 'timestamp':
					$value = HT()->string_to_datetime( $value );
					$value = strtotime( $value );
					break;
			}
		}

		return $value;
	}

	public function size( $size ) {
		if ( is_array( $size ) ) {
			if ( ! isset( $size['width'] ) ) {
				if ( isset( $size[0] ) ) {
					$size['width'] = $size[0];
				} else {
					$size['width'] = 0;
				}
			}
			if ( ! isset( $size['height'] ) ) {
				if ( isset( $size[1] ) ) {
					$size['height'] = $size[1];
				} else {
					$size['height'] = 0;
				}
			}
			$size[0] = $size['width'];
			$size[1] = $size['height'];

			return $size;
		}
		if ( is_numeric( $size ) ) {
			$size = absint( $size );

			return array( $size, $size );
		}

		return false;
	}

	public function html_id( $id ) {
		if ( is_array( $id ) ) {
			$id = implode( '@', $id );
		}
		$id    = strtolower( $id );
		$id    = str_replace( '][', '_', $id );
		$chars = array(
			'-',
			' ',
			'[',
			']',
			'@',
			'.'
		);
		$id    = str_replace( $chars, '_', $id );
		$id    = trim( $id, '_' );

		return $id;
	}

	public function post_type_or_taxonomy_args( $args ) {
		$name = isset( $args['name'] ) ? $args['name'] : '';

		if ( empty( $name ) ) {
			$name = isset( $args['labels']['name'] ) ? $args['labels']['name'] : '';
		}

		$singular_name = isset( $args['singular_name'] ) ? $args['singular_name'] : '';

		if ( empty( $singular_name ) ) {
			$singular_name = isset( $args['labels']['singular_name'] ) ? $args['labels']['singular_name'] : '';
		}

		$menu_name = isset( $args['menu_name'] ) ? $args['menu_name'] : '';

		if ( empty( $menu_name ) ) {
			$menu_name = isset( $args['labels']['menu_name'] ) ? $args['labels']['menu_name'] : '';
		}

		if ( empty( $name ) ) {
			if ( ! empty( $singular_name ) ) {
				$name = $singular_name;
			} elseif ( ! empty( $menu_name ) ) {
				$name = $menu_name;
			}
		}

		if ( empty( $singular_name ) ) {
			$singular_name = $name;
		}

		if ( empty( $menu_name ) ) {
			$menu_name = $name;
		}

		$args['name']          = $name;
		$args['singular_name'] = $singular_name;
		$args['menu_name']     = $menu_name;

		return $args;
	}
}

function HT_Sanitize() {
	return HOCWP_Theme_Sanitize::instance();
}