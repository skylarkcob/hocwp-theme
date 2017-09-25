<?php
if ( ! defined( 'HOCWP_THEME_DEVELOPING' ) || 1 != HOCWP_THEME_DEVELOPING ) {
	return;
}

global $hocwp_theme;
$hocwp_theme->defaults['compress_css_and_js_paths'] = $paths = array(
	get_template_directory(),
	HOCWP_THEME_CORE_PATH,
	get_template_directory() . '/custom'
);
$hocwp_theme->defaults['compress_css_and_js_paths'] = apply_filters( 'hocwp_theme_compress_css_and_js_paths', $hocwp_theme->defaults['compress_css_and_js_paths'] );

function hocwp_theme_debug( $value ) {
	if ( is_array( $value ) || is_object( $value ) ) {
		error_log( print_r( $value, true ) );
	} else {
		error_log( $value );
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
	$source     = str_replace( '\\', '/', realpath( $source ) );
	$filesystem = HOCWP_Theme_Utility::filesystem();
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
				$zip->addFromString( str_replace( $source . '/', '', $file ), $filesystem->get_contents( $file ) );
			}
		}
	} else if ( is_file( $source ) === true ) {
		$zip->addFromString( basename( $source ), $filesystem->get_contents( $source ) );
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
	}
}

add_action( 'wp_loaded', 'hocwp_theme_auto_create_backup_current_theme' );
add_action( 'hocwp_theme_upgrade_new_version', 'hocwp_theme_zip_current_theme', 99 );

function hocwp_theme_admin_development_scripts() {
	global $hocwp_theme, $pagenow, $plugin_page;
	if ( 'themes.php' == $pagenow && 'themecheck' == $plugin_page ) {
		wp_enqueue_style( 'hocwp-theme-admin-themecheck-style', HOCWP_THEME_CORE_URL . '/css/admin-themecheck' . HOCWP_THEME_CSS_SUFFIX );
	}
	if ( 'themes.php' == $pagenow && 'hocwp_theme' == $plugin_page && 'development' == $hocwp_theme->option->tab ) {
		wp_enqueue_style( 'hocwp-theme-ajax-overlay-style' );
		wp_enqueue_script( 'admin-settings-page-development', HOCWP_THEME_CORE_URL . '/js/admin-settings-page-development' . HOCWP_THEME_JS_SUFFIX, array(
			'hocwp-theme-admin',
			'hocwp-theme-ajax-button'
		), false, true );
	}
}

add_action( 'admin_enqueue_scripts', 'hocwp_theme_admin_development_scripts' );

function hocwp_theme_update_ver_css_js_realtime( $src ) {
	if ( false !== strpos( $src, 'ver=' ) ) {
		$src = add_query_arg( array( 'ver' => time() ), $src );
	}

	return $src;
}

add_filter( 'style_loader_src', 'hocwp_theme_update_ver_css_js_realtime', 9999 );
add_filter( 'script_loader_src', 'hocwp_theme_update_ver_css_js_realtime', 9999 );

function hocwp_theme_compress_all_css_and_js( $paths = null ) {
	if ( ! is_array( $paths ) ) {
		global $hocwp_theme;
		$paths = $hocwp_theme->defaults['compress_css_and_js_paths'];
	}
	if ( ! class_exists( 'HOCWP_Theme_Minify' ) ) {
		require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-minify.php';
	}
	foreach ( $paths as $path ) {
		if ( is_dir( $path ) ) {
			$css = $path . '/css';
			_hocwp_theme_compress_all_css_and_js( $css );
			$js = $path . '/js';
			_hocwp_theme_compress_all_css_and_js( $js );
		} elseif ( is_readable( $path ) ) {
			_hocwp_theme_compress_all_css_and_js( $path );
		}
	}
}

function hocwp_theme_development_admin_notices() {

}

add_action( 'admin_notices', 'hocwp_theme_development_admin_notices' );

