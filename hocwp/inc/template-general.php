<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_module_site_header() {
	hocwp_theme_load_custom_module( 'module-site-header' );
}

add_action( 'hocwp_theme_module_site_header', 'hocwp_theme_module_site_header' );

function hocwp_theme_module_site_header_amp() {
	hocwp_theme_load_custom_module( 'module-site-header-amp' );
}

add_action( 'hocwp_theme_module_site_header_amp', 'hocwp_theme_module_site_header_amp' );

function hocwp_theme_module_site_footer() {
	hocwp_theme_load_custom_module( 'module-site-footer' );
}

add_action( 'hocwp_theme_module_site_footer', 'hocwp_theme_module_site_footer' );

function hocwp_theme_module_site_footer_amp() {
	hocwp_theme_load_custom_module( 'module-site-footer-amp' );
}

add_action( 'hocwp_theme_module_site_footer_amp', 'hocwp_theme_module_site_footer_amp' );

function hocwp_theme_template_index() {
	if ( HT_Util()->is_amp( array( 'transitional', 'standard' ) ) ) {
		hocwp_theme_load_custom_template( 'template-amp' );

		return;
	}

	if ( is_home() && is_front_page() ) {
		hocwp_theme_load_custom_template( 'template-index' );
	} else {
		$path = '';

		if ( is_front_page() ) {
			$path = HOCWP_THEME_CUSTOM_PATH . '/views/template-front-page.php';
		} elseif ( is_home() ) {
			$path = HOCWP_THEME_CUSTOM_PATH . '/views/template-blog.php';
		}

		if ( ! empty( $path ) && file_exists( $path ) ) {
			load_template( $path );

			return;
		}
	}
}

add_action( 'hocwp_theme_template_index', 'hocwp_theme_template_index' );

function hocwp_theme_template_page() {
	if ( HT_Util()->is_amp( array( 'transitional', 'standard' ) ) ) {
		hocwp_theme_load_custom_template( 'template-single-amp' );

		return;
	}

	$path = '';

	if ( is_front_page() ) {
		$path = HOCWP_THEME_CUSTOM_PATH . '/views/template-front-page.php';
	}

	if ( ! empty( $path ) && file_exists( $path ) ) {
		load_template( $path );

		return;
	}

	hocwp_theme_load_custom_template( 'template-page' );
}

add_action( 'hocwp_theme_template_page', 'hocwp_theme_template_page' );

function hocwp_theme_template_single() {
	if ( HT_Util()->is_amp( array( 'transitional', 'standard' ) ) ) {
		hocwp_theme_load_custom_template( 'template-single-amp' );

		return;
	}

	if ( ! is_page() && is_singular() ) {
		$tmp = get_post_type( get_the_ID() );

		if ( 'post' != $tmp ) {
			$file = HOCWP_THEME_CUSTOM_PATH . '/views/template-single-' . $tmp . '.php';

			if ( file_exists( $file ) ) {
				load_template( $file );

				return;
			}

			$tmp  = str_replace( '_', '-', $tmp );
			$file = HOCWP_THEME_CUSTOM_PATH . '/views/template-single-' . $tmp . '.php';

			if ( file_exists( $file ) ) {
				load_template( $file );

				return;
			}
		}
	}

	hocwp_theme_load_custom_template( 'template-single' );
}

add_action( 'hocwp_theme_template_single', 'hocwp_theme_template_single' );

function hocwp_theme_template_404() {
	hocwp_theme_load_custom_template( 'template-404' );
}

add_action( 'hocwp_theme_template_404', 'hocwp_theme_template_404' );

function hocwp_theme_template_archive() {
	if ( HT_Util()->is_amp( array( 'transitional', 'standard' ) ) ) {
		hocwp_theme_load_custom_template( 'template-amp' );

		return;
	}

	if ( is_post_type_archive() ) {
		global $post_type;

		$type = $post_type;

		if ( is_array( $type ) ) {
			$type = array_filter( $type );
			$type = array_unique( $type );
			$type = current( $type );
		}

		if ( ! empty( $type ) ) {
			$file = HOCWP_THEME_CUSTOM_PATH . '/views/template-archive-' . $type . '.php';

			if ( file_exists( $file ) ) {
				load_template( $file );

				return;
			}

			$tmp  = str_replace( '_', '-', $type );
			$file = HOCWP_THEME_CUSTOM_PATH . '/views/template-archive-' . $tmp . '.php';

			if ( file_exists( $file ) ) {
				load_template( $file );

				return;
			}
		}
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$object = get_queried_object();

		if ( $object instanceof WP_Term ) {
			$tmp  = $object->taxonomy;
			$file = HOCWP_THEME_CUSTOM_PATH . '/views/template-archive-' . $tmp . '.php';

			if ( file_exists( $file ) ) {
				load_template( $file );

				return;
			}

			$tmp  = str_replace( '_', '-', $tmp );
			$file = HOCWP_THEME_CUSTOM_PATH . '/views/template-archive-' . $tmp . '.php';

			if ( file_exists( $file ) ) {
				load_template( $file );

				return;
			}

			$tax = get_taxonomy( $object->taxonomy );

			if ( $tax instanceof WP_Taxonomy ) {
				$object_type = $tax->object_type;

				if ( HT()->array_has_value( $object_type ) ) {
					foreach ( $object_type as $type ) {
						$tmp  = $type;
						$file = HOCWP_THEME_CUSTOM_PATH . '/views/template-archive-' . $tmp . '.php';

						if ( file_exists( $file ) ) {
							load_template( $file );

							return;
						}

						$tmp  = str_replace( '_', '-', $tmp );
						$file = HOCWP_THEME_CUSTOM_PATH . '/views/template-archive-' . $tmp . '.php';

						if ( file_exists( $file ) ) {
							load_template( $file );

							return;
						}
					}
				}
			}
		}

	} elseif ( is_author() ) {
		$file = HOCWP_THEME_CUSTOM_PATH . '/views/template-archive-author.php';

		if ( file_exists( $file ) ) {
			load_template( $file );

			return;
		}
	}

	hocwp_theme_load_custom_template( 'template-archive' );
}

add_action( 'hocwp_theme_template_archive', 'hocwp_theme_template_archive', 99 );

/**
 * Filter template path for using default template in theme.
 *
 * @param string $template The path of template file.
 *
 * @return string The filtered template file path.
 */
function hocwp_theme_template_include_filter( $template ) {
	// Filter use templates in plugin.
	$plugin_template = apply_filters( 'hocwp_theme_use_plugin_templates', true );

	// If use templates in plugin, check dir contains plugins directory.
	if ( $plugin_template ) {
		if ( false === strpos( $template, 'plugins' ) ) {
			$plugin_template = false;
		}
	}

	// If not use plugin templates or template dir not contain plugins directory just use default theme template.
	if ( ! $plugin_template ) {
		$dir = get_template_directory();
		$dir = trailingslashit( $dir );

		$is_wc = ( false !== strpos( $template, $dir . 'woocommerce' ) );

		// Check back for WooCommerce and other plugin templates.
		if ( ! $is_wc || ! file_exists( $template ) ) {
			if ( is_archive() && 'archive.php' != basename( $template ) ) {
				$template = HOCWP_Theme()->theme_path . '/archive.php';
			} elseif ( ! is_page() && is_single() && 'single.php' != basename( $template ) ) {
				$template = HOCWP_Theme()->theme_path . '/single.php';
			}
		}
	}

	return $template;
}

add_filter( 'template_include', 'hocwp_theme_template_include_filter', 9999 );

