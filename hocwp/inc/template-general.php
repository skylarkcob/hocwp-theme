<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_module_site_header() {
	hocwp_theme_load_custom_module( 'module-site-header' );
}

add_action( 'hocwp_theme_module_site_header', 'hocwp_theme_module_site_header' );

function hocwp_theme_module_site_footer() {
	hocwp_theme_load_custom_module( 'module-site-footer' );
}

add_action( 'hocwp_theme_module_site_footer', 'hocwp_theme_module_site_footer' );

function hocwp_theme_template_index() {
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

function hocwp_theme_template_404() {
	hocwp_theme_load_custom_template( 'template-404' );
}

add_action( 'hocwp_theme_template_404', 'hocwp_theme_template_404' );

function hocwp_theme_template_archive() {
	if ( is_post_type_archive() ) {
		global $post_type;

		if ( ! empty( $post_type ) ) {
			$file = HOCWP_THEME_CUSTOM_PATH . '/views/template-archive-' . $post_type . '.php';

			if ( file_exists( $file ) ) {
				load_template( $file );

				return;
			}

			$tmp  = str_replace( '_', '-', $post_type );
			$file = HOCWP_THEME_CUSTOM_PATH . '/views/template-archive-' . $tmp . '.php';

			if ( file_exists( $file ) ) {
				load_template( $file );

				return;
			}
		}
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$object = get_queried_object();
		global $post_type;

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
		}

	}
	hocwp_theme_load_custom_template( 'template-archive' );
}

add_action( 'hocwp_theme_template_archive', 'hocwp_theme_template_archive' );

function hocwp_theme_template_search() {
	hocwp_theme_load_custom_template( 'template-search' );
}

add_action( 'hocwp_theme_template_search', 'hocwp_theme_template_search' );

function hocwp_theme_widget_title_filter( $title ) {
	if ( ! is_admin() && ! empty( $title ) ) {
		$first = substr( $title, 0, 1 );

		if ( '!' == $first ) {
			$title = '';
		} else {
			if ( ! HT()->string_contain( $title, '</span>' ) ) {
				$title = '<span>' . $title . '</span>';
			}
		}
	}

	return $title;
}

add_filter( 'widget_title', 'hocwp_theme_widget_title_filter' );

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
		echo $before_title . $title . $after_title;
	} else {
		echo '<div class="widget-content">';
	}
}

add_action( 'hocwp_theme_widget_title', 'hocwp_theme_widget_title', 10, 3 );

function hocwp_theme_widget_before( $args, $instance, $widget ) {
	$before_widget = isset( $args['before_widget'] ) ? $args['before_widget'] : '<div class="widget">';
	echo $before_widget;

	$show_title = isset( $instance['show_title'] ) ? (bool) $instance['show_title'] : true;

	if ( $show_title ) {
		do_action( 'hocwp_theme_widget_title', $args, $instance, $widget );
	}
}

add_action( 'hocwp_theme_widget_before', 'hocwp_theme_widget_before', 9, 3 );

function hocwp_theme_widget_after( $args, $instance, $widget ) {
	$show_title = isset( $instance['show_title'] ) ? (bool) $instance['show_title'] : true;

	if ( $show_title ) {
		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title, $instance, $widget->id_base );

		if ( ! $title ) {
			echo '</div>';
		}
	}

	$after_widget = isset( $args['after_widget'] ) ? $args['after_widget'] : '</div>';

	echo $after_widget;
}

add_action( 'hocwp_theme_widget_after', 'hocwp_theme_widget_after', 99, 3 );

function hocwp_theme_module_sidebar() {
	if ( ! did_action( 'hocwp_theme_module_sidebar' ) ) {
		_doing_it_wrong( __FUNCTION__, __( 'Please call function get_sidebar instead!', 'hocwp-theme' ), '5.2.2' );
	}

	hocwp_theme_load_views( 'module-sidebar' );
}

add_action( 'hocwp_theme_module_sidebar', 'hocwp_theme_module_sidebar' );

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
	} elseif ( is_404() && is_active_sidebar( '404' ) ) {
		$sidebar = '404';
	}

	unset( $dynamic_sidebar );

	return $sidebar;
}

