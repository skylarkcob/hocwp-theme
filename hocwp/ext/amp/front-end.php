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
			if ( ! empty( $url ) ) {
				$data['font_urls'][ $key ] = $url;
			}
		}
	}

	return $data;
}

add_filter( 'amp_post_template_data', 'hocwp_ext_amp_post_template_data_filter' );

function hocwp_ext_amp_post_template_css_action() {
	include HOCWP_EXT_AMP_PATH . '/style.php';
}

add_action( 'amp_post_template_css', 'hocwp_ext_amp_post_template_css_action' );

function hocwp_ext_amp_custom_wp_head() {
	$fonts = hocwp_ext_amp_fonts();

	if ( HT()->array_has_value( $fonts ) ) {
		foreach ( $fonts as $key => $url ) {
			if ( ! empty( $url ) ) {
				echo '<link rel="stylesheet" href="' . esc_attr( $url ) . '">';
			}
		}
	}
}

add_action( 'hocwp_theme_wp_head_amp', 'hocwp_ext_amp_custom_wp_head' );

function hocwp_ext_amp_wp_action() {
	global $wp_query;

	if ( isset( $_GET['amp'] ) || isset( $wp_query->query['amp'] ) ) {
		if ( ! HT_Util()->is_amp( array( 'transitional', 'standard' ) ) ) {
			wp_redirect( HT_Util()->get_current_url() );
			exit;
		}
	}
}

add_action( 'wp', 'hocwp_ext_amp_wp_action' );