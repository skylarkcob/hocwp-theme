<?php
/**
 * Template Name: Images
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header();

$args = array(
	'paged' => ht_frontend()->get_paged(),
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

hocwp_theme()->add_loop_data( 'query', $query );

ht_custom()->load_template( 'template-blog' );

get_footer();