function _hocwp_theme_compress_all_css_and_js( $dir ) {
	if ( is_dir( $dir ) ) {
		hocwp_theme_debug( '---------------------------------------------------------------------------------------------' );
		hocwp_theme_debug( sprintf( __( 'Scanning directory %s', 'hocwp-theme' ), $dir ) );
		hocwp_theme_debug( '---------------------------------------------------------------------------------------------' );
		$files = scandir( $dir );
		if ( ! class_exists( 'HOCWP_Theme_Minify' ) ) {
			require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-minify.php';
		}
		foreach ( $files as $file ) {
			if ( ! _hocwp_theme_is_css_or_js_file( $file ) ) {
				continue;
			}
			$file = trailingslashit( $dir ) . $file;
			hocwp_theme_debug( sprintf( __( 'Minifying file %s', 'hocwp-theme' ), $file ) );
			HOCWP_Theme_Minify::generate( $file );
		}
	} elseif ( is_readable( $dir ) ) {
		hocwp_theme_debug( sprintf( __( 'Minifying file %s', 'hocwp-theme' ), $dir ) );
		HOCWP_Theme_Minify::generate( $dir );
	}
}

function _hocwp_theme_is_css_or_js_file( $file ) {
	$info = pathinfo( $file );

	return ! ( ! isset( $info['extension'] ) || ( 'js' != $info['extension'] && 'css' != $info['extension'] ) );
}

function hocwp_theme_execute_development_ajax_callback() {
	$result = array();
	hocwp_theme_debug( '/* ============================================= ' . date( 'Y-m-d H:i:s' ) . ' ============================================= */' );
	hocwp_theme_debug( __( 'Building theme environment...', 'hocwp-theme' ) );
	$compress_css_and_js = isset( $_POST['compress_css_and_js'] ) ? $_POST['compress_css_and_js'] : '';
	if ( 'true' == $compress_css_and_js || ( 'false' != $compress_css_and_js && ! empty( $compress_css_and_js ) ) ) {
		if ( 'true' != $compress_css_and_js ) {
			$compress_css_and_js = HOCWP_Theme::json_string_to_array( $compress_css_and_js );
			$tmp                 = array();
			foreach ( $compress_css_and_js as $path ) {
				$tmp[] = $path;
			}
			$compress_css_and_js = $tmp;
		}
		hocwp_theme_debug( __( 'Sarting to generate minified files...', 'hocwp-theme' ) );
		hocwp_theme_compress_all_css_and_js( $compress_css_and_js );
		hocwp_theme_debug( __( 'All files compressed...', 'hocwp-theme' ) );
	}
	$publish_release = isset( $_POST['publish_release'] ) ? $_POST['publish_release'] : '';
	if ( 'true' == $publish_release ) {
		hocwp_theme_debug( __( 'Creating zip file...', 'hocwp-theme' ) );
		$ziped = hocwp_theme_zip_current_theme();
		if ( $ziped ) {
			hocwp_theme_debug( __( 'Theme has been compressed successfully.', 'hocwp-theme' ) );
		} else {
			hocwp_theme_debug( __( 'The theme can not be compressed.', 'hocwp-theme' ) );
		}
	}
	hocwp_theme_debug( __( 'Tasks Finished', 'hocwp-theme' ) );
	hocwp_theme_debug( '/* ============================================= ' . date( 'Y-m-d H:i:s' ) . ' ============================================= */' );
	wp_send_json( $result );
}

if ( is_admin() ) {
	add_action( 'wp_ajax_hocwp_theme_execute_development', 'hocwp_theme_execute_development_ajax_callback' );
}

function hocwp_theme_debug_save_queries() {
	if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES && current_user_can( 'administrator' ) ) {
		global $wpdb;
		if ( is_admin() ) {
			hocwp_theme_debug( '/* ============================= BACK-END QUERIES ============================= */' );
		} else {
			hocwp_theme_debug( '/* ============================= FRONT-END QUERIES ============================= */' );
		}
		hocwp_theme_debug( $wpdb->queries );
	}
}

