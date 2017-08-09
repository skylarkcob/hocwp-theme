<?php
function hocwp_theme_upgrade_new_version() {
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

add_action( 'wp_loaded', 'hocwp_theme_upgrade_new_version' );