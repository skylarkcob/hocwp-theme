<?php
/**
 * Add meta boxes to post types.
 */
function hocwp_theme_custom_post_meta() {
	$meta = new HOCWP_Theme_Meta_Post();
	$meta->set_title( __( 'Post Options', 'hocwp-theme' ) );
	$meta->add_post_type( 'post' );
	$meta->form_table = true;

	$args  = array(
		'description' => __( 'Add custom post heading. If is empty than will be displayed the post title.', 'hocwp-theme' )
	);
	$field = hocwp_theme_create_meta_field( 'custom_page_heading', __( 'Custom Post Heading', 'hocwp-theme' ), 'input', $args );
	$meta->add_field( $field );

	$args  = array(
		'description' => __( 'Add custom post description. It will be displayed under the post <strong>Title</strong>.', 'hocwp-theme' ),
		'rows'        => 4
	);
	$field = hocwp_theme_create_meta_field( 'custom_page_description', __( 'Custom Page Description', 'hocwp-theme' ), 'textarea', $args );
	$meta->add_field( $field );

	$args  = array(
		'description' => __( 'Add in this field the image, video or audio url and select the Post Format in right sidebar.', 'hocwp-theme' ),
		'rows'        => 4
	);
	$field = hocwp_theme_create_meta_field( 'custom_post_format', __( 'Post Format URL', 'hocwp-theme' ), 'textarea', $args );
	$meta->add_field( $field );
}

add_action( 'load-post.php', 'hocwp_theme_custom_post_meta' );
add_action( 'load-post-new.php', 'hocwp_theme_custom_post_meta' );

/**
 * Add custom meta fields for term.
 */
function hocwp_theme_custom_term_meta() {

}

add_action( 'load-edit-tags.php', 'hocwp_theme_custom_term_meta' );