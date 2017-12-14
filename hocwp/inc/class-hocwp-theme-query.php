<?php

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

	public static function related_posts( $args = array() ) {
		$post_id  = isset( $args['post_id'] ) ? $args['post_id'] : get_the_ID();
		$obj      = get_post( $post_id );
		$defaults = array(
			'post__not_in' => array( $post_id ),
			'post_type'    => $obj->post_type
		);
		$args     = wp_parse_args( $args, $defaults );
		$taxs     = get_object_taxonomies( $obj );
		if ( HOCWP_Theme::array_has_value( $taxs ) ) {
			$tax_query = isset( $args['tax_query'] ) ? $args['tax_query'] : array();
			$new       = array();
			foreach ( $taxs as $tax ) {
				$ids = wp_get_post_terms( $post_id, $tax, array( 'fields' => 'ids' ) );
				if ( HOCWP_Theme::array_has_value( $ids ) ) {
					$new[] = array(
						'taxonomy' => $tax,
						'field'    => 'id',
						'terms'    => $ids
					);
				}
			}
			shuffle( $new );
			$new['relation']   = 'or';
			$tax_query[]       = $new;
			$args['tax_query'] = $tax_query;
		}

		return new WP_Query( $args );
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

	public static function posts_orderby_meta( $meta_key, $args = array() ) {
		$defaults = array(
			'meta_key' => $meta_key,
			'orderby'  => 'meta_value_num',
			'order'    => 'desc'
		);
		$args     = wp_parse_args( $args, $defaults );

		return new WP_Query( $args );
	}

	public static function get_top_commenters( $number = 10 ) {
		global $wpdb;
		$sql = "SELECT COUNT(comment_author_email) AS comments_count, comment_author_email, comment_author, comment_author_url";
		$sql .= " FROM $wpdb->comments";
		$sql .= " WHERE comment_author_email != '' AND comment_type = '' AND comment_approved = 1";
		$sql .= " GROUP BY comment_author_email ORDER BY comments_count DESC, comment_author ASC LIMIT $number";

		return $wpdb->get_results( $sql );
	}

	public function meta_keys( $search = '' ) {
		global $wpdb;
		$sql  = "SELECT meta_key";
		$sql  .= " FROM $wpdb->postmeta";
		$sql  .= " WHERE meta_key like '%$search%'";
		$sql  .= " GROUP BY meta_key";
		$sql  .= " ORDER BY meta_key";
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