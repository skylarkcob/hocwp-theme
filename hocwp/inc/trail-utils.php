<?php
defined( 'ABSPATH' ) || exit;

if ( ! trait_exists( 'HOCWP_Theme_Deprecated' ) ) {
	require_once __DIR__ . '/trait-deprecated.php';
}

trait HOCWP_Theme_Utils {
	use HOCWP_Theme_Deprecated;

	public function price_format( $price, $decimals = 0, $format = null ) {
		if ( empty( $format ) ) {
			$format = _x( '$%s', 'price format', 'hocwp-theme' );
		}

		$formatted = sprintf( $format, number_format_i18n( $price, $decimals ) );

		return apply_filters( 'hocwp_theme_price_format', $formatted, $price, $decimals, $format );
	}

	public function is_localhost() {
		$domain = home_url();
		$domain = HT()->get_domain_name( $domain, true );

		return ( 'localhost' == $domain || str_contains( $domain, 'localhost' ) || str_contains( $domain, '127.0.0.1' ) || str_contains( $domain, '192.168.1.249' ) || str_contains( $domain, '192.168.1.213' ) || str_contains( $domain, '192.168.1.69' ) );
	}
}