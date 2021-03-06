<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! HOCWP_THEME_DOING_AJAX ) {
	return;
}

class HOCWP_Theme_AJAX {
	protected static $instance;

	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		if ( self::$instance instanceof self ) {
			return;
		}

		if ( ! HOCWP_THEME_DOING_AJAX ) {
			return;
		}

		add_action( 'wp_ajax_hocwp_theme_ajax', array( $this, 'default_callback' ) );
		add_action( 'wp_ajax_nopriv_hocwp_theme_ajax', array( $this, 'default_callback' ) );
		add_action( 'wp_ajax_hocwp_theme_ajax_private', array( $this, 'default_callback' ) );

		// Back compact
		add_action( 'wp_ajax_hocwp_theme_update_meta', array( $this, 'update_meta' ) );
		add_action( 'wp_ajax_nopriv_hocwp_theme_update_meta', array( $this, 'update_meta' ) );
	}

	public function default_callback() {
		$method = HT()->get_method_value( 'method', 'request', 'post' );
		$nonce  = HT()->get_method_value( 'nonce', $method );

		$data = array(
			'message' => __( 'AJAX nonce is invalid.', 'hocwp-theme' )
		);

		if ( HOCWP_Theme()->verify_nonce( $nonce ) ) {
			$callback = HT()->get_method_value( 'callback', $method );

			$tmp = array( $this, $callback );

			if ( is_callable( $tmp ) ) {
				$callback = $tmp;
			}

			if ( is_callable( $callback ) ) {
				call_user_func( $callback );

				$data['message'] = __( 'AJAX has been done successfully!', 'hocwp-theme' );
				wp_send_json_success( $data );
			}

			$data['message'] = __( 'Invalid AJAX callback.', 'hocwp-theme' );
		}

		wp_send_json_error( $data );
	}

	public function update_meta() {
		// Type of meta table. Example: usermeta, postmeta, termmeta.
		$meta_type = HT()->get_method_value( 'meta_type' );

		// Object id for using in meta table. Example: post_id, term_id, user_id.
		$object_id = HT()->get_method_value( 'object_id' );

		// Meta key for using in meta table.
		$meta_key = HT()->get_method_value( 'meta_key' );

		if ( ! empty( $meta_type ) && HT()->is_positive_number( $object_id ) && ! empty( $meta_key ) ) {
			$value_type = HT()->get_method_value( 'value_type' );

			if ( 'up_down' == $value_type ) {
				$key    = $meta_type . '_' . $meta_key;
				$change = 1;

				if ( is_user_logged_in() ) {
					$values = get_user_meta( get_current_user_id(), $key, true );
				} else {
					$values = isset( $_SESSION[ $key ] ) ? $_SESSION[ $key ] : '';
				}

				$values = maybe_unserialize( $values );

				if ( ( is_array( $values ) && in_array( $object_id, $values ) ) || ( is_numeric( $values ) && $object_id == $values ) ) {
					$change = - 1;
				}

				$meta_value = HT()->get_method_value( 'meta_value', 'post' );

				if ( empty( $meta_value ) ) {
					$meta_value = get_metadata( $meta_type, $object_id, $meta_key, true );
				}

				$meta_value = absint( $meta_value );

				$meta_value += $change;

				$updated = update_metadata( $meta_type, $object_id, $meta_key, $meta_value );

				if ( $updated ) {
					$values = (array) $values;

					if ( 1 == $change ) {
						if ( ! in_array( $object_id, $values ) ) {
							$values[] = $object_id;
						}
					} else {
						unset( $values[ array_search( $object_id, $values ) ] );
					}

					if ( is_user_logged_in() ) {
						update_user_meta( get_current_user_id(), $key, $values );
					} else {
						$_SESSION[ $key ] = maybe_serialize( $values );
					}

					$formatted = ( is_numeric( $meta_value ) ) ? number_format( $meta_value ) : $meta_value;

					$data = array(
						'meta_value'           => $meta_value,
						'formatted_meta_value' => $formatted
					);

					wp_send_json_success( $data );
				}
			} elseif ( 'add_remove' == $value_type ) {
				$meta_value = HT()->get_method_value( 'meta_value', 'post' );

				if ( empty( $meta_value ) ) {
					$meta_value = get_metadata( $meta_type, $object_id, $meta_key, true );
				}

				if ( ! is_array( $meta_value ) ) {
					$meta_value = array();
				}

				$change_value = HT()->get_method_value( 'change_value' );

				if ( empty( $change_value ) ) {
					$change_value = HT()->get_method_value( 'change_id' );
				}

				if ( ! is_array( $change_value ) ) {
					$data = array();

					if ( in_array( $change_value, $meta_value ) ) {
						unset( $meta_value[ array_search( $change_value, $meta_value ) ] );
						$data['job_action'] = 'undo';
					} else {
						$meta_value[] = $change_value;

						$data['job_action'] = 'do';
					}

					$updated = update_metadata( $meta_type, $object_id, $meta_key, $meta_value );

					if ( $updated ) {
						wp_send_json_success( $data );
					}
				}
			}

			do_action( 'hocwp_theme_update_meta_ajax', $meta_type, $object_id, $meta_key );
		} else {
			$this->update_meta_fallback();
		}

		wp_send_json_error();
	}

	private function update_meta_fallback() {
		$data      = array();
		$object_id = HT()->get_method_value( 'object_id' );

		if ( HT()->is_positive_number( $object_id ) ) {
			$meta_key = HT()->get_method_value( 'meta_key' );

			if ( ! empty( $meta_key ) ) {
				$meta_type = HT()->get_method_value( 'meta_type' );

				if ( empty( $meta_type ) ) {
					$meta_type = 'post';
				}

				if ( isset( $_POST['meta_value'] ) ) {
					$meta_value = $_POST['meta_value'];
				} else {
					$meta_value = isset( $_POST['meta_change'] ) ? $_POST['meta_change'] : 1;
					$old        = null;

					if ( isset( $_POST['old_meta_value'] ) ) {
						$old = $_POST['old_meta_value'];
					}

					if ( ! is_numeric( $old ) ) {
						$old = get_metadata( $meta_type, $object_id, $meta_key, true );
					}

					if ( ! is_numeric( $old ) ) {
						$old = 0;
					}

					$meta_value += $old;
				}

				$updated = update_metadata( $meta_type, $object_id, $meta_key, $meta_value );

				if ( $updated ) {
					$data['meta_value'] = $meta_value;

					if ( is_numeric( $meta_value ) ) {
						$meta_value = number_format( $meta_value );
					}

					$data['formatted_meta_value'] = $meta_value;
					wp_send_json_success( $data );
				}
			}
		}

		wp_send_json_error( $data );
	}
}

