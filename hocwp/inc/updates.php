<?php
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/class-hocwp-theme-updates.php';

add_action( 'init', function () {
	$theme = wp_get_theme();

	// Register theme update
	if ( $theme instanceof WP_Theme ) {
		hocwp_theme_register_theme_update( array(
			'slug'    => $theme->get_stylesheet(),
			'version' => $theme->get( 'Version' )
		) );

		while ( $theme->parent() ) {
			$theme = $theme->parent();

			hocwp_theme_register_theme_update( array(
				'slug'    => $theme->get_stylesheet(),
				'version' => $theme->get( 'Version' )
			) );
		}
	}
}, 20 );