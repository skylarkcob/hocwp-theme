<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $pagenow;

if ( 'post-new.php' !== $pagenow && 'post.php' !== $pagenow ) {
	return;
}

function hocwp_theme_meta_box_google_maps( $id = 'google_maps', $post_type = 'post' ) {
	$meta = new HOCWP_Theme_Meta_Post();

	if ( is_array( $post_type ) ) {
		$meta->set_post_types( $post_type );
	} else {
		$meta->add_post_type( $post_type );
	}

	$meta->set_title( __( 'Maps', 'hocwp-theme' ) );
	$meta->set_id( $id . '-box' );

	$field = hocwp_theme_create_meta_field( $id, '', 'google_maps' );
	$meta->add_field( $field );
}

function hocwp_theme_meta_box_editor( $args = array() ) {
	$post_type    = isset( $args['post_type'] ) ? $args['post_type'] : 'post';
	$box_title    = isset( $args['title'] ) ? $args['title'] : __( 'Additional Information', 'hocwp-theme' );
	$current_type = HT_Admin()->get_current_post_type();

	if ( is_array( $current_type ) ) {
		$current_type = current( $current_type );
	}

	$box_id = isset( $args['id'] ) ? $args['id'] : '';

	if ( empty( $box_id ) ) {
		$box_id = HT_Sanitize()->html_id( $box_title );

		if ( empty( $box_id ) ) {
			return;
		}
	}

	if ( empty( $current_type ) ) {
		$current_type = isset( $_POST['post_type'] ) ? $_POST['post_type'] : '';
	}

	if ( ! empty( $current_type ) ) {
		$box_id = $current_type . '_' . $box_id;
	}

	$field_args = HT()->get_value_in_array( $args, 'field_args', array() );
	$field_args = (array) $field_args;

	$meta = new HOCWP_Theme_Meta_Post();

	if ( is_array( $post_type ) ) {
		$meta->set_post_types( $post_type );
	} else {
		$meta->add_post_type( $post_type );
	}

	$meta->set_title( $box_title );
	$meta->set_id( $box_id );

	$id   = isset( $field_args['id'] ) ? $field_args['id'] : '';
	$name = isset( $field_args['name'] ) ? $field_args['name'] : '';
	HT()->transmit( $id, $name );
	$field_args['name'] = $name;

	$field = hocwp_theme_create_meta_field( $id, '', 'editor', $field_args, 'html' );
	$meta->add_field( $field );
}

function hocwp_theme_meta_box_editor_gallery( $args = array() ) {
	$defaults = array(
		'title'      => __( 'Gallery', 'hocwp-theme' ),
		'field_args' => array(
			'id'      => 'image_gallery',
			'teeny'   => true,
			'toolbar' => false,
			'name'    => 'gallery'
		)
	);

	$args = wp_parse_args( $args, $defaults );
	hocwp_theme_meta_box_editor( $args );
}

function hocwp_theme_meta_box_advanced_settings() {
	$meta = new HOCWP_Theme_Meta_Post();
	$meta->set_context( 'side' );
	$meta->set_priority( 'low' );
	$meta->set_id( 'hocwp-post-advanced-settings' );

	$args  = array(
		'public' => true
	);
	$types = get_post_types( $args );

	$meta->set_post_types( $types );
	$meta->set_title( __( 'Advanced Settings', 'hocwp-theme' ) );

	$sidebars = array(
		'' => __( 'Default Sidebar', 'hocwp-theme' )
	);

	$args = array(
		'post_type'      => 'hocwp_sidebar',
		'posts_per_page' => - 1
	);

	$query = new WP_Query( $args );

	if ( $query->have_posts() ) {
		foreach ( $query->posts as $post ) {
			$sidebars[ $post->post_name ] = $post->post_title;
		}
	}

	$args = array(
		'options' => $sidebars
	);

	$field = hocwp_theme_create_meta_field( 'sidebar', __( 'Sidebar', 'hocwp-theme' ), 'select', $args );
	$meta->add_field( $field );

	$args = array(
		'options' => array(
			''      => __( 'Default', 'hocwp-theme' ),
			'right' => _x( 'Right', 'sidebar position', 'hocwp-theme' ),
			'left'  => _x( 'Left', 'sidebar position', 'hocwp-theme' )
		)
	);

	$field = hocwp_theme_create_meta_field( 'sidebar_position', __( 'Sidebar Position', 'hocwp-theme' ), 'select', $args );
	$meta->add_field( $field );

	$args  = array(
		'type' => 'checkbox'
	);
	$field = hocwp_theme_create_meta_field( 'full_width', __( 'Display content box as full width.', 'hocwp-theme' ), 'input', $args );
	$meta->add_field( $field );
}

add_action( 'load-post.php', 'hocwp_theme_meta_box_advanced_settings' );
add_action( 'load-post-new.php', 'hocwp_theme_meta_box_advanced_settings' );