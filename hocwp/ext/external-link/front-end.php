<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_external_link_script() {
	wp_enqueue_script( 'hocwp-theme-external-link', HOCWP_THEME_CORE_URL . '/js/external-link' . HOCWP_THEME_JS_SUFFIX, array( 'hocwp-theme' ), false, true );
}

add_action( 'wp_enqueue_scripts', 'hocwp_theme_external_link_script' );

function hocwp_theme_external_link_goout_before() {
	HT_Util()->display_ads( 'goout_top' );
}

add_action( 'hocwp_theme_goout_before', 'hocwp_theme_external_link_goout_before' );

function hocwp_theme_external_link_goout_middle() {
	HT_Util()->display_ads( 'goout_middle' );
}

add_action( 'hocwp_theme_goout', 'hocwp_theme_external_link_goout_middle' );

function hocwp_theme_external_link_goout_after() {
	HT_Util()->display_ads( 'goout_bottom' );
}

add_action( 'hocwp_theme_goout_after', 'hocwp_theme_external_link_goout_after' );

function hocwp_theme_external_link_template_include( $template ) {
	$goto = isset( $_GET['goto'] ) ? $_GET['goto'] : '';
	if ( ! empty( $goto ) ) {
		if ( HT()->is_positive_number( $goto ) ) {
			$obj = get_post( $goto );
			if ( $obj instanceof WP_Post ) {
				wp_redirect( get_permalink( $obj ) );
				exit;
			}
		} else {
			$template = HOCWP_THEME_CORE_PATH . '/ext/external-link/confirm-goout.php';
		}
	}

	return $template;
}

add_filter( 'template_include', 'hocwp_theme_external_link_template_include' );