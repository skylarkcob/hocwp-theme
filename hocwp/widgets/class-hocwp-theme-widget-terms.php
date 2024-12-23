<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Widget_Terms extends WP_Widget {
	public $defaults;

	public function __construct() {
		$this->defaults = array(
			'related'          => false,
			'hide_empty'       => true,
			'child_of'         => false,
			'number'           => 5,
			'orderby'          => 'name',
			'order'            => 'ASC',
			'show_count'       => false,
			'search_filter'    => false,
			'taxonomy'         => 'category',
			'current_as_title' => false,
			'hierarchical'     => false,
			'link_text_format' => ''
		);

		$this->defaults = apply_filters( 'hocwp_theme_widget_terms_defaults', $this->defaults, $this );

		$widget_options = array(
			'classname'                   => 'hocwp-theme-widget-terms hocwp-widget-term',
			'description'                 => _x( 'A list of terms.', 'widget description', 'hocwp-theme' ),
			'customize_selective_refresh' => true
		);

		$control_options = array(
			'width' => 400
		);
		parent::__construct( 'hocwp_widget_term', 'HocWP Terms', $widget_options, $control_options );
	}

	public function widget( $args, $instance ) {
		$instance = apply_filters( 'hocwp_theme_widget_terms_instance', $instance, $args, $this );

		$number   = isset( $instance['number'] ) ? absint( $instance['number'] ) : $this->defaults['number'];
		$related  = isset( $instance['related'] ) ? (bool) $instance['related'] : $this->defaults['related'];
		$taxonomy = isset( $instance['taxonomy'] ) ? $instance['taxonomy'] : $this->defaults['taxonomy'];
		$orderby  = isset( $instance['orderby'] ) ? $instance['orderby'] : $this->defaults['orderby'];

		if ( is_array( $orderby ) ) {
			$orderby = array_shift( $orderby );
		}

		$order        = isset( $instance['order'] ) ? $instance['order'] : $this->defaults['order'];
		$hide_empty   = isset( $instance['hide_empty'] ) ? (bool) $instance['hide_empty'] : $this->defaults['hide_empty'];
		$child_of     = isset( $instance['child_of'] ) ? (bool) $instance['child_of'] : $this->defaults['child_of'];
		$hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : $this->defaults['hierarchical'];
		$show_count   = isset( $instance['show_count'] ) ? (bool) $instance['show_count'] : $this->defaults['show_count'];

		$query_args = array(
			'number'     => $number,
			'taxonomy'   => $taxonomy,
			'orderby'    => $orderby,
			'order'      => $order,
			'hide_empty' => $hide_empty
		);

		if ( $child_of ) {
			if ( ! is_category() && ! is_tag() && ! is_tax() ) {
				return;
			}

			$obj = get_queried_object();

			$query_args['child_of'] = $obj->term_id;
		}

		if ( $hierarchical ) {
			if ( is_array( $taxonomy ) ) {
				$taxonomy = current( $taxonomy );
			}

			$targs = array(
				'taxonomy'   => $taxonomy,
				'title_li'   => '',
				'show_count' => $show_count
			);

			$query_args = wp_parse_args( $targs, $query_args );
		}

		$query_args = apply_filters( 'hocwp_theme_widget_terms_query_args', $query_args, $instance, $args, $this );

		$terms = ht_util()->get_terms( 'category', $query_args );

		if ( ht()->array_has_value( $terms ) ) {
			$current_as_title = isset( $instance['current_as_title'] ) ? (bool) $instance['current_as_title'] : $this->defaults['current_as_title'];

			if ( $current_as_title && ( is_category() || is_tag() || is_tax() ) ) {
				$object            = get_queried_object();
				$instance['title'] = $object->name;
			}

			do_action( 'hocwp_theme_widget_before', $args, $instance, $this );

			$search_filter = isset( $instance['search_filter'] ) ? $instance['search_filter'] : $this->defaults['search_filter'];
			?>
			<div class="terms-box filter-box">
				<?php
				if ( $search_filter ) {
					if ( is_array( $taxonomy ) && 1 == count( $taxonomy ) ) {
						$taxonomy = current( $taxonomy );
					}

					$tax = get_taxonomy( $taxonomy );

					if ( $tax instanceof WP_Taxonomy ) {
						$placeholder = sprintf( __( 'Search %s', 'hocwp-theme' ), $tax->labels->singular_name );
					} else {
						if ( $hierarchical ) {
							$placeholder = __( 'Search category', 'hocwp-theme' );
						} else {
							$placeholder = __( 'Search tag', 'hocwp-theme' );
						}
					}
					?>
					<div class="input-search-cat">
						<input type="text" placeholder="<?php echo esc_attr( $placeholder ); ?>" value=""
						       class="form-control filter-input" onkeyup="hocwpThemeFilterList(this)">
					</div>
					<?php
				}
				?>
				<ul class="filter-list">
					<?php
					$link_text_format = isset( $instance['link_text_format'] ) ? $instance['link_text_format'] : $this->defaults['link_text_format'];

					if ( empty( $link_text_format ) ) {
						$link_text_format = '%term_name%';
					}

					if ( $hierarchical ) {
						wp_list_categories( $query_args );
					} else {
						foreach ( $terms as $term ) {
							if ( $term instanceof WP_Term ) {
								?>
								<li data-slug="<?php echo esc_attr( $term->slug ); ?>">
									<a class="<?php echo $term->taxonomy; ?>"
									   href="<?php echo get_term_link( $term ); ?>"
									   title="<?php echo esc_attr( $term->name ); ?>"><?php echo str_replace( '%term_name%', $term->name, $link_text_format ); ?></a>
								</li>
								<?php
							}
						}
					}
					?>
				</ul>
			</div>
			<?php
			do_action( 'hocwp_theme_widget_after', $args, $instance, $this );
		}
	}

	public function form( $instance ) {
		$number     = isset( $instance['number'] ) ? absint( $instance['number'] ) : $this->defaults['number'];
		$related    = isset( $instance['related'] ) ? (bool) $instance['related'] : $this->defaults['related'];
		$taxonomy   = isset( $instance['taxonomy'] ) ? $instance['taxonomy'] : $this->defaults['taxonomy'];
		$taxonomies = get_taxonomies( array( 'public' => true ) );

		$orderbys = array(
			'name'           => __( 'Term name', 'hocwp-theme' ),
			'slug'           => __( 'Term slug', 'hocwp-theme' ),
			'term_group'     => __( 'Term group', 'hocwp-theme' ),
			'term_id'        => __( 'Term id', 'hocwp-theme' ),
			'description'    => __( 'Description', 'hocwp-theme' ),
			'count'          => __( 'Term count', 'hocwp-theme' ),
			'meta_value'     => __( 'Meta value', 'hocwp-theme' ),
			'meta_value_num' => __( 'Numeric meta value', 'hocwp-theme' ),
			'none'           => __( 'No order', 'hocwp-theme' )
		);

		$orderby = isset( $instance['orderby'] ) ? $instance['orderby'] : $this->defaults['orderby'];

		$orders = array(
			'DESC' => __( 'DESC', 'hocwp-theme' ),
			'ASC'  => __( 'ASC', 'hocwp-theme' )
		);

		$order            = isset( $instance['order'] ) ? $instance['order'] : $this->defaults['order'];
		$hide_empty       = isset( $instance['hide_empty'] ) ? (bool) $instance['hide_empty'] : $this->defaults['hide_empty'];
		$child_of         = isset( $instance['child_of'] ) ? (bool) $instance['child_of'] : $this->defaults['child_of'];
		$show_count       = isset( $instance['show_count'] ) ? (bool) $instance['show_count'] : $this->defaults['show_count'];
		$current_as_title = isset( $instance['current_as_title'] ) ? (bool) $instance['current_as_title'] : $this->defaults['current_as_title'];
		$hierarchical     = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : $this->defaults['hierarchical'];

		$link_text_format = isset( $instance['link_text_format'] ) ? $instance['link_text_format'] : $this->defaults['link_text_format'];
		$search_filter    = isset( $instance['search_filter'] ) ? $instance['search_filter'] : $this->defaults['search_filter'];

		do_action( 'hocwp_theme_widget_form_before', $instance, $this );
		?>
		<nav class="nav-tab-wrapper wp-clearfix">
			<a href="#widgetTermGeneral<?php echo $this->number; ?>"
			   class="nav-tab nav-tab-active"><?php _e( 'General', 'hocwp-theme' ); ?></a>
			<a href="#widgetTermAdvanced<?php echo $this->number; ?>"
			   class="nav-tab"><?php _e( 'Advanced', 'hocwp-theme' ); ?></a>
			<a href="#widgetTermSortable<?php echo $this->number; ?>"
			   class="nav-tab"><?php _e( 'Sortable', 'hocwp-theme' ); ?></a>
		</nav>
		<div class="tab-content">
			<div id="widgetTermGeneral<?php echo $this->number; ?>" class="tab-pane active">
				<div style="margin: 1em 0">
					<?php
					$args = array(
						'for'  => $this->get_field_id( 'taxonomy' ),
						'text' => 'Taxonomy:'
					);

					ht_html_field()->label( $args );

					$args = array(
						'id'       => $this->get_field_id( 'taxonomy' ),
						'name'     => $this->get_field_name( 'taxonomy' ),
						'options'  => $taxonomies,
						'class'    => 'widefat',
						'multiple' => 'multiple',
						'value'    => $taxonomy
					);

					ht_html_field()->chosen( $args );
					?>
				</div>
				<p>
					<label
						for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of terms to show:', 'hocwp-theme' ); ?></label>
					<input class="small-text" id="<?php echo $this->get_field_id( 'number' ); ?>"
					       name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" step="1" min="1"
					       value="<?php echo $number; ?>" size="3"/>
				</p>

				<p>
					<input class="checkbox" type="checkbox"<?php checked( $hide_empty ); ?>
					       id="<?php echo $this->get_field_id( 'hide_empty' ); ?>"
					       name="<?php echo $this->get_field_name( 'hide_empty' ); ?>"/>
					<label
						for="<?php echo $this->get_field_id( 'hide_empty' ); ?>"><?php _e( 'Hide terms not assigned to any posts?', 'hocwp-theme' ); ?></label>
				</p>

				<p>
					<?php
					$args = array(
						'for'  => $this->get_field_id( 'orderby' ),
						'text' => __( 'Order by:', 'hocwp-theme' )
					);

					ht_html_field()->label( $args );

					$args = array(
						'id'      => $this->get_field_id( 'orderby' ),
						'name'    => $this->get_field_name( 'orderby' ),
						'options' => $orderbys,
						'class'   => 'widefat',
						'value'   => $orderby
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
			</div>
			<div id="widgetTermAdvanced<?php echo $this->number; ?>" class="tab-pane">
				<p>
					<label
						for="<?php echo $this->get_field_id( 'link_text_format' ); ?>"><?php _e( 'Link text format:', 'hocwp-theme' ); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id( 'link_text_format' ); ?>"
					       name="<?php echo $this->get_field_name( 'link_text_format' ); ?>" type="text"
					       value="<?php echo $link_text_format; ?>">
				</p>

				<p>
					<input class="checkbox" type="checkbox"<?php checked( $related ); ?>
					       id="<?php echo $this->get_field_id( 'related' ); ?>"
					       name="<?php echo $this->get_field_name( 'related' ); ?>"/>
					<label
						for="<?php echo $this->get_field_id( 'related' ); ?>"><?php _e( 'Display related terms?', 'hocwp-theme' ); ?></label>
				</p>

				<p>
					<input class="checkbox" type="checkbox"<?php checked( $child_of ); ?>
					       id="<?php echo $this->get_field_id( 'child_of' ); ?>"
					       name="<?php echo $this->get_field_name( 'child_of' ); ?>"/>
					<label
						for="<?php echo $this->get_field_id( 'child_of' ); ?>"><?php _e( 'Get childs of current term?', 'hocwp-theme' ); ?></label>
				</p>

				<p>
					<input class="checkbox" type="checkbox"<?php checked( $show_count ); ?>
					       id="<?php echo $this->get_field_id( 'show_count' ); ?>"
					       name="<?php echo $this->get_field_name( 'show_count' ); ?>"/>
					<label
						for="<?php echo $this->get_field_id( 'show_count' ); ?>"><?php _e( 'Display post count?', 'hocwp-theme' ); ?></label>
				</p>

				<p>
					<input class="checkbox" type="checkbox"<?php checked( $current_as_title ); ?>
					       id="<?php echo $this->get_field_id( 'current_as_title' ); ?>"
					       name="<?php echo $this->get_field_name( 'current_as_title' ); ?>"/>
					<label
						for="<?php echo $this->get_field_id( 'current_as_title' ); ?>"><?php _e( 'Display current term name as widget title?', 'hocwp-theme' ); ?></label>
				</p>

				<p>
					<input class="checkbox" type="checkbox"<?php checked( $hierarchical ); ?>
					       id="<?php echo $this->get_field_id( 'hierarchical' ); ?>"
					       name="<?php echo $this->get_field_name( 'hierarchical' ); ?>"/>
					<label
						for="<?php echo $this->get_field_id( 'hierarchical' ); ?>"><?php _e( 'Whether to include terms that have non-empty descendants?', 'hocwp-theme' ); ?></label>
				</p>

				<p>
					<input class="checkbox" type="checkbox"<?php checked( $search_filter ); ?>
					       id="<?php echo $this->get_field_id( 'search_filter' ); ?>"
					       name="<?php echo $this->get_field_name( 'search_filter' ); ?>"/>
					<label
						for="<?php echo $this->get_field_id( 'search_filter' ); ?>"><?php _e( 'Display search box for filter terms?', 'hocwp-theme' ); ?></label>
				</p>
			</div>
			<div id="widgetTermSortable<?php echo $this->number; ?>" class="tab-pane"></div>
		</div>
		<?php
		do_action( 'hocwp_theme_widget_form_after', $instance, $this );
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']            = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['taxonomy']         = isset( $new_instance['taxonomy'] ) ? $new_instance['taxonomy'] : $this->defaults['taxonomy'];
		$instance['number']           = isset( $new_instance['number'] ) ? absint( $new_instance['number'] ) : $this->defaults['number'];
		$instance['related']          = isset( $new_instance['related'] ) ? (bool) $new_instance['related'] : $this->defaults['related'];
		$instance['orderby']          = isset( $new_instance['orderby'] ) ? $new_instance['orderby'] : $this->defaults['orderby'];
		$instance['order']            = isset( $new_instance['order'] ) ? $new_instance['order'] : $this->defaults['order'];
		$instance['hide_empty']       = isset( $new_instance['hide_empty'] ) ? (bool) $new_instance['hide_empty'] : false;
		$instance['child_of']         = isset( $new_instance['child_of'] ) ? (bool) $new_instance['child_of'] : $this->defaults['child_of'];
		$instance['show_count']       = isset( $new_instance['show_count'] ) ? (bool) $new_instance['show_count'] : $this->defaults['show_count'];
		$instance['current_as_title'] = isset( $new_instance['current_as_title'] ) ? (bool) $new_instance['current_as_title'] : $this->defaults['current_as_title'];
		$instance['hierarchical']     = isset( $new_instance['hierarchical'] ) ? (bool) $new_instance['hierarchical'] : false;
		$instance['link_text_format'] = isset( $new_instance['link_text_format'] ) ? $new_instance['link_text_format'] : $this->defaults['link_text_format'];
		$instance['search_filter']    = isset( $new_instance['search_filter'] ) ? (bool) $new_instance['search_filter'] : $this->defaults['search_filter'];

		return $instance;
	}
}