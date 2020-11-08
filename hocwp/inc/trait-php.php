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
}