<?php
defined( 'ABSPATH' ) || exit;

if ( ! trait_exists( 'HOCWP_Theme_Deprecated' ) ) {
	require_once __DIR__ . '/trait-deprecated.php';
}

trait HOCWP_Theme_Utils {
	use HOCWP_Theme_Deprecated;

	public function make_image_lazyload( $content, $originals = array() ) {
		$dom = new DOMDocument();
		@$dom->loadHTML( $content );

		if ( ! in_array( 'data-original', $originals ) ) {
			$originals[] = 'data-original';
		}

		if ( ! in_array( 'data-src', $originals ) ) {
			$originals[] = 'data-src';
		}

		foreach ( $dom->getElementsByTagName( 'img' ) as $node ) {
			$old_src = $node->getAttribute( 'src' );

			foreach ( $originals as $attr ) {
				$node->setAttribute( $attr, $old_src );
			}

			$node->setAttribute( 'src', HOCWP_THEME_DOT_IMAGE_SRC );
		}

		return $dom->saveHtml();
	}

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

	public function get_site_name() {
		$name = HT_Options()->get_general( 'site_short_name' );

		if ( empty( $name ) ) {
			$name = get_bloginfo( 'name' );
		}

		return apply_filters( 'hocwp_theme_site_name', $name );
	}
}