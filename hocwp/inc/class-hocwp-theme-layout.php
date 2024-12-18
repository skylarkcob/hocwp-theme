<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Layout {
	public $id;
	public $name;
	public $image;

	public $title;
	public $header;
	public $footer;
	public $query;
	public $lists;

	public function __construct( $id, $name, $image ) {
		if ( empty( $name ) ) {
			$name = ucfirst( $id );
		}

		$this->id    = $id;
		$this->name  = $name;
		$this->image = $image;
	}

	public function prepare_items() {
		if ( ! ht()->array_has_value( $this->lists ) && $this->query instanceof WP_Query ) {
			$this->lists = $this->query->get_posts();
		}
	}
}