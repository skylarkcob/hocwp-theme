<?php
defined('ABSPATH') || exit;

trait HOCWP_Theme_Deprecated {
	/*
	 * List deprecated functions.
	 */
	public function enqueue_media() {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.5.4', 'HT_Enqueue()->media_upload()' );
		ht_enqueue()->media_upload();
	}

	public function enqueue_sortable() {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.5.4', 'HT_Enqueue()->sortable()' );
		ht_enqueue()->sortable();
	}

	public function enqueue_jquery_ui_style() {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.5.4', 'HT_Enqueue()->jquery_ui_style()' );
		ht_enqueue()->jquery_ui_style();
	}

	public function enqueue_datepicker() {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.5.4', 'HT_Enqueue()->datepicker()' );
		ht_enqueue()->datepicker();
	}

	public function enqueue_datetime_picker() {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.5.4', 'HT_Enqueue()->datetime_picker()' );
		ht_enqueue()->datetime_picker();
	}

	public function enqueue_color_picker() {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.5.4', 'HT_Enqueue()->color_picker()' );
		ht_enqueue()->color_picker();
	}

	public function enqueue_chosen() {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.5.4', 'HT_Enqueue()->chosen()' );
		ht_enqueue()->chosen();
	}

	public function enqueue_ajax_overlay() {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.5.4', 'HT_Enqueue()->ajax_overlay()' );
		ht_enqueue()->ajax_overlay();
	}

	public function enqueue_code_editor() {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.5.4', 'HT_Enqueue()->code_editor()' );
		ht_enqueue()->code_editor();
	}

	public function pagination( $args = array() ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.3.9', 'HT_Frontend()->' . __FUNCTION__ . '()' );
		ht_frontend()->pagination( $args );
	}

	public function get_archive_title( $prefix = true ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.3.9', 'HT_Frontend()->' . __FUNCTION__ . '()' );

		return ht_frontend()->get_archive_title( $prefix );
	}

	public function breadcrumb( $args = array() ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.3.9', 'HT_Frontend()->' . __FUNCTION__ . '()' );
		ht_frontend()->breadcrumb( $args );
	}

	public function facebook_share_button( $args = array() ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.3.9', 'HT_Frontend()->' . __FUNCTION__ . '()' );
		ht_frontend()->facebook_share_button( $args );
	}

	public function addthis_toolbox( $args = array() ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.3.9', 'HT_Frontend()->' . __FUNCTION__ . '()' );
		ht_frontend()->addthis_toolbox( $args );
	}

	public function back_to_top_button() {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.3.9', 'HT_Frontend()->' . __FUNCTION__ . '()' );
		ht_frontend()->back_to_top_button();
	}

	public function is_admin_page( $pages, $admin_page = '' ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.3.9', 'HT_Admin()->' . __FUNCTION__ . '()' );

		return ht_admin()->is_admin_page( $pages, $admin_page );
	}

	public function is_post_new_update_page() {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.3.9', 'HT_Admin()->' . __FUNCTION__ . '()' );

		return ht_admin()->is_post_new_update_page();
	}

	public function is_edit_post_new_update_page() {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.3.9', 'HT_Admin()->' . __FUNCTION__ . '()' );

		return ht_admin()->is_edit_post_new_update_page();
	}

	public function get_current_post_type() {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.3.9', 'HT_Admin()->' . __FUNCTION__ . '()' );

		return ht_admin()->get_current_post_type();
	}

	public function get_current_new_post() {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.3.9', 'HT_Admin()->' . __FUNCTION__ . '()' );

		return ht_admin()->get_current_new_post();
	}

	public function shortcut_icon_tag() {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.7.7' );
	}

	public function is_captcha_valid( $url, $params = array() ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.9.4', 'HT_CAPTCHA()->is_captcha_valid()' );

		return ht_captcha()->is_captcha_valid( $url, $params );
	}

	public function captcha() {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.9.4', 'HT_CAPTCHA()->display_html()' );
		ht_captcha()->display_html();
	}

	public function captcha_valid() {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.9.4', 'HT_CAPTCHA()->check_valid()' );

		return ht_captcha()->check_valid();
	}

	public function hcaptcha( $atts = array(), $script_params = array() ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.9.4', 'HT_CAPTCHA()->display_html()' );
		ht_captcha()->display_html();
	}

	public function hcaptcha_valid( $params = array() ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.9.4', 'HT_CAPTCHA()->check_valid()' );

		return ht_captcha()->check_valid();
	}

	public function recaptcha( $version = 'v2' ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.9.4', 'HT_CAPTCHA()->display_html()' );
		ht_captcha()->display_html();
	}

	public function recaptcha_valid( $response = null ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.9.4', 'HT_CAPTCHA()->check_valid()' );

		return ht_captcha()->check_valid();
	}
}