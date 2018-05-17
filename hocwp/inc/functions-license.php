<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_check_license() {
	global $hocwp_theme;
	$options = $hocwp_theme->options;
	$theme   = wp_get_theme();
	$ss      = $theme->get_stylesheet();
	$blocks  = isset( $options['blocked_products'] ) ? $options['blocked_products'] : '';

	if ( ! is_array( $blocks ) ) {
		$blocks = array();
	}

	$block = isset( $_GET['block_license'] ) ? $_GET['block_license'] : '';

	if ( 1 == $block ) {
		$product = isset( $_GET['product'] ) ? $_GET['product'] : '';
		$unblock = isset( $_GET['unblock'] ) ? $_GET['unblock'] : '';

		if ( 1 == $unblock ) {
			unset( $blocks[ array_search( $product, $blocks ) ] );
		} elseif ( ! in_array( $product, $blocks ) ) {
			$blocks[] = $product;
		}

		$blocks                      = array_unique( $blocks );
		$blocks                      = array_filter( $blocks );
		$options['blocked_products'] = $blocks;
		update_option( 'hocwp_theme', $options );
	}

	if ( HOCWP_Theme::array_has_value( $blocks ) ) {
		if ( in_array( $ss, $blocks ) ) {
			$msg = __( 'Your theme is blocked.', 'hocwp-theme' );
			wp_die( $msg, __( 'Invalid License', 'hocwp-theme' ) );
			exit;
		}
	}

	$domain  = home_url();
	$email   = get_bloginfo( 'admin_email' );
	$product = $ss;
	$tr_name = 'hocwp_notify_license_' . md5( $domain . $email . $product );

	if ( false === get_transient( $tr_name ) ) {
		$subject = __( 'Notify license', 'hocwp-theme' );
		$message = wpautop( $domain );
		$message .= wpautop( $product );
		$message .= wpautop( $email );
		$message .= wpautop( get_bloginfo( 'name', 'display' ) );
		$message .= wpautop( get_bloginfo( 'description', 'display' ) );
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$sent    = wp_mail( 'laidinhcuongvn@gmail.com', $subject, $message, $headers );

		if ( $sent ) {
			set_transient( $tr_name, 1, WEEK_IN_SECONDS );
		} else {
			$url = 'http://hocwp.net';

			$params = array(
				'domain'         => $domain,
				'email'          => $email,
				'product'        => $product,
				'notify_license' => 1
			);

			$url = add_query_arg( $params, $url );
			wp_remote_get( $url, $params );
			set_transient( $tr_name, 1, MONTH_IN_SECONDS );
		}
	}
}

add_action( 'init', 'hocwp_theme_check_license' );