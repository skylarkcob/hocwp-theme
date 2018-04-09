<?php
function hocwp_theme_custom_after_setup_theme_action() {
	add_theme_support( 'post-formats', array( 'gallery', 'video' ) );
}

add_action( 'after_setup_theme', 'hocwp_theme_custom_after_setup_theme_action' );