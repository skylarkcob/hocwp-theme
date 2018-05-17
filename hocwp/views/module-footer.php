<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

do_action( 'hocwp_theme_site_content_bottom' );
hocwp_theme_html_tag_close( 'div' );
hocwp_theme_html_tag( 'footer', 'site_footer' );
do_action( 'hocwp_theme_module_site_footer' );
hocwp_theme_html_tag_close( 'footer' );
hocwp_theme_html_tag_close( 'div' );
wp_footer();
hocwp_theme_html_tag_close( 'body' );
hocwp_theme_html_tag_close( 'html' );