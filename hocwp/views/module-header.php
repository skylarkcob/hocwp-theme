<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Check and load header for AMP.
if ( ht_frontend()->is_amp( array( 'transitional', 'standard' ) ) ) {
	hocwp_theme_load_views( 'module-header-amp' );

	return;
} else {
	// Load normal header.
	hocwp_theme_get_header();
}

// Check and load normal templates not VR site.
if ( ! ht_util()->is_vr_theme() ) {
	if ( hocwp_theme()->is_blank_body() ) {
		return;
	}

	if ( ! defined( 'HOCWP_THEME_BLANK_STYLE' ) || ! HOCWP_THEME_BLANK_STYLE ) {
		// Open site container div with .site class.
		hocwp_theme_html_tag( 'div', 'site_container' ); // Open .site
		?>
        <a class="skip-link screen-reader-text"
           href="#content"
           title="<?php esc_attr_e( 'Skip to content', 'hocwp-theme' ); ?>"><?php esc_html_e( 'Skip to content', 'hocwp-theme' ); ?></a>
		<?php
		// Before .site-header action.
		do_action( 'hocwp_theme_site_header_before' );
		do_action( 'ht/site_header/before' );
		// Open .site-header tag.
		hocwp_theme_html_tag( 'header', 'site_header', array(
			'class' => 'site-header',
			'id'    => 'masthead'
		) ); // Open .site-header
		// Middle .site-header tag action.
		do_action( 'hocwp_theme_module_site_header' );
		do_action( 'ht/module/site_header' );
		// Close .site-header tag.
		hocwp_theme_html_tag_close( 'header', 'site_header' ); // Close .site-header
		// After .site-header action.
		do_action( 'hocwp_theme_site_header_after' );
		do_action( 'ht/site_header/after' );
		// Open .site-content tag.
		hocwp_theme_html_tag( 'div', 'site_content' ); // Open .site-content
		// Inner top .site-content tag action.
		do_action( 'hocwp_theme_site_content_top' );
		do_action( 'ht/site_content/top' );
	} else {
		// Middle .site-header tag action.
		do_action( 'hocwp_theme_module_site_header' );
		do_action( 'ht/module/site_header' );
	}
}