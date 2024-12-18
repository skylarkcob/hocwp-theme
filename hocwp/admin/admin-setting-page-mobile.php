<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$tab = new HOCWP_Theme_Admin_Setting_Tab( 'mobile', __( 'Mobile', 'hocwp-theme' ), '<span class="dashicons dashicons-smartphone"></span>' );


function hocwp_theme_admin_setting_page_mobile_scripts() {
	ht_enqueue()->media_upload();
}

add_action( 'hocwp_theme_admin_setting_page_mobile_scripts', 'hocwp_theme_admin_setting_page_mobile_scripts' );