<?php
/**
 * Template Name: Blank Page
 */

defined( 'ABSPATH' ) || exit;
get_header();

hocwp_theme()->set_temp_data( 'full_width', true );

while ( have_posts() ) {
	the_post();
	do_action( 'hocwp_theme_the_content' );
}

get_footer();