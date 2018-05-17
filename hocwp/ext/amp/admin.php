<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( HT_Admin()->is_admin_page( 'themes.php', 'hocwp_theme' ) ) {
	require HOCWP_EXT_AMP_PATH . '/admin-setting-page.php';
}