function hocwp_theme_template_search() {
	if ( HT_Util()->is_amp( array( 'transitional', 'standard' ) ) ) {
		hocwp_theme_load_custom_template( 'template-amp' );

		return;
	}

	hocwp_theme_load_custom_template( 'template-search' );
}

add_action( 'hocwp_theme_template_search', 'hocwp_theme_template_search' );

/*
 * Filter widget title.
 */
function hocwp_theme_widget_title_filter( $title, $instance = array(), $id = '' ) {
	if ( ! is_admin() && ! empty( $title ) && null !== $instance ) {
		$first = substr( $title, 0, 1 );

		if ( '!' == $first ) {
			$title = '';
		} else {
			if ( ! HT()->string_contain( $title, '</span>' ) && 'rss' != $id ) {
				$title = '<span>' . $title . '</span>';
			}
		}
	}

	return $title;
}

add_filter( 'widget_title', 'hocwp_theme_widget_title_filter', 10, 3 );

/*
 * Display widget title.
 */
function hocwp_theme_widget_title( $args, $instance, $widget ) {
	$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';
	$title = apply_filters( 'widget_title', $title, $instance, $widget->id_base );

	$term_as_title   = false;
	$title_term_link = false;

	if ( 'hocwp_post_widget' == $widget->id_base ) {
		$term_as_title   = isset( $instance['term_as_title'] ) ? (bool) $instance['term_as_title'] : $widget->defaults['term_as_title'];
		$title_term_link = isset( $instance['title_term_link'] ) ? (bool) $instance['title_term_link'] : $widget->defaults['title_term_link'];
	}

	if ( $term_as_title || $title_term_link ) {
		$term = isset( $instance['term'] ) ? $instance['term'] : '';

		if ( ! empty( $term ) ) {
			if ( is_array( $term ) ) {
				$term  = array_shift( $term );
				$parts = explode( ',', $term );

				if ( 2 == count( $parts ) ) {
					$term = get_term_by( 'id', $parts[1], $parts[0] );

					if ( $term instanceof WP_Term ) {
						if ( $term_as_title ) {
							$title = $term->name;
						}

						if ( $title_term_link && ! empty( $title ) ) {
							$title = '<a href="' . get_term_link( $term ) . '">' . $title . '</a>';
						}
					}
				}
			}
		}
	}

	if ( $title ) {
		$before_title = isset( $args['before_title'] ) ? $args['before_title'] : '<h3 class="widget-title">';
		$after_title  = isset( $args['after_title'] ) ? $args['after_title'] : '</h3>';
		echo $before_title . $title . $after_title . PHP_EOL;
	} else {
		echo '<div class="widget-content">' . PHP_EOL;
	}
}

add_action( 'hocwp_theme_widget_title', 'hocwp_theme_widget_title', 10, 3 );

/*
 * Display widget before HTML code.
 */
function hocwp_theme_widget_before( $args, $instance, $widget ) {
	$before_widget = isset( $args['before_widget'] ) ? $args['before_widget'] : '<div class="widget">' . PHP_EOL;

	$before_widget = apply_filters( 'hocwp_theme_widget_before_html', $before_widget, $args, $instance, $widget );

	echo $before_widget . PHP_EOL;

	$show_title = isset( $instance['show_title'] ) ? (bool) $instance['show_title'] : true;
	$title      = '';

	if ( $show_title ) {
		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title, $instance, $widget->id_base );
	}

	if ( ! $show_title || ! $title ) {
		echo '<div class="widget-content">' . PHP_EOL;
	} elseif ( $show_title ) {
		do_action( 'hocwp_theme_widget_title', $args, $instance, $widget );
	}
}

add_action( 'hocwp_theme_widget_before', 'hocwp_theme_widget_before', 9, 3 );

/*
 * Update widget html class.
 */
function hocwp_theme_widget_before_html_filter( $before_widget, $args, $instance, $widget ) {
	if ( $widget instanceof WP_Widget && is_array( $args ) && isset( $args['before_widget'] ) && ! empty( $args['before_widget'] ) ) {
		$pos = strpos( $before_widget, 'class=' );

		if ( false !== $pos ) {
			$class = substr( $before_widget, $pos );
			$class = HT()->get_string_between( $class, '"', '"' );

			if ( ! empty( $class ) ) {
				$bk = $class;

				foreach ( $instance as $key => $value ) {
					if ( ! empty( $value ) ) {
						if ( is_array( $value ) ) {
							$value = json_encode( $value );
						}

						$value = sanitize_title( $value );

						$value = sanitize_html_class( $value );

						if ( ! empty( $value ) ) {
							$value = $key . '_' . $value;

							$class .= ' ' . $value;
						}
					}
				}

				$before_widget = str_replace( $bk, $class, $before_widget );
			}
		}
	}

	return $before_widget;
}

add_filter( 'hocwp_theme_widget_before_html', 'hocwp_theme_widget_before_html_filter', 10, 4 );

/*
 * Display widget after HTML code.
 */
function hocwp_theme_widget_after( $args, $instance, $widget ) {
	$show_title = isset( $instance['show_title'] ) ? (bool) $instance['show_title'] : true;
	$title      = '';

	if ( $show_title ) {
		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title, $instance, $widget->id_base );
	}

	if ( ! $title || ! $show_title ) {
		echo '</div>' . PHP_EOL;
	}

	$after_widget = isset( $args['after_widget'] ) ? $args['after_widget'] : '</div>';

	echo $after_widget . PHP_EOL;
}

add_action( 'hocwp_theme_widget_after', 'hocwp_theme_widget_after', 99, 3 );

/*
 * Get default core sidebar.
 */
function hocwp_theme_module_sidebar() {
	if ( ! did_action( 'hocwp_theme_module_sidebar' ) ) {
		_doing_it_wrong( __FUNCTION__, __( 'Please call function get_sidebar instead!', 'hocwp-theme' ), '5.2.2' );
	}

	if ( function_exists( 'hocwp_theme_load_views' ) ) {
		hocwp_theme_load_views( 'module-sidebar' );
	}
}

add_action( 'hocwp_theme_module_sidebar', 'hocwp_theme_module_sidebar' );

/*
 * Change sidebar for each page.
 */
function hocwp_theme_sidebar_filter( $sidebar ) {
	$dynamic_sidebar = '';

	if ( ( is_single() || is_page() ) && ! is_singular() ) {
		$dynamic_sidebar = get_post_meta( get_the_ID(), 'sidebar', true );
	} elseif ( is_post_type_archive() || is_tax() || is_singular() ) {
		if ( is_singular() ) {
			$dynamic_sidebar = get_post_meta( get_the_ID(), 'sidebar', true );
		}

		if ( ! is_active_sidebar( $dynamic_sidebar ) ) {
			$obj = get_queried_object();

			if ( $obj instanceof WP_Post_Type ) {
				$dynamic_sidebar = $obj->name;
			} elseif ( $obj instanceof WP_Post ) {
				$dynamic_sidebar = $obj->post_type;
			} elseif ( $obj instanceof WP_Term ) {
				$taxonomy        = get_taxonomy( $obj->taxonomy );
				$dynamic_sidebar = current( $taxonomy->object_type );
			}

			unset( $obj );
		}
	}

	if ( ! empty( $dynamic_sidebar ) && is_active_sidebar( $dynamic_sidebar ) ) {
		$sidebar = $dynamic_sidebar;
	} elseif ( ( is_home() || is_front_page() ) && is_active_sidebar( 'home' ) ) {
		$sidebar = 'home';
	} elseif ( is_archive() && is_active_sidebar( 'archive' ) ) {
		$sidebar = 'archive';
	} elseif ( is_search() && is_active_sidebar( 'search' ) ) {
		$sidebar = 'search';
	} elseif ( ( ( is_singular() || is_single() ) && ! is_page() ) && is_active_sidebar( 'single' ) ) {
		$sidebar = 'single';
	} elseif ( is_page() && is_active_sidebar( 'page' ) ) {
		$sidebar = 'page';
	} elseif ( is_404() && is_active_sidebar( 'page_404' ) ) {
		$sidebar = 'page_404';
	}

	unset( $dynamic_sidebar );

	return $sidebar;
}

