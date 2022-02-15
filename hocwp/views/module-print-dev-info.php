<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $wpdb;

$yes = __( 'Yes', 'hocwp-theme' );
$no  = __( 'No', 'hocwp-theme' );

function hocwp_theme_dev_info_section_open( $heading ) {
	echo '<h2>' . $heading . '</h2>';

	if ( is_admin() ) {
		echo PHP_EOL . PHP_EOL;
	}

	echo '<pre>';
}

function hocwp_theme_dev_info_section_close() {
	echo '</pre>';

	if ( is_admin() ) {
		echo PHP_EOL;
	}
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

hocwp_theme_dev_info_section_open( __( 'WordPress Information', 'hocwp-theme' ) );
printf( __( 'WP version: %s', 'hocwp-theme' ), $GLOBALS['wp_version'] . PHP_EOL );
printf( __( 'Home URL: %s', 'hocwp-theme' ), home_url() . PHP_EOL );
printf( __( 'Site URL: %s', 'hocwp-theme' ), site_url() . PHP_EOL );
printf( __( 'Admin email: %s', 'hocwp-theme' ), get_bloginfo( 'admin_email' ) . PHP_EOL );
printf( __( 'WP multisite: %s', 'hocwp-theme' ), ( is_multisite() ? $yes : $no ) . PHP_EOL );

$memory = WP_MEMORY_LIMIT;

if ( function_exists( 'memory_get_usage' ) ) {
	$system_memory = @ini_get( 'memory_limit' );
	$memory        = max( $memory, $system_memory );
}

printf( __( 'WP memory limit: %s', 'hocwp-theme' ), size_format( HT()->memory_size_convert( $memory ) ) . PHP_EOL );

printf( __( 'Home directory: %s', 'hocwp-theme' ), htmlspecialchars( ABSPATH ) . PHP_EOL );
printf( __( 'Content directory: %s', 'hocwp-theme' ), htmlspecialchars( WP_CONTENT_DIR ) . PHP_EOL );
printf( __( 'Plugin directory: %s', 'hocwp-theme' ), htmlspecialchars( WP_PLUGIN_DIR ) . PHP_EOL );

printf( __( 'WP debug mode: %s', 'hocwp-theme' ), ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? $yes : $no ) . PHP_EOL );
printf( __( 'WP debug log active: %s', 'hocwp-theme' ), ( ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) ? $yes : $no ) . PHP_EOL );
printf( __( 'Table prefix: %s', 'hocwp-theme' ), HT_Util()->get_table_prefix() . PHP_EOL );
$users = count_users();
printf( __( 'Total users: %s', 'hocwp-theme' ), number_format_i18n( $users['total_users'] ) . PHP_EOL );
printf( __( 'WP debug log file location: %s', 'hocwp-theme' ), ( ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) ? ini_get( 'error_log' ) : '' ) . PHP_EOL );
printf( __( 'WP cron: %s', 'hocwp-theme' ), ( ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) ? $no : $yes ) . PHP_EOL );
printf( __( 'Language: %s', 'hocwp-theme' ), get_locale() . PHP_EOL );
$upload_dir = wp_upload_dir();
printf( __( 'Upload directory location: %s', 'hocwp-theme' ), $upload_dir['basedir'] . PHP_EOL );
printf( __( 'Upload URL location: %s', 'hocwp-theme' ), $upload_dir['baseurl'] . PHP_EOL );

printf( __( 'WP local time: %s', 'hocwp-theme' ), HT_Util()->get_timezone() . PHP_EOL );
printf( __( 'Site time: %s', 'hocwp-theme' ), ( current_time( 'mysql' ) ) . PHP_EOL );
printf( __( 'DB time: %s', 'hocwp-theme' ), ( $wpdb->get_var( 'SELECT utc_timestamp()' ) ) . PHP_EOL );
printf( __( 'PHP time: %s', 'hocwp-theme' ), date( 'Y-m-d H:i:s' ) . PHP_EOL );

$post_types = $wpdb->get_results( "SELECT post_type AS 'type', count(1) AS 'count' FROM {$wpdb->posts} GROUP BY post_type ORDER BY count DESC;" );

$type = '';

foreach ( $post_types as $pt ) {
	$type .= sprintf( ', %s (%s)', $pt->type, $pt->count );
}

$type = ltrim( $type, ', ' );

printf( __( 'Post types: %s', 'hocwp-theme' ), ( $type ) . PHP_EOL );
hocwp_theme_dev_info_section_close();

hocwp_theme_dev_info_section_open( __( 'Server Information', 'hocwp-theme' ) );
printf( __( 'Operating system: %s', 'hocwp-theme' ), php_uname() . PHP_EOL );
printf( __( 'PHP version: %s', 'hocwp-theme' ), phpversion() . PHP_EOL );
printf( __( 'MySQL version: %s', 'hocwp-theme' ), $wpdb->db_version() . PHP_EOL );
printf( __( 'PHP post max size: %s', 'hocwp-theme' ), ( size_format( HT()->memory_size_convert( ini_get( 'post_max_size' ) ) ) ) . PHP_EOL );
printf( __( 'PHP time limit: %s', 'hocwp-theme' ), ( ini_get( 'max_execution_time' ) ) . PHP_EOL );
printf( __( 'PHP max input vars: %s', 'hocwp-theme' ), ( ini_get( 'max_input_vars' ) ) . PHP_EOL );
printf( __( 'Peak memory usage: %s', 'hocwp-theme' ), size_format( memory_get_peak_usage( true ) ) . PHP_EOL );
printf( __( 'Current memory usage: %s', 'hocwp-theme' ), size_format( memory_get_usage( true ) ) . PHP_EOL );
printf( __( 'Max upload size: %s', 'hocwp-theme' ), ( size_format( wp_max_upload_size() ) ) . PHP_EOL );

