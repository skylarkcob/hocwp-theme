<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

final class HOCWP_Theme_Frontend extends HOCWP_Theme_Utility {
	public static $instance;

	protected function __construct() {
		if ( ! is_admin() ) {
			add_filter( 'nav_menu_link_attributes', array( $this, 'nav_menu_link_attributes_filter' ), 99, 4 );
		}
	}

	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function site_logo( $args = array(), $echo = true ) {
		$logo       = get_custom_logo();
		$site_title = get_bloginfo( 'name' );

		$defaults = array(
			'logo'        => '%1$s<span class="screen-reader-text">%2$s</span>',
			'logo_class'  => 'site-logo',
			'title'       => '<a href="%1$s">%2$s</a>',
			'title_class' => 'site-title',
			'home_wrap'   => '<h1 class="%1$s">%2$s</h1>',
			'single_wrap' => '<div class="%1$s faux-heading">%2$s</div>',
			'condition'   => ( is_front_page() || is_home() ) && ! is_page(),
		);

		$args = wp_parse_args( $args, $defaults );

		$args = apply_filters( 'hocwp_theme_site_logo_args', $args, $defaults );

		if ( has_custom_logo() ) {
			$contents  = sprintf( $args['logo'], $logo, esc_html( $site_title ) );
			$classname = $args['logo_class'];
		} else {
			$contents  = sprintf( $args['title'], esc_url( get_home_url( null, '/' ) ), esc_html( $site_title ) );
			$classname = $args['title_class'];
		}

		$wrap = $args['condition'] ? 'home_wrap' : 'single_wrap';

		$html = sprintf( $args[ $wrap ], $classname, $contents );

		$html = apply_filters( 'hocwp_theme_site_logo', $html, $args, $classname, $contents );

		if ( $echo ) {
			echo $html;
		}

		return $html;
	}

	public function nav_menu_link_attributes_filter( $atts, $item, $args, $depth ) {
		if ( is_object( $args ) ) {
			$atts['data-object']    = $item->object;
			$atts['data-object-id'] = $item->object_id;
			$atts['data-type']      = $item->type;
			$atts['data-depth']     = esc_attr( $depth );
		}

		return $atts;
	}

	public function bootstrap_nav_menu( $args = array() ) {
		$args['fallback_cb'] = 'HOCWP_Theme_Walker_Nav_Menu_Bootstrap::fallback';
		$args['walker']      = new HOCWP_Theme_Walker_Nav_Menu_Bootstrap();

		$container_class = isset( $args['container_class'] ) ? $args['container_class'] : '';

		$container_class .= ' collapse navbar-collapse';

		$args['container_class'] = trim( $container_class );

		$menu_class = isset( $args['menu_class'] ) ? $args['menu_class'] : '';

		$menu_class .= ' navbar-nav mr-auto';

		$args['menu_class'] = trim( $menu_class );

		wp_nav_menu( $args );
	}

	public function wp_nav_menu_helper( $args = array() ) {
		$container_class = isset( $args['container_class'] ) ? $args['container_class'] : '';

		$position = isset( $args['position'] ) ? $args['position'] : 'left';

		if ( 'left' !== $position ) {
			$position = 'right';
		}

		$container_class .= ' position-' . $position;

		$button = isset( $args['mobile_button'] ) ? $args['mobile_button'] : '';

		$button_control = isset( $args['button_control'] ) ? $args['button_control'] : '';

		if ( empty( $button ) ) {
			$button = hocwp_theme_menu_button( $button_control );
		}

		$mobile_button_id = isset( $args['mobile_button_id'] ) ? $args['mobile_button_id'] : '';

		if ( empty( $mobile_button_id ) ) {
			if ( empty( $button_control ) ) {
				$button_control = 'main-menu';
			}

			$mobile_button_id = 'toggle-' . $button_control;
		}

		$button .= '<ul id="%1$s" class="%2$s" data-button-control="' . esc_attr( $mobile_button_id ) . '">%3$s</ul>';

		$defaults = array(
			'items_wrap' => $button
		);

		$args = wp_parse_args( $args, $defaults );

		$args['container_class'] = trim( $container_class );

		wp_nav_menu( $args );
	}

