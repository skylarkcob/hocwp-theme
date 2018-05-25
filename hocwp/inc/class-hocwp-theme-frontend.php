<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

final class HOCWP_Theme_Frontend extends HOCWP_Theme_Utility {
	public static $instance;

	protected function __construct() {
	}

	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function pagination( $args = array() ) {
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

		$args  = wp_parse_args( $args, $defaults );
		$args  = apply_filters( 'hocwp_theme_pagination_args', $args );
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
			$class = $args['class'];
			$class = sanitize_html_class( $class );
			$class .= ' pagination';
			$class = trim( $class );

			echo '<ul class="' . $class . '">';

			if ( isset( $args['label'] ) && ! empty( $args['label'] ) ) {
				echo '<li class="label-item page-item"><span class="page-numbers label page-link">' . $args['label'] . '</span></li>';
			}

			if ( $first_last ) {
				$first = isset( $args['first'] ) ? $args['first'] : isset( $args['first_text'] ) ? $args['first_text'] : '';

				if ( ! empty( $first ) && 2 < $current ) {
					if ( true === $first ) {
						$first = __( 'First', 'hocwp-theme' );
					}

					$url = get_pagenum_link( 1 );
					echo '<li class="page-item"><a class="first page-numbers page-link" href="' . esc_url( $url ) . '">' . $first . '</a></li>';
				}
			}

			foreach ( $items as $item ) {
				echo '<li class="page-item">' . $item . '</li>';
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

			echo '</ul>';
		}
	}

