<?php
function hocwp_theme_custom_widgets_init_action() {
	$args = array(
		'id'   => 'footer',
		'name' => __( 'Footer', 'hocwp-theme' )
	);
	$args = HT_Util()->sidebar_args( $args );
	register_sidebar( $args );
}

add_action( 'widgets_init', 'hocwp_theme_custom_widgets_init_action' );

function hocwp_theme_custom_after_setup_theme_action() {
	add_image_size( 'slider', 1100, 180 );
}

add_action( 'after_setup_theme', 'hocwp_theme_custom_after_setup_theme_action' );