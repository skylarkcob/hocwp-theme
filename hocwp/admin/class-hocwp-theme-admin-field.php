<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class HOCWP_Theme_Admin_Field {
	public $id;
	public $title;
	public $callback;
	public $callback_args;
	public $data_type;

	public function __construct( $id, $title, $callback = 'input', $callback_args = array(), $data_type = 'string' ) {
		$this->id            = $id;
		$this->title         = $title;
		$this->callback      = $callback;
		$this->callback_args = $callback_args;
		$this->data_type     = $data_type;
	}

	protected function sanitize() {
		if ( ! is_callable( $this->callback ) ) {
			if ( empty( $this->callback ) ) {
				$this->callback = 'input';
			}

			$this->callback = array( 'HOCWP_Theme_HTML_Field', $this->callback );

			if ( ! is_callable( $this->callback ) ) {
				$this->callback = array( 'HOCWP_Theme_HTML_Field', 'input' );
			}
		}

		if ( ! is_array( $this->callback_args ) ) {
			$this->callback_args = (array) $this->callback_args;
		}

		if ( empty( $this->data_type ) ) {
			$this->data_type = 'string';
		}
	}

	public abstract function generate();
}