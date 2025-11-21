<?php
defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( basename( __DIR__ ), get_template_directory() . '/custom/languages' );
} );

if ( file_exists( __DIR__ . '/hocwp/load.php' ) ) {
	require_once( __DIR__ . '/hocwp/load.php' );
}