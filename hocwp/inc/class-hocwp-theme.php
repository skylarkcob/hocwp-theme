<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

final class HOCWP_Theme {
	public $version = HOCWP_THEME_CORE_VERSION;
	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {

	}

	public function debug( $value ) {
		if ( function_exists( 'hocwp_theme_debug' ) ) {
			hocwp_theme_debug( $value );
		} else {
			if ( is_array( $value ) || is_object( $value ) ) {
				error_log( print_r( $value, true ) );
			} else {
				error_log( $value );
			}
		}
	}

	public static function is_file( $file, $check = 'exists' ) {
		return ( is_file( $file ) && ( ( 'exists' == $check && file_exists( $file ) ) || ( 'readable' == $check && is_readable( $file ) ) || ( ( 'writable' == $check || 'writeable' == $check ) && is_writable( $file ) ) || ( 'executable' == $check && is_executable( $file ) ) ) ) ? true : false;
	}

	public static function is_dir( $dir, $check = 'exists' ) {
		return ( is_dir( $dir ) && ( ( 'exists' == $check && file_exists( $dir ) ) || ( 'readable' == $check && is_readable( $file ) ) ) ) ? true : false;
	}

	public static function wrap_text( $text, $before, $after, $echo = false ) {
		$text = $before . $text . $after;
		if ( $echo ) {
			echo $text;
		}

		return $text;
	}

	public function string_chunk( $string, $size, $delimiter = ' ' ) {
		$size  = absint( $size );
		$parts = explode( $delimiter, $string );

		if ( 1 == $size ) {
			return $parts;
		}

		if ( ( $size - 1 ) < count( $parts ) ) {
			$chunks = array_chunk( $parts, $size );

			foreach ( $chunks as $index => $value ) {
				if ( is_array( $value ) ) {
					$chunks[ $index ] = implode( $delimiter, $value );
				}
			}

			return $chunks;
		}

		return (array) $string;
	}

	public function string_concatenate( $string, $open = '', $close = '' ) {
		$string = $open . $string;
		$string .= $close;

		return $string;
	}

