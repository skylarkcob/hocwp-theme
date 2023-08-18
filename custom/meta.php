<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Add meta boxes to post types.
 */
if ( ! function_exists( 'hocwp_theme_custom_post_meta' ) ) {
	function hocwp_theme_custom_post_meta() {

	}
}

add_action( 'load-post.php', 'hocwp_theme_custom_post_meta' );
add_action( 'load-post-new.php', 'hocwp_theme_custom_post_meta' );
add_action( 'load-edit.php', 'hocwp_theme_custom_post_meta' );

// Apply for quick edit fields and bulk actions
if ( HOCWP_THEME_DOING_AJAX ) {
	add_action( 'init', 'hocwp_theme_custom_post_meta' );
}

/**
 * Add custom meta fields for Extra Information meta box.
 *
 * @param HOCWP_Theme_Meta_Post $meta Meta object for post, page or custom post types.
 */
if ( ! function_exists( 'hocwp_theme_custom_extra_information_meta_fields' ) ) {
	function hocwp_theme_custom_extra_information_meta_fields( $meta ) {
		if ( $meta instanceof HOCWP_Theme_Meta_Post ) {

		}
	}
}

add_action( 'hocwp_theme_extra_information_meta_fields', 'hocwp_theme_custom_extra_information_meta_fields' );

/**
 * Add custom meta fields for term.
 */
if ( ! function_exists( 'hocwp_theme_custom_term_meta' ) ) {
	function hocwp_theme_custom_term_meta() {

	}

	add_action( 'load-edit-tags.php', 'hocwp_theme_custom_term_meta' );
}

/**
 * Add custom meta fields for menu.
 */
if ( ! function_exists( 'hocwp_theme_custom_menu_meta' ) ) {
	function hocwp_theme_custom_menu_meta() {

	}
}

add_action( 'load-nav-menus.php', 'hocwp_theme_custom_menu_meta' );
add_action( 'load-admin-ajax.php', 'hocwp_theme_custom_menu_meta' );

/**
 * Add meta fields to media file edit.
 */
if ( ! function_exists( 'hocwp_theme_custom_attachment_meta' ) ) {
	function hocwp_theme_custom_attachment_meta() {

	}
}

add_action( 'admin_init', 'hocwp_theme_custom_attachment_meta' );