HOCWP_Theme_AJAX::get_instance();

function hocwp_theme_update_facebook_data_ajax_callback() {
	$post_id = isset( $_GET['post_id'] ) ? $_GET['post_id'] : '';

	if ( HT()->is_positive_number( $post_id ) ) {
		$event = isset( $_GET['event'] ) ? $_GET['event'] : '';

		if ( 'like' == $event || 'unlike' == $event ) {
			$likes = get_post_meta( $post_id, 'likes', true );
			$likes = absint( $likes );

			if ( 'like' == $event ) {
				$likes ++;
			} else {
				$likes --;
			}

			update_post_meta( $post_id, 'likes', absint( $likes ) );
		}
	}

	exit;
}

add_action( 'wp_ajax_hocwp_theme_update_facebook_data', 'hocwp_theme_update_facebook_data_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_theme_update_facebook_data', 'hocwp_theme_update_facebook_data_ajax_callback' );

function hocwp_theme_change_post_name_ajax_callback() {
	$post_id = HT()->get_method_value( 'post_id' );

	if ( HT()->is_positive_number( $post_id ) ) {
		$post_name = HT()->get_method_value( 'post_name' );

		if ( ! empty( $post_name ) ) {
			$data = array(
				'ID'        => $post_id,
				'post_name' => $post_name
			);

			wp_update_post( $data );
		}
	}

	exit;
}

add_action( 'wp_ajax_hocwp_theme_change_post_name', 'hocwp_theme_change_post_name_ajax_callback' );

function hocwp_theme_detect_client_info_ajax_callback() {
	$screen_width = isset( $_GET['screen_width'] ) ? $_GET['screen_width'] : '';

	if ( HT()->is_positive_number( $screen_width ) ) {
		$client_info = HT_Util()->get_client_info( true );

		$client_info['screen_width'] = $screen_width;

		$client_info = json_encode( $client_info, JSON_UNESCAPED_SLASHES );

		$_SESSION['hocwp_theme_client_info'] = $client_info;
		setcookie( 'hocwp_theme_client_info', $client_info );
	}

	exit;
}

