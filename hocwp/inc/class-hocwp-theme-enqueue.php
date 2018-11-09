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

	public function code_editor() {
		wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
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
}

function HT_Enqueue() {
	return HOCWP_Theme_Enqueue::get_instance();
}