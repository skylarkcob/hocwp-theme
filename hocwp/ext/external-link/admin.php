<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_ext_external_link_ads_positions( $positions ) {
	$positions['goout_top']    = __( 'Go out page top', 'hocwp-theme' );
	$positions['goout_middle'] = __( 'Go out page middle', 'hocwp-theme' );
	$positions['goout_bottom'] = __( 'Go out page bottom', 'hocwp-theme' );

	return $positions;
}

add_filter( 'hocwp_theme_ads_positions', 'hocwp_ext_external_link_ads_positions' );