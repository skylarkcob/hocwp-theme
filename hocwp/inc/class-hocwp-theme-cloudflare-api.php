<?php
defined( 'ABSPATH' ) || exit;

class HOCWP_Theme_Cloudflare_API {
	protected $base = 'https://api.cloudflare.com/client/v4/';

	protected $headers;

	public $resource;
	public $api_token;
	public $api_key;
	public $user_email;
	public $zone_id;
	public $account_id;
	public $domain;

	public function __construct( $resource, $api_token ) {
		$this->resource = $resource;

		if ( is_array( $api_token ) ) {
			$this->set_api_token( $api_token['api_token'] ?? '' );
			$this->api_key    = $api_token['api_key'] ?? '';
			$this->user_email = $api_token['user_email'] ?? '';
			$this->zone_id    = $api_token['zone_id'] ?? '';
			$this->account_id = $api_token['account_id'] ?? '';
			$this->domain     = $api_token['domain'] ?? '';
		} else {
			$this->set_api_token( $api_token );
		}

		if ( empty( $this->domain ) ) {
			$this->domain = HT()->get_domain_name( home_url() );
		} else {
			$this->domain = HT()->get_domain_name( $this->domain );
		}
	}

	public function set_api_token( $api_token ) {
		if ( ! empty( $api_token ) ) {
			if ( ! str_contains( $api_token, 'Bearer' ) ) {
				$api_token = 'Bearer ' . $api_token;
			}

			$this->api_token = $api_token;
		}
	}

	public function generate_headers() {
		if ( ! empty( $this->headers ) ) {
			return $this->headers;
		}

		if ( empty( $this->api_token ) && empty( $this->api_key ) ) {
			return new WP_Error( 'missing_api_key', __( 'Missing API Token or API Key!', 'hocwp-theme' ) );
		}

		$headers = array(
			'Content-Type'  => 'application/json',
			'Cache-Control' => 'no-cache'
		);

		if ( ! empty( $this->api_token ) ) {
			$headers['Authorization'] = $this->api_token;
		} else {
			if ( ! empty( $this->api_key ) && is_email( $this->user_email ) ) {
				$headers['X-Auth-Key']   = $this->api_key;
				$headers['X-Auth-Email'] = $this->user_email;
			}
		}

		$this->headers = $headers;

		return $headers;
	}

	public function find_zones( $args = array() ) {
		$headers = $this->generate_headers();

		if ( is_wp_error( $headers ) ) {
			return $headers;
		}

		$url = $this->base . $this->resource;

		$defaults = array(
			'name'      => $this->domain,
			'status'    => 'active',
			'page'      => 1,
			'per_page'  => 20,
			'order'     => 'status',
			'direction' => 'desc',
			'match'     => 'all'
		);

		$args = wp_parse_args( $args, $defaults );

		$url = add_query_arg( $args, $url );

		$args = array(
			'method'  => 'GET',
			'headers' => $headers
		);

		return $this->retrieve_response_body( wp_remote_get( $url, $args ) );
	}

	public function delete_cache( $args = array() ) {
		return $this->purge_cache( $args );
	}

	public function retrieve_response_body( $response ) {
		$body = wp_remote_retrieve_body( $response );

		if ( ! empty( $body ) ) {
			$body = json_decode( $body );
		}

		return $body;
	}

	public function purge_cache( $args = array() ) {
		$headers = $this->generate_headers();

		if ( is_wp_error( $headers ) ) {
			return $headers;
		}

		$default = array(
			'zone_id' => $this->zone_id,
			'body'    => array( 'purge_everything' => true )
		);

		$args = wp_parse_args( $args, $default );

		$zone_id = $args['zone_id'];

		if ( empty( $zone_id ) ) {
			$zones = $this->find_zones();

			if ( ! is_wp_error( $zones ) ) {
				if ( $zones->success && $zones->result ) {
					if ( is_array( $zones->result ) ) {
						$result = '';

						foreach ( $zones->result as $zone ) {
							if ( $zone->id ) {
								$args['zone_id'] = $zone->id;

								$result = $this->purge_cache( $args );

								if ( is_wp_error( $result ) || ! $result->success ) {
									return $result;
								}
							}
						}

						return $result;
					}
				}
			}
		}

		$url = $this->base . $this->resource;
		$url = trailingslashit( $url ) . $args['zone_id'];
		$url = trailingslashit( $url ) . 'purge_cache';

		$args = array(
			'method'      => 'DELETE',
			'headers'     => $headers,
			'body'        => json_encode( $args['body'] ),
			'data_format' => 'body'
		);

		return $this->retrieve_response_body( wp_remote_post( $url, $args ) );
	}
}