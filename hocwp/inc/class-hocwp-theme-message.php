<?php
defined( 'ABSPATH' ) || exit;

class HOCWP_Theme_Message {
	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	protected function __construct() {
	}

	public function noscript() {
		return __( '<strong>ERROR:</strong> Javascript not activated!', 'hocwp-theme' );
	}

	public function theme_or_site_incorrect_config() {
		return __( 'The website or theme is set up incorrectly.', 'hocwp-theme' );
	}

	public function browser_not_support_audio() {
		return __( 'Your browser does not support the audio element.', 'hocwp-theme' );
	}

	public function invalid_vtour_theme() {
		return __( 'Invalid VR Tour theme! Please try to activate VR Tour extension and try again.', 'hocwp-theme' );
	}
}

function HT_Message() {
	return HOCWP_Theme_Message::instance();
}