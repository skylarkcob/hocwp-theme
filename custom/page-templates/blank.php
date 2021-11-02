<?php
/**
 * Template Name: Blank Page
 */

get_header();

global $hocwp_theme;
$hocwp_theme->temp_data['full_width'] = true;

while ( have_posts() ) {
	the_post();
	do_action( 'hocwp_theme_the_content' );
}

get_footer();