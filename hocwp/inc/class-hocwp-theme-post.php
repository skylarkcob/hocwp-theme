<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Post {
	public $post;

	public function __construct( $post = null ) {
		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		} elseif ( ! ( $post instanceof WP_Post ) ) {
			$post = HT_Util()->return_post( $post );
		}

		if ( $post instanceof WP_Post ) {
			$this->post = $post;
		}
	}

	public function get_id() {
		return $this->post->ID;
	}

	public function get() {
		return $this->post;
	}

	public function set( $post ) {
		$this->post = $post;
	}

	public function get_meta( $key, $single = true ) {
		return get_post_meta( $this->post->ID, $key, $single );
	}

	public function get_terms( $taxonomy = 'post_tag', $args = array() ) {
		return wp_get_object_terms( $this->get_id(), $taxonomy, $args );
	}

	public function get_ancestor_terms( $taxonomy = 'category', $output = OBJECT ) {
		$result = null;

		if ( is_taxonomy_hierarchical( $taxonomy ) && has_term( '', $taxonomy, $this->post->ID ) ) {
			$result = get_ancestors( $this->post->ID, $taxonomy, 'taxonomy' );

			if ( ! HT()->array_has_value( $result ) ) {
				$result = wp_get_post_terms( $this->post->ID, $taxonomy, array(
					'orderby' => 'parent',
					'fields'  => 'ids'
				) );
			}
		}

		if ( HT()->array_has_value( $result ) && OBJECT == $output ) {
			foreach ( $result as $key => $term_id ) {
				$result[ $key ] = get_term( $term_id, $taxonomy );
			}
		}

		return $result;
	}

	public function thumbnail( $size = 'thumbnail', $attr = '' ) {
		hocwp_theme_post_thumbnail( $size, $attr );
	}

	public function the_date( $format = '' ) {
		echo get_the_date( $format );
	}

	public function get_the_excerpt( $length = null, $more = null ) {
		if ( ! is_numeric( $length ) ) {
			$length = HT_Options()->get_tab( 'excerpt_length', '', 'reading' );

			if ( ! is_numeric( $length ) ) {
				$length = 55;
			}

			$length = apply_filters( 'excerpt_length', $length );
		}

		$length = intval( $length );

		$excerpt = $this->post->post_excerpt;

		if ( empty( $excerpt ) ) {
			global $post;
			$tmp = $post;

			$post = $this->post;
			setup_postdata( $post );

			$excerpt = get_the_content( '' );

			$excerpt = strip_shortcodes( $excerpt );

			$excerpt = apply_filters( 'the_content', $excerpt );
			$excerpt = str_replace( ']]>', ']]&gt;', $excerpt );

			wp_reset_postdata();

			$post = $tmp;
		}

		if ( null == $more ) {
			$more = '&hellip;';
		}

		$excerpt = wp_trim_words( $excerpt, $length, $more );

		return $excerpt;
	}

	public function the_excerpt( $length = null, $more = null ) {
		echo apply_filters( 'the_excerpt', $this->get_the_excerpt( $length, $more ) );
	}
}