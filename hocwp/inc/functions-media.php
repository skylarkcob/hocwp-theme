<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_media_file_exists( $id ) {
	if ( HT()->is_file( get_attached_file( $id ) ) ) {
		return true;
	}

	return false;
}

function hocwp_theme_is_image( $url, $id = 0 ) {
	if ( HOCWP_Theme::is_positive_number( $id ) ) {
		return wp_attachment_is_image( $id );
	}

	return hocwp_theme_is_image_url( $url );
}

function hocwp_theme_is_image_url( $url ) {
	$img_formats = array( 'png', 'jpg', 'jpeg', 'gif', 'tiff', 'bmp', 'ico' );
	$path_info   = pathinfo( $url );
	$extension   = isset( $path_info['extension'] ) ? $path_info['extension'] : '';
	$extension   = trim( strtolower( $extension ) );

	return in_array( $extension, $img_formats );
}

function hocwp_theme_attachment_path_to_postid( $path ) {
	global $wpdb;
	$upload = wp_upload_dir();
	$path   = str_replace( $upload['basedir'], '', $path );
	$sql    = 'SELECT post_id FROM ';
	$sql .= $wpdb->postmeta;
	$sql .= " WHERE meta_key = '_wp_attached_file' AND meta_value = %s";
	$sql     = $wpdb->prepare( $sql, $path );
	$post_id = $wpdb->get_var( $sql );

	return $post_id;
}

add_filter( 'wp_calculate_image_srcset', '__return_false' );