add_filter( 'hocwp_theme_sidebar', 'hocwp_theme_sidebar_filter' );

function hocwp_theme_dynamic_sidebar_params_filter( $params ) {
	$wrap = apply_filters( 'hocwp_theme_wrap_widget', true, $params );

	if ( isset( $params[0] ) ) {
		$args = $params[0];

		$id = isset( $args['id'] ) ? $args['id'] : '';

		$wrap = ( $wrap && ! HT()->string_contain( $id, 'sidebar' ) ) ? true : false;

		if ( isset( $args['before_widget'] ) ) {
			$before_widget = $args['before_widget'];
			$before_widget = str_replace( '<li', '<section', $before_widget );
		} else {
			$before_widget = '<section class="widget">';
		}

		if ( $wrap ) {
			$before_widget .= '<div class="widget-inner">';
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
			$before_title = '</div>' . $before_title;
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
			$after_title .= '<div class="widget-content">';
		}

		$params[0]['after_title'] = $after_title;

		if ( isset( $args['after_widget'] ) ) {
			$after_widget = $args['after_widget'];
			$after_widget = str_replace( '</li>', '</section>', $after_widget );
		} else {
			$after_widget = '</section>';
		}

		if ( $wrap ) {
			$after_widget = '</div>' . $after_widget;
		}

		$params[0]['after_widget'] = $after_widget;
	}

	return $params;
}

add_filter( 'dynamic_sidebar_params', 'hocwp_theme_dynamic_sidebar_params_filter' );

