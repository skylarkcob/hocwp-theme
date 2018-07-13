<?php

class HOCWP_Theme_Options {
	protected static $instance;

	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		if ( self::$instance instanceof self ) {
			return;
		}
	}

	public function get( $key = null, $defaults = '' ) {
		global $hocwp_theme;
		$options = $hocwp_theme->options;

		if ( null !== $key ) {
			$options = HT()->get_value_in_array( $options, $key, $defaults );
		}

		return $options;
	}

	public function get_home( $key = null, $default = '' ) {
		return HT_Util()->get_theme_option( $key, $default, 'home' );
	}

	public function get_general( $key = null, $default = '' ) {
		return HT_Util()->get_theme_option( $key, $default );
	}

	public function get_tab( $key = null, $default = '', $tab = 'general' ) {
		return HT_Util()->get_theme_option( $key, $default, $tab );
	}
}

function HT_Options() {
	return HOCWP_Theme_Options::get_instance();
}