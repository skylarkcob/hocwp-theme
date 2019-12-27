<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Enqueue {
	protected static $instance;

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

	public function jquery_ui_style() {
		wp_enqueue_style( 'jquery-ui-style', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css' );
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
		wp_enqueue_code_editor( $args );
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

	public function bootstrap( $args = array() ) {
		$defaults = array(
			'cdn'     => false,
			'version' => '3.3.7',
			'js'      => false,
			'theme'   => false
		);

		$args = wp_parse_args( $args, $defaults );

		$base_url = HOCWP_Theme()->custom_url . '/lib/bootstrap/';
		$base_dir = HOCWP_Theme()->custom_path . '/lib/bootstrap/' . $args['version'];

		if ( $args['cdn'] ) {
			$parts    = array( 'maxcdn', 'bootstrapcdn', 'com' );
			$base_url = 'https://';
			$base_url .= join( '.', $parts );
			$base_url .= '/bootstrap/';

			unset( $parts );
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

		if ( $args['theme'] && HT()->is_file( $base_dir . 'css/bootstrap-theme.min.css' ) ) {
			wp_enqueue_style( $handle . '-theme-style', $base_url . 'css/bootstrap-theme.min.css' );
		}

		if ( $args['js'] && HT()->is_file( $base_dir . 'js/bootstrap.min.js' ) ) {
			wp_enqueue_script( $handle, $base_url . 'js/bootstrap.min.js', array( 'jquery' ), false, true );
		}

		unset( $defaults, $base_url, $base_dir, $handle, $css_file );
	}

	private function auto_check_lib_version( &$args, &$base_dir, $abs_file ) {
		if ( ! $args['cdn'] && ! HT()->is_dir( $base_dir ) ) {
			// Auto check version
			$tmp = dirname( $base_dir );
			$tmp = trailingslashit( $tmp );
			$tmp .= '*';
			$dirs = glob( $tmp, GLOB_ONLYDIR );

			if ( HT()->array_has_value( $dirs ) ) {
				$tmp = current( $dirs );
				$tmp = trailingslashit( $tmp );

				if ( file_exists( $tmp . $abs_file ) ) {
					$args['version'] = basename( $tmp );

					$base_dir = $tmp;
				}
			}

			if ( ! HT()->is_dir( $base_dir ) ) {
				$base_dir = dirname( $base_dir );
				$base_dir = trailingslashit( $base_dir );

				if ( ! HT()->is_dir( $base_dir ) || ! HT()->is_file( $base_dir . $abs_file ) ) {
					return;
				}

				$args['version'] = '';
			}

			unset( $tmp, $dirs );
		}
	}

	public function fontawesome( $args = array() ) {
		$defaults = array(
			'cdn'     => false,
			'version' => '5.11.2',
			'kit'     => false
		);

		$args = wp_parse_args( $args, $defaults );

		$kit = $args['kit'];

		if ( ! empty( $kit ) ) {
			if ( false === strpos( $kit, 'http' ) ) {
				$kit = 'https://kit.fontawesome.com/' . $kit . '.js';
			}

			$handle = 'fontawesome-' . $args['version'];
			$handle = sanitize_title( $handle );

			wp_enqueue_script( $handle, $kit, array(), false, true );

			unset( $handle, $kit );

			return;
		} else {
			$base_url = HOCWP_Theme()->custom_url . '/lib/fontawesome/';
			$base_dir = HOCWP_Theme()->custom_path . '/lib/fontawesome/' . $args['version'];

			if ( $args['cdn'] ) {
				$parts    = array( 'cdnjs', 'cloudflare', 'com' );
				$base_url = 'https://';
				$base_url .= join( '.', $parts );
				$base_url .= '/ajax/libs/font-awesome/';

				unset( $parts );
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
		}

		$handle = 'fontawesome-' . $args['version'];
		$handle = sanitize_title( $handle );

		wp_enqueue_style( $handle . '-style', $css_url );

		unset( $defaults, $handle, $css_url, $kit );
	}
}

function HT_Enqueue() {
	return HOCWP_Theme_Enqueue::get_instance();
}