<?php
defined( 'ABSPATH' ) || exit;

if ( HOCWP_THEME_SUPPORT_PHP8 ) {
	class HOCWP_Theme_PHP8 {
		protected static $_instance = null;

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		public static function match( $switch, $values = array() ) {
			$count = count( $values );

			$tmp = current( $values );

			if ( ! is_array( $tmp ) ) {
				return $values[ $switch ] ?? $values['default'] ?? '';
			}

			if ( 6 >= $count ) {
				return match ( $switch ) {
					is_array( $values[0][0] ?? '' ) ? ( in_array( $switch, $values[0][0] ) ? $switch : false ) : $values[0][0] => $values[0][1] ?? '',
					is_array( $values[1][0] ?? '' ) ? ( in_array( $switch, $values[1][0] ) ? $switch : false ) : $values[1][0] ?? '' => $values[1][1] ?? '',
					is_array( $values[2][0] ?? '' ) ? ( in_array( $switch, $values[2][0] ) ? $switch : false ) : $values[2][0] ?? '' => $values[2][1] ?? '',
					is_array( $values[3][0] ?? '' ) ? ( in_array( $switch, $values[3][0] ) ? $switch : false ) : $values[3][0] ?? '' => $values[3][1] ?? '',
					is_array( $values[4][0] ?? '' ) ? ( in_array( $switch, $values[4][0] ) ? $switch : false ) : $values[4][0] ?? '' => $values[4][1] ?? '',
					is_array( $values[5][0] ?? '' ) ? ( in_array( $switch, $values[5][0] ) ? $switch : false ) : $values[5][0] ?? '' => $values[5][1] ?? '',
					default => $values['default'] ?? ''
				};
			}

			return match ( $switch ) {
				is_array( $values[0][0] ?? '' ) ? ( in_array( $switch, $values[0][0] ) ? $switch : false ) : $values[0][0] => $values[0][1] ?? '',
				is_array( $values[1][0] ?? '' ) ? ( in_array( $switch, $values[1][0] ) ? $switch : false ) : $values[1][0] ?? '' => $values[1][1] ?? '',
				is_array( $values[2][0] ?? '' ) ? ( in_array( $switch, $values[2][0] ) ? $switch : false ) : $values[2][0] ?? '' => $values[2][1] ?? '',
				is_array( $values[3][0] ?? '' ) ? ( in_array( $switch, $values[3][0] ) ? $switch : false ) : $values[3][0] ?? '' => $values[3][1] ?? '',
				is_array( $values[4][0] ?? '' ) ? ( in_array( $switch, $values[4][0] ) ? $switch : false ) : $values[4][0] ?? '' => $values[4][1] ?? '',
				is_array( $values[5][0] ?? '' ) ? ( in_array( $switch, $values[5][0] ) ? $switch : false ) : $values[5][0] ?? '' => $values[5][1] ?? '',
				is_array( $values[6][0] ?? '' ) ? ( in_array( $switch, $values[6][0] ) ? $switch : false ) : $values[6][0] ?? '' => $values[6][1] ?? '',
				is_array( $values[7][0] ?? '' ) ? ( in_array( $switch, $values[7][0] ) ? $switch : false ) : $values[7][0] ?? '' => $values[7][1] ?? '',
				is_array( $values[8][0] ?? '' ) ? ( in_array( $switch, $values[8][0] ) ? $switch : false ) : $values[8][0] ?? '' => $values[8][1] ?? '',
				is_array( $values[9][0] ?? '' ) ? ( in_array( $switch, $values[9][0] ) ? $switch : false ) : $values[9][0] ?? '' => $values[9][1] ?? '',
				is_array( $values[10][0] ?? '' ) ? ( in_array( $switch, $values[10][0] ) ? $switch : false ) : $values[10][0] ?? '' => $values[10][1] ?? '',
				is_array( $values[11][0] ?? '' ) ? ( in_array( $switch, $values[11][0] ) ? $switch : false ) : $values[11][0] ?? '' => $values[11][1] ?? '',
				is_array( $values[12][0] ?? '' ) ? ( in_array( $switch, $values[12][0] ) ? $switch : false ) : $values[12][0] ?? '' => $values[12][1] ?? '',
				is_array( $values[13][0] ?? '' ) ? ( in_array( $switch, $values[13][0] ) ? $switch : false ) : $values[13][0] ?? '' => $values[13][1] ?? '',
				is_array( $values[14][0] ?? '' ) ? ( in_array( $switch, $values[14][0] ) ? $switch : false ) : $values[14][0] ?? '' => $values[14][1] ?? '',
				is_array( $values[15][0] ?? '' ) ? ( in_array( $switch, $values[15][0] ) ? $switch : false ) : $values[15][0] ?? '' => $values[15][1] ?? '',
				default => $values['default'] ?? ''
			};
		}
	}

	function ht_php8() {
		return HOCWP_Theme_PHP8::instance();
	}
}