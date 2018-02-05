<?php
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