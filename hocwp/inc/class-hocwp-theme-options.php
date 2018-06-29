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
}

function HT_Options() {
	return HOCWP_Theme_Options::get_instance();
}