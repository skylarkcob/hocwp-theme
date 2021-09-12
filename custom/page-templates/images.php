<?php
/**
 * Template Name: Images
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header();

$args = array(
	'paged' => HT_Frontend()->get_paged(),
	'tax_query'      => array(
		'relation' => 'AND',
		array(
			'taxonomy' => 'post_format',
			'field'    => 'slug',
			'terms'    => array( 'post-format-image' ),
		)
	)
);

$query = new WP_Query( $args );

HOCWP_Theme()->add_loop_data( 'query', $query );

HT_Custom()->load_template( 'template-blog' );

get_footer();