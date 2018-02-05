<?php
/**
 * Load theme styles and scripts on front-end.
 */
function hocwp_theme_custom_enqueue_scripts() {
	wp_enqueue_style( 'font-awesome-style', HOCWP_THEME_CUSTOM_URL . '/lib/font-awesome/css/font-awesome' . HOCWP_THEME_CSS_SUFFIX );
	$url = HOCWP_THEME_CUSTOM_URL . '/lib/slick/slick';
	wp_enqueue_style( 'slick-style', $url . HOCWP_THEME_CSS_SUFFIX );
	wp_enqueue_script( 'slick', $url . HOCWP_THEME_JS_SUFFIX, array( 'jquery' ), false, true );
}

add_action( 'wp_enqueue_scripts', 'hocwp_theme_custom_enqueue_scripts' );

function hocwp_theme_custom_site_content_bottom() {
	echo '<div class="clearfix"></div>';
	$maps = hocwp_theme_get_option( 'maps' );
	if ( ! empty( $maps ) ) {
		$domain = HT()->get_domain_name( home_url(), true );
		echo '<div class="google-maps why-us">';
		echo '<h3>' . sprintf( __( 'Road map to %s', 'hocwp-theme' ), strtoupper( $domain ) ) . '</h3>';
		echo $maps;
		echo '</div>';
	}
}

add_action( 'hocwp_theme_site_content_bottom', 'hocwp_theme_custom_site_content_bottom' );

function hocwp_theme_custom_load_addthis( $load ) {
	if ( is_single() && ! is_page() ) {
		$load = true;
	}

	return $load;
}

add_filter( 'hocwp_theme_load_addthis', 'hocwp_theme_custom_load_addthis' );