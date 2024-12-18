<?php
/**
 * Template Name: Blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header();

$args = array(
	'paged' => ht_frontend()->get_paged()
);

$query = new WP_Query( $args );

hocwp_theme()->add_loop_data( 'query', $query );

ht_custom()->load_template( 'template-blog' );

get_footer();