add_filter( 'hocwp_theme_sidebar', 'hocwp_theme_sidebar_filter' );

/*
 * Filter dynamic sidebar params.
 */
function hocwp_theme_dynamic_sidebar_params_filter( $params ) {
	$wrap = apply_filters( 'hocwp_theme_wrap_widget', true, $params );

	if ( isset( $params[0] ) ) {
		$args = $params[0];

		$id = isset( $args['id'] ) ? $args['id'] : '';

		$wrap = ( $wrap && ! HT()->string_contain( $id, 'sidebar' ) ) ? true : false;

		if ( isset( $args['convert_section'] ) ) {
			$section = (bool) $args['convert_section'];
		} else {
			$section = ( ! isset( $params[0]['id'] ) || false === strpos( $params[0]['id'], 'footer' ) );
		}

		if ( $section ) {
			if ( isset( $args['before_widget'] ) && '<li id="%1$s" class="widget %2$s">' !== $args['before_widget'] ) {
				if ( false !== strpos( $args['before_widget'], '%' ) && false !== strpos( $args['before_widget'], '$' ) ) {
					$section = false;
				}
			}
		}

		if ( isset( $args['before_widget'] ) ) {
			$before_widget = $args['before_widget'];

			if ( $section ) {
				$before_widget = str_replace( '<li', '<section', $before_widget );
			}
		} else {
			$before_widget = '<section class="widget">';
		}

		if ( $wrap ) {
			$before_widget .= PHP_EOL;
			$before_widget .= '<div class="widget-inner">' . PHP_EOL;
		}

		$params[0]['before_widget'] = $before_widget;

		if ( isset( $args['before_title'] ) ) {
			$before_title = $args['before_title'];
			$search       = array( 'h1', 'h2' );
			$replace      = array( 'h3', 'h3' );

			if ( ! HT()->string_contain( $before_title, 'widget-title' ) ) {
				array_unshift( $search, 'widgettitle' );
				array_unshift( $replace, 'widget-title widgettitle' );
			}

			$before_title = str_replace( $search, $replace, $before_title );
		} else {
			$before_title = '<h3 class="widget-title widgettitle">';
		}

		if ( $wrap ) {
			$before_title = '</div>' . PHP_EOL . $before_title;
		}

		$params[0]['before_title'] = $before_title;

		if ( isset( $args['after_title'] ) ) {
			$after_title = $args['after_title'];
			$search      = array( 'h1', 'h2' );
			$replace     = array( 'h3', 'h3' );
			$after_title = str_replace( $search, $replace, $after_title );
		} else {
			$after_title = '</h3>';
		}

		if ( $wrap ) {
			$after_title .= PHP_EOL . '<div class="widget-content">' . PHP_EOL;
		}

		$params[0]['after_title'] = $after_title;

		if ( isset( $args['after_widget'] ) ) {
			$after_widget = $args['after_widget'];

			if ( $section ) {
				$after_widget = str_replace( '</li>', '</section>', $after_widget );
			}
		} else {
			$after_widget = '</section>';
		}

		if ( $wrap ) {
			$after_widget = '</div>' . PHP_EOL . $after_widget;
		}

		$params[0]['after_widget'] = $after_widget;
	}

	return $params;
}

add_filter( 'dynamic_sidebar_params', 'hocwp_theme_dynamic_sidebar_params_filter' );

function hocwp_theme_content_area_before() {
	?>
    <div id="primary" class="content-area">
		<?php do_action( 'hocwp_theme_site_main_before' ); ?>
        <main id="main" class="site-main">
			<?php
			}

			add_action( 'hocwp_theme_content_area_before', 'hocwp_theme_content_area_before', 3 );

			function hocwp_theme_content_area_after() {
			?>
        </main>
        <!-- #main -->
		<?php do_action( 'hocwp_theme_site_main_after' ); ?>
    </div><!-- #primary -->
	<?php
}

add_action( 'hocwp_theme_content_area_after', 'hocwp_theme_content_area_after', 3 );

function hocwp_theme_article_header_before() {
	echo '<header class="entry-header">';
}

add_action( 'hocwp_theme_article_header_before', 'hocwp_theme_article_header_before' );

function hocwp_theme_article_header_after() {
	echo '</header><!-- .entry-header -->';
}

add_action( 'hocwp_theme_article_header_after', 'hocwp_theme_article_header_after' );

function hocwp_theme_replace_search_submit_button( $form = '', $icon = '' ) {
	if ( empty( $form ) ) {
		$form = get_search_form( false );
	}

	ob_start();
	?>
    <button type="submit" class="btn js-search-submit search-submit">
		<?php
		if ( empty( $icon ) ) {
			HOCWP_Theme_SVG_Icon::search();
		} else {
			echo $icon;
		}
		?>
    </button>
	<?php
	$button = ob_get_clean();
	$search = '</label>';

	if ( false !== ( $pos = HT()->string_contain( $form, $search, 0, 'int' ) ) ) {
		$form = substr( $form, 0, $pos + strlen( $search ) );
	}

	$form .= "\n";
	$form .= $button;
	$form .= '</form>';

	return $form;
}

function hocwp_theme_get_the_archive_title_filter( $title ) {
	if ( is_search() ) {
		$title = sprintf( __( 'Search Results: %s', 'hocwp-theme' ), get_search_query() );
	}

	return $title;
}

add_filter( 'get_the_archive_title', 'hocwp_theme_get_the_archive_title_filter' );

function hocwp_theme_adjacent_post_link_filter( $output, $format, $link, $post, $adjacent ) {
	if ( $post instanceof WP_Post ) {
		$attr = '';

		switch ( $adjacent ) {
			case 'next':
				$attr = sprintf( 'data-text="%s"', __( 'Next Posts', 'hocwp-theme' ) );
				break;
			case 'previous':
				$attr = sprintf( 'data-text="%s"', __( 'Previous Posts', 'hocwp-theme' ) );
				break;
		}

		$output = HT()->add_html_attribute( 'div', $output, $attr );
	}

	return $output;
}

add_filter( 'next_post_link', 'hocwp_theme_adjacent_post_link_filter', 10, 5 );
add_filter( 'previous_post_link', 'hocwp_theme_adjacent_post_link_filter', 10, 5 );

function hocwp_theme_recheck_has_nav_menu( $has_nav_menu, $location ) {
	if ( ! $has_nav_menu ) {
		$menu = wp_nav_menu( array( 'theme_location' => $location, 'echo' => false, 'fallback_cb' => '' ) );

		unset( $menu );
	}

	return $has_nav_menu;
}

add_filter( 'has_nav_menu', 'hocwp_theme_recheck_has_nav_menu', 10, 2 );

