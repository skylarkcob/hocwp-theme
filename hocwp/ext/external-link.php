<?php
/*
 * Name: External Link
 * Description: Make all external link as nofollow and contro go out url.
 */
function hocwp_theme_load_extension_external_link() {
	$load = hocwp_theme_is_extension_active( __FILE__ );
	$load = apply_filters( 'hocwp_theme_load_extension_external_link', $load );

	return $load;
}

$load = hocwp_theme_load_extension_external_link();
if ( ! $load ) {
	return;
}

function hocwp_theme_external_link_script() {
	wp_enqueue_script( 'hocwp-theme-external-link', HOCWP_THEME_CORE_URL . '/js/external-link' . HOCWP_THEME_JS_SUFFIX, array( 'hocwp-theme' ), false, true );
}

if ( ! is_admin() ) {
	add_action( 'wp_enqueue_scripts', 'hocwp_theme_external_link_script' );
}

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