function hocwp_theme_template_single() {
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

function hocwp_theme_content_area_before() {
	?>
	<div id="primary" class="content-area">
		<main id="main" class="site-main">
			<?php
			}

			add_action( 'hocwp_theme_content_area_before', 'hocwp_theme_content_area_before', 3 );

			function hocwp_theme_content_area_after() {
			?>
		</main>
		<!-- #main -->
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
		$output = HOCWP_Theme::add_html_attribute( 'div', $output, $attr );
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

	$args['menu_class'] = trim( $menu_class );

	unset( $menu_class );

	return $args;
}

add_filter( 'wp_nav_menu_args', 'hocwp_theme_wp_nav_menu_args_filter' );

function hocwp_theme_wp_page_menu_args_filter( $args ) {
	$container_class   = isset( $args['container_class'] ) ? $args['container_class'] : '';
	$container_classes = explode( ' ', $container_class );

	$menu_class   = isset( $args['menu_class'] ) ? $args['menu_class'] : '';
	$menu_classes = explode( ' ', $menu_class );

	$menu_classes = array_merge( $menu_classes, $container_classes );

	$menu_classes = array_filter( $menu_classes );
	$menu_classes = array_unique( $menu_classes );

	$args['menu_class'] = implode( ' ', $menu_classes );

	unset( $container_class, $container_classes, $menu_class, $menu_classes );

	return $args;
}

add_filter( 'wp_page_menu_args', 'hocwp_theme_wp_page_menu_args_filter' );

function hocwp_theme_menu_button( $control = 'main-menu' ) {
	ob_start();
	?>
	<button class="menu-toggle" aria-controls="<?php echo $control; ?>" aria-expanded="false">
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

	$position = isset( $args['position'] ) ? $args['position'] : 'left';

	if ( 'left' !== $position ) {
		$position = 'right';
	}

	$container_class .= ' position-' . $position;

	$button = hocwp_theme_menu_button();
	$button .= '<ul id="%1$s" class="%2$s">%3$s</ul>';

	$theme_location = isset( $args['theme_location'] ) ? $args['theme_location'] : 'menu-1';

	$defaults = array(
		'theme_location'  => $theme_location,
		'container_id'    => 'site-navigation',
		'menu_id'         => 'main-menu',
		'container_class' => $container_class,
		'items_wrap'      => $button
	);

	$args = wp_parse_args( $args, $defaults );

	$args = apply_filters( 'hocwp_theme_main_menu_args', $args );

	wp_nav_menu( $args );
}

add_action( 'hocwp_theme_main_menu', 'hocwp_theme_main_menu' );

function hocwp_theme_mobile_menu( $args ) {
	$args = (array) $args;

	$container_class = 'mobile-menu';

	$position = isset( $args['position'] ) ? $args['position'] : 'left';

	if ( 'left' !== $position ) {
		$position = 'right';
	}

	$container_class .= ' position-' . $position;

	$button = hocwp_theme_menu_button( 'mobile-menu' );
	$button .= '<ul id="%1$s" class="%2$s">%3$s</ul>';

	$defaults = array(
		'theme_location'  => 'mobile',
		'container_id'    => 'mobile-navigation',
		'menu_id'         => 'mobile-menu',
		'container_class' => $container_class,
		'items_wrap'      => $button
	);

	$args = wp_parse_args( $args, $defaults );

	wp_nav_menu( $args );
}

add_action( 'hocwp_theme_mobile_menu', 'hocwp_theme_mobile_menu' );

function hocwp_theme_wp_nav_menu_items_filter( $items, $args ) {
	if ( 'mobile' == $args->theme_location ) {
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
			echo '<meta name="theme-color" content="' . $color . '">' . PHP_EOL;
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
	$options = $hocwp_theme->options;
	HT_Util()->load_facebook_javascript_sdk();
	$agent = HT()->get_user_agent();

	if ( empty( $agent ) || ! HT()->string_contain( $agent, 'Page Speed' ) || ! HT()->string_contain( $agent, 'Speed Insights' ) ) {
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
}

add_action( 'wp_footer', 'hocwp_theme_wp_footer_action' );

function hocwp_theme_site_branding_action() {
	?>
	<div class="site-branding site-logo">
		<?php
		the_custom_logo();
		do_action( 'hocwp_theme_site_branding_middle' );
		$description = get_bloginfo( 'description', 'display' );

		if ( $description || is_customize_preview() ) {
			?>
			<p class="site-description"><?php echo $description; /* WPCS: xss ok. */ ?></p>
			<?php
		}
		?>
	</div><!-- .site-branding -->
	<?php
}

add_action( 'hocwp_theme_site_branding', 'hocwp_theme_site_branding_action' );

function hocwp_theme_theme_mod_custom_logo_filter( $mod ) {
	global $hocwp_theme;
	$options      = $hocwp_theme->options;
	$logo_display = $options['general']['logo_display'];

	if ( 'image' == $logo_display ) {
		$id = isset( $options['general']['logo_image'] ) ? $options['general']['logo_image'] : '';

		if ( HOCWP_Theme::is_positive_number( $id ) ) {
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

	if ( isset( $options['general']['site_icon'] ) && HOCWP_Theme::is_positive_number( $options['general']['site_icon'] ) ) {
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
	$options      = $hocwp_theme->options;
	$logo_display = $options['general']['logo_display'];

	if ( 'image' != $logo_display ) {
		if ( 'text' == $logo_display ) {
			$text = isset( $options['general']['logo_text'] ) ? $options['general']['logo_text'] : '';

			if ( empty( $text ) ) {
				$text = get_bloginfo( 'name', 'display' );
			} else {
				$domain = HOCWP_Theme::get_domain_name( home_url() );
				$text   = str_replace( '[DOMAIN]', $domain, $text );
			}

			$text = strip_tags( $text );

			if ( ! empty( $text ) ) {
				if ( is_front_page() && is_home() ) : ?>
					<h1 class="site-title">
						<a class="navbar-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"
						   rel="home"><?php echo $text; ?></a>
					</h1>
				<?php else : ?>
					<p class="site-title">
						<a class="navbar-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"
						   rel="home"><?php echo $text; ?></a>
					</p>
					<?php
				endif;
			}
		} elseif ( 'custom' == $logo_display ) {
			if ( isset( $options['general']['logo_html'] ) ) {
				echo isset( $options['general']['logo_html'] );
			}
		}
	} else {
		if ( empty( $html ) ) {
			$html = get_bloginfo( 'name', 'display' );
			$html = HT()->wrap_text( $html, '<a href="' . esc_url( home_url( '/' ) ) . '">', '</a>' );
		}

		if ( is_home() ) {
			$html = HT()->wrap_text( $html, '<h1 class="site-title">', '</h1>' );
		} else {
			$html = HT()->wrap_text( $html, '<p class="site-title">', '</p>' );
		}
	}

	return $html;
}

add_filter( 'get_custom_logo', 'hocwp_theme_get_custom_logo_filter' );

function hocwp_theme_widget_posts_loop_html() {
	global $hocwp_theme;
	$widget = isset( $hocwp_theme->loop_data['widget'] ) ? $hocwp_theme->loop_data['widget'] : '';

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

	$container_tag = 'div';

	$list = isset( $hocwp_theme->loop_data['list'] ) ? $hocwp_theme->loop_data['list'] : false;

	if ( $list ) {
		$container_tag = 'li';
	}

	do_action( 'hocwp_theme_article_before', array( 'container' => $container_tag ) );

	if ( ! empty( $width ) || ! empty( $height ) ) {
		if ( has_post_thumbnail() ) {
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

			hocwp_theme_post_thumbnail( $size, array( 'post_link' => true, 'class' => 'alignleft' ) );
		}
	}

	$title_length = isset( $instance['title_length'] ) ? absint( $instance['title_length'] ) : '';
	$title        = get_the_title();
	$before       = '<a class="post-link" href="' . get_the_permalink() . '" title="' . $title . '">';

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

		if ( $list ) {
			echo '<ul>';
		}

		while ( $query->have_posts() ) {
			$query->the_post();

			if ( $template_valid ) {
				hocwp_theme_load_custom_loop( $template );
			} else {
				$instance = isset( $hocwp_theme->loop_data['widget_instance'] ) ? $hocwp_theme->loop_data['widget_instance'] : '';

				if ( $on_sidebar && is_array( $instance ) ) {
					hocwp_theme_widget_posts_loop_html();
				} else {
					do_action( 'hocwp_theme_the_title' );
				}
			}

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

	$args = wp_parse_args( $args, $defaults );
	$url  = $args['url'];

	if ( empty( $url ) ) {
		$post_id = $args['post_id'];

		if ( HOCWP_Theme::is_positive_number( $post_id ) ) {
			$url = get_permalink( $post_id );
		} else {
			$url = hocwp_get_current_url();
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

		foreach ( $socials as $social => $data ) {
			$base  = $data['base'];
			$base  = str_replace( '[URL]', $url, $base );
			$base  = str_replace( '[TITLE]', $title, $base );
			$class = $data['class'];
			$class .= ' ' . sanitize_html_class( $social );
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

remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );

function hocwp_theme_pre_get_posts_action( $query ) {
	if ( $query instanceof WP_Query && $query->is_main_query() ) {
		if ( $query->is_home() ) {
			$query->set( 'posts_per_page', HT_Util()->get_posts_per_page( true ) );
		}
	}
}

add_action( 'pre_get_posts', 'hocwp_theme_pre_get_posts_action' );

function _hocwp_theme_facebook_javascript_sdk( $app_id, $version = '2.11', $language = 'vi_VN' ) {
	if ( ! empty( $app_id ) ) {
		?>
		<div id="fb-root"></div>
		<script>(function (d, s, id) {
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) return;
				js = d.createElement(s);
				js.id = id;
				js.src = 'https://connect.facebook.net/<?php echo $language; ?>/sdk.js#xfbml=1&version=v<?php echo $version; ?>&appId=<?php echo $app_id; ?>';
				fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));</script>
		<?php
	}
}

function hocwp_theme_facebook_javascript_sdk( $app_id = '' ) {
	if ( ! empty( $app_id ) ) {
		_hocwp_theme_facebook_javascript_sdk( $app_id );

		return;
	}

	$sdk = hocwp_theme_get_option( 'facebook_sdk_javascript', '', 'social' );

	if ( ! empty( $sdk ) ) {
		echo $sdk;

		return;
	}

	$app_id = hocwp_theme_get_option( 'facebook_app_id', '', 'social' );
	_hocwp_theme_facebook_javascript_sdk( $app_id );
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