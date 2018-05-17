<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_wp_login_action( $user_login, $user ) {
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
	$columns['last_login'] = __( 'Last login', 'hocwp-theme' );

	return $columns;
}

add_filter( 'manage_users_columns', 'hocwp_theme_manage_manage_users_columns_filter' );

function hocwp_theme_manage_users_sortable_columns_filter( $columns ) {
	$columns['last_login'] = 'last_login';

	return $columns;
}

add_filter( 'manage_users_sortable_columns', 'hocwp_theme_manage_users_sortable_columns_filter' );

function hocwp_theme_manage_users_custom_column_filter( $value, $column_name, $user_id ) {
	switch ( $column_name ) {
		case 'last_login':
			$value = get_user_meta( $user_id, $column_name, true );
			if ( ! empty( $value ) ) {
				$value = HOCWP_Theme_Utility::timestamp_to_string( $value );
			}
			break;
	}

	return $value;
}

add_filter( 'manage_users_custom_column', 'hocwp_theme_manage_users_custom_column_filter', 10, 3 );

function hocwp_theme_pre_get_users_action( WP_User_Query $query ) {
	$orderby = isset( $_GET['orderby'] ) ? $_GET['orderby'] : '';
	if ( 'last_login' == $orderby ) {
		$query->set( 'orderby', 'meta_value_num' );
		$query->set( 'meta_key', $orderby );
	}
}

if ( is_admin() ) {
	add_action( 'pre_get_users', 'hocwp_theme_pre_get_users_action' );
}

function hocwp_theme_user_contactmethods_filter( $methods ) {
	$methods['facebook']    = __( 'Facebook URL', 'hocwp-theme' );
	$methods['youtube']     = __( 'YouTube URL', 'hocwp-theme' );
	$methods['google_plus'] = __( 'Google Plus URL', 'hocwp-theme' );
	$methods['twitter']     = __( 'Twitter URL', 'hocwp-theme' );
	$methods['donate']      = __( 'Donate URL', 'hocwp-theme' );
	$methods['phone']       = __( 'Phone', 'hocwp-theme' );
	$methods['identity']    = __( 'Identity', 'hocwp-theme' );

	return $methods;
}

add_filter( 'user_contactmethods', 'hocwp_theme_user_contactmethods_filter' );

function hocwp_theme_wp_new_user_notification_email_filter( $data, $user, $blog_name ) {
	if ( $user instanceof WP_User ) {
		$message = isset( $data['message'] ) ? $data['message'] : '';

		preg_match_all( '#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $message, $matches );

		$matches = isset( $matches[0] ) ? $matches[0] : '';

		$matches = (array) $matches;

		if ( isset( $matches[1] ) && ! empty( $matches[0] ) ) {
			$message = str_replace( '<' . $matches[0] . '>', '', $message );
			$url     = '<a href="' . esc_url( $matches[0] ) . '" target="_blank">' . $matches[1] . '</a>' . PHP_EOL;
			$message = str_replace( $matches[1], $url, $message );
		}

		if ( isset( $matches[0] ) && ! empty( $matches[0] ) ) {
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
	if ( ! isset( $data['headers'] ) || empty( $data['headers'] ) ) {
		$data['headers'] = "Content-Type: text/html; charset=UTF-8\r\n";
		$data['message'] = wpautop( $data['message'] );
	}

	return $data;
}

add_filter( 'wp_mail', 'hocwp_theme_wp_mail_filter' );