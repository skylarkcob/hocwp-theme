<?php
/**
 * Theme core version.
 */
define( 'HOCWP_THEME_CORE_VERSION', '6.3.2' );

/**
 * Theme developing mode.
 */
define( 'HOCWP_THEME_DEVELOPING', ( ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) ? true : false ) );

/**
 * Theme root path.
 */
define( 'HOCWP_THEME_PATH', get_template_directory() );

/**
 * Theme base url.
 */
define( 'HOCWP_THEME_URL', get_template_directory_uri() );

/**
 * Theme core path.
 */
define( 'HOCWP_THEME_CORE_PATH', untrailingslashit( dirname( __FILE__ ) ) );

/**
 * Theme core base url.
 */
define( 'HOCWP_THEME_CORE_URL', untrailingslashit( HOCWP_THEME_URL . '/hocwp' ) );

/**
 * CSS suffix.
 */
define( 'HOCWP_THEME_CSS_SUFFIX', ( HOCWP_THEME_DEVELOPING ) ? '.css' : '.min.css' );

/**
 * Javascript suffix.
 */
define( 'HOCWP_THEME_JS_SUFFIX', ( HOCWP_THEME_DEVELOPING ) ? '.js' : '.min.js' );

/**
 * Theme custom path.
 */
define( 'HOCWP_THEME_CUSTOM_PATH', HOCWP_THEME_PATH . '/custom' );

/**
 * Theme custom base url.
 */
define( 'HOCWP_THEME_CUSTOM_URL', HOCWP_THEME_URL . '/custom' );

/**
 * Detect doing ajax or not.
 */
define( 'HOCWP_THEME_DOING_AJAX', ( ( defined( 'DOING_AJAX' ) && true === DOING_AJAX ) ? true : false ) );

/**
 * Theme load
 */
function hocwp_theme_load() {
	global $pagenow;

	/**
	 * Check class HocWP_Theme exists.
	 */
	if ( class_exists( 'HOCWP_Theme' ) ) {
		return;
	}

	$pre_hook = HOCWP_THEME_CUSTOM_PATH . '/pre-hook.php';

	if ( ( is_file( $pre_hook ) && file_exists( $pre_hook ) ) ) {
		require $pre_hook;
	}

	require HOCWP_THEME_CORE_PATH . '/inc/functions-deprecated.php';
	require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme.php';
	require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-sanitize.php';
	require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-utility.php';
	require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-requirement.php';
	require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-svg-icon.php';
	require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-html-tag.php';
	require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-html-field.php';
	require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-query.php';
	require HOCWP_THEME_CORE_PATH . '/inc/template-tags.php';
	require HOCWP_THEME_CORE_PATH . '/inc/functions-scripts.php';
	require HOCWP_THEME_CORE_PATH . '/inc/functions-media.php';
	require HOCWP_THEME_CORE_PATH . '/inc/functions-user.php';
	require HOCWP_THEME_CORE_PATH . '/inc/functions-preprocess.php';
	require HOCWP_THEME_CORE_PATH . '/inc/functions-extensions.php';
	require HOCWP_THEME_CORE_PATH . '/inc/setup.php';
	require HOCWP_THEME_CORE_PATH . '/inc/defaults.php';
	require HOCWP_THEME_CORE_PATH . '/inc/functions-permalinks.php';
	require HOCWP_THEME_CORE_PATH . '/inc/functions-license.php';

	/**
	 * Widgets.
	 */
	require HOCWP_THEME_CORE_PATH . '/widgets/class-hocwp-theme-widget-terms.php';
	require HOCWP_THEME_CORE_PATH . '/widgets/class-hocwp-theme-widget-posts.php';
	require HOCWP_THEME_CORE_PATH . '/widgets/class-hocwp-theme-widget-top-commenters.php';
	require HOCWP_THEME_CORE_PATH . '/widgets/class-hocwp-theme-widget-icon.php';

	/**
	 * Extensions.
	 */
	require HOCWP_THEME_CORE_PATH . '/ext/comment-notification.php';
	require HOCWP_THEME_CORE_PATH . '/ext/security.php';
	require HOCWP_THEME_CORE_PATH . '/ext/dynamic-thumbnail.php';
	require HOCWP_THEME_CORE_PATH . '/ext/smtp.php';
	require HOCWP_THEME_CORE_PATH . '/ext/external-link.php';
	require HOCWP_THEME_CORE_PATH . '/ext/improve-search.php';

	if ( is_admin() ) {
		require HOCWP_THEME_CORE_PATH . '/admin/admin.php';
	} else {
		require HOCWP_THEME_CORE_PATH . '/inc/functions-context.php';
	}

	/**
	 * Setup After.
	 */
	require HOCWP_THEME_CORE_PATH . '/inc/setup-after.php';

	if ( ! is_admin() ) {
		require HOCWP_THEME_CORE_PATH . '/inc/template.php';
		require HOCWP_THEME_CORE_PATH . '/inc/template-general.php';
		require HOCWP_THEME_CORE_PATH . '/inc/template-comments.php';
		require HOCWP_THEME_CORE_PATH . '/inc/template-post.php';

		if ( 'wp-login.php' == $pagenow ) {
			require HOCWP_THEME_CORE_PATH . '/inc/template-user.php';
		}
	} else {
		require HOCWP_THEME_CORE_PATH . '/admin/meta.php';
	}

	HOCWP_Theme::require_if_exists( HOCWP_THEME_CUSTOM_PATH . '/functions.php' );
	HOCWP_Theme::require_if_exists( HOCWP_THEME_CUSTOM_PATH . '/register.php' );
	HOCWP_Theme::require_if_exists( HOCWP_THEME_CUSTOM_PATH . '/hook.php' );

	if ( is_admin() ) {
		HOCWP_Theme::require_if_exists( HOCWP_THEME_CUSTOM_PATH . '/admin.php' );
	}

	HOCWP_Theme::require_if_exists( HOCWP_THEME_CUSTOM_PATH . '/extensions.php' );

	if ( is_admin() ) {
		HOCWP_Theme::require_if_exists( HOCWP_THEME_CUSTOM_PATH . '/meta.php' );

		if ( HOCWP_THEME_DOING_AJAX ) {
			HOCWP_Theme::require_if_exists( HOCWP_THEME_CUSTOM_PATH . '/ajax.php' );
		}
	} else {
		HOCWP_Theme::require_if_exists( HOCWP_THEME_CUSTOM_PATH . '/front-end.php' );
		HOCWP_Theme::require_if_exists( HOCWP_THEME_CUSTOM_PATH . '/template.php' );
	}
}

if ( ! has_action( 'after_setup_theme', 'hocwp_theme_load' ) ) {
	add_action( 'after_setup_theme', 'hocwp_theme_load', 0 );
}