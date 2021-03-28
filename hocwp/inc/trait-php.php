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

	public function remove_empty_lines( $string ) {
		return preg_replace( "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $string );
	}
}