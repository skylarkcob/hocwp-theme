<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! ( current_user_can( 'manage_options' ) || ( defined( 'HOCWP_THEME_DEVELOPING' ) && HOCWP_THEME_DEVELOPING ) ) ) {
	return;
}

$tab = new HOCWP_Theme_Admin_Setting_Tab( 'system_information', __( 'System Information', 'hocwp-theme' ), '<span class="dashicons dashicons-info-outline"></span>', array(), 999999999 );

$tab->callback      = HOCWP_THEME_CORE_PATH . '/admin/views/admin-system-information.php';
$tab->submit_button = false;