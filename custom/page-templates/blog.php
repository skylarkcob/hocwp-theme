<?php
/**
 * Template Name: Blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header();
HT_Custom()->load_template( 'template-blog' );
get_footer();