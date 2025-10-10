<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

final class HOCWP_Theme_Frontend extends HOCWP_Theme_Utility {
	public static $instance;

	protected function __construct() {
		parent::__construct();

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
		$logo = get_custom_logo();

		$display = ht_options()->get_general( 'logo_display' );

		if ( 'image' == $display ) {
			$site_title = get_bloginfo( 'name' );

			if ( ! display_header_text() ) {
				$site_title = '';
			}

			/** @noinspection HtmlUnknownTarget */
			$defaults = array(
				'logo'        => '%1$s<span class="screen-reader-text">%2$s</span>',
				'logo_class'  => 'site-logo',
				'title'       => '<a href="%1$s" title="%2$s">%2$s</a>',
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

			if ( ! str_contains( $logo, '<img' ) && ( ! str_contains( $contents, '<h1' ) || ! str_contains( $wrap, '<h1' ) ) ) {
				$html = sprintf( $args[ $wrap ], $classname, $contents );
			} else {
				$html = $logo;
			}
		} else {
			$html = $logo;

			$classname = '';
			$contents  = '';
		}

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
			$atts['data-name']      = $item->post_name;
		}

		return $atts;
	}

	public function bootstrap_nav_menu( $args = array() ) {
		$args['fallback_cb'] = 'HOCWP_Theme_Walker_Nav_Menu_Bootstrap::fallback';
		$args['walker']      = new HOCWP_Theme_Walker_Nav_Menu_Bootstrap();

		$container_class = $args['container_class'] ?? '';

		$container_class .= ' collapse navbar-collapse';

		$args['container_class'] = trim( $container_class );

		$menu_class = $args['menu_class'] ?? '';

		$menu_class .= ' navbar-nav mr-auto flex-row';

		$args['menu_class'] = trim( $menu_class );

		$menu = apply_filters( 'hocwp_theme_main_menu_default_location', 'menu-1' );

		$args['theme_location'] = $args['theme_location'] ?? $menu;

		wp_nav_menu( wp_parse_args( $args, array( 'theme_location' => '' ) ) );
	}

	public function wp_nav_menu_helper( $args = array() ) {
		if ( isset( $args['bootstrap'] ) && $args['bootstrap'] ) {
			$this->bootstrap_nav_menu( $args );

			return;
		}

		$mobile_control = $args['mobile_control'] ?? false;

		$container_class = $args['container_class'] ?? '';

		$position = $args['position'] ?? 'left';

		if ( 'left' !== $position ) {
			$position = 'right';
		}

		$container_class .= ' position-' . $position;

		$full_width = $args['full_width'] ?? false;

		if ( $full_width ) {
			$container_class .= ' full-width displaying-stretch';
		}

		$button = $args['mobile_button'] ?? '';

		$button_control = $args['button_control'] ?? '';

		if ( empty( $button ) ) {
			$button = hocwp_theme_menu_button( $button_control );
		}

		$mobile_button_id = $args['mobile_button_id'] ?? '';

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

		if ( ! $mobile_control ) {
			unset( $defaults['items_wrap'] );
		}

		$args = wp_parse_args( $args, $defaults );

		$args['container_class'] = trim( $container_class );

		$menu = apply_filters( 'hocwp_theme_main_menu_default_location', 'menu-1' );

		$args['theme_location'] = $args['theme_location'] ?? $menu;

		wp_nav_menu( wp_parse_args( $args, array( 'theme_location' => '' ) ) );
	}

	public function is_custom_page_template( $file ) {
		if ( is_array( $file ) ) {
			foreach ( $file as $a ) {
				if ( $this->is_custom_page_template( $a ) ) {
					return true;
				}
			}
		} else {
			$file = ht()->trim_string( $file, '.php' );

			return is_page_template( 'custom/page-templates/' . $file . '.php' );
		}

		return false;
	}

	public function is_full_width() {
		$full_width = $this->is_custom_page_template( 'full-width' );

		if ( ! $full_width && ( is_single() || is_page() || is_singular() ) ) {
			if ( is_single() || is_page() || is_singular() ) {
				$full_width = get_post_meta( get_the_ID(), 'full_width', true );
			} elseif ( is_category() || is_tag() || is_tax() ) {
				$full_width = get_term_meta( get_queried_object_id(), 'full_width', true );
			}
		}

		return apply_filters( 'hocwp_theme_is_template_full_width', $full_width );
	}

	public function the_query_pagination( $args = array() ) {
		self::pagination( $args );
	}

	/**
	 * Display pagination for custom list items.
	 *
	 * @param int $ppp Number of item per page.
	 * @param int $paged Current paged.
	 * @param array $lists List items to have pagination.
	 * @param array $args Pagination arguments.
	 *
	 * @return void
	 */
	public function custom_pagination( $ppp, $paged, $lists = array(), $args = array() ) {
		$defaults = array(
			'posts_per_page' => $ppp,
			'paged'          => $paged,
			'query'          => $lists
		);

		$args = wp_parse_args( $args, $defaults );
		$this->pagination( $args );
	}

	public function pagination( $args = array() ) {
		// Pass total page number to use for custom items array
		if ( ht()->is_positive_number( $args ) ) {
			$args = array( 'total' => $args );
		} elseif ( ! is_array( $args ) && $args instanceof WP_Query ) {
			$args = array( 'query' => $args );
		}

		// Use pagination from plugin
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
			'total'         => '',
			'class'         => 'hocwp-pagination',
			'layout'        => '', // Accepts: default, only-label, bootstrap
			'items'         => '', // Array items to loop pagination
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'hocwp_theme_pagination_args', $args );
		$args = apply_filters( 'ht/pagination/args', $args );

		$output = apply_filters( 'hocwp_theme_pagination', '', $args );
		$output = apply_filters( 'ht/pagination', $output, $args );

		if ( ! empty( $output ) ) {
			echo $output;

			return;
		}

		$query = $args['items'] ?? '';

		if ( empty( $query ) ) {
			$query = $args['query'];
		}

		$total = $args['total'] ?? '';

		$query_vars = '';

		$ppp = $args['posts_per_page'] ?? '';

		if ( ! is_numeric( $ppp ) ) {
			$ppp = ht_frontend()->get_posts_per_page( is_home() );
		}

