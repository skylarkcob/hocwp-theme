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

	public function check_page_valid( $page, $check_current_page = false ) {
		if ( $page instanceof WP_Post ) {
			$page_template = get_post_meta( $page->ID, '_wp_page_template', true );

			if ( ! empty( $page->post_content ) || ( 'default' != $page_template && file_exists( get_template_directory() . '/' . $page_template ) ) ) {

				if ( $check_current_page ) {
					if ( is_page( $page->ID ) ) {
						return true;
					}

					return false;
				}

				return true;
			}
		}

		return false;
	}
}

function HT_Options() {
	return HOCWP_Theme_Options::get_instance();
}