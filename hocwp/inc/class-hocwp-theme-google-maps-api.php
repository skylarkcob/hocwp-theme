<?php
defined( 'ABSPATH' ) || exit;

class HOCWP_Theme_Google_Maps_API {
	public $base = 'https://maps.googleapis.com/maps/api/';
	public $key;

	public $name;

	public $api;

	public $output = 'json';

	public $params;

	public $result;

	public $cache = true;

	public function __construct( $name, $api, $params, $output = '' ) {
		$this->name   = $name;
		$this->api    = $api;
		$this->params = $params;

		if ( 'xml' == $output ) {
			$this->output = $output;
		}
	}

	/**
	 * @return mixed|object
	 */
	public function get_result() {
		return $this->result;
	}

	/**
	 * @param string $output
	 */
	public function set_output( mixed $output ) {
		$this->output = $output;
	}

	public function fetch() {
		if ( empty( $this->name ) ) {
			return new WP_Error( 'invalid_name', __( 'API name cannot be empty, can be place, geocode, distancematrix,...', 'hocwp-theme' ) );
		}

		if ( ! HT()->array_has_value( $this->params ) ) {
			return new WP_Error( 'invalid_params', __( 'Query parameters must be provided in full.', 'hocwp-theme' ) );
		}

		$url = $this->base . $this->name . '/';

		if ( ! empty( $this->api ) ) {
			$url .= $this->api . '/';
		}

		$url .= $this->output . '/';

		$key = $this->params['key'] ?? '';

		if ( empty( $key ) ) {
			$key = HT_Options()->get_tab( 'google_api_key', '', 'social' );

			$this->params['key'] = $key;
		}

		if ( empty( $key ) ) {
			return new WP_Error( 'invalid_api_key', __( 'The Google API Key has not been provided or is incorrect.', 'hocwp-theme' ) );
		}

		$url = untrailingslashit( $url );
		$url = add_query_arg( $this->params, $url );

		$tr_name = 'google_maps_' . md5( $url );

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
					set_transient( $tr_name, $res, MONTH_IN_SECONDS );
				}
			}
		}

		if ( ! empty( $res ) ) {
			$this->result = $res;
		}

		return $res;
	}

	public function is_valid() {
		if ( ! is_object( $this->result ) ) {
			$this->fetch();
		}

		return ( is_object( $this->result ) && isset( $this->result->status ) && 'OK' == $this->result->status );
	}
}