<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( file_exists( dirname( __FILE__ ) . '/constants.php' ) ) {
	require_once( dirname( __FILE__ ) . '/constants.php' );
}

if ( ! trait_exists( 'HTC_Functions' ) ) {
	require_once( dirname( __FILE__ ) . '/trait-functions.php' );
}

class HOCWP_Theme_Custom extends Abstract_HT_Custom {
	use HTC_Functions;

	/*
	 * Default function to register post type and taxonomy. Do not remove it.
	 */
	public function register_post_type_and_taxonomy() {
	}

	/*
	 * Default enqueue script action for front-end before core styles and scripts loaded. Do not remove it.
	 */
	public function enqueue_scripts_early() {
	}

	/*
	 * Default enqueue script action for front-end. Do not remove it.
	 */
	public function enqueue_scripts() {
	}

	/*
	 * Default theme general setting fields filter. Do not remove it.
	 */
	public function general_setting_fields( $fields, $options ) {
		return $fields;
	}

	/*
	 * Default theme home setting fields filter. Do not remove it.
	 */
	public function home_setting_fields( $fields, $options ) {
		return $fields;
	}

	/*
	 * Default theme general setting sections filter. Do not remove it.
	 */
	public function general_setting_sections( $sections ) {
		return $sections;
	}

	/*
	 * Default theme home setting sections filter. Do not remove it.
	 */
	public function home_setting_sections( $sections ) {
		return $sections;
	}

	/*
	 * Default AJAX action callback to execute AJAX on website. Do not remove it.
	 */
	public function ajax_callback() {
		$data   = array();
		$method = HT()->get_method_value( 'method', 'request', 'post' );
		$action = HT()->get_method_value( 'do_action', $method );

		switch ( $action ) {
			default:
		}

		wp_send_json_error( $data );
	}

	/*
	 * Default private AJAX action callback to execute AJAX on website. Do not remove it.
	 */
	public function ajax_private_callback() {
		$data   = array();
		$method = HT()->get_method_value( 'method', 'request', 'post' );
		$action = HT()->get_method_value( 'do_action', $method );

		switch ( $action ) {
			default:
		}

		wp_send_json_error( $data );
	}

	/*
	 * Default action to create post meta fields. Do not remove it.
	 */
	public function post_meta() {
	}

	/*
	 * Default action to create term meta fields. Do not remove it.
	 */
	public function term_meta() {
	}

	/*
	 * Default action to load custom hooks on website. Do not remove it.
	 */
	public function load_custom_hook() {
	}

	/*
	 * Default widgets init action for register sidebar or widget. Do not remove it.
	 */
	public function widgets_init() {
	}

	/*
	 * Default widgets init action for register nav menu. Do not remove it.
	 */
	public function menus_init() {

	}

	/*
	 * Default post and term meta configuration. Do not remove it.
	 */
	public function meta_config() {
		// =============== POST META CONFIGURATION =============== //


		// =============== TERM META CONFIGURATION =============== //

	}

	/* =============== That's all, stop editing! Happy coding. =============== */

	protected static $instance;

	/*
	 * Default function to get single instance for this class. Do not remove or change it.
	 */
	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/*
	 * Default construct function. Do not remove or change it.
	 */
	public function __construct() {
		if ( self::$instance instanceof self ) {
			return;
		}

		add_action( 'after_setup_theme', array( $this, 'custom_after_setup_theme_action' ), 1 );
	}
}

function HT_Custom() {
	return HOCWP_Theme_Custom::get_instance();
}

HT_Custom();