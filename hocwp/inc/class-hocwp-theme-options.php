<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Options {
	protected static $instance;

	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {

	}

	/**
	 * Update theme options or any key value in options.
	 *
	 * @param mixed $key The option key to be updated.
	 * @param mixed $value The option value to be updated.
	 * @param null|string $tab The setting tab name.
	 * @param null|array $options The options array.
	 *
	 * @return bool|null Updated result.
	 */
	public function update( $key, $value, $tab = null, $options = null ) {
		if ( HT()->is_array_key_valid( $key ) && null !== $value ) {
			if ( ! HT()->array_has_value( $options ) ) {
				$options = $this->get();
			}

			if ( null == $tab ) {
				$options[ $key ] = $value;
			} else {
				$options[ $tab ][ $key ] = $value;
			}
		}

		if ( null === $options ) {
			return null;
		}

		return update_option( HOCWP_Theme()->get_prefix(), $options );
	}

	/**
	 * Get theme options for page template.
	 *
	 * @param mixed $tab_name Default type name
	 * @param mixed $post_id The page ID or current page ID in loop
	 *
	 * @return array|mixed|string
	 */
	public function get_page_options( $tab_name, $post_id = '' ) {
		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}

		$obj = get_post( $post_id );

		if ( empty( $tab_name ) ) {
			return HT_Options()->get( $obj->post_name );
		}

		$options = HT_Options()->get( $tab_name );

		if ( $obj->post_name != $tab_name ) {
			$options = HT_Options()->get( $obj->post_name );
		}

		return $options;
	}

	/**
	 * Get theme options or any key value in options with default fallback.
	 *
	 * @param mixed $key Key name or index.
	 * @param mixed $default The default fallback value if key value not exists.
	 *
	 * @return array|mixed|string Full options array or a value of key.
	 */
	public function get( $key = null, $default = '' ) {
		$options = HOCWP_Theme()->get_options();

		if ( HT()->is_array_key_valid( $key ) ) {
			$options = HT()->get_value_in_array( $options, $key, $default );
		}

		return $options;
	}

	/**
	 * Get option value by key in tab.
	 *
	 * @param mixed $key The option key name.
	 * @param mixed $default The default option value fallback.
	 * @param mixed $tab The tab name.
	 *
	 * @return mixed|array Option value in a tab. If key is invalid, tab options will be returned.
	 */
	public function get_tab( $key = null, $default = '', $tab = 'general' ) {
		if ( ! HT()->is_array_key_valid( $key ) ) {
			return $this->get( $tab );
		}

		return HT_Util()->get_theme_option( $key, $default, $tab );
	}

	public function get_default( $key = null ) {
		global $hocwp_theme;

		$defaults = $hocwp_theme->defaults;

		if ( HT()->is_array_key_valid( $key ) ) {
			$defaults = $defaults[ $key ] ?? '';
		}

		return $defaults;
	}

	public function get_home( $key = null, $default = '' ) {
		return HT_Util()->get_theme_option( $key, $default, 'home' );
	}

	public function get_general( $key = null, $default = '' ) {
		return HT_Util()->get_theme_option( $key, $default );
	}

	public function check_page_valid( $page, $check_current_page = false, $page_template = true ) {
		return HT_Util()->check_page_valid( $page, $check_current_page, $page_template );
	}

	public function check_post_valid( $id_or_object, $post_type = null ) {
		return HT_Util()->check_post_valid( $id_or_object, $post_type );
	}

	public function get_google_api_key( $restriction = '' ) {
		$options = $this->get_tab( null, '', 'social' );

		$key  = $options['google_api_key'] ?? '';
		$http = $options['google_api_key_http'] ?? '';
		$ip   = $options['google_api_key_ip'] ?? '';

		if ( 'http' == $restriction || 'http_referrer' == $restriction ) {
			if ( empty( $http ) ) {
				$http = $key;
			}

			return $http;
		} elseif ( 'ip' == $restriction || 'ip_address' == $restriction ) {
			if ( empty( $ip ) ) {
				$ip = $key;
			}

			return $ip;
		}

		if ( empty( $key ) ) {
			$key = $http;
		}

		if ( empty( $key ) ) {
			$key = $ip;
		}

		return $key;
	}
}

function HT_Options() {
	return HOCWP_Theme_Options::get_instance();
}