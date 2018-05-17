<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'HOCWP_EXT_AMP_PATH', dirname( __FILE__ ) );
define( 'HOCWP_EXT_AMP_URL', HOCWP_THEME_CORE_PATH . '/ext/amp' );

function hocwp_ext_amp_fonts() {
	$options = HT_Util()->get_theme_options( 'amp' );
	$result  = array();

	$fonts = isset( $options['fonts'] ) ? $options['fonts'] : '';

	if ( ! empty( $fonts ) ) {
		$fonts = explode( ',', $fonts );
		$fonts = array_map( 'trim', $fonts );

		$args = array(
			'type' => 'url'
		);

		foreach ( $fonts as $font ) {
			$key = strtolower( $font );
			$key = str_replace( ' ', '_', $key );

			$font = isset( $options[ 'font_' . $key ] ) ? $options[ 'font_' . $key ] : '';

			if ( ! empty( $font ) ) {
				$result[ $key ] = $font;
			}
		}
	}

	return $result;
}

if ( ! is_admin() ) {
	load_template( HOCWP_EXT_AMP_PATH . '/front-end.php' );
} else {
	load_template( HOCWP_EXT_AMP_PATH . '/admin.php' );
}