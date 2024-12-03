<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Walker_Nav_Menu_Edit' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-walker-nav-menu-edit.php' );
}

class HOCWP_Theme_Walker_Nav_Menu_Edit extends Walker_Nav_Menu_Edit {
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		parent::start_el( $output, $item, $depth, $args, $id );

		if ( $item instanceof WP_Post ) {
			$menu_id = 'id="menu-item-' . $item->ID . '"';

			$parts = explode( $menu_id, $output );

			if ( isset( $parts[1] ) ) {
				$add = $parts[1];

				// Only add fields to current menu item
				if ( false === strpos( $add, 'custom-fields hocwp-theme' ) ) {
					ob_start();
					do_action( 'hocwp_theme_nav_menu_edit_fields', $item, $depth, $args, $id );
					$html = ob_get_clean();

					if ( ! empty( $html ) ) {
						$html     = HT()->wrap_text( $html, '<div class="custom-fields hocwp-theme">', '</div>' );
						$html     .= PHP_EOL;
						$add      = preg_replace( '/<fieldset /', $html . '<fieldset ', $add, 1 );
						$parts[1] = $add;
						$output   = join( $menu_id, $parts );
					}
				}
			}
		}
	}
}