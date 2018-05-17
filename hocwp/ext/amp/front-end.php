<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! HT_Util()->is_amp() ) {
	return;
}

function hocwp_ext_amp_post_template_data_filter( $data ) {
	$fonts = hocwp_ext_amp_fonts();

	if ( HT()->array_has_value( $fonts ) ) {
		$remove_default_font = HT_Util()->get_theme_option( 'remove_default_font', '', 'amp' );

		if ( 1 == $remove_default_font ) {
			unset( $data['font_urls']['merriweather'] );
		}

		foreach ( $fonts as $key => $url ) {
			$data['font_urls'][ $key ] = $url;
		}
	}

	return $data;
}

add_filter( 'amp_post_template_data', 'hocwp_ext_amp_post_template_data_filter' );

function hocwp_ext_amp_post_template_css_action() {
	include HOCWP_EXT_AMP_PATH . '/style.php';
}

add_action( 'amp_post_template_css', 'hocwp_ext_amp_post_template_css_action' );