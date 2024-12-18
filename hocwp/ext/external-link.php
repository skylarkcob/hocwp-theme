<?php
/*
 * Name: External Link
 * Description: Make all external links as nofollow and control go out url.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'hocwp_theme_load_extension_external_link' ) ) {
	function hocwp_theme_load_extension_external_link() {
		$load = ht_extension()->is_active( __FILE__ );
		$load = apply_filters( 'hocwp_theme_load_extension_external_link', $load );

		return $load;
	}
}

$load = hocwp_theme_load_extension_external_link();

if ( ! $load ) {
	return;
}

if ( ! is_admin() ) {
	load_template( dirname( __FILE__ ) . '/external-link/front-end.php' );
} else {
	load_template( dirname( __FILE__ ) . '/external-link/admin.php' );
}