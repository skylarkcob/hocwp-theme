<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$tab = new HOCWP_Theme_Admin_Setting_Tab( 'writing', __( 'Writing', 'hocwp-theme' ), '<span class="dashicons dashicons-edit"></span>' );

$tab->add_field_array( array(
	'id'    => 'default_thumbnail',
	'title' => __( 'Default Thumbnail', 'hocwp-theme' ),
	'args'  => array(
		'type'          => 'positive_integer',
		'callback'      => array( 'HOCWP_Theme_HTML_Field', 'media_upload' ),
		'callback_args' => array()
	)
) );

function hocwp_theme_admin_setting_page_writing_scripts() {
	HT_Enqueue()->media_upload();
}

add_action( 'hocwp_theme_admin_setting_page_writing_scripts', 'hocwp_theme_admin_setting_page_writing_scripts' );