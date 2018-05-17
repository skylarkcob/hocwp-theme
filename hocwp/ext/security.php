<?php
/*
 * Name: Security
 * Description: Protect your site from external threats.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$load = apply_filters( 'hocwp_theme_load_extension_security', hocwp_theme_is_extension_active( __FILE__ ) );
if ( ! $load ) {
	return;
}