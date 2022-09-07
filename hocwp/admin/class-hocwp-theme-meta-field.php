<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HOCWP_Theme_Admin_Field' ) ) {
	require_once HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-admin-field.php';
}

class HOCWP_Theme_Meta_Field extends HOCWP_Theme_Admin_Field {
	public function __construct( $id, $title, $callback = 'input', $callback_args = array(), $data_type = 'string' ) {
		if ( 'input_number' == $callback && ! isset( $callback_args['class'] ) ) {
			$callback_args['class'] = 'medium-text';
		}

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

		if ( array( 'HOCWP_Theme_HTML_Field', 'latitude_longitude' ) == $this->callback
		     || 'latitude_longitude' == $this->callback ) {
			if ( ! isset( $field['callback_args']['type'] ) ) {
				$field['callback_args']['type'] = $this->data_type;
			}
		}

		return $field;
	}
}

class HOCWP_Theme_Meta_Quick_Edit_Field extends HOCWP_Theme_Meta_Field {
	public $show_admin_column = true;
	public $show_in_quick_edit = true;

	public function __construct( $id, $title, $callback = 'input', $callback_args = array(), $data_type = 'string' ) {
		parent::__construct( $id, $title, $callback, $callback_args, $data_type );
	}

	public function generate() {
		$field = parent::generate();

		$field['show_admin_column']  = $this->show_admin_column;
		$field['show_in_quick_edit'] = $this->show_in_quick_edit;

		return $field;
	}
}