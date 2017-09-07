<?php

final class HOCWP_Theme_Term_Meta extends HOCWP_Theme_Meta {
	protected $taxonomies = array();

	public function set_taxonomies( $taxonomies ) {
		$this->taxonomies = $taxonomies;
	}

	public function add_taxonomy( $taxonomy ) {
		if ( ! HOCWP_Theme::in_array( $taxonomy, $this->taxonomies ) ) {
			$this->taxonomies[] = $taxonomy;
		}
	}

	public function init() {
		$callback      = $this->args['callback'];
		$add_callback  = isset( $callback['add'] ) ? $callback['add'] : $callback;
		$edit_callback = isset( $callback['edit'] ) ? $callback['edit'] : $callback;
		foreach ( $this->taxonomies as $taxonomy ) {
			add_action( $taxonomy . '_add_form_fields', array( $this, 'add_form_fields_callback' ) );
			add_action( $taxonomy . '_edit_form_fields', $edit_callback );
			add_action( 'edited_' . $taxonomy, array( $this, 'save_data' ) );
			add_action( 'created_' . $taxonomy, array( $this, 'save_data' ) );
		}
	}

	public function add_form_fields_callback( $taxonomy ) {
		$callback     = $this->args['callback'];
		$add_callback = isset( $callback['add'] ) ? $callback['add'] : '';
		if ( is_callable( $add_callback ) ) {
			call_user_func( $add_callback, $taxonomy );
		} else {
			foreach ( $this->fields as $field ) {
				if ( isset( $field['on_add_page'] ) && (bool) $field['on_add_page'] ) {

				}
			}
		}
	}

	public function save_data( $term_id ) {
		$taxonomy = isset( $_REQUEST['taxonomy'] ) ? $_REQUEST['taxonomy'] : '';
		foreach ( $this->fields as $field ) {
			$type = isset( $field['type'] ) ? $field['type'] : 'default';
			$name = isset( $field['args']['name'] ) ? $field['args']['name'] : '';
			if ( empty( $name ) ) {
				continue;
			}
			$value = isset( $_POST[ $name ] ) ? $_POST[ $name ] : '';
			$value = sanitize_meta( $name, $value, 'term' );
			update_term_meta( $term_id, $name, $value );
		}

		return $term_id;
	}
}