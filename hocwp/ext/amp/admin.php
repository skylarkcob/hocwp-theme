<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ht_admin()->is_theme_option_page() ) {
	require( HOCWP_EXT_AMP_PATH . '/admin-setting-page.php' );
}