	public static function pagination( $args = array() ) {
		if ( $args instanceof WP_Query ) {
			$args = array( 'query' => $args );
		}

		if ( function_exists( 'hocwp_pagination' ) ) {
			hocwp_pagination( $args );

			return;
		}

		$defaults = array(
			'query'         => $GLOBALS['wp_query'],
			'dynamic_size'  => 1,
			'show_all'      => false,
			'label'         => '',
			'end_size'      => 1,
			'mid_size'      => 2,
			'first_last'    => 0,
			'current_total' => 0,
			'class'         => 'hocwp-pagination'
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'hocwp_theme_pagination_args', $args );

		$query = $args['query'];

		if ( ! ( $query instanceof WP_Query ) ) {
			return;
		}

		$total = $query->max_num_pages;

		if ( 2 > $total ) {
			return;
		}

		$big = 999999999;

		if ( isset( $args['paged'] ) && is_numeric( $args['paged'] ) ) {
			$paged = $args['paged'];
		} else {
			$paged = self::get_paged();
		}

		$current = max( 1, $paged );

		$pla = array(
			'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format'  => '?paged=%#%',
			'current' => $current,
			'total'   => $total,
			'type'    => 'array'
		);

		$args = wp_parse_args( $args, $pla );
		$next = isset( $args['next'] ) ? $args['next'] : '';

		$load_more = isset( $args['load_more'] ) ? $args['load_more'] : '';

		if ( $load_more ) {
			$next = true;
		}

		if ( empty( $next ) ) {
			$next = isset( $args['next_text'] ) ? $args['next_text'] : '';
		}

		$prev = isset( $args['prev'] ) ? $args['prev'] : '';

		if ( empty( $prev ) ) {
			$prev = isset( $args['prev_text'] ) ? $args['prev_text'] : '';
		}

		if ( ! empty( $next ) || ! empty( $prev ) ) {
			$args['prev_next'] = true;

			if ( is_string( $next ) && ! empty( $next ) ) {
				$args['next_text'] = $next;
			}

			if ( is_string( $prev ) && ! empty( $prev ) ) {
				$args['prev_text'] = $prev;
			}
		}

		if ( empty( $next ) && empty( $prev ) ) {
			$args['prev_next'] = false;
		}

		$dynamic_size = HT()->convert_to_boolean( $args['dynamic_size'] );

		$first_last = isset( $args['first_last'] ) ? (bool) $args['first_last'] : false;

		if ( ! $first_last ) {
			if ( isset( $args['first'] ) && isset( $args['last'] ) ) {
				$first_last = true;
			}
		}

		if ( $load_more || ! empty( $load_more ) ) {
			$dynamic_size = false;

			$args['mid_size'] = $big;
			$args['end_size'] = $big;
		}

		if ( $dynamic_size ) {
			$show_all = HT()->convert_to_boolean( $args['show_all'] );

			if ( $show_all ) {
				$count = 0;
				$label = $args['label'];

				if ( ! empty( $label ) ) {
					$count ++;
				}

				$end_size = absint( $args['end_size'] );
				$count += $end_size;
				$mid_size = absint( $args['mid_size'] );
				$count += $mid_size;
				$prev_next = $args['prev_next'];

				if ( 1 == $prev_next ) {
					$prev_text = $args['prev_text'];

					if ( ! empty( $prev_text ) ) {
						$count ++;
					}

					$next_text = $args['next_text'];

					if ( ! empty( $next_text ) ) {
						$count ++;
					}
				}

				if ( 1 == $first_last || true == $first_last ) {
					$first_text = $args['first_text'];

					if ( ! empty( $first_text ) ) {
						$count ++;
					}

					$last_text = $args['last_text'];

					if ( ! empty( $last_text ) ) {
						$count ++;
					}
				}

				$current_total = $args['current_total'];

				if ( ! empty( $current_total ) ) {
					$count ++;
				}

				if ( 1 == $paged && 11 > $count ) {
					$end_size += ( 11 - $count );
				} elseif ( 3 < $paged && 7 < $count && $paged < $total ) {
					$mid_size = 0;
				} elseif ( $paged == $total && 11 > $count ) {
					$end_size += ( 11 - $count - 1 );
				}

				$args['end_size'] = $end_size;
				$args['mid_size'] = $mid_size;
			}
		}

		$items = paginate_links( $args );

		if ( HOCWP_Theme::array_has_value( $items ) ) {
			$bootstrap = isset( $args['bootstrap'] ) ? $args['bootstrap'] : false;

			$class = $args['class'];
			$class = sanitize_html_class( $class );
			$class .= ' pagination';
			$class = trim( $class );

			$ajax = isset( $args['ajax'] ) ? (bool) $args['ajax'] : false;

			$list_id = isset( $args['list_id'] ) ? $args['list_id'] : '';

			if ( $load_more ) {
				$ajax = true;

				if ( empty( $list_id ) ) {
					$list_id = 'prev';
				}
			}

			$root_url = get_pagenum_link( 1 );
			$root_url = apply_filters( 'hocwp_theme_pagination_first_item_url', $root_url, $args );

			if ( $bootstrap ) {
				echo '<nav aria-label="' . esc_attr__( 'Page navigation', 'hocwp-theme' ) . '" class="mt-5">' . PHP_EOL;
			}

			echo '<ul class="' . $class . '" data-query-vars="' . esc_attr( json_encode( $query->query ) ) . '" data-ajax="' . HT()->bool_to_int( $ajax ) . '" data-load-more="' . HT()->bool_to_int( $load_more ) . '" data-list="' . $list_id . '" data-root-url="' . $root_url . '">' . PHP_EOL;

			if ( isset( $args['label'] ) && ! empty( $args['label'] ) ) {
				echo '<li class="label-item page-item"><span class="page-numbers label page-link">' . $args['label'] . '</span></li>';
			}

			if ( $first_last ) {
				$first = isset( $args['first'] ) ? $args['first'] : isset( $args['first_text'] ) ? $args['first_text'] : '';

				if ( ! empty( $first ) && 2 < $current ) {
					if ( true === $first ) {
						$first = __( 'First', 'hocwp-theme' );
					}

					$url = $root_url;
					echo '<li class="page-item"><a class="first page-numbers page-link" href="' . esc_url( $url ) . '">' . $first . '</a></li>';
				}
			}

			foreach ( $items as $item ) {
				$class = 'page-item';

				if ( $bootstrap ) {
					$item = str_replace( 'page-numbers', 'page-numbers page-link', $item ) . PHP_EOL;

					if ( false !== strpos( $item, 'current' ) ) {
						$class .= ' active';
					}
				}

				echo '<li class="' . esc_attr( $class ) . '">' . $item . '</li>';
			}

			if ( $first_last ) {
				$last = isset( $args['last'] ) ? $args['last'] : isset( $args['last_text'] ) ? $args['last_text'] : '';

				if ( ! empty( $last ) && $current < ( $total - 1 ) ) {
					if ( true === $last ) {
						$last = __( 'Last', 'hocwp-theme' );
					}

					$url = get_pagenum_link( $total );
					echo '<li class="page-item"><a class="last page-numbers page-link" href="' . esc_url( $url ) . '">' . $last . '</a></li>';
				}
			}

			$current_total = isset( $args['current_total'] ) ? $args['current_total'] : false;

			if ( $current_total ) {
				if ( ! is_string( $current_total ) || ( ! HT()->string_contain( $current_total, '[CURRENT]' ) && ! HT()->string_contain( $current_total, '[TOTAL]' ) ) ) {
					$current_total = __( 'Page [CURRENT]/[TOTAL]', 'hocwp-theme' );
				}

				$search = array(
					'[CURRENT]',
					'[TOTAL]'
				);

				$replace = array(
					$paged,
					$query->max_num_pages
				);

				$current_total = str_replace( $search, $replace, $current_total );
				?>
				<li class="page-item current-total">
					<a class="page-numbers page-link" href="javascript:" title=""><?php echo $current_total; ?></a>
				</li>
				<?php
			}

			echo '</ul>' . PHP_EOL;

			if ( $bootstrap ) {
				echo '</nav>' . PHP_EOL;
			}
		}
	}

