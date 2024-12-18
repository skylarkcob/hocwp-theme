<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! ht_util()->is_amp() ) {
	return;
}

function hocwp_ext_amp_post_template_data_filter( $data ) {
	$fonts = hocwp_ext_amp_fonts();

	if ( ht()->array_has_value( $fonts ) ) {
		$remove_default_font = ht_util()->get_theme_option( 'remove_default_font', '', 'amp' );

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

	if ( ht()->array_has_value( $fonts ) ) {
		foreach ( $fonts as $key => $url ) {
			if ( ! empty( $url ) ) {
				echo '<link rel="stylesheet" href="' . esc_attr( $url ) . '">';
			}
		}
	}

	$value = ht_options()->get_tab( 'amp_head', '', 'amp' );
	echo $value;
}

add_action( 'hocwp_theme_wp_head_amp', 'hocwp_ext_amp_custom_wp_head' );

function hocwp_ext_amp_wp_action() {
	global $wp_query;

	if ( isset( $_GET['amp'] ) || isset( $wp_query->query['amp'] ) ) {
		if ( ! ht_util()->is_amp( array( 'transitional', 'standard' ) ) ) {
			wp_redirect( ht_util()->get_current_url() );
			exit;
		}
	}

	// Fix Referenced AMP URL is self-canonical AMP
	if ( is_page() || is_singular() || is_single() ) {
		$amp_status = get_post_meta( get_the_ID(), 'amp_status', true );

		if ( 'disabled' != $amp_status ) {
			add_filter( 'get_canonical_url', '__return_false', 99 );
			add_filter( 'wpseo_canonical', '__return_false', 99 );
		}
	}
}

add_action( 'wp', 'hocwp_ext_amp_wp_action' );