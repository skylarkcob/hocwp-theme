<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Term extends Abstract_HOCWP_Theme_Object {
	public $term;

	public function __construct( $term = null, $taxonomy = '' ) {
		if ( is_numeric( $term ) ) {
			$term = get_term( $term, $taxonomy );
		} elseif ( ! ( $term instanceof WP_Term ) ) {
			$term = HT_Util()->return_term( $term, $taxonomy );
		}

		$this->set( $term );
	}

	/*
	 * Get list array meta keys.
	 *
	 * @return array The meta keys array.
	 */
	public function get_meta_keys() {
		$this->meta_keys = apply_filters( 'hocwp_theme_term_meta_keys', $this->meta_keys, $this );

		return $this->meta_keys;
	}

	/**
	 * Get term id.
	 *
	 * @return int Term id.
	 */
	public function get_id() {
		return $this->term->term_id;
	}

	/**
	 * Get term object.
	 *
	 * @return WP_Term Term object.
	 */
	public function get() {
		return $this->term;
	}

	/**
	 * Set term object.
	 *
	 * @param WP_Term $term The term object.
	 */
	public function set( $term ) {
		if ( $term instanceof WP_Term ) {
			$this->term = $term;

			// Set meta keys as term object properties.
			if ( HT()->array_has_value( $this->get_meta_keys() ) ) {
				foreach ( $this->get_meta_keys() as $key ) {
					$this->term->{$key} = $this->get_meta( $key );
				}
			}
		}
	}

	/**
	 * Check if is a term.
	 *
	 * @param null $taxonomy The taxonomy string name.
	 *
	 * @return bool True if is term else false.
	 */
	public function is( $taxonomy = null ) {
		$result = ( $this->get() instanceof WP_Term );

		if ( $result && ! empty( $taxonomy ) ) {
			$result = ( $taxonomy == $this->get()->taxonomy );
		}

		return $result;
	}

	/**
	 * Get term meta value.
	 *
	 * @param string $key The meta key.
	 * @param bool|true $single Return single item or array items.
	 *
	 * @return mixed The meta value.
	 */
	public function get_meta( $key, $single = true ) {
		return get_term_meta( $this->get_id(), $key, $single );
	}
}