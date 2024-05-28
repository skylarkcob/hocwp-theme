<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

final class HOCWP_Theme_Query {
	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {
	}

	/**
	 * Get current query before loop. Usually used for custom query in page template.
	 *
	 * @return mixed|WP_Query
	 */
	public function get_current_query() {
		$query = HOCWP_Theme()->get_loop_data( 'query' );

		if ( ! is_object( $query ) || ! ( $query instanceof WP_Query ) ) {
			$query = $GLOBALS['wp_query'];
		}

		return $query;
	}

	public function pages_by_template( $template_name, $args = array() ) {
		$args['meta_key']   = '_wp_page_template';
		$args['meta_value'] = $template_name;

		return get_pages( $args );
	}

	public function page_by_template( $template_name, $args = array() ) {
		$pages = self::pages_by_template( $template_name, $args );

		return array_shift( $pages );
	}

	public function pages_by_custom_template( $file, $args = array() ) {
		$file = HT()->trim_string( $file, '.php', 'right' );

		return $this->pages_by_template( 'custom/page-templates/' . $file . '.php', $args );
	}

	public function page_by_custom_template( $file, $args = array() ) {
		$pages = self::pages_by_custom_template( $file, $args );

		return array_shift( $pages );
	}

	public function blog_page() {
		$blog = HT_Options()->get_tab( 'blog_page', '', 'reading' );

		if ( HT()->is_positive_number( $blog ) ) {
			return get_post( $blog );
		}

		return HT_Query()->page_by_template( 'custom/page-templates/blog.php' );
	}

	public function pages( $args = array() ) {
		return get_pages( $args );
	}

	public function related( $args = array(), $type = 'post' ) {
		if ( ! isset( $args['post_type'] ) ) {
			$args['post_type'] = $type;
		}

		return self::related_posts( $args );
	}

	public function by_post_format( $format, $args = array() ) {
		$tax_query = $args['tax_query'] ?? array();

		if ( ! is_array( $format ) ) {
			$format = array( $format );
		}

		$format = array_map( array( 'HOCWP_Theme_Sanitize', 'post_format' ), $format );
		$format = array_filter( $format );

		if ( HT()->array_has_value( $format ) ) {
			if ( HT()->array_has_value( $tax_query ) ) {
				$tax_query['relation'] = 'AND';
			}

			$formats = array(
				'relation' => 'OR',
				array(
					'taxonomy' => 'post_format',
					'field'    => 'slug',
					'terms'    => $format
				)
			);

			$tax_query[] = $formats;

			$args['tax_query'] = $tax_query;
		}

		return new WP_Query( $args );
	}

	public function same_category_posts( $args = array(), $hierarchical = true ) {
		$taxs = get_object_taxonomies( get_post_type() );

		$query = new WP_Query();

		if ( HT()->array_has_value( $taxs ) ) {
			if ( $hierarchical ) {
				foreach ( $taxs as $key => $tax ) {
					if ( ! is_taxonomy_hierarchical( $tax ) ) {
						unset( $taxs[ $key ] );
					}
				}
			}

			$terms = wp_get_object_terms( get_the_ID(), $taxs );

			if ( HT()->array_has_value( $terms ) ) {
				$has = false;

				$tax_query = $args['tax_query'] ?? '';

				if ( ! is_array( $tax_query ) ) {
					$tax_query = array();
				}

				foreach ( $terms as $term ) {
					$tax_query[] = array(
						'taxonomy' => $term->taxonomy,
						'field'    => 'term_id',
						'terms'    => array( $term->term_id )
					);

					$has = true;
				}

				if ( $has && HT()->array_has_value( $tax_query ) ) {
					$tax_query['relation'] = 'AND';

					$args['tax_query'] = $tax_query;

					$query = new WP_Query( $args );
				}
			}
		}

		return $query;
	}

