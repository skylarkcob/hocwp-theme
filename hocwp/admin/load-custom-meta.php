<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Generate metas list for each post type or taxonomy.
 *
 * @param array $object_names The post type names or taxonomy names array.
 * @param array $metas The meta fields array contains field and object_name;
 *
 * @return array The list meta fields to be added for each post type or taxonomy.
 */
function hocwp_theme_meta_get_object_metas_list( $object_names, $metas ) {
	$lists = array();

	if ( HT()->array_has_value( $object_names ) ) {
		foreach ( $object_names as $name ) {
			foreach ( $metas as $key => $data ) {
				if ( is_array( $data ) && isset( $data['field'] ) && HT()->array_has_value( $data['field'] ) ) {
					$object_name = isset( $data['object_name'] ) ? $data['object_name'] : '';

					if ( ! is_array( $object_name ) ) {
						$object_name = array( $object_name );
					}

					if ( in_array( $name, $object_name ) ) {
						if ( ! isset( $lists[ $name ] ) ) {
							$lists[ $name ] = array();
						}

						$lists[ $name ][] = $data['field'];
					}
				}
			}
		}
	}

	return $lists;
}

/**
 * Add custom meta fields for post. Stop edit or change this function, the meta fields auto added if you use
 * meta configuration in custom functions file.
 */
function hocwp_theme_add_post_meta_from_configuration() {
	global $hocwp_theme_metas;

	$metas = $hocwp_theme_metas->get();

	if ( HT()->array_has_value( $metas ) && isset( $metas['post_type'] ) && HT()->array_has_value( $metas['post_type'] ) ) {
		$lists = hocwp_theme_meta_get_object_metas_list( $hocwp_theme_metas->post_types, $metas['post_type'] );

		if ( HT()->array_has_value( $lists ) ) {
			foreach ( $lists as $type => $fields ) {
				$meta = new HOCWP_Theme_Meta_Post();
				$meta->add_post_type( $type );
				$meta->form_table = true;
				$meta->set_fields( $fields );
			}
		}
	}
}

add_action( 'load-post.php', 'hocwp_theme_add_post_meta_from_configuration' );
add_action( 'load-post-new.php', 'hocwp_theme_add_post_meta_from_configuration' );
add_action( 'load-edit.php', 'hocwp_theme_add_post_meta_from_configuration' );

/**
 * Add custom meta fields for term. Stop edit or change this function, the meta fields auto added if you use
 * meta configuration in custom functions file.
 */
function hocwp_theme_add_term_meta_from_configuration() {
	global $hocwp_theme_metas;

	$metas = $hocwp_theme_metas->get();

	if ( HT()->array_has_value( $metas ) && isset( $metas['taxonomy'] ) && HT()->array_has_value( $metas['taxonomy'] ) ) {
		$lists = hocwp_theme_meta_get_object_metas_list( $hocwp_theme_metas->taxonomies, $metas['taxonomy'] );

		if ( HT()->array_has_value( $lists ) ) {
			foreach ( $lists as $tax => $fields ) {
				$meta = new HOCWP_Theme_Meta_Term();
				$meta->add_taxonomy( $tax );
				$meta->set_fields( $fields );
			}
		}
	}
}

add_action( 'load-edit-tags.php', 'hocwp_theme_add_term_meta_from_configuration' );