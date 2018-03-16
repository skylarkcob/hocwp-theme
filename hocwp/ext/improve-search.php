<?php
/*
 * Name: Improve Search
 * Description: Help search results on your site more accurate instead of using the default search engine of WordPress.
 */
function hocwp_theme_load_extension_improve_search() {
	$load = hocwp_theme_is_extension_active( __FILE__ );
	$load = apply_filters( 'hocwp_theme_load_extension_improve_search', $load );

	return $load;
}

$load = hocwp_theme_load_extension_improve_search();

if ( ! $load ) {
	return;
}

function hocwp_ext_improve_search_build_term_query( $search, $chunk_size, $column_name = 'post_title' ) {
	$chunks = HT()->string_chunk( $search, 2 );

	$s = array_shift( $chunks );

	$search = "$column_name LIKE '%$s%'";

	foreach ( $chunks as $value ) {
		$search .= " OR $column_name LIKE '%$value%'";
	}

	$search = trim( $search );

	if ( 0 < count( $chunks ) ) {
		$search = '(' . $search . ')';
	}

	unset( $chunks, $s, $value );

	return $search;
}

function hocwp_ext_improve_search_query_post_ids( $search, $sql, $ppp, $chunk_size, $column_name = 'post_title' ) {
	global $wpdb;

	$search = hocwp_ext_improve_search_build_term_query( $search, $chunk_size, $column_name );

	$sql .= $search;

	$sql .= " LIMIT 0, " . $ppp;

	return $wpdb->get_col( $sql );
}

function hocwp_ext_improve_search_pre_get_posts_action( $query ) {
	if ( $query instanceof WP_Query && $query->is_main_query() ) {
		if ( $query->is_search() || ( is_admin() && isset( $_GET['s'] ) ) ) {
			$search = isset( $query->query_vars['s'] ) ? $query->query_vars['s'] : '';

			if ( ! empty( $search ) ) {
				global $wpdb;

				$ppp = isset( $query->query_vars['posts_per_page'] ) ? $query->query_vars['posts_per_page'] : HT_Util()->get_posts_per_page();

				$sql = "SELECT ID FROM";
				$sql .= " `$wpdb->posts` WHERE post_status = 'publish' AND ";

				$post_type  = isset( $query->query_vars['post_type'] ) ? $query->query_vars['post_type'] : 'post';
				$post_types = (array) $post_type;

				$post_type = array_shift( $post_types );

				$type = "post_type = '$post_type'";

				foreach ( $post_types as $post_type ) {
					$type .= " OR post_type = '$post_type'";
				}

				$type = trim( $type );

				if ( 0 < count( $post_types ) ) {
					$type = '(' . $type . ')';
				}

				$post_type = $type;

				$sql .= $post_type;
				$sql .= ' AND ';

				$save = $sql;

				$slug = sanitize_title( $search );
				$sql .= "post_name = '$slug'";
				$post_ids = $wpdb->get_col( $sql );

				if ( ! HT()->array_has_value( $post_ids ) ) {
					$sql = $save;
					$sql .= "post_name LIKE '%$slug%'";
					$post_ids = $wpdb->get_col( $sql );

					if ( ! HT()->array_has_value( $post_ids ) ) {
						$parts = explode( ' ', $search );

						if ( 1 < count( $parts ) ) {
							$post_ids = hocwp_ext_improve_search_query_post_ids( $search, $save, $ppp, 2 );

							if ( ! HT()->array_has_value( $post_ids ) ) {
								$post_ids = hocwp_ext_improve_search_query_post_ids( $search, $save, $ppp, 1 );

								if ( ! HT()->array_has_value( $post_ids ) ) {
									$post_ids = hocwp_ext_improve_search_query_post_ids( $search, $save, $ppp, 2, 'post_content' );
								}
							}
						}

						unset( $parts );
					}

					$args = $query->query_vars;

					$args['fields'] = 'ids';

					remove_action( 'pre_get_posts', 'hocwp_ext_improve_search_pre_get_posts_action', 99 );

					$tmp = new WP_Query( $args );

					if ( $tmp->have_posts() ) {
						$post_ids += $tmp->posts;
						$query->set( 'orderby', 'post__in' );
					}
				}

				if ( HT()->array_has_value( $post_ids ) ) {
					$query->set( 'post__in', $post_ids );
					$query->set( 's', '' );
				}

				unset( $ppp, $post_ids, $sql, $tmp, $post_type, $post_types, $type, $save, $slug );
			}

			unset( $search );
		}
	}
}

add_action( 'pre_get_posts', 'hocwp_ext_improve_search_pre_get_posts_action', 99 );

function hocwp_ext_improve_search_get_search_query_filter( $term ) {
	if ( empty( $term ) ) {
		$term = HT()->get_method_value( 's', 'request' );
	}

	return $term;
}

add_filter( 'get_search_query', 'hocwp_ext_improve_search_get_search_query_filter', 99 );

unset( $load );