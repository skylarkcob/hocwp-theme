<?php

final class HOCWP_Theme_Requirement {
	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {
	}

	public static function get_required_plugins() {
		$plugins = array();

		if ( defined( 'HOCWP_THEME_REQUIRED_PLUGINS' ) ) {

			if ( is_string( HOCWP_THEME_REQUIRED_PLUGINS ) ) {
				$plugins = explode( ',', HOCWP_THEME_REQUIRED_PLUGINS );
				$plugins = array_map( 'trim', $plugins );
			} else {
				$plugins = HOCWP_THEME_REQUIRED_PLUGINS;
			}

			$plugins = (array) $plugins;
		}

		$plugins = apply_filters( 'hocwp_theme_required_plugins', $plugins );
		$plugins = array_filter( $plugins );
		$plugins = array_unique( $plugins );

		return $plugins;
	}

	public static function check_required_plugins() {
		$plugins = self::get_required_plugins();

		if ( HOCWP_Theme::array_has_value( $plugins ) ) {
			$active_plugins = get_option( 'active_plugins' );
			$active_plugins = (array) $active_plugins;

			$required = false;
			$root     = WP_PLUGIN_DIR;

			foreach ( $plugins as $plugin ) {
				$path = trailingslashit( $root ) . $plugin;
				if ( ! HT()->is_dir( $path ) ) {
					$required = true;
					break;
				} else {
					$tmp = get_plugins();
					foreach ( $tmp as $key => $value ) {
						$slug = dirname( $key );
						if ( $slug == $plugin ) {
							$plugin = $key;
							break;
						}
					}
					if ( ! in_array( $plugin, $active_plugins ) ) {
						$required = true;
						break;
					}
				}
			}

			if ( $required ) {
				return false;
			}
		}

		return true;
	}

	public static function check_extension_woocommerce() {
		$is_wc = $GLOBALS['hocwp_theme']->is_wc_activated;

		if ( $is_wc && ( function_exists( 'hocwp_theme_load_extension_woocommerce' ) && ! hocwp_theme_load_extension_woocommerce() || ! function_exists( 'hocwp_theme_load_extension_woocommerce' ) ) ) {
			return false;
		}

		return true;
	}

	public static function check() {
		$rp = self::check_required_plugins();

		if ( ! $rp ) {
			return false;
		}

		$ewc = self::check_extension_woocommerce();

		if ( ! $ewc ) {
			return false;
		}

		return true;
	}
}

function HT_Requirement() {
	return HOCWP_Theme_Requirement::instance();
}