<?php

abstract class HOCWP_Theme_Meta {
	protected $args = array(
		'callback' => '',
		'load'     => array(
			'media_upload'    => false,
			'color_picker'    => false,
			'datatime_picker' => false,
			'select_chosen'   => false
		)
	);
	protected $fields;

	abstract public function init();

	public function set_fields( $fields ) {
		$this->fields = $fields;
	}

	public function add_field( $field ) {
		$this->fields[] = $field;
	}
}