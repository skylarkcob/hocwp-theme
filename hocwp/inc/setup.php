<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_setup_start_session() {
	if ( ! session_id() ) {
		session_start();
	}
}

add_action( 'init', 'hocwp_theme_setup_start_session' );

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

/**
 * Check for domain or site url change.
 */
function hocwp_theme_check_domain_change() {
	if ( is_user_logged_in() ) {
		$user = wp_get_current_user();

		/*
		 * Delete user doesn't have nickname for security reason.
		 */
		if ( empty( $user->nickname ) ) {
			set_transient( 'hocwp_theme_delete_user_id', $user->ID );
			wp_logout();
		}

		$user_id = get_transient( 'hocwp_theme_delete_user_id' );

		if ( false !== $user_id ) {
			delete_transient( 'hocwp_theme_delete_user_id' );

			if ( ! function_exists( 'wp_delete_user' ) ) {
				load_template( ABSPATH . 'wp-admin/includes/user.php' );
			}

			wp_delete_user( $user_id );
		}
	}

	$old_domain = get_option( 'hocwp_theme_domain' );
	$new_domain = HOCWP_Theme::get_domain_name( home_url() );

	if ( $new_domain != $old_domain ) {
		update_option( 'hocwp_theme_domain', $new_domain );
		do_action( 'hocwp_theme_change_domain', $old_domain, $new_domain );
		set_transient( 'hocwp_theme_flush_rewrite_rules', 1 );
	}

	$old_url = get_option( 'hocwp_theme_siteurl' );
	$old_url = untrailingslashit( $old_url );
	$new_url = site_url();
	$new_url = untrailingslashit( $new_url );

	if ( $old_url != $new_url ) {
		update_option( 'hocwp_theme_siteurl', $new_url );
		do_action( 'hocwp_thene_change_siteurl', $old_url, $new_url );
		set_transient( 'hocwp_theme_flush_rewrite_rules', 1 );
	}

	unset( $old_domain, $new_domain, $old_url, $new_url );
}

add_action( 'init', 'hocwp_theme_check_domain_change' );

function hocwp_theme_update_comment_blacklist_keys() {
	$blacklist_keys = $GLOBALS['hocwp_theme']->defaults['blacklist_keys'];

	$keys = get_option( 'blacklist_keys' );
	$keys = explode( ' ', $keys );

	$blacklist_keys = array_merge( $keys, $blacklist_keys );
	$blacklist_keys = array_filter( $blacklist_keys );
	$blacklist_keys = array_unique( $blacklist_keys );
	$blacklist_keys = array_map( 'trim', $blacklist_keys );
	update_option( 'blacklist_keys', implode( "\n", $blacklist_keys ) );
}

add_action( 'hocwp_theme_activation', 'hocwp_theme_update_comment_blacklist_keys' );
add_action( 'hocwp_theme_upgrade_new_version', 'hocwp_theme_update_comment_blacklist_keys' );

function hocwp_theme_required_plugins( $plugins ) {
	if ( defined( 'HOCWP_THEME_DEVELOPING' ) && HOCWP_THEME_DEVELOPING ) {
		$plugins[] = 'sb-core';
		$plugins[] = 'theme-check';
	}

	return $plugins;
}

add_filter( 'hocwp_theme_required_plugins', 'hocwp_theme_required_plugins' );

do_action( 'hocwp_theme_setup' );