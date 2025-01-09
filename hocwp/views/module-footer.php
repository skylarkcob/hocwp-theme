<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// If not is VR theme, load all templates.
if ( ! ht_util()->is_vr_theme() ) {
	// Load all templates if is not blank body.
	if ( ! hocwp_theme()->is_blank_body() ) {
		// Check and load only AMP templates.
		if ( ht_frontend()->is_amp( array( 'transitional', 'standard' ) ) ) {
			hocwp_theme_load_views( 'module-footer-amp' );

			return;
		}

		if ( ! defined( 'HOCWP_THEME_BLANK_STYLE' ) || ! HOCWP_THEME_BLANK_STYLE ) {
			// Inner .site-content bottom action.
			do_action( 'hocwp_theme_site_content_bottom' );
			do_action( 'ht/site_content/bottom' );
			// Close .site-content div.
			hocwp_theme_html_tag_close( 'div', 'site_content' ); // Close .site-content
			// Before .site-footer action.
			do_action( 'hocwp_theme_site_footer_before' );
			do_action( 'ht/site_footer/before' );
			// Open .site-footer tag.
			hocwp_theme_html_tag( 'footer', 'site_footer' ); // Open .site-footer
			// Inner .site-footer action.
			do_action( 'hocwp_theme_module_site_footer' );
			do_action( 'ht/module/site_footer' );
			// Close .site-footer tag.
			hocwp_theme_html_tag_close( 'footer', 'site_footer' ); // Close .site-footer
			// After .site-footer action.
			do_action( 'hocwp_theme_site_footer_after' );
			do_action( 'ht/site_footer/after' );
			// Close site_container div with .site class.
			hocwp_theme_html_tag_close( 'div', 'site_container' ); // Close .site
		} else {
			// Inner .site-footer action.
			do_action( 'hocwp_theme_module_site_footer' );
			do_action( 'ht/module/site_footer' );
		}
	}
}

hocwp_theme_get_footer();