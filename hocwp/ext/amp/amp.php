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

function hocwp_ext_amp_after_setup_theme_action() {
	register_nav_menu( 'amp', __( 'AMP menu', 'hocwp-theme' ) );
}

add_action( 'after_setup_theme', 'hocwp_ext_amp_after_setup_theme_action' );

function hocwp_ext_amp_init_action() {
	add_rewrite_endpoint( 'menu-amp', EP_ROOT );
}

add_action( 'init', 'hocwp_ext_amp_init_action', 0 );

if ( ! is_admin() ) {
	load_template( HOCWP_EXT_AMP_PATH . '/front-end.php' );
} else {
	load_template( HOCWP_EXT_AMP_PATH . '/admin.php' );
}