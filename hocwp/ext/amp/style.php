<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HT_Minify' ) ) {
	require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-minify.php';
}

$style = HT_Util()->get_theme_option( 'custom_css', '', 'amp' );
$style = HT_Minify()->css( $style );
echo $style;