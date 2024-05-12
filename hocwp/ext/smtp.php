<?php
/*
 * Name: SMTP Email
 * Description: Sending mail by using SMTP.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'hocwp_theme_load_extension_smtp' ) ) {
	function hocwp_theme_load_extension_smtp() {
		$load = HT_extension()->is_active( __FILE__ );

		return apply_filters( 'hocwp_theme_load_extension_smtp', $load );
	}
}

$load = hocwp_theme_load_extension_smtp();

if ( ! $load ) {
	return;
}

function hocwp_theme_wp_mail_from_name_filter( $name ) {
	$options = HT_Options()->get( 'smtp' );

	if ( ! empty( $options['from_name'] ) ) {
		$name = $options['from_name'];
	}

	return $name;
}

add_filter( 'wp_mail_from_name', 'hocwp_theme_wp_mail_from_name_filter' );

function hocwp_theme_wp_mail_from_filter( $email ) {
	$options = HT_Options()->get( 'smtp' );

	if ( isset( $options['from_email'] ) && HT_Util()->is_email( $options['from_email'] ) ) {
		$email = sanitize_email( $options['from_email'] );
	}

	return $email;
}

add_filter( 'wp_mail_from', 'hocwp_theme_wp_mail_from_filter' );

/**
 * Action to change $phpmailer object for SMTP setting on theme.
 *
 * @param $phpmailer
 *
 * @since Theme core version 6.8.5.1
 *
 */
function hocwp_theme_phpmailer_init_action( $phpmailer ) {
	global $wp_version;

	if ( version_compare( $wp_version, '5.5.0', '<' ) ) {
		if ( ! ( $phpmailer instanceof PHPMailer ) ) {
			return;
		}
	}

	$data = HT_Options()->get( 'smtp' );

	$phpmailer->Mailer = 'smtp';

	if ( isset( $data['return_path'] ) && $data['return_path'] ) {
		$phpmailer->Sender = $phpmailer->From;
	}

	$encryption = $data['encryption'] ?? 'ssl';
	$host       = $data['host'] ?? '';
	$port       = $data['port'] ?? 25;
	$username   = $data['username'] ?? '';
	$password   = $data['password'] ?? '';

	$phpmailer->SMTPSecure = ( $encryption == 'none' ) ? '' : $encryption;

	$phpmailer->Host = $host;
	$phpmailer->Port = $port;

	$phpmailer->SMTPAuth = true;
	$phpmailer->Username = $username;
	$phpmailer->Password = $password;

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