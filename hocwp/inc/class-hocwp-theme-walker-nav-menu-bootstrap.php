<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HOCWP_Theme_Walker_Nav_Menu' ) ) {
	require_once HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-walker-nav-menu.php';
}

if ( ! class_exists( 'HOCWP_Theme_Walker_Nav_Menu_Bootstrap' ) ) {
	/**
	 * HOCWP_Theme_Walker_Nav_Menu_Bootstrap class.
	 *
	 * @extends Walker_Nav_Menu
	 */
	class HOCWP_Theme_Walker_Nav_Menu_Bootstrap extends HOCWP_Theme_Walker_Nav_Menu {

		public function __construct() {
			parent::__construct();
			add_filter( 'nav_menu_submenu_css_class', array( $this, 'submenu_css_class_filter' ), 10, 2 );
			add_filter( 'nav_menu_css_class', array( $this, 'menu_item_css_class_filter' ), 10, 3 );
			add_filter( 'nav_menu_link_attributes', array( $this, 'nav_menu_link_attributes_filter' ), 10, 3 );
			add_filter( 'walker_nav_menu_start_el', array( $this, 'nav_menu_start_el_filter' ), 10, 2 );
		}

		public function nav_menu_start_el_filter( $item_output, $item ) {
			if ( isset( $item->classes ) && HT()->array_has_value( $item->classes ) && in_array( 'divider', $item->classes ) ) {
				$item_output = '<div class="dropdown-divider"></div>';
			}

			return $item_output;
		}

		public function submenu_css_class_filter( $classes, $args ) {
			if ( ! isset( $args->walker ) || ! ( $args->walker instanceof self ) ) {
				return $classes;
			}

			$show_submenu = ( isset( $args->show_submenu ) && $args->show_submenu );

			if ( ! $show_submenu ) {
				$classes[] = 'dropdown-menu';
			}

			$classes = array_filter( $classes );
			$classes = array_unique( $classes );

			return $classes;
		}

		public function menu_item_css_class_filter( $classes, $item, $args ) {
			if ( ! isset( $args->walker ) || ! ( $args->walker instanceof self ) ) {
				return $classes;
			}

			$show_submenu = ( isset( $args->show_submenu ) && $args->show_submenu );

			if ( ! $show_submenu || ! HT()->is_positive_number( $item->menu_item_parent ) ) {
				$classes[] = 'nav-item';
			}

			if ( isset( $item->classes ) && HT()->array_has_value( $item->classes ) && in_array( 'menu-item-has-children', $item->classes ) ) {
				$classes[] = 'dropdown';
			}

			$classes = array_filter( $classes );
			$classes = array_unique( $classes );

			return $classes;
		}

		public function menu_item_title( $title, $item, $args, $depth ) {
			return $title;
		}

		public function nav_menu_link_attributes_filter( $atts, $item, $args ) {
			if ( ! isset( $args->walker ) || ! ( $args->walker instanceof self ) ) {
				return $atts;
			}

			$show_submenu = ( isset( $args->show_submenu ) && $args->show_submenu );

			$classes = isset( $atts['class'] ) ? $atts['class'] : '';

			if ( ! is_array( $classes ) ) {
				$classes = explode( ' ', $classes );
			}

			if ( ! $show_submenu || ! HT()->is_positive_number( $item->menu_item_parent ) ) {
				$classes[] = 'nav-link';
			}

			if ( ! $show_submenu ) {
				if ( isset( $item->classes ) && HT()->array_has_value( $item->classes ) && in_array( 'menu-item-has-children', $item->classes ) ) {
					$classes[] = 'dropdown-toggle';

					unset( $atts['data-object-id'] );

					$atts['role']           = 'button';
					$atts['data-toggle']    = 'dropdown';
					$atts['aria-haspopup']  = 'true';
					$atts['aria-expanded']  = 'false';
					$atts['id']             = 'menu-item-dropdown-' . $item->ID;
					$atts['data-object-id'] = $item->ID;
				}

				if ( HT()->is_positive_number( $item->menu_item_parent ) ) {
					$classes[] = 'dropdown-item';
				}
			}

			$classes = array_filter( $classes );
			$classes = array_unique( $classes );

			$classes = array_map( 'sanitize_html_class', $classes );

			$atts['class'] = join( ' ', $classes );

			return $atts;
		}

		/**
		 * Starts the list before the elements are added.
		 *
		 * @since 3.0.0
		 *
		 * @see Walker::start_lvl()
		 *
		 * @param string $output Used to append additional content (passed by reference).
		 * @param int $depth Depth of menu item. Used for padding.
		 * @param stdClass $args An object of wp_nav_menu() arguments.
		 */
		public function start_lvl( &$output, $depth = 0, $args = null ) {
			parent::start_lvl( $output, $depth, $args );

			if ( ! isset( $args->walker ) || ! ( $args->walker instanceof self ) ) {
				return;
			}

			$labelledby = '';

			// Find all links with an id in the output.
			preg_match_all( '/(<a.*?id=\"|\')(.*?)\"|\'.*?>/im', $output, $matches );

			// With pointer at end of array check if we got an ID match.
			if ( end( $matches[2] ) ) {
				// Build a string to use as aria-labelledby.
				$labelledby = 'aria-labelledby="' . esc_attr( end( $matches[2] ) ) . '"';
			}

			if ( ! empty( $labelledby ) ) {
				$output = str_replace( '<ul', '<ul ' . $labelledby, $output );
			}
		}

		/**
		 * Menu Fallback.
		 *
		 * If this function is assigned to the wp_nav_menu's fallback_cb variable
		 * and a menu has not been assigned to the theme location in the WordPress
		 * menu manager the function with display nothing to a non-logged in user,
		 * and will add a link to the WordPress menu manager if logged in as an admin.
		 *
		 * @param array $args passed from the wp_nav_menu function.
		 *
		 * @return string Menu HTML code with Add new menu link for admin users.
		 */
		public static function fallback( $args ) {
			if ( current_user_can( 'edit_theme_options' ) ) {
				// Get Arguments.
				$container       = $args['container'];
				$container_id    = $args['container_id'];
				$container_class = $args['container_class'];
				$menu_class      = $args['menu_class'];
				$menu_id         = $args['menu_id'];

				// Initialize var to store fallback html.
				$fallback_output = '';

				if ( $container ) {
					$fallback_output .= '<' . esc_attr( $container );

					if ( $container_id ) {
						$fallback_output .= ' id="' . esc_attr( $container_id ) . '"';
					}

					if ( $container_class ) {
						$fallback_output .= ' class="' . esc_attr( $container_class ) . '"';
					}

					$fallback_output .= '>';
				}

				$fallback_output .= '<ul';

				if ( $menu_id ) {
					$fallback_output .= ' id="' . esc_attr( $menu_id ) . '"';
				}

				if ( $menu_class ) {
					$fallback_output .= ' class="' . esc_attr( $menu_class ) . '"';
				}

				$fallback_output .= '>';
				$fallback_output .= '<li class="nav-item"><a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '" class="nav-link" title="' . esc_attr__( 'Add a menu', 'hocwp-theme' ) . '">' . esc_html__( 'Add a menu', 'hocwp-theme' ) . '</a></li>';
				$fallback_output .= '</ul>';

				if ( $container ) {
					$fallback_output .= '</' . esc_attr( $container ) . '>';
				}

				// If $args has 'echo' key and it's true echo, otherwise return.
				if ( array_key_exists( 'echo', $args ) && $args['echo'] ) {
					echo $fallback_output; // WPCS: XSS OK.
				}

				return $fallback_output;
			}

			return '';
		}
	}
}