	public function get_email_from_string( $string ) {
		preg_match_all( "/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $string, $matches );

		return $matches[0];
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

	private function _insert_to_array_helper( $array, $item, $key = '' ) {
		if ( empty( $key ) ) {
			$array[] = $item;
		} else {
			$array[ $key ] = $item;
		}

		return $array;
	}

	public function insert_to_array( $array, $item, $index, $key = '' ) {
		if ( is_array( $array ) ) {
			$count = count( $array );

			if ( is_numeric( $index ) ) {
				$index = absint( $index );
			}

			if ( 'head' == $index ) {
				$index = 1;
			} elseif ( 'after_head' == $index ) {
				$index = 2;
			} elseif ( 'tail' == $index ) {
				$index = $count + 1;
			} elseif ( 'before_tail' == $index ) {
				$index = $count;
			} elseif ( 'rand' == $index || 'random' == $index ) {
				$index = rand( 1, $count );
			}

			if ( is_numeric( $index ) ) {
				if ( $index > $count ) {
					$array = $this->_insert_to_array_helper( $array, $item, $key );
				} else {
					$count = $j = 0;
					$tmp   = array();

					foreach ( $array as $i => $value ) {
						if ( $count == ( $index - 1 ) ) {
							if ( is_numeric( $key ) ) {
								$tmp[] = $item;
							} else {
								$tmp[ $key ] = $item;
							}
						}

						if ( is_numeric( $i ) ) {
							$tmp[] = $value;
						} else {
							$tmp[ $i ] = $value;
						}

						$count ++;
					}

					$array = $tmp;
				}
			}

			unset( $tmp, $count );
		}

		return $array;
	}

	public function is_string_empty( $string ) {
		return ( is_string( $string ) && empty( $string ) ) ? true : false;
	}

	public function get_value_in_array( $arr, $key, $default = '' ) {
		if ( ! is_array( $arr ) || is_object( $key ) || is_object( $arr ) || $this->is_string_empty( $key ) ) {
			return $default;
		}
		$has_key = false;

		$result = '';
		if ( HT()->array_has_value( $arr ) ) {
			if ( is_array( $key ) ) {
				if ( count( $key ) == 1 ) {
					$key = array_shift( $key );
					if ( isset( $arr[ $key ] ) ) {
						return $arr[ $key ];
					}
				} else {
					$tmp = $arr;
					if ( is_array( $tmp ) ) {
						$has_value = false;
						$level     = 0;
						foreach ( $key as $index => $child_key ) {
							if ( is_array( $child_key ) ) {
								if ( count( $child_key ) == 1 ) {
									$child_key = array_shift( $child_key );
								}
								$result = $this->get_value_in_array( $tmp, $child_key );
							} else {
								if ( isset( $tmp[ $child_key ] ) ) {
									$tmp       = $tmp[ $child_key ];
									$has_value = true;
									$level ++;
									$has_key = true;
								}
							}
						}
						if ( ! $has_value ) {
							reset( $key );
							$first_key = current( $key );
							if ( HT()->array_has_value( $arr ) ) {
								$tmp = $this->get_value_in_array( $arr, $first_key );
								if ( HT()->array_has_value( $tmp ) ) {
									$result = $this->get_value_in_array( $tmp, $key );
								}
							}
						}
						if ( $has_value && $this->is_string_empty( $result ) ) {
							$result = $tmp;
						}
					}
				}
			} else {
				if ( isset( $arr[ $key ] ) ) {
					$result  = $arr[ $key ];
					$has_key = true;
				} else {
					foreach ( $arr as $index => $value ) {
						if ( is_array( $value ) ) {
							$result = $this->get_value_in_array( $value, $key );
						} else {
							if ( $key === $index ) {
								$has_key = true;
								$result  = $value;
							}
						}
					}
				}
			}
		}
		if ( ! $has_key ) {
			$result = $default;
		}

		return $result;
	}

	public function get_method_value( $key, $method = 'post', $default = '' ) {
		$method = strtoupper( $method );
		switch ( $method ) {
			case 'POST':
				$result = $this->get_value_in_array( $_POST, $key, $default );
				break;
			case 'GET':
				$result = $this->get_value_in_array( $_GET, $key, $default );
				break;
			default:
				$result = $this->get_value_in_array( $_REQUEST, $key, $default );
		}

		return $result;
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
		if ( self::is_file( $file ) ) {
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

	public function is_nonnegative_number( $number ) {
		return ( is_numeric( $number ) && $number >= 0 );
	}

	public static function convert_to_boolean( $value ) {
		if ( is_numeric( $value ) ) {
			if ( 0 == $value ) {
				return false;
			}

			return true;
		}
		if ( is_string( $value ) ) {
			if ( 'false' == strtolower( $value ) ) {
				return false;
			}

			return true;
		}

		return (bool) $value;
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

	public function keep_only_number( $string, $keep = ',' ) {
		if ( is_numeric( $string ) ) {
			return $string;
		}

		$tmp = preg_replace( '/[^0-9\.' . $keep . ']/', '', $string );

		if ( null != $tmp && false != $tmp ) {
			return $tmp;
		}

		return '';
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

	public function string_to_datetime( $string, $format = '' ) {
		if ( empty( $format ) ) {
			$format = 'Y-m-d H:i:s';
		}

		$string = str_replace( '/', '-', $string );
		$string = trim( $string );

		$result = date( $format, strtotime( $string ) );

		return $result;
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

	public function substr( $str, $len, $more = '...', $charset = 'UTF-8', $offset = 0 ) {
		if ( 1 > $len ) {
			return $str;
		}
		$more = esc_html( $more );
		$str  = html_entity_decode( $str, ENT_QUOTES, $charset );
		if ( function_exists( 'mb_strlen' ) ) {
			$length = mb_strlen( $str, $charset );
		} else {
			$length = strlen( $str );
		}
		if ( $length > $len ) {
			$arr = explode( ' ', $str );
			if ( function_exists( 'mb_substr' ) ) {
				$str = mb_substr( $str, $offset, $len, $charset );
			} else {
				$str = substr( $str, $offset, $len );
			}
			$arr_words = explode( ' ', $str );
			$index     = count( $arr_words ) - 1;
			$last      = $arr[ $index ];
			unset( $arr );
			if ( strcasecmp( $arr_words[ $index ], $last ) ) {
				unset( $arr_words[ $index ] );
			}

			return implode( ' ', $arr_words ) . $more;
		}

		return $str;
	}

	public function get_user_agent() {
		return isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
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

		$content = $file->get_contents( $url );

		if ( empty( $content ) ) {
			return false;
		}

		return true;
	}

	public static function is_image_url( $url ) {
		$img_formats = array( 'png', 'jpg', 'jpeg', 'gif', 'tiff', 'bmp', 'ico' );

		$path_info = pathinfo( $url );
		$extension = isset( $path_info['extension'] ) ? $path_info['extension'] : '';
		$extension = trim( strtolower( $extension ) );

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

	public function has_image( $string ) {
		$result = false;

		if ( false !== HT()->string_contain( $string, '.jpg' ) ) {
			$result = true;
		} elseif ( false !== HT()->string_contain( $string, '.png' ) ) {
			$result = true;
		} elseif ( false !== HT()->string_contain( $string, '.gif' ) ) {
			$result = true;
		}

		return $result;
	}

	public static function get_all_image_from_string( $data, $output = 'img' ) {
		$output = trim( $output );
		preg_match_all( '/<img[^>]+>/i', $data, $matches );
		$matches = isset( $matches[0] ) ? $matches[0] : array();

		if ( ! self::array_has_value( $matches ) && ! empty( $data ) ) {
			if ( false !== HT()->string_contain( $data, '//' ) ) {
				if ( HT()->has_image( $data ) ) {
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

	public function string_contain( $haystack, $needle, $offset = 0, $output = 'boolean' ) {
		$pos = strpos( $haystack, $needle, $offset );

		if ( false === $pos && function_exists( 'mb_strpos' ) ) {
			$pos = mb_strpos( $haystack, $needle, $offset );
		}

		if ( 'int' == $output || 'integer' == $output || 'numeric' == $output ) {
			return $pos;
		}

		return ( false !== $pos );
	}

	public function is_valid_domain( $url ) {
		$validation = false;

		$url = filter_var( $url, FILTER_SANITIZE_URL );

		if ( false !== $url ) {
			$urlparts = parse_url( $url );

			if ( ! isset( $urlparts['host'] ) ) {
				$urlparts['host'] = $urlparts['path'];
			}

			if ( ! empty( $urlparts['host'] ) ) {
				if ( ! isset( $urlparts['scheme'] ) ) {
					$urlparts['scheme'] = 'http';
				}

				$protocols = array(
					'http',
					'https'
				);

				if ( checkdnsrr( $urlparts['host'], 'A' ) ) {
					$validation = true;
				} elseif ( in_array( $urlparts['scheme'], $protocols ) && ! HT()->is_IP( $urlparts['host'] ) ) {
					$urlparts['host'] = preg_replace( '/^www\./', '', $urlparts['host'] );

					$url = $urlparts['scheme'] . '://' . $urlparts['host'] . '/';

					if ( filter_var( $url, FILTER_VALIDATE_URL ) !== false && @get_headers( $url ) ) {
						$validation = true;
					}
				}
			}

			unset( $urlparts, $protocols );
		}

		return $validation;
	}

	public static function get_domain_name( $url, $root = false ) {
		if ( ! is_string( $url ) || empty( $url ) ) {
			return '';
		}

		if ( false === HT()->string_contain( $url, 'http://' ) && false === HT()->string_contain( $url, 'https://' ) ) {
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

	public static function transmit( &$value, &$another, $filter = FILTER_SANITIZE_STRING ) {
		if ( $filter == FILTER_SANITIZE_NUMBER_INT || $filter == FILTER_SANITIZE_NUMBER_FLOAT ) {
			if ( ( is_numeric( $value ) && ! is_numeric( $another ) ) || ( ! is_numeric( $value ) && is_numeric( $another ) ) ) {
				if ( is_numeric( $value ) && ! is_numeric( $another ) ) {
					$another = $value;
				} elseif ( ! is_numeric( $value ) && is_numeric( $another ) ) {
					$value = $another;
				}
			}

			return;
		}

		if ( ( empty( $value ) || empty( $another ) ) && $value != $another ) {
			if ( empty( $value ) && ! empty( $another ) ) {
				$value = $another;
			} elseif ( empty( $another ) && ! empty( $value ) ) {
				$another = $value;
			}
		}
	}

	public function bool_to_string( $value, $uppercase = false ) {
		$value = ( (bool) $value ) ? 'true' : 'false';

		if ( $uppercase ) {
			$value = strtoupper( $value );
		}

		return $value;
	}

	public function bool_to_int( $value ) {
		return ( $value ) ? 1 : 0;
	}

	public function int_to_bool( $value ) {
		return ! ( 0 === $value );
	}

	public function int_to_bool_string( $value, $uppercase = false ) {
		return $this->bool_to_string( $this->int_to_bool( $value ), $uppercase );
	}
}

function HT() {
	return HOCWP_Theme::instance();
}