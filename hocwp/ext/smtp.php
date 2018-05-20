<?php
/*
 * Name: SMTP Email
 * Description: Sending mail by using SMTP.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_load_extension_smtp() {
	$load = HT_extension()->is_active( __FILE__ );
	$load = apply_filters( 'hocwp_theme_load_extension_smtp', $load );

	return $load;
}

$load = hocwp_theme_load_extension_smtp();
if ( ! $load ) {
	return;
}

function hocwp_theme_wp_mail_from_name_filter( $name ) {
	global $hocwp_theme;
	if ( isset( $hocwp_theme->options['smtp']['from_name'] ) && ! empty( $hocwp_theme->options['smtp']['from_name'] ) ) {
		$name = $hocwp_theme->options['smtp']['from_name'];
	}

	return $name;
}

add_filter( 'wp_mail_from_name', 'hocwp_theme_wp_mail_from_name_filter' );

function hocwp_theme_wp_mail_from_filter( $email ) {
	global $hocwp_theme;
	if ( isset( $hocwp_theme->options['smtp']['from_email'] ) && is_email( $hocwp_theme->options['smtp']['from_email'] ) ) {
		$email = sanitize_email( $hocwp_theme->options['smtp']['from_email'] );
	}

	return $email;
}

add_filter( 'wp_mail_from', 'hocwp_theme_wp_mail_from_filter' );

function hocwp_theme_phpmailer_init_action( $phpmailer ) {
	if ( ! ( $phpmailer instanceof PHPMailer ) ) {
		return;
	}
	global $hocwp_theme;
	$data              = $hocwp_theme->options['smtp'];
	$phpmailer->Mailer = 'smtp';
	if ( isset( $data['return_path'] ) && (bool) $data['return_path'] ) {
		$phpmailer->Sender = $phpmailer->From;
	}
	$encryption             = isset( $data['encryption'] ) ? $data['encryption'] : 'ssl';
	$host                   = isset( $data['host'] ) ? $data['host'] : '';
	$port                   = isset( $data['port'] ) ? $data['port'] : 25;
	$username               = isset( $data['username'] ) ? $data['username'] : '';
	$password               = isset( $data['password'] ) ? $data['password'] : '';
	$phpmailer->SMTPSecure  = ( $encryption == 'none' ) ? '' : $encryption;
	$phpmailer->Host        = $host;
	$phpmailer->Port        = $port;
	$phpmailer->SMTPAuth    = true;
	$phpmailer->Username    = $username;
	$phpmailer->Password    = $password;
	$phpmailer->SMTPOptions = array(
		'ssl' => array(
			'verify_peer'       => false,
			'verify_peer_name'  => false,
			'allow_self_signed' => true
		)
	);
}

add_action( 'phpmailer_init', 'hocwp_theme_phpmailer_init_action' );

function hocwp_theme_wp_mail_content_type_filter() {
	return 'text/html';
}

add_filter( 'wp_mail_content_type', 'hocwp_theme_wp_mail_content_type_filter', 99 );