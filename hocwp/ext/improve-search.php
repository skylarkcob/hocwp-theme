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

function hocwp_ext_improve_search_pre_get_posts_action( $query ) {
	if ( $query instanceof WP_Query && $query->is_main_query() ) {
		if ( $query->is_search() || ( is_admin() && isset( $_GET['s'] ) ) ) {
			remove_action( 'pre_get_posts', 'hocwp_ext_improve_search_pre_get_posts_action', 99 );

			$tmp = $query;
			$tmp->get_posts();

			if ( ! $tmp->have_posts() ) {
				$search = isset( $query->query_vars['s'] ) ? $query->query_vars['s'] : '';

				if ( ! empty( $search ) ) {
					global $wpdb;

					$parts = explode( ' ', $search );

					if ( 1 < count( $parts ) ) {
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

						$search = hocwp_ext_improve_search_build_term_query( $search, 2 );

						$sql .= $search;

						$ppp = isset( $query->query_vars['posts_per_page'] ) ? $query->query_vars['posts_per_page'] : HT_Util()->get_posts_per_page();

						$sql .= " LIMIT 0, " . $ppp;

						$result = $wpdb->get_col( $sql );

						if ( ! HT()->array_has_value( $result ) ) {
							$sql = $save;

							$search = hocwp_ext_improve_search_build_term_query( $search, 1 );

							$sql .= $search;

							$sql .= " LIMIT 0, " . $ppp;

							$result = $wpdb->get_col( $sql );

							if ( ! HT()->array_has_value( $result ) ) {
								$sql = $save;

								$search = hocwp_ext_improve_search_build_term_query( $search, 2, 'post_content' );

								$sql .= $search;

								$sql .= " LIMIT 0, " . $ppp;

								$result = $wpdb->get_col( $sql );
							}
						}

						if ( HT()->array_has_value( $result ) ) {
							$query->set( 'post__in', $result );
							$query->set( 's', '' );
						}

						unset( $sql, $post_type, $post_types, $type, $save, $ppp, $result );
					}

					unset( $parts );
				}

				unset( $search );
			}

			unset( $tmp );
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