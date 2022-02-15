<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

trait HOCWP_Theme_PHP {
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

		if ( empty( $value ) ) {
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

	public function current_milliseconds() {
		return round( microtime( true ) * 1000 );
	}
}