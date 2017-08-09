<?php
if ( has_tag() ) {
	the_tags( '<div class="tags-links">', ' ', '</div>' );
} else {
	echo '<div class="tags-links">';
	$post_type  = get_post_type();
	$taxonomies = get_object_taxonomies( $post_type );
	if ( is_tax() || is_category() || is_tag() ) {
		$term = get_queried_object();
		if ( ( $key = array_search( $term->taxonomy, $taxonomies ) ) !== false ) {
			unset( $taxonomies[ $key ] );
		}
	}
	foreach ( $taxonomies as $tax ) {
		the_terms( get_the_ID(), $tax, '', ' ', '' );
	}
	echo '</div>';
}