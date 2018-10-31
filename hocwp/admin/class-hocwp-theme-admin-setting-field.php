<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Admin_Setting_Field extends HOCWP_Theme_Admin_Field {
	public $tab;
	public $section;

	public function __construct( $id, $title, $callback = 'input', $callback_args = array(), $data_type = 'string', $tab = 'general', $section = 'default' ) {
		parent::__construct( $id, $title, $callback, $callback_args, $data_type );

		$this->tab     = $tab;
		$this->section = $section;
	}

	public function generate() {
		$this->sanitize();

		$field = array(
			'tab'     => $this->tab,
			'section' => $this->section,
			'id'      => $this->id,
			'title'   => $this->title,
			'type'    => $this->data_type,
			'args'    => array(
				'type'          => $this->data_type,
				'callback'      => $this->callback,
				'callback_args' => array(
					'class' => 'widefat'
				)
			)
		);

		if ( isset( $this->callback_args['description'] ) ) {
			$field['args']['description'] = $this->callback_args['description'];
			unset( $this->callback_args['description'] );
		}

		$field['args']['callback_args'] = wp_parse_args( $this->callback_args, $field['args']['callback_args'] );

		return $field;
	}
}