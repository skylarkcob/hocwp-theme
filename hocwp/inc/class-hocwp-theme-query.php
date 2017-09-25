<?php

class HOCWP_Theme_Query {
	public static function pages_by_template( $template_name, $args = array() ) {
		$args['meta_key']   = '_wp_page_template';
		$args['meta_value'] = $template_name;

		return get_pages( $args );
	}

	public static function page_by_template( $template_name, $args = array() ) {
		$pages = self::pages_by_template( $template_name, $args );

		return array_shift( $pages );
	}

	public static function related( $args = array() ) {
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
}