function hocwp_theme_wp_nav_menu_args_filter( $args ) {
	$menu_class = isset( $args['menu_class'] ) ? $args['menu_class'] : '';
	$menu_class .= ' hocwp-menu clearfix';

	$theme_location = isset( $args['theme_location'] ) ? $args['theme_location'] : '';

	if ( ! empty( $theme_location ) ) {
		$menu_class .= ' location-' . $theme_location;
	}

	$args['menu_class'] = trim( $menu_class );

	unset( $menu_class );

	return $args;
}

add_filter( 'wp_nav_menu_args', 'hocwp_theme_wp_nav_menu_args_filter' );

function hocwp_theme_wp_page_menu_args_filter( $args ) {
	$container_class     = isset( $args['container_class'] ) ? $args['container_class'] : '';
	$container_classes   = explode( ' ', $container_class );
	$container_classes[] = 'menu-pages';

	$menu_class   = isset( $args['menu_class'] ) ? $args['menu_class'] : '';
	$menu_classes = explode( ' ', $menu_class );

	$menu_classes = array_merge( $menu_classes, $container_classes );

	$menu_classes = array_filter( $menu_classes );
	$menu_classes = array_unique( $menu_classes );

	$args['menu_class'] = implode( ' ', $menu_classes );

	if ( ! isset( $args['before'] ) || empty( $args['before'] ) || '<ul>' == $args['before'] ) {
		$args['before']    = '';
		$args['container'] = 'ul';
	}

	unset( $container_class, $container_classes, $menu_class, $menu_classes );

	return $args;
}

add_filter( 'wp_page_menu_args', 'hocwp_theme_wp_page_menu_args_filter', 99 );

function hocwp_theme_menu_button( $control = 'main-menu', $id = '' ) {
	if ( empty( $control ) ) {
		$control = 'main-menu';
	}

	if ( empty( $id ) ) {
		$id = 'toggle-' . $control;
	}

	ob_start();
	?>
    <div class="menu-overlay-bg"></div>
    <button id="<?php echo esc_attr( $id ); ?>" class="menu-toggle" aria-controls="<?php echo $control; ?>"
            aria-expanded="false">
		<?php
		HT_SVG_Icon()->bars();
		HT_SVG_Icon()->close();
		?>
        <span class="screen-reader-text"><?php esc_html_e( 'Menu', 'hocwp-theme' ); ?></span>
    </button>
	<?php
	return ob_get_clean();
}

function hocwp_theme_main_menu( $args ) {
	$args = (array) $args;

	$container_class = 'primary-menus main-menu primary-menu';

	$theme_location = isset( $args['theme_location'] ) ? $args['theme_location'] : 'menu-1';

	$defaults = array(
		'theme_location'  => $theme_location,
		'container_id'    => 'site-navigation',
		'menu_id'         => 'main-menu',
		'container_class' => $container_class
	);

	$args = wp_parse_args( $args, $defaults );

	$args = apply_filters( 'hocwp_theme_main_menu_args', $args );

	HT_Frontend()->wp_nav_menu_helper( $args );
}

add_action( 'hocwp_theme_main_menu', 'hocwp_theme_main_menu' );

function hocwp_theme_main_menu_args_filter( $args ) {
	$theme_location = isset( $args['theme_location'] ) ? $args['theme_location'] : '';

	if ( 'mobile' != $theme_location && wp_is_mobile() && has_nav_menu( 'mobile' ) ) {
		$args['theme_location'] = 'mobile';
	}

	return $args;
}

add_filter( 'hocwp_theme_main_menu_args', 'hocwp_theme_main_menu_args_filter' );

function hocwp_theme_mobile_menu( $args ) {
	$args = (array) $args;

	$container_class = 'mobile-menu';

	$displaying = isset( $args['displaying'] ) ? $args['displaying'] : 'default';

	$container_class .= ' displaying-' . sanitize_html_class( $displaying );

	$defaults = array(
		'theme_location'  => 'mobile',
		'container_id'    => 'mobile-navigation',
		'menu_id'         => 'mobile-menu',
		'container_class' => $container_class,
		'button_control'  => 'mobile-menu',
		'fallback_cb'     => false
	);

	$args = wp_parse_args( $args, $defaults );

	$args = apply_filters( 'hocwp_theme_mobile_menu_args', $args );

	HT_Frontend()->wp_nav_menu_helper( $args );
}

add_action( 'hocwp_theme_mobile_menu', 'hocwp_theme_mobile_menu' );

function hocwp_theme_wp_nav_menu_items_filter( $items, $args ) {
	$insert = apply_filters( 'hocwp_theme_insert_mobile_menu_search', ( 'mobile' == $args->theme_location && wp_is_mobile() ) );

	if ( $insert ) {
		$form  = get_search_form( false );
		$form  = '<li class="menu-item search-item">' . $form . '</li>';
		$items = $form . $items;
	}

	return $items;
}

add_filter( 'wp_nav_menu_items', 'hocwp_theme_wp_nav_menu_items_filter', 10, 2 );

function hocwp_theme_human_time_diff_filter( $since, $diff ) {
	if ( $diff < MINUTE_IN_SECONDS ) {
		$secs = $diff;

		if ( $secs <= 1 ) {
			$secs = 1;
		}

		$since = sprintf( _n( '%s sec', '%s secs', $secs, 'hocwp-theme' ), $secs );
	}

	$since = apply_filters( 'hocwp_theme_human_time_diff', $since, $diff );

	return $since;
}

add_filter( 'human_time_diff', 'hocwp_theme_human_time_diff_filter', 10, 2 );

function hocwp_theme_navigation_markup_template_filter() {
	$template = '<nav class="navigation %1$s">
		<h2 class="screen-reader-text">%2$s</h2>
		<div class="nav-links">%3$s</div>
	</nav>';

	return $template;
}

add_filter( 'navigation_markup_template', 'hocwp_theme_navigation_markup_template_filter' );

function hocwp_theme_color_meta() {
	global $hocwp_theme;
	$options = $hocwp_theme->options;

	if ( isset( $options['reading']['theme_color'] ) && ! empty( $options['reading']['theme_color'] ) ) {
		$color = $options['reading']['theme_color'];
		$color = sanitize_hex_color( $color );

		if ( ! empty( $color ) ) {
			echo '<meta name="theme-color" content="' . $color . '" />' . PHP_EOL;
		}
	}
}

add_action( 'wp_head', 'hocwp_theme_color_meta' );
add_action( 'login_head', 'hocwp_theme_color_meta' );

function hocwp_theme_wp_head_action() {
	global $hocwp_theme;
	$options = $hocwp_theme->options;

	if ( isset( $options['custom_code']['head'] ) ) {
		echo $options['custom_code']['head'];
	}

	$css = isset( $options['custom_code']['css'] ) ? $options['custom_code']['css'] : '';

	if ( ! empty( $css ) ) {
		if ( ! class_exists( 'HOCWP_Theme_Minify' ) ) {
			require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-minify.php';
		}

		$css   = strip_tags( $css );
		$css   = HOCWP_Theme_Minify::css( $css );
		$style = new HOCWP_Theme_HTML_Tag( 'style' );

		$style->set_text( $css );
		$style->add_attribute( 'type', 'text/css' );
		$style->output();
	}
}

add_action( 'wp_head', 'hocwp_theme_wp_head_action' );

