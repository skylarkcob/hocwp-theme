<?php
defined( 'ABSPATH' ) || exit;

trait HOCWP_Theme_Utils {
	public function price_format( $price, $decimals = 0, $format = null ) {
		if ( empty( $format ) ) {
			$format = _x( '$%s', 'price format', 'hocwp-theme' );
		}

		$formatted = sprintf( $format, number_format_i18n( $price, $decimals ) );

		return apply_filters( 'hocwp_theme_price_format', $formatted, $price, $decimals, $format );
	}
}