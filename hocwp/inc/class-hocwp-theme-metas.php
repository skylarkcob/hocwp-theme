<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Metas {
	public $metas;
	public $taxonomies;
	public $post_types;

	public function __construct() {
		$this->taxonomies = array();
		$this->post_types = array();
	}

	/**
	 * Add meta field to list metas array.
	 *
	 * @param mixed|HOCWP_Theme_Meta_Field|array $field The meta field object or array.
	 * @param string|array $tax_or_type_name The taxonomy name or post type name or array of names.
	 * @param string $type Determine if type is post_type or taxonomy.
	 *
	 * @return array The array contains 0 is meta type, 1 is meta type name, 2 is meta field array.
	 */
	public function add( $field, $tax_or_type_name, $type = 'post_type' ) {
		if ( ! is_array( $this->metas ) ) {
			$this->metas = array(
				'taxonomy'  => array(),
				'post_type' => array()
			);
		}

		if ( $field instanceof HOCWP_Theme_Meta_Field ) {
			$field = $field->generate();
		}

		if ( is_array( $field ) && isset( $field['id'] ) && ! empty( $field['id'] ) ) {
			$this->metas[ $type ][ $field['id'] ] = array(
				'field'       => $field,
				'object_name' => $tax_or_type_name
			);

			if ( 'post_type' == $type ) {
				if ( ! in_array( $tax_or_type_name, $this->post_types ) ) {
					$this->post_types[] = $tax_or_type_name;
				}
			} elseif ( 'taxonomy' == $type ) {
				if ( ! in_array( $tax_or_type_name, $this->taxonomies ) ) {
					$this->taxonomies[] = $tax_or_type_name;
				}
			}
		}

		return array( $type, $tax_or_type_name, $field );
	}

	public function set( $metas ) {
		if ( HT()->array_has_value( $metas ) ) {
			$this->metas = $metas;
		}
	}

	public function get() {
		return $this->metas;
	}
}