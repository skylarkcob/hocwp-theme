<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $pagenow;

if ( 'post-new.php' !== $pagenow && 'post.php' !== $pagenow && 'link.php' != $pagenow && 'link-add.php' != $pagenow ) {
	if ( 'edit-tags.php' != $pagenow && 'term.php' != $pagenow ) {
		return;
	}
}

function hocwp_theme_default_meta_boxes() {
	$args = array(
		'public' => true
	);

	$post_types = get_post_types( $args );

	if ( ht()->is_array_has_value( $post_types ) ) {
		$post_types = apply_filters( 'hocwp_theme_extra_info_meta_box_post_types', $post_types );

		$meta = new HOCWP_Theme_Meta_Post();
		$meta->set_post_types( $post_types );
		$meta->set_id( 'extra-post-info' );
		$meta->set_title( __( 'Extra Information', 'hocwp-theme' ) );
		$meta->form_table = true;

		$field = new HOCWP_Theme_Meta_Field( 'different_title', __( 'Different Title', 'hocwp-theme' ) );
		$meta->add_field( $field );

		$field = new HOCWP_Theme_Meta_Field( 'short_title', __( 'Short Title', 'hocwp-theme' ) );
		$meta->add_field( $field );

		$field = new HOCWP_Theme_Meta_Field( 'large_thumbnail', __( 'Large Thumbnail', 'hocwp-theme' ), 'media_upload' );
		$meta->add_field( $field );

		do_action_ref_array( 'hocwp_theme_extra_information_meta_fields', array( &$meta ) );

		$meta = new HOCWP_Theme_Meta_Post();
		$meta->set_post_types( $post_types );
		$meta->set_id( 'custom-post-code' );
		$meta->set_title( __( 'Custom Code', 'hocwp-theme' ) );

		$args = array(
			'description' => __( 'Add any custom HTML code after post content.', 'hocwp-theme' )
		);

		$meta->add_field( new HOCWP_Theme_Meta_Field( 'custom_code', '', 'code_editor', $args, 'html' ) );
	}
}

add_action( 'load-post.php', 'hocwp_theme_default_meta_boxes' );
add_action( 'load-post-new.php', 'hocwp_theme_default_meta_boxes' );

if ( 'post-new.php' == $pagenow || 'post.php' == $pagenow ) {
	function hocwp_theme_meta_box_google_maps( $id = 'google_maps', $post_type = 'post', $args = array() ) {
		$meta = new HOCWP_Theme_Meta_Post();

		if ( is_array( $post_type ) ) {
			$meta->set_post_types( $post_type );
		} else {
			$meta->add_post_type( $post_type );
		}

		$meta->set_title( __( 'Maps', 'hocwp-theme' ) );
		$meta->set_id( $id . '-box' );

		$field = hocwp_theme_create_meta_field( $id, '', 'google_maps', $args );
		$meta->add_field( $field );
	}

	function hocwp_theme_meta_box_editor( $args = array() ) {
		$post_type    = $args['post_type'] ?? 'post';
		$box_title    = $args['title'] ?? __( 'Additional Information', 'hocwp-theme' );
		$current_type = ht_admin()->get_current_post_type();

		if ( is_array( $current_type ) ) {
			$current_type = current( $current_type );
		}

		$box_id = $args['id'] ?? '';

		if ( empty( $box_id ) ) {
			$box_id = sanitize_title( $box_title );
			$box_id = ht_sanitize()->html_id( $box_id );

			if ( empty( $box_id ) ) {
				return;
			}
		}

		if ( empty( $current_type ) ) {
			$current_type = $_POST['post_type'] ?? '';
		}

		if ( ! empty( $current_type ) ) {
			$box_id = $current_type . '_' . $box_id;
		}

		$field_args = ht()->get_value_in_array( $args, 'field_args', array() );
		$field_args = (array) $field_args;

		$meta = new HOCWP_Theme_Meta_Post();

		if ( is_array( $post_type ) ) {
			$meta->set_post_types( $post_type );
		} else {
			$meta->add_post_type( $post_type );
		}

		$meta->set_title( $box_title );
		$meta->set_id( $box_id );

		$id   = $field_args['id'] ?? '';
		$name = $field_args['name'] ?? '';

		if ( empty( $id ) && empty( $name ) ) {
			$name = $box_id;
		}

		ht()->transmit( $id, $name );
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

		$args = array(
			'public' => true
		);

		$types = get_post_types( $args );

		$meta->set_post_types( $types );
		$meta->set_title( __( 'Advanced Settings', 'hocwp-theme' ) );

		$args = array(
			'options' => ht_util()->choose_sidebar_select_options( 'hocwp_sidebar' )
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

		$args = array(
			'type' => 'checkbox',
			'text' => __( 'Display content box as full width.', 'hocwp-theme' )
		);

		$field = hocwp_theme_create_meta_field( 'full_width', '', 'input', $args );
		$meta->add_field( $field );

		do_action( 'hocwp_theme_meta_post_advanced_settings_fields', $meta );
	}

	add_action( 'load-post.php', 'hocwp_theme_meta_box_advanced_settings' );
	add_action( 'load-post-new.php', 'hocwp_theme_meta_box_advanced_settings' );
}