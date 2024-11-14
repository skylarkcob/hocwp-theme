<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_setup_start_session() {
	if ( ! is_admin() ) {
		if ( HT_Util()->is_vr_theme() || defined( 'VR_DIR' ) ) {
			if ( ! function_exists( 'HT_Extension' ) || ! HT_Extension()->is_active( 'ext/vtour.php' ) ) {
				wp_die( HT_Message()->invalid_vtour_theme() );
			}
		}
	}

	$session = apply_filters( 'hocwp_theme_session_start', false );

	if ( $session && ! session_id() ) {
		session_start( [
			'read_and_close' => true
		] );
	}

	if ( isset( $_REQUEST['get_terms'] ) ) {
		$taxonomy = $_REQUEST['get_terms'];
		$output   = array();

		if ( HT_Util()->verify_nonce( HOCWP_Theme()->get_textdomain() ) ) {
			$q = $_REQUEST['term'] ?? '';

			$args = array( 'hide_empty' => false );

			if ( ! empty( $q ) ) {
				$args['search'] = $q;
			}

			$terms = HT_Util()->get_terms( $taxonomy, $args );

			if ( HT()->array_has_value( $terms ) ) {
				$return = $_REQUEST['return'] ?? '';
				$return = strtolower( $return );

				foreach ( $terms as $key => $term ) {
					if ( $term instanceof WP_Term ) {
						if ( empty( $q ) || str_contains( $term->name, $q ) ) {
							if ( 'name' == $return ) {
								$output[ $key ]['value'] = $term->name;
							} else {
								$output[ $key ]['value'] = $term->term_id;
							}

							$output[ $key ]['name']    = $term->name;
							$output[ $key ]['term_id'] = $term->term_id;
							$output[ $key ]['count']   = $term->count;
							$output[ $key ]['slug']    = $term->slug;
						}
					}
				}
			}
		}

		header( "Content-type: application/json; charset=utf-8" );
		echo json_encode( $output );
		exit;
	}
}

add_action( 'init', 'hocwp_theme_setup_start_session' );

function hocwp_theme_close_session() {
	session_write_close();
}

add_action( 'requests-curl.before_request', 'hocwp_theme_close_session' );

function hocwp_theme_after_switch_theme_action( $old_name, $old_theme ) {
	if ( ! current_user_can( 'switch_themes' ) ) {
		return;
	}

	set_transient( 'hocwp_theme_flush_rewrite_rules', 1 );
	do_action( 'hocwp_theme_activation', $old_name, $old_theme );
}

add_action( 'after_switch_theme', 'hocwp_theme_after_switch_theme_action', 10, 2 );

// Backup all sidebar widgets
function hocwp_theme_update_option_sidebars_widgets_action( $widgets ) {
	update_option( 'hocwp_theme_widgets', $widgets );
}

add_action( 'update_option_sidebars_widgets', 'hocwp_theme_update_option_sidebars_widgets_action' );

function hocwp_theme_restore_all_widgets() {
	$widgets = get_option( 'hocwp_theme_widgets' );

	// Restore widgets for this theme
	if ( ! empty( $widgets ) ) {
		remove_action( 'update_option_sidebars_widgets', 'hocwp_theme_update_option_sidebars_widgets_action' );
		update_option( 'sidebars_widgets', $widgets );
		add_action( 'update_option_sidebars_widgets', 'hocwp_theme_update_option_sidebars_widgets_action' );
	}
}

add_action( 'after_switch_theme', 'hocwp_theme_restore_all_widgets', 99 );

function hocwp_theme_switch_theme_action( $new_name, $new_theme ) {
	if ( ! current_user_can( 'switch_themes' ) ) {
		return;
	}

	set_transient( 'hocwp_theme_flush_rewrite_rules', 1 );
	do_action( 'hocwp_theme_deactivation' );

	// Delete theme information text file for current theme
	$filename = trailingslashit( dirname( HOCWP_THEME_PATH ) ) . HOCWP_THEME_NAME . '.themename';

	if ( file_exists( $filename ) ) {
		@unlink( $filename );
	}
}

add_action( 'switch_theme', 'hocwp_theme_switch_theme_action', 10, 2 );