	public function get_archive_title( $prefix = true ) {
		if ( is_category() ) {
			$title = single_cat_title( '', false );

			if ( $prefix ) {
				$title = sprintf( __( 'Category: %s', 'hocwp-theme' ), $title );
			}
		} elseif ( is_tag() ) {
			$title = single_tag_title( '', false );

			if ( $prefix ) {
				$title = sprintf( __( 'Tag: %s', 'hocwp-theme' ), $title );
			}
		} elseif ( is_author() ) {
			$title = '<span class="vcard">' . get_the_author() . '</span>';

			if ( $prefix ) {
				$title = sprintf( __( 'Author: %s', 'hocwp-theme' ), $title );
			}
		} elseif ( is_date() ) {
			$year = get_the_date( _x( 'Y', 'yearly archives date format', 'hocwp-theme' ) );

			if ( is_year() ) {
				$title = $year;
				$title = sprintf( _x( 'Year %s', 'yearly archives', 'hocwp-theme' ), $title );
			} elseif ( is_month() ) {
				$title = get_the_date( _x( 'F', 'monthly archives date format', 'hocwp-theme' ) );
				$title = sprintf( _x( '%1$s %2$s', 'monthly archives', 'hocwp-theme' ), $title, $year );
			} elseif ( is_day() ) {
				$month = get_the_date( _x( 'F', 'daily archives date format', 'hocwp-theme' ) );
				$day   = get_the_date( _x( 'j', 'daily archives date format', 'hocwp-theme' ) );
				$title = sprintf( _x( '%1$s %2$s, %3$s', 'daily archives', 'hocwp-theme' ), $month, $day, $year );
			}

			if ( $prefix ) {
				$title = sprintf( __( 'Archives: %s', 'hocwp-theme' ), $title );
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
		} elseif ( is_post_type_archive() ) {
			$title = post_type_archive_title( '', false );

			if ( $prefix ) {
				$title = sprintf( __( 'Archives: %s', 'hocwp-theme' ), post_type_archive_title( '', false ) );
			}
		} elseif ( is_tax() ) {
			$title = single_term_title( '', false );

			if ( $prefix ) {
				$tax = get_taxonomy( get_queried_object()->taxonomy );

				$title = sprintf( '%1$s: %2$s', $tax->labels->singular_name, $title );
			}
		} elseif ( is_search() ) {
			$title = get_search_query();

			if ( $prefix ) {
				$title = sprintf( __( 'Search results for: %s', 'hocwp-theme' ), $title );
			}
		} elseif ( ! ( is_home() && is_front_page() ) && ! is_front_page() ) {
			$title = __( 'Recent posts', 'hocwp-theme' );
		} else {
			$title = __( 'Archives', 'hocwp-theme' );
		}

		return apply_filters( 'hocwp_theme_get_the_archive_title', $title, $prefix );
	}

	public static function breadcrumb( $args = array() ) {
		if ( is_home() ) {
			return;
		}

		$separator   = isset( $args['separator'] ) ? $args['separator'] : '&#xBB;';
		$home_item   = '<a href="' . home_url( '/' ) . '" rel="v:url" property="v:title" class="breadcrumb-item breadcrumb-first trail-item trail-begin breadcrumb_first">' . __( 'Home', 'hocwp-theme' ) . '</a>';
		$items       = array();
		$link_schema = '<a href="%s" rel="v:url" property="v:title" class="breadcrumb-item trail-item">%s</a>';

		if ( is_single() ) {
			$obj  = get_post( get_the_ID() );
			$term = null;

			if ( defined( 'WPSEO_FILE' ) || defined( 'WPSEO_PATH' ) ) {
				$primary = get_post_meta( $obj->ID, '_yoast_wpseo_primary_category', true );

				if ( HT()->is_positive_number( $primary ) ) {
					$term = get_category( $primary );
				}
			}

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
		$nav   = new HOCWP_Theme_HTML_Tag( 'nav' );
		$nav->add_attribute( 'class', 'breadcrumb hocwp-breadcrumb' );
		$nav->add_attribute( 'itemtype', '' );
		$nav->add_attribute( 'itemtype', 'https://schema.org/BreadcrumbList' );

		$span = new HOCWP_Theme_HTML_Tag( 'span' );
		$span->add_attribute( 'xmlns:v', 'http://rdf.data-vocabulary.org/#' );
		ob_start();
		?>
		<span typeof="v:Breadcrumb">
			<?php echo $home_item . '&nbsp;' . $separator; ?>
			<span rel="v:child" typeof="v:Breadcrumb">
				<?php
				foreach ( $items as $index => $item ) {
					echo $item;
					if ( $index < ( $count - 1 ) ) {
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

		unset( $nav, $span, $home_item, $items, $index, $item, $count, $separator );
	}

	public function facebook_share_button( $args = array() ) {
		$post_id = isset( $args['post_id'] ) ? $args['post_id'] : get_the_ID();
		$url     = isset( $args['url'] ) ? $args['url'] : '';
		if ( empty( $url ) ) {
			$url = get_permalink( $post_id );
		}
		$layout     = isset( $args['layout'] ) ? $args['layout'] : 'button_count';
		$action     = isset( $args['action'] ) ? $args['action'] : 'like';
		$show_faces = isset( $args['show_faces'] ) ? $args['show_faces'] : false;
		$show_faces = HT()->bool_to_string( $show_faces );
		$share      = isset( $args['share'] ) ? $args['share'] : true;
		$share      = HT()->bool_to_string( $share );
		do_action( 'hocwp_theme_facebook_javascript_sdk' );
		$ajax_url = admin_url( 'admin-ajax.php' );
		$params   = array(
			'action'  => 'hocwp_theme_update_facebook_data',
			'post_id' => $post_id
		);
		$ajax_url = add_query_arg( $params, $ajax_url );
		?>
		<div class="fb-like-buttons like-share">
			<div class="item">
				<div class="fb-like" data-href="<?php echo $url; ?>" data-layout="<?php echo $layout; ?>"
				     data-action="<?php echo $action; ?>" data-show-faces="<?php echo $show_faces; ?>"
				     data-share="<?php echo $share; ?>" data-post-id="<?php echo $post_id; ?>"></div>
			</div>
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
		$text = _x( 'Top', 'back to top', 'hocwp-theme' );
		$icon = hocwp_theme_get_option( 'back_top_icon', '', 'reading' );

		$style = '';

		if ( HT()->is_positive_number( $icon ) ) {
			$text = sprintf( '<img src="%s" alt="">', wp_get_attachment_url( $icon ) );

			$style .= 'padding:0;border:none;border-radius:0;';
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
}

function HT_Frontend() {
	return HOCWP_Theme_Frontend::get_instance();
}