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

	public static function pages_by_template( $template_name, $args = array() ) {
		$args['meta_key']   = '_wp_page_template';
		$args['meta_value'] = $template_name;

		return get_pages( $args );
	}

	public static function page_by_template( $template_name, $args = array() ) {
		$pages = self::pages_by_template( $template_name, $args );

		return array_shift( $pages );
	}

	public function pages( $args = array() ) {
		return get_pages( $args );
	}

	public static function related( $args = array(), $type = 'post' ) {
		return self::related_posts( $args );
	}

	public static function related_posts( $args = array() ) {
		$post_id = isset( $args['post_id'] ) ? $args['post_id'] : get_the_ID();

		$obj = get_post( $post_id );

		$defaults = array(
			'post__not_in'  => array( $post_id ),
			'post_type'     => $obj->post_type,
			'orderby'       => 'rand',
			'related_posts' => true
		);

		$args = wp_parse_args( $args, $defaults );

		$query = new WP_Query();

		$taxonomy = isset( $args['taxonomy'] ) ? $args['taxonomy'] : '';
		unset( $args['taxonomy'] );

		if ( ! empty( $taxonomy ) ) {
			if ( ! is_array( $taxonomy ) ) {
				$taxonomy = array( $taxonomy );
			}

			$tax_query = isset( $args['tax_query'] ) ? $args['tax_query'] : '';

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
			return $query;
		}

		$term_relation = array();

		$taxs = get_object_taxonomies( $obj );

		if ( HOCWP_Theme::array_has_value( $taxs ) ) {
			$tax_query = isset( $args['tax_query'] ) ? $args['tax_query'] : array();

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

				if ( $taxonomy instanceof WP_Taxonomy ) {
					if ( ! $taxonomy->hierarchical ) {
						$ids = wp_get_post_terms( $post_id, $tax, array( 'fields' => 'ids' ) );

						if ( HOCWP_Theme::array_has_value( $ids ) ) {
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

			if ( ! $query->have_posts() ) {
				foreach ( $taxs as $tax ) {
					$ids = wp_get_post_terms( $post_id, $tax, array( 'fields' => 'ids' ) );

					if ( HOCWP_Theme::array_has_value( $ids ) ) {
						$tax_item['taxonomy'] = $tax;

						$tax_item['terms'] = $ids;

						$new[] = $tax_item;

						$term_relation[ $tax ] = $ids;
					}
				}

				if ( HT()->array_has_value( $new ) ) {
					$new['relation'] = 'or';

					$tax_query[] = $new;
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

		return $query;
	}

	public static function terms( $args = array() ) {
		$defaults = array(
			'hide_empty' => false
		);
		if ( ! is_array( $args ) && is_string( $args ) ) {
			$args = array( 'taxonomy' => $args );
		}
		$args  = wp_parse_args( $args, $defaults );
		$query = new WP_Term_Query( $args );

		return $query->get_terms();
	}

	public static function posts_by_meta( $meta_key, $meta_value, $args = array() ) {
		$defaults = array(
			'meta_key'   => $meta_key,
			'meta_value' => $meta_value
		);
		$args     = wp_parse_args( $args, $defaults );

		return new WP_Query( $args );
	}

	public function get_post_by_column( $column_name, $column_value, $output = 'OBJECT', $args = array() ) {
		global $wpdb;
		$post_types = isset( $args['post_type'] ) ? $args['post_type'] : '';
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
		HT()->debug( $sql );

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

	public static function posts_orderby_meta( $meta_key, $args = array() ) {
		$defaults = array(
			'meta_key' => $meta_key,
			'orderby'  => 'meta_value_num',
			'order'    => 'desc'
		);
		$args     = wp_parse_args( $args, $defaults );

		return new WP_Query( $args );
	}

	public static function get_top_commenters( $number = 10, $interval = 'all' ) {
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
		$keys = $wpdb->get_col( $sql );

		return $keys;
	}

	public function add_tax_query_item( $tax_item, &$args ) {
		if ( is_array( $args ) ) {
			if ( ! isset( $args['tax_query']['relation'] ) ) {
				$args['tax_query']['relation'] = 'OR';
			}
			if ( isset( $args['tax_query'] ) ) {
				array_push( $args['tax_query'], $tax_item );
			} else {
				$args['tax_query'] = array( $tax_item );
			}
		}

		return $args;
	}
}

function HT_Query() {
	return HOCWP_Theme_Query::instance();
}