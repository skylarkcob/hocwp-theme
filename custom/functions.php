<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Custom {
	protected static $instance;

	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get_url() {
		return HOCWP_THEME_CUSTOM_URL;
	}

	public function get_path() {
		return HOCWP_THEME_CUSTOM_PATH;
	}

	public function __construct() {
		if ( self::$instance instanceof self ) {
			return;
		}

		if ( is_admin() ) {
			add_filter( 'hocwp_theme_setting_fields', array( $this, 'general_setting_fields' ), 99, 2 );
			add_filter( 'hocwp_theme_setting_page_home_fields', array( $this, 'home_setting_fields' ), 99, 2 );
			add_filter( 'hocwp_theme_setting_sections', array( $this, 'general_setting_sections' ) );
			add_filter( 'hocwp_theme_setting_page_home_sections', array( $this, 'home_setting_sections' ) );

			add_action( 'load-post.php', array( $this, 'post_meta' ) );
			add_action( 'load-post-new.php', array( $this, 'post_meta' ) );

			add_action( 'load-edit-tags.php', array( $this, 'term_meta' ) );

			if ( HOCWP_THEME_DOING_AJAX ) {
				add_action( 'wp_ajax_hocwp_theme_ajax', array( $this, 'ajax_callback' ) );
				add_action( 'wp_ajax_nopriv_hocwp_theme_ajax', array( $this, 'ajax_callback' ) );
				add_action( 'wp_ajax_hocwp_theme_ajax_private', array( $this, 'ajax_private_callback' ) );
			}
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		add_action( 'widgets_init', array( $this, 'widgets_init' ) );
	}

	public function enqueue_scripts() {

	}

	public function general_setting_fields( $fields, $options ) {
		return $fields;
	}

	public function home_setting_fields( $fields, $options ) {

		return $fields;
	}

	public function general_setting_sections( $sections ) {

		return $sections;
	}

	public function home_setting_sections( $sections ) {

		return $sections;
	}

	public function ajax_callback() {
		$data   = array();
		$action = HT()->get_method_value( 'do_action' );

		switch ( $action ) {
			default:
		}

		wp_send_json_error( $data );
	}

	public function ajax_private_callback() {
		$data   = array();
		$action = HT()->get_method_value( 'do_action' );

		switch ( $action ) {
			default:
		}

		wp_send_json_error( $data );
	}

	public function post_meta() {

	}

	public function term_meta() {

	}

	public function widgets_init() {

	}
}

function HT_Custom() {
	return HOCWP_Theme_Custom::get_instance();
}

HT_Custom();