	public function related_posts( $args = array() ) {
		$post_id = $args['post_id'] ?? get_the_ID();

		$obj = get_post( $post_id );

		$defaults = array(
			'post__not_in'  => array( $post_id ),
			'post_type'     => $obj->post_type,
			'orderby'       => 'rand',
			'related_posts' => true
		);

		$post_parent = $obj->post_parent;

		if ( HT()->is_positive_number( $post_parent ) ) {
			$defaults['post_parent'] = $post_parent;
		}

		$args = wp_parse_args( $args, $defaults );

		$tr_name = 'query_related_posts_' . md5( maybe_serialize( $args ) );

		if ( false === ( $query = get_transient( $tr_name ) ) ) {
			$query = new WP_Query();

			$taxonomy = $args['taxonomy'] ?? '';
			unset( $args['taxonomy'] );

			if ( ! empty( $taxonomy ) ) {
				if ( ! is_array( $taxonomy ) ) {
					$taxonomy = array( $taxonomy );
				}

				$tax_query = $args['tax_query'] ?? '';

				if ( ! is_array( $tax_query ) ) {
					$tax_query = array();
				}

				foreach ( $taxonomy as $tax ) {
					$term_ids = wp_get_object_terms( get_the_ID(), $tax, array( 'fields' => 'ids' ) );

					if ( HT()->array_has_value( $term_ids ) ) {
						$tax_query[] = array(
							'taxonomy' => $tax,
							'field'    => 'term_id',
							'terms'    => $term_ids
						);
					}
				}

				if ( ! isset( $tax_query['relation'] ) ) {
					$tax_query['relation'] = 'OR';
				}

				$args['tax_query'] = $tax_query;

				$query = new WP_Query( $args );
			}

			if ( $query->have_posts() ) {
				set_transient( $tr_name, $query, HOUR_IN_SECONDS );

				return $query;
			}

			$by_term = false;

			if ( isset( $args['cat'] ) && is_numeric( $args['cat'] ) ) {
				$by_term = true;
			} elseif ( isset( $args['category_name'] ) && ! empty( $args['category_name'] ) ) {
				$by_term = true;
			} elseif ( isset( $args['category__and'] ) && HT()->array_has_value( $args['category__and'] ) ) {
				$by_term = true;
			} elseif ( isset( $args['category__in'] ) && HT()->array_has_value( $args['category__in'] ) ) {
				$by_term = true;
			} elseif ( isset( $args['tag_id'] ) && is_numeric( $args['tag_id'] ) ) {
				$by_term = true;
			} elseif ( isset( $args['tag'] ) && ! empty( $args['tag'] ) ) {
				$by_term = true;
			} elseif ( isset( $args['tag__and'] ) && HT()->array_has_value( $args['tag__and'] ) ) {
				$by_term = true;
			} elseif ( isset( $args['tag__in'] ) && HT()->array_has_value( $args['tag__in'] ) ) {
				$by_term = true;
			} elseif ( isset( $args['tag_slug__and'] ) && HT()->array_has_value( $args['tag_slug__and'] ) ) {
				$by_term = true;
			} elseif ( isset( $args['tag_slug__in'] ) && HT()->array_has_value( $args['tag_slug__in'] ) ) {
				$by_term = true;
			} elseif ( isset( $args['tax_query'] ) && HT()->array_has_value( $args['tax_query'] ) ) {
				$by_term = true;
			}

			if ( $by_term ) {
				$query = new WP_Query( $args );
			}

			if ( $query->have_posts() ) {
				set_transient( $tr_name, $query, HOUR_IN_SECONDS );

				return $query;
			}

			$term_relation = array();

			$taxs = get_object_taxonomies( $obj );

			if ( HT()->array_has_value( $taxs ) ) {
				$tax_query = $args['tax_query'] ?? array();

				if ( ! is_array( $tax_query ) ) {
					$tax_query = array();
				}

				$new = array();

				$has_tag = false;

				$tax_item = array(
					'field'    => 'term_id',
					'operator' => 'IN'
				);

				foreach ( $taxs as $key => $tax ) {
					$taxonomy = get_taxonomy( $tax );

					if ( $taxonomy instanceof WP_Taxonomy && is_string( $tax ) ) {
						if ( ! $taxonomy->hierarchical ) {
							$ids = wp_get_post_terms( $post_id, $tax, array( 'fields' => 'ids' ) );

							if ( HT()->array_has_value( $ids ) ) {
								$tax_item['taxonomy'] = $tax;

								$tax_item['terms'] = $ids;

								$new[] = $tax_item;

								$has_tag = true;

								$term_relation[ $tax ] = $ids;
							}

							unset( $taxs[ $key ] );
						}
					} else {
						unset( $taxs[ $key ] );
					}
				}

				if ( $has_tag ) {
					$new['relation'] = 'or';

					$tax_query[] = $new;
				}

				if ( $has_tag ) {
					$args['tax_query'] = $tax_query;

					$query = new WP_Query( $args );
				}

				$missing = false;

				if ( $query->have_posts() ) {
					$ppp = $query->get( 'posts_per_page' );

					if ( ! is_numeric( $ppp ) ) {
						$ppp = HT_Util()->get_posts_per_page();
					}

					if ( $query->found_posts < ( $ppp / 2 ) ) {
						$missing = true;
					}
				}

				if ( ! $query->have_posts() || $missing ) {
					foreach ( $taxs as $tax ) {
						$ids = wp_get_post_terms( $post_id, $tax, array( 'fields' => 'ids' ) );

						if ( HT()->array_has_value( $ids ) && is_string( $tax ) ) {
							$tax_item['taxonomy'] = $tax;

							$tax_item['terms'] = $ids;

							$new[] = $tax_item;

							$term_relation[ $tax ] = $ids;
						}
					}

					if ( HT()->array_has_value( $new ) ) {
						$new['relation'] = 'or';

						$tax_query = $new;
					}

					if ( ! isset( $tax_query['relation'] ) ) {
						$tax_query['relation'] = 'or';
					}

					$args['tax_query'] = $tax_query;

					$query = new WP_Query( $args );
				}
			} else {
				$args['s'] = $obj->post_title;
				$query     = new WP_Query( $args );

				if ( ! $query->have_posts() ) {
					$parts = explode( ' ', $obj->post_title );

					while ( ! $query->have_posts() && count( $parts ) > 0 ) {
						$key       = array_shift( $parts );
						$args['s'] = $key;
						$query     = new WP_Query( $args );
					}
				}
			}

			if ( ! isset( $query->query_vars['term_relation'] ) ) {
				$query->query_vars['term_relation'] = $term_relation;
			}

			if ( $query->have_posts() ) {
				set_transient( $tr_name, $query, HOUR_IN_SECONDS );
			}
		}

		if ( ! $query->have_posts() ) {
			$default = $args['none_default'] ?? '';

			if ( $default ) {
				unset( $args['tax_query'], $args['meta_query'] );
				$query = new WP_Query( $args );
			}
		}

		return $query;
	}

