<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

trait HOCWP_Theme_PHP {
	public function get_user_agent() {
		return $_SERVER['HTTP_USER_AGENT'] ?? '';
	}

	public function get_params_from_url( $url ) {
		$parse = parse_url( $url );
		$parse = isset( $parse['query'] ) ? $parse['query'] : '';
		parse_str( $parse, $params );

		return $params;
	}

	public function explode_empty_line( $string ) {
		return preg_split( "#\n\s*\n#Uis", $string );
	}

	public function explode_new_line( $string ) {
		return preg_split( '/\r\n|\r|\n/', $string );
	}

	public function remove_empty_lines( $string ) {
		return preg_replace( "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $string );
	}

	public function remove_empty_tag_p( $string ) {
		$pattern = "/<p[^>]*><\\/p[^>]*>/";

		return preg_replace( $pattern, '', $string );
	}

	public function remove_html_tag( $tag, $string, $replace = '' ) {
		return preg_replace( "/<" . $tag . "[^>]+\>/i", $replace, $string );
	}

	public function memory_size_convert( $size ) {
		$l   = substr( $size, - 1 );
		$ret = substr( $size, 0, - 1 );

		switch ( strtoupper( $l ) ) {
			case 'P':
				$ret *= 1024;
			case 'T':
				$ret *= 1024;
			case 'G':
				$ret *= 1024;
			case 'M':
				$ret *= 1024;
			case 'K':
				$ret *= 1024;
		}

		return $ret;
	}

	public function get_value_in_arrays( $key, $arr1, $arr2 ) {
		$value = $arr1[ $key ] ?? '';

		if ( '' == $value ) {
			$value = $arr2[ $key ] ?? '';
		}

		return $value;
	}

	public function get_last_modified_files( $dir, $number = 10, $mode = null ) {
		$lists = array();

		if ( ! class_exists( 'RecursiveDirectoryIterator' ) ) {
			return $lists;
		}

		if ( is_file( $dir ) ) {
			$dir = dirname( $dir );
		}

		if ( null === $mode ) {
			$mode = RecursiveIteratorIterator::CHILD_FIRST;
		}

		$rii = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir ), $mode );

		foreach ( $rii as $file_path => $cls_spl ) {
			if ( $cls_spl->isFile() ) {
				$lists[] = $file_path;
			}
		}

		$lists = array_combine( $lists, array_map( 'filemtime', $lists ) );

		arsort( $lists );

		if ( 0 < $number && $number < count( $lists ) ) {
			$lists = array_slice( $lists, 0, $number );
		}

		return $lists;
	}

	public function add_string_with_char( &$string, $add_text, $char = ' ', $char_position = 'head' ) {
		if ( ! empty( $add_text ) ) {
			if ( 'head' == $char_position ) {
				$string = $add_text . $char . $string;
			} else {
				$string .= $char;
				$string .= $add_text;
			}
		}
	}

	public function current_milliseconds() {
		return round( microtime( true ) * 1000 );
	}

	public function get_ie_version( $agent = null ) {
		if ( empty( $agent ) ) {
			$agent = $this->get_user_agent();
		}

		// Detect IE below 11
		if ( strrpos( $agent, 'MSIE' ) !== false ) {
			$parts   = explode( 'MSIE', $agent );
			$version = (int) $parts[1];

			return $version;
		}

		// Detect IE 11
		if ( strrpos( $agent, 'Trident/' ) !== false ) {
			$parts   = explode( 'rv:', $agent );
			$version = (int) $parts[1];

			return $version;
		}

		// Not found
		return null;
	}

	public function get_browser() {
		$u_agent  = $this->get_user_agent();
		$bname    = 'Unknown';
		$platform = 'Unknown';
		$ub       = '';

		// First get the platform
		if ( preg_match( '/linux/i', $u_agent ) ) {
			$platform = 'linux';
		} elseif ( preg_match( '/macintosh|mac os x/i', $u_agent ) ) {
			$platform = 'mac';
		} elseif ( preg_match( '/windows|win32/i', $u_agent ) ) {
			$platform = 'windows';
		}

		// Next get the name of the useragent yes separately and for good reason
		if ( preg_match( '/MSIE/i', $u_agent ) && ! preg_match( '/Opera/i', $u_agent ) ) {
			$bname = 'Internet Explorer';
			$ub    = 'MSIE';
		} elseif ( preg_match( '/Firefox/i', $u_agent ) ) {
			$bname = 'Mozilla Firefox';
			$ub    = 'Firefox';
		} elseif ( preg_match( '/OPR/i', $u_agent ) ) {
			$bname = 'Opera';
			$ub    = 'Opera';
		} elseif ( preg_match( '/Chrome/i', $u_agent ) && ! preg_match( '/Edge/i', $u_agent ) ) {
			$bname = 'Google Chrome';
			$ub    = 'Chrome';
		} elseif ( preg_match( '/Safari/i', $u_agent ) && ! preg_match( '/Edge/i', $u_agent ) ) {
			$bname = 'Apple Safari';
			$ub    = 'Safari';
		} elseif ( preg_match( '/Netscape/i', $u_agent ) ) {
			$bname = 'Netscape';
			$ub    = 'Netscape';
		} elseif ( preg_match( '/Edge/i', $u_agent ) ) {
			$bname = 'Edge';
			$ub    = 'Edge';
		} elseif ( preg_match( '/Trident/i', $u_agent ) ) {
			$bname = 'Internet Explorer';
			$ub    = 'MSIE';
		}

		// Finally, get the correct version number
		$known   = array( 'Version', $ub, 'other' );
		$pattern = '#(?<browser>' . join( '|', $known ) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';

		preg_match_all( $pattern, $u_agent, $matches );

		// See how many we have
		$i = count( $matches['browser'] );

		if ( $i != 1 ) {
			// We will have two since we are not using 'other' argument yet
			// See if version is before or after the name
			if ( strripos( $u_agent, 'Version' ) < strripos( $u_agent, $ub ) ) {
				$version = $matches['version'][0];
			} else {
				$version = $matches['version'][1] ?? '';
			}
		} else {
			$version = $matches['version'][0];
		}

		// Check if we have a number
		if ( $version == null || $version == '' ) {
			if ( 'msie' == strtolower( $ub ) ) {
				$version = $this->get_ie_version( $u_agent );
			} else {
				$version = '?';
			}
		}

		return array(
			'user_agent' => $u_agent,
			'name'       => $bname,
			'short_name' => $ub,
			'version'    => $version,
			'platform'   => $platform,
			'pattern'    => $pattern
		);
	}
}