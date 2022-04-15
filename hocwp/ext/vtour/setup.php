<?php
/*
 * HOCWP Theme VR Tour
 *
 * Last updated: 14/04/2022
 * Version: 1.0.3
 */

defined( 'ABSPATH' ) || exit;

const HT_VR_VERSION = '1.0.2';

const HT_VR_REQUIRE_CORE_VERSION = '6.9.3';

if ( version_compare( HOCWP_THEME_CORE_VERSION, HT_VR_REQUIRE_CORE_VERSION, '<' ) ) {
	wp_die( sprintf( __( 'VR Tour requires theme core version %s or later.', 'hocwp-theme' ), HT_VR_REQUIRE_CORE_VERSION ) );
}

do_action( 'hocwp_theme_vr_tour_template_init' );