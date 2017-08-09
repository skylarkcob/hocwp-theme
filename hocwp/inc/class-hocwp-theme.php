<?php

class HOCWP_Theme {
	public static function array_has_value( $arr ) {
		return ( is_array( $arr ) && count( $arr ) > 0 );
	}

	public static function in_array( $needle, $haystack ) {
		if ( ! is_array( $haystack ) || is_array( $needle ) ) {
			return false;
		}
		if ( in_array( $needle, $haystack ) ) {
			return true;
		}
		foreach ( $haystack as $element ) {
			if ( is_array( $element ) && self::in_array( $needle, $element ) ) {
				return true;
			} elseif ( $element == $needle ) {
				return true;
			}
		}

		return false;
	}

	public static function require_if_exists( $file, $require_once = false ) {
		if ( file_exists( $file ) ) {
			if ( $require_once ) {
				require_once $file;
			} else {
				require $file;
			}
		}
	}

	public static function is_positive_number( $number ) {
		return ( is_numeric( $number ) && $number > 0 );
	}

	public static function random_color_hex() {
		$count = 1;
		$part  = '';
		while ( $count <= 3 ) {
			$part .= str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT );
			$count ++;
		}

		return '#' . $part;
	}

	public static function callback_exists( $callback ) {
		if ( empty( $callback ) || ( ! is_array( $callback ) && ! function_exists( $callback ) ) ) {
			return false;
		}
		if ( ( is_array( $callback ) && count( $callback ) != 2 ) ) {
			return false;
		}
		if ( ( is_array( $callback ) && ! method_exists( $callback[0], $callback[1] ) ) ) {
			return false;
		}
		if ( ! is_callable( $callback ) ) {
			return false;
		}

		return true;
	}

	public static function change_html_attribute( $tag, $attr, $value ) {
		$tag = preg_replace( '/' . $attr . '="(.*?)"/i', $attr . '="' . $value . '"', $tag );

		return $tag;
	}

	public static function add_html_attribute( $tag, $html, $attr ) {
		$html = preg_replace( '^' . preg_quote( '<' . $tag . ' ' ) . '^', '<' . $tag . ' ' . $attr . ' ', $html );

		return $html;
	}

	public static function attributes_to_string( $atts ) {
		if ( is_array( $atts ) ) {
			$temp = array();
			foreach ( $atts as $key => $value ) {
				$att    = $key . '="' . $value . '"';
				$temp[] = $att;
			}
			$atts = implode( ' ', $temp );
		}
		if ( ! empty( $atts ) ) {
			$atts = trim( $atts );
		}

		return $atts;
	}

	public static function sanitize_extension( $file, $extension ) {
		$extension = trim( $extension, '' );
		$extension = trim( $extension, '.' );
		$parts     = pathinfo( $file );
		if ( ! isset( $parts['extension'] ) || $extension != $parts['extension'] ) {
			$file .= '.' . $extension;
		}

		return $file;
	}

	public static function sanitize_prefix( $string, $prefix, $sep = '-' ) {
		$pre_len = mb_strlen( $prefix );
		$sub     = mb_substr( $string, 0, $pre_len );
		if ( $prefix != $sub ) {
			$string = $prefix . $sep . $string;
		}

		return $string;
	}

	public static function sanitize_html_class( $classes, $add = '' ) {
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
}