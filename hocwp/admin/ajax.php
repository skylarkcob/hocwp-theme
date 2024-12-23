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
		$method = ht()->get_method_value( 'method', 'request', 'post' );
		$nonce  = ht()->get_method_value( 'nonce', $method );

		$data = array(
			'message' => __( 'AJAX nonce is invalid.', 'hocwp-theme' )
		);

		if ( hocwp_theme()->verify_nonce( $nonce ) ) {
			$callback = ht()->get_method_value( 'callback', $method );

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
		$meta_type = ht()->get_method_value( 'meta_type' );

		// Object id for using in meta table. Example: post_id, term_id, user_id.
		$object_id = ht()->get_method_value( 'object_id' );

		// Meta key for using in meta table.
		$meta_key = ht()->get_method_value( 'meta_key' );

		if ( ! empty( $meta_type ) && ht()->is_positive_number( $object_id ) && ! empty( $meta_key ) ) {
			$value_type = ht()->get_method_value( 'value_type' );

			if ( 'up_down' == $value_type ) {
				$key    = $meta_type . '_' . $meta_key;
				$change = 1;

				if ( is_user_logged_in() ) {
					$values = get_user_meta( get_current_user_id(), $key, true );
				} else {
					$values = $_SESSION[ $key ] ?? '';
				}

				$values = maybe_unserialize( $values );

				if ( ( is_array( $values ) && in_array( $object_id, $values ) ) || ( is_numeric( $values ) && $object_id == $values ) ) {
					$change = - 1;
				}

				$meta_value = ht()->get_method_value( 'meta_value' );

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
				$meta_value = ht()->get_method_value( 'meta_value' );

				if ( empty( $meta_value ) ) {
					$meta_value = get_metadata( $meta_type, $object_id, $meta_key, true );
				}

				if ( ! is_array( $meta_value ) ) {
					$meta_value = array();
				}

				$change_value = ht()->get_method_value( 'change_value' );

				if ( empty( $change_value ) ) {
					$change_value = ht()->get_method_value( 'change_id' );
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
		$object_id = ht()->get_method_value( 'object_id' );

		if ( ht()->is_positive_number( $object_id ) ) {
			$meta_key = ht()->get_method_value( 'meta_key' );

			if ( ! empty( $meta_key ) ) {
				$meta_type = ht()->get_method_value( 'meta_type' );

				if ( empty( $meta_type ) ) {
					$meta_type = 'post';
				}

				if ( isset( $_POST['meta_value'] ) ) {
					$meta_value = $_POST['meta_value'];
				} else {
					$meta_value = $_POST['meta_change'] ?? 1;
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
	$post_id = $_GET['post_id'] ?? '';

	if ( ht()->is_positive_number( $post_id ) ) {
		$event = $_GET['event'] ?? '';

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
	$post_id = ht()->get_method_value( 'post_id' );

	if ( ht()->is_positive_number( $post_id ) ) {
		$post_name = ht()->get_method_value( 'post_name' );

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
	$screen_width = $_GET['screen_width'] ?? '';

	if ( ht()->is_positive_number( $screen_width ) ) {
		$client_info = ht_util()->get_client_info( true );

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
	$nonce = $_POST['nonce'] ?? '';

	if ( hocwp_theme()->verify_nonce( $nonce ) ) {
		$meta_type  = ht()->get_method_value( 'meta_type' );
		$meta_key   = ht()->get_method_value( 'meta_key' );
		$meta_value = absint( ht()->get_method_value( 'meta_value' ) );
		$object_id  = ht()->get_method_value( 'object_id' );

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

require_once( dirname( __FILE__ ) . '/ajax-administration-tools.php' );