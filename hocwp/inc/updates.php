<?php
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/class-hocwp-theme-updates.php';

add_action( 'init', function () {
	$allow_updates = apply_filters( 'hocwp_theme_allow_updates', HOCWP_THEME_DEVELOPING );

	if ( ! $allow_updates ) {
		return;
	}

	$theme = wp_get_theme();

	// Register theme update
	if ( $theme instanceof WP_Theme ) {
		$allow = apply_filters( 'hocwp_theme_allow_update_theme', $theme );

		if ( $allow ) {
			hocwp_theme_register_theme_update( array(
				'slug'    => $theme->get_stylesheet(),
				'version' => $theme->get( 'Version' )
			) );

			while ( $theme->parent() ) {
				$theme = $theme->parent();
				$allow = apply_filters( 'hocwp_theme_allow_update_theme', $theme );

				if ( $allow ) {
					hocwp_theme_register_theme_update( array(
						'slug'    => $theme->get_stylesheet(),
						'version' => $theme->get( 'Version' )
					) );
				}
			}
		}
	}
}, 20 );