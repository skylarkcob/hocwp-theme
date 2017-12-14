<?php
/**
 * Add meta boxes to post types.
 */
function hocwp_theme_custom_post_meta() {

}

add_action( 'load-post.php', 'hocwp_theme_custom_post_meta' );
add_action( 'load-post-new.php', 'hocwp_theme_custom_post_meta' );

/**
 * Add custom meta fields for term.
 */
function hocwp_theme_custom_term_meta() {

}

add_action( 'load-edit-tags.php', 'hocwp_theme_custom_term_meta' );