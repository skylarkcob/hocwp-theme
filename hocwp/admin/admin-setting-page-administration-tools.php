<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! current_user_can( 'manage_options' ) ) {
	return;
}

$tab = new HOCWP_Theme_Admin_Setting_Tab( 'administration_tools', __( 'Administration Tools', 'hocwp-theme' ), '<span class="dashicons dashicons-admin-tools"></span>', array(), 99999 );

$tab->submit_button = false;

$args = array(
	'title'       => __( 'Update Administrative Email', 'hocwp-theme' ),
	'description' => __( 'Changing admin email address does not require confirmation.', 'hocwp-theme' )
);

$tab->add_section( 'administrative_email', $args );

$tab->add_field( 'new_email', __( 'New Email', 'hocwp-theme' ), 'input', array( 'type' => 'email' ), 'string', 'administrative_email' );

$args = array(
	'attributes'  => array(
		'data-ajax-button'  => 1,
		'data-message'      => __( 'Admin email has been changed successfully!', 'hocwp-theme' ),
		'data-change-email' => 1
	),
	'button_type' => 'button',
	'text'        => __( 'Change', 'hocwp-theme' )
);

$tab->add_field( 'change_admin_email', '', 'button', $args, 'string', 'administrative_email' );

$args = array(
	'title'       => __( 'Change URL', 'hocwp-theme' ),
	'description' => __( 'Change all old URLs in database into new URL.', 'hocwp-theme' )
);

$tab->add_section( 'change_url', $args );

$tab->add_field( 'old_url', __( 'Old URL', 'hocwp-theme' ), 'input', array(), 'string', 'change_url' );
$tab->add_field( 'new_url', __( 'New URL', 'hocwp-theme' ), 'input', array(), 'string', 'change_url' );

$args = array(
	'attributes'  => array(
		'data-ajax-button'     => 1,
		'data-message'         => __( 'All URLs have been changed successfully!', 'hocwp-theme' ),
		'data-confirm-message' => __( 'Please make a backup before you change site URL.', 'hocwp-theme' ),
		'data-change-url'      => 1
	),
	'button_type' => 'button'
);

$tab->add_field( 'submit_change_url', '', 'button', $args, 'string', 'change_url' );

$args = array(
	'title'       => __( 'Vietnamese Administrative Boundaries', 'hocwp-theme' ),
	'description' => __( 'Import information about Vietnamese administrative boundaries into the database automatically.', 'hocwp-theme' )
);

$tab->add_section( 'vn_administrative_boundaries', $args );

$args = array(
	'type' => 'checkbox',
	'text' => __( 'Import the administrative boundary at the district level?', 'hocwp-theme' )
);

$tab->add_field( 'district', __( 'District', 'hocwp-theme' ), 'input', $args, 'boolean', 'vn_administrative_boundaries' );

$args = array(
	'type' => 'checkbox',
	'text' => __( 'Import the administrative boundary at the commune level?', 'hocwp-theme' )
);

$tab->add_field( 'commune', __( 'Commune', 'hocwp-theme' ), 'input', $args, 'boolean', 'vn_administrative_boundaries' );

$taxs = get_taxonomies( array(), 'objects' );

$options = array(
	'' => __( 'Choose taxonomy', 'hocwp-theme' )
);

foreach ( $taxs as $tax ) {
	if ( $tax instanceof WP_Taxonomy ) {
		$options[ $tax->name ] = sprintf( '%s (%s)', $tax->labels->singular_name, $tax->name );
	}
}

$args = array(
	'options' => $options
);

$tab->add_field( 'ab_taxonomy', __( 'Taxonomy', 'hocwp-theme' ), 'select', $args, 'string', 'vn_administrative_boundaries' );

$args = array(
	'attributes'  => array(
		'data-ajax-button' => 1,
		'data-message'     => __( 'Data has been imported successfully!', 'hocwp-theme' ),
		'data-import-ab'   => 1
	),
	'button_type' => 'button',
	'text'        => __( 'Import', 'hocwp-theme' )
);

$tab->add_field( 'import_ab', '', 'button', $args, 'string', 'vn_administrative_boundaries' );

$tab->load_script( 'jquery' );
$tab->load_script( 'hocwp-theme' );
$tab->load_script( 'hocwp-theme-ajax-button' );

function hocwp_theme_setting_page_administration_tools_script() {
	wp_enqueue_script( 'hocwp-theme-administration-tools', HOCWP_Theme()->core_url . '/js/admin-administration-tools.js', array( 'jquery' ), false, true );
}

add_action( 'hocwp_theme_admin_setting_page_' . $tab->name . '_scripts', 'hocwp_theme_setting_page_administration_tools_script' );