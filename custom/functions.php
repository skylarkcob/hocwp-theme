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
	}
}

function HT_Custom() {
	return HOCWP_Theme_Custom::get_instance();
}

HT_Custom();