<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_preprocess_comment_filter( $commentdata ) {
	if ( isset( $commentdata['comment_author_url'] ) && ! empty( $commentdata['comment_author_url'] ) ) {
		$domain                            = HOCWP_Theme::get_domain_name( $commentdata['comment_author_url'] );
		$commentdata['comment_author_url'] = $domain;
	}

	return $commentdata;
}

add_filter( 'preprocess_comment', 'hocwp_theme_preprocess_comment_filter' );

function hocwp_theme_wp_handle_upload_prefilter_filter( $file ) {
	$file_name = isset( $file['name'] ) ? $file['name'] : '';
	$info      = pathinfo( $file_name );
	$file_name = sanitize_title( $info['filename'] );
	if ( isset( $info['extension'] ) && ! empty( $info['extension'] ) ) {
		$file_name .= '.' . $info['extension'];
	}
	$file['name'] = $file_name;

	return $file;
}

add_filter( 'wp_handle_upload_prefilter', 'hocwp_theme_wp_handle_upload_prefilter_filter' );