	public function get_posts_by_menu_order( $menu_order, $args = array() ) {
		global $wpdb;

		$join    = $args['join'] ?? '';
		$where   = $args['where'] ?? '';
		$groupby = $args['groupby'] ?? '';
		$orderby = $args['orderby'] ?? '';
		$limit   = $args['limit'] ?? '';

		$query = "SELECT ID FROM ";
		$query .= $wpdb->posts;

		if ( ! empty( $join ) ) {
			$query .= " $join";
		}

		$query .= " WHERE menu_order = " . $menu_order;

		if ( ! empty( $where ) ) {
			$query .= " $where";
		}

		if ( ! empty( $groupby ) ) {
			$query .= " $groupby";
		}

		if ( ! empty( $orderby ) ) {
			$query .= " $orderby";
		}

		if ( ! empty( $limit ) ) {
			$query .= " $limit";
		}

		$columns = $wpdb->get_col( $query );

		$output = $args['output'] ?? object;

		if ( HT()->array_has_value( $columns ) ) {
			if ( object == $output ) {
				$columns = array_map( 'get_post', $columns );
			}
		}

		return $columns;
	}

	public function terms( $args = array() ) {
		$defaults = array(
			'hide_empty' => false
		);

		if ( is_string( $args ) ) {
			$args = array( 'taxonomy' => $args );
		}

		$args  = wp_parse_args( $args, $defaults );
		$query = new WP_Term_Query( $args );

		return $query->get_terms();
	}

