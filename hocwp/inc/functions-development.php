<?php
if ( ! defined( 'HOCWP_THEME_DEVELOPING' ) || 1 != HOCWP_THEME_DEVELOPING ) {
	return;
}

function hocwp_theme_debug( $value ) {
	if ( HOCWP_THEME_DEVELOPING ) {
		if ( is_array( $value ) || is_object( $value ) ) {
			error_log( print_r( $value, true ) );
		} else {
			error_log( $value );
		}
	}
}

function hocwp_theme_zip_folder( $source, $destination ) {
	if ( ! extension_loaded( 'zip' ) || ! file_exists( $source ) ) {
		return false;
	}
	$zip = new ZipArchive();
	if ( ! $zip->open( $destination, ZIPARCHIVE::CREATE ) ) {
		return false;
	}
	$source = str_replace( '\\', '/', realpath( $source ) );
	if ( is_dir( $source ) === true ) {
		$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $source ), RecursiveIteratorIterator::SELF_FIRST );
		foreach ( $files as $file ) {
			$file = str_replace( '\\', '/', $file );
			if ( in_array( substr( $file, strrpos( $file, '/' ) + 1 ), array( '.', '..' ) ) ) {
				continue;
			}
			$file = realpath( $file );
			if ( is_dir( $file ) === true ) {
				$zip->addEmptyDir( str_replace( $source . '/', '', $file . '/' ) );
			} else if ( is_file( $file ) === true ) {
				$zip->addFromString( str_replace( $source . '/', '', $file ), file_get_contents( $file ) );
			}
		}
	} else if ( is_file( $source ) === true ) {
		$zip->addFromString( basename( $source ), file_get_contents( $source ) );
	}

	return $zip->close();
}

function hocwp_theme_zip_current_theme() {
	$time    = strtotime( date( 'Y-m-d H:i:s' ) );
	$theme   = wp_get_theme();
	$sheet   = $theme->get_stylesheet();
	$version = $theme->get( 'Version' );
	$source  = untrailingslashit( get_template_directory() );
	$dest    = dirname( $source ) . '/' . $sheet;
	$dest .= '_v' . $version;
	$dest .= '_' . $time;
	$dest .= '.zip';

	return hocwp_theme_zip_folder( $source, $dest );
}

function hocwp_theme_auto_create_backup_current_theme() {
	$tr_name = 'hocwp_theme_backup_current_developing_theme';
	if ( false === get_transient( $tr_name ) ) {
		$result = hocwp_theme_zip_current_theme();
		if ( $result ) {
			set_transient( $tr_name, 1, 6 * HOUR_IN_SECONDS );
		}
	} else {
		add_action( 'hocwp_theme_upgrade_new_version', 'hocwp_theme_zip_current_theme', 99 );
	}
}

add_action( 'wp_loaded', 'hocwp_theme_auto_create_backup_current_theme' );