if ( is_admin() ) {
	add_action( 'admin_footer', 'hocwp_theme_debug_save_queries', 9999 );
}
add_action( 'wp_footer', 'hocwp_theme_debug_save_queries', 9999 );

function hocwp_theme_dev_global_scripts() {
	$domain = HOCWP_Theme::get_domain_name( home_url(), true );
	if ( 'localhost' == $domain ) {
		wp_enqueue_script( 'taking-breaks', HOCWP_THEME_CORE_URL . '/js/taking-breaks' . HOCWP_THEME_JS_SUFFIX, array(
			'jquery',
			'hocwp-theme'
		), false, true );
	}
}

add_action( 'hocwp_theme_global_enqueue_scripts', 'hocwp_theme_dev_global_scripts' );

function hocwp_theme_dev_taking_breaks_ajax_callback() {
	$result = array(
		'taking_break' => false,
		'message'      => ''
	);
	$tb     = 'hocwp_theme_dev_taking_breaks';
	if ( false === get_transient( $tb ) ) {
		$tr_name   = 'hocwp_theme_dev_taking_breaks_timestamp';
		$timestamp = get_transient( $tr_name );
		$current   = time();
		if ( false === $timestamp ) {
			delete_transient( $tb );
			set_transient( $tr_name, $current );
		} else {
			$diff = absint( $current - $timestamp );

			$result['diff'] = $diff;
			$diff /= MINUTE_IN_SECONDS;
			$interval = 25;
			if ( $interval <= $diff ) {
				$result['taking_break'] = true;

				$count = absint( get_transient( 'hocwp_theme_dev_taking_breaks_count' ) );
				$count ++;
				$minute = 5;
				if ( 4 == $count ) {
					$minute = 15;
					$count  = 0;
				}
				set_transient( 'hocwp_theme_dev_taking_breaks_count', $count );
				set_transient( $tb, $minute, $minute * MINUTE_IN_SECONDS );
			} elseif ( ( $interval - 5 ) <= $diff ) {
				$result['message'] = __( 'You will take a break for the next 5 minutes.', 'hocwp-theme' );
			}
		}
	} else {
		$result['taking_break'] = true;
	}
	wp_send_json( $result );
}

add_action( 'wp_ajax_hocwp_theme_dev_taking_breaks', 'hocwp_theme_dev_taking_breaks_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_theme_dev_taking_breaks', 'hocwp_theme_dev_taking_breaks_ajax_callback' );

function hocwp_theme_dev_init_action() {
	if ( ! is_admin() ) {
		$tr_name = 'hocwp_theme_dev_taking_breaks';
		if ( false !== ( $minute = get_transient( $tr_name ) ) ) {
			if ( ! wp_doing_ajax() && ! wp_doing_cron() ) {
				delete_transient( 'hocwp_theme_dev_taking_breaks_timestamp' );
				$minute  = absint( $minute );
				$message = sprintf( __( 'You should take a break and relax for %d minutes.', 'hocwp-theme' ), $minute );
				$message .= '<script>setInterval(function(){window.location.href = window.location.href;},5e3);</script>';
				$title = __( 'Taking Beaks', 'hocwp-theme' );
				if ( 15 <= $minute ) {
					$title = __( 'Taking Long Breaks', 'hocwp-theme' );
				}
				wp_die( $message, $title );
				exit;
			}
		}
	}
}

//add_action( 'init', 'hocwp_theme_dev_init_action' );

function hocwp_theme_dev_remove_url_ver( $src ) {
	if ( strpos( $src, 'ver=' ) ) {
		$src = remove_query_arg( 'ver', $src );
	}

	return $src;
}

add_filter( 'style_loader_src', 'hocwp_theme_dev_remove_url_ver', 9999 );
add_filter( 'script_loader_src', 'hocwp_theme_dev_remove_url_ver', 9999 );