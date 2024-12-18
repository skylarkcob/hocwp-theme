<?php
defined( 'ABSPATH' ) || exit;

class HOCWP_Theme_Google_Maps_API extends Abstract_HOCWP_Theme_Google_API {
	public $base = 'https://maps.googleapis.com/maps/api/';
	public $cache_prefix = 'google_maps_api_';

	public $name;

	public $api;

	public $output = 'json';

	public function __construct( $name, $api, $params, $output = '' ) {
		$this->name   = $name;
		$this->api    = $api;
		$this->params = $params;

		if ( 'xml' == $output ) {
			$this->output = $output;
		}

		parent::__construct();
	}

	/**
	 * @param string $output
	 */
	public function set_output( mixed $output ) {
		$this->output = $output;
	}

	public function build_query_url() {
		// TODO: Implement build_query_url() method.
		if ( ! empty( $this->query_url ) && ! is_wp_error( $this->query_url ) ) {
			return $this->query_url;
		}

		if ( empty( $this->name ) ) {
			return new WP_Error( 'invalid_name', __( 'API name cannot be empty, can be place, geocode, distancematrix,...', 'hocwp-theme' ) );
		}

		if ( ! ht()->array_has_value( $this->params ) ) {
			return new WP_Error( 'invalid_params', __( 'Query parameters must be provided in full.', 'hocwp-theme' ) );
		}

		$this->query_url = $this->base . $this->name . '/';

		if ( ! empty( $this->api ) ) {
			$this->query_url .= $this->api . '/';
		}

		$this->query_url .= $this->output . '/';

		return $this->query_url;
	}

	public function is_valid() {
		if ( ! is_object( $this->result ) ) {
			$this->fetch();
		}

		return ( is_object( $this->result ) && isset( $this->result->status ) && 'OK' == $this->result->status );
	}
}