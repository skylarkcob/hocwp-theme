<?php

final class HOCWP_Theme_Admin extends HOCWP_Theme_Utility {
	public static $instance;

	protected function __construct() {
	}

	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function is_admin_page( $pages, $admin_page = '' ) {
		global $pagenow;

		if ( ! empty( $admin_page ) ) {
			global $plugin_page;

			if ( ! empty( $plugin_page ) && $admin_page != $plugin_page ) {
				return false;
			}
		}

		if ( is_string( $pages ) && $pagenow == $pages ) {
			return true;
		}

		return ( is_array( $pages ) && in_array( $pagenow, $pages ) ) ? true : false;
	}
}

function HT_Admin() {
	return HOCWP_Theme_Admin::get_instance();
}