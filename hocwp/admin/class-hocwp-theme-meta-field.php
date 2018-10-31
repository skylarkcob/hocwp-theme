<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Meta_Field extends HOCWP_Theme_Admin_Field {
	public function __construct( $id, $title, $callback = 'input', $callback_args = array(), $data_type = 'string' ) {
		parent::__construct( $id, $title, $callback, $callback_args, $data_type );
	}

	public function generate() {
		$this->sanitize();

		$field = array(
			'id'            => $this->id,
			'title'         => $this->title,
			'type'          => $this->data_type,
			'callback'      => $this->callback,
			'callback_args' => array(
				'class' => 'widefat'
			)
		);

		if ( isset( $this->callback_args['description'] ) ) {
			$field['description'] = $this->callback_args['description'];
			unset( $this->callback_args['description'] );
		}

		$field['callback_args'] = wp_parse_args( $this->callback_args, $field['callback_args'] );

		if ( isset( $this->callback_args['name'] ) && ! empty( $this->callback_args['name'] ) ) {
			$field['name'] = $this->callback_args['name'];
		}

		return $field;
	}
}