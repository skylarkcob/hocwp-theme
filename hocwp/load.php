<?php
defined( 'ABSPATH' ) || exit;

/**
 * Theme core version.
 */
const HOCWP_THEME_CORE_VERSION = '7.0.8';

class HOCWP_Theme_Load {
	protected static $instance;

	public static function get_instance(): HOCWP_Theme_Load {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function __construct() {
		global $hocwp_current_theme;

		if ( ! $hocwp_current_theme ) {
			$hocwp_current_theme = wp_get_theme();
		}

		$require_version = $hocwp_current_theme->get( 'RequiresPHP' );

		// If theme not declare required PHP version, just provide new.
		if ( empty( $require_version ) ) {
			$require_version = '8.1';
		}

		/**
		 * Requires PHP version.
		 */
		define( 'HOCWP_THEME_REQUIRE_PHP_VERSION', $require_version );

		/**
		 * Theme developing mode.
		 */
		define( 'HOCWP_THEME_DEVELOPING', ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) );

		define( 'HOCWP_THEME_SUPPORT_PHP8', version_compare( phpversion(), '8.1', '>=' ) );

		/**
		 * Current theme root path or theme parent root path.
		 */
		define( 'HOCWP_THEME_PATH', get_template_directory() );

		/**
		 * Theme child root path.
		 */
		define( 'HOCWP_THEME_CURRENT_PATH', get_stylesheet_directory() );

		/**
		 * Current theme base url or theme parent base url.
		 */
		define( 'HOCWP_THEME_URL', get_template_directory_uri() );

		/**
		 * Theme child base url.
		 */
		define( 'HOCWP_THEME_CURRENT_URL', get_stylesheet_directory_uri() );

		/**
		 * Theme core base url.
		 */
		define( 'HOCWP_THEME_CORE_URL', untrailingslashit( HOCWP_THEME_URL ) . '/hocwp' );

		/**
		 * Detect doing ajax or not.
		 */
		define( 'HOCWP_THEME_DOING_AJAX', ( defined( 'DOING_AJAX' ) && true === DOING_AJAX ) );

		/**
		 * Detect doing cron or not.
		 */
		define( 'HOCWP_THEME_DOING_CRON', ( defined( 'DOING_CRON' ) && true === DOING_CRON ) );

		/**
		 * Theme core path.
		 */
		define( 'HOCWP_THEME_CORE_PATH', __DIR__ );

		define( 'HOCWP_THEME_INC_PATH', HOCWP_THEME_CORE_PATH . '/inc' );

		/**
		 * CSS suffix.
		 */
		define( 'HOCWP_THEME_CSS_SUFFIX', '.css' );

		/**
		 * Javascript suffix.
		 */
		define( 'HOCWP_THEME_JS_SUFFIX', '.js' );

		/**
		 * Theme custom path or theme parent custom path.
		 */
		define( 'HOCWP_THEME_CUSTOM_PATH', HOCWP_THEME_PATH . '/custom' );

		/**
		 * Child theme custom path.
		 */
		define( 'HOCWP_THEME_CUSTOM_CURRENT_PATH', HOCWP_THEME_CURRENT_PATH . '/custom' );

		/**
		 * Theme custom base url or theme parent custom base url.
		 */
		define( 'HOCWP_THEME_CUSTOM_URL', HOCWP_THEME_URL . '/custom' );

		/**
		 * Child theme custom base url.
		 */
		define( 'HOCWP_THEME_CUSTOM_CURRENT_URL', HOCWP_THEME_CURRENT_URL . '/custom' );

		/**
		 * The dot image for lazyload.
		 */
		define( 'HOCWP_THEME_DOT_IMAGE_SRC', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=' );

		/**
		 * Check current PHP version.
		 */
		$php_version = phpversion();

		// Check if current PHP version less than required PHP version.
		if ( version_compare( $php_version, $require_version, '<' ) ) {
			$dir = HOCWP_THEME_PATH;
			$dir = dirname( $dir );

			// Find all themes in wp-content/themes folder.
			$dirs = array_filter( glob( $dir . '/*' ), 'is_dir' );

			// Check and switch to another theme.
			if ( ! empty( $dirs ) ) {
				$msg   = sprintf( __( '<strong>Error:</strong> You are using PHP version %s, please upgrade PHP version to at least %s.', 'hocwp-theme' ), $php_version, $require_version );
				$title = __( 'Invalid PHP Version', 'hocwp-theme' );

				$args = array(
					'back_link' => admin_url( 'themes.php' )
				);

				$has = false;

				foreach ( $dirs as $dir ) {
					$folder = basename( $dir );

					// Skip current theme and parent theme.
					if ( $folder == $hocwp_current_theme->get_stylesheet() || $folder == $hocwp_current_theme->get_template() ) {
						continue;
					}

					$has = true;

					$theme = wp_get_theme( $folder );
					$uri   = $theme->get( 'ThemeURI' );

					// Check and switch to default WordPress theme.
					if ( str_contains( $uri, 'wordpress.org' ) ) {
						switch_theme( $folder );
						wp_die( $msg, $title, $args );
					}
				}

				// If it has another theme not default WordPress theme.
				if ( $has ) {
					$dir    = array_shift( $dirs );
					$folder = basename( $dir );
					switch_theme( $folder );
					wp_die( $msg, $title, $args );
				}

				unset( $args, $title, $has, $msg );
			}

			unset( $dir, $dirs );
		}

		unset( $theme, $php_version, $require_version );

		// Include 3rd party compatibility.
		require_once( HOCWP_THEME_INC_PATH . '/third-party.php' );

		/*
		 * Load Theme Controller Class.
		 */
		require_once( HOCWP_THEME_INC_PATH . '/class-hocwp-theme-controller.php' );
	}
}

HOCWP_THEME_Load::get_instance();