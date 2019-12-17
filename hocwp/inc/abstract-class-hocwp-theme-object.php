<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class Abstract_HOCWP_Theme_Object {
	public $meta_keys = array();

	/*
	 * Add meta key string.
	 *
	 * @param string $key The meta key string.
	 */
	public function add_meta_key( $key ) {
		if ( ! in_array( $key, $this->meta_keys ) ) {
			$this->meta_keys[] = $key;
		}
	}

	/*
	 * Set meta keys array.
	 *
	 * @param array $keys The meta keys array.
	 */
	public function set_meta_keys( $keys ) {
		$this->meta_keys = $keys;
	}

	abstract function get_meta( $key, $single = true );

	abstract function get_meta_keys();

	abstract function get_id();

	abstract function get();

	abstract function set( $object );

	abstract function is( $post_type_or_taxonomy = null );
}