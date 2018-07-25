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
			$post = get_post( get_the_ID() );
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

	public function thumbnail( $size = 'thumbnail', $attr = '' ) {
		hocwp_theme_post_thumbnail( $size, $attr );
	}
}