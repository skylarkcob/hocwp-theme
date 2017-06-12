<?php
define( 'HOCWP_THEME_CORE_VERSION', '6.1.1' );
define( 'HOCWP_THEME_DEVELOPING', ( ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) ? true : false ) );
define( 'HOCWP_THEME_CORE_PATH', untrailingslashit( dirname( __FILE__ ) ) );
define( 'HOCWP_THEME_CORE_URL', untrailingslashit( get_template_directory_uri() . '/hocwp' ) );
define( 'HOCWP_THEME_CSS_SUFFIX', ( HOCWP_THEME_DEVELOPING ) ? '.css' : '.min.css' );
define( 'HOCWP_THEME_JS_SUFFIX', ( HOCWP_THEME_DEVELOPING ) ? '.js' : '.min.js' );

function hocwp_theme_load() {
	require get_template_directory() . '/custom/pre-hook.php';
	require HOCWP_THEME_CORE_PATH . '/inc/functions-deprecated.php';
	require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme.php';
	if ( HOCWP_THEME_DEVELOPING ) {
		require HOCWP_THEME_CORE_PATH . '/inc/functions-development.php';
	}
	require HOCWP_THEME_CORE_PATH . '/inc/functions-scripts.php';
	if ( ! is_admin() ) {
		require HOCWP_THEME_CORE_PATH . '/inc/functions-context.php';
		require HOCWP_THEME_CORE_PATH . '/inc/template.php';
		require HOCWP_THEME_CORE_PATH . '/inc/template-general.php';
	} else {
		require HOCWP_THEME_CORE_PATH . '/admin/admin.php';
	}
	require get_template_directory() . '/custom/functions.php';
}

add_action( 'init', 'hocwp_theme_load' );