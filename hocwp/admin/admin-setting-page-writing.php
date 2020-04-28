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

$args = array(
	'class' => 'medium-text',
	'type'  => 'checkbox',
	'label' => __( 'Allow using custom HTML in term description.', 'hocwp-theme' )
);

$field = hocwp_theme_create_setting_field( 'term_html_description', __( 'Term HTML Description', 'hocwp-theme' ), '', $args, 'boolean', 'writing' );
$tab->add_field_array( $field );

function hocwp_theme_admin_setting_page_writing_scripts() {
	HT_Enqueue()->media_upload();
}

add_action( 'hocwp_theme_admin_setting_page_writing_scripts', 'hocwp_theme_admin_setting_page_writing_scripts' );