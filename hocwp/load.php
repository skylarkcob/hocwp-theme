<?php
define( 'HOCWP_THEME_CORE_VERSION', '6.1.2' );
define( 'HOCWP_THEME_DEVELOPING', ( ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) ? true : false ) );
define( 'HOCWP_THEME_CORE_PATH', untrailingslashit( dirname( __FILE__ ) ) );
define( 'HOCWP_THEME_CORE_URL', untrailingslashit( get_template_directory_uri() . '/hocwp' ) );
define( 'HOCWP_THEME_CSS_SUFFIX', ( HOCWP_THEME_DEVELOPING ) ? '.css' : '.min.css' );
define( 'HOCWP_THEME_JS_SUFFIX', ( HOCWP_THEME_DEVELOPING ) ? '.js' : '.min.js' );
define( 'HOCWP_THEME_CUSTOM_PATH', get_template_directory() . '/custom' );

function hocwp_theme_load() {
	if ( class_exists( 'HOCWP_Theme' ) ) {
		return;
	}
	$pre_hook = get_template_directory() . '/custom/pre-hook.php';
	if ( file_exists( $pre_hook ) ) {
		require $pre_hook;
	}
	require HOCWP_THEME_CORE_PATH . '/inc/functions-deprecated.php';
	require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme.php';
	require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-sanitize.php';
	require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-utility.php';
	require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-svg-icon.php';
	require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-html-tag.php';
	require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-html-field.php';
	require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-query.php';
	require HOCWP_THEME_CORE_PATH . '/inc/functions-scripts.php';
	require HOCWP_THEME_CORE_PATH . '/inc/functions-media.php';
	require HOCWP_THEME_CORE_PATH . '/inc/functions-preprocess.php';
	if ( HOCWP_THEME_DEVELOPING ) {
		require HOCWP_THEME_CORE_PATH . '/inc/functions-development.php';
	}
	require HOCWP_THEME_CORE_PATH . '/inc/setup.php';
	require HOCWP_THEME_CORE_PATH . '/inc/defaults.php';
	require HOCWP_THEME_CORE_PATH . '/inc/functions-license.php';
	/**
	 * Extensions
	 */
	require HOCWP_THEME_CORE_PATH . '/inc/functions-extensions.php';
	require HOCWP_THEME_CORE_PATH . '/ext/google-code-prettify.php';
	require HOCWP_THEME_CORE_PATH . '/ext/comment-notification.php';
	require HOCWP_THEME_CORE_PATH . '/ext/dynamic-thumbnail.php';
	require HOCWP_THEME_CORE_PATH . '/ext/optimize.php';
	require HOCWP_THEME_CORE_PATH . '/ext/recent-activity-post.php';
	require HOCWP_THEME_CORE_PATH . '/ext/smtp.php';
	require HOCWP_THEME_CORE_PATH . '/ext/woocommerce.php';
	if ( ! is_admin() ) {
		require HOCWP_THEME_CORE_PATH . '/inc/functions-context.php';
		require HOCWP_THEME_CORE_PATH . '/inc/template.php';
		require HOCWP_THEME_CORE_PATH . '/inc/template-general.php';
		require HOCWP_THEME_CORE_PATH . '/inc/template-comments.php';
		require HOCWP_THEME_CORE_PATH . '/inc/template-post.php';
	} else {
		require HOCWP_THEME_CORE_PATH . '/admin/admin.php';
	}
	HOCWP_Theme::require_if_exists( HOCWP_THEME_CUSTOM_PATH . '/functions.php' );
	HOCWP_Theme::require_if_exists( HOCWP_THEME_CUSTOM_PATH . '/hook.php' );
	if ( is_admin() ) {
		HOCWP_Theme::require_if_exists( HOCWP_THEME_CUSTOM_PATH . '/admin.php' );
	}
}

add_action( 'after_setup_theme', 'hocwp_theme_load', 0 );