if ( function_exists( 'curl_version' ) ) {
	$curl_version = curl_version();
	$version      = $curl_version['version'] . ', ' . $curl_version['ssl_version'];
} else {
	$version = __( 'N/A', 'hocwp-theme' );
}

printf( __( 'cURL version: %s', 'hocwp-theme' ), ( $version ) . PHP_EOL );
printf( __( 'Suhosin installed: %s', 'hocwp-theme' ), ( extension_loaded( 'suhosin' ) ? $yes : $no ) . PHP_EOL );
printf( __( 'Default timezone is UTC: %s', 'hocwp-theme' ), ( 'UTC' === date_default_timezone_get() ? $yes : $no ) . PHP_EOL );
printf( __( 'PHP Extensions: %s', 'hocwp-theme' ), ( implode( ', ', get_loaded_extensions() ) ) . PHP_EOL );

$fields = array();

// fsockopen/cURL.
$fields['fsockopen_curl']['name'] = 'fsockopen/cURL';

if ( function_exists( 'fsockopen' ) || function_exists( 'curl_init' ) ) {
	$fields['fsockopen_curl']['success'] = true;
} else {
	$fields['fsockopen_curl']['success'] = false;
}

// SOAP.
$fields['soap_client']['name'] = 'SoapClient';

if ( class_exists( 'SoapClient' ) ) {
	$fields['soap_client']['success'] = true;
} else {
	$fields['soap_client']['success'] = false;
}

// DOMDocument.
$fields['dom_document']['name'] = 'DOMDocument';

if ( class_exists( 'DOMDocument' ) ) {
	$fields['dom_document']['success'] = true;
} else {
	$fields['dom_document']['success'] = false;
}

// GZIP.
$fields['gzip']['name'] = 'GZip';

if ( is_callable( 'gzopen' ) ) {
	$fields['gzip']['success'] = true;
} else {
	$fields['gzip']['success'] = false;
}

// Multibyte String.
$fields['mbstring']['name'] = 'Multibyte string';

if ( extension_loaded( 'mbstring' ) ) {
	$fields['mbstring']['success'] = true;
} else {
	$fields['mbstring']['success'] = false;
}

// Remote Get.
$fields['remote_get']['name'] = __( 'Remote get status', 'hocwp-theme' );

$response = wp_remote_get( 'http://example.com' );

$response_code = wp_remote_retrieve_response_code( $response );

if ( $response_code == 200 ) {
	$fields['remote_get']['success'] = true;
} else {
	$fields['remote_get']['success'] = false;
}

foreach ( $fields as $field ) {
	printf( '%s: %s', $field['name'], ( $field['success'] ? $yes : $no ) . PHP_EOL );
}

if ( ! is_admin() ) {
	echo '----------------------------------------------------------------------------------------------------' . PHP_EOL;
	print_r( $_SERVER );
}

hocwp_theme_dev_info_section_close();

$number = 20;

if ( ! is_admin() ) {
	$number = - 1;
}

$files = HT()->get_last_modified_files( get_template_directory(), $number );

if ( HT()->array_has_value( $files ) ) {
	hocwp_theme_dev_info_section_open( __( 'Recent Modified Theme Files', 'hocwp-theme' ) );

	$count = 1;

	foreach ( $files as $path => $time ) {
		printf( '%s. %s: %s', $count, $path, date( 'Y-m-d H:i:s', $time ) . PHP_EOL );
		$count ++;
	}

	hocwp_theme_dev_info_section_close();
}

if ( ! is_admin() ) {
	$path = get_template_directory() . '/style.css';

	if ( file_exists( $path ) ) {
		$style = HT_Util()->read_all_text( $path );
		$style = substr( $style, 0, strpos( $style, '*/' ) + 2 );
		hocwp_theme_dev_info_section_open( __( 'Theme Style File', 'hocwp-theme' ) );
		print_r( $style );
		hocwp_theme_dev_info_section_close();
	}
}

$path = HOCWP_THEME_CUSTOM_PATH . '/readme.txt';

if ( file_exists( $path ) ) {
	$data = HT_Util()->read_all_text( $path );
	hocwp_theme_dev_info_section_open( __( 'Theme Custom Readme', 'hocwp-theme' ) );
	print_r( $data );
	echo PHP_EOL;
	hocwp_theme_dev_info_section_close();
}

$path = HOCWP_THEME_CORE_PATH . '/readme.txt';

if ( file_exists( $path ) ) {
	$data = HT_Util()->read_all_text( $path );
	hocwp_theme_dev_info_section_open( __( 'Theme Core Readme', 'hocwp-theme' ) );
	print_r( $data );
	echo PHP_EOL;
	hocwp_theme_dev_info_section_close();
}

hocwp_theme_dev_info_section_open( __( 'Active Plugins', 'hocwp-theme' ) );
$data = get_option( 'active_plugins' );

if ( is_multisite() ) {
	$network_activated_plugins = array_keys( get_site_option( 'active_sitewide_plugins', array() ) );

	$data = array_merge( $data, $network_activated_plugins );
}

if ( HT()->array_has_value( $data ) ) {
	foreach ( $data as $key => $pl ) {
		$plugin = get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . $pl );

		$data[ $pl ] = $plugin;
		unset( $data[ $key ] );
	}
}

$data['count'] = count( $data );

print_r( $data );
hocwp_theme_dev_info_section_close();

if ( ! is_admin() ) {
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

	hocwp_theme_dev_info_section_open( __( 'Theme Core Object', 'hocwp-theme' ) );
	$theme = $GLOBALS['hocwp_theme'];
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
}

do_action( 'hocwp_theme_display_development_information' );