add_action( 'wp_ajax_hocwp_theme_detect_client_info', 'hocwp_theme_detect_client_info_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_theme_detect_client_info', 'hocwp_theme_detect_client_info_ajax_callback' );

function hocwp_theme_boolean_meta_ajax_callback() {
	$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';

	if ( HOCWP_Theme()->verify_nonce( $nonce ) ) {
		$meta_type  = HT()->get_method_value( 'meta_type' );
		$meta_key   = HT()->get_method_value( 'meta_key' );
		$meta_value = absint( HT()->get_method_value( 'meta_value' ) );
		$object_id  = HT()->get_method_value( 'object_id' );

		if ( 1 == $meta_value ) {
			$meta_value = 0;
		} else {
			$meta_value = 1;
		}

		$updated = false;

		switch ( $meta_type ) {
			case 'post':
				$updated = update_post_meta( $object_id, $meta_key, $meta_value );
				break;
			case 'term':
				$updated = update_term_meta( $object_id, $meta_key, $meta_value );
				break;
			case 'user':
				$updated = update_user_meta( $object_id, $meta_key, $meta_value );
				break;
			case 'comment':
				$updated = update_comment_meta( $object_id, $meta_key, $meta_value );
				break;
		}

		if ( $updated ) {
			$data = array(
				'meta_value' => $meta_value
			);

			wp_send_json_success( $data );
		}
	}

	wp_send_json_error();
}

add_action( 'wp_ajax_hocwp_theme_boolean_meta_ajax', 'hocwp_theme_boolean_meta_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_theme_boolean_meta_ajax', 'hocwp_theme_boolean_meta_ajax_callback' );

function hocwp_theme_backup_this_theme_ajax_callback() {
	$data = array(
		'message' => __( 'Backup failed.', 'hocwp-theme' )
	);

	if ( function_exists( 'hocwp_theme_zip_current_theme' ) ) {
		hocwp_theme_zip_current_theme();
		hocwp_theme_dev_export_database();
		hocwp_theme_backup_wp_content_folders();
		$data['message'] = __( 'Backup done!', 'hocwp-theme' );
		wp_send_json_success( $data );
	}

	wp_send_json_error( $data );
}

add_action( 'wp_ajax_backup_this_theme', 'hocwp_theme_backup_this_theme_ajax_callback' );

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

function hocwp_theme_wp_ajax_hocwp_theme_search_post_ajax_callback() {
	$results = array();

	$term = $_GET['term'] ?? '';

	if ( ! empty( $term ) ) {
		$post_type = $_GET['post_type'] ?? '';

		if ( empty( $post_type ) ) {
			$post_type = 'any';
		}

		$post_ids    = $_GET['post_ids'] ?? '';
		$search_post = $_GET['search_post'] ?? '';

		$post_ids = explode( ',', $post_ids );
		$post_ids = array_map( 'trim', $post_ids );

		$search_post = explode( ',', $search_post );
		$search_post = array_map( 'trim', $search_post );

		foreach ( $search_post as $key => $sp ) {
			if ( ! is_numeric( $sp ) ) {
				unset( $search_post[ $key ] );
			}
		}

		$excludes = array_merge( $post_ids, $search_post );
		$excludes = array_unique( $excludes );
		$excludes = array_filter( $excludes );

		$args = array(
			'post_type'      => $post_type,
			'posts_per_page' => - 1,
			'post_status'    => 'any',
			's'              => $term,
			'post__not_in'   => $excludes
		);

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			foreach ( $query->get_posts() as $obj ) {
				$results[] = array(
					'value' => $obj->ID,
					'label' => sprintf( '%s (%s - %s)', $obj->post_title, $obj->ID, $obj->post_type )
				);
			}
		}
	}

	echo json_encode( $results );

	wp_die();
}

add_action( 'wp_ajax_hocwp_theme_search_post', 'hocwp_theme_wp_ajax_hocwp_theme_search_post_ajax_callback' );

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
			wp_send_json_success( $data );
		}
	}

	wp_send_json_error( $data );
}

add_action( 'wp_ajax_hocwp_theme_change_administrative_email', 'hocwp_theme_change_administrative_email_ajax_callback' );