<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Widget_Disqus_Recent_Comment extends WP_Widget {
	public $defaults;

	public function __construct() {
		$this->defaults = array(
			'num_items'      => 5,
			'hide_mods'      => 0,
			'excerpt_length' => 100,
			'hide_avatars'   => 0,
			'avatar_size'    => 32,
			'shortname'      => ''
		);

		$this->defaults = apply_filters( 'hocwp_theme_widget_disqus_recent_comment_defaults', $this->defaults, $this );

		$widget_options = array(
			'classname'                   => 'hocwp-theme-widget-disqus-recent-comment hocwp-widget-disqus-recent-comment',
			'description'                 => _x( 'A list of recent comments on Disqus system.', 'widget description', 'hocwp-theme' ),
			'customize_selective_refresh' => true
		);

		$control_options = array(
			'width' => 400
		);

		parent::__construct( 'hocwp_widget_disqus_recent_comment', 'HocWP Disqus Recent Comments', $widget_options, $control_options );
	}

	public function widget( $args, $instance ) {
		$instance = apply_filters( 'hocwp_theme_widget_disqus_recent_comment_instance', $instance, $args, $this );

		$num_items      = isset( $instance['num_items'] ) ? absint( $instance['num_items'] ) : $this->defaults['num_items'];
		$hide_mods      = isset( $instance['hide_mods'] ) ? absint( $instance['hide_mods'] ) : $this->defaults['hide_mods'];
		$excerpt_length = isset( $instance['excerpt_length'] ) ? absint( $instance['excerpt_length'] ) : $this->defaults['excerpt_length'];
		$hide_avatars   = isset( $instance['hide_avatars'] ) ? absint( $instance['hide_avatars'] ) : $this->defaults['hide_avatars'];
		$avatar_size    = isset( $instance['avatar_size'] ) ? absint( $instance['avatar_size'] ) : $this->defaults['avatar_size'];
		$shortname      = isset( $instance['shortname'] ) ? $instance['shortname'] : $this->defaults['shortname'];

		if ( empty( $shortname ) ) {
			$shortname = HT_Options()->get_tab( 'disqus_shortname', '', 'discussion' );
		}

		if ( empty( $shortname ) ) {
			return;
		}

		$url = 'https://' . $shortname;
		$url .= '.disqus.com/recent_comments_widget.js';

		$url = add_query_arg( array(
			'num_items'      => $num_items,
			'hide_mods'      => $hide_mods,
			'hide_avatars'   => $hide_avatars,
			'avatar_size'    => $avatar_size,
			'excerpt_length' => $excerpt_length
		), $url );

		do_action( 'hocwp_theme_widget_before', $args, $instance, $this );
		?>
		<div class="dsq-widget">
			<script src="<?php echo $url; ?>"></script>
		</div>
		<?php
		do_action( 'hocwp_theme_widget_after', $args, $instance, $this );
	}

	public function form( $instance ) {
		$num_items      = isset( $instance['num_items'] ) ? absint( $instance['num_items'] ) : $this->defaults['num_items'];
		$hide_mods      = isset( $instance['hide_mods'] ) ? absint( $instance['hide_mods'] ) : $this->defaults['hide_mods'];
		$excerpt_length = isset( $instance['excerpt_length'] ) ? absint( $instance['excerpt_length'] ) : $this->defaults['excerpt_length'];
		$hide_avatars   = isset( $instance['hide_avatars'] ) ? absint( $instance['hide_avatars'] ) : $this->defaults['hide_avatars'];
		$avatar_size    = isset( $instance['avatar_size'] ) ? absint( $instance['avatar_size'] ) : $this->defaults['avatar_size'];
		$shortname      = isset( $instance['shortname'] ) ? $instance['shortname'] : $this->defaults['shortname'];

		do_action( 'hocwp_theme_widget_form_before', $instance, $this );

		$args = array();

		HT_HTML_Field()->widget_field( $this, 'shortname', __( 'Disqus Shortname:', 'hocwp-theme' ), $shortname );

		$args['type']  = 'number';
		$args['class'] = 'medium-text';

		HT_HTML_Field()->widget_field( $this, 'num_items', _x( 'Number items:', 'comment items', 'hocwp-theme' ), $num_items, 'input', $args );
		HT_HTML_Field()->widget_field( $this, 'excerpt_length', __( 'Excerpt length:', 'hocwp-theme' ), $excerpt_length, 'input', $args );
		HT_HTML_Field()->widget_field( $this, 'avatar_size', __( 'Avatar size:', 'hocwp-theme' ), $avatar_size, 'input', $args );

		$args = array(
			'type'        => 'checkbox',
			'right_label' => true
		);

		HT_HTML_Field()->widget_field( $this, 'hide_mods', __( 'Hide moderator\'s comments?', 'hocwp-theme' ), $hide_mods, 'input', $args );
		HT_HTML_Field()->widget_field( $this, 'hide_avatars', __( 'Hide commenter avatar?', 'hocwp-theme' ), $hide_avatars, 'input', $args );

		do_action( 'hocwp_theme_widget_form_after', $instance, $this );
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']          = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['num_items']      = isset( $new_instance['num_items'] ) ? absint( $new_instance['num_items'] ) : $this->defaults['num_items'];
		$instance['excerpt_length'] = isset( $new_instance['excerpt_length'] ) ? absint( $new_instance['excerpt_length'] ) : $this->defaults['excerpt_length'];
		$instance['avatar_size']    = isset( $new_instance['avatar_size'] ) ? absint( $new_instance['avatar_size'] ) : $this->defaults['avatar_size'];
		$instance['shortname']      = isset( $new_instance['shortname'] ) ? esc_html( $new_instance['shortname'] ) : $this->defaults['shortname'];

		$instance['hide_mods']    = isset( $new_instance['hide_mods'] ) ? 1 : 0;
		$instance['hide_avatars'] = isset( $new_instance['hide_avatars'] ) ? 1 : 0;

		return $instance;
	}
}