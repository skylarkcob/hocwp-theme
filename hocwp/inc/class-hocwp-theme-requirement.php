<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

final class HOCWP_Theme_Requirement {
	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {
		if ( self::$_instance ) {
			HT_Util()->doing_it_wrong( __CLASS__, sprintf( __( '%s is a singleton class and you cannot create a second instance.', 'hocwp-theme' ), get_class( $this ) ), '6.4.2' );

			return;
		}

		self::$_instance = $this;
	}

	private function get_defined_value_array( $conts ) {
		$results = array();

		if ( ! empty( $conts ) ) {
			if ( is_string( $conts ) ) {
				$results = explode( ',', $conts );
			} else {
				$results = $conts;
			}

			$results = (array) $results;
		}

		$results = array_map( 'trim', $results );

		return $results;
	}

	public function get_required_extensions() {
		$exts = defined( 'HOCWP_THEME_REQUIRED_EXTENSIONS' ) ? HOCWP_THEME_REQUIRED_EXTENSIONS : '';

		$extensions = $this->get_defined_value_array( $exts );

		unset( $exts );

		$extensions = apply_filters( 'hocwp_theme_required_extensions', $extensions );

		if ( HT()->array_has_value( $extensions ) ) {
			$extensions = array_filter( $extensions );
			$extensions = array_unique( $extensions );
			$extensions = array_map( array( $this, 'sanitize_extension_basename' ), $extensions );
		}

		return $extensions;
	}

	public function get_recommended_extensions() {
		$exts = defined( 'HOCWP_THEME_RECOMMENDED_EXTENSIONS' ) ? HOCWP_THEME_RECOMMENDED_EXTENSIONS : '';

		$extensions = $this->get_defined_value_array( $exts );

		unset( $exts );

		$extensions = apply_filters( 'hocwp_theme_recommended_extensions', $extensions );

		if ( HT()->array_has_value( $extensions ) ) {
			$extensions = array_filter( $extensions );
			$extensions = array_unique( $extensions );
			$extensions = array_map( array( $this, 'sanitize_extension_basename' ), $extensions );
		}

		return $extensions;
	}

	public function sanitize_extension_basename( $basename ) {
		$basename = HT_Extension()->sanitize_basename( $basename );

		return $basename;
	}

	public function check_required_extensions() {
		$extensions = $this->get_required_extensions();

		if ( HT()->array_has_value( $extensions ) ) {
			foreach ( $extensions as $basename ) {
				if ( ! HT_extension()->is_active( $basename ) ) {
					return false;
				}
			}
		}

		return true;
	}

	public static function get_recommended_plugins() {
		$rps = defined( 'HOCWP_THEME_RECOMMENDED_PLUGINS' ) ? HOCWP_THEME_RECOMMENDED_PLUGINS : '';

		$plugins = HT_Requirement()->get_defined_value_array( $rps );

		unset( $rps );

		$plugins = apply_filters( 'hocwp_theme_recommended_plugins', $plugins );
		$plugins = array_filter( $plugins );
		$plugins = array_unique( $plugins );

		return $plugins;
	}

	public static function get_required_plugins() {
		$rps = defined( 'HOCWP_THEME_REQUIRED_PLUGINS' ) ? HOCWP_THEME_REQUIRED_PLUGINS : '';

		$plugins = HT_Requirement()->get_defined_value_array( $rps );

		unset( $rps );

		$plugins = apply_filters( 'hocwp_theme_required_plugins', $plugins );
		$plugins = array_filter( $plugins );
		$plugins = array_unique( $plugins );

		return $plugins;
	}

	public static function check_required_plugins() {
		$plugins = self::get_required_plugins();

		if ( HT()->array_has_value( $plugins ) ) {
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

		$re = HT_Requirement()->check_required_extensions();

		if ( ! $re ) {
			return false;
		}

		$ewc = self::check_extension_woocommerce();

		if ( ! $ewc ) {
			return false;
		}

		return true;
	}

	public function load_ajax_loop_functions() {
		if ( ! function_exists( 'hocwp_theme_load_custom_loop' ) ) {
			require_once( HOCWP_Theme()->core_path . '/inc/functions-context.php' );
			require_once( HOCWP_Theme()->core_path . '/inc/functions-media.php' );
			require_once( HOCWP_Theme()->core_path . '/inc/template.php' );
			require_once( HOCWP_Theme()->core_path . '/inc/template-general.php' );
			require_once( HOCWP_Theme()->core_path . '/inc/template-post.php' );
		}
	}
}

function HT_Requirement() {
	return HOCWP_Theme_Requirement::instance();
}