function hocwp_theme_wp_footer_action() {
	global $hocwp_theme;

	if ( ! isset( $hocwp_theme->options ) || ! is_array( $hocwp_theme->options ) ) {
		$hocwp_theme->options = HOCWP_Theme()->get_options();
	}

	$options = $hocwp_theme->options;

	HT_Util()->load_facebook_javascript_sdk();

	$agent = HT()->get_user_agent();

	if ( empty( $agent ) || ! HT()->is_google_pagespeed() ) {
		$google_analytics = isset( $options['custom_code']['google_analytics'] ) ? $options['custom_code']['google_analytics'] : '';
		echo $google_analytics;
	}

	if ( isset( $options['custom_code']['body'] ) ) {
		echo $options['custom_code']['body'];
	}

	if ( isset( $options['custom_code']['footer'] ) ) {
		echo $options['custom_code']['footer'];
	}

	$load = apply_filters( 'hocwp_theme_load_addthis', false );

	if ( $load ) {
		$addthis_id = isset( $options['social']['addthis_id'] ) ? $options['social']['addthis_id'] : '';

		if ( ! empty( $addthis_id ) ) {
			?>
            <!-- Go to www.addthis.com/dashboard to customize your tools -->
            <script type="text/javascript"
                    src="//s7.addthis.com/js/300/addthis_widget.js#pubid=<?php echo $addthis_id; ?>"></script>
			<?php
		}
	}

	$back_to_top = isset( $options['reading']['back_to_top'] ) ? $options['reading']['back_to_top'] : '';

	if ( 1 == $back_to_top ) {
		HT_Frontend()->back_to_top_button();
	}

	if ( is_single() || is_page() || is_singular() ) {
		$float_post_nav = HT_Options()->get_tab( 'float_post_nav', '', 'reading' );

		if ( 1 == $float_post_nav ) {
			$obj = HT_Query()->get_previous_post();
			?>
            <div class="float-post-nav">
				<?php
				if ( $obj instanceof WP_Post ) {
					?>
                    <div class="prev">
                        <a href="<?php echo get_permalink( $obj ); ?>"
                           title="<?php echo esc_attr( $obj->post_title ); ?>">&laquo;</a>
                    </div>
					<?php
				}

				$obj = HT_Query()->get_previous_post( false );

				if ( $obj instanceof WP_Post ) {
					?>
                    <div class="next">
                        <a href="<?php echo get_permalink( $obj ); ?>"
                           title="<?php echo esc_attr( $obj->post_title ); ?>">&raquo;</a>
                    </div>
					<?php
				}
				?>
            </div>
			<?php
		}
	}

	$cookie_alert = HT_Options()->get_tab( 'cookie_alert', '', 'reading' );

	if ( 1 == $cookie_alert ) {
		$page = get_post( get_option( 'page_for_privacy_policy' ) );

		$text = __( 'This website uses cookies to ensure you get the best experience on our website. By continuing to browse on this website, you accept the use of cookies for the above purposes.', 'hocwp-theme' );

		if ( $page instanceof WP_Post && 'publish' == $page->post_status ) {
			$text = sprintf( __( 'We use cookies to give you the best possible website experience. <span>By using %s, you agree to our <a href="%s">%s</a></span>.', 'hocwp-theme' ), get_bloginfo( 'name' ), get_permalink( $page ), $page->post_title );
		}

		$text .= '&nbsp;';
		$text .= '<button id="sc-gdpr-accept" class="btn btn-success">' . __( 'Accept', 'hocwp-theme' ) . '</button>';
		?>
        <div id="sc-gdpr-box"
             class="fixed-bottom alert alert-warning mb-0 text-dark rounded-0 alert-dismissible fade show" role="alert"
             style="display: none;">
            <div class="centerd">
				<?php echo wpautop( $text ); ?>
                <button id="sc-gdpr-close" type="button" class="close" data-dismiss="alert"
                        aria-label="<?php esc_attr_e( 'Close', 'hocwp-theme' ); ?>"><span
                            aria-hidden="true">&times;</span></button>
            </div>
        </div>
		<?php
	}
}

add_action( 'wp_footer', 'hocwp_theme_wp_footer_action' );

function hocwp_theme_site_branding_action() {
	?>
    <div class="site-branding site-logo">
		<?php
		do_action( 'hocwp_theme_site_branding_before' );
		HT_Frontend()->site_logo();
		do_action( 'hocwp_theme_site_branding_middle' );

		if ( display_header_text() ) {
			$description = get_bloginfo( 'description', 'display' );

			if ( $description || is_customize_preview() ) {
				?>
                <p class="site-description"><?php echo $description; /* WPCS: xss ok. */ ?></p>
				<?php
			}
		}

		do_action( 'hocwp_theme_site_branding_after' );
		?>
    </div><!-- .site-branding -->
	<?php
}

add_action( 'hocwp_theme_site_branding', 'hocwp_theme_site_branding_action' );

function hocwp_theme_theme_mod_custom_logo_filter( $mod ) {
	global $hocwp_theme;

	$options = $hocwp_theme->options;

	$logo_display = $options['general']['logo_display'];

	if ( 'image' == $logo_display ) {
		$id = isset( $options['general']['logo_image'] ) ? $options['general']['logo_image'] : '';

		if ( HT()->is_positive_number( $id ) ) {
			$mod = $id;
		}
	} else {
		$mod = null;
	}

	return $mod;
}

add_filter( 'theme_mod_custom_logo', 'hocwp_theme_theme_mod_custom_logo_filter' );

function hocwp_theme_pre_option_site_icon_filter( $value ) {
	global $hocwp_theme;

	$options = $hocwp_theme->options;

	if ( isset( $options['general']['site_icon'] ) && HT()->is_positive_number( $options['general']['site_icon'] ) ) {
		$ico  = $options['general']['site_icon'];
		$mime = get_post_mime_type( $ico );

		if ( 'image/jpeg' == $mime || 'image/png' == $mime ) {
			$value = $ico;
		}
	}

	return $value;
}

add_filter( 'pre_option_site_icon', 'hocwp_theme_pre_option_site_icon_filter' );

function hocwp_theme_get_custom_logo_filter( $html ) {
	global $hocwp_theme;

	$options = $hocwp_theme->options;

	$logo_display = $options['general']['logo_display'];

	if ( is_customize_preview() ) {
		$tag_name = 'div';
	} else {
		if ( ( is_home() || is_front_page() ) && false === strpos( $html, '<h1' ) ) {
			$tag_name = 'h1';
		} else {
			$tag_name = 'p';
		}
	}

	$tag_name = apply_filters( 'hocwp_theme_site_title_tag', $tag_name );

	if ( 'image' != $logo_display ) {
		if ( 'text' == $logo_display ) {
			$text = isset( $options['general']['logo_text'] ) ? $options['general']['logo_text'] : '';

			if ( empty( $text ) ) {
				$text = get_bloginfo( 'name', 'display' );
			} else {
				$domain = HT()->get_domain_name( home_url() );
				$text   = str_replace( '[DOMAIN]', $domain, $text );
			}

			$text = strip_tags( $text );

			if ( ! empty( $text ) ) {
				$tag = new HOCWP_Theme_HTML_Tag( $tag_name );
				$tag->add_attribute( 'class', 'site-title' );

				$link = new HOCWP_Theme_HTML_Tag( 'a' );
				$link->add_attribute( 'class', 'navbar-brand' );
				$link->add_attribute( 'href', esc_url( home_url( '/' ) ) );
				$link->add_attribute( 'rel', 'home' );
				$link->set_text( $text );

				$tag->set_text( $link );

				$html = $tag->build();
			}
		} elseif ( 'custom' == $logo_display ) {
			if ( isset( $options['general']['logo_html'] ) ) {
				$html = $options['general']['logo_html'];
			}
		}
	} else {
		if ( empty( $html ) || false !== strpos( $html, 'style="display:none;"><img class="custom-logo"' ) ) {
			$html = get_bloginfo( 'name', 'display' );

			$link = new HOCWP_Theme_HTML_Tag( 'a' );
			$link->add_attribute( 'class', 'navbar-brand' );
			$link->add_attribute( 'href', esc_url( home_url( '/' ) ) );
			$link->add_attribute( 'rel', 'home' );
			$link->set_text( $html );

			$html = $link->build();
		}

		$tag = new HOCWP_Theme_HTML_Tag( $tag_name );
		$tag->add_attribute( 'class', 'site-title' );
		$tag->set_text( $html );

		$html = $tag->build();
	}

	return $html;
}

