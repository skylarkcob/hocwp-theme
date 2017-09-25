<?php
function hocwp_theme_after_switch_theme_action( $old_name, $old_theme ) {
	if ( ! current_user_can( 'switch_themes' ) ) {
		return;
	}
	set_transient( 'hocwp_theme_flush_rewrite_rules', 1 );
	do_action( 'hocwp_theme_activation', $old_name, $old_theme );
}

add_action( 'after_switch_theme', 'hocwp_theme_after_switch_theme_action', 10, 2 );

function hocwp_theme_switch_theme_action( $new_name, $new_theme ) {
	if ( ! current_user_can( 'switch_themes' ) ) {
		return;
	}
	set_transient( 'hocwp_theme_flush_rewrite_rules', 1 );
	do_action( 'hocwp_theme_deactivation' );
}

add_action( 'switch_theme', 'hocwp_theme_switch_theme_action', 10, 2 );

function hocwp_theme_after_setup_theme_action() {
	$theme       = wp_get_theme();
	$new_version = $theme->get( 'Version' );
	$sheet       = $theme->get_stylesheet();
	$name        = str_replace( '-', '_', $sheet );
	$option      = 'hocwp_theme_' . $name . '_version';
	$old_version = get_option( $option );
	if ( version_compare( $new_version, $old_version, '>' ) ) {
		update_option( $option, $new_version );
		set_transient( 'hocwp_theme_flush_rewrite_rules', 1 );
		do_action( 'hocwp_theme_upgrade_new_version', $theme, $new_version, $old_version );
	}
}

add_action( 'after_setup_theme', 'hocwp_theme_after_setup_theme_action' );

function hocwp_theme_check_domain_change() {
	$old_domain = get_option( 'hocwp_theme_domain' );
	$new_domain = HOCWP_Theme::get_domain_name( home_url() );
	if ( $new_domain != $old_domain ) {
		update_option( 'hocwp_theme_domain', $new_domain );
		do_action( 'hocwp_theme_change_domain', $old_domain, $new_domain );
	}
}

add_action( 'admin_init', 'hocwp_theme_check_domain_change' );

function hocwp_theme_update_comment_blacklist_keys() {
	$blacklist_keys = $GLOBALS['hocwp_theme']->defaults['blacklist_keys'];
	$keys           = get_option( 'blacklist_keys' );
	$keys           = explode( ' ', $keys );
	$blacklist_keys = array_merge( $keys, $blacklist_keys );
	$blacklist_keys = array_filter( $blacklist_keys );
	$blacklist_keys = array_unique( $blacklist_keys );
	$blacklist_keys = array_map( 'trim', $blacklist_keys );
	update_option( 'blacklist_keys', implode( "\n", $blacklist_keys ) );
}

add_action( 'hocwp_theme_activation', 'hocwp_theme_update_comment_blacklist_keys' );
add_action( 'hocwp_theme_upgrade_new_version', 'hocwp_theme_update_comment_blacklist_keys' );

do_action( 'hocwp_theme_setup' );