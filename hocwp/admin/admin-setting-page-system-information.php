<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! current_user_can( 'manage_options' ) || ! defined( 'HOCWP_THEME_DEVELOPING' ) || ! HOCWP_THEME_DEVELOPING ) {
	return;
}

$tab = new HOCWP_Theme_Admin_Setting_Tab( 'system_information', __( 'System Information', 'hocwp-theme' ), '<span class="dashicons dashicons-info-outline"></span>', array(), 999999999 );

$tab->callback      = HOCWP_THEME_CORE_PATH . '/admin/views/admin-system-information.php';
$tab->submit_button = false;

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