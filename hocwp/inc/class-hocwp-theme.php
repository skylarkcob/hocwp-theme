<?php

class HOCWP_Theme {
	public static function wrap_text( $text, $before, $after ) {
		return $before . $text . $after;
	}

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

	public static function array_merge_recursive( array $array1, array $array2 ) {
		$merged = $array1;

		foreach ( $array2 as $key => &$value ) {
			if ( is_array( $value ) && isset ( $merged [ $key ] ) && is_array( $merged [ $key ] ) ) {
				$merged [ $key ] = self::array_merge_recursive( $merged [ $key ], $value );
			} else {
				$merged [ $key ] = $value;
			}
		}

		return $merged;
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

	public static function attribute_to_array( $attr ) {
		if ( ! empty( $attr ) ) {
			if ( ! is_array( $attr ) ) {
				$x    = (array) new SimpleXMLElement( "<element $attr />" );
				$attr = current( $x );

				return $attr;
			}

			return $attr;
		}

		return array();
	}

	public static function json_string_to_array( $json_string ) {
		if ( ! is_array( $json_string ) ) {
			$json_string = stripslashes( $json_string );
			$json_string = json_decode( $json_string, true );
		}
		$json_string = (array) $json_string;

		return $json_string;
	}

	public static function javascript_datetime_format( $php_format ) {
		$matched_symbols = array(
			'd' => 'dd',
			'D' => 'D',
			'j' => 'd',
			'l' => 'DD',
			'N' => '',
			'S' => '',
			'w' => '',
			'z' => 'o',
			'W' => '',
			'F' => 'MM',
			'm' => 'mm',
			'M' => 'M',
			'n' => 'm',
			't' => '',
			'L' => '',
			'o' => '',
			'Y' => 'yy',
			'y' => 'y',
			'a' => '',
			'A' => '',
			'B' => '',
			'g' => '',
			'G' => '',
			'h' => '',
			'H' => '',
			'i' => '',
			's' => '',
			'u' => ''
		);

		$result   = '';
		$escaping = false;
		for ( $i = 0; $i < strlen( $php_format ); $i ++ ) {
			$char = $php_format[ $i ];
			if ( isset( $matched_symbols[ $char ] ) ) {
				$result .= $matched_symbols[ $char ];
			} else {
				$result .= $char;
			}
		}
		if ( $escaping ) {
			$result = esc_attr( $result );
		}

		return $result;
	}

	public static function is_IP( $IP ) {
		return filter_var( $IP, FILTER_VALIDATE_IP );
	}

	public static function get_IP() {
		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) && self::is_IP( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			return $_SERVER['HTTP_CLIENT_IP'];
		}
		if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && self::is_IP( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}

		return isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
	}

	public static function url_exists( $url ) {
		if ( ! class_exists( 'WP_Filesystem_Direct' ) ) {
			require ABSPATH . 'wp-includes/class-wp-error.php';
			require ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
			require ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
		}
		$file = new WP_Filesystem_Direct( null );
		if ( empty( $file->get_contents( $url ) ) ) {
			return false;
		}

		return true;
	}

	public static function is_image_url( $url ) {
		$img_formats = array( 'png', 'jpg', 'jpeg', 'gif', 'tiff', 'bmp', 'ico' );
		$path_info   = pathinfo( $url );
		$extension   = isset( $path_info['extension'] ) ? $path_info['extension'] : '';
		$extension   = trim( strtolower( $extension ) );
		if ( in_array( $extension, $img_formats ) ) {
			return true;
		}

		return false;
	}

	public static function is_image( $url, $id = 0 ) {
		$result = false;
		if ( self::is_positive_number( $id ) ) {
			$result = wp_attachment_is_image( $id );
		}
		if ( ! $result && ! empty( $url ) ) {
			$result = self::is_image_url( $url );
		}

		return $result;
	}

	public static function get_first_image_source( $content ) {
		$doc = new DOMDocument();
		@$doc->loadHTML( $content );
		$xpath = new DOMXPath( $doc );
		$src   = $xpath->evaluate( 'string(//img/@src)' );
		unset( $doc, $xpath );

		return $src;
	}

	public static function get_all_image_from_string( $data, $output = 'img' ) {
		$output = trim( $output );
		preg_match_all( '/<img[^>]+>/i', $data, $matches );
		$matches = isset( $matches[0] ) ? $matches[0] : array();
		if ( ! self::array_has_value( $matches ) && ! empty( $data ) ) {
			if ( false !== strpos( $data, '//' ) && ( false !== strpos( $data, '.jpg' ) || false !== strpos( $data, '.png' ) || false !== strpos( $data, '.gif' ) ) ) {
				$sources = explode( PHP_EOL, $data );
				if ( self::array_has_value( $sources ) ) {
					foreach ( $sources as $src ) {
						if ( self::is_image_url( $src ) ) {
							if ( 'img' == $output ) {
								$matches[] = '<img src="' . $src . '" alt="">';
							} else {
								$matches[] = $src;
							}
						}
					}

				}
			}
		} elseif ( 'img' != $output && self::array_has_value( $matches ) ) {
			$tmp = array();
			foreach ( $matches as $img ) {
				$src   = self::get_first_image_source( $img );
				$tmp[] = $img;
			}
			$matches = $tmp;
		}

		return $matches;
	}

	public static function get_domain_name( $url, $root = false ) {
		if ( ! is_string( $url ) || empty( $url ) ) {
			return '';
		}
		if ( false === strpos( $url, 'http://' ) && false === strpos( $url, 'https://' ) ) {
			$url = 'http://' . $url;
		}
		$url    = strval( $url );
		$parse  = parse_url( $url );
		$result = isset( $parse['host'] ) ? $parse['host'] : '';
		if ( $root && ! self::is_IP( $result ) ) {
			$tmp = explode( '.', $result );
			while ( count( $tmp ) > 2 ) {
				array_shift( $tmp );
			}
			$result = implode( '.', $tmp );
		}

		return $result;
	}

	public static function transmit( &$value, &$another ) {
		if ( $value != $another ) {
			if ( empty( $value ) && ! empty( $another ) ) {
				$value = $another;
			} elseif ( empty( $another ) && ! empty( $value ) ) {
				$another = $value;
			}
		}
	}
}