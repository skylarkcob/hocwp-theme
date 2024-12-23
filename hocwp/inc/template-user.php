<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_login_headerurl_filter( $login_header_url ) {
	if ( ht()->string_contain( $login_header_url, 'wordpress.org' ) ) {
		$login_header_url = home_url( '/' );
	}

	return $login_header_url;
}

add_filter( 'login_headerurl', 'hocwp_theme_login_headerurl_filter' );

function hocwp_theme_login_headertitle_filter( $title ) {
	if ( ! ht()->string_contain( $title, 'img' ) && ! ht()->string_contain( $title, 'src' ) ) {
		$title = get_bloginfo( 'name', 'display' );
	}

	return $title;
}

add_filter( 'login_headertext', 'hocwp_theme_login_headertitle_filter' );