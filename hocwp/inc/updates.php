<?php
defined( 'ABSPATH' ) || exit;

require_once( __DIR__ . '/class-hocwp-theme-updates.php' );

add_action( 'init', function () {
	$allow_updates = apply_filters( 'hocwp_theme_allow_updates', HOCWP_THEME_DEVELOPING );

	if ( ! $allow_updates ) {
		return;
	}

	if ( is_admin() && ht_admin()->is_admin_page( 'themes.php', 'hocwp_theme' ) ) {
		$tab = $_GET['tab'] ?? '';

		if ( 'system_information' == $tab ) {
			hocwp_theme_updates()->refresh_themes_transient();
		}
	} else {
		$do_action = $_GET['do_action'] ?? '';

		if ( 'check_updates' == $do_action ) {
			hocwp_theme_updates()->refresh_themes_transient();
		}
	}

	// Register theme update.
	if ( hocwp_theme()->theme instanceof WP_Theme ) {
		$allow = apply_filters( 'hocwp_theme_allow_update_theme', hocwp_theme()->theme );

		if ( $allow ) {
			// Check update for current theme.
			hocwp_theme_register_theme_update( array(
				'slug'    => hocwp_theme()->stylesheet,
				'version' => hocwp_theme()->version
			) );

			$theme = hocwp_theme()->theme;

			while ( $theme->parent() ) {
				$theme = $theme->parent();
				$allow = apply_filters( 'hocwp_theme_allow_update_theme', $theme );

				if ( $allow ) {
					// Check update for parent theme.
					hocwp_theme_register_theme_update( array(
						'slug'    => $theme->get_stylesheet(),
						'version' => $theme->get( 'Version' )
					) );
				}
			}
		}
	}
}, 20 );