	public function get_previous_post( $previous = true ) {
		if ( $previous && is_attachment() ) {
			$post = get_post( get_post()->post_parent );
		} else {
			$post = get_adjacent_post( false, '', $previous );
		}

		return $post;
	}

	public function featured_posts( $args = array() ) {
		return HT_Query()->posts_by_meta( 'featured', 1, $args );
	}

	public function set_orderby_popularity( &$query, $range = 'auto' ) {
		if ( $query instanceof WP_Query ) {
			if ( function_exists( 'wpp_get_mostpopular' ) ) {
				$ids = HT_Query()->popular_post_ids( $range );

				if ( HT()->array_has_value( $ids ) ) {
					$ppp = $query->get( 'posts_per_page' );

					if ( count( $ids ) > $ppp ) {
						$paged  = $query->get( 'paged' );
						$offset = ( $paged - 1 ) * $ppp;
						$ids    = array_slice( $ids, $offset, $ppp );
					}

					if ( HT()->array_has_value( $ids ) ) {
						$query->set( 'post__in', $ids );
						$query->set( 'orderby', 'post__in' );
					}
				}
			}
		}
	}

	public function popular_post_ids( $range = 'auto' ) {
		if ( class_exists( '\WordPressPopularPosts\Query' ) ) {
			if ( 'auto' == $range ) {
				$query = new \WordPressPopularPosts\Query();
				$lists = $query->get_posts();

				if ( ! HT()->array_has_value( $lists ) ) {
					$query = new \WordPressPopularPosts\Query( array( 'range' => 'last7days' ) );
					$lists = $query->get_posts();
				}

				if ( ! HT()->array_has_value( $lists ) ) {
					$query = new \WordPressPopularPosts\Query( array( 'range' => 'last30days' ) );
					$lists = $query->get_posts();
				}

				if ( ! HT()->array_has_value( $lists ) ) {
					$query = new \WordPressPopularPosts\Query( array( 'range' => 'all' ) );
					$lists = $query->get_posts();
				}
			} else {
				$query = new \WordPressPopularPosts\Query( array( 'range' => $range ) );
				$lists = $query->get_posts();
			}

			if ( HT()->array_has_value( $lists ) ) {
				$ids = array();

				foreach ( $lists as $obj ) {
					$ids[] = $obj->id;
				}

				return $ids;
			}
		}

		return array();
	}

	public function most_views( $args = array() ) {
		$defaults = array(
			'orderby'  => 'meta_value_num',
			'meta_key' => 'views'
		);

		$args = wp_parse_args( $args, $defaults );

		if ( function_exists( 'pvc_get_most_viewed_posts' ) ) {
			unset( $args['meta_key'] );

			$args['suppress_filters'] = false;

			$args['orderby'] = 'post_views';
			$args['order']   = 'DESC';
		} elseif ( class_exists( '\WordPressPopularPosts\Query' ) ) {
			$ids = $this->popular_post_ids();

			if ( HT()->array_has_value( $ids ) ) {
				$ppp = $args['posts_per_page'] ?? '';

				if ( empty( $ppp ) ) {
					$ppp = HT_Frontend()->get_posts_per_page( is_home() );
				}

				if ( $ppp < count( $ids ) ) {
					$paged = $args['paged'] ?? HT_Frontend()->get_paged();
					$ids   = array_slice( $ids, ( $paged - 1 ) * $ppp, $ppp );
				}

				$args['post__in'] = $ids;
				$args['orderby']  = 'post__in';
				unset( $args['meta_key'] );
			}
		}

		return new WP_Query( $args );
	}

	public function posts_by_term( $term, $args = array() ) {
		$item = array(
			'taxonomy' => $term->taxonomy,
			'terms'    => array( $term->term_id )
		);

		$this->add_tax_query_item( $item, $args );

		return new WP_Query( $args );
	}

