<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
	$current_type = HT_Util()->get_current_post_type();
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
	$args     = wp_parse_args( $args, $defaults );
	hocwp_theme_meta_box_editor( $args );
}