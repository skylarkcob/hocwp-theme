<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HOCWP_Theme_Walker_Nav_Menu' ) ) {
	require_once( HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-walker-nav-menu.php' );
}

if ( ! class_exists( 'HOCWP_Theme_Walker_Nav_Menu_Link' ) ) {
	/**
	 * HOCWP_Theme_Walker_Nav_Menu_Link class.
	 *
	 * @extends Walker_Nav_Menu
	 */
	class HOCWP_Theme_Walker_Nav_Menu_Link extends HOCWP_Theme_Walker_Nav_Menu {
		public function __construct() {
			parent::__construct();
		}

		public function menu_item_title( $title, $item, $args, $depth ) {
			return $title;
		}

		public function start_lvl( &$output, $depth = 0, $args = null ) {
			if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
				$t = '';
				$n = '';
			} else {
				$t = "\t";
				$n = "\n";
			}

			$indent = str_repeat( $t, $depth );

			$output .= "{$n}{$indent}{$n}";
		}

		public function end_lvl( &$output, $depth = 0, $args = null ) {
			if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
				$t = '';
				$n = '';
			} else {
				$t = "\t";
				$n = "\n";
			}

			$indent = str_repeat( $t, $depth );
			$output .= "$indent{$n}";
		}

		public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
			if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
				$t = '';
			} else {
				$t = "\t";
			}

			$indent = ( $depth ) ? str_repeat( $t, $depth ) : '';

			$classes   = empty( $item->classes ) ? array() : (array) $item->classes;
			$classes[] = 'menu-item-' . $item->ID;

			$args = apply_filters( 'nav_menu_item_args', $args, $item, $depth );

			$output .= $indent;

			$atts           = array();
			$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
			$atts['target'] = ! empty( $item->target ) ? $item->target : '';

			if ( '_blank' === $item->target && empty( $item->xfn ) ) {
				$atts['rel'] = 'noopener noreferrer';
			} else {
				$atts['rel'] = $item->xfn;
			}

			$atts['href']         = ! empty( $item->url ) ? $item->url : '';
			$atts['aria-current'] = $item->current ? 'page' : '';

			$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

			$attributes = '';

			foreach ( $atts as $attr => $value ) {
				if ( is_scalar( $value ) && '' !== $value && false !== $value ) {
					$value      = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
					$attributes .= ' ' . $attr . '="' . $value . '"';
				}
			}

			$title = apply_filters( 'the_title', $item->title, $item->ID );

			$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

			$item_output = $args->before;
			$item_output .= '<a' . $attributes . '>';
			$item_output .= $args->link_before . $title . $args->link_after;
			$item_output .= '</a>';
			$item_output .= $args->after;

			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}

		public function end_el( &$output, $item, $depth = 0, $args = null ) {
			if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
				$n = '';
			} else {
				$n = "\n";
			}

			$output .= "{$n}";
		}
	}
}