	public function posts_by_meta( $meta_key, $meta_value, $args = array() ) {
		$defaults = array(
			'meta_key'   => $meta_key,
			'meta_value' => $meta_value
		);

		$args = wp_parse_args( $args, $defaults );

		$meta_query = $args['meta_query'] ?? '';

		if ( ! is_array( $meta_query ) ) {
			$meta_query = array();
		}

		$meta_item = array(
			'key'   => $meta_key,
			'value' => $meta_value
		);

		if ( is_numeric( $meta_value ) ) {
			$meta_item['type'] = 'NUMERIC';
		}

		$meta_query['relation'] = 'AND';

		$meta_query[] = $meta_item;

		$args['meta_query'] = $meta_query;

		return new WP_Query( $args );
	}

	public function get_post_by_column( $column_name, $column_value, $output = 'OBJECT', $args = array() ) {
		global $wpdb;
		$post_types = $args['post_type'] ?? '';
		$post_types = (array) $post_types;

		$output = strtoupper( $output );

		if ( is_string( $column_value ) ) {
			$column_value = "'" . $column_value . "'";
		}

		$sql = "SELECT ID FROM %s WHERE ";
		$sql = sprintf( $sql, $wpdb->posts );
		$sql .= "$column_name = %s";

		$sql = $wpdb->prepare( $sql, $column_value );

		$count_type = 0;

		foreach ( $post_types as $post_type ) {
			if ( empty( $post_type ) ) {
				continue;
			}

			if ( 0 == $count_type ) {
				$sql .= " AND post_type = '$post_type'";
			} else {
				$sql .= " OR post_type = '$post_type'";
			}

			$count_type ++;
		}

		$post_id = $wpdb->get_var( $sql );
		$result  = '';

		switch ( $output ) {
			case OBJECT:
				if ( HT()->is_positive_number( $post_id ) ) {
					$result = get_post( $post_id );
				}

				break;
			default:
				$result = $post_id;
		}

		return $result;
	}

	public function get_all_meta_values( $meta_key, $order = 'ASC' ) {
		global $wpdb;

		$sql     = "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '" . $meta_key . "' GROUP BY meta_value ORDER BY meta_value " . $order . ";";
		$results = $wpdb->get_col( $sql );
		HT()->unique_filter( $results );

		return $results;
	}

	public function posts_orderby_meta( $meta_key, $args = array() ) {
		$defaults = array(
			'meta_key' => $meta_key,
			'orderby'  => 'meta_value_num',
			'order'    => 'desc'
		);
		$args     = wp_parse_args( $args, $defaults );

		return new WP_Query( $args );
	}

	public function get_top_commenters( $number = 10, $interval = 'all' ) {
		global $wpdb;
		$sql = "SELECT COUNT(comment_author_email) AS comments_count, comment_author_email, comment_author, comment_author_url";
		$sql .= " FROM $wpdb->comments";
		$sql .= " WHERE comment_author_email != '' AND comment_type = '' AND comment_approved = 1";

		switch ( $interval ) {
			case 'daily':
			case 'today':
				$sql .= " AND DATE(comment_date) = curdate()";
				break;
			case 'weekly':
			case 'thisweek':
				$sql .= " AND YEAR(comment_date) = YEAR(curdate()) AND WEEK(comment_date) = WEEK(curdate())";
				break;
			case 'monthly':
			case 'thismonth':
				$sql .= " AND YEAR(comment_date) = YEAR(curdate()) AND MONTH(comment_date) = MONTH(curdate())";
				break;
			case 'yearly':
			case 'thisyear':
				$sql .= " AND YEAR(comment_date) = YEAR(curdate())";
				break;
		}

		$sql .= " GROUP BY comment_author_email ORDER BY comments_count DESC, comment_author ASC LIMIT $number";

		return $wpdb->get_results( $sql );
	}

	public function meta_keys( $search = '' ) {
		global $wpdb;

		$sql = "SELECT meta_key";
		$sql .= " FROM $wpdb->postmeta";
		$sql .= " WHERE meta_key like '%$search%'";
		$sql .= " GROUP BY meta_key";
		$sql .= " ORDER BY meta_key";

		return $wpdb->get_col( $sql );
	}

