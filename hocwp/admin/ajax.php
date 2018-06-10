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
		$data   = array(
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
		$meta_type = HT()->get_method_value( 'meta_type' );
		$object_id = HT()->get_method_value( 'object_id' );
		$meta_key  = HT()->get_method_value( 'meta_key' );

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

				$meta_value = HT()->get_method_value( 'meta_value', 'post', get_metadata( $meta_type, $object_id, $meta_key ) );
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