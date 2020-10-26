<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$blank_body = apply_filters( 'hocwp_theme_blank_body', false );

if ( ! $blank_body ) {
	if ( HT_Frontend()->is_amp( array( 'transitional', 'standard' ) ) ) {
		hocwp_theme_load_views( 'module-footer-amp' );

		return;
	}

	if ( ! defined( 'HOCWP_THEME_BLANK_STYLE' ) || ! HOCWP_THEME_BLANK_STYLE ) {
		do_action( 'hocwp_theme_site_content_bottom' );
		hocwp_theme_html_tag_close( 'div' );
		do_action( 'hocwp_theme_site_footer_before' );
		hocwp_theme_html_tag( 'footer', 'site_footer' );
		do_action( 'hocwp_theme_module_site_footer' );
		hocwp_theme_html_tag_close( 'footer' );
		do_action( 'hocwp_theme_site_footer_after' );
		hocwp_theme_html_tag_close( 'div' );
	} else {
		do_action( 'hocwp_theme_module_site_footer' );
	}
}

wp_footer();
hocwp_theme_html_tag_close( 'body' );
hocwp_theme_html_tag_close( 'html' );