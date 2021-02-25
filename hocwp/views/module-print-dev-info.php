<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_dev_info_section_open( $heading ) {
	echo '<h2>' . $heading . '</h2>';
	echo '<pre>';
}

function hocwp_theme_dev_info_section_close() {
	echo '</pre>';
}

function hocwp_theme_dev_info_stats( $stats ) {
	if ( isset( $stats['size'] ) ) {
		$stats['size'] = size_format( $stats['size'] );
	}

	if ( isset( $stats['atime'] ) ) {
		$stats['atime'] = sprintf( __( 'Last access: %s ago', 'hocwp-theme' ), human_time_diff( $stats['atime'] ) );
	}

	if ( isset( $stats['mtime'] ) ) {
		$stats['mtime'] = sprintf( __( 'Last modified: %s ago', 'hocwp-theme' ), human_time_diff( $stats['mtime'] ) );
	}

	if ( isset( $stats['ctime'] ) ) {
		$stats['ctime'] = sprintf( __( 'Last changed: %s ago', 'hocwp-theme' ), human_time_diff( $stats['ctime'] ) );
	}

	print_r( $stats );
}

$path = get_template_directory() . '/style.css';

if ( file_exists( $path ) ) {
	$style = HT_Util()->read_all_text( $path );
	$style = substr( $style, 0, strpos( $style, '*/' ) + 2 );
	hocwp_theme_dev_info_section_open( __( 'Theme Style File', 'hocwp-theme' ) );
	print_r( $style );
	hocwp_theme_dev_info_section_close();
}

$path = HOCWP_THEME_CUSTOM_PATH . '/readme.txt';

if ( file_exists( $path ) ) {
	$data = HT_Util()->read_all_text( $path );
	hocwp_theme_dev_info_section_open( __( 'Theme Custom Readme', 'hocwp-theme' ) );
	print_r( $data );
	hocwp_theme_dev_info_section_close();
}

hocwp_theme_dev_info_section_open( __( 'Active Plugins', 'hocwp-theme' ) );
$data = get_option( 'active_plugins' );
print_r( $data );
hocwp_theme_dev_info_section_close();

$path = ABSPATH . 'wp-config.php';

if ( file_exists( $path ) ) {
	$data = HT_Util()->read_all_text( $path );
	$data = str_replace( '<?php', '', $data );
	$data = str_replace( '?>', '', $data );
	hocwp_theme_dev_info_section_open( __( 'Configure File', 'hocwp-theme' ) );
	print_r( $data );
	hocwp_theme_dev_info_section_close();
}

hocwp_theme_dev_info_section_open( __( 'Theme Object', 'hocwp-theme' ) );
$theme = wp_get_theme();
print_r( $theme );
hocwp_theme_dev_info_section_close();

hocwp_theme_dev_info_section_open( __( 'Theme Statistics', 'hocwp-theme' ) );
$stats = lstat( get_template_directory() );
hocwp_theme_dev_info_stats( $stats );
hocwp_theme_dev_info_section_close();

hocwp_theme_dev_info_section_open( __( 'Theme Custom Statistics', 'hocwp-theme' ) );
$stats = lstat( HOCWP_THEME_CUSTOM_PATH );
hocwp_theme_dev_info_stats( $stats );
hocwp_theme_dev_info_section_close();

do_action( 'hocwp_theme_display_development_information' );