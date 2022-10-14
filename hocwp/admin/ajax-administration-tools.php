<?php
defined( 'ABSPATH' ) || exit;

function hocwp_theme_change_administrative_email_ajax_callback() {
	$data = array();

	$email = $_POST['email'] ?? '';

	if ( ! is_email( $email ) ) {
		$data['message'] = __( 'Invalid email.', 'hocwp-theme' );
	} else {
		$old_email = get_bloginfo( 'admin_email' );
		$result    = update_option( 'admin_email', $email );

		if ( $result ) {
			update_option( 'new_admin_email', $email );
			wp_site_admin_email_change_notification( $old_email, $email, 'admin_email' );

			$do_action = $_POST['do_action'] ?? '';

			if ( 'create_new_admin' == $do_action ) {
				$pass = $email . 'M' . date( 'm' ) . 'Y' . date( 'Y' );

				$userdata = array(
					'user_login' => $email,
					'user_email' => $email,
					'user_pass'  => $pass,
					'role'       => 'administrator'
				);

				$result = wp_insert_user( $userdata );

				if ( $result ) {
					$data['new_password'] = sprintf( __( 'Your new password: %s', 'hocwp-theme' ), $pass );

					$args = array(
						'role'    => 'administrator',
						'orderby' => 'ID',
						'number'  => 2,
						'fields'  => 'ID'
					);

					$query = new WP_User_Query( $args );

					// Only update first administrator user
					if ( HT()->array_has_value( $ids = $query->get_results() ) && 1 < $query->get_total() ) {
						$user_id = current( $ids );

						$userdata = array(
							'ID'   => $user_id,
							'role' => 'subscriber'
						);

						wp_update_user( $userdata );
					}

					// Update admin user with old email address
					$user = get_user_by( 'email', $old_email );

					if ( $user instanceof WP_User ) {
						$userdata = array(
							'ID'   => $user->ID,
							'role' => 'subscriber'
						);

						wp_update_user( $userdata );
					}
				}
			}

			if ( $result ) {
				wp_send_json_success( $data );
			}
		}
	}

	wp_send_json_error( $data );
}

add_action( 'wp_ajax_hocwp_theme_change_administrative_email', 'hocwp_theme_change_administrative_email_ajax_callback' );

function hocwp_theme_send_test_email_ajax_callback() {
	$data = array();

	$email = $_POST['email'] ?? '';

	if ( ! is_email( $email ) ) {
		$email = get_bloginfo( 'admin_email' );
	}

	if ( is_email( $email ) ) {
		$subject = sprintf( __( '[%s] Testing email', 'hocwp-theme' ), get_bloginfo( 'name' ) );
		$message = __( 'This is a testing email. If you see this message, it means your email setting works normally.', 'hocwp-theme' );
		$sent    = HT_Util()->html_mail( $email, $subject, $message );

		if ( $sent ) {
			wp_send_json_success( $data );
		} else {
			$data['message'] = __( 'The mailing system is not working!', 'hocwp-theme' );
		}
	} else {
		$data['message'] = __( 'Invalid email address!', 'hocwp-theme' );
	}

	wp_send_json_error( $data );
}

add_action( 'wp_ajax_hocwp_theme_send_test_email', 'hocwp_theme_send_test_email_ajax_callback' );

function hocwp_theme_fetch_option_ajax_callback() {
	$data = array();

	$option = $_POST['option'] ?? '';
	$option = get_option( $option );
	$option = maybe_serialize( $option );

	if ( empty( $option ) ) {
		$data['message'] = __( 'Invalid option name or empty option value.', 'hocwp-theme' );
		wp_send_json_error( $data );
	}

	$data['option'] = $option;

	wp_send_json_success( $data );
}

add_action( 'wp_ajax_hocwp_theme_fetch_option', 'hocwp_theme_fetch_option_ajax_callback' );

