<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'HOCWP_Theme_Updates' ) ) {
	class HOCWP_Theme_Updates {
		public $version = '1.0';

		public $plugins = array();
		public $themes = array();

		public $checked = 0;

		public $api_url = 'https://api.ldcuong.com/';

		public $github_username = 'skylarkcob';

		public function __construct() {
			// Append update information to transient.
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'modify_plugins_transient' ) );

			// Modify plugin data visible in the 'View details' popup.
			add_filter( 'plugins_api', array( $this, 'modify_plugin_details' ), 10, 3 );

			add_filter( 'pre_set_site_transient_update_themes', array( $this, 'modify_themes_transient' ) );
			add_filter( 'themes_api', array( $this, 'modify_theme_details' ), 10, 3 );
		}

		public function get_api_url() {
			if ( str_contains( __DIR__, 'xampp' ) ) {
				$this->api_url = 'http://localhost/api/';
			}

			return $this->api_url;
		}

		private function modify_transient( $transient, $callback, $type ) {
			// Bail early if no response (error).
			if ( ! isset( $transient->response ) ) {
				return $transient;
			}

			// Ensure no_update is set for back compat.
			if ( ! isset( $transient->no_update ) ) {
				$transient->no_update = array();
			}

			// Force-check (only once).
			$force_check = $this->checked == 0 && ! empty( $_GET['force-check'] ); // phpcs:ignore -- False positive, value not used.

			// Fetch updates (this filter is called multiple times during a single page load).
			$updates = call_user_func( $callback, $force_check );

			// Append my themes.
			if ( is_array( $updates ) ) {
				if ( ! empty( $updates[ $type ] ) ) {
					foreach ( $updates[ $type ] as $basename => $update ) {
						if ( 'plugins' == $type ) {
							$update = (object) $update;
						}

						$transient->response[ $basename ] = $update;
					}
				}

				if ( ! empty( $updates['no_update'] ) ) {
					foreach ( $updates['no_update'] as $basename => $update ) {
						if ( 'plugins' == $type ) {
							$update = (object) $update;
						}

						$transient->no_update[ $basename ] = $update;
					}
				}
			}

			++ $this->checked;

			return $transient;
		}

		public function modify_themes_transient( $transient ) {
			return $this->modify_transient( $transient, array( $this, 'get_theme_updates' ), 'themes' );
		}

		private function get_updates( $transient_name, $type, $endpoint, $force_check = false ) {
			// Don't call our site if no items have registered updates.
			if ( empty( $this->{$type} ) ) {
				return array();
			}

			// Construct array of 'checked' items.
			// Sort by key to avoid detecting change due to "include order".
			$checked = array();

			foreach ( $this->{$type} as $slug => $theme ) {
				$checked[ $slug ] = $theme['version'];
			}

			ksort( $checked );

			// $force_check prevents transient lookup.
			if ( ! $force_check ) {
				$transient = get_transient( $transient_name );

				// If cached response was found, compare $transient['checked'] against $checked and ignore if they don't match (themes/versions have changed).
				if ( is_array( $transient ) ) {
					$transient_checked = $transient['checked'] ?? array();

					if ( wp_json_encode( $checked ) !== wp_json_encode( $transient_checked ) ) {
						$transient = false;
					}
				}

				if ( $transient !== false ) {
					return $transient;
				}
			}

			$post = $this->request_post( array( $type => wp_json_encode( $this->{$type} ) ) );

			// Check update from connect.
			$response = $this->request( $endpoint, $post );

			if ( is_wp_error( $response ) ) {
				return array();
			}

			if ( isset( $response['code'] ) && isset( $response['message'] ) ) {
				return array();
			}

			// Append checked reference.
			if ( is_array( $response ) ) {
				$response['checked'] = $checked;

				// Check information from response here
				do_action( 'hocwp_theme_requested_updates', $response, $this );
			}

			// Allow json to include expiration but force minimum and max for safety.
			$expiration = $this->get_expiration( $response, DAY_IN_SECONDS, MONTH_IN_SECONDS );

			// Update transient and return.
			set_transient( $transient_name, $response, $expiration );

			return $response;
		}

		public function get_theme_updates( $force_check = false ) {
			return $this->get_updates( 'hocwp_theme_theme_updates', 'themes', 'v1/themes/update-check', $force_check );
		}

		public function modify_theme_details( $result, $action = null, $args = null ) {
			return $this->modify_details( 'themes', $result, $action, $args );
		}

		public function modify_plugins_transient( $transient ) {
			return $this->modify_transient( $transient, array( $this, 'get_plugin_updates' ), 'plugins' );
		}

		private function request_post( $args = array() ) {
			$defaults = array(
				'wp'    => wp_json_encode(
					array(
						'wp_name'      => get_bloginfo( 'name' ),
						'wp_url'       => home_url(),
						'wp_version'   => get_bloginfo( 'version' ),
						'wp_language'  => get_bloginfo( 'language' ),
						'wp_timezone'  => get_option( 'timezone_string' ),
						'wp_multisite' => (int) is_multisite(),
						'php_version'  => PHP_VERSION,
					)
				),
				'hocwp' => wp_json_encode(
					array(
						'version' => HOCWP_THEME_CORE_VERSION
					)
				),
			);

			return wp_parse_args( $args, $defaults );
		}

		public function get_github_release( $repository, $username = '' ) {
			$tr_name = 'hocwp_theme_update_' . $repository . '_release_data';

			if ( false === ( $data = get_transient( $tr_name ) ) ) {
				if ( empty( $username ) ) {
					$username = $this->github_username;
				}

				$data = wp_remote_get( 'https://api.github.com/repos/' . $username . '/' . $repository . '/releases/latest' );
				$data = wp_remote_retrieve_body( $data );
				$data = json_decode( $data );

				if ( is_object( $data ) && isset( $data->tag_name ) ) {
					set_transient( $tr_name, $data, $this->get_expiration( '', DAY_IN_SECONDS, MONTH_IN_SECONDS ) );
				}
			}

			return $data;
		}

		public function get_github_data_header( $repository, $file ) {
			return get_file_data( 'https://raw.githubusercontent.com/' . $this->build_github_endpoint( $repository ) . '/master/' . $file, array(
				'version'      => 'Version',
				'tested'       => 'Tested up to',
				'requires'     => 'Requires at least',
				'requires_php' => 'Requires PHP'
			) );
		}

		private function build_github_endpoint( $repository ) {
			return $this->github_username . '/' . $repository;
		}

		public function get_plugin_updates( $force_check = false ) {
			return $this->get_updates( 'hocwp_theme_plugin_updates', 'plugins', 'v1/plugins/update-check', $force_check );
		}

		public function request( $endpoint = '', $body = null ) {
			if ( empty( $endpoint ) ) {
				return $endpoint;
			}

			// Determine URL.
			$url = trailingslashit( $this->get_api_url() ) . ltrim( $endpoint, '/' );

			// Staging environment.
			if ( defined( 'LDC_DEV_API' ) && LDC_DEV_API ) {
				// Change api url to dev environment and debug log
				$url = trailingslashit( LDC_DEV_API ) . $endpoint;
				CAD_DEBUG( $url );
				CAD_DEBUG( $body );
			}

			// Make request.
			$raw_response = wp_remote_post(
				$url,
				array(
					'timeout' => 10,
					'body'    => $body,
				)
			);

			// Handle response error.
			if ( is_wp_error( $raw_response ) ) {
				return $raw_response;

				// Handle http error.
			} elseif ( wp_remote_retrieve_response_code( $raw_response ) !== 200 ) {
				return new WP_Error( 'server_error', wp_remote_retrieve_response_message( $raw_response ) );
			}

			$raw_response = wp_remote_retrieve_body( $raw_response );

			if ( empty( $raw_response ) ) {
				return $raw_response;
			}

			// Decode JSON response.
			$json = json_decode( $raw_response, true );

			// Allow non json value.
			if ( $json === null ) {
				return $raw_response;
			}

			return $json;
		}

		public function get_expiration( $response = false, $min = 0, $max = 0 ) {
			$expiration = 0;

			// Check possible error conditions.
			if ( is_wp_error( $response ) || is_string( $response ) ) {
				return 15 * MINUTE_IN_SECONDS;
			}

			// Use the server requested expiration if present.
			if ( is_array( $response ) && isset( $response['expiration'] ) ) {
				$expiration = (int) $response['expiration'];
			}

			// Use the minimum if neither check matches, or ensure the server expiration isn't lower than our minimum.
			if ( $expiration < $min ) {
				return $min;
			}

			// Ensure the server expiration isn't higher than our max.
			if ( $expiration > $max ) {
				return $max;
			}

			return $expiration;
		}

		function refresh_plugins_transient() {
			delete_site_transient( 'update_plugins' );
			delete_transient( 'hocwp_theme_plugin_updates' );
		}

		function refresh_themes_transient() {
			delete_site_transient( 'update_themes' );
			delete_transient( 'hocwp_theme_theme_updates' );
		}

		private function modify_details( $type, $result, $action = null, $args = null ) {
			$do_action = rtrim( $type, 's' ) . '_information';

			// Only for 'plugin_information' action.
			if ( $action !== $do_action ) {
				return $result;
			}

			// Find item via slug.
			$item = $this->get_item_by( $type, 'slug', $args->slug );

			if ( ! $item ) {
				return $result;
			}

			// Get data from connect or cache.
			$response = $this->get_info( $type, $item['id'] );

			// Bail early if no response.
			if ( ! is_array( $response ) ) {
				return $result;
			}

			// Remove tags (different context).
			unset( $response['tags'] );

			// Convert to object.
			$response = (object) $response;

			$sections = array(
				'description'    => '',
				'installation'   => '',
				'changelog'      => '',
				'upgrade_notice' => '',
			);

			foreach ( $sections as $k => $v ) {
				$sections[ $k ] = $response->{$k} ?? '';
			}

			$response->sections = $sections;

			return $response;
		}

		public function modify_plugin_details( $result, $action = null, $args = null ) {
			return $this->modify_details( 'plugins', $result, $action, $args );
		}

		public function get_item_by( $type, $key = '', $value = null ) {
			foreach ( $this->{$type} as $item ) {
				if ( $item[ $key ] === $value ) {
					return $item;
				}
			}

			return false;
		}

		public function get_plugin_by( $key = '', $value = null ) {
			return $this->get_item_by( 'plugins', $key, $value );
		}

		private function get_info( $type, $id = '', $force_check = false ) {
			$transient_name = 'hocwp_theme_' . $type . '_info_' . $id;

			// check cache but allow for $force_check override.
			if ( ! $force_check ) {
				$transient = get_transient( $transient_name );

				if ( $transient !== false ) {
					return $transient;
				}
			}

			// Get item info from connect
			$response = $this->request( 'v1/' . $type . '/get-info/?p=' . $id );

			// convert string (misc error) to WP_Error object.
			if ( is_string( $response ) ) {
				$response = new WP_Error( 'server_error', esc_html( $response ) );
			}

			if ( isset( $response['code'] ) && isset( $response['message'] ) ) {
				return new WP_Error( $response['code'], $response['message'] );
			}

			// allow json to include expiration but force minimum and max for safety.
			$expiration = $this->get_expiration( $response, DAY_IN_SECONDS, MONTH_IN_SECONDS );

			// update transient.
			set_transient( $transient_name, $response, $expiration );

			return $response;
		}

		public function get_theme_info( $id = '', $force_check = false ) {
			return $this->get_info( 'themes', $id, $force_check );
		}

		public function get_plugin_info( $id = '', $force_check = false ) {
			return $this->get_info( 'plugins', $id, $force_check );
		}

		private function add_item( $type, $item ) {
			if ( ! is_array( $item ) ) {
				return;
			}

			if ( ! is_array( $this->{$type} ) ) {
				$this->{$type} = array();
			}

			// validate.
			$item = wp_parse_args(
				$item,
				array(
					'id'       => '',
					'key'      => '',
					'slug'     => '',
					'basename' => '',
					'version'  => '',
				)
			);

			if ( empty( $item['basename'] ) ) {
				$item['basename'] = $item['slug'];
			}

			if ( empty( $item['slug'] ) ) {
				$item['slug'] = $item['basename'];
			}

			$item['slug'] = dirname( $item['slug'] );

			if ( 'plugins' == $type ) {
				// Check if is_plugin_active() function exists. This is required on the front end of the
				// site, since it is in a file that is normally only loaded in the admin.
				if ( ! function_exists( 'is_plugin_active' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				}

				// add if is active plugin (not included in theme).
				if ( ! is_plugin_active( $item['basename'] ) ) {
					return;
				}
			}

			$this->{$type}[ $item['basename'] ] = $item;
		}

		public function add_plugin( $plugin ) {
			$this->add_item( 'plugins', $plugin );
		}

		public function add_theme( $theme ) {
			$this->add_item( 'themes', $theme );
		}
	}

	function hocwp_theme_updates() {
		global $hocwp_theme_updates;

		if ( ! isset( $hocwp_theme_updates ) ) {
			$hocwp_theme_updates = new HOCWP_Theme_Updates();
		}

		return $hocwp_theme_updates;
	}

	function hocwp_theme_register_plugin_update( $plugin ) {
		hocwp_theme_updates()->add_plugin( $plugin );
	}

	function hocwp_theme_register_theme_update( $theme ) {
		hocwp_theme_updates()->add_theme( $theme );
	}
}