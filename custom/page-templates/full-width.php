<?php
/**
 * Template Name: Full Width
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

hocwp_theme()->set_temp_data( 'full_width', true );

get_header();

do_action( 'hocwp_theme_template_page' );

get_footer();