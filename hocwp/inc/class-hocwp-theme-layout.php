<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Layout {
	public $id;
	public $name;
	public $image;

	public function __construct( $id, $name, $image ) {
		if ( empty( $name ) ) {
			$name = ucfirst( $id );
		}

		$this->id    = $id;
		$this->name  = $name;
		$this->image = $image;
	}
}