	public function get_archive_title( $prefix = true ) {
		$title = '';

		if ( is_category() ) {
			$title = single_cat_title( '', false );

			if ( $prefix && ! empty( $title ) ) {
				$title = sprintf( __( 'Category: %s', 'hocwp-theme' ), $title );
			}
		} elseif ( is_tag() ) {
			$title = single_tag_title( '', false );

			if ( $prefix && ! empty( $title ) ) {
				$title = sprintf( __( 'Tag: %s', 'hocwp-theme' ), $title );
			}
		} elseif ( is_author() ) {
			$title = '<span class="vcard">' . get_the_author() . '</span>';

			if ( $prefix && ! empty( $title ) ) {
				$title = sprintf( __( 'Author: %s', 'hocwp-theme' ), $title );
			}
		} elseif ( is_date() ) {
			if ( is_year() ) {
				$title = get_the_date( _x( 'Y', 'yearly archives date format', 'hocwp-theme' ) );

				if ( $prefix && ! empty( $title ) ) {
					$title = sprintf( _x( 'Year: %s', 'yearly archives', 'hocwp-theme' ), $title );
				}
			} elseif ( is_month() ) {
				$title = get_the_date( _x( 'F Y', 'monthly archives date format', 'hocwp-theme' ) );

				if ( $prefix && ! empty( $title ) ) {
					$title = sprintf( _x( 'Month: %s', 'monthly archives', 'hocwp-theme' ), $title );
				}
			} elseif ( is_day() ) {
				$title = get_the_date( _x( 'F j, Y', 'daily archives date format', 'hocwp-theme' ) );

				if ( $prefix && ! empty( $title ) ) {
					$title = sprintf( _x( 'Day: %s', 'daily archives', 'hocwp-theme' ), $title );
				}
			}
		} elseif ( is_tax( 'post_format' ) ) {
			if ( is_tax( 'post_format', 'post-format-aside' ) ) {
				$title = _x( 'Asides', 'post format archive title', 'hocwp-theme' );
			} elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
				$title = _x( 'Galleries', 'post format archive title', 'hocwp-theme' );
			} elseif ( is_tax( 'post_format', 'post-format-image' ) ) {
				$title = _x( 'Images', 'post format archive title', 'hocwp-theme' );
			} elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
				$title = _x( 'Videos', 'post format archive title', 'hocwp-theme' );
			} elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
				$title = _x( 'Quotes', 'post format archive title', 'hocwp-theme' );
			} elseif ( is_tax( 'post_format', 'post-format-link' ) ) {
				$title = _x( 'Links', 'post format archive title', 'hocwp-theme' );
			} elseif ( is_tax( 'post_format', 'post-format-status' ) ) {
				$title = _x( 'Statuses', 'post format archive title', 'hocwp-theme' );
			} elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
				$title = _x( 'Audio', 'post format archive title', 'hocwp-theme' );
			} elseif ( is_tax( 'post_format', 'post-format-chat' ) ) {
				$title = _x( 'Chats', 'post format archive title', 'hocwp-theme' );
			}

			if ( $prefix && ! empty( $title ) ) {
				$title = sprintf( _x( 'Post Format: %s', 'post format archives', 'hocwp-theme' ), $title );
			}
		} elseif ( is_post_type_archive() ) {
			$title = post_type_archive_title( '', false );

			if ( $prefix && ! empty( $title ) ) {
				$title = sprintf( __( 'Archives: %s', 'hocwp-theme' ), $title );
			}
		} elseif ( is_tax() ) {
			$title = single_term_title( '', false );

			if ( $prefix && ! empty( $title ) ) {
				$tax = get_taxonomy( get_queried_object()->taxonomy );

				$title = sprintf( '%1$s: %2$s', $tax->labels->singular_name, $title );
			}
		} elseif ( is_search() ) {
			$title = get_search_query();

			if ( $prefix && ! empty( $title ) ) {
				$title = sprintf( __( 'Search results for: %s', 'hocwp-theme' ), $title );
			}
		} elseif ( ! ( is_home() && is_front_page() ) && ! is_front_page() ) {
			// Blog archive page
			$title = __( 'Recent posts', 'hocwp-theme' );
		} else {
			$title = __( 'Archives', 'hocwp-theme' );
		}

		return apply_filters( 'hocwp_theme_get_the_archive_title', $title, $prefix );
	}

	public function get_separator( $context = 'title' ) {
		$separator = '-';

		if ( class_exists( 'WPSEO_Utils' ) ) {
			$separator = WPSEO_Utils::get_title_separator();
		}

		return apply_filters( 'hocwp_theme_separator', $separator, $context );
	}

	public function is_yoast_breadcrumb() {
		if ( function_exists( 'yoast_breadcrumb' ) ) {
			$breadcrumbs_enabled = current_theme_supports( 'yoast-seo-breadcrumbs' );

			if ( ! $breadcrumbs_enabled ) {
				$breadcrumbs_enabled = WPSEO_Options::get( 'breadcrumbs-enable', false );
			}

			return ( bool ) $breadcrumbs_enabled;
		}

		return false;
	}

	public static function breadcrumb( $args = array() ) {
		$bootstrap = isset( $args['bootstrap'] ) ? $args['bootstrap'] : false;

		if ( ! $bootstrap && HT_Frontend()->is_yoast_breadcrumb() ) {
			yoast_breadcrumb( '<div class="breadcrumb hocwp-breadcrumb">', '</div>' );

			return;
		}

		if ( is_home() ) {
			return;
		}

		$separator = isset( $args['separator'] ) ? $args['separator'] : '&#xBB;';

		if ( $bootstrap ) {
			$link_schema = '<a href="%s">%s</a>';
			$home_item   = sprintf( $link_schema, esc_url( home_url() ), __( 'Home', 'hocwp-theme' ) );
		} else {
			$home_item   = '<a href="' . home_url( '/' ) . '" rel="v:url" property="v:title" class="breadcrumb-item breadcrumb-first trail-item trail-begin breadcrumb_first">' . __( 'Home', 'hocwp-theme' ) . '</a>';
			$link_schema = '<a href="%s" rel="v:url" property="v:title" class="breadcrumb-item trail-item">%s</a>';
		}

		$items = array();

		if ( is_single() ) {
			$obj  = get_post( get_the_ID() );
			$term = null;

			if ( defined( 'WPSEO_FILE' ) || defined( 'WPSEO_PATH' ) ) {
				$primary = get_post_meta( $obj->ID, '_yoast_wpseo_primary_category', true );

				if ( HT()->is_positive_number( $primary ) ) {
					$term = get_category( $primary );
				}
			}

			$has_cat = false;

			if ( ! ( $term instanceof WP_Term ) ) {
				$terms   = wp_get_post_categories( $obj->ID );
				$has_cat = false;

				if ( ! is_wp_error( $terms ) && HT()->array_has_value( $terms ) ) {
					$term = array_shift( $terms );

					if ( HT()->is_positive_number( $term ) ) {
						$term = get_category( $term );
					}
				}
			}

			if ( $term instanceof WP_Term ) {
				$item = sprintf( $link_schema, get_term_link( $term ), $term->name );
				array_unshift( $items, $item );
				$has_cat = true;

				while ( $term->parent > 0 ) {
					$term = get_category( $term->parent );
					$item = sprintf( $link_schema, get_term_link( $term ), $term->name );
					array_unshift( $items, $item );
				}

				unset( $item );
			}

			if ( ! $has_cat ) {
				if ( 'post' != $obj->post_type && 'page' != $obj->post_type ) {
					$type = get_post_type_object( $obj->post_type );

					$tmp = '';

					if ( ! $type->has_archive ) {
						$taxonomies = get_object_taxonomies( $obj, 'objects' );

						$taxonomy = null;

						foreach ( $taxonomies as $tax ) {
							if ( $tax->hierarchical ) {
								$taxonomy = $tax;
								break;
							}
						}

						if ( $taxonomy instanceof WP_Taxonomy ) {
							$terms = wp_get_post_terms( $obj->ID, $taxonomy->name );
							$term  = current( $terms );

							if ( $term instanceof WP_Term ) {
								$tmp = sprintf( $link_schema, get_term_link( $term ), $term->name );
							}
						}
					} else {
						$tmp = sprintf( $link_schema, get_post_type_archive_link( $obj->post_type ), $type->labels->singular_name );
					}

					if ( empty( $tmp ) ) {
						$tmp = $type->labels->singular_name;
					}

					array_unshift( $items, $tmp );

					unset( $tmp );
				}
			}

			unset( $has_cat );
		}

		$last_item = '';

		if ( is_archive() || is_search() ) {
			$last_item = HT_Frontend()->get_archive_title( false );
		} elseif ( is_single() || is_singular() ) {
			$last_item = get_the_title();
		} elseif ( is_404() ) {
			$last_item = __( 'Page not found', 'hocwp-theme' );
		}

		if ( ! empty( $last_item ) ) {
			$items[] = '<span class="breadcrumb_last active breadcrumb-item breadcrumb-last trail-item trail-end">' . $last_item . '</span>';
		}

		unset( $last_item );

		$count = count( $items );

		if ( $bootstrap ) {
			$ol = new HOCWP_Theme_HTML_Tag( 'ol' );
			$ol->add_attribute( 'class', 'breadcrumb hocwp-breadcrumb' );

			$html = '<li class="breadcrumb-item">' . $home_item . '</li>' . PHP_EOL;

			foreach ( $items as $item ) {
				$html .= '<li class="breadcrumb-item">' . $item . '</li>' . PHP_EOL;
			}

			$ol->set_text( $html );
			$ol->output();

			unset( $ol, $html );
		} else {
			$nav = new HOCWP_Theme_HTML_Tag( 'nav' );
			$nav->add_attribute( 'class', 'breadcrumb hocwp-breadcrumb' );
			$nav->add_attribute( 'itemtype', '' );
			$nav->add_attribute( 'itemtype', 'https://schema.org/BreadcrumbList' );

			$span = new HOCWP_Theme_HTML_Tag( 'span' );

			ob_start();
			?>
			<span typeof="v:Breadcrumb">
				<?php
				if ( empty( $separator ) ) {
					echo $home_item;
				} else {
					echo $home_item . '&nbsp;' . $separator;
				}
				?>
				<span rel="v:child" typeof="v:Breadcrumb">
					<?php
					foreach ( $items as $index => $item ) {
						echo $item;

						if ( $index < ( $count - 1 ) && empty( $separator ) ) {
							echo '&nbsp;' . $separator . '&nbsp;';
						}
					}
					?>
				</span>
			</span>
			<?php
			$span->set_text( ob_get_clean() );

			$nav->set_text( $span );
			$nav->output();

			unset( $nav, $span, $index, $item );
		}

		unset( $home_item, $items, $count, $separator );
	}

	public function facebook_share_button( $args = array() ) {
		$post_id = isset( $args['post_id'] ) ? $args['post_id'] : get_the_ID();

		$url = isset( $args['url'] ) ? $args['url'] : '';

		if ( empty( $url ) ) {
			$url = get_permalink( $post_id );
		}

		$layout = isset( $args['layout'] ) ? $args['layout'] : 'button_count';
		$action = isset( $args['action'] ) ? $args['action'] : 'like';

		$show_faces = isset( $args['show_faces'] ) ? $args['show_faces'] : false;
		$show_faces = HT()->bool_to_string( $show_faces );

		$share = isset( $args['share'] ) ? $args['share'] : true;

		$recommend = isset( $args['recommend'] ) ? $args['recommend'] : false;

		if ( $recommend ) {
			$share = false;
		}

		$share = HT()->bool_to_string( $share );

		$before = HT()->get_value_in_array( $args, 'before' );

		echo $before;

		do_action( 'hocwp_theme_facebook_javascript_sdk' );

		$ajax_url = HOCWP_Theme()->get_ajax_url();

		$params = array(
			'action'  => 'hocwp_theme_update_facebook_data',
			'post_id' => $post_id
		);

		$ajax_url = add_query_arg( $params, $ajax_url );
		?>
		<div class="fb-like-buttons like-share clearfix">
			<div class="item">
				<div class="fb-like" data-href="<?php echo $url; ?>" data-layout="<?php echo $layout; ?>"
				     data-action="<?php echo $action; ?>" data-show-faces="<?php echo $show_faces; ?>"
				     data-share="<?php echo $share; ?>" data-post-id="<?php echo $post_id; ?>"></div>
				<?php
				if ( $recommend ) {
					?>
					<div data-share="true" data-show-faces="false" data-action="recommend" data-layout="button_count"
					     data-href="<?php echo $url; ?>" class="fb-like fb_iframe_widget"></div>
					<?php
				}
				?>
			</div>
			<?php do_action( 'hocwp_theme_facebook_share_button', $args ); ?>
		</div>
		<script>
			function updateFacebookData(event) {
				var xhr = new XMLHttpRequest();
				xhr.open("GET", "<?php echo $ajax_url; ?>&event=" + event, true);
				xhr.send();
			}

			window.fbAsyncInit = function () {
				FB.Event.subscribe("edge.create", function () {
					updateFacebookData('like');
				});

				FB.Event.subscribe("edge.remove", function () {
					updateFacebookData('unlike');
				});
			};
		</script>
		<?php
		$after = HT()->get_value_in_array( $args, 'after' );

		echo $after;
	}

	public function addthis_toolbox( $args = array() ) {
		$post_id = isset( $args['post_id'] ) ? $args['post_id'] : get_the_ID();
		$class   = isset( $args['class'] ) ? $args['class'] : 'addthis_native_toolbox';
		$class   = apply_filters( 'hocwp_theme_addthis_toolbox_class', $class );
		$class .= ' addthis-tools';
		$url   = isset( $args['url'] ) ? $args['url'] : get_the_permalink();
		$title = isset( $args['title'] ) ? $args['title'] : get_the_title();
		?>
		<!-- Go to www.addthis.com/dashboard to customize your tools -->
		<div class="<?php echo $class; ?>" data-url="<?php echo $url; ?>"
		     data-title="<?php echo $this->get_wpseo_post_title( $post_id ); ?>"></div>
		<?php
	}

	public function back_to_top_button() {
		if ( ! function_exists( 'hocwp_theme_get_option' ) ) {
			return;
		}

		$text = _x( 'Top', 'back to top', 'hocwp-theme' );
		$icon = hocwp_theme_get_option( 'back_top_icon', '', 'reading' );

		$style = '';

		if ( HT()->is_positive_number( $icon ) ) {
			$text = sprintf( '<img src="%s" alt="">', wp_get_attachment_url( $icon ) );

			$style .= 'padding:0;border:none;border-radius:0;';
		}

		$icon = hocwp_theme_get_option( 'back_top_icon_html', '', 'reading' );

		if ( ! empty( $icon ) ) {
			$text = $icon;
		}

		$bg_color = HT_Util()->get_theme_option( 'back_top_bg', '', 'reading' );

		if ( ! empty( $bg_color ) ) {
			$style .= 'background-color:' . $bg_color . ';';
		}

		$custom_style = HT_Util()->get_theme_option( 'back_top_style', '', 'reading' );

		if ( ! empty( $custom_style ) ) {
			$style .= $custom_style;
		}

		$style = trim( $style );
		?>
		<button id="backToTop" class="back-to-top"
		        onclick="scrollToTop(1000);"
		        title="<?php _e( 'Go to top', 'hocwp-theme' ); ?>"
		        style="<?php echo $style; ?>"><?php echo $text; ?></button>
		<script>
			window.onscroll = function () {
				scrollFunction()
			};

			function scrollFunction() {
				if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
					document.getElementById("backToTop").style.display = "block";
				} else {
					document.getElementById("backToTop").style.display = "none";
				}
			}

			function scrollToTop(scrollDuration) {
				var cosParameter = window.scrollY / 2,
					scrollCount = 0,
					oldTimestamp = performance.now();

				function step(newTimestamp) {
					scrollCount += Math.PI / (scrollDuration / (newTimestamp - oldTimestamp));
					if (scrollCount >= Math.PI) window.scrollTo(0, 0);
					if (window.scrollY === 0) return;
					window.scrollTo(0, Math.round(cosParameter + cosParameter * Math.cos(scrollCount)));
					oldTimestamp = newTimestamp;
					window.requestAnimationFrame(step);
				}

				window.requestAnimationFrame(step);
			}
		</script>
		<?php
	}

	public function content_404() {
		$html = apply_filters( 'hocwp_theme_404_content', '' );

		if ( ! empty( $html ) ) {
			echo $html;
		} else {
			$page = HT_Util()->get_theme_option( 'page_404', '', 'reading' );
			$page = get_post( $page );

			if ( $page instanceof WP_Post && 'page' == $page->post_type ) {
				?>
				<header class="page-header">
					<h2 class="page-title"><?php echo get_the_title( $page ); ?></h2>
				</header>
				<!-- .page-header -->
				<div class="page-content entry-content">
					<?php
					$content = apply_filters( 'the_content', $page->post_content );
					echo $content;
					?>
				</div>
				<?php
			} else {
				?>
				<header class="page-header">
					<h2 class="page-title"><?php esc_html_e( 'Oops! That page can&rsquo;t be found.', 'hocwp-theme' ); ?></h2>
				</header>
				<!-- .page-header -->
				<div class="page-content entry-content">
					<p><?php esc_html_e( 'It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'hocwp-theme' ); ?></p>
					<?php
					get_search_form();
					the_widget( 'WP_Widget_Recent_Posts' );
					?>
					<div class="widget widget_categories">
						<h2 class="widget-title"><?php esc_html_e( 'Most Used Categories', 'hocwp-theme' ); ?></h2>
						<ul>
							<?php
							wp_list_categories( array(
								'orderby'    => 'count',
								'order'      => 'DESC',
								'show_count' => 1,
								'title_li'   => '',
								'number'     => 10,
							) );
							?>
						</ul>
					</div>
					<!-- .widget -->
					<?php
					/* translators: %1$s: smiley */
					$archive_content = '<p>' . sprintf( esc_html__( 'Try looking in the monthly archives. %1$s', 'hocwp-theme' ), convert_smilies( ':)' ) ) . '</p>';
					the_widget( 'WP_Widget_Archives', 'dropdown=1', "after_title=</h2>$archive_content" );
					the_widget( 'WP_Widget_Tag_Cloud' );
					?>
				</div>
				<!-- .page-content -->
				<?php
			}
		}

		do_action( 'hocwp_theme_content_404' );
	}
}

function HT_Frontend() {
	return HOCWP_Theme_Frontend::get_instance();
}

HT_Frontend();