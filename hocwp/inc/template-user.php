<?php
add_filter( 'login_headerurl', 'hocwp_theme_login_headerurl_filter' );
function hocwp_theme_login_headerurl_filter( $login_header_url ) {
	if ( false !== strpos( $login_header_url, 'wordpress.org' ) ) {
		$login_header_url = home_url( '/' );
	}

	return $login_header_url;
}

add_filter( 'login_headertitle', 'hocwp_theme_login_headertitle_filter' );
function hocwp_theme_login_headertitle_filter() {
	return get_bloginfo( 'description', 'display' );
}