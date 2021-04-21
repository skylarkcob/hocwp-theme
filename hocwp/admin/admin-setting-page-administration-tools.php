<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$tab = new HOCWP_Theme_Admin_Setting_Tab( 'administration_tools', __( 'Administration Tools', 'hocwp-theme' ), '<span class="dashicons dashicons-admin-tools"></span>', array(), 999999999 );

$tab->submit_button = false;

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

$tab->load_script( 'jquery' );
$tab->load_script( 'hocwp-theme' );
$tab->load_script( 'hocwp-theme-ajax-button' );

function hocwp_theme_setting_page_administration_tools_script() {
	wp_enqueue_script( 'hocwp-theme-administration-tools', HOCWP_Theme()->core_url . '/js/admin-administration-tools.js', array( 'jquery' ), false, true );
}

add_action( 'hocwp_theme_admin_setting_page_' . $tab->name . '_scripts', 'hocwp_theme_setting_page_administration_tools_script' );