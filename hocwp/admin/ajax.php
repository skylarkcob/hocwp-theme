<?php
if ( ! HOCWP_THEME_DOING_AJAX ) {
	return;
}

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

function hocwp_theme_update_meta_ajax_callback() {
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

add_action( 'wp_ajax_hocwp_theme_update_meta', 'hocwp_theme_update_meta_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_theme_update_meta', 'hocwp_theme_update_meta_ajax_callback' );

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
	}

	if ( $updated ) {
		$data = array(
			'meta_value' => $meta_value
		);

		wp_send_json_success( $data );
	}

	wp_send_json_error();
}

add_action( 'wp_ajax_hocwp_theme_boolean_meta_ajax', 'hocwp_theme_boolean_meta_ajax_callback' );