		if ( empty( $total ) ) {
			if ( $query instanceof WP_Query ) {
				$total      = $query->max_num_pages;
				$query_vars = $query->query_vars;
			} elseif ( is_array( $query ) ) {
				// Show pagination for custom array
				$total = ceil( count( $query ) / $ppp );

				$query_vars = $args['query_vars'] ?? '';
			}

			$args['total'] = $total;
		}

		if ( is_array( $query_vars ) ) {
			$query_vars = json_encode( $query_vars );
		}

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
		$next = $args['next'] ?? '';

		$load_more = $args['load_more'] ?? '';

		if ( $load_more ) {
			$next = true;
		}

		if ( empty( $next ) ) {
			$next = $args['next_text'] ?? '';
		}

		$prev = $args['prev'] ?? '';

		if ( empty( $prev ) ) {
			$prev = $args['prev_text'] ?? '';
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

		$dynamic_size = ht()->convert_to_boolean( $args['dynamic_size'] );

		$first_last = $args['first_last'] ?? false;

		if ( ! $first_last ) {
			if ( isset( $args['first'] ) && isset( $args['last'] ) ) {
				$first_last = true;
			}
		}

		if ( $load_more ) {
			$dynamic_size = false;

			$args['mid_size'] = $big;
			$args['end_size'] = $big;
		}

		if ( $dynamic_size ) {
			$show_all = ht()->convert_to_boolean( $args['show_all'] );

			if ( $show_all ) {
				$count = 0;
				$label = $args['label'];

				if ( ! empty( $label ) ) {
					$count ++;
				}

				$end_size  = absint( $args['end_size'] );
				$count     += $end_size;
				$mid_size  = absint( $args['mid_size'] );
				$count     += $mid_size;
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

				if ( 1 == $first_last || true === $first_last ) {
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

		$short_mid_mobile = apply_filters( 'hocwp_theme_pagination_short_mid_mobile', wp_is_mobile() );
		$short_mid_mobile = apply_filters( 'ht/pagination/short_mid_mobile', $short_mid_mobile );

		if ( $short_mid_mobile ) {
			if ( ( 1 + 2 ) > $paged && $paged < ( $total - 2 ) ) {
				$mid_size = 1;

				$args['mid_size'] = $mid_size;
			}
		}

		$args = apply_filters( 'hocwp_theme_paginate_links_args', $args );
		$args = apply_filters( 'ht/pagination/links/args', $args );

		$items = paginate_links( $args );

		if ( ht()->array_has_value( $items ) ) {
			$bootstrap = $args['bootstrap'] ?? false;

			$layout = $args['layout'] ?? '';

			$layout = sanitize_title( $layout );
			$layout = strtolower( $layout );

			if ( 'only-label' == $layout ) {
				$bootstrap = false;
			}

			if ( empty( $layout ) && $bootstrap ) {
				$layout = 'bootstrap';
			} elseif ( empty( $layout ) ) {
				$layout = 'default';
			}

			$class = $args['class'];
			$class = sanitize_html_class( $class );
			$class .= ' pagination';
			$class .= ' layout-' . $layout;
			$class = trim( $class );

			$ajax = $args['ajax'] ?? false;

			$list_id = $args['list_id'] ?? '';

			if ( $load_more ) {
				$ajax = true;

				if ( empty( $list_id ) ) {
					$list_id = 'prev';
				}
			}

			$root_url = $args['root_url'] ?? '';

			if ( empty( $root_url ) ) {
				$root_url = get_pagenum_link();
			}

			$root_url = apply_filters( 'hocwp_theme_pagination_first_item_url', $root_url, $args );
			$root_url = apply_filters( 'ht/pagination/first_item_url', $root_url, $args );

			if ( $bootstrap ) {
				$class .= ' mt-5';
			}

			$atts = array(
				'class'               => $class,
				'data-query-vars'     => $query_vars,
				'data-ajax'           => ht()->bool_to_int( $ajax ),
				'data-load-more'      => ht()->bool_to_int( $load_more ),
				'data-list'           => $list_id,
				'data-root-url'       => $root_url,
				'data-posts-per-page' => $ppp,
				'data-total-page'     => $total,
				'aria-label'          => __( 'Page navigation', 'hocwp-theme' )
			);

			$atts = apply_filters( 'hocwp_theme_pagination_attributes', $atts, $args );
			$atts = apply_filters( 'ht/pagination/attributes', $atts, $args );

			$atts = array_map( 'esc_attr', $atts );
			$atts = ht()->attributes_to_string( $atts );

			if ( $bootstrap ) {
				echo '<nav class="bootstrap-pagination">' . PHP_EOL;
			}

			if ( 'default' == $layout || 'bootstrap' == $layout || $bootstrap ) {
				echo '<ul ' . $atts . '>' . PHP_EOL;
			}

			if ( 'only-label' == $layout ) {
				printf( '<nav %s>', $atts );
				printf( '<h2 class="screen-reader-text">%s</h2>', esc_html__( 'Posts navigation', 'hocwp-theme' ) );
				echo '<div class="nav-links">' . PHP_EOL;
			}

			switch ( $layout ) {
				case 'only-label':
					$label_format = '<span class="label-item page-item page-numbers label page-link">%s</span>';
					$first_format = '<a class="page-item first page-numbers page-link" href="%s">%s</a>';
					$last_format  = '<a class="page-item last page-numbers page-link" href="%s">%s</a>';
					$link_format  = '';

					$current_total_format = '<a class="page-item current-total page-numbers page-link" href="javascript:" title="">%s</a>';
					break;
				default:
					$label_format = '<li class="label-item page-item"><span class="page-numbers label">%s</span></li>';
					$first_format = '<li class="page-item"><a class="first page-numbers page-link" href="%s">%s</a></li>';
					$last_format  = '<li class="page-item"><a class="last page-numbers page-link" href="%s">%s</a></li>';
					$link_format  = '<li class="%s">%s</li>';

					$current_total_format = '<li class="page-item current-total"><a class="page-numbers page-link" href="javascript:" title="">%s</a></li>';
					break;
			}

			if ( ! empty( $args['label'] ) ) {
				printf( $label_format, $args['label'] );
			}

			if ( $first_last ) {
				$first = $args['first'] ?? '';

				if ( empty( $first ) ) {
					$first = $args['first_text'] ?? '';
				}

				if ( ! empty( $first ) && 2 < $current ) {
					if ( true === $first ) {
						$first = __( 'First', 'hocwp-theme' );
					}

					$url = $root_url;
					printf( $first_format, esc_url( $url ), $first );
				}
			}

			foreach ( $items as $item ) {
				$class = 'page-item';

				if ( $bootstrap ) {
					$item = str_replace( 'page-numbers', 'page-numbers page-link', $item ) . PHP_EOL;

					if ( str_contains( $item, 'current' ) ) {
						$class .= ' active';
					}
				}

				if ( 'only-label' == $layout ) {
					echo $item;
				} else {
					printf( $link_format, esc_attr( $class ), $item );
				}
			}

			if ( $first_last ) {
				$last = $args['last'] ?? '';

				if ( empty( $last ) ) {
					$last = $args['last_text'] ?? '';
				}

				if ( ! empty( $last ) && $current < ( $total - 1 ) ) {
					if ( true === $last ) {
						$last = __( 'Last', 'hocwp-theme' );
					}

					$url = get_pagenum_link( $total );
					printf( $last_format, esc_url( $url ), $last );
				}
			}

			$current_total = $args['current_total'] ?? false;

			if ( $current_total ) {
				if ( ! is_string( $current_total ) || ( ! ht()->string_contain( $current_total, '[CURRENT]' ) && ! ht()->string_contain( $current_total, '[TOTAL]' ) ) ) {
					$current_total = __( 'Page [CURRENT]/[TOTAL]', 'hocwp-theme' );
				}

				$search = array(
					'[CURRENT]',
					'[TOTAL]'
				);

				$replace = array(
					$paged,
					$total
				);

				$current_total = str_replace( $search, $replace, $current_total );
				printf( $current_total_format, $current_total );
			}

			if ( 'default' == $layout || 'bootstrap' == $layout || $bootstrap ) {
				echo '</ul>' . PHP_EOL;
			}

			if ( 'only-label' == $layout ) {
				echo '</div>' . PHP_EOL;
			}

			if ( $bootstrap || 'only-label' == $layout ) {
				echo '</nav>' . PHP_EOL;
			}
		}
	}

	public function list_social_html( $list_class = '' ) {
		$list_socials = ht_options()->get_tab( 'list_socials', '', 'social' );

		if ( ! empty( $list_socials ) ) {
			$list_socials = explode( ',', $list_socials );
			$list_socials = array_map( 'trim', $list_socials );
			$list_class   .= ' list-socials';
			$list_class   = trim( $list_class );
			?>
            <ul class="<?php echo esc_attr( $list_class ); ?>">
				<?php
				foreach ( $list_socials as $social ) {
					$url = ht_options()->get_tab( $social . '_url', '', 'social' );

					if ( ! empty( $url ) ) {
						$icon = ht_options()->get_tab( $social . '_icon', '', 'social' );

						if ( empty( $icon ) ) {
							$icon = ucfirst( $social );
						}

						$class = 'social-item ' . sanitize_html_class( $social );
						?>
                        <li class="<?php echo esc_attr( $class ); ?>" data-social="<?php echo esc_attr( $social ); ?>">
                            <a href="<?php echo esc_url( $url ); ?>"
                               title="<?php echo esc_attr( ucfirst( $social ) ); ?>"
                               target="_blank" rel="nofollow"><?php echo $icon ?></a>
                        </li>
						<?php
					}
				}
				?>
            </ul>
			<?php
		}
	}

	public function social_sharing_buttons( $args = array(), $bg = true, $show_text = true, $rounded = false ) {
		$title = get_the_title();
		$url   = get_the_permalink();

		$defaults = array(
			'whatsapp'  => array(
				'url'    => 'whatsapp://send',
				'icon'   => '<i class="fa fa-phone"></i>',
				'params' => array(
					'text' => $title . ' - ' . $url
				)
			),
			'facebook'  => array(
				'url'    => 'https://www.facebook.com/sharer.php?',
				'icon'   => '<i class="fa fa-facebook"></i>',
				'params' => array(
					'u' => $url
				)
			),
			'twitter'   => array(
				'url'    => 'https://twitter.com/share',
				'icon'   => '<i class="fa fa-twitter"></i>',
				'params' => array(
					'url' => $url
				)
			),
			'email'     => array(
				'url'    => 'mailto:',
				'icon'   => '<i class="fa fa-envelope"></i>',
				'params' => array(
					'subject' => $title,
					'body'    => sprintf( __( 'Check this out: %s', 'hocwp-theme' ), $url )
				)
			),
			'pinterest' => array(
				'url'    => 'https://pinterest.com/pin/create/button/',
				'icon'   => '<i class="fa fa-pinterest"></i>',
				'params' => array(
					'url'         => $url,
					'description' => $title,
					'media'       => get_the_post_thumbnail_url( '', 'full' )
				)
			),
			'linkedin'  => array(
				'url'    => 'https://www.linkedin.com/shareArticle',
				'icon'   => '<i class="fa fa-linkedin"></i>',
				'params' => array(
					'url'   => $url,
					'title' => $title,
					'mini'  => true
				)
			),
			'copy_url'  => array(
				'url'   => get_permalink(),
				'title' => __( 'Copy URL', 'hocwp-theme' ),
				'name'  => __( 'Copy URL', 'hocwp-theme' ),
				'icon'  => '<i class="fa fa-link" aria-hidden="true"></i>'
			)
		);

		$args = wp_parse_args( $args, $defaults );

		$args = apply_filters( 'hocwp_theme_social_sharing_buttons', $args );

		$container_class = 'td-post-sharing';

		if ( $bg ) {
			$container_class .= ' td-ps-bg';
		}

		if ( ! $show_text ) {
			$container_class .= ' td-ps-notext';
		}

		if ( $rounded ) {
			$container_class .= ' td-ps-rounded';
		}
		?>
        <div class="<?php echo esc_attr( $container_class ); ?>">
            <div class="td-post-sharing-visible">
				<?php
				foreach ( $args as $social => $data ) {
					$url = $data['url'];

					if ( ! empty( $data['params'] ) ) {
						$url = add_query_arg( $data['params'], $url );
					}

					$class = 'td-social-sharing-button td-social-sharing-button-js td-social-network td-social-' . sanitize_html_class( $social );
					$class .= ' icon button circle is-outline tooltip show-for-medium tooltipstered';
					$class .= ' ' . sanitize_html_class( $social );

					if ( 'email' == $social ) {
						$class .= ' td-social-mail';
					}

					$name = $data['name'] ?? '';

					if ( empty( $name ) ) {
						$name = ucfirst( $social );
					}

					$icon = $data['icon'] ?? '';

					if ( ! empty( $icon ) ) {
						$icon = str_replace( 'class="', 'class="td-icon-' . sanitize_title( $social ) . ' ', $icon );

						if ( 'email' == $social ) {
							$icon = str_replace( 'class="', 'class="td-icon-mail ', $icon );
						}
					}

					$title = $data['title'] ?? '';

					if ( empty( $title ) ) {
						$title = sprintf( __( 'Share on %s', 'hocwp-theme' ), $name );
					}
					?>
                    <a href="<?php echo esc_attr( $url ); ?>" rel="nofollow" target="_blank"
                       title="<?php echo esc_attr( $title ); ?>"
                       class="<?php echo esc_attr( $class ); ?>">
                        <div class="td-social-but-icon">
							<?php
							if ( 'copy_url' == $social ) {
								?>
                                <div class="td-social-copy_url-check td-icon-check">
                                    <i class="fa fa-check" aria-hidden="true"></i>
                                </div>
								<?php
							}

							echo $icon;
							?>
                        </div>
                        <div class="td-social-but-text"><?php echo $name; ?></div>
                    </a>
					<?php
				}
				?>
            </div>
        </div>
		<?php
	}

	public function get_current_title( $args = array() ) {
		if ( is_singular() || is_single() || is_page() ) {
			$title = get_the_title();
		} else {
			$title = $this->get_archive_title( $args );
		}

		return apply_filters( 'hocwp_theme_current_title', $title );
	}

	public function get_archive_title( $args = array() ) {
		global $wp_query;

		$title = '';

		if ( ! ht()->array_has_value( $args ) ) {
			$args = array(
				'prefix' => $args
			);
		}

		$defaults = array(
			'prefix'        => true,
			'search_format' => ''
		);

		$args = wp_parse_args( $args, $defaults );

		$prefix = $args['prefix'];

		if ( ! empty( $args['search_format'] ) && is_search() ) {
			$search_format = $args['search_format'];

			$title = str_replace(
				array( '%found_posts%', '%search_query%' ),
				array( $wp_query->found_posts, get_search_query() ),
				$search_format
			);
		} elseif ( is_category() ) {
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
			} elseif ( empty( $title ) ) {
				$title = __( 'Search results', 'hocwp-theme' );
			}
		} elseif ( ! ( is_home() && is_front_page() ) && ! is_front_page() ) {
			if ( is_page_template() ) {
				$title = get_the_title();
			} else {
				// Blog archive page
				$title = __( 'Recent posts', 'hocwp-theme' );
			}
		} else {
			$title = __( 'Archives', 'hocwp-theme' );
		}

		$title = apply_filters( 'hocwp_theme_get_the_archive_title', $title, $prefix );

		if ( $prefix ) {
			$title = $this->get_accent_archive_title( $title );
		}

		return $title;
	}

	public function get_default_colors() {
		$colors = array(
			'text'      => '',
			'accent'    => '',
			'secondary' => '',
			'borders'   => '',
		);

		$defaults = array(
			'content'       => $colors,
			'header-footer' => $colors
		);

		if ( defined( 'HOCWP_THEME_DEFAULT_COLORS' ) && ht()->array_has_value( HOCWP_THEME_DEFAULT_COLORS ) ) {
			$defaults = wp_parse_args( HOCWP_THEME_DEFAULT_COLORS, $defaults );
		}

		return apply_filters( 'hocwp_theme_default_colors', $defaults );
	}

	public function get_accent_archive_title( $title ) {
		$regex = apply_filters(
			'hocwp_theme_get_the_archive_title_regex',
			array(
				'pattern'     => '/(\A[^\:]+\:)/',
				'replacement' => '<span class="color-accent">$1</span>'
			)
		);

		if ( empty( $regex ) ) {
			return $title;
		}

		return preg_replace( $regex['pattern'], $regex['replacement'], $title );
	}

	public function get_separator( $context = 'title' ) {
		return apply_filters( 'hocwp_theme_separator', $this->get_yoast_seo_title_separator(), $context );
	}

	public function is_yoast_breadcrumb() {
		if ( function_exists( 'yoast_breadcrumb' ) && class_exists( 'WPSEO_Options' ) ) {
			$breadcrumbs_enabled = current_theme_supports( 'yoast-seo-breadcrumbs' );

			if ( ! $breadcrumbs_enabled ) {
				/** @noinspection PhpUndefinedClassInspection */
				$breadcrumbs_enabled = WPSEO_Options::get( 'breadcrumbs-enable', false );
			}

			return ( bool ) $breadcrumbs_enabled;
		}

		return false;
	}

	public function is_rank_math_breadcrumb() {
		$options = get_option( 'rank-math-options-general' );

		if ( ! isset( $options['breadcrumbs'] ) || 'on' !== $options['breadcrumbs'] ) {
			return false;
		}

		return function_exists( 'rank_math_the_breadcrumbs' );
	}

	public function delay_load( $module, $delay = '' ) {
		?>
        <div class="delay-load" data-module="<?php echo esc_attr( $module ); ?>"
             data-delay="<?php echo esc_attr( $delay ); ?>"></div>
		<?php
	}

	public function site_footer_tools() {
		$options = ht_options()->get();

		$back_to_top = $options['reading']['back_to_top'] ?? '';

		if ( 1 == $back_to_top ) {
			ht_frontend()->back_to_top_button();
		}

		if ( is_single() || is_page() || is_singular() ) {
			$float_post_nav = ht_options()->get_tab( 'float_post_nav', '', 'reading' );

			if ( 1 == $float_post_nav ) {
				$obj = ht_query()->get_previous_post();
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

					$obj = ht_query()->get_previous_post( false );

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

		$cookie_alert = ht_options()->get_tab( 'cookie_alert', '', 'reading' );

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
                 class="fixed-bottom alert alert-warning mb-0 text-dark rounded-0 alert-dismissible fade show"
                 role="alert"
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

		// Float supports
		$tab_name   = 'float_support';
		$sort_order = ht_options()->get_tab( 'sort_order', '', $tab_name );

		if ( ! empty( $sort_order ) ) {
			$sort_order = json_decode( $sort_order );

			if ( ht()->array_has_value( $sort_order ) ) {
				$style = ht_options()->get_tab( 'style', '', $tab_name );
				$pos   = ht_options()->get_tab( 'position', '', $tab_name );

				$css_style = '';

				$margin = ht_options()->get_tab( 'margin', '', $tab_name );

				if ( ! empty( $margin ) ) {
					$css_style .= 'margin:' . $margin . ';';
				}

				$padding = ht_options()->get_tab( 'padding', '', $tab_name );

				if ( ! empty( $padding ) ) {
					$css_style .= 'padding:' . $padding . ';';
				}

				$radius = ht_options()->get_tab( 'border_radius', '', $tab_name );

				if ( ! empty( $radius ) ) {
					$css_style .= 'border-radius:' . $radius . ';';
				}

				$bg_color = ht_options()->get_tab( 'background_color', '', $tab_name );

				$bg_image = ht_options()->get_tab( 'background_image', '', $tab_name );

				$css_style .= ht_util()->background_image_css( $bg_image, $bg_color );

				ob_start();

				foreach ( $sort_order as $key ) {
					$value = ht_options()->get_tab( $key, '', $tab_name );

					$url   = $key . '_url';
					$url   = $value[ $url ] ?? '';
					$text  = $key . '_text';
					$text  = $value[ $text ] ?? '';
					$icon  = $key . '_icon';
					$icon  = $value[ $icon ] ?? '';
					$image = $key . '_icon_image';
					$image = $value[ $image ] ?? '';

					if ( ! empty( $url ) && ! empty( $text ) && ( ! empty( $icon ) || ! empty( $image ) ) ) {
						if ( 'phone' == $key && ! str_contains( $url, 'tel:' ) ) {
							$url = ht()->sanitize_phone_number( $url );
							$url = 'tel:' . $url;
						}

						if ( ! empty( $url ) ) {
							$vibrate    = $value[ $key . '_vibrate' ] ?? '';
							$vibrate    = ht()->bool_to_int( $vibrate );
							$earthquake = $value[ $key . '_earthquake' ] ?? '';
							$earthquake = ht()->bool_to_int( $earthquake );
							?>
                            <div class="support-item" data-key="<?php echo esc_attr( $key ); ?>"
                                 data-vibrate="<?php echo esc_attr( $vibrate ); ?>"
                                 data-earthquake="<?php echo esc_attr( $earthquake ); ?>">
                                <a target="_blank" href="<?php echo esc_url( $url ); ?>" rel="nofollow"
                                   title="<?php echo esc_attr( $text ); ?>">
									<?php
									if ( 1 == $earthquake ) {
										?>
                                        <span class="earthquake-outer"></span>
                                        <span class="earthquake"></span>
										<?php
									}

									if ( ht_media()->exists( $image ) ) {
										echo wp_get_attachment_image( $image, 'full' );
									} else {
										echo $icon;
									}
									?>
                                    <span class="text"><?php echo esc_html( $text ); ?></span>
                                </a>
                            </div>
							<?php
						}
					}
				}

				$items = ob_get_clean();
				?>
                <div class="float-supports hot-linking hidden-xs" data-style="<?php echo esc_attr( $style ); ?>"
                     data-position="<?php echo esc_attr( $pos ); ?>" style="<?php echo esc_attr( $css_style ); ?>">
                    <div class="box-container center d-flex">
						<?php echo $items; ?>
                    </div>
                    <span class="show_hide"></span>
                </div>
                <div class="float-supports hot-linking visible-xs for-mobile" data-style="horizontal"
                     data-position="bottom">
                    <div class="box-container center d-flex">
						<?php echo $items; ?>
                    </div>
                    <span class="show_hide"></span>
                </div>
				<?php
			}
		}
	}

	public function breadcrumb( $args = array() ) {
		$args = apply_filters( 'hocwp_theme_breadcrumb_args', $args );
		$args = apply_filters( 'ht/breadcrumb/args', $args );

		$type = $args['type'] ?? ht_options()->get_tab( 'breadcrumb_type', '', 'reading' );

		// Switch to default if function of breadcrumb type not exists
		if ( 'yoast_seo' == $type && ! ht_frontend()->is_yoast_breadcrumb() ) {
			$type = 'default';
		} elseif ( 'rank_math' == $type && ! ht_frontend()->is_rank_math_breadcrumb() ) {
			$type = 'default';
		} elseif ( 'woocommerce' == $type && ! function_exists( 'woocommerce_breadcrumb' ) ) {
			$type = 'default';
		}

		$before = '<div class="breadcrumb hocwp-breadcrumb" data-type="' . esc_attr( $type ) . '">';
		$after  = '</div>';

		if ( function_exists( 'bcn_display' ) && ( empty( $type ) || 'navxt' == $type ) ) {
			$linked  = $args['linked'] ?? true;
			$reverse = $args['reverse'] ?? false;
			$force   = $args['force'] ?? false;
			echo $before;
			bcn_display( false, $linked, $reverse, $force );
			echo $after;

			return;
		}

		if ( function_exists( 'woocommerce_breadcrumb' ) && 'woocommerce' == $type ) {
			echo $before;
			woocommerce_breadcrumb();
			echo $after;

			return;
		}

		$bootstrap = $args['bootstrap'] ?? false;

		if ( 'list' == $type ) {
			$bootstrap = true;
		}

		if ( ! $bootstrap && ht_frontend()->is_yoast_breadcrumb() && ( empty( $type ) || 'yoast_seo' == $type ) ) {
			/** @noinspection PhpUndefinedFunctionInspection */
			yoast_breadcrumb( $before, $after );

			return;
		}

		if ( ! $bootstrap && ht_frontend()->is_rank_math_breadcrumb() && ( empty( $type ) || 'rank_math' == $type ) ) {
			if ( isset( $args['separator'] ) ) {
				$args['delimiter'] = $args['separator'];
			}

			if ( ! isset( $args['wrap_before'] ) ) {
				$args['wrap_before'] = $before;
				$args['wrap_after']  = $after;
			}

			/** @noinspection PhpUndefinedFunctionInspection */
			rank_math_the_breadcrumbs( $args );

			return;
		}

		if ( is_home() ) {
			return;
		}

		if ( empty( $type ) ) {
			$type = 'simple';
		}

		$separator = $args['separator'] ?? '&#xBB;';

		if ( $bootstrap ) {
			/** @noinspection HtmlUnknownTarget */
			$link_schema = '<a href="%s">%s</a>';
			$home_item   = sprintf( $link_schema, esc_url( home_url() ), __( 'Home', 'hocwp-theme' ) );
		} else {
			$home_item = '<a href="' . home_url( '/' ) . '" class="breadcrumb-item breadcrumb-first trail-item trail-begin breadcrumb_first">' . __( 'Home', 'hocwp-theme' ) . '</a>';
			/** @noinspection HtmlUnknownTarget */
			$link_schema = '<a href="%s" class="breadcrumb-item trail-item">%s</a>';
		}

		$items = array();

		if ( is_single() ) {
			$obj  = get_post( get_the_ID() );
			$term = null;

			if ( defined( 'WPSEO_FILE' ) || defined( 'WPSEO_PATH' ) ) {
				$primary = get_post_meta( $obj->ID, '_yoast_wpseo_primary_category', true );

				if ( ht()->is_positive_number( $primary ) ) {
					$term = get_category( $primary );
				}
			}

			$has_cat = false;

			if ( ! ( $term instanceof WP_Term ) ) {
				$terms = wp_get_post_categories( $obj->ID );

				if ( ! is_wp_error( $terms ) && ht()->array_has_value( $terms ) ) {
					$term = array_shift( $terms );

					if ( ht()->is_positive_number( $term ) ) {
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
					$type_obj = get_post_type_object( $obj->post_type );

					$tmp = '';

					if ( ! $type_obj->has_archive ) {
						$taxonomies = get_object_taxonomies( $obj, 'objects' );

						$taxonomy = null;

						foreach ( $taxonomies as $tax ) {
							if ( $tax->hierarchical ) {
								$taxonomy = $tax;
								break;
							}
						}

						if ( $taxonomy instanceof WP_Taxonomy ) {
							$terms = (array) wp_get_post_terms( $obj->ID, $taxonomy->name );
							$term  = current( $terms );

							if ( $term instanceof WP_Term ) {
								$tmp = sprintf( $link_schema, get_term_link( $term ), $term->name );
							}
						}
					} else {
						$tmp = sprintf( $link_schema, get_post_type_archive_link( $obj->post_type ), $type_obj->labels->singular_name );
					}

					if ( empty( $tmp ) ) {
						$tmp = $type_obj->labels->singular_name;
					}

					array_unshift( $items, $tmp );

					unset( $tmp );
				}
			}

			unset( $has_cat );
		} elseif ( is_page() ) {
			$parent = get_post_parent( get_the_ID() );

			// Loop through all parent posts @since version 6.9.1
			while ( $parent instanceof WP_Post ) {
				$tmp = sprintf( $link_schema, get_permalink( $parent ), $parent->post_title );
				array_unshift( $items, $tmp );

				if ( ! ht()->is_positive_number( $parent->post_parent ) ) {
					break;
				}

				$parent = get_post( $parent->post_parent );
			}
		}

		$last_item = '';

		if ( is_archive() || is_search() ) {
			$last_item = ht_frontend()->get_archive_title( false );
		} elseif ( is_single() || is_singular() ) {
			$last_item = get_the_title();
		} elseif ( is_404() ) {
			$last_item = __( 'Page not found', 'hocwp-theme' );
		}

		if ( ! empty( $last_item ) ) {
			$items[] = '<span class="breadcrumb_last active breadcrumb-item breadcrumb-last trail-item trail-end">' . $last_item . '</span>';
		}

		unset( $last_item );

		$items = apply_filters( 'hocwp_theme_breadcrumb_items', $items, $args );
		$items = apply_filters( 'ht/breadcrumb/items', $items, $args );

		if ( empty( $items ) ) {
			return;
		}

		$count = count( $items );

		if ( $bootstrap ) {
			$ol = new HOCWP_Theme_HTML_Tag( 'ol' );
			$ol->add_attribute( 'class', 'breadcrumb hocwp-breadcrumb' );
			$ol->add_attribute( 'data-type', esc_attr( $type ) );

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
			$nav->add_attribute( 'data-type', esc_attr( $type ) );

			$span = new HOCWP_Theme_HTML_Tag( 'span' );

			$items_html = '';

			foreach ( $items as $index => $item ) {
				$items_html .= $item;

				if ( $index < ( $count - 1 ) && ! empty( $separator ) ) {
					$items_html .= '&nbsp;' . $separator . '&nbsp;';
				}
			}

			if ( ! empty( $items_html ) ) {
				$items_html = ht()->wrap_text( $items_html, '<span>', '</span>' );
			}

			if ( empty( $separator ) ) {
				$items_html = $home_item . $items_html;
			} else {
				$items_html = $home_item . '&nbsp;' . $separator . $items_html;
			}

			$items_html = ht()->wrap_text( $items_html, '<span>', '</span>' );

			$span->set_text( $items_html );

			$nav->set_text( $span );
			$nav->output();

			unset( $nav, $span, $index, $item, $items_html );
		}

		unset( $home_item, $items, $count, $separator );
	}

	public function facebook_share_button( $args = array() ) {
		$post_id = $args['post_id'] ?? get_the_ID();

		$url = $args['url'] ?? '';

		if ( empty( $url ) ) {
			$url = get_permalink( $post_id );
		}

		$layout = $args['layout'] ?? 'button_count';
		$action = $args['action'] ?? 'like';

		$show_faces = $args['show_faces'] ?? false;
		$show_faces = ht()->bool_to_string( $show_faces );

		$share = $args['share'] ?? true;

		$recommend = $args['recommend'] ?? false;

		if ( $recommend ) {
			$share = false;
		}

		$share = ht()->bool_to_string( $share );

		$before = ht()->get_value_in_array( $args, 'before' );

		echo $before;

		//do_action( 'hocwp_theme_facebook_javascript_sdk' );

		$ajax_url = hocwp_theme()->get_ajax_url();

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
                let xhr = new XMLHttpRequest();
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
		$after = ht()->get_value_in_array( $args, 'after' );

		echo $after;
	}

	public function addthis_toolbox( $args = array() ) {
		$post_id = $args['post_id'] ?? get_the_ID();

		$class = $args['class'] ?? 'addthis_native_toolbox';
		$class .= ' addthis_sharing_toolbox';
		$class = apply_filters( 'hocwp_theme_addthis_toolbox_class', $class, $args );
		$class .= ' addthis-tools';
		$url   = $args['url'] ?? get_the_permalink();

		$widget_id = $args['widget_id'] ?? '';

		if ( empty( $widget_id ) ) {
			$widget_id = ht_options()->get_tab( 'addthis_widget_id', '', 'social' );
		}

		$widget_id = apply_filters( 'hocwp_theme_addthis_toolbox_widget_id', $widget_id, $args );
		?>
        <!-- Go to www.addthis.com/dashboard to customize your tools -->
        <div class="<?php echo $class; ?>" data-url="<?php echo $url; ?>"
             data-widget-id="<?php echo esc_attr( $widget_id ); ?>"
             data-title="<?php echo $this->get_wpseo_post_title( $post_id ); ?>"></div>
		<?php
	}

	public function lazy_image( $class, $src, $width = '', $height = '', $title = '' ) {
		if ( empty( $src ) ) {
			return;
		}

		if ( is_numeric( $src ) && ht_media()->exists( $src ) ) {
			$src = wp_get_attachment_image_url( $src, 'full' );
		} elseif ( is_array( $src ) ) {
			$id = $src['image'] ?? '';

			if ( ht_media()->exists( $id ) ) {
				$src = wp_get_attachment_image_url( $id, 'full' );
			}
		}

		if ( ! is_string( $src ) ) {
			return;
		}

		$class .= ' lozad';
		$class = trim( $class );

		$atts = array(
			'class'         => $class,
			'src'           => HOCWP_THEME_DOT_IMAGE_SRC,
			'data-src'      => $src,
			'data-original' => $src,
			'alt'           => $title,
			'width'         => $width,
			'height'        => $height
		);

		$img = new HOCWP_Theme_HTML_Tag( 'img' );
		$img->set_attributes( $atts );
		$img->output();
	}

	public function back_to_top_button() {
		if ( ! function_exists( 'hocwp_theme_get_option' ) ) {
			return;
		}

		$text = _x( 'Top', 'back to top', 'hocwp-theme' );
		$icon = hocwp_theme_get_option( 'back_top_icon', '', 'reading' );

		$style = '';

		if ( ht()->is_positive_number( $icon ) ) {
			/** @noinspection HtmlUnknownTarget */
			$text = sprintf( '<img src="%s" alt="">', wp_get_attachment_url( $icon ) );

			$style .= 'padding:0;border:none;border-radius:0;';
		}

		$icon = hocwp_theme_get_option( 'back_top_icon_html', '', 'reading' );

		if ( ! empty( $icon ) ) {
			$text = $icon;
		}

		$bg_color = ht_util()->get_theme_option( 'back_top_bg', '', 'reading' );

		if ( ! empty( $bg_color ) ) {
			$style .= 'background-color:' . $bg_color . ';';
		}

		$custom_style = ht_util()->get_theme_option( 'back_top_style', '', 'reading' );

		if ( ! empty( $custom_style ) ) {
			$style .= $custom_style;
		}

		$style = trim( $style );
		?>
        <button id="backToTop" class="back-to-top"
                onclick="scrollToTop(1000);"
                title="<?php _e( 'Go to top', 'hocwp-theme' ); ?>"
                style="<?php echo $style; ?>"
                aria-label="<?php esc_attr_e( 'Go to top', 'hocwp-theme' ); ?>"><?php echo $text; ?></button>
        <!--suppress JSUnresolvedVariable -->
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
                let cosParameter = window.scrollY / 2,
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

	/**
	 * Convert post loop tag into specific HTML.
	 *
	 * @param string $thumb_size Post thumbnail size.
	 * @param array $sort List of HTML tag for generating on loop post.
	 *
	 * @return array The converted HTML for loop tag.
	 */
	public function convert_loop_tag_to_html( $thumb_size = 'post-thumbnail', $sort = array(), $post_id = null ) {
		$multi_char = '~';

		if ( is_string( $sort ) && ! empty( $sort ) ) {
			$sort = array( $sort );
		}

		// Displaying default post HTML in loop
		if ( empty( $sort ) ) {
			$sort = array( 'thumbnail', 'post_title', 'post_excerpt' );
		}

		foreach ( $sort as $index => $sort_key ) {
			$multi = ( str_contains( $sort_key, $multi_char ) );

			if ( $multi ) {
				$html = $this->convert_loop_tag_to_html( $thumb_size, explode( $multi_char, $sort_key ), $post_id );
				$html = join( PHP_EOL, $html );
			} else {
				$html = $sort_key;

				// User can use post_thumbnail or thumbnail tag for display post thumbnail HTML
				$html = str_replace( 'post_thumbnail', 'thumbnail', $html );

				if ( $thumb_size && str_contains( $html, 'thumbnail' ) ) {
					$thumbnail = get_the_post_thumbnail( $post_id, $thumb_size );

					if ( ! empty( $thumbnail ) ) {
						$thumbnail = sprintf( '<a href="%s" title="%s" class="post-thumb">%s</a>', esc_url( get_the_permalink() ), esc_attr( get_the_title() ), $thumbnail );
					}

					$html = str_replace( 'thumbnail', $thumbnail, $html );
				}

				if ( str_contains( $html, 'post_title' ) ) {
					$post_title = get_the_title( $post_id );

					if ( ! empty( $post_title ) ) {
						$post_title = sprintf( '<a href="%s" title="%s">%s</a>', esc_url( get_the_permalink() ), esc_attr( get_the_title() ), get_the_title() );
						$post_title = sprintf( '<div class="post-title">%s</div>', $post_title );
					}

					$html = str_replace( 'post_title', $post_title, $html );
				}

				if ( str_contains( $html, 'posted_on' ) ) {
					$parts = explode( '|', $html );

					$count = count( $parts );

					if ( 2 != $count ) {
						$date = get_the_date( '', $post_id );

						$modified = get_the_modified_date( '', $post_id );
					} else {
						$date = get_the_date( $parts[1], $post_id );

						$modified = get_the_modified_date( $parts[1], $post_id );

						$html = str_replace( '|' . $parts[1], '', $html );
					}

					$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';

					if ( get_the_time( 'U', $post_id ) !== get_the_modified_time( 'U', $post_id ) ) {
						$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
					}

					$time_string = sprintf(
						$time_string,
						esc_attr( get_the_date( DATE_W3C, $post_id ) ),
						esc_html( $date ),
						esc_attr( get_the_modified_date( DATE_W3C, $post_id ) ),
						esc_html( $modified )
					);

					$posted_on = '<a href="' . esc_url( get_permalink( $post_id ) ) . '" rel="bookmark">' . $time_string . '</a>';

					$posted_on = '<span class="posted-on">' . $posted_on . '</span>';

					$html = str_replace( 'posted_on', $posted_on, $html );
				}

				if ( str_contains( $html, 'post_excerpt' ) ) {
					$excerpt = get_the_excerpt( $post_id );

					if ( ! empty( $excerpt ) ) {
						$excerpt = sprintf( '<p class="summary">%s</p>', wp_strip_all_tags( $excerpt ) );
					}

					$html = str_replace( 'post_excerpt', $excerpt, $html );
				}

				if ( str_contains( $html, 'category' ) ) {
					$taxs  = get_object_taxonomies( get_post_type( $post_id ), 'objects' );
					$terms = '';

					if ( ht()->array_has_value( $taxs ) ) {
						$id = $post_id;

						if ( ! ht()->is_positive_number( $id ) ) {
							$id = get_the_ID();
						}

						ob_start();

						foreach ( $taxs as $tax ) {
							if ( $tax instanceof WP_Taxonomy && $tax->hierarchical ) {
								the_terms( $id, $tax->name );
							}
						}

						$terms = ob_get_clean();
					}

					if ( ! empty( $terms ) ) {
						$terms = sprintf( '<div class="terms">%s</div>', $terms );
					}

					$html = str_replace( 'category', $terms, $html );
				}

				if ( str_contains( $html, 'read_more' ) ) {
					$more = sprintf( __( '<a href="%s" class="read-more-link">Read more &rarr;</a>', 'hocwp-theme' ), get_the_permalink( $post_id ) );

					$html = str_replace( 'read_more', $more, $html );
				}
			}

			if ( $multi ) {
				$class = 'details-wrap';

				if ( str_contains( $sort_key, 'thumbnail' ) ) {
					$class .= ' thumb-box';
				}

				$html = sprintf( '<div class="%s" data-key="%s">%s</div>', $class, esc_attr( $sort_key ), $html );
			}

			$sort[ $index ] = $html;
		}

		return $sort;
	}

	public function post_meta( $sort = array(), $post_id = null ) {
		if ( is_string( $sort ) && ! empty( $sort ) ) {
			$sort = array( $sort );
		}

		if ( empty( $sort ) ) {
			$sort = array( 'posted_on', 'category' );
		}

		$sort = $this->convert_loop_tag_to_html( null, $sort, $post_id );

		$sort = apply_filters( 'hocwp_theme_loop_post_meta_html_data', $sort, $post_id );

		$html = join( '', $sort );
		echo $html;
	}

	public function loop_post( $thumb_size = 'post-thumbnail', $post_class = '', $sort = array(), $post_id = null ) {
		if ( is_string( $sort ) && ! empty( $sort ) ) {
			$sort = array( $sort );
		}

		// Displaying default post HTML in loop
		if ( empty( $sort ) ) {
			$sort = array( 'thumbnail', 'post_title', 'post_excerpt' );
		}

		$sort = $this->convert_loop_tag_to_html( $thumb_size, $sort, $post_id );

		$sort = apply_filters( 'hocwp_theme_loop_post_html_data', $sort, $post_id );

		$html = join( '', $sort );

		if ( null !== $post_class ) {
			?>
            <div <?php post_class( $post_class, $post_id ); ?>>
                <div class="post-container">
					<?php echo $html; ?>
                </div>
            </div>
			<?php
		} else {
			echo $html;
		}
	}

	public function content_404() {
		$html = apply_filters( 'hocwp_theme_404_content', '' );

		if ( ! empty( $html ) ) {
			echo $html;
		} else {
			$page = ht_util()->get_theme_option( 'page_404', '', 'reading' );
			$page = get_post( $page );

			if ( $page instanceof WP_Post && 'page' == $page->post_type ) {
				?>
                <header class="page-header">
                    <h2 class="page-title"><?php echo get_the_title( $page ); ?></h2>
                </header>
                <!-- .page-header -->
                <div class="page-content entry-content">
					<?php
					$content = ht_util()->apply_the_content( $page->post_content );
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

					if ( class_exists( 'WP_Widget_Recent_Posts' ) ) {
						the_widget( 'WP_Widget_Recent_Posts' );
					}
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

					if ( class_exists( 'WP_Widget_Archives' ) ) {
						// Fix missing widget archives bug
						register_widget( 'WP_Widget_Archives' );
						the_widget( 'WP_Widget_Archives', 'dropdown=1', "after_title=</h2>$archive_content" );
					}

					if ( class_exists( 'WP_Widget_Tag_Cloud' ) ) {
						the_widget( 'WP_Widget_Tag_Cloud' );
					}
					?>
                </div>
                <!-- .page-content -->
				<?php
			}
		}

		do_action( 'hocwp_theme_content_404' );
	}
}

function ht_frontend() {
	return HOCWP_Theme_Frontend::get_instance();
}

ht_frontend();