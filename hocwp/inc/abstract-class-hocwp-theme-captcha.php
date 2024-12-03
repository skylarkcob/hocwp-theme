<?php
defined( 'ABSPATH' ) || exit;

if ( ! trait_exists( 'HOCWP_Theme_CAPTCHA_Utils' ) ) {
	require_once( __DIR__ . '/trait-hocwp-theme-captcha-utils.php' );
}

abstract class Abstract_HOCWP_Theme_CAPTCHA {
	use HOCWP_Theme_CAPTCHA_Utils;

	public $service = '';
	public $attributes = array();
	public $script_prams = array();

	public $post_key = '';
	public $insert_before = '';

	public function __construct() {
	}

	public function set_attributes( $atts ) {
		if ( is_array( $atts ) ) {
			$this->attributes = $atts;
		}
	}

	public function set_script_params( $params ) {
		if ( is_array( $params ) ) {
			$this->script_prams = $params;
		}
	}

	abstract public function display_html();

	abstract public function check_valid( $params = array() );
}