add_filter( 'get_custom_logo', 'hocwp_theme_get_custom_logo_filter' );

function hocwp_theme_widget_posts_loop_html( $args = 0 ) {
	global $hocwp_theme;

	$widget = isset( $hocwp_theme->loop_data['widget'] ) ? $hocwp_theme->loop_data['widget'] : '';

	if ( ! ( $widget instanceof HOCWP_Theme_Widget_Posts ) ) {
		return;
	}

	if ( is_numeric( $args ) ) {
		$count = $args;
	} else {
		$count = isset( $args['count'] ) ? $args['count'] : 0;
	}

	$query = isset( $args['query'] ) ? $args['query'] : '';

	$instance = isset( $hocwp_theme->loop_data['widget_instance'] ) ? $hocwp_theme->loop_data['widget_instance'] : '';

	if ( ! is_array( $instance ) ) {
		$instance = array();
	}

	$thumbnail_size     = isset( $instance['thumbnail_size'] ) ? $instance['thumbnail_size'] : $widget->defaults['thumbnail_size'];
	$thumbnail_size     = HT_Sanitize()->size( $thumbnail_size );
	$width              = $thumbnail_size[0];
	$height             = $thumbnail_size[1];
	$crop_thumbnail     = isset( $instance['crop_thumbnail'] ) ? $instance['crop_thumbnail'] : $widget->defaults['crop_thumbnail'];
	$show_excerpt       = isset( $instance['show_excerpt'] ) ? (bool) $instance['show_excerpt'] : $widget->defaults['show_excerpt'];
	$show_date          = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : $widget->defaults['show_date'];
	$show_author        = isset( $instance['show_author'] ) ? (bool) $instance['show_author'] : $widget->defaults['show_author'];
	$show_comment_count = isset( $instance['show_comment_count'] ) ? (bool) $instance['show_comment_count'] : $widget->defaults['show_comment_count'];
	$display_type       = isset( $instance['display_type'] ) ? $instance['display_type'] : $widget->defaults['display_type'];

	$container_tag = 'div';

	$list = isset( $hocwp_theme->loop_data['list'] ) ? $hocwp_theme->loop_data['list'] : false;

	if ( $list ) {
		$container_tag = 'li';
	}

	$full_width = false;

	if ( 0 == $count && 'full_first' == $display_type ) {
		$full_width = true;
	} elseif ( $query instanceof WP_Query && 'full_last' == $display_type && $count == ( $query->post_count - 1 ) ) {
		$full_width = true;
	} elseif ( 'full_odd' == $display_type && ( ( $count + 1 ) % 2 != 0 ) ) {
		$full_width = true;
	} elseif ( 'full_even' == $display_type && ( ( $count + 1 ) % 2 == 0 ) ) {
		$full_width = true;
	}

	$class = 'loop-item';

	if ( $full_width ) {
		$class .= ' full-width-item';
	}

	do_action( 'hocwp_theme_article_before', array( 'container' => $container_tag, 'class' => $class ) );

	if ( ! empty( $width ) || ! empty( $height ) ) {
		$size = array(
			$width,
			$height,
			'crop' => $crop_thumbnail
		);

		if ( ! empty( $width ) ) {
			$size['width'] = $width;
		}

		if ( ! empty( $height ) ) {
			$size['height'] = $height;
		}

		$img_class = 'alignleft';

		if ( $full_width ) {
			$size      = 'full';
			$img_class = 'alignnone';
		}

		hocwp_theme_post_thumbnail_html( $size, array( 'post_link' => true, 'class' => $img_class ) );
	}

	$title_length = isset( $instance['title_length'] ) ? absint( $instance['title_length'] ) : '';

	$title = get_the_title();

	$before = '<a class="post-link" href="' . get_the_permalink() . '" title="' . esc_attr( $title ) . '">';

	if ( is_numeric( $title_length ) ) {
		$title = HT()->substr( $title, $title_length );
	}

	HT()->wrap_text( $title, $before, '</a>', true );

	if ( $show_date || $show_author || $show_comment_count ) {
		?>
        <div class="entry-meta meta entry-byline">
			<?php
			if ( $show_date ) {
				hocwp_theme_post_date();
			}

			if ( $show_author ) {
				hocwp_theme_post_author();
			}

			if ( $show_comment_count ) {
				hocwp_theme_comments_popup_link();
			}
			?>
        </div>
		<?php
	}

	if ( $show_excerpt ) {
		$excerpt        = get_the_excerpt();
		$excerpt_length = isset( $instance['excerpt_length'] ) ? absint( $instance['excerpt_length'] ) : '';

		if ( is_numeric( $excerpt_length ) ) {
			$more    = apply_filters( 'excerpt_more', '' );
			$excerpt = HT()->substr( $excerpt, $excerpt_length, $more );
		}

		HT()->wrap_text( $excerpt, '<div class="entry-summary">', '</div>', true );
	}

	do_action( 'hocwp_theme_article_after', array( 'container' => $container_tag ) );
}

function hocwp_theme_loop_before() {

}

add_action( 'hocwp_theme_loop_before', 'hocwp_theme_loop_before' );

