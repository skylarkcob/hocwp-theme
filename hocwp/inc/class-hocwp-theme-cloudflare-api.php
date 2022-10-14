<?php
defined( 'ABSPATH' ) || exit;

class HOCWP_Theme_Cloudflare_API {
	protected $base;

	protected $headers;

	public $resource;
	public $api_token;
	public $api_key;
	public $user_email;
	public $zone_id;
	public $account_id;
	public $domain;

	public function __construct( $resource, $api_token ) {
		$this->base = join( '.', array(
			'https://api',
			'cloudflare',
			'com/client/v4/'
		) );

		$this->resource = $resource;

		if ( is_array( $api_token ) ) {
			$this->set_api_token( $api_token['api_token'] ?? '' );
			$this->api_key    = $api_token['api_key'] ?? '';
			$this->user_email = $api_token['user_email'] ?? '';
			$this->zone_id    = $api_token['zone_id'] ?? '';
			$this->account_id = $api_token['account_id'] ?? '';
			$this->set_domain( $api_token['domain'] ?? '' );
		} else {
			$this->set_api_token( $api_token );
		}

		if ( empty( $this->get_domain() ) ) {
			$this->set_domain( HT()->get_domain_name( home_url() ) );
		} else {
			$this->set_domain( HT()->get_domain_name( $this->get_domain() ) );
		}
	}

	public function get_domain() {
		return $this->domain;
	}

	public function set_domain( $domain ) {
		$this->domain = $domain;
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
		$defaults = array(
			'name'      => $this->get_domain(),
			'status'    => 'active',
			'page'      => 1,
			'per_page'  => 20,
			'order'     => 'status',
			'direction' => 'desc',
			'match'     => 'all'
		);

		$args = wp_parse_args( $args, $defaults );

		// Check and cache zones for 1 month
		$tr_name = 'cloudflare_zones_' . md5( maybe_serialize( $args ) );

		if ( false === ( $zones = get_transient( $tr_name ) ) ) {
			$headers = $this->generate_headers();

			if ( is_wp_error( $headers ) ) {
				return $headers;
			}

			$url = $this->base . $this->resource;

			$url = add_query_arg( $args, $url );

			$args = array(
				'method'  => 'GET',
				'headers' => $headers
			);

			$zones = $this->retrieve_response_body( wp_remote_get( $url, $args ) );
			set_transient( $tr_name, $zones, MONTH_IN_SECONDS );
		}

		return $zones;
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

	public function is_response_valid( $response, $suffix = '' ) {
		if ( ! empty( $response ) ) {
			if ( is_object( $response ) ) {
				if ( $response->errors && HT()->array_has_value( $response->errors ) ) {
					$error = new WP_Error();

					foreach ( $response->errors as $err ) {
						if ( is_object( $err ) && isset( $err->code ) ) {
							$error->add( $err->code, $err->message );
						}
					}

					return $error;
				}

				if ( isset( $response->success ) && $response->success ) {
					return true;
				}
			}
		}

		return false;
	}

	public function find_zone_id() {
		$zones = $this->find_zones();

		if ( ! is_wp_error( $zones ) ) {
			if ( $zones->success && $zones->result ) {
				if ( is_array( $zones->result ) ) {
					foreach ( $zones->result as $zone ) {
						if ( $zone->id ) {
							$this->zone_id = $zone->id;

							return $zone->id;
						}
					}
				}
			}
		}

		return '';
	}

	public function get_setting( $url_suffix = '', $args = array() ) {
		return $this->query( 'GET', '', '', $url_suffix, $args );
	}

	public function get_settings( &$settings = array() ) {
		foreach ( $settings as $key => $item ) {
			$value = $this->get_setting( $item['suffix'] );

			if ( is_object( $value ) && ! is_wp_error( $value ) ) {
				if ( $value->result ) {
					if ( $value->result->value ) {
						$value = $value->result->value;
					} else {
						$value = $value->result;
					}
				}
			}

			$item['value']    = $value;
			$settings[ $key ] = $item;
		}
	}

	public function update_setting( $body, $url_suffix = '', $args = array() ) {
		return $this->query( 'PATCH', $body, '', $url_suffix, $args );
	}

	public function update_settings( $settings = array() ) {
		foreach ( $settings as $item ) {
			if ( $item['current_value'] != $item['value'] ) {
				$result = $this->update_setting( array( 'value' => $item['value'] ), $item['suffix'] );

				if ( is_wp_error( $valid = $this->is_response_valid( $result ) ) ) {
					return $valid;
				}
			}
		}

		return true;
	}

	public function query( $method, $body, $callback, $url_suffix = '', $args = array() ) {
		$headers = $this->generate_headers();

		if ( is_wp_error( $headers ) ) {
			return $headers;
		}

		if ( ! is_array( $body ) ) {
			$body = array();
		}

		$defaults = array(
			'zone_id' => $this->zone_id,
			'body'    => $body
		);

		$args = wp_parse_args( $args, $defaults );

		$zone_id = $args['zone_id'];

		if ( empty( $zone_id ) ) {
			$zone_id = $this->find_zone_id();

			if ( ! empty( $zone_id ) ) {
				$args['zone_id'] = $zone_id;

				if ( is_callable( $callback ) ) {
					return call_user_func( $callback, $args );
				}
			}

			if ( empty( $zone_id ) ) {
				return new WP_Error( 'invalid_zone', __( 'Cannot find Cloudflare Zone ID!', 'hocwp-theme' ) );
			}
		}

		$url = $this->base . $this->resource;
		$url = trailingslashit( $url ) . $args['zone_id'];

		if ( ! empty( $url_suffix ) ) {
			$url = trailingslashit( $url ) . $url_suffix;
		}

		$remote_args = array(
			'method'  => $method,
			'headers' => $headers
		);

		if ( ! empty( $body ) ) {
			$remote_args['body'] = json_encode( $args['body'] );

			$remote_args['data_format'] = 'body';
		}

		return $this->retrieve_response_body( wp_remote_post( $url, $remote_args ) );
	}

	public function enable_development_mode( $args = array() ) {
		return $this->query( 'PATCH', array( 'value' => 'on' ), array(
			$this,
			'enable_development_mode'
		), 'settings/development_mode', $args );
	}

	public function purge_cache( $args = array() ) {
		return $this->query( 'DELETE', array( 'purge_everything' => true ), array(
			$this,
			'purge_cache'
		), 'purge_cache', $args );
	}
}