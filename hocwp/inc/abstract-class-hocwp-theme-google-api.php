<?php
defined( 'ABSPATH' ) || exit;

abstract class Abstract_HOCWP_Theme_Google_API {
	public $base;
	public $api_key;
	public $params;
	public $cache = true;
	public $result;
	public $cache_prefix = 'google_api_';
	public $cache_time = MONTH_IN_SECONDS;
	public $query_url = '';

	public function __construct() {
		$this->set_api_key();
	}

	public function set_api_key( $key = '' ) {
		if ( empty( $key ) ) {
			$key = HT_Options()->get_tab( 'google_api_key', '', 'social' );
		}

		$this->api_key = $key;
	}

	public function has_api_key( &$params = array() ) {
		if ( ! empty( $this->api_key ) ) {
			$params['key'] = $this->api_key;

			return true;
		}

		if ( HT()->array_has_value( $params ) ) {
			$this->api_key = $params['key'] ?? '';

			return empty( $this->api_key );
		}

		return false;
	}

	public function get_response( $url ) {
		if ( is_object( $url ) || is_array( $url ) ) {
			return $url;
		}

		if ( ! $this->has_api_key( $this->params ) ) {
			return new WP_Error( 'invalid_api_key', __( 'The Google API Key has not been provided or is incorrect.', 'hocwp-theme' ) );
		}

		$url = untrailingslashit( $url );
		$url = add_query_arg( $this->params, $url );

		$tr_name = $this->cache_prefix . md5( $url );

		$res = '';

		if ( $this->cache ) {
			$res = get_transient( $tr_name );
		}

		if ( empty( $res ) ) {
			$res = wp_remote_get( $url );

			$res = wp_remote_retrieve_body( $res );

			if ( ! empty( $res ) ) {
				$res = json_decode( $res );

				if ( $this->cache ) {
					set_transient( $tr_name, $res, $this->cache_time );
				}
			}
		}

		if ( ! empty( $res ) ) {
			$this->result = $res;
		}

		return $this->result;
	}

	/**
	 * @return mixed|object
	 */
	public function get_result() {
		if ( empty( $this->result ) ) {
			$this->fetch();
		}

		return $this->result;
	}

	public function fetch() {
		return $this->get_response( $this->build_query_url() );
	}

	public abstract function build_query_url();

	public abstract function is_valid();
}