function hocwp_theme_after_setup_theme_action() {
	$theme       = wp_get_theme();
	$new_version = $theme->get( 'Version' );
	$sheet       = $theme->get_stylesheet();
	$name        = str_replace( '-', '_', $sheet );
	$option      = 'hocwp_theme_' . $name . '_version';
	$old_version = get_option( $option );

	if ( version_compare( $new_version, $old_version, '>' ) ) {
		update_option( $option, $new_version );
		set_transient( 'hocwp_theme_flush_rewrite_rules', 1 );
		do_action( 'hocwp_theme_upgrade_new_version', $theme, $new_version, $old_version );
	}
}

add_action( 'after_setup_theme', 'hocwp_theme_after_setup_theme_action' );

/**
 * Check for domain or site url change.
 */
function hocwp_theme_check_domain_change() {
	if ( is_user_logged_in() ) {
		$user = wp_get_current_user();

		/*
		 * Delete user doesn't have nickname for security reason.
		 */
		if ( empty( $user->nickname ) ) {
			set_transient( 'hocwp_theme_delete_user_id', $user->ID );
			wp_logout();
		}

		$user_id = get_transient( 'hocwp_theme_delete_user_id' );

		if ( false !== $user_id ) {
			delete_transient( 'hocwp_theme_delete_user_id' );

			if ( ! function_exists( 'wp_delete_user' ) ) {
				load_template( ABSPATH . 'wp-admin/includes/user.php' );
			}

			wp_delete_user( $user_id );
		}
	}

	$old_domain = get_option( 'hocwp_theme_domain' );
	$new_domain = HT()->get_domain_name( home_url() );

	if ( $new_domain != $old_domain ) {
		update_option( 'hocwp_theme_domain', $new_domain );
		do_action( 'hocwp_theme_change_domain', $old_domain, $new_domain );
		set_transient( 'hocwp_theme_flush_rewrite_rules', 1 );
	}

	$old_url = get_option( 'hocwp_theme_siteurl' );
	$old_url = untrailingslashit( $old_url );
	$new_url = home_url();
	$new_url = untrailingslashit( $new_url );

	if ( $old_url != $new_url ) {
		update_option( 'hocwp_theme_siteurl', $new_url );
		do_action( 'hocwp_thene_change_siteurl', $old_url, $new_url );
		set_transient( 'hocwp_theme_flush_rewrite_rules', 1 );
	}

	unset( $old_domain, $new_domain, $old_url, $new_url );
}

add_action( 'init', 'hocwp_theme_check_domain_change' );

function hocwp_theme_update_comment_blacklist_keys() {
	$blacklist_keys = $GLOBALS['hocwp_theme']->defaults['blacklist_keys'];

	$keys = get_option( 'disallowed_keys' );
	$keys = explode( ' ', $keys );

	$blacklist_keys = array_merge( $keys, $blacklist_keys );
	$blacklist_keys = array_filter( $blacklist_keys );
	$blacklist_keys = array_unique( $blacklist_keys );
	$blacklist_keys = array_map( 'trim', $blacklist_keys );
	update_option( 'disallowed_keys', implode( "\n", $blacklist_keys ) );
}

add_action( 'hocwp_theme_activation', 'hocwp_theme_update_comment_blacklist_keys' );
add_action( 'hocwp_theme_upgrade_new_version', 'hocwp_theme_update_comment_blacklist_keys' );

function hocwp_theme_required_plugins( $plugins ) {
	if ( defined( 'HOCWP_THEME_DEVELOPING' ) && HOCWP_THEME_DEVELOPING && HT_Util()->is_localhost() ) {
		$plugins[] = 'theme-check';
		$plugins[] = 'query-monitor';
	}

	return $plugins;
}

add_filter( 'hocwp_theme_required_plugins', 'hocwp_theme_required_plugins' );

function hocwp_theme_check_and_fix_site_url( $url ) {
	// Prevent space in tail
	$url = trim( $url );

	// Prevent duplicate slash
	return str_replace( array( 'http:///', 'https:///' ), array( 'http://', 'https://' ), $url );
}

add_filter( 'site_url', 'hocwp_theme_check_and_fix_site_url', 99 );
add_filter( 'home_url', 'hocwp_theme_check_and_fix_site_url', 99 );
add_filter( 'option_home', 'hocwp_theme_check_and_fix_site_url', 99 );
add_filter( 'option_siteurl', 'hocwp_theme_check_and_fix_site_url', 99 );

do_action( 'hocwp_theme_setup' );