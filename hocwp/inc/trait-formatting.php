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
}