<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_check_rewrite_rules() {
	if ( false !== get_transient( 'hocwp_theme_flush_rewrite_rules' ) ) {
		flush_rewrite_rules();
		delete_transient( 'hocwp_theme_flush_rewrite_rules' );
	}
}

add_action( 'init', 'hocwp_theme_check_rewrite_rules', 9999 );

function hocwp_theme_get_custom_post_types( $output = 'names', $builtin = false ) {
	$args = array(
		'public'   => true,
		'_builtin' => $builtin
	);

	return get_post_types( $args, $output );
}

function hocwp_theme_get_custom_taxonomies( $output = 'names', $builtin = false ) {
	$args = array(
		'public'   => true,
		'_builtin' => $builtin
	);

	return get_taxonomies( $args, $output );
}

function hocwp_theme_update_custom_permalinks_base() {
	if ( function_exists( 'hocwp_ext_change_base_slug' ) ) {
		hocwp_ext_change_base_slug();
	}
}

add_action( 'init', 'hocwp_theme_update_custom_permalinks_base', 999 );

function hocwp_theme_upate_permalinks_base_saved( $old_value, $value ) {
	$old_per = isset( $old_value['permalinks'] ) ? $old_value['permalinks'] : '';
	$old_per = md5( maybe_serialize( $old_per ) );
	$per     = isset( $value['permalinks'] ) ? $value['permalinks'] : '';
	$per     = md5( maybe_serialize( $per ) );
	if ( $per != $old_per ) {
		set_transient( 'hocwp_theme_flush_rewrite_rules', 1 );
	}
}

add_action( 'update_option_hocwp_theme', 'hocwp_theme_upate_permalinks_base_saved', 10, 2 );