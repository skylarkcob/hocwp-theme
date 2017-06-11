<?php
function hocwp_theme_site_header() {
	hocwp_theme_load_custom_module( 'site-header' );
}

add_action( 'hocwp_theme_site_header', 'hocwp_theme_site_header' );

function hocwp_theme_site_footer() {
	hocwp_theme_load_custom_module( 'site-footer' );
}

add_action( 'hocwp_theme_site_footer', 'hocwp_theme_site_footer' );