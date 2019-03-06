<?php
define( 'DOING_AJAX', true );

if ( ! defined( 'WP_ADMIN' ) ) {
	define( 'WP_ADMIN', true );
}

if ( ! defined( 'WP_USE_THEMES' ) ) {
	define( 'WP_USE_THEMES', false );
}

$_SERVER['PHP_SELF'] = '/wp-admin/';

$path = dirname( __FILE__ );
$path = substr( $path, 0, strpos( $path, 'wp-content' ) );

/** Load WordPress Bootstrap */
require_once( $path . 'wp-load.php' );

/** Allow for cross-domain requests (from the front end). */
if ( ! function_exists( 'send_origin_headers' ) ) {
	require_once ABSPATH . WPINC . '/link-template.php';
	require_once ABSPATH . WPINC . '/http.php';
}

send_origin_headers();

// Require an action parameter
if ( empty( $_REQUEST['action'] ) ) {
	wp_die( '0', 400 );
}

/** Load WordPress Administration APIs */
require_once( ABSPATH . 'wp-admin/includes/admin.php' );

/** Load Ajax Handlers for WordPress Core */
require_once( ABSPATH . 'wp-admin/includes/ajax-actions.php' );

@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
@header( 'X-Robots-Tag: noindex' );

send_nosniff_header();
nocache_headers();

/** This action is documented in wp-admin/admin.php */
//do_action( 'admin_init' );

if ( is_user_logged_in() ) {
	// If no action is registered, return a Bad Request response.
	if ( ! has_action( 'wp_ajax_' . $_REQUEST['action'] ) ) {
		wp_die( '0', 400 );
	}

	/**
	 * Fires authenticated Ajax actions for logged-in users.
	 *
	 * The dynamic portion of the hook name, `$_REQUEST['action']`,
	 * refers to the name of the Ajax action callback being fired.
	 *
	 * @since 2.1.0
	 */
	do_action( 'wp_ajax_' . $_REQUEST['action'] );
} else {
	// If no action is registered, return a Bad Request response.
	if ( ! has_action( 'wp_ajax_nopriv_' . $_REQUEST['action'] ) ) {
		wp_die( '0', 400 );
	}

	/**
	 * Fires non-authenticated Ajax actions for logged-out users.
	 *
	 * The dynamic portion of the hook name, `$_REQUEST['action']`,
	 * refers to the name of the Ajax action callback being fired.
	 *
	 * @since 2.8.0
	 */
	do_action( 'wp_ajax_nopriv_' . $_REQUEST['action'] );
}

// Default status
wp_die( '0' );