function hocwp_theme_loop( $query ) {
	global $hocwp_theme;
	$hocwp_theme->loop_data['custom_query'] = true;

	if ( ! ( $query instanceof WP_Query ) ) {
		global $wp_query;
		$query = $wp_query;

		$hocwp_theme->loop_data['custom_query'] = false;
	}

	$hocwp_theme->loop_data['query'] = $query;

	$content_none = isset( $hocwp_theme->loop_data['content_none'] ) ? $hocwp_theme->loop_data['content_none'] : '';

	$template = isset( $hocwp_theme->loop_data['template'] ) ? $hocwp_theme->loop_data['template'] : '';

	if ( $query->have_posts() ) {
		$class        = array( 'loop' );
		$post_types   = $query->get( 'post_type' );
		$post_types   = (array) $post_types;
		$post_types[] = 'post';

		if ( ! HT()->is_file( $template ) ) {
			$post_types[] = sanitize_html_class( $template );
		}

		$post_types = array_unique( $post_types );
		$post_types = array_filter( $post_types );

		foreach ( $post_types as $post_type ) {
			$class[] = 'loop-' . sanitize_html_class( $post_type );
		}

		$on_sidebar = isset( $hocwp_theme->loop_data['on_sidebar'] ) ? $hocwp_theme->loop_data['on_sidebar'] : false;

		if ( $on_sidebar ) {
			$class[] = 'on-sidebar widget-content';
		}

		$class = implode( ' ', $class );
		$count = 0;

		$hocwp_theme->loop_data['count'] = $count;

		$class = apply_filters( 'hocwp_theme_loop_container_class', $class, $query );

		echo '<div class="' . $class . '">';

		do_action( 'hocwp_theme_loop_before' );

		$template_valid = true;

		if ( ! empty( $template ) ) {
			$name = HT_Sanitize()->prefix( $template, 'loop' );
			$name = HT_Sanitize()->extension( $name, 'php' );
			$path = HOCWP_Theme()->custom_path . '/views/' . $name;

			$template_valid = file_exists( $path );
		}

		$list = isset( $hocwp_theme->loop_data['list'] ) ? $hocwp_theme->loop_data['list'] : false;

		if ( empty( $template ) ) {
			$template = 'post';
		}

		if ( $list ) {
			echo '<ul>';
		}

		while ( $query->have_posts() ) {
			$query->the_post();

			do_action( 'hocwp_theme_in_loop_before' );

			if ( $template_valid ) {
				hocwp_theme_load_custom_loop( $template );
			} else {
				$instance = isset( $hocwp_theme->loop_data['widget_instance'] ) ? $hocwp_theme->loop_data['widget_instance'] : '';

				if ( $on_sidebar && is_array( $instance ) ) {
					hocwp_theme_widget_posts_loop_html( array( 'count' => $count, 'query' => $query ) );
				} else {
					do_action( 'hocwp_theme_the_title' );
				}
			}

			do_action( 'hocwp_theme_in_loop_after' );

			$count ++;
		}

		wp_reset_postdata();

		if ( $list ) {
			echo '</ul>';
		}

		do_action( 'hocwp_theme_loop_after' );

		echo '</div>';

		$pa = isset( $hocwp_theme->loop_data['pagination_args'] ) ? $hocwp_theme->loop_data['pagination_args'] : array();

		if ( true === $pa || is_array( $pa ) || ( null !== $pa && $pa ) ) {
			if ( ! is_array( $pa ) ) {
				$pa = array();
			}

			$pa['query'] = $query;

			HT_Frontend()->pagination( $pa );
		}
	} elseif ( false !== $content_none ) {
		if ( HT()->is_file( $content_none ) ) {
			load_template( $content_none );
		} else {
			hocwp_theme_load_content_none();
		}
	}

	hocwp_theme_reset_loopdata();
}

add_action( 'hocwp_theme_loop', 'hocwp_theme_loop' );

function hocwp_theme_loop_after() {

}

add_action( 'hocwp_theme_loop_after', 'hocwp_theme_loop_after' );

/**
 * Social share buttons
 *
 * @param array $args
 */
function hocwp_theme_socials( $args = array() ) {
	$defaults = array(
		'socials' => array(
			'facebook' => array(
				'base'  => 'https://www.facebook.com/sharer.php?u=[URL]',
				'class' => 'btn btn-primary btn-sm'
			),
			'gplus'    => array(
				'base'  => 'https://plus.google.com/share?url=[URL]',
				'class' => 'btn btn-danger btn-sm',
				'name'  => 'Google+'
			),
			'twitter'  => array(
				'base'     => 'https://twitter.com/intent/tweet?url=[URL]',
				'class'    => 'btn btn-info btn-sm',
				'username' => ''
			),
			'linkedin' => array(
				'base'  => 'https://www.linkedin.com/cws/share?url=[URL]',
				'class' => 'btn btn-primary btn-sm'
			),
			'email'    => array(
				'base'  => 'mailto:?subject=[TITLE]&body=[URL]',
				'class' => 'btn btn-default'
			)
		),
		'url'     => '',
		'post_id' => get_the_ID(),
		'title'   => ''
	);

	$args    = wp_parse_args( $args, $defaults );
	$url     = $args['url'];
	$post_id = $args['post_id'];

	if ( empty( $url ) ) {
		if ( HT()->is_positive_number( $post_id ) ) {
			$url = get_permalink( $post_id );
		} else {
			$url = HT_Util()->get_current_url( true );
		}
	}

	$title = $args['title'];

	if ( empty( $title ) ) {
		$title = get_the_title( $post_id );
	}

	$socials = $args['socials'];

	if ( empty( $url ) ) {
		return;
	}

	$url = urlencode( $url );
	?>
    <div class="social share-tools">
		<?php
		$link = '<a href="%s" rel="nofollow" target="%s" class="%s" title="%s" data-new-tab="1">%s</a>';

		$target = '_blank';

		foreach ( $socials as $social => $data ) {
			$base   = $data['base'];
			$base   = str_replace( '[URL]', $url, $base );
			$base   = str_replace( '[TITLE]', $title, $base );
			$class  = $data['class'];
			$class  .= ' ' . sanitize_html_class( $social );
			$name   = isset( $data['name'] ) ? $data['name'] : ucwords( $social );
			$target = '_blank';

			if ( 'twitter' == $social ) {
				$params = array(
					'original_referer' => urlencode( home_url( '/' ) ),
					'source'           => 'tweetbutton',
					'text'             => urlencode( $title )
				);

				if ( isset( $data['username'] ) && ! empty( $data['username'] ) ) {
					$params['via'] = $data['username'];
				}

				$base = add_query_arg( $params, $base );
			} elseif ( 'email' == $social ) {
				$target = '_self';
			}

			$real_name = strip_tags( $name );

			if ( empty( $real_name ) ) {
				$real_name = ucwords( $social );
			}

			printf( $link, esc_url( $base ), $target, $class, esc_attr( sprintf( __( 'Share on %s', 'hocwp-theme' ), $real_name ) ), $name );
		}

		if ( current_user_can( 'publish_posts' ) ) {
			$base = 'https://www.google.com/webmasters/tools/submit-url';

			$params = array(
				'urlnt' => $url
			);

			$base  = add_query_arg( $params, $base );
			$class = 'btn btn-submit-url';
			$name  = __( 'Submit URL', 'hocwp-theme' );
			printf( $link, esc_url( $base ), $target, $class, esc_attr( __( 'Submit URL to Google Search Console', 'hocwp-theme' ) ), $name );
		}
		?>
    </div>
	<?php
	unset( $defaults, $args, $url, $post_id, $title, $socials, $social, $base, $class, $name, $params );
}

function hocwp_theme_get_option( $name, $default = '', $base = 'general' ) {
	return HT_Util()->get_theme_option( $name, $default, $base );
}

function hocwp_theme_get_option_home( $name, $default = '' ) {
	return HT_Util()->get_theme_option( $name, $default, 'home' );
}

function hocwp_theme_pre_get_posts_action( $query ) {
	if ( $query instanceof WP_Query && $query->is_main_query() ) {
		if ( $query->is_home() ) {
			$query->set( 'posts_per_page', HT_Util()->get_posts_per_page( true ) );
		}
	}
}

add_action( 'pre_get_posts', 'hocwp_theme_pre_get_posts_action' );

function _hocwp_theme_facebook_javascript_sdk( $app_id, $version = '2.11', $language = 'vi_VN' ) {
	_deprecated_function( __FUNCTION__, '6.5.8', 'HT_Util()->load_facebook_javascript_sdk' );

	if ( ! empty( $app_id ) ) {
		$args = array(
			'app_id'  => $app_id,
			'version' => $version,
			'locale'  => $language,
			'load'    => true
		);

		HT_Util()->load_facebook_javascript_sdk( $args );
	}
}

function hocwp_theme_facebook_javascript_sdk( $app_id = '' ) {
	if ( empty( $app_id ) ) {
		$sdk = hocwp_theme_get_option( 'facebook_sdk_javascript', '', 'social' );

		if ( ! empty( $sdk ) ) {
			echo $sdk;

			return;
		}

		$app_id = hocwp_theme_get_option( 'facebook_app_id', '', 'social' );
	}

	if ( ! empty( $app_id ) ) {
		HT_Util()->load_facebook_javascript_sdk( array( 'app_id' => $app_id ) );
	}
}

