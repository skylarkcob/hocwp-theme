<?php
define( 'HOCWP_THEME_CORE_VERSION', '6.1.0' );
define( 'HOCWP_THEME_DEVELOPING', ( ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) ? true : false ) );
define( 'HOCWP_THEME_CORE_PATH', untrailingslashit( dirname( __FILE__ ) ) );

function hocwp_theme_load() {
	require HOCWP_THEME_CORE_PATH . '/inc/functions-deprecated.php';
	require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme.php';
	if ( HOCWP_THEME_DEVELOPING ) {
		require HOCWP_THEME_CORE_PATH . '/inc/functions-development.php';
	}
	if ( ! is_admin() ) {
		require HOCWP_THEME_CORE_PATH . '/inc/functions-context.php';
		require HOCWP_THEME_CORE_PATH . '/inc/template.php';
		require HOCWP_THEME_CORE_PATH . '/inc/template-general.php';
	}
}

add_action( 'init', 'hocwp_theme_load' );