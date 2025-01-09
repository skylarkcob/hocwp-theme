<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( function_exists( 'hocwp_theme_load_views' ) ) {
	hocwp_theme_load_views( 'module-header' );
}

do_action( 'ht/module/header' );