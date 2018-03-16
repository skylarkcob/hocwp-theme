<?php
/*
 * Name: External Link
 * Description: Make all external link as nofollow and contro go out url.
 */
function hocwp_theme_load_extension_external_link() {
	$load = hocwp_theme_is_extension_active( __FILE__ );
	$load = apply_filters( 'hocwp_theme_load_extension_external_link', $load );

	return $load;
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