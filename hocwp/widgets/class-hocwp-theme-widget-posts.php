<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Widget_Posts extends WP_Widget {
	public $defaults;

	public function __construct() {
		$this->defaults = array(
			'post_type'          => 'post',
			'number'             => 5,
			'thumbnail_size'     => array( 64, 64 ),
			'crop_thumbnail'     => true,
			'orderby'            => 'date',
			'order'              => 'DESC',
			'related'            => false,
			'show_date'          => false,
			'show_date_diff'     => false,
			'show_excerpt'       => false,
			'show_author'        => false,
			'show_comment_count' => false,
			'excerpt_length'     => apply_filters( 'excerpt_length', 55 ),
			'title_length'       => 75,
			'term_as_title'      => false,
			'title_term_link'    => false,
			'date_interval'      => 'all',
			'group_category'     => false,
			'show_category'      => false,
			'display_type'       => '',
			'view_more'
		);

		$this->defaults = apply_filters( 'hocwp_theme_widget_posts_defaults', $this->defaults, $this );

		$widget_options = array(
			'classname'                   => 'hocwp-theme-widget-posts hocwp-widget-post',
			'description'                 => _x( 'Your site\'s most recent Posts and more.', 'widget description', 'hocwp-theme' ),
			'customize_selective_refresh' => true
		);

		$control_options = array(
			'width' => 400
		);

		parent::__construct( 'hocwp_widget_post', 'HocWP Posts', $widget_options, $control_options );

		if ( is_admin() ) {
			add_action( 'wp_ajax_hocwp_theme_search_meta_key', array(
				$this,
				'hocwp_theme_search_meta_key_ajax_callback'
			) );
		} else {
			add_filter( 'hocwp_theme_widget_posts_query_args', array( $this, 'query_args_filter' ), 10, 2 );
		}
	}

	public function query_args_filter( $query_args, $instance ) {
		if ( isset( $query_args['meta_key'] ) && 'views' == $query_args['meta_key'] && class_exists( 'WPP_Query' ) ) {
			$ppp = $query_args['posts_per_page'] ?? ht_util()->get_posts_per_page();

			$post_type = $query_args['post_type'] ?? 'post';

			if ( is_array( $post_type ) ) {
				$post_type = join( ',', $post_type );
			}

			$params = array(
				'range'     => 'monthly',
				'limit'     => $ppp,
				'post_type' => $post_type
			);

			if ( isset( $query_args['date_interval'] ) && ! empty( $query_args['date_interval'] ) ) {
				$params['range'] = $query_args['date_interval'];
			}

			if ( isset( $instance['term'] ) && ht()->array_has_value( $instance['term'] ) ) {
				$terms = $instance['term'];
				$first = array_shift( $terms );

				$parts = explode( ',', $first );

				if ( isset( $parts[0] ) && isset( $parts[1] ) ) {
					$term = get_term( $parts[1], $parts[0] );

					while ( ! ( $term instanceof WP_Term ) && ht()->array_has_value( $terms ) ) {
						$first = array_shift( $terms );

						$parts = explode( ',', $first );

						if ( isset( $parts[0] ) && isset( $parts[1] ) ) {
							$term = get_term( $parts[1], $parts[0] );
						}
					}

					if ( $term instanceof WP_Term ) {
						$term_id = '';

						foreach ( $instance['term'] as $data ) {
							if ( str_contains( $data, $term->taxonomy ) ) {
								$parts = explode( ',', $data );

								if ( isset( $parts[1] ) && ht()->is_positive_number( $parts[1] ) ) {
									$term_id .= $parts[1] . ',';
								}
							}
						}

						if ( ! empty( $term_id ) ) {
							$term_id = trim( $term_id );
							$term_id = rtrim( $term_id, ',' );
						}

						if ( ! empty( $term_id ) ) {
							$params['taxonomy'] = $term->taxonomy;
							$params['term_id']  = $term_id;
						}
					}
				}
			}

			$list_posts = array();

			if ( class_exists( 'WordPressPopularPosts\Query' ) ) {
				$wq = new WordPressPopularPosts\Query( $params );

				$list_posts = $wq->get_posts();
			}

			if ( ht()->array_has_value( $list_posts ) ) {
				$ids = array();

				foreach ( $list_posts as $data ) {
					$ids[] = $data->id;
				}

				unset( $query_args['meta_key'], $query_args['meta_value'], $query_args['date_query'] );

				$query_args['post__in'] = $ids;
			}
		}

		return $query_args;
	}

	private function get_post_type_from_instance( $instance ) {
		$post_type = $instance['post_type'] ?? '';
		$post_type = ht()->json_string_to_array( $post_type );

		if ( ! ht()->array_has_value( $post_type ) ) {
			$post_type = array(
				array(
					'value' => 'post'
				)
			);
		}

		return apply_filters( 'hocwp_theme_widget_posts_post_type', $post_type, $this );
	}

	public function widget( $args, $instance ) {
		$instance = apply_filters( 'hocwp_theme_widget_posts_instance', $instance, $args, $this );
		$related  = isset( $instance['related'] ) ? (bool) $instance['related'] : $this->defaults['related'];

		if ( $related && ! is_single() && ! is_singular() && ! is_page() ) {
			return;
		}

		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		$post_type      = $this->get_post_type_from_instance( $instance );
		$term           = $instance['term'] ?? '';
		$number         = isset( $instance['number'] ) ? absint( $instance['number'] ) : $this->defaults['number'];
		$orderby        = $instance['orderby'] ?? $this->defaults['orderby'];
		$meta_key       = $instance['meta_key'] ?? '';
		$meta_value     = $instance['meta_value'] ?? '';
		$date_interval  = $instance['date_interval'] ?? $this->defaults['date_interval'];
		$order          = $instance['order'] ?? $this->defaults['order'];
		$group_category = isset( $instance['group_category'] ) ? (bool) $instance['group_category'] : $this->defaults['group_category'];

		$query_args = array(
			'post_type'           => $post_type,
			'posts_per_page'      => $number,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'order'               => $order
		);

		if ( ! empty( $meta_key ) ) {
			$query_args['meta_key'] = $meta_key;
		}

		if ( ! empty( $meta_value ) ) {
			$query_args['meta_value'] = $meta_value;
		}

		if ( ! empty( $meta_key ) && ! empty( $meta_value ) ) {
			$query_args['meta_query'] = array(
				'relation' => 'and',
				array(
					'key'   => $meta_key,
					'value' => $meta_value
				)
			);
		}

		if ( is_array( $orderby ) ) {
			$orderby = implode( ' ', $orderby );
		}

		$query_args['orderby'] = $orderby;

		if ( ! empty( $term ) && ! $group_category ) {
			$term = (array) $term;

			$tax_query = array(
				'relation' => 'or'
			);

			foreach ( $term as $value ) {
				$value = str_replace( ' ', '', $value );
				$parts = explode( ',', $value );

				if ( 2 == count( $parts ) ) {
					$tax_query[] = array(
						'taxonomy' => $parts[0],
						'field'    => 'id',
						'terms'    => $parts[1]
					);
				}
			}

			$query_args['tax_query'] = $tax_query;
		}

		switch ( $date_interval ) {
			case 'daily':
				$today = getdate();

				$query_args['date_query'] = array(
					array(
						'year'  => $today['year'],
						'month' => $today['mon'],
						'day'   => $today['mday']
					)
				);
				break;
			case 'weekly':
				$query_args['date_query'] = array(
					'year' => date( 'Y' ),
					'week' => date( 'W' )
				);
				break;
			case 'monthly':
				$today = getdate();

				$query_args['date_query'] = array(
					array(
						'year'  => $today['year'],
						'month' => $today['mon']
					)
				);
				break;
			case 'yearly':
				$today = getdate();

				$query_args['date_query'] = array(
					array(
						'year' => $today['year']
					)
				);
				break;
		}

		$query_args = apply_filters( 'hocwp_theme_widget_posts_query_args', $query_args, $instance, $args, $this );

		$display_type = $instance['display_type'] ?? $this->defaults['display_type'];

		if ( $group_category ) {
			$term = (array) $term;

			if ( ht()->array_has_value( $term ) ) {
				$list = '';

				foreach ( $term as $td ) {
					$value = str_replace( ' ', '', $td );
					$parts = explode( ',', $value );

					if ( 2 == count( $parts ) ) {
						$term = get_term( $parts[1], $parts[0] );

						$query_args['tax_query'] = array(
							array(
								'taxonomy' => $parts[0],
								'field'    => 'id',
								'terms'    => $parts[1]
							)
						);

						$query = new WP_Query( $query_args );

						if ( $query->have_posts() && $term instanceof WP_Term ) {
							$tmp = '<li>';

							$tmp .= '<a href="' . get_term_link( $term ) . '">' . $term->name . ' <span>(' . $query->post_count . ')</span></a>';

							$tmp .= '<ul class="posts">';

							while ( $query->have_posts() ) {
								$query->the_post();
								ob_start();
								?>
                                <li>
                                    <a href="<?php the_permalink(); ?>"
                                       title="<?php the_title(); ?>"><?php the_title(); ?></a>
                                </li>
								<?php
								$tmp .= ob_get_clean();
							}

							wp_reset_postdata();

							$tmp  .= '</ul></li>';
							$list .= $tmp;
						}
					}
				}

				if ( ! empty( $list ) ) {
					$list = '<ul class="group-posts">' . $list . '</ul>';

					do_action( 'hocwp_theme_widget_before', $args, $instance, $this );

					echo $list;

					do_action( 'hocwp_theme_widget_after', $args, $instance, $this );
				}
			}
		} else {
			if ( $related ) {
				$query = ht_query()->related_posts( $query_args );
			} else {
				$query = new WP_Query( $query_args );
			}

			if ( $query->have_posts() ) {
				$term_as_title = $instance['term_as_title'] ?? $this->defaults['term_as_title'];

				if ( ( 1 == $term_as_title || $term_as_title ) && ht()->array_has_value( $term ) ) {
					reset( $term );
					$value = current( $term );

					$value = str_replace( ' ', '', $value );
					$parts = explode( ',', $value );

					if ( 2 == count( $parts ) ) {
						$term = get_term( $parts[1], $parts[0] );

						if ( $term instanceof WP_Term ) {
							$instance['show_title'] = false;
						}
					}
				}

				do_action( 'hocwp_theme_widget_before', $args, $instance, $this );

				if ( $term instanceof WP_Term ) {
					$title_term_link = $instance['title_term_link'] ?? $this->defaults['title_term_link'];

					$text = $term->name;

					if ( 1 == $title_term_link || $title_term_link ) {
						$text = '<a href="' . get_term_link( $term ) . '">' . $text . '</a>';
					}

					echo '<h2 class="widget-title">' . $text . '</h2>';
				}

				$html = apply_filters( 'hocwp_theme_widget_posts_html', '', $query, $instance, $args, $this );

				if ( empty( $html ) ) {
					$more = $instance['view_more'] ?? '';
					$more = explode( '|', $more );

					$more_link = $more[0] ?? '';
					$more_text = $more[1] ?? '';
					$more_pos  = $more[2] ?? 'bottom';
					$more_pos  = strtolower( $more_pos );

					if ( ! empty( $more_link ) ) {
						if ( empty( $more_text ) ) {
							$more_text = $more_link;
						}

						$more_link = sprintf( '<div class="more-link"><a class="view-more %s" href="%s">%s</a></div>', esc_attr( $more_pos ), esc_url( $more_link ), $more_text );

						if ( 'top' == $more_pos ) {
							echo $more_link;
						}
					}

					global $hocwp_theme;
					$hocwp_theme->loop_data['list']       = true;
					$hocwp_theme->loop_data['on_sidebar'] = true;
					$hocwp_theme->loop_data['template']   = 'sidebar';

					$hocwp_theme->loop_data['widget'] = $this;

					$hocwp_theme->loop_data['widget_args']     = $args;
					$hocwp_theme->loop_data['widget_instance'] = $instance;

					$hocwp_theme->loop_data['pagination_args'] = null;
					$hocwp_theme->loop_data['content_none']    = false;
					$hocwp_theme->loop_data['display_type']    = $display_type;
					do_action( 'hocwp_theme_loop', $query );

					if ( ! empty( $more_link ) && 'bottom' == $more_pos ) {
						echo $more_link;
					}
				} else {
					echo $html;
				}

				do_action( 'hocwp_theme_widget_after', $args, $instance, $this );
			}
		}
	}

	public function hocwp_theme_search_meta_key_ajax_callback() {
		$suggestions = array();
		$search      = $_GET['term'] ?? '';

		if ( ! empty( $search ) ) {
			$tr_name = 'hocwp_theme_search_meta_key_' . md5( $search );

			if ( false === get_transient( $tr_name ) ) {
				$keys = ht_query()->meta_keys( $search );

				if ( ht()->array_has_value( $keys ) ) {
					foreach ( $keys as $key ) {
						$suggestions[] = array(
							'value' => $key,
							'label' => $key,
							'term'  => $key
						);
					}

					set_transient( $tr_name, $keys, DAY_IN_SECONDS );
				}
			}
		}

		wp_send_json( $suggestions );
	}

	public function form( $instance ) {
		$post_types = get_post_types( array( 'public' => true ) );
		unset( $post_types['attachment'] );
		$post_types = apply_filters( 'hocwp_theme_widget_posts_post_types', $post_types, $this );

		$post_type  = $instance['post_type'] ?? $this->defaults['post_type'];
		$taxonomies = array();

		if ( is_array( $post_type ) ) {
			foreach ( $post_type as $type ) {
				$tmp        = get_object_taxonomies( $type );
				$taxonomies = array_merge( $tmp, $taxonomies );
			}
		} else {
			$taxonomies = get_object_taxonomies( $post_type );
		}

		if ( $key = array_search( 'post_format', $taxonomies ) ) {
			unset( $taxonomies[ $key ] );
		}

		foreach ( $taxonomies as $key => $tax_name ) {
			$tax_obj = get_taxonomy( $tax_name );

			if ( ! $tax_obj->public ) {
				unset( $taxonomies[ $key ] );
			}
		}

		$term           = $instance['term'] ?? '';
		$thumbnail_size = $instance['thumbnail_size'] ?? $this->defaults['thumbnail_size'];
		$crop_thumbnail = $instance['crop_thumbnail'] ?? $this->defaults['crop_thumbnail'];
		$number         = isset( $instance['number'] ) ? absint( $instance['number'] ) : $this->defaults['number'];
		$group_category = isset( $instance['group_category'] ) ? (bool) $instance['group_category'] : $this->defaults['group_category'];

		$orderbys = array(
			'none'           => __( 'No order', 'hocwp-theme' ),
			'ID'             => __( 'Post id', 'hocwp-theme' ),
			'author'         => __( 'Author', 'hocwp-theme' ),
			'title'          => __( 'Post title', 'hocwp-theme' ),
			'name'           => __( 'Post name', 'hocwp-theme' ),
			'type'           => __( 'Post type', 'hocwp-theme' ),
			'date'           => __( 'Post date', 'hocwp-theme' ),
			'modified'       => __( 'Last modified date', 'hocwp-theme' ),
			'parent'         => __( 'Post parent id', 'hocwp-theme' ),
			'rand'           => __( 'Random post', 'hocwp-theme' ),
			'comment_count'  => __( 'Number of comments', 'hocwp-theme' ),
			'relevance'      => __( 'Search term', 'hocwp-theme' ),
			'menu_order'     => __( 'Menu order', 'hocwp-theme' ),
			'meta_value'     => __( 'Meta value', 'hocwp-theme' ),
			'meta_value_num' => __( 'Numeric meta value', 'hocwp-theme' )
		);

		$orderby         = $instance['orderby'] ?? $this->defaults['orderby'];
		$meta_key        = $instance['meta_key'] ?? '';
		$meta_value      = $instance['meta_value'] ?? '';
		$show_date       = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : $this->defaults['show_date'];
		$show_date_diff  = isset( $instance['show_date_diff'] ) ? (bool) $instance['show_date_diff'] : $this->defaults['show_date_diff'];
		$related         = isset( $instance['related'] ) ? (bool) $instance['related'] : $this->defaults['related'];
		$term_as_title   = isset( $instance['term_as_title'] ) ? (bool) $instance['term_as_title'] : $this->defaults['term_as_title'];
		$title_term_link = isset( $instance['title_term_link'] ) ? (bool) $instance['title_term_link'] : $this->defaults['title_term_link'];
		$date_intervals  = ht_util()->date_intervals();
		$date_interval   = $instance['date_interval'] ?? $this->defaults['date_interval'];

		$orders = array(
			'DESC' => __( 'DESC', 'hocwp-theme' ),
			'ASC'  => __( 'ASC', 'hocwp-theme' )
		);

		$order       = $instance['order'] ?? $this->defaults['order'];
		$show_author = isset( $instance['show_author'] ) ? (bool) $instance['show_author'] : $this->defaults['show_author'];

		$show_comment_count = isset( $instance['show_comment_count'] ) ? (bool) $instance['show_comment_count'] : $this->defaults['show_comment_count'];

		$title_length   = isset( $instance['title_length'] ) ? absint( $instance['title_length'] ) : $this->defaults['title_length'];
		$show_excerpt   = isset( $instance['show_excerpt'] ) ? (bool) $instance['show_excerpt'] : $this->defaults['show_excerpt'];
		$excerpt_length = isset( $instance['excerpt_length'] ) ? absint( $instance['excerpt_length'] ) : $this->defaults['excerpt_length'];
		$show_category  = isset( $instance['show_category'] ) ? (bool) $instance['show_category'] : $this->defaults['show_category'];
		$view_more      = $instance['view_more'] ?? '';

		do_action( 'hocwp_theme_widget_form_before', $instance, $this );
		?>
        <nav class="nav-tab-wrapper wp-clearfix">
            <a href="#widgetPostGeneral<?php echo $this->number; ?>"
               class="nav-tab nav-tab-active"><?php _e( 'General', 'hocwp-theme' ); ?></a>
            <a href="#widgetPostAdvanced<?php echo $this->number; ?>"
               class="nav-tab"><?php _e( 'Advanced', 'hocwp-theme' ); ?></a>
            <a href="#widgetPostSortable<?php echo $this->number; ?>"
               class="nav-tab"><?php _e( 'Sortable', 'hocwp-theme' ); ?></a>
        </nav>
        <div class="tab-content">
            <div id="widgetPostGeneral<?php echo $this->number; ?>" class="tab-pane active">
                <div style="margin: 1em 0">
					<?php
					$args = array(
						'for'  => $this->get_field_id( 'post_type' ),
						'text' => __( 'Post type:', 'hocwp-theme' )
					);

					ht_html_field()->label( $args );

					$args = array(
						'id'       => $this->get_field_id( 'post_type' ),
						'name'     => $this->get_field_name( 'post_type' ),
						'options'  => $post_types,
						'class'    => 'widefat',
						'multiple' => 'multiple',
						'value'    => $post_type
					);

					ht_html_field()->chosen( $args );
					?>
                </div>
                <p>
					<?php
					$args = array(
						'for'  => $this->get_field_id( 'thumbnail_size' ),
						'text' => __( 'Thumbnail size:', 'hocwp-theme' )
					);

					ht_html_field()->label( $args );

					$args = array(
						'id'    => $this->get_field_id( 'thumbnail_size' ),
						'name'  => $this->get_field_name( 'thumbnail_size' ),
						'value' => $thumbnail_size
					);

					ht_html_field()->size( $args );
					?>
                </p>
                <p>
                    <input class="checkbox" type="checkbox"<?php checked( $crop_thumbnail ); ?>
                           id="<?php echo $this->get_field_id( 'crop_thumbnail' ); ?>"
                           name="<?php echo $this->get_field_name( 'crop_thumbnail' ); ?>"/>
                    <label
                            for="<?php echo $this->get_field_id( 'crop_thumbnail' ); ?>"><?php _e( 'Crop thumbnail to exact dimensions?', 'hocwp-theme' ); ?></label>
                </p>
                <p>
                    <label
                            for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:', 'hocwp-theme' ); ?></label>
                    <input class="small-text" id="<?php echo $this->get_field_id( 'number' ); ?>"
                           name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" step="1" min="1"
                           value="<?php echo $number; ?>" size="3"/>
                </p>
                <p>
                    <label
                            for="<?php echo $this->get_field_id( 'title_length' ); ?>"><?php _e( 'Post title length:', 'hocwp-theme' ); ?></label>
                    <input class="small-text" id="<?php echo $this->get_field_id( 'title_length' ); ?>"
                           name="<?php echo $this->get_field_name( 'title_length' ); ?>" type="number" step="1" min="1"
                           value="<?php echo $title_length; ?>" size="3"/>
                </p>
            </div>
            <div id="widgetPostAdvanced<?php echo $this->number; ?>" class="tab-pane">
                <div style="margin: 1em 0">
					<?php
					$args = array(
						'for'  => $this->get_field_id( 'term' ),
						'text' => __( 'Term:', 'hocwp-theme' )
					);

					ht_html_field()->label( $args );

					$args = array(
						'id'         => $this->get_field_id( 'term' ),
						'name'       => $this->get_field_name( 'term' ),
						'taxonomy'   => $taxonomies,
						'class'      => 'widefat',
						'multiple'   => 'multiple',
						'value'      => $term,
						'attributes' => array(
							'data-placeholder' => __( 'Choose some terms', 'hocwp-theme' )
						),
						'callback'   => 'select_term'
					);

					ht_html_field()->chosen( $args );
					?>
                </div>
                <div style="margin: 1em 0">
					<?php
					$args = array(
						'for'  => $this->get_field_id( 'orderby' ),
						'text' => __( 'Order by:', 'hocwp-theme' )
					);

					ht_html_field()->label( $args );

					$args = array(
						'id'       => $this->get_field_id( 'orderby' ),
						'name'     => $this->get_field_name( 'orderby' ),
						'options'  => $orderbys,
						'class'    => 'widefat',
						'multiple' => 'multiple',
						'value'    => $orderby
					);

					ht_html_field()->chosen( $args );
					?>
                </div>
                <p>
                    <label
                            for="<?php echo $this->get_field_id( 'meta_key' ); ?>"><?php _e( 'Meta key:', 'hocwp-theme' ); ?></label>
                    <input class="widefat autocomplete" id="<?php echo $this->get_field_id( 'meta_key' ); ?>"
                           name="<?php echo $this->get_field_name( 'meta_key' ); ?>" data-autocomplete="1" type="text"
                           value="<?php echo $meta_key; ?>" data-action="hocwp_theme_search_meta_key"/>
                </p>
                <p>
                    <label
                            for="<?php echo $this->get_field_id( 'meta_value' ); ?>"><?php _e( 'Meta value:', 'hocwp-theme' ); ?></label>
                    <input class="widefat" id="<?php echo $this->get_field_id( 'meta_value' ); ?>"
                           name="<?php echo $this->get_field_name( 'meta_value' ); ?>" type="text"
                           value="<?php echo $meta_value; ?>"/>
                </p>
                <p>
                    <label
                            for="<?php echo $this->get_field_id( 'excerpt_length' ); ?>"><?php _e( 'Post excerpt length:', 'hocwp-theme' ); ?></label>
                    <input class="medium-text" id="<?php echo $this->get_field_id( 'excerpt_length' ); ?>"
                           name="<?php echo $this->get_field_name( 'excerpt_length' ); ?>" type="number" step="1"
                           min="1"
                           value="<?php echo $excerpt_length; ?>" size="4"/>
                </p>
                <fieldset>
                    <legend><?php _e( 'Entry meta:', 'hocwp-theme' ); ?></legend>
                    <p>
                        <input class="checkbox" type="checkbox"<?php checked( $show_date ); ?>
                               id="<?php echo $this->get_field_id( 'show_date' ); ?>"
                               name="<?php echo $this->get_field_name( 'show_date' ); ?>"/>
                        <label
                                for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date?', 'hocwp-theme' ); ?></label>
                    </p>
                    <p>
                        <input class="checkbox" type="checkbox"<?php checked( $show_date_diff ); ?>
                               id="<?php echo $this->get_field_id( 'show_date_diff' ); ?>"
                               name="<?php echo $this->get_field_name( 'show_date_diff' ); ?>"/>
                        <label
                                for="<?php echo $this->get_field_id( 'show_date_diff' ); ?>"><?php _e( 'Display post date human time diff?', 'hocwp-theme' ); ?></label>
                    </p>
                    <p>
                        <input class="checkbox" type="checkbox"<?php checked( $show_author ); ?>
                               id="<?php echo $this->get_field_id( 'show_author' ); ?>"
                               name="<?php echo $this->get_field_name( 'show_author' ); ?>"/>
                        <label
                                for="<?php echo $this->get_field_id( 'show_author' ); ?>"><?php _e( 'Display post author?', 'hocwp-theme' ); ?></label>
                    </p>
                    <p>
                        <input class="checkbox" type="checkbox"<?php checked( $show_comment_count ); ?>
                               id="<?php echo $this->get_field_id( 'show_comment_count' ); ?>"
                               name="<?php echo $this->get_field_name( 'show_comment_count' ); ?>"/>
                        <label
                                for="<?php echo $this->get_field_id( 'show_comment_count' ); ?>"><?php _e( 'Display post comment count?', 'hocwp-theme' ); ?></label>
                    </p>
                    <p>
                        <input class="checkbox" type="checkbox"<?php checked( $show_excerpt ); ?>
                               id="<?php echo $this->get_field_id( 'show_excerpt' ); ?>"
                               name="<?php echo $this->get_field_name( 'show_excerpt' ); ?>"/>
                        <label
                                for="<?php echo $this->get_field_id( 'show_excerpt' ); ?>"><?php _e( 'Display post excerpt?', 'hocwp-theme' ); ?></label>
                    </p>
                    <p>
                        <input class="checkbox" type="checkbox"<?php checked( $show_category ); ?>
                               id="<?php echo $this->get_field_id( 'show_category' ); ?>"
                               name="<?php echo $this->get_field_name( 'show_category' ); ?>"/>
                        <label
                                for="<?php echo $this->get_field_id( 'show_category' ); ?>"><?php _e( 'Display post category?', 'hocwp-theme' ); ?></label>
                    </p>
                </fieldset>
                <p>
                    <input class="checkbox" type="checkbox"<?php checked( $related ); ?>
                           id="<?php echo $this->get_field_id( 'related' ); ?>"
                           name="<?php echo $this->get_field_name( 'related' ); ?>"/>
                    <label
                            for="<?php echo $this->get_field_id( 'related' ); ?>"><?php _e( 'Display related posts?', 'hocwp-theme' ); ?></label>
                </p>
                <p>
					<?php
					$args = array(
						'for'  => $this->get_field_id( 'date_interval' ),
						'text' => __( 'Date interval:', 'hocwp-theme' )
					);

					ht_html_field()->label( $args );

					$args = array(
						'id'      => $this->get_field_id( 'date_interval' ),
						'name'    => $this->get_field_name( 'date_interval' ),
						'options' => $date_intervals,
						'class'   => 'widefat',
						'value'   => $date_interval
					);

					ht_html_field()->select( $args );
					?>
                </p>
                <p>
					<?php
					$args = array(
						'for'  => $this->get_field_id( 'order' ),
						'text' => __( 'Order:', 'hocwp-theme' )
					);

					ht_html_field()->label( $args );

					$args = array(
						'id'      => $this->get_field_id( 'order' ),
						'name'    => $this->get_field_name( 'order' ),
						'options' => $orders,
						'class'   => 'widefat',
						'value'   => $order
					);

					ht_html_field()->select( $args );
					?>
                </p>
                <p>
					<?php
					$args = array(
						'for'  => $this->get_field_id( 'display_type' ),
						'text' => __( 'Display type:', 'hocwp-theme' )
					);

					ht_html_field()->label( $args );

					$args = array(
						'id'      => $this->get_field_id( 'display_type' ),
						'name'    => $this->get_field_name( 'display_type' ),
						'options' => array(
							''                => __( '-- Choose style --', 'hocwp-theme' ),
							'full_first'      => __( 'First post with full width thumbnail', 'hocwp-theme' ),
							'full_last'       => __( 'Last post with full width thumbnail', 'hocwp-theme' ),
							'full_odd'        => __( 'Display full width thumbnail for odd posts', 'hocwp-theme' ),
							'full_even'       => __( 'Display full width thumbnail for even posts', 'hocwp-theme' ),
							'full_first_last' => __( 'First and last post with full width thumbnail', 'hocwp-theme' ),
							'full'            => __( 'Display all posts with full width thumbnail', 'hocwp-theme' ),
						),
						'class'   => 'widefat',
						'value'   => $instance['display_type'] ?? $this->defaults['display_type']
					);

					ht_html_field()->select( $args );
					?>
                </p>
                <p>
                    <input class="checkbox" type="checkbox"<?php checked( $term_as_title ); ?>
                           id="<?php echo $this->get_field_id( 'term_as_title' ); ?>"
                           name="<?php echo $this->get_field_name( 'term_as_title' ); ?>"/>
                    <label
                            for="<?php echo $this->get_field_id( 'term_as_title' ); ?>"><?php _e( 'Display term as widget title?', 'hocwp-theme' ); ?></label>
                </p>
                <p>
                    <input class="checkbox" type="checkbox"<?php checked( $title_term_link ); ?>
                           id="<?php echo $this->get_field_id( 'title_term_link' ); ?>"
                           name="<?php echo $this->get_field_name( 'title_term_link' ); ?>"/>
                    <label
                            for="<?php echo $this->get_field_id( 'title_term_link' ); ?>"><?php _e( 'Use term link for widget title?', 'hocwp-theme' ); ?></label>
                </p>
                <p>
                    <input class="checkbox" type="checkbox"<?php checked( $group_category ); ?>
                           id="<?php echo $this->get_field_id( 'group_category' ); ?>"
                           name="<?php echo $this->get_field_name( 'group_category' ); ?>"/>
                    <label
                            for="<?php echo $this->get_field_id( 'group_category' ); ?>"><?php _e( 'Group posts by each category?', 'hocwp-theme' ); ?></label>
                </p>
                <p>
                    <label
                            for="<?php echo $this->get_field_id( 'view_more' ); ?>"><?php _e( 'View more:', 'hocwp-theme' ); ?></label>
                    <input class="widefat" id="<?php echo $this->get_field_id( 'view_more' ); ?>"
                           name="<?php echo $this->get_field_name( 'view_more' ); ?>" type="text"
                           value="<?php echo $view_more; ?>"/>
                    <small class="desc"><em><?php _e( 'Show more link, syntax: URL|TEXT|POSITION. For example: https://ldcuong.com|LD Cuong|top.', 'hocwp-theme' ); ?></em></small>
                </p>
            </div>
            <div id="widgetPostSortable<?php echo $this->number; ?>" class="tab-pane">

            </div>
        </div>
		<?php
		do_action( 'hocwp_theme_widget_form_after', $instance, $this );
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']              = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['post_type']          = $new_instance['post_type'] ?? $this->defaults['post_type'];
		$instance['term']               = $new_instance['term'] ?? '';
		$instance['thumbnail_size']     = $new_instance['thumbnail_size'] ?? $this->defaults['thumbnail_size'];
		$instance['crop_thumbnail']     = isset( $new_instance['crop_thumbnail'] ) && $new_instance['crop_thumbnail'];
		$instance['orderby']            = $new_instance['orderby'] ?? $this->defaults['orderby'];
		$instance['number']             = isset( $new_instance['number'] ) ? absint( $new_instance['number'] ) : $this->defaults['number'];
		$instance['title_length']       = isset( $new_instance['title_length'] ) ? absint( $new_instance['title_length'] ) : $this->defaults['title_length'];
		$instance['show_date']          = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : $this->defaults['show_date'];
		$instance['show_date_diff']          = isset( $new_instance['show_date_diff'] ) ? (bool) $new_instance['show_date_diff'] : $this->defaults['show_date_diff'];
		$instance['related']            = isset( $new_instance['related'] ) ? (bool) $new_instance['related'] : $this->defaults['related'];
		$instance['term_as_title']      = isset( $new_instance['term_as_title'] ) ? (bool) $new_instance['term_as_title'] : $this->defaults['term_as_title'];
		$instance['title_term_link']    = isset( $new_instance['title_term_link'] ) ? (bool) $new_instance['title_term_link'] : $this->defaults['title_term_link'];
		$instance['date_interval']      = $new_instance['date_interval'] ?? $this->defaults['date_interval'];
		$instance['meta_key']           = isset( $new_instance['meta_key'] ) ? sanitize_title( $new_instance['meta_key'] ) : '';
		$instance['meta_value']         = $new_instance['meta_value'] ?? '';
		$instance['order']              = $new_instance['order'] ?? $this->defaults['order'];
		$instance['display_type']       = $new_instance['display_type'] ?? $this->defaults['display_type'];
		$instance['show_author']        = isset( $new_instance['show_author'] ) ? (bool) $new_instance['show_author'] : $this->defaults['show_author'];
		$instance['show_excerpt']       = isset( $new_instance['show_excerpt'] ) ? (bool) $new_instance['show_excerpt'] : $this->defaults['show_excerpt'];
		$instance['show_comment_count'] = isset( $new_instance['show_comment_count'] ) ? (bool) $new_instance['show_comment_count'] : $this->defaults['show_comment_count'];
		$instance['excerpt_length']     = $new_instance['excerpt_length'] ?? $this->defaults['excerpt_length'];
		$instance['group_category']     = isset( $new_instance['group_category'] ) ? (bool) $new_instance['group_category'] : $this->defaults['group_category'];
		$instance['show_category']      = isset( $new_instance['show_category'] ) ? (bool) $new_instance['show_category'] : $this->defaults['show_category'];
		$instance['view_more']          = $new_instance['view_more'] ?? '';

		return $instance;
	}
}