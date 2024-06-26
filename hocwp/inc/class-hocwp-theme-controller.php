<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 * Main class to load all theme functions.
 */

final class HOCWP_Theme_Controller {
	public $core_version = HOCWP_THEME_CORE_VERSION;
	public $is_developing = HOCWP_THEME_DEVELOPING;
	public $theme_path = HOCWP_THEME_PATH;
	public $theme_url = HOCWP_THEME_URL;
	public $core_path = HOCWP_THEME_CORE_PATH;
	public $core_url = HOCWP_THEME_CORE_URL;
	public $css_suffix = HOCWP_THEME_CSS_SUFFIX;
	public $js_suffix = HOCWP_THEME_JS_SUFFIX;

	public $template;
	public $stylesheet;
	public $is_child_theme;

	public $custom_path = HOCWP_THEME_CUSTOM_PATH;
	public $custom_url = HOCWP_THEME_CUSTOM_URL;
	public $custom_current_path = HOCWP_THEME_CUSTOM_CURRENT_PATH;
	public $custom_current_url = HOCWP_THEME_CUSTOM_CURRENT_URL;

	public $doing_ajax = HOCWP_THEME_DOING_AJAX;
	public $doing_cron = HOCWP_THEME_DOING_CRON;

	protected $textdomain = 'hocwp-theme';
	protected $prefix = 'hocwp_theme';
	protected $short_name = 'ht_';

	protected static $instance;

	public $object;
	public $captcha;

	public $loop_data = array();
	public $temp_data = array();

	public $protocol;

	public function __construct() {
		if ( self::$instance ) {
			HT_Util()->doing_it_wrong( __CLASS__, sprintf( __( '%s is a singleton class and you cannot create a second instance.', 'hocwp-theme' ), get_class( $this ) ), '6.4.1' );

			return;
		}

		$this->object = new stdClass();

		$this->object->theme_core_version = HOCWP_THEME_CORE_VERSION;
		$this->object->theme_core_path    = HOCWP_THEME_CORE_PATH;
		$this->object->theme_core_url     = HOCWP_THEME_CORE_URL;

		self::$instance = $this;

		add_action( 'after_setup_theme', array( $this, 'load' ), 0 );
	}

	private function defaults() {
		global $hocwp_theme, $is_opera, $hocwp_theme_protocol;

		$this->object->browser = HT()->get_browser();

		if ( empty( $hocwp_theme_protocol ) ) {
			$hocwp_theme_protocol = ( isset( $_SERVER['HTTPS'] ) && strtolower( $_SERVER['HTTPS'] ) != 'off' ) ? 'https://' : 'http://';
		}

		$this->protocol = $hocwp_theme_protocol;

		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$is_opera = ( HT()->string_contain( $_SERVER['HTTP_USER_AGENT'], 'Opera' ) || HT()->string_contain( $_SERVER['HTTP_USER_AGENT'], 'OPR/' ) );
		}

		if ( ! isset( $this->object->client_info ) ) {
			$client_info = $_COOKIE['hocwp_theme_client_info'] ?? '';

			if ( empty( $client_info ) ) {
				$client_info = $_SESSION['hocwp_theme_client_info'] ?? '';
			}

			if ( is_string( $client_info ) ) {
				$client_info = HT()->json_string_to_array( $client_info );
			}

			$this->object->client_info = (array) $client_info;
		}

		if ( ! isset( $this->object->temp_data ) ) {
			$this->object->temp_data = array();
		}

		if ( ! isset( $this->object->loop_data ) ) {
			$this->object->loop_data = array();
		}

		if ( ! isset( $this->object->options ) ) {
			$this->object->options = $this->get_options();
		}

		$this->object->is_wc_activated = class_exists( 'WC_Product' );

		if ( ! isset( $this->object->active_extensions ) ) {
			$this->object->active_extensions = (array) get_option( 'hocwp_theme_active_extensions', array() );
		}

		if ( ! isset( $this->object->option ) ) {
			$this->object->option = '';
		}

		$this->object->users_can_register = (bool) get_option( 'users_can_register' );

		$this->object->default_sidebars = array(
			array(
				'id'          => 'home',
				'name'        => __( 'Home Sidebar', 'hocwp-theme' ),
				'description' => __( 'Display widgets on home page.', 'hocwp-theme' )
			),
			array(
				'id'          => 'search',
				'name'        => __( 'Search Sidebar', 'hocwp-theme' ),
				'description' => __( 'Display widgets on search result page.', 'hocwp-theme' )
			),
			array(
				'id'          => 'archive',
				'name'        => __( 'Archive Sidebar', 'hocwp-theme' ),
				'description' => __( 'Display widgets on archive page.', 'hocwp-theme' )
			),
			array(
				'id'          => 'single',
				'name'        => __( 'Single Sidebar', 'hocwp-theme' ),
				'description' => __( 'Display widgets on single page.', 'hocwp-theme' )
			),
			array(
				'id'          => 'page',
				'name'        => __( 'Page Sidebar', 'hocwp-theme' ),
				'description' => __( 'Display widgets on page.', 'hocwp-theme' )
			),
			array(
				'id'          => 'page_404',
				'name'        => __( 'Not Found Sidebar', 'hocwp-theme' ),
				'description' => __( 'Display widgets on 404 page.', 'hocwp-theme' )
			)
		);

		if ( ! isset( $this->object->defaults ) ) {
			$this->object->defaults = array();
		}

		$this->object->defaults['blacklist_keys']   = array();
		$this->object->defaults['blacklist_keys'][] = 'sex';
		$this->object->defaults['blacklist_keys'][] = 'adult';
		$this->object->defaults['blacklist_keys'][] = 'porn';
		$this->object->defaults['blacklist_keys'][] = 'ass';
		$this->object->defaults['blacklist_keys'][] = 'penis';
		$this->object->defaults['blacklist_keys'][] = 'tits';
		$this->object->defaults['blacklist_keys'][] = 'viagra';
		$this->object->defaults['blacklist_keys'][] = 'lesbian';

		$this->object->defaults['date_format']     = get_option( 'date_format' );
		$this->object->defaults['time_format']     = get_option( 'time_format' );
		$this->object->defaults['timezone_string'] = get_option( 'timezone_string' );
		$this->object->defaults['posts_per_page']  = get_option( 'posts_per_page' );
		$this->object->defaults['locale']          = get_locale();

		/*
		 * SMTP Email
		 */
		$this->object->defaults['options']['smtp']['from_name']  = get_bloginfo( 'name' );
		$this->object->defaults['options']['smtp']['from_email'] = get_bloginfo( 'admin_email' );
		$this->object->defaults['options']['smtp']['port']       = 465;
		$this->object->defaults['options']['smtp']['encryption'] = 'ssl';

		/*
		 * Discussion
		 */
		$this->object->defaults['options']['discussion']['avatar_size']    = 48;
		$this->object->defaults['options']['discussion']['comment_system'] = 'default';

		/*
		 * General
		 */
		$this->object->defaults['options']['general']['logo_display'] = 'image';

		/*
		 * Home
		 */
		$this->object->defaults['options']['home']['posts_per_page'] = isset( $this->object->options['home']['posts_per_page'] ) ? absint( $this->object->options['home']['posts_per_page'] ) : $this->object->defaults['posts_per_page'];

		/*
		 * Reading
		 */
		$this->object->defaults['options']['reading']['excerpt_more'] = '&hellip;';

		/*
		 * Media
		 */
		$this->object->defaults['options']['media']['upload_per_day'] = 10;

		/*
		 * VIP Management
		 */
		$this->object->defaults['options']['vip']['post_price'] = 100;

		$this->object->options = wp_parse_args( $this->object->options, $this->object->defaults['options'] );

		$hocwp_theme = $this->object;
	}

	public function get_options() {
		if ( ! isset( $this->object->options ) ) {
			$this->object->options = array();
		}

		if ( ! HT()->array_has_value( $this->object->options ) ) {
			$this->object->options = (array) get_option( $this->get_prefix() );

			if ( ! isset( $this->object->defaults ) ) {
				$this->object->defaults = array();
			}

			if ( isset( $this->object->defaults['options'] ) && HT()->array_has_value( $this->object->defaults['options'] ) ) {
				$this->object->options = wp_parse_args( $this->object->options, $this->object->defaults['options'] );
			}
		}

		// Remove empty value as 0 and 1 index
		$this->object->options = array_filter( $this->object->options );

		return apply_filters( 'hocwp_theme_options', $this->object->options );
	}

	public function get_textdomain() {
		return $this->textdomain;
	}

	public function get_prefix() {
		return $this->prefix;
	}

	public function get_short_name() {
		return $this->short_name;
	}

	public function verify_nonce( $nonce_name ) {
		return HT_Util()->verify_nonce( is_child_theme() ? get_stylesheet() : $this->textdomain, $nonce_name );
	}

	public function get_default( $key ) {
		return $this->object->defaults[ $key ] ?? '';
	}

	public function get_date_format() {
		return $this->get_default( 'date_format' );
	}

	public function get_time_format() {
		return $this->get_default( 'time_format' );
	}

	public function get_date_time_format( $time = true ) {
		$format = $this->get_date_format();

		if ( $time ) {
			$format .= ' ' . $this->get_time_format();
		}

		return $format;
	}

	public function the_date( $format = '', $post = null, $time = true ) {
		if ( empty( $format ) ) {
			$format = $this->get_date_time_format( $time );
		}

		echo get_the_time( $format, $post );
	}

	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Load all extensions.
	 *
	 * @param string $base_path The path contains ext folder.
	 */
	public function load_extensions( $base_path ) {
		$exts = get_option( 'hocwp_theme_active_extensions' );

		if ( is_array( $exts ) && 0 < count( $exts ) ) {
			$path = trailingslashit( $base_path );

			if ( ! isset( $this->object->loaded_extensions ) ) {
				$this->object->loaded_extensions = array();
			}

			$exts = array_diff( $exts, $this->object->loaded_extensions );

			$invalid_exts = array();

			foreach ( $exts as $ext ) {
				$ext_file = $path . $ext;

				if ( file_exists( $ext_file ) ) {
					$data = get_file_data( $ext_file, array( 'name' => 'Name', 'requires_core' => 'Requires core' ) );

					$requires_core = $data['requires_core'] ?? '';

					if ( ! empty( $requires_core ) && version_compare( HOCWP_THEME_CORE_VERSION, $requires_core, '<' ) ) {
						$data['file'] = $ext_file;

						$data['error_code'] = 'invalid_core';

						$invalid_exts[] = $data;

						continue;
					}

					load_template( $ext_file );
					$this->object->loaded_extensions[] = $ext;
				}
			}

			if ( HT()->array_has_value( $invalid_exts ) ) {
				update_option( 'hocwp_theme_invalid_extensions', $invalid_exts );
			} else {
				delete_option( 'hocwp_theme_invalid_extensions' );
			}

			unset( $path, $ext, $ext_file );
		}

		unset( $exts );
	}

	public function get_widget_classes( $base_path = '' ) {
		if ( empty( $base_path ) ) {
			$base_path = $this->core_path;
		}

		$base_path = trailingslashit( $base_path );

		$base_path .= 'widgets';

		$result = array();

		if ( is_dir( $base_path ) ) {
			$files = HT()->scandir( $base_path );

			foreach ( $files as $file ) {
				$path = trailingslashit( $base_path ) . $file;
				$info = pathinfo( $path );

				if ( isset( $info['extension'] ) && 'php' == $info['extension'] ) {
					if ( HT()->string_contain( $info['filename'], 'class-' ) ) {
						$name = HT_Util()->get_class_name_from_file( $path );

						$result[ $path ] = $name;
					}
				}
			}

			unset( $files, $file, $path, $info, $name );
		}

		return $result;
	}

	public function load_widgets( $base_path = '' ) {
		$widgets = $this->get_widget_classes( $base_path );

		foreach ( $widgets as $path => $class ) {
			load_template( $path );
		}

		unset( $widgets, $path, $class );
	}

	public function get_ajax_url() {
		return apply_filters( 'hocwp_theme_ajax_url', admin_url( 'admin-ajax.php' ) );
	}

	/**
	 * Detect file in child theme first.
	 *
	 * @param $path
	 *
	 * @return string
	 */
	private function load_child_first( $path ) {
		if ( ! $this->is_child_theme ) {
			$this->template   = get_option( 'template' );
			$this->stylesheet = get_option( 'stylesheet' );

			$this->is_child_theme = ( $this->stylesheet != $this->template && $this->template = $this->textdomain );
		}

		if ( is_string( $path ) && ! empty( $path ) ) {
			$path = ltrim( $path, '/' );

			if ( $this->is_child_theme ) {
				$path = trailingslashit( $this->custom_current_path ) . $path;
			} else {
				$path = trailingslashit( $this->custom_path ) . $path;
			}
		}

		return $path;
	}

	/**
	 * Theme load
	 */
	public function load() {
		global $pagenow;

		/**
		 * Check class HocWP_Theme exists.
		 */
		if ( class_exists( 'HOCWP_Theme' ) ) {
			return;
		}

		if ( HOCWP_THEME_DEVELOPING ) {
			// Load all development configurations.
			$dev_config = trailingslashit( $_SERVER['DOCUMENT_ROOT'] ) . 'dev-config.php';

			if ( file_exists( $dev_config ) ) {
				require_once $dev_config;
			}
		}

		// Allow user run custom hook before theme load
		$pre_hook = $this->load_child_first( 'pre-hook.php' );

		if ( ( is_file( $pre_hook ) && file_exists( $pre_hook ) ) ) {
			require $pre_hook;
		}

		// Check if theme must use child theme or can only use one root theme
		if ( ! defined( 'HOCWP_THEME_FORCE_PARENT' ) || HOCWP_THEME_FORCE_PARENT ) {
			$theme = wp_get_theme();

			if ( empty( $theme->parent() ) ) {
				$msg = __( '<strong>HocWP Theme:</strong> Your current theme is a parent theme developed by HocWP Team, please create a child theme to use it.', 'hocwp-theme' );

				if ( is_admin() ) {
					global $pagenow;

					if ( 'themes.php' != $pagenow ) {
						wp_redirect( admin_url( 'themes.php' ) );
						exit;
					}

					add_action( 'admin_notices', function () use ( $msg ) {
						?>
                        <div class="notice notice-error is-dismissible">
                            <p><?php echo $msg; ?></p>
                        </div>
						<?php
					} );
				} else {
					$args = array(
						'link_url'  => admin_url( 'themes.php' ),
						'link_text' => __( 'Change Theme', 'hocwp-theme' ),
						'back_link' => false,
						'code'      => 'missing_child_theme'
					);

					wp_die( $msg, __( 'Missing Child Theme', 'hocwp-theme' ), $args );
				}

				return;
			}
		}

		// Load all deprecated functions
		require $this->core_path . '/inc/functions-deprecated.php';

		// Load PHP 8 functions
		if ( HOCWP_THEME_SUPPORT_PHP8 ) {
			require $this->core_path . '/inc/class-hocwp-theme-php8.php';
		}

		// Load all default messages text.
		require $this->core_path . '/inc/class-hocwp-theme-message.php';

		// Load all normal PHP utility functions
		require $this->core_path . '/inc/class-hocwp-theme.php';

		require $this->core_path . '/inc/abstract-class-hocwp-theme-google-api.php';
		require $this->core_path . '/inc/class-hocwp-theme-youtube-api.php';
		require $this->core_path . '/inc/class-hocwp-theme-google-maps-api.php';
		require $this->core_path . '/inc/class-hocwp-theme-google-maps-find-place-api.php';
		require $this->core_path . '/inc/class-hocwp-theme-google-maps-autocomplete-api.php';
		require $this->core_path . '/inc/class-hocwp-theme-google-maps-distance-matrix-api.php';
		require $this->core_path . '/inc/class-hocwp-theme-google-maps-geocode-api.php';

		require $this->core_path . '/inc/class-hocwp-theme-sanitize.php';
		require $this->core_path . '/inc/class-hocwp-theme-enqueue.php';
		require $this->core_path . '/inc/class-hocwp-theme-utility.php';

		require $this->core_path . '/inc/class-hocwp-theme-options.php';

		require $this->core_path . '/inc/abstract-class-hocwp-theme-captcha.php';
		require $this->core_path . '/inc/class-hocwp-theme-captcha-hcaptcha.php';
		require $this->core_path . '/inc/class-hocwp-theme-captcha-recaptcha.php';
		require $this->core_path . '/inc/class-hocwp-theme-captcha.php';

		$this->captcha = HT_CAPTCHA();

		if ( is_admin() ) {
			require $this->core_path . '/admin/class-hocwp-theme-admin.php';
		}

		// Load front-end class on frontend and customize preview pages.
		if ( ! is_admin() || is_customize_preview() ) {
			require $this->core_path . '/inc/class-hocwp-theme-frontend.php';
		}

		require $this->core_path . '/inc/class-hocwp-theme-requirement.php';
		require $this->core_path . '/inc/class-hocwp-theme-svg-icon.php';

		//require $this->core_path . '/inc/class-hocwp-theme-color.php';

		require $this->core_path . '/inc/class-hocwp-theme-html-tag.php';
		require $this->core_path . '/inc/class-hocwp-theme-layout.php';

		require $this->core_path . '/inc/class-hocwp-theme-html-field.php';

		require $this->core_path . '/inc/class-hocwp-theme-metas.php';
		require $this->core_path . '/inc/abstract-class-hocwp-theme-object.php';
		require $this->core_path . '/inc/class-hocwp-theme-post.php';
		require $this->core_path . '/inc/class-hocwp-theme-term.php';

		require $this->core_path . '/inc/class-hocwp-theme-query.php';

		require $this->core_path . '/inc/template-tags.php';

		require $this->core_path . '/inc/functions-scripts.php';
		require $this->core_path . '/inc/functions-media.php';
		require $this->core_path . '/inc/functions-user.php';
		require $this->core_path . '/inc/functions-preprocess.php';
		require $this->core_path . '/inc/functions-extensions.php';
		require $this->core_path . '/inc/class-hocwp-theme-extension.php';

		require $this->core_path . '/inc/setup.php';

		$this->defaults();
		require $this->core_path . '/inc/defaults.php';

		require $this->core_path . '/inc/functions-permalinks.php';
		require $this->core_path . '/inc/functions-license.php';

		/**
		 * Widgets.
		 */
		$this->load_widgets();

		/**
		 * Extensions.
		 */
		$this->load_extensions( $this->core_path );
		$this->load_extensions( $this->custom_path );
		$this->load_extensions( $this->custom_current_path );

		if ( is_admin() ) {
			require $this->core_path . '/admin/admin.php';
		} else {
			require $this->core_path . '/inc/class-hocwp-theme-walker-nav-menu-bootstrap.php';
			require $this->core_path . '/inc/class-hocwp-theme-walker-nav-menu-link.php';
			require $this->core_path . '/inc/functions-context.php';
		}

		require $this->core_path . '/inc/class-hocwp-theme-walker-page.php';

		if ( is_customize_preview() ) {
			require $this->core_path . '/inc/class-hocwp-theme-customize.php';
		}

		/**
		 * Setup After.
		 */
		require $this->core_path . '/inc/setup-after.php';

		if ( ! is_admin() || is_customize_preview() ) {
			require $this->core_path . '/inc/template.php';
			require $this->core_path . '/inc/template-general.php';
			require $this->core_path . '/inc/template-comments.php';
			require $this->core_path . '/inc/template-post.php';

			if ( 'wp-login.php' == $pagenow ) {
				require $this->core_path . '/inc/template-user.php';
			}
		}

		if ( is_admin() || is_customize_preview() ) {
			require $this->core_path . '/admin/meta.php';
		}

		require $this->core_path . '/inc/abstract-class-hocwp-theme-custom.php';
		HT()->require_if_exists( $this->load_child_first( 'functions.php' ) );
		require $this->core_path . '/inc/back-compat.php';
		HT()->require_if_exists( $this->load_child_first( 'register.php' ) );

		// Autoload all PHP files in custom inc folder
		if ( $this->is_child_theme ) {
			$inc = $this->custom_current_path . '/inc';
		} else {
			$inc = $this->custom_path . '/inc';
		}

		if ( is_dir( $inc ) ) {
			$path  = $inc;
			$files = HT()->scandir( $path );
			$files = array_diff( $files, array( '.', '..', 'index.php' ) );

			foreach ( $files as $file ) {
				HT()->require_if_exists( $inc . '/' . $file );
			}
		}

		HT()->require_if_exists( $this->load_child_first( 'hook.php' ) );

		if ( is_admin() ) {
			add_action( 'admin_menu', function () {
				HT()->require_if_exists( $this->load_child_first( 'admin.php' ) );
			} );
		}

		HT()->require_if_exists( $this->load_child_first( 'extensions.php' ) );

		if ( is_admin() ) {
			require $this->core_path . '/admin/load-custom-meta.php';
			HT()->require_if_exists( $this->load_child_first( 'meta.php' ) );

			if ( $this->doing_ajax ) {
				HT()->require_if_exists( $this->load_child_first( 'ajax.php' ) );
			}
		} else {
			HT()->require_if_exists( $this->load_child_first( 'front-end.php' ) );
			HT()->require_if_exists( $this->load_child_first( 'template.php' ) );
		}

		require_once $this->core_path . '/inc/customizer.php';

		require $this->core_path . '/inc/updates.php';

		do_action( 'hocwp_theme_loaded' );
	}

	public function reset_loop_data( $reset_tmp = false ) {
		$GLOBALS['hocwp_theme']->loop_data = array();

		if ( $reset_tmp ) {
			$GLOBALS['hocwp_theme']->temp_data = array();
		}
	}

	public function set_loop_data( $data = array() ) {
		$GLOBALS['hocwp_theme']->loop_data = $data;
	}

	public function add_loop_data( $key, $value ) {
		if ( $key instanceof WP_Query ) {
			$value = $key;
			$key   = 'query';
		}

		$GLOBALS['hocwp_theme']->loop_data[ $key ] = $value;
	}

	public function remove_loop_data( $key ) {
		unset( $GLOBALS['hocwp_theme']->loop_data[ $key ] );
	}

	public function get_loop_data( $key ) {
		return HT()->get_value_in_array( $GLOBALS['hocwp_theme']->loop_data, $key );
	}
}

function HOCWP_Theme() {
	global $ht_controller;

	// Instantiate only once.
	if ( ! isset( $ht_controller ) ) {
		$ht_controller = HOCWP_Theme_Controller::get_instance();
	}

	return $ht_controller;
}

HOCWP_Theme();

function HT_Control() {
	return HOCWP_Theme();
}