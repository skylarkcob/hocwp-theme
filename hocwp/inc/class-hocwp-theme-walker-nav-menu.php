<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class HOCWP_Theme_Walker_Nav_Menu extends Walker_Nav_Menu {
	public function __construct() {
		add_filter( 'nav_menu_submenu_css_class', array( $this, 'submenu_css_classes' ), 21 );
		add_filter( 'nav_menu_item_title', array( $this, 'menu_item_title' ), 21, 4 );
	}

	public function submenu_css_classes( $classes ) {
		$classes[] = 'submenu';
		$classes[] = 'child-menu';
		$classes[] = 'shadow';

		$classes = array_filter( $classes );

		return array_unique( $classes );
	}

	public abstract function menu_item_title( $title, $item, $args, $depth );
}