<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_settings_page_media_tab( $tabs ) {
	$tabs['media'] = array(
		'text' => __( 'Media', 'hocwp-theme' ),
		'icon' => '<span class="dashicons dashicons-paperclip"></span>'
	);

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_media_tab' );

if ( 'media' != hocwp_theme_object()->option->tab ) {
	return;
}

function hocwp_theme_settings_page_media_field() {
	$fields = array();

	$args = array(
		'type'        => 'number',
		'class'       => 'small-text',
		'description' => __( 'The maximum media files upload per day for each member.', 'hocwp-theme' )
	);

	$field    = hocwp_theme_create_setting_field( 'upload_per_day', __( 'Upload Per Day', 'hocwp-theme' ), 'input', $args, 'positive_integer', 'media' );
	$fields[] = $field;

	$args['min'] = 0;
	$args['max'] = 100;

	$args['description'] = __( 'The right JPEG quality will ensure your images look great, your pages load fast and even help you rank well in search engines.', 'hocwp-theme' );

	$field    = hocwp_theme_create_setting_field( 'jpeg_quality', __( 'JPEG Quality', 'hocwp-theme' ), 'input', $args, 'positive_integer', 'media' );
	$fields[] = $field;

	$args['min']     = 0;
	$args['max']     = 99999;
	$args['default'] = 2560;
	$args['class']   = 'medium-text';

	$args['description'] = __( 'If the original image width or height is above the threshold, it will be scaled down. The threshold is used as max width and max height.', 'hocwp-theme' );

	$field    = hocwp_theme_create_setting_field( 'big_image_size_threshold', __( 'Big Image Size Threshold', 'hocwp-theme' ), 'input', $args, 'positive_integer', 'media' );
	$fields[] = $field;

	$sizes = ht_util()->get_image_sizes();

	$args = array();

	foreach ( $sizes as $key => $size ) {
		if ( false === strpos( $key, 'woocommerce' ) && false === strpos( $key, 'shop' ) ) {
			$title = str_replace( '_', ' ', $key );
			$title = str_replace( '-', ' ', $title );
			$title = ucwords( $title );

			$args['default'] = $size;

			$field    = new HOCWP_Theme_Admin_Setting_Field( 'size_' . $key, $title, 'image_size', $args, 'array', 'media', 'image_sizes' );
			$fields[] = $field;
		}
	}

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_media_settings_field', 'hocwp_theme_settings_page_media_field' );

function hocwp_theme_settings_page_media_section_filter() {
	$sections = array();

	$sections['image_sizes'] = array(
		'tab'         => 'media',
		'id'          => 'image_sizes',
		'title'       => __( 'Image Sizes', 'hocwp-theme' ),
		'description' => __( 'Image size settings, including width, height and image crop function.', 'hocwp-theme' )
	);

	return $sections;
}

add_filter( 'hocwp_theme_settings_page_media_settings_section', 'hocwp_theme_settings_page_media_section_filter' );

function hocwp_theme_update_option_media_action( $old_value, $value ) {
	$tab = $_REQUEST['tab'] ?? '';

	// Sync all media sizes
	if ( 'media' == $tab ) {
		$media = $value['media'] ?? '';

		$sizes = array( 'thumbnail', 'medium', 'large' );

		foreach ( $sizes as $s ) {
			$size = $media[ 'size_' . $s ] ?? '';

			$w = $size['width'] ?? '';
			$h = $size['height'] ?? '';
			$c = $size['crop'] ?? 0;

			if ( is_numeric( $w ) ) {
				if ( ! is_numeric( $h ) ) {
					$h = $w;
				}

				update_option( $s . '_size_w', $w );
				update_option( $s . '_size_h', $h );
				update_option( $s . '_crop', $c );
			}
		}
	}
}

add_action( 'update_option_hocwp_theme', 'hocwp_theme_update_option_media_action', 10, 2 );