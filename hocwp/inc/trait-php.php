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

	function memory_size_convert( $size ) {
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
}