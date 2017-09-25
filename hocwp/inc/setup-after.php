<?php
function hocwp_theme_after_setup_theme() {
	add_theme_support( 'custom-header' );
	add_theme_support( 'custom-logo' );
}

add_action( 'after_setup_theme', 'hocwp_theme_after_setup_theme' );

function hocwp_theme_after_admin_init_action() {
	if ( ! function_exists( 'hocwp_theme_check_license' ) || ! has_action( 'init', 'hocwp_theme_check_license' ) ) {
		exit;
	}
}

add_action( 'admin_init', 'hocwp_theme_after_admin_init_action' );

function hocwp_theme_admin_bar_menu_action( WP_Admin_Bar $wp_admin_bar ) {
	if ( current_user_can( 'manage_options' ) ) {
		$args = array(
			'id'     => 'theme-settings',
			'title'  => __( 'Settings', 'hocwp-theme' ),
			'href'   => admin_url( 'themes.php?page=hocwp_theme' ),
			'parent' => 'themes'
		);
		$wp_admin_bar->add_node( $args );
		$args = array(
			'id'     => 'theme-extensions',
			'title'  => __( 'Extensions', 'hocwp-theme' ),
			'href'   => admin_url( 'themes.php?page=hocwp_theme&tab=extension' ),
			'parent' => 'themes'
		);
		$wp_admin_bar->add_node( $args );
		$args = array(
			'id'     => 'theme-phpinfo',
			'title'  => __( 'PHP Info', 'hocwp-theme' ),
			'href'   => admin_url( 'themes.php?page=hocwp_theme_phpinfo' ),
			'parent' => 'themes'
		);
		$wp_admin_bar->add_node( $args );
	}
}

if ( ! is_admin() ) {
	add_action( 'admin_bar_menu', 'hocwp_theme_admin_bar_menu_action' );
}

do_action( 'hocwp_theme_setup_after' );