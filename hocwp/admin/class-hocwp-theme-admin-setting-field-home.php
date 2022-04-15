<?php
defined( 'ABSPATH' ) || exit;

class HOCWP_Theme_Admin_Setting_Field_Home extends HOCWP_Theme_Admin_Setting_Field {
	public function __construct( $id, $title, $callback = 'input', $callback_args = array(), $data_type = 'string', $section = 'default' ) {
		parent::__construct( $id, $title, $callback, $callback_args, $data_type, $section );

		$this->tab     = 'home';
		$this->section = $section;
	}
}