function hocwp_theme_import_settings_ajax_callback() {
	$data = array();

	$option = $_POST['option'] ?? '';
	$value  = $_POST['value'] ?? '';
	$value  = wp_unslash( $value );
	$value  = maybe_unserialize( $value );

	if ( ! empty( $option ) && ! empty( $value ) ) {
		$data['updated'] = update_option( $option, $value );
		wp_send_json_success( $data );
	}

	wp_send_json_error( $data );
}

add_action( 'wp_ajax_hocwp_theme_import_settings', 'hocwp_theme_import_settings_ajax_callback' );

function hocwp_theme_change_site_url_ajax_callback() {
	$data = array();

	$old_url = $_POST['old_url'] ?? '';
	$new_url = $_POST['new_url'] ?? '';

	if ( ! empty( $old_url ) && ! empty( $new_url ) ) {
		// Auto backup database first if available
		if ( function_exists( 'hocwp_theme_dev_export_database' ) ) {
			hocwp_theme_dev_export_database();
		}

		global $wpdb;

		$sqls = array(
			"UPDATE $wpdb->options SET option_value = replace(option_value, '%s', '%s');",
			"UPDATE $wpdb->posts SET post_content = replace(post_content, '%s', '%s');",
			"UPDATE $wpdb->postmeta SET meta_value = replace(meta_value,'%s','%s');",
			"UPDATE $wpdb->users SET user_url = replace(user_url, '%s','%s');",
			"UPDATE $wpdb->usermeta SET meta_value = replace(meta_value, '%s','%s');",
			"UPDATE $wpdb->termmeta SET meta_value = replace(meta_value, '%s','%s');",
			"UPDATE $wpdb->commentmeta SET meta_value = replace(meta_value, '%s','%s');",
			"UPDATE $wpdb->links SET link_url = replace(link_url, '%s','%s');",
			"UPDATE $wpdb->links SET link_image = replace(link_image, '%s','%s');",
			"UPDATE $wpdb->comments SET comment_content = replace(comment_content , '%s','%s');",
			"UPDATE $wpdb->posts SET guid = replace(guid, '%s','%s');"
		);

		foreach ( $sqls as $sql ) {
			$sql = sprintf( $sql, $old_url, $new_url );
			$wpdb->query( $sql );
		}

		wp_send_json_success( $data );
	}

	wp_send_json_error( $data );
}

add_action( 'wp_ajax_hocwp_theme_change_site_url', 'hocwp_theme_change_site_url_ajax_callback' );

