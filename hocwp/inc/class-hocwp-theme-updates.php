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
			$this->refresh_themes_transient();
			// Append update information to transient.
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'modify_plugins_transient' ) );

			// Modify plugin data visible in the 'View details' popup.
			add_filter( 'plugins_api', array( $this, 'modify_plugin_details' ), 10, 3 );

			add_filter( 'pre_set_site_transient_update_themes', array( $this, 'modify_themes_transient' ) );
			add_filter( 'themes_api', array( $this, 'modify_theme_details' ), 10, 3 );
		}

		public function modify_themes_transient( $transient ) {
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
			$updates = $this->get_theme_updates( $force_check );

			// Append my themes.
			if ( is_array( $updates ) ) {
				if ( ! empty( $updates['themes'] ) ) {
					foreach ( $updates['themes'] as $basename => $update ) {
						$transient->response[ $basename ] = $update;
					}
				}

				if ( ! empty( $updates['no_update'] ) ) {
					foreach ( $updates['no_update'] as $basename => $update ) {
						$transient->no_update[ $basename ] = $update;
					}
				}
			}

			++ $this->checked;

			return $transient;
		}

		public function get_theme_updates( $force_check = false ) {
			$transient_name = 'hocwp_theme_theme_updates';

			// Don't call our site if no themes have registered updates.
			if ( empty( $this->themes ) ) {
				return array();
			}

			// Construct array of 'checked' plugins.
			// Sort by key to avoid detecting change due to "include order".
			$checked = array();

			foreach ( $this->themes as $slug => $theme ) {
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

			$post = $this->request_post( array( 'themes' => wp_json_encode( $this->themes ) ) );

			// Check update from connect.
			$response = $this->request( 'v1/themes/update-check', $post );

			// Append checked reference.
			if ( is_array( $response ) ) {
				$response['checked'] = $checked;

				// Check information from response here
			}

			// Allow json to include expiration but force minimum and max for safety.
			$expiration = $this->get_expiration( $response, DAY_IN_SECONDS, MONTH_IN_SECONDS );

			// Update transient and return.
			set_transient( $transient_name, $response, $expiration );

			return $response;
		}

		public function modify_theme_details( $result, $action = null, $args = null ) {

			return $result;
		}

		public function modify_plugins_transient( $transient ) {
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
			$updates = $this->get_plugin_updates( $force_check );

			// Append my plugins.
			if ( is_array( $updates ) ) {
				if ( ! empty( $updates['plugins'] ) ) {
					foreach ( $updates['plugins'] as $basename => $update ) {
						$transient->response[ $basename ] = $update;
					}
				}

				if ( ! empty( $updates['no_update'] ) ) {
					foreach ( $updates['no_update'] as $basename => $update ) {
						$transient->no_update[ $basename ] = $update;
					}
				}
			}

			++ $this->checked;

			return $transient;
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
			$transient_name = 'hocwp_theme_plugin_updates';

			// Don't call our site if no plugins have registered updates.
			if ( empty( $this->plugins ) ) {
				return array();
			}

			// Construct array of 'checked' plugins.
			// Sort by key to avoid detecting change due to "include order".
			$checked = array();

			foreach ( $this->plugins as $basename => $plugin ) {
				$checked[ $basename ] = $plugin['version'];
			}

			ksort( $checked );

			// $force_check prevents transient lookup.
			if ( ! $force_check ) {
				$transient = get_transient( $transient_name );

				// If cached response was found, compare $transient['checked'] against $checked and ignore if they don't match (plugins/versions have changed).
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

			$post = $this->request_post( array( 'plugins' => wp_json_encode( $this->plugins ) ) );

			// Check update from connect.
			$response = $this->request( 'plugin', $post );

			// Append checked reference.
			if ( is_array( $response ) ) {
				$response['checked'] = $checked;

				// Check information from response here
			}

			// Allow json to include expiration but force minimum and max for safety.
			$expiration = $this->get_expiration( $response, DAY_IN_SECONDS, MONTH_IN_SECONDS );

			// Update transient and return.
			set_transient( $transient_name, $response, $expiration );

			return $response;
		}

		public function request( $endpoint = '', $body = null ) {
			if ( empty( $endpoint ) ) {
				return $endpoint;
			}

			// Determine URL.
			$url = $this->api_url . $endpoint;

			// Staging environment.
			if ( HOCWP_THEME_DEVELOPING ) {
				// Change api url to dev environment and debug log
				$url = 'http://localhost/api/' . $endpoint;
			}

			$raw_response = '';

			$error = new WP_Error();

			if ( str_contains( $url, 'github.com' ) || empty( $this->api_url ) ) {
				$results = array();

				if ( 'theme' == $endpoint ) {
					foreach ( $this->themes as $slug => $info ) {
						if ( ! empty( $slug ) ) {
							$data = $this->get_github_release( $slug );

							if ( is_object( $data ) && isset( $data->tag_name ) ) {
								$cv = $info['version'] ?? '';

								$remote = $this->get_github_data_header( $slug, 'style.css' );

								$version = $remote['version'] ?? '';

								if ( version_compare( $version, $cv, '>' ) ) {
									$download = 'https://github.com/' . $this->build_github_endpoint( $slug ) . '/archive/refs/tags/' . $data->tag_name . '.zip';

									$results[ $slug ] = array(
										'theme'        => $slug,
										'new_version'  => $version,
										'url'          => 'https://github.com/' . $this->build_github_endpoint( $slug ) . '/compare/' . $data->tag_name . '...master',
										'tested'       => $remote['tested'] ?? '',
										'requires'     => $remote['requires'] ?? '',
										'package'      => $download,
										'requires_php' => $remote['requires_php'] ?? ''
									);
								}
							} elseif ( is_object( $data ) && isset( $data->message ) && isset( $data->documentation_url ) ) {
								$error->add( sanitize_title( $data->message ), $data->message );
							}
						}
					}

					if ( HT()->array_has_value( $results ) ) {
						$raw_response = wp_json_encode( array(
							'themes' => $results
						) );
					}
				}
			} else {
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
			}

			if ( empty( $raw_response ) ) {
				if ( $error->has_errors() ) {
					return $error;
				}

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

		public function modify_plugin_details( $result, $action = null, $args = null ) {
			$plugin = false;

			// Only for 'plugin_information' action.
			if ( $action !== 'plugin_information' ) {
				return $result;
			}

			// Find plugin via slug.
			$plugin = $this->get_plugin_by( 'slug', $args->slug );

			if ( ! $plugin ) {
				return $result;
			}

			// Get data from connect or cache.
			$response = $this->get_plugin_info( $plugin['id'] );

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
				$sections[ $k ] = $response->$k;
			}

			$response->sections = $sections;

			return $response;
		}

		public function get_plugin_by( $key = '', $value = null ) {
			foreach ( $this->plugins as $plugin ) {
				if ( $plugin[ $key ] === $value ) {
					return $plugin;
				}
			}

			return false;
		}

		public function get_plugin_info( $id = '', $force_check = false ) {
			$transient_name = 'hocwp_theme_plugin_info_' . $id;

			// check cache but allow for $force_check override.
			if ( ! $force_check ) {
				$transient = get_transient( $transient_name );

				if ( $transient !== false ) {
					return $transient;
				}
			}

			// Get plugin info from connect
			$response = $this->request( '' . $id );

			// convert string (misc error) to WP_Error object.
			if ( is_string( $response ) ) {
				$response = new WP_Error( 'server_error', esc_html( $response ) );
			}

			// allow json to include expiration but force minimum and max for safety.
			$expiration = $this->get_expiration( $response, DAY_IN_SECONDS, MONTH_IN_SECONDS );

			// update transient.
			set_transient( $transient_name, $response, $expiration );

			return $response;
		}

		public function add_plugin( $plugin ) {
			if ( ! is_array( $this->plugins ) ) {
				$this->plugins = array();
			}

			// validate.
			$plugin = wp_parse_args(
				$plugin,
				array(
					'id'       => '',
					'key'      => '',
					'slug'     => '',
					'basename' => '',
					'version'  => '',
				)
			);

			// Check if is_plugin_active() function exists. This is required on the front end of the
			// site, since it is in a file that is normally only loaded in the admin.
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			// add if is active plugin (not included in theme).
			if ( is_plugin_active( $plugin['basename'] ) ) {
				$this->plugins[ $plugin['basename'] ] = $plugin;
			}
		}

		public function add_theme( $theme ) {
			if ( ! is_array( $this->themes ) ) {
				$this->themes = array();
			}

			// validate.
			$theme = wp_parse_args(
				$theme,
				array(
					'id'       => '',
					'key'      => '',
					'slug'     => '',
					'basename' => '',
					'version'  => '',
				)
			);

			$this->themes[ $theme['slug'] ] = $theme;
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