	public function not_meta_query_args( $key, $value = 1, $relation = 'OR' ) {
		return array(
			'relation' => $relation,
			array(
				'key'   => $key,
				'value' => ''
			),
			array(
				'key'     => $key,
				'compare' => 'NOT EXISTS'
			),
			array(
				'key'     => $key,
				'value'   => $value,
				'compare' => '!='
			)
		);
	}

	private function add_meta_or_tax_query_item( $item, &$args, $key = 'meta_query' ) {
		if ( is_array( $args ) ) {
			if ( ! isset( $args[ $key ] ) || ! is_array( $args[ $key ] ) ) {
				$args[ $key ] = array();
			}

			if ( ! isset( $args[ $key ]['relation'] ) ) {
				$args[ $key ]['relation'] = 'OR';
			}

			if ( isset( $args[ $key ] ) ) {
				$args[ $key ][] = $item;
			} else {
				$args[ $key ] = array( $item );
			}
		}
	}

	public function add_tax_query_item( $tax_item, &$args ) {
		$this->add_meta_or_tax_query_item( $tax_item, $args, 'tax_query' );

		return $args;
	}

	public function add_tax_query_item_to_array( &$tax_query, $taxonomy, $terms = null, $key = '' ) {
		if ( empty( $terms ) ) {
			$terms = $_GET[ $key ] ?? '';
		}

		if ( ! is_array( $terms ) && ! empty( $terms ) ) {
			$terms = (array) $terms;
		}

		if ( ! empty( $terms ) ) {
			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'terms'    => $terms
			);
		}
	}

	/**
	 * Auto update tax_query for a WP_Query.
	 *
	 * @param WP_Query $query Query object to adjust.
	 * @param array $taxs The list of taxonomy and form key, uses like that array( 'tax' => 'key' ).
	 * @param string $relation Tax query item relation.
	 *
	 * @return void
	 */
	public function update_tax_query( &$query, $taxs, $relation = 'AND' ) {
		if ( $query instanceof WP_Query ) {
			$tax_query = $query->get( 'tax_query' );

			if ( ! is_array( $tax_query ) ) {
				$tax_query = array();
			}

			$items = array();

			foreach ( $taxs as $taxonomy => $key ) {
				$this->add_tax_query_item_to_array( $items, $taxonomy, null, $key );
			}

			if ( ! empty( $items ) ) {
				$items['relation'] = $relation;

				$tax_query[] = $items;
				$query->set( 'tax_query', $tax_query );
			}
		}
	}

	public function add_meta_query_item( $item, &$args ) {
		$this->add_meta_or_tax_query_item( $item, $args );

		return $args;
	}

	public function add_meta_query_item_to_array( &$meta_query, $meta_key, $meta_value = null, $key = '' ) {
		if ( empty( $meta_value ) ) {
			$meta_value = $_GET[ $key ] ?? '';
		}

		if ( ! empty( $meta_value ) ) {
			$meta_query[] = array(
				'key'   => $meta_key,
				'value' => $meta_value
			);
		}
	}

	/**
	 * Auto update meta_query for a WP_Query.
	 *
	 * @param WP_Query $query Query object to adjust.
	 * @param array $params The list of meta_key and form key, uses like that array( 'meta_key' => 'form_name' ).
	 * @param string $relation Tax query item relation.
	 *
	 * @return void
	 */
	public function update_meta_query( &$query, $params, $relation = 'AND' ) {
		if ( $query instanceof WP_Query ) {
			$meta_query = $query->get( 'meta_query' );

			if ( ! is_array( $meta_query ) ) {
				$meta_query = array();
			}

			$items = array();

			foreach ( $params as $meta_key => $key ) {
				$this->add_meta_query_item_to_array( $items, $meta_key, null, $key );
			}

			if ( ! empty( $items ) ) {
				$items['relation'] = $relation;

				$meta_query[] = $items;
				$query->set( 'meta_query', $meta_query );
			}
		}
	}
}

function HT_Query() {
	return HOCWP_Theme_Query::instance();
}