function hocwp_theme_admin_tools_ajax_callback() {
	$data = array();

	if ( current_user_can( 'manage_options' ) ) {
		$do_action = $_REQUEST['do_action'] ?? '';

		switch ( $do_action ) {
			case 'update_cloudflare_settings':
			case 'fetch_cloudflare_settings':
				$api_key    = $_REQUEST['cloudflare_api_key'] ?? '';
				$api_token  = $_REQUEST['cloudflare_api_token'] ?? '';
				$user_email = $_REQUEST['cloudflare_user_email'] ?? '';
				$account_id = $_REQUEST['cloudflare_account_id'] ?? '';
				$zone_id    = $_REQUEST['cloudflare_zone_id'] ?? '';
				$domain     = $_REQUEST['cloudflare_domain'] ?? '';

				if ( ! empty( $api_token ) || ! empty( $api_key ) ) {
					if ( ! class_exists( 'HOCWP_Theme_Cloudflare_API' ) ) {
						require_once HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-cloudflare-api.php';
					}

					$params = array(
						'api_key'    => $api_key,
						'api_token'  => $api_token,
						'user_email' => $user_email,
						'account_id' => $account_id,
						'zone_id'    => $zone_id,
						'domain'     => $domain
					);

					$api = new HOCWP_Theme_Cloudflare_API( 'zones', $params );

					$settings = $_REQUEST['settings'] ?? '';

					if ( HT()->array_has_value( $settings ) ) {
						if ( 'fetch_cloudflare_settings' == $do_action ) {
							$value = '';

							foreach ( $settings as $key => $item ) {
								$value = $api->get_setting( $item['suffix'] );

								if ( is_wp_error( $value ) ) {
									$data['message'] = $value->get_error_message();
									break;
								}

								if ( is_object( $value ) ) {
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

							if ( ! is_wp_error( $value ) ) {
								$data['settings'] = $settings;

								wp_send_json_success( $data );
							}
						} elseif ( 'update_cloudflare_settings' == $do_action ) {
							$result = '';

							foreach ( $settings as $item ) {
								if ( $item['current_value'] != $item['value'] ) {
									$result = $api->update_setting( array( 'value' => $item['value'] ), $item['suffix'] );

									if ( is_wp_error( $valid = $api->is_response_valid( $result ) ) ) {
										$data['message'] = $valid->get_error_message();
										break;
									}
								}
							}

							if ( $result && ! is_wp_error( $result ) ) {
								wp_send_json_success( $data );
							}
						}
					}
				} else {
					$data['message'] = __( 'Please fill the required fields above.', 'hocwp-theme' );
				}

				break;
		}
	}

	wp_send_json_error( $data );
}

add_action( 'wp_ajax_hocwp_theme_admin_tools', 'hocwp_theme_admin_tools_ajax_callback' );

function hocwp_theme_delete_cache_ajax_callback() {
	$data = array();

	if ( current_user_can( 'manage_options' ) ) {
		$api_key    = $_REQUEST['cloudflare_api_key'] ?? '';
		$api_token  = $_REQUEST['cloudflare_api_token'] ?? '';
		$user_email = $_REQUEST['cloudflare_user_email'] ?? '';
		$account_id = $_REQUEST['cloudflare_account_id'] ?? '';
		$zone_id    = $_REQUEST['cloudflare_zone_id'] ?? '';
		$domain     = $_REQUEST['cloudflare_domain'] ?? '';

		$clear_pc = false;

		if ( ! empty( $api_token ) || ! empty( $api_key ) ) {
			if ( ! class_exists( 'HOCWP_Theme_Cloudflare_API' ) ) {
				require_once HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-cloudflare-api.php';
			}

			$params = array(
				'api_key'    => $api_key,
				'api_token'  => $api_token,
				'user_email' => $user_email,
				'account_id' => $account_id,
				'zone_id'    => $zone_id,
				'domain'     => $domain
			);

			$do_action = $_REQUEST['do_action'] ?? '';

			$api = new HOCWP_Theme_Cloudflare_API( 'zones', $params );

			if ( 'development_mode' == $do_action ) {
				$result = $api->enable_development_mode();
			} else {
				$result = $api->purge_cache();
			}

			if ( is_wp_error( $result ) ) {
				$data['message'] = $result->get_error_message();
			} elseif ( is_object( $result ) && $result->success ) {
				$clear_pc = true;
			}
		} else {
			$clear_pc = true;
		}

		if ( $clear_pc ) {
			$domain = $_REQUEST['domain'] ?? '';

			if ( empty( $domain ) || str_contains( home_url(), $domain ) ) {
				$data['message'] = __( 'All cache files have been deleted successfully!', 'hocwp-theme' );

				if ( defined( 'LSCWP_V' ) ) {
					do_action( 'litespeed_purge_all' );
				}

				wp_send_json_success( $data );
			} else {
				$domain = esc_url( $domain );
				$domain = add_query_arg( 'do_action', 'delete_cache', $domain );

				$result = wp_remote_get( $domain );

				if ( is_wp_error( $result ) ) {
					$data['message'] = $result->get_error_message();
				}
			}
		}
	}

	wp_send_json_error( $data );
}

add_action( 'wp_ajax_hocwp_theme_delete_cache', 'hocwp_theme_delete_cache_ajax_callback' );

function hocwp_theme_delete_transient_ajax_callback() {
	$data = array();

	$transient = $_POST['transient'] ?? '';
	HT_Util()->delete_transient( $transient );

	wp_send_json_success( $data );
}

add_action( 'wp_ajax_hocwp_theme_delete_transient', 'hocwp_theme_delete_transient_ajax_callback' );

global $hocwp_theme_import_administrative_boundaries;

if ( ! class_exists( 'HOCWP_Theme_Import_Administrative_Boundaries_Process' ) ) {
	require_once HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-import-administrative-boundaries.php';
}

if ( ! $hocwp_theme_import_administrative_boundaries instanceof HOCWP_Theme_Import_Administrative_Boundaries_Process ) {
	$hocwp_theme_import_administrative_boundaries = new HOCWP_Theme_Import_Administrative_Boundaries_Process();
}

function hocwp_theme_import_administrative_boundaries_ajax_callback() {
	global $hocwp_theme_import_administrative_boundaries;

	if ( ! class_exists( 'HOCWP_Theme_Import_Administrative_Boundaries_Process' ) ) {
		require_once HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-import-administrative-boundaries.php';
	}

	$data = array();

	$taxonomy = $_POST['taxonomy'] ?? '';

	if ( taxonomy_exists( $taxonomy ) ) {
		$district = $_POST['district'] ?? '';
		$commune  = $_POST['commune'] ?? '';

		$csv = HT_Util()->read_all_text( HOCWP_Theme()->core_path . '/inc/dia-gioi-hanh-chinh-viet-nam.csv' );
		$csv = HT()->explode_new_line( $csv );

		// Remove heading text
		array_shift( $csv );
		$csv = array_filter( $csv );

		if ( ! $hocwp_theme_import_administrative_boundaries instanceof HOCWP_Theme_Import_Administrative_Boundaries_Process ) {
			$hocwp_theme_import_administrative_boundaries = new HOCWP_Theme_Import_Administrative_Boundaries_Process();
		}

		$abs = $hocwp_theme_import_administrative_boundaries->convert_to_array( $csv, $district, $commune );

		foreach ( $abs as $id => $value ) {
			$item = array(
				'id'       => $id,
				'taxonomy' => $taxonomy,
				'value'    => $value
			);

			$hocwp_theme_import_administrative_boundaries->push_to_queue( $item );
		}

		$hocwp_theme_import_administrative_boundaries->save()->dispatch();

		wp_send_json_success( $data );
	}

	wp_send_json_error( $data );
}

add_action( 'wp_ajax_hocwp_theme_import_administrative_boundaries', 'hocwp_theme_import_administrative_boundaries_ajax_callback' );

function hocwp_theme_fetch_administrative_boundaries_ajax_callback() {
	$data = array();

	$type = $_REQUEST['type'] ?? '';
	$id   = $_REQUEST['id'] ?? '';

	if ( ! empty( $type ) && ! empty( $id ) ) {
		$csv = HT_Util()->read_all_text( HOCWP_Theme()->core_path . '/inc/dia-gioi-hanh-chinh-viet-nam.csv' );
		$csv = HT()->explode_new_line( $csv );

		// Remove heading text
		array_shift( $csv );
		$csv = array_filter( $csv );

		$lists = HT_Util()->convert_administrative_boundaries_to_array( $csv, true, true );

		$parent = $_REQUEST['parent'] ?? '';

		if ( 'province' == $type ) {
			$type  = 'district';
			$lists = $lists[ $id ] ?? '';
		} elseif ( 'district' == $type ) {
			$type  = 'commune';
			$lists = $lists[ $parent ][ $id ] ?? '';
		}

		$option = $_REQUEST['option'] ?? '';

		foreach ( $lists as $key => $item ) {
			if ( 'name' != $key && 'type' != $key ) {
				$option .= sprintf( '<option data-name="%s" value="%s" data-type="%s">%s</option>', esc_attr( $item['name'] ), esc_attr( $key ), esc_attr( $item['type'] ), $item['name'] );
			}
		}

		$data['type'] = $type;

		$data['option'] = $option;

		wp_send_json_success( $data );
	}

	wp_send_json_error( $data );
}

add_action( 'wp_ajax_fetch_administrative_boundaries', 'hocwp_theme_fetch_administrative_boundaries_ajax_callback' );
add_action( 'wp_ajax_nopriv_fetch_administrative_boundaries', 'hocwp_theme_fetch_administrative_boundaries_ajax_callback' );