add_action( 'hocwp_theme_facebook_javascript_sdk', 'hocwp_theme_facebook_javascript_sdk' );

function hocwp_theme_reset_loopdata() {
	global $hocwp_theme;

	if ( isset( $hocwp_theme->loop_data['custom_query'] ) && $hocwp_theme->loop_data['custom_query'] ) {
		wp_reset_postdata();
	}

	$hocwp_theme->loop_data = array();
}

function hocwp_theme_script_loader_tag_filter( $tag ) {
	$tag = str_replace( "type='text/javascript'", '', $tag );

	if ( HT()->string_contain( $tag, 'kit' ) && HT()->string_contain( $tag, 'fontawesome' ) ) {
		$tag = HT()->add_html_attribute( 'script', $tag, 'crossorigin="anonymous"' );
	}

	$tag = str_replace( '  ', ' ', $tag );

	return $tag;
}

add_filter( 'script_loader_tag', 'hocwp_theme_script_loader_tag_filter' );

function hocwp_theme_style_loader_tag_filter( $tag ) {
	$tag = str_replace( "type='text/css'", '', $tag );

	return $tag;
}

add_filter( 'style_loader_tag', 'hocwp_theme_style_loader_tag_filter' );

function hocwp_theme_get_archive_title( $prefix = true ) {
	return HT_Frontend()->get_archive_title( $prefix );
}

function hocwp_theme_the_archive_title( $prefix = true ) {
	$title = hocwp_theme_get_archive_title( $prefix );
	HT()->wrap_text( $title, '<h1 class="archive-title main-title">', '</h1>', true );
}

function hocwp_theme_wp_title_filter( $title ) {
	$paged = HT_Util()->get_paged();
	$sep   = HT_Util()->get_title_separator();

	if ( 1 < $paged ) {
		$add = sprintf( _x( 'Page %d', 'pagination', 'hocwp-theme' ), $paged );
		$add = $sep . ' ' . $add;

		if ( false == strpos( $title, $add ) ) {
			$title .= ' ' . $add;
		}

		unset( $add );
	}

	unset( $paged, $sep );

	return $title;
}

add_filter( 'wp_title', 'hocwp_theme_wp_title_filter' );
add_filter( 'wpseo_title', 'hocwp_theme_wp_title_filter' );

function hocwp_theme_check_endpoint() {
	global $wp_query;

	if ( is_search() ) {
		$s = get_search_query();

		if ( empty( $s ) ) {
			$redirect = HT_Options()->get_tab( 'redirect_empty_search', '', 'reading' );

			if ( $redirect ) {
				wp_redirect( home_url( '/' ) );
				exit;
			}
		}
	}

	$random = HT_Util()->get_theme_option( 'random', '', 'reading' );

	if ( 1 == $random && isset( $wp_query->query_vars['random'] ) ) {
		$post_types   = get_post_types( array( 'public' => true, '_builtin' => false ) );
		$post_types[] = 'post';

		$args = array(
			'fields'         => 'ids',
			'post_type'      => $post_types,
			'posts_per_page' => 1,
			'orderby'        => 'rand'
		);

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			wp_redirect( get_permalink( $query->posts[0] ) );
			exit;
		}
	}
}

add_action( 'wp', 'hocwp_theme_check_endpoint' );

function hocwp_theme_fix_paginate_links( $link ) {
	if ( false !== strpos( $link, '#' ) ) {
		$parts = parse_url( $link );

		if ( isset( $parts['fragment'] ) && ! empty( $parts['fragment'] ) ) {
			parse_str( $parts['query'], $query );

			if ( HT()->array_has_value( $query ) ) {
				$link = strtok( $link, '?' );
				$link = add_query_arg( $query, $link );
			}

			unset( $query );
		}

		unset( $parts );
	}

	return $link;
}

add_filter( 'paginate_links', 'hocwp_theme_fix_paginate_links' );

function hocwp_theme_print_url_params_as_hidden( $excludes ) {
	if ( ! is_array( $excludes ) ) {
		$excludes = array();
	}

	$url   = HT_Util()->get_current_url( true );
	$parts = parse_url( $url );

	if ( isset( $parts['query'] ) ) {
		parse_str( $parts['query'], $query );

		if ( HT()->array_has_value( $query ) ) {
			foreach ( $query as $key => $value ) {
				if ( in_array( $key, $excludes ) ) {
					continue;
				}

				echo '<input type="hidden" value="' . esc_attr( $value ) . '" name="' . esc_attr( $key ) . '">' . PHP_EOL;
			}
		}
	}
}

add_action( 'hocwp_theme_print_url_params_as_hidden', 'hocwp_theme_print_url_params_as_hidden' );

function hocwp_theme_fix_not_found_paged() {
	if ( defined( 'HOCWP_THEME_USE_DEFAULT_TEMPLATE' ) && HOCWP_THEME_USE_DEFAULT_TEMPLATE ) {
		$post_types = get_post_types( array( '_builtin' => false, 'public' => true ), 'objects' );

		if ( HT()->array_has_value( $post_types ) ) {
			foreach ( $post_types as $post_type ) {
				if ( $post_type instanceof WP_Post_Type ) {
					if ( is_post_type_archive( $post_type->name ) || ( HT()->array_has_value( $post_type->taxonomies ) && is_tax( $post_type->taxonomies ) ) ) {
						include get_template_directory() . '/archive.php';
						exit;
					} elseif ( is_singular( $post_type->name ) ) {
						include get_template_directory() . '/single.php';
						exit;
					}
				}
			}
		}
	}

	if ( is_page() ) {
		// Load blog page when page chosen in reading settings.
		$blog = HT_Options()->get_tab( 'blog_page', 'reading' );

		if ( ! empty( $blog ) && is_page( $blog ) && ! is_page_template( 'custom/page-templates/blog.php' ) ) {
			$path = HOCWP_Theme()->custom_path . '/page-templates/blog.php';

			if ( file_exists( $path ) ) {
				require_once $path;
				exit;
			}
		}
	}

	global $wp_query;

	if ( ! $wp_query->have_posts() ) {
		$paged = $wp_query->get( 'paged' );

		if ( 1 < $paged ) {
			$url = HT_Util()->get_current_url( true );
			$url = str_replace( '/page/' . $paged, '', $url );
			wp_redirect( $url );
			exit;
		}
	}
}

add_action( 'template_redirect', 'hocwp_theme_fix_not_found_paged' );

function hocwp_theme_custom_nav_menu_css_class_filter( $classes, $item ) {
	if ( $item instanceof WP_Post && ( 'custom' == $item->type || 'custom' == $item->object ) ) {
		$theme_list_social = get_post_meta( $item->ID, 'theme_list_social', true );

		if ( 1 == $theme_list_social ) {
			$classes[] = 'social-item';
			$classes[] = 'list-social-item';
			$classes[] = 'menu-item-type-social';
			$classes[] = 'menu-item-social';

			if ( ! empty( $item->post_name ) ) {
				$classes[] = $item->post_name;
				$classes[] = 'item-social-' . $item->post_name;
			}

			$classes = apply_filters( 'hocwp_theme_menu_item_social_classes', $classes, $item );
		}
	}

	return $classes;
}

add_filter( 'nav_menu_css_class', 'hocwp_theme_custom_nav_menu_css_class_filter', 10, 2 );