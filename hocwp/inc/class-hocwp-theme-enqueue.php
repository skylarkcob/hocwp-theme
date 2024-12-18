<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Enqueue {
	protected static $instance;

	public $custom_lib_dir;
	public $custom_lib_url;

	public static function get_instance() {
		if ( ! self::$instance instanceof self ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		if ( self::$instance instanceof self ) {
			return;
		}

		$this->custom_lib_dir = hocwp_theme()->custom_path;

		if ( hocwp_theme()->is_child_theme ) {
			$this->custom_lib_dir = hocwp_theme()->custom_current_path;
		}

		$this->custom_lib_dir = trailingslashit( $this->custom_lib_dir );
		$this->custom_lib_dir .= 'lib/';

		$this->custom_lib_url = hocwp_theme()->custom_url;

		if ( hocwp_theme()->is_child_theme ) {
			$this->custom_lib_url = hocwp_theme()->custom_current_url;
		}

		$this->custom_lib_url = trailingslashit( $this->custom_lib_url );
		$this->custom_lib_url .= 'lib/';
	}

	public function custom_lib_style( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {
		if ( false === strpos( $handle, '-style' ) ) {
			$handle .= '-style';
		}

		$src = $this->fix_custom_lib_url( $src );

		wp_enqueue_style( $handle, $src, $deps, $ver, $media );
	}

	private function fix_custom_lib_url( $src ) {
		if ( false === strpos( $src, $this->custom_lib_url ) ) {
			$src = ltrim( $src, '/' );
			$src = $this->custom_lib_url . $src;
		}

		return $src;
	}

	public function custom_lib_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = true ) {
		$src = $this->fix_custom_lib_url( $src );

		if ( is_bool( $deps ) && $deps ) {
			$deps = array( 'jquery' );
		}

		wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
	}

	public function media_upload() {
		wp_enqueue_media();
		wp_enqueue_script( 'hocwp-theme-media-upload' );
		wp_enqueue_style( 'hocwp-theme-media-upload-style' );
	}

	public function sortable() {
		wp_enqueue_style( 'hocwp-theme-sortable-style' );
		wp_enqueue_script( 'hocwp-theme-sortable' );
	}

	public function datepicker() {
		$this->jquery_ui_style();
		wp_enqueue_script( 'hocwp-theme-datepicker' );
	}

	public function datetime_picker() {
		$this->datepicker();
	}

	public function color_picker() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'hocwp-theme-color-picker' );
	}

	public function chosen() {
		wp_enqueue_style( 'chosen-style' );
		wp_enqueue_script( 'chosen-select' );
	}

	public function ajax_overlay() {
		wp_enqueue_style( 'hocwp-theme-ajax-overlay-style' );
		wp_enqueue_script( 'hocwp-theme-ajax-button' );
	}

	public function ajax_pagination() {
		$this->ajax_overlay();
		wp_enqueue_script( 'hocwp-theme-pagination' );
	}

	public function code_editor( $args = array() ) {
		$defaults = array( 'type' => 'text/html' );
		$args     = wp_parse_args( $args, $defaults );

		// Check function exists for WordPress version older than 4.9.0
		if ( function_exists( 'wp_enqueue_code_editor' ) ) {
			wp_enqueue_code_editor( $args );
		}

		wp_enqueue_script( 'hocwp-theme-code-editor' );
	}

	public function google_maps( $api_key = null ) {
		hocwp_theme_load_google_maps_script( $api_key );
	}

	public function update_meta() {
		wp_enqueue_style( 'hocwp-theme-ajax-overlay-style' );
		wp_enqueue_script( 'hocwp-theme-update-meta' );
	}

	public function dashicons() {
		wp_enqueue_style( 'dashicons' );
	}

	public function combobox() {
		$this->jquery_ui_style();
		wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_script( 'jquery-ui-widget' );
		wp_enqueue_script( 'jquery-ui-button' );
		$this->autocomplete();
		wp_enqueue_script( 'hocwp-theme-combobox' );
	}

	public function autocomplete() {
		wp_enqueue_script( 'jquery-ui-autocomplete' );
	}

	public function popper( $args = array() ) {
		$defaults = array(
			'cdn'     => null,
			'version' => '1.16.0',
			'utils'   => false
		);

		$args = wp_parse_args( $args, $defaults );

		$folder_name = 'popper.js';

		if ( ! ht()->is_dir( $this->custom_lib_dir . $folder_name ) ) {
			$folder_name = 'popper';
		}

		$base_dir = $this->custom_lib_dir . $folder_name;

		if ( ! is_dir( $base_dir ) ) {
			return;
		}

		$base_url = $this->custom_lib_url . $folder_name . '/';

		if ( ! empty( $args['version'] ) && is_dir( trailingslashit( $base_dir ) . $args['version'] ) ) {
			$base_dir = trailingslashit( $base_dir );
			$base_dir .= $args['version'];
		}

		if ( $args['cdn'] ) {
			_deprecated_argument( __FUNCTION__, '6.7.7', sprintf( __( 'Stop using %s param from %s for loading resource from CDN.', 'hocwp-theme' ), 'cdn', '$args' ) );
		}

		$file = 'popper.min.js';

		$this->auto_check_lib_version( $args, $base_dir, $file );

		$base_dir = trailingslashit( $base_dir );

		if ( ! empty( $args['version'] ) && false !== strpos( $base_dir, $args['version'] ) ) {
			$base_url .= $args['version'];
		}

		$base_url = trailingslashit( $base_url );

		if ( is_dir( $base_dir . 'umd/' ) ) {
			$base_dir .= 'umd/';
			$base_url .= 'umd/';
		}

		if ( $args['utils'] ) {
			$handle = 'popper-utils-' . $args['version'];
			$handle = sanitize_title( $handle );

			wp_enqueue_script( $handle, $base_url . 'popper-utils.js', array( 'jquery' ), false, true );
		}

		$handle = 'popper-' . $args['version'];
		$handle = sanitize_title( $handle );

		wp_enqueue_script( $handle, $base_url . 'popper.js', array( 'jquery' ), false, true );

		unset( $defaults, $base_url, $base_dir, $handle, $file );
	}

	public function bootstrap( $args = array() ) {
		$defaults = array(
			'cdn'     => null,
			'version' => '3.3.7',
			'js'      => false,
			'theme'   => false,
			'bundle'  => false
		);

		if ( is_array( $args ) ) {
			$args = wp_parse_args( $args, $defaults );
		} else {
			$args = $defaults;
		}

		$base_url = $this->custom_lib_url . 'bootstrap/';
		$base_dir = $this->custom_lib_dir . 'bootstrap/' . $args['version'];

		if ( $args['cdn'] ) {
			_deprecated_argument( __FUNCTION__, '6.7.7', sprintf( __( 'Stop using %s param from %s for loading resource from CDN.', 'hocwp-theme' ), 'cdn', '$args' ) );
		}

		$css_file = 'css/bootstrap.min.css';

		$this->auto_check_lib_version( $args, $base_dir, $css_file );

		$base_dir = trailingslashit( $base_dir );

		if ( ! empty( $args['version'] ) ) {
			$base_url .= $args['version'];
		}

		$base_url = trailingslashit( $base_url );

		$handle = 'bootstrap-' . $args['version'];
		$handle = sanitize_title( $handle );

		wp_enqueue_style( $handle . '-style', $base_url . $css_file );

		if ( $args['theme'] && ht()->is_file( $base_dir . 'css/bootstrap-theme.min.css' ) ) {
			wp_enqueue_style( $handle . '-theme-style', $base_url . 'css/bootstrap-theme.min.css' );
		}

		if ( $args['js'] && ht()->is_file( $base_dir . 'js/bootstrap.min.js' ) ) {
			$popper = isset( $args['popper'] ) ? $args['popper'] : array();

			if ( ht()->array_has_value( $popper ) || $popper ) {
				$this->popper( $popper );
			}

			$name = 'bootstrap';

			if ( $args['bundle'] ) {
				$name .= '.bundle';
			}

			wp_enqueue_script( $handle, $base_url . 'js/' . $name . '.min.js', array( 'jquery' ), false, true );
		}

		unset( $defaults, $base_url, $base_dir, $handle, $css_file );
	}

	private function auto_check_lib_version( &$args, &$base_dir, $abs_file ) {
		if ( ( ! isset( $args['cdn'] ) || ! $args['cdn'] ) && ! ht()->is_dir( $base_dir ) ) {
			// Auto check version
			$tmp  = dirname( $base_dir );
			$tmp  = trailingslashit( $tmp );
			$tmp  .= '*';
			$dirs = glob( $tmp, GLOB_ONLYDIR );

			if ( ht()->array_has_value( $dirs ) ) {
				$tmp = current( $dirs );
				$tmp = trailingslashit( $tmp );

				if ( file_exists( $tmp . $abs_file ) ) {
					$args['version'] = basename( $tmp );

					$base_dir = $tmp;
				}
			}

			if ( ! ht()->is_dir( $base_dir ) ) {
				$base_dir = dirname( $base_dir );
				$base_dir = trailingslashit( $base_dir );

				if ( ! ht()->is_dir( $base_dir ) || ! ht()->is_file( $base_dir . $abs_file ) ) {
					return;
				}

				$args['version'] = '';
			}

			unset( $tmp, $dirs );
		}
	}

	public function font_icons() {
		$base = 'font-icons/css/hocwp-icons.css';

		$path = $this->custom_lib_dir . $base;

		if ( file_exists( $path ) ) {
			wp_enqueue_style( 'hocwp-font-icons-style', $this->custom_lib_url . $base );
		}
	}

	public function swiper( $args = array() ) {
		$defaults = array(
			'css_url' => $this->custom_lib_url . 'swiper/swiper-bundle.min.css',
			'js_url'  => $this->custom_lib_url . 'swiper/swiper-bundle.min.js'
		);

		$args = wp_parse_args( $args, $defaults );

		$css_url = $args['css_url'];
		$js_url  = $args['js_url'];

		if ( ! empty( $css_url ) ) {
			wp_enqueue_style( 'swiper-style', $css_url );
		}

		if ( ! empty( $js_url ) ) {
			wp_enqueue_script( 'swiper', $js_url, array(), false, true );
		}
	}

	public function slick( $args = array() ) {
		$defaults = array(
			'theme' => false
		);

		$args = wp_parse_args( $args, $defaults );

		wp_enqueue_style( 'slick-style', $this->custom_lib_url . 'slick/slick.css' );

		if ( $args['theme'] ) {
			wp_enqueue_style( 'slick-theme-style', $this->custom_lib_url . 'slick/slick-theme.css' );
		}

		wp_enqueue_script( 'slick', $this->custom_lib_url . 'slick/slick.js', array( 'jquery' ), false, true );
	}

	public function fancybox( $version = false ) {
		if ( 4 == $version || ( is_string( $version ) && version_compare( $version, '4', '>=' ) ) ) {
			$css_url = $this->custom_lib_url . 'fancybox/fancybox.css';
			$js_url  = $this->custom_lib_url . 'fancybox/fancybox.umd.js';
		} else {
			$css_url = $this->custom_lib_url . 'fancybox/jquery.fancybox.css';
			$js_url  = $this->custom_lib_url . 'fancybox/jquery.fancybox.js';
		}

		wp_enqueue_style( 'fancybox-style', $css_url );
		wp_enqueue_script( 'fancybox', $js_url, array( 'jquery' ), false, true );
	}

	public function font_awesome( $args = array() ) {
		$this->fontawesome( $args );
	}

	public function fontawesome( $args = array() ) {
		$folder_name = 'fontawesome';

		$dir_lib = $this->custom_lib_dir;
		$url_lib = $this->custom_lib_url;

		if ( ! is_dir( $dir_lib . $folder_name . '/' ) ) {
			$folder_name = 'font-awesome';
		}

		if ( ! is_dir( $dir_lib . $folder_name . '/' ) ) {
			return;
		}

		$dir_lib .= $folder_name;
		$dir_lib = trailingslashit( $dir_lib );

		$url_lib .= $folder_name;
		$url_lib = trailingslashit( $url_lib );

		if ( null === $args || false === $args ) {
			$exts = array(
				'font-awesome.min.css', // Old Fontawesome version
				'all.min.css' // New Fontawesome version
			);

			foreach ( $exts as $ext ) {
				$base_dir = $dir_lib . 'css/' . $ext;

				if ( file_exists( $base_dir ) ) {
					wp_enqueue_style( $folder_name, $url_lib . 'css/' . $ext );
					break;
				}
			}

			return;
		}

		$defaults = array(
			'version' => '6.5.2',
			'kit'     => null,
			'cdn'     => null
		);

		$args = wp_parse_args( $args, $defaults );

		$kit = $args['kit'];

		if ( ! empty( $kit ) ) {
			_deprecated_argument( __FUNCTION__, '6.7.7', sprintf( __( 'Stop using %s param from %s for loading resource from CDN.', 'hocwp-theme' ), 'kit', '$args' ) );
		}

		$base_url = $url_lib;
		$base_dir = $dir_lib . $args['version'];

		if ( $args['cdn'] ) {
			_deprecated_argument( __FUNCTION__, '6.7.7', sprintf( __( 'Stop using %s param from %s for loading resource from CDN.', 'hocwp-theme' ), 'cdn', '$args' ) );
		}

		if ( version_compare( $args['version'], '4.7.0', '>' ) ) {
			$css_file = 'css/all.min.css';
		} else {
			$css_file = 'css/font-awesome.min.css';
		}

		$this->auto_check_lib_version( $args, $base_dir, $css_file );

		$base_dir = trailingslashit( $base_dir );

		if ( ! empty( $args['version'] ) ) {
			$base_url .= $args['version'];
		}

		$base_url = trailingslashit( $base_url );

		$css_url = $base_url . $css_file;

		unset( $base_url, $base_dir, $css_file );

		$handle = $folder_name . $args['version'];
		$handle = sanitize_title( $handle );

		wp_enqueue_style( $handle . '-style', $css_url );

		unset( $defaults, $handle, $css_url, $kit );
	}

	public function jquery_ui_style( $deprecated = null ) {
		if ( ! empty( $deprecated ) ) {
			_deprecated_argument( __FUNCTION__, '6.7.7' );
		}

		wp_enqueue_style( 'jquery-ui-style', hocwp_theme()->core_url . '/css/jquery-ui' . HOCWP_THEME_CSS_SUFFIX );
	}
}

function ht_enqueue() {
	return HOCWP_Theme_Enqueue::get_instance();
}