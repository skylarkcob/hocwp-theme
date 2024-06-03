<?php
/*
 * Name: Improve Search
 * Description: Help search results on your site more accurate instead of using the default search engine of WordPress.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'hocwp_theme_load_extension_improve_search' ) ) {
	function hocwp_theme_load_extension_improve_search() {
		$load = HT_extension()->is_active( __FILE__ );

		return apply_filters( 'hocwp_theme_load_extension_improve_search', $load );
	}
}

$load = hocwp_theme_load_extension_improve_search();

if ( ! $load ) {
	return;
}

if ( ! class_exists( 'HOCWP_Ext_Improve_Search' ) ) {
	final class HOCWP_Ext_Improve_Search extends HOCWP_Theme_Extension {
		protected static $instance;

		public static function get_instance() {
			if ( ! ( self::$instance instanceof self ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public $posts_per_page;
		public $paged;
		public $search_term;

		public function __construct() {
			parent::__construct( __FILE__ );

			add_filter( 'get_search_query', array( $this, 'get_search_query_filter' ), 99 );
			add_action( 'pre_get_posts', array( $this, 'pre_get_posts_action' ), 99 );
		}

		public function get_search_query_filter( $term ) {
			if ( empty( $term ) ) {
				$term = HT()->get_method_value( 's', 'request' );

				if ( empty( $this->search_term ) ) {
					$this->search_term = $term;
				}
			}

			return trim( $term );
		}

		public function query( $sql, $ppp = false ) {
			global $wpdb;

			$sql = apply_filters( 'hocwp_theme_custom_search_sql_query', $sql, $this );

			if ( empty( $ppp ) ) {
				$ppp = $this->posts_per_page;
			}

			return $wpdb->get_col( $sql );
		}

		public function pre_get_posts_action( $query ) {
			if ( $query instanceof WP_Query && ( is_search() || apply_filters( 'hocwp_theme_improve_search_query', false, $query ) ) ) {
				$search = $query->query_vars['s'] ?? '';

				if ( empty( $search ) ) {
					$search = $_GET['text'] ?? '';
				}

				$search = apply_filters( 'hocwp_theme_improve_search_search_term', $search, $query );

				if ( ! empty( $search ) ) {
					$search = trim( $search );

					if ( empty( $this->search_term ) ) {
						$this->search_term = $search;
					}

					$tr_name = 'search_custom_sql_post_ids_' . md5( $this->search_term );

					if ( false === ( $post_ids = get_transient( $tr_name ) ) ) {
						global $wpdb;

						$ppp = $query->query_vars['posts_per_page'] ?? HT_Util()->get_posts_per_page();

						if ( empty( $this->posts_per_page ) ) {
							$this->posts_per_page = $ppp;
						}

						if ( empty( $this->paged ) ) {
							$this->paged = $query->get( 'paged' );

							if ( ! is_numeric( $this->paged ) || 0 == $this->paged ) {
								$this->paged = 1;
							}
						}

						$sql = "SELECT p.ID FROM $wpdb->posts p";
						$sql .= " LEFT JOIN $wpdb->postmeta pm ON p.ID = pm.post_id";
						$sql .= " WHERE 1 = 1 AND pm.meta_id is not NULL";
						$sql .= " AND p.post_status = 'publish' AND ";

						$post_type  = $query->query_vars['post_type'] ?? 'post';
						$post_types = (array) $post_type;

						$post_type = array_shift( $post_types );

						$type = "p.post_type = '$post_type'";

						foreach ( $post_types as $post_type ) {
							$type .= " OR p.post_type = '$post_type'";
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
						$sql  .= "p.post_name LIKE '%$search%'";

						$post_ids = $this->query( $sql );

						if ( ! HT()->array_has_value( $post_ids ) ) {
							$sql = $save;
							$sql .= "p.post_name LIKE '%$slug%'";

							$post_ids = $this->query( $sql );

							if ( ! HT()->array_has_value( $post_ids ) ) {
								$parts = explode( ' ', $search );

								if ( 1 < count( $parts ) ) {
									$search_columns = array(
										'p.post_title'   => array( 2, 1 ),
										'p.post_content' => 2,
										'p.post_excerpt' => 2
									);

									foreach ( $search_columns as $column => $chunks ) {
										if ( is_array( $chunks ) ) {
											foreach ( $chunks as $chunk ) {
												$post_ids = $this->query_post_ids( $search, $save, $ppp, $chunk, $column );

												if ( ! empty( $post_ids ) ) {
													break;
												}
											}
										} else {
											$post_ids = $this->query_post_ids( $search, $save, $ppp, $chunks, $column );
										}

										if ( ! empty( $post_ids ) ) {
											break;
										}
									}
								}

								unset( $parts );
							}

							if ( ! HT()->array_has_value( $post_ids ) ) {
								$sql = $save;
								$sql .= "pm.meta_value LIKE '%$search%' AND p.ID = pm.post_id";

								$post_ids = $this->query( $sql );
							}

							if ( ! HT()->array_has_value( $post_ids ) ) {
								$args = $query->query_vars;

								$args['fields'] = 'ids';

								remove_action( 'pre_get_posts', array( $this, 'pre_get_posts_action' ), 99 );

								$tmp = new WP_Query( $args );

								if ( ! $tmp->have_posts() ) {
									$tax_query = $args['tax_query'] ?? '';

									if ( ! is_array( $tax_query ) ) {
										$tax_query = array();
									}

									if ( ! isset( $tax_query['relation'] ) ) {
										$tax_query['relation'] = 'OR';
									}

									$taxonomies = get_taxonomies( array( 'public' => true ) );

									foreach ( $taxonomies as $tax_name ) {
										$tax_query[] = array(
											'taxonomy' => $tax_name,
											'field'    => 'name',
											'terms'    => $search
										);
									}

									$args['tax_query'] = $tax_query;

									unset( $taxonomies, $tax_name, $tax_query, $args['s'] );

									$tmp = new WP_Query( $args );
								}

								if ( $tmp->have_posts() ) {
									$post_ids += $tmp->posts;
								}
							}
						}

						HT()->unique_filter( $post_ids );

						if ( HT()->array_has_value( $post_ids ) ) {
							set_transient( $tr_name, $post_ids, 6 * HOUR_IN_SECONDS );
						}
					}

					if ( HT()->array_has_value( $post_ids ) ) {
						$query->set( 'post__in', $post_ids );
						unset( $query->query_vars['s'] );
					}

					unset( $ppp, $post_ids, $sql, $tmp, $post_type, $post_types, $type, $save, $slug );
				}

				unset( $search );
			}
		}

		public function sanitize_search_word( &$words ) {
			foreach ( $words as $index => $word ) {
				if ( is_numeric( $word ) || empty( $word ) ) {
					unset( $words[ $index ] );
				}
			}
		}

		private function build_term_query( $search, $chunk_size = 2, $column_name = 'post_title' ) {
			$chunks = HT()->string_chunk( $search, $chunk_size );
			$this->sanitize_search_word( $chunks );

			if ( empty( $chunks ) ) {
				return $search;
			}

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

		private function query_post_ids( $search, $sql, $ppp, $chunk_size, $column_name = 'post_title' ) {
			$search = $this->build_term_query( $search, $chunk_size, $column_name );

			$sql .= $search;

			return $this->query( $sql, $ppp );
		}
	}
}

unset( $load );

if ( ! function_exists( 'HTE_Improve_Search' ) ) {
	function HTE_Improve_Search() {
		return HOCWP_Ext_Improve_Search::get_instance();
	}
}

if ( ! function_exists( 'hocwp_ext_load_improve_search' ) ) {
	function hocwp_ext_load_improve_search() {
		HTE_Improve_Search();
	}
}

add_action( 'hocwp_theme_setup_after', 'hocwp_ext_load_improve_search' );