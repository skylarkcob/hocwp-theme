<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Widget_Terms extends WP_Widget {
	public $defaults;

	public function __construct() {
		$this->defaults  = array(
			'related'    => false,
			'hide_empty' => true,
			'child_of'   => false,
			'number'     => 5,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'show_count' => false,
			'taxonomy'   => 'category'
		);
		$this->defaults  = apply_filters( 'hocwp_theme_widget_terms_defaults', $this->defaults, $this );
		$widget_options  = array(
			'classname'   => 'hocwp-theme-widget-terms hocwp-widget-term',
			'description' => _x( 'A list of terms.', 'widget hocwp term', 'hocwp-theme' )
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
		$order      = isset( $instance['order'] ) ? $instance['order'] : $this->defaults['order'];
		$hide_empty = isset( $instance['hide_empty'] ) ? (bool) $instance['hide_empty'] : $this->defaults['hide_empty'];
		$child_of   = isset( $instance['child_of'] ) ? (bool) $instance['child_of'] : $this->defaults['child_of'];

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

		$query_args = apply_filters( 'hocwp_theme_widget_terms_query_args', $query_args, $instance, $args, $this );

		$terms = HT_Util()->get_terms( 'category', $query_args );

		if ( HT()->array_has_value( $terms ) ) {
			do_action( 'hocwp_theme_widget_before', $args, $instance, $this );
			?>
            <ul>
				<?php
				foreach ( $terms as $term ) {
					?>
                    <li>
                        <a class="<?php echo $term->taxonomy; ?>"
                           href="<?php echo get_term_link( $term ); ?>"><?php echo $term->name; ?></a>
                    </li>
					<?php
				}
				?>
            </ul>
			<?php
			do_action( 'hocwp_theme_widget_after', $args, $instance, $this );
		}
	}

	public function form( $instance ) {
		$number     = isset( $instance['number'] ) ? absint( $instance['number'] ) : $this->defaults['number'];
		$related    = isset( $instance['related'] ) ? (bool) $instance['related'] : $this->defaults['related'];
		$taxonomy   = isset( $instance['taxonomy'] ) ? $instance['taxonomy'] : $this->defaults['taxonomy'];
		$taxonomies = get_taxonomies( array( 'public' => true ) );
		$orderbys   = array(
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
		$orderby    = isset( $instance['orderby'] ) ? $instance['orderby'] : $this->defaults['orderby'];
		$orders     = array(
			'DESC' => __( 'DESC', 'hocwp-theme' ),
			'ASC'  => __( 'ASC', 'hocwp-theme' )
		);
		$order      = isset( $instance['order'] ) ? $instance['order'] : $this->defaults['order'];
		$hide_empty = isset( $instance['hide_empty'] ) ? (bool) $instance['hide_empty'] : $this->defaults['hide_empty'];
		$child_of   = isset( $instance['child_of'] ) ? (bool) $instance['child_of'] : $this->defaults['child_of'];
		$show_count = isset( $instance['show_count'] ) ? (bool) $instance['show_count'] : $this->defaults['show_count'];

		do_action( 'hocwp_theme_widget_form_before', $instance, $this );
		?>
        <div style="margin: 1em 0">
			<?php
			$args = array(
				'for'  => $this->get_field_id( 'taxonomy' ),
				'text' => 'Taxonomy:'
			);
			HT_HTML_Field()->label( $args );
			$args = array(
				'id'       => $this->get_field_id( 'taxonomy' ),
				'name'     => $this->get_field_name( 'taxonomy' ),
				'options'  => $taxonomies,
				'class'    => 'widefat',
				'multiple' => 'multiple',
				'value'    => $taxonomy
			);
			HT_HTML_Field()->chosen( $args );
			?>
        </div>
        <p>
            <label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of terms to show:', 'hocwp-theme' ); ?></label>
            <input class="tiny-text" id="<?php echo $this->get_field_id( 'number' ); ?>"
                   name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" step="1" min="1"
                   value="<?php echo $number; ?>" size="3"/>
        </p>
        <p>
            <input class="checkbox" type="checkbox"<?php checked( $related ); ?>
                   id="<?php echo $this->get_field_id( 'related' ); ?>"
                   name="<?php echo $this->get_field_name( 'related' ); ?>"/>
            <label for="<?php echo $this->get_field_id( 'related' ); ?>"><?php _e( 'Display related terms?', 'hocwp-theme' ); ?></label>
        </p>
        <p>
			<?php
			$args = array(
				'for'  => $this->get_field_id( 'orderby' ),
				'text' => __( 'Order by:', 'hocwp-theme' )
			);
			HT_HTML_Field()->label( $args );
			$args = array(
				'id'       => $this->get_field_id( 'orderby' ),
				'name'     => $this->get_field_name( 'orderby' ),
				'options'  => $orderbys,
				'class'    => 'widefat',
				'value'    => $orderby
			);
			HT_HTML_Field()->select( $args );
			?>
        </p>
        <p>
			<?php
			$args = array(
				'for'  => $this->get_field_id( 'order' ),
				'text' => __( 'Order:', 'hocwp-theme' )
			);
			HT_HTML_Field()->label( $args );
			$args = array(
				'id'      => $this->get_field_id( 'order' ),
				'name'    => $this->get_field_name( 'order' ),
				'options' => $orders,
				'class'   => 'widefat',
				'value'   => $order
			);
			HT_HTML_Field()->select( $args );
			?>
        </p>
        <p>
            <input class="checkbox" type="checkbox"<?php checked( $hide_empty ); ?>
                   id="<?php echo $this->get_field_id( 'hide_empty' ); ?>"
                   name="<?php echo $this->get_field_name( 'hide_empty' ); ?>"/>
            <label for="<?php echo $this->get_field_id( 'hide_empty' ); ?>"><?php _e( 'Hide terms not assigned to any posts?', 'hocwp-theme' ); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox"<?php checked( $child_of ); ?>
                   id="<?php echo $this->get_field_id( 'child_of' ); ?>"
                   name="<?php echo $this->get_field_name( 'child_of' ); ?>"/>
            <label for="<?php echo $this->get_field_id( 'child_of' ); ?>"><?php _e( 'Get childs of current term?', 'hocwp-theme' ); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox"<?php checked( $show_count ); ?>
                   id="<?php echo $this->get_field_id( 'show_count' ); ?>"
                   name="<?php echo $this->get_field_name( 'show_count' ); ?>"/>
            <label for="<?php echo $this->get_field_id( 'show_count' ); ?>"><?php _e( 'Display post count?', 'hocwp-theme' ); ?></label>
        </p>
		<?php
		do_action( 'hocwp_theme_widget_form_after', $instance, $this );
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']      = sanitize_text_field( $new_instance['title'] );
		$instance['taxonomy']   = isset( $new_instance['taxonomy'] ) ? $new_instance['taxonomy'] : $this->defaults['taxonomy'];
		$instance['number']     = isset( $new_instance['number'] ) ? absint( $new_instance['number'] ) : $this->defaults['number'];
		$instance['related']    = isset( $new_instance['related'] ) ? (bool) $new_instance['related'] : $this->defaults['related'];
		$instance['orderby']    = isset( $new_instance['orderby'] ) ? $new_instance['orderby'] : $this->defaults['orderby'];
		$instance['order']      = isset( $new_instance['order'] ) ? $new_instance['order'] : $this->defaults['order'];
		$instance['hide_empty'] = isset( $new_instance['hide_empty'] ) ? (bool) $new_instance['hide_empty'] : false;
		$instance['child_of']   = isset( $new_instance['child_of'] ) ? (bool) $new_instance['child_of'] : $this->defaults['child_of'];
		$instance['show_count'] = isset( $new_instance['show_count'] ) ? (bool) $new_instance['show_count'] : $this->defaults['show_count'];

		return $instance;
	}
}