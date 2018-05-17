<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_post_submitbox_misc_actions_action( $post ) {
	$post_types = HT_Util()->post_types_support_featured();

	if ( ! in_array( $post->post_type, $post_types ) || ! current_user_can( 'publish_posts' ) ) {
		return;
	}

	wp_nonce_field( 'hocwp_theme_post_submitbox', 'hocwp_theme_post_submitbox_nonce' );
	$type  = get_post_type_object( $post->post_type );
	$value = get_post_meta( $post->ID, 'featured', true );
	do_action( 'hocwp_theme_post_submitbox_meta_field', $post );
	?>
	<div class="misc-pub-section misc-pub-featured">
		<input type="checkbox" id="featured" name="featured" value="1" <?php checked( 1, $value ); ?>>
		<label
			for="featured"><?php printf( __( 'Make this %s as featured?', 'hocwp-theme' ), $type->labels->singular_name ); ?></label>
	</div>
	<?php
}

add_action( 'post_submitbox_misc_actions', 'hocwp_theme_post_submitbox_misc_actions_action' );

function hocwp_theme_save_post_action( $post_id ) {
	if ( ! in_array( get_post_type( $post_id ), HT_Util()->post_types_support_featured() ) ) {
		return;
	}

	if ( ! HOCWP_Theme_Utility::can_save_post( $post_id, 'hocwp_theme_post_submitbox', 'hocwp_theme_post_submitbox_nonce' ) ) {
		return;
	}

	if ( isset( $_POST['featured'] ) ) {
		update_post_meta( $post_id, 'featured', 1 );
	} else {
		update_post_meta( $post_id, 'featured', 0 );
	}

	do_action( 'hocwp_theme_post_submitbox_meta_field_save', $post_id );
}

add_action( 'save_post', 'hocwp_theme_save_post_action' );

function hocwp_theme_manage_posts_columns_filter( $columns ) {
	if ( current_user_can( 'publish_posts' ) ) {
		global $post_type;

		if ( in_array( $post_type, HT_Util()->post_types_support_featured() ) ) {
			if ( ! ( 'product' == $post_type && $GLOBALS['hocwp_theme']->is_wc_activated ) ) {
				$text = _x( 'Featured', 'manage posts columns', 'hocwp-theme' );

				$columns['featured'] = '<span class="vers comment-grey-bubble featured-star" title="' . $text . '"><span class="screen-reader-text">' . $text . '</span></span>';
			}
		}
	}

	return $columns;
}

add_filter( 'manage_posts_columns', 'hocwp_theme_manage_posts_columns_filter' );
add_filter( 'manage_page_posts_columns', 'hocwp_theme_manage_posts_columns_filter' );

function hocwp_theme_manage_sortable_columns_filter( $columns ) {
	global $post_type;

	if ( in_array( $post_type, HT_Util()->post_types_support_featured() ) ) {
		if ( ! ( 'product' == $post_type && $GLOBALS['hocwp_theme']->is_wc_activated ) ) {
			$columns['featured'] = 'featured';
		}
	}

	return $columns;
}

function hocwp_theme_init_edit_sortable_columns() {
	$types = hocwp_theme_get_custom_post_types();
	add_filter( 'manage_edit-post_sortable_columns', 'hocwp_theme_manage_sortable_columns_filter', 10 );
	add_filter( 'manage_edit-page_sortable_columns', 'hocwp_theme_manage_sortable_columns_filter', 10 );
	$post_types = HT_Util()->post_types_support_featured();
	$types      = array_diff( $types, $post_types );

	foreach ( $types as $post_type ) {
		add_filter( 'manage_edit-' . $post_type . '_sortable_columns', 'hocwp_theme_manage_sortable_columns_filter', 10 );
	}
}

add_action( 'init', 'hocwp_theme_init_edit_sortable_columns' );

function hocwp_theme_manage_posts_custom_column_action( $column_name, $post_id ) {
	$obj       = get_post( $post_id );
	$post_type = $obj->post_type;

	if ( in_array( $post_type, HT_Util()->post_types_support_featured() ) ) {
		if ( ( ! ( 'product' == $post_type && $GLOBALS['hocwp_theme']->is_wc_activated ) ) ) {
			if ( 'featured' == $column_name ) {
				$value = get_post_meta( $post_id, 'featured', true );
				$value = absint( $value );
				$class = 'dashicons hocwp-theme-featured';

				if ( 1 == $value ) {
					$class .= ' active';
				}

				echo '<span class="' . $class . '" data-featured="' . $value . '" data-id="' . $post_id . '" data-ajax-button="1"></span>';
			}
		}
	}
}

add_action( 'manage_posts_custom_column', 'hocwp_theme_manage_posts_custom_column_action', 10, 2 );
add_action( 'manage_page_posts_custom_column', 'hocwp_theme_manage_posts_custom_column_action', 10, 2 );

function hocwp_theme_hocwp_theme_featured_post_ajax_callback() {
	$result = array(
		'success' => false
	);

	$post_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : '';

	if ( HOCWP_Theme::is_positive_number( $post_id ) ) {
		if ( in_array( get_post_type( $post_id ), HT_Util()->post_types_support_featured() ) ) {
			$featured = isset( $_POST['featured'] ) ? $_POST['featured'] : '';
			$featured = absint( $featured );

			if ( 1 == $featured ) {
				$featured = 0;
			} else {
				$featured = 1;
			}

			$update = update_post_meta( $post_id, 'featured', $featured );

			if ( $update ) {
				$result['success']  = true;
				$result['featured'] = $featured;
			}
		}
	}

	wp_send_json( $result );
}

add_action( 'wp_ajax_hocwp_theme_featured_post_ajax', 'hocwp_theme_hocwp_theme_featured_post_ajax_callback' );

function hocwp_theme_manage_column_pre_get_posts( $query ) {
	if ( $query instanceof WP_Query ) {
		$orderby = isset( $_GET['orderby'] ) ? $_GET['orderby'] : '';

		if ( 'featured' == $orderby ) {
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'meta_key', 'featured' );
		}
	}
}

add_action( 'pre_get_posts', 'hocwp_theme_manage_column_pre_get_posts' );