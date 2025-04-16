<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_wp_login_action( $user_login, $user ) {
	if ( ! ( $user instanceof WP_User ) ) {
		$user = get_user_by( 'login', $user_login );
	}

	update_user_meta( $user->ID, 'last_login', time() );
}

add_action( 'wp_login', 'hocwp_theme_wp_login_action', 10, 2 );

function hocwp_theme_track_user_last_activity() {
	if ( is_user_logged_in() ) {
		$user = wp_get_current_user();
		update_user_meta( $user->ID, 'last_activity', time() );
	}
}

add_action( 'init', 'hocwp_theme_track_user_last_activity' );

function hocwp_theme_manage_manage_users_columns_filter( $columns ) {
	$columns['last_login']    = __( 'Last login', 'hocwp-theme' );
	$columns['last_activity'] = __( 'Last Activity', 'hocwp-theme' );

	return $columns;
}

add_filter( 'manage_users_columns', 'hocwp_theme_manage_manage_users_columns_filter' );

function hocwp_theme_manage_users_sortable_columns_filter( $columns ) {
	$columns['last_login']    = 'last_login';
	$columns['last_activity'] = 'last_activity';

	return $columns;
}

add_filter( 'manage_users_sortable_columns', 'hocwp_theme_manage_users_sortable_columns_filter' );

function hocwp_theme_manage_users_custom_column_filter( $value, $column_name, $user_id ) {
	switch ( $column_name ) {
		case 'last_activity':
		case 'last_login':
			$value = get_user_meta( $user_id, $column_name, true );

			if ( ! empty( $value ) ) {
				$value = ht_util()->timestamp_to_string( $value );
			}

			break;
	}

	return $value;
}

add_filter( 'manage_users_custom_column', 'hocwp_theme_manage_users_custom_column_filter', 10, 3 );

function hocwp_theme_pre_get_users_action( WP_User_Query $query ) {
	$orderby = $_GET['orderby'] ?? '';

	if ( 'last_login' == $orderby || 'last_activity' == $orderby ) {
		$query->set( 'orderby', 'meta_value_num' );
		$query->set( 'meta_key', $orderby );
	}
}

if ( is_admin() ) {
	add_action( 'pre_get_users', 'hocwp_theme_pre_get_users_action' );
}

function hocwp_theme_wp_new_user_notification_email_filter( $data, $user, $blog_name ) {
	if ( $user instanceof WP_User ) {
		$message = $data['message'] ?? '';

		preg_match_all( '#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $message, $matches );

		$matches = $matches[0] ?? '';

		$matches = (array) $matches;

		if ( isset( $matches[1] ) && ! empty( $matches[0] ) ) {
			$message = str_replace( '<' . $matches[0] . '>', '', $message );
			$url     = '<a href="' . esc_url( $matches[0] ) . '" target="_blank">' . $matches[1] . '</a>' . PHP_EOL;
			$message = str_replace( $matches[1], $url, $message );
		}

		if ( ! empty( $matches[0] ) ) {
			if ( ! empty( $message ) ) {
				$message .= PHP_EOL;
			}

			$message .= __( 'If the link above not working, just copy the link below and paste it to browser address bar:', 'hocwp-theme' ) . PHP_EOL;
			$message .= $matches[0];
		}

		$data['message'] = $message;
	}

	return $data;
}

add_filter( 'wp_new_user_notification_email', 'hocwp_theme_wp_new_user_notification_email_filter', 10, 3 );

function hocwp_theme_wp_mail_filter( $data ) {
	if ( empty( $data['headers'] ) ) {
		$data['headers'] = "Content-Type: text/html; charset=UTF-8\r\n";
		$data['message'] = wpautop( $data['message'] );
	}

	return $data;
}

add_filter( 'wp_mail', 'hocwp_theme_wp_mail_filter' );

function hocwp_theme_verify_user_notification( $key, $user ) {
	$user = ht_util()->return_user( $user );

	if ( $user instanceof WP_User ) {
		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		$switched_locale = switch_to_locale( get_user_locale( $user ) );

		$url = home_url();

		$params = array(
			'action'  => 'verify_email',
			'key'     => $key,
			'user_id' => $user->ID
		);

		$url = add_query_arg( $params, $url );

		$message = sprintf( __( 'Username: %s', 'hocwp-theme' ), $user->user_login ) . "\r\n\r\n";
		$message .= __( 'To verify your email address, visit the following link:', 'hocwp-theme' ) . "\r\n\r\n";
		$message .= sprintf( '<a href="%s">%s</a>', $url, $url ) . "\r\n\r\n";

		$notification_email = array(
			'to'      => $user->user_email,
			'subject' => __( '[%s] Verify your email address', 'hocwp-theme' ),
			'message' => $message,
			'headers' => '',
		);

		$notification_email = apply_filters( 'hocwp_theme_verify_user_notification_email', $notification_email, $user, $blogname );

		$notification_email['subject'] = wp_specialchars_decode( sprintf( $notification_email['subject'], $blogname ) );

		$sent = ht_util()->html_mail( $notification_email['to'], $notification_email['subject'], $notification_email['message'], $notification_email['headers'] );

		if ( $switched_locale ) {
			restore_previous_locale();
		}

		return $sent;
	}

	return false;
}

/**
 * Search and delete invalid WordPress users.
 *
 * @return void
 */
function hocwp_theme_remove_invalid_user() {
	global $wpdb;

	// On WordPress 6.8 or later, the password is encrypted using a new hashing algorithm. (Found on 16/04/2025)
	$ids = $wpdb->get_col( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_email = '' OR (user_pass NOT LIKE %s AND user_pass NOT LIKE %s)", '$P$%', '$wp$2y$10$%' ) );

	if ( ht()->array_has_value( $ids ) ) {
		foreach ( $ids as $user_id ) {
			$user = new WP_User( $user_id );

			if ( empty( $user->get_role_caps() ) || user_can( $user_id, 'publish_posts' ) ) {
				wp_delete_user( $user_id );
			}
		}
	}
}

add_action( 'user_register', 'hocwp_theme_remove_invalid_user' );
add_action( 'wp_login', 'hocwp_theme_remove_invalid_user' );