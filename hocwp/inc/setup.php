<?php
function hocwp_theme_after_switch_theme_action( $old_name, $old_theme ) {
	if ( ! current_user_can( 'switch_themes' ) ) {
		return;
	}
	flush_rewrite_rules();
	do_action( 'hocwp_theme_activation', $old_name, $old_theme );
}

add_action( 'after_switch_theme', 'hocwp_theme_after_switch_theme_action', 10, 2 );

function hocwp_theme_switch_theme_action( $new_name, $new_theme ) {
	if ( ! current_user_can( 'switch_themes' ) ) {
		return;
	}
	flush_rewrite_rules();
	do_action( 'hocwp_theme_deactivation' );
}

add_action( 'switch_theme', 'hocwp_theme_switch_theme_action', 10, 2 );

function hocwp_theme_wp_loaded_action() {
	$theme       = wp_get_theme();
	$new_version = $theme->get( 'Version' );
	$sheet       = $theme->get_stylesheet();
	$name        = str_replace( '-', '_', $sheet );
	$option      = 'hocwp_theme_' . $name . '_version';
	$old_version = get_option( $option );
	if ( version_compare( $new_version, $old_version, '>' ) ) {
		update_option( $option, $new_version );
		do_action( 'hocwp_theme_upgrade_new_version', $theme, $new_version, $old_version );
	}
}

add_action( 'wp_loaded', 'hocwp_theme_wp_loaded_action' );