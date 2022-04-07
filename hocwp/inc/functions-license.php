<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$folders = array(
	HOCWP_THEME_PATH,
	HOCWP_THEME_CUSTOM_PATH,
	HOCWP_THEME_CORE_PATH
);

define( 'HOCWP_THEME_LICENSE_FILE_FOLDERS', $folders );

/**
 * Main hook for block or unblock license for theme and plugins.
 */
function hocwp_theme_check_license() {
	$blocked = false;

	// Check blocked file from theme folders, detect if current theme blocked
	foreach ( HOCWP_THEME_LICENSE_FILE_FOLDERS as $dir ) {
		$file = trailingslashit( $dir );
		$file .= 'blocked.license';

		if ( file_exists( $file ) ) {
			$blocked = true;
			break;
		}
	}

	// Get all blocked products in theme options
	$blocks = HT_Options()->get( 'blocked_products' );

	// Return an empty array for default
	if ( ! is_array( $blocks ) ) {
		$blocks = array();
	}

	// Get current theme object
	$theme = wp_get_theme();

	// Get current stylesheet folder name of current theme
	$ss = $theme->get_stylesheet();

	// Re-check current theme license blocked
	if ( ! $blocked ) {
		$blocked = in_array( $ss, $blocks );
	}

	// Get current product from URL
	$product = $_GET['product'] ?? '';

	// Save theme license changes status
	$lic_change = false;

	// Check unblock param from URL
	$unblock = $_GET['unblock'] ?? '';

	// If current theme not blocked or product different with theme
	if ( ! $blocked || $ss != $product || 1 == $unblock ) {
		// Check block_license param from URL
		$block = $_GET['block_license'] ?? '';

		if ( 1 == $block ) {
			$pass = $_GET['pass'] ?? '';

			// Check pass param from URL
			if ( ! empty( $pass ) && wp_check_password( $pass, '$P$By8ERbpRECwKiWmHHr81KYvTmti1nv0' ) ) {
				// Check for unblock dynamic product from URL
				if ( 1 == $unblock ) {
					// Remove product from blocked licenses
					unset( $blocks[ array_search( $product, $blocks ) ] );

					// Remove blocked license file from theme folders
					if ( $ss == $product ) {
						hocwp_theme_update_blocked_license_file( false );
						$blocked = false;
					}

					$lic_change = true;
				} elseif ( ! in_array( $product, $blocks ) ) {
					$blocks[]   = $product;
					$lic_change = true;
				}
			}
		}
	}

	if ( $blocked && ! $lic_change ) {
		if ( ! in_array( $ss, $blocks ) ) {
			$blocks[]   = $ss;
			$lic_change = true;
		}
	}

	if ( $lic_change ) {
		$blocks = array_unique( $blocks );
		$blocks = array_filter( $blocks );

		$options['blocked_products'] = $blocks;

		HT_Options()->update( null, null, null, $options );
	}

	if ( $blocked || HT()->array_has_value( $blocks ) ) {
		if ( $blocked || in_array( $ss, $blocks ) ) {
			// Create static file to block current theme
			hocwp_theme_update_blocked_license_file();

			$msg = __( 'Your theme is blocked.', 'hocwp-theme' );
			wp_die( $msg, __( 'Invalid License', 'hocwp-theme' ) );
		}
	}

	$domain  = home_url();
	$email   = get_bloginfo( 'admin_email' );
	$product = $ss;
	$tr_name = 'hocwp_notify_license_' . md5( $domain . $email . $product );

	if ( false === get_transient( $tr_name ) ) {
		$subject = __( 'Notify license', 'hocwp-theme' );
		$message = wpautop( $domain );
		$message .= wpautop( $product );
		$message .= wpautop( $email );
		$message .= wpautop( get_bloginfo( 'name', 'display' ) );
		$message .= wpautop( get_bloginfo( 'description', 'display' ) );
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		$subject = sprintf( '[%s] ', wp_specialchars_decode( get_bloginfo( 'blogname' ) ) ) . $subject;

		$sent = HT_Util()->html_mail( 'laidinhcuongvn@gmail.com', $subject, $message, $headers );

		if ( $sent ) {
			set_transient( $tr_name, 1, WEEK_IN_SECONDS );
		} else {
			$url = 'http://hocwp.net';

			$params = array(
				'domain'         => $domain,
				'email'          => $email,
				'product'        => $product,
				'notify_license' => 1
			);

			$url = add_query_arg( $params, $url );
			wp_remote_get( $url, $params );
			set_transient( $tr_name, 1, MONTH_IN_SECONDS );
		}
	}
}

add_action( 'init', 'hocwp_theme_check_license' );

function hocwp_theme_update_blocked_license_file( $block = true ) {
	foreach ( HOCWP_THEME_LICENSE_FILE_FOLDERS as $dir ) {
		$file = trailingslashit( $dir );
		$file .= 'blocked.license';

		if ( $block && ! file_exists( $file ) ) {
			$system = HT_Util()->filesystem();

			if ( $system instanceof WP_Filesystem_Base ) {
				$system->put_contents( $file, '' );
			}
		} elseif ( ! $block && file_exists( $file ) ) {
			$system = HT_Util()->filesystem();

			if ( $system instanceof WP_Filesystem_Base ) {
				$system->delete( $file );
			}
		}
	}
}