<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Widget_Tabber extends WP_Widget {
	public $defaults;

	public function __construct() {
		$this->defaults = array(
			'sidebar' => '',
			'class'   => ''
		);

		$this->defaults = apply_filters( 'hocwp_theme_widget_tabber_defaults', $this->defaults, $this );

		$widget_options = array(
			'classname'                   => 'hocwp-theme-widget-tabber hocwp-widget-tabber',
			'description'                 => _x( 'Display widget as tabber.', 'widget description', 'hocwp-theme' ),
			'customize_selective_refresh' => true
		);

		$control_options = array(
			'width' => 400
		);

		parent::__construct( 'hocwp_widget_tabber', 'HocWP Tabber', $widget_options, $control_options );

		if ( ! is_admin() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		}
	}

	public function wp_enqueue_scripts() {
		$src = HOCWP_THEME_CORE_URL . '/css/widget-tabber' . HOCWP_THEME_CSS_SUFFIX;
		wp_enqueue_style( 'hocwp-theme-widget-tabber-style', $src );

		$src = HOCWP_THEME_CORE_URL . '/js/widget-tabber' . HOCWP_THEME_JS_SUFFIX;
		wp_enqueue_script( 'hocwp-theme-widget-tabber', $src, array( 'hocwp-theme' ), false, true );
	}

	public function widget( $args, $instance ) {
		$instance = wp_parse_args( $instance, $this->defaults );

		$instance = apply_filters( 'hocwp_theme_widget_tabber_instance', $instance, $args, $this );

		$sidebar = $instance['sidebar'] ?? '';

		add_filter( 'dynamic_sidebar_params', array( &$this, 'widget_sidebar_params' ) );

		do_action( 'hocwp_theme_widget_before', $args, $instance, $this );

		$sidebar_id = $args['id'] ?? '';

		if ( $sidebar_id != $sidebar ) {
			$widget_id = $args['widget_id'] ?? '';
			echo '<div id="' . $widget_id . '" class="hocwp-tabber-widget hocwp-tab-content">';
			?>
            <ul class="nav nav-tabs list-tab hocwp-tabs"></ul>
            <div class="tab-content hocwp-tab-container">
				<?php
				if ( is_active_sidebar( $sidebar ) ) {
					dynamic_sidebar( $sidebar );
				} else {
					$tmp = ht_util()->get_sidebar_by( 'id', $sidebar );

					$sidebar_name = '';

					if ( $tmp ) {
						$sidebar_name = $tmp['name'];
					}
					?>
                    <p><?php printf( __( 'Please drag and drop widget into sidebar %s.', 'hocwp-theme' ), $sidebar_name ); ?></p>
					<?php
				}
				?>
            </div>
			<?php
			echo '</div>';
		} else {
			_e( 'Tabber widget is not properly configured.', 'hocwp-theme' );
		}

		do_action( 'hocwp_theme_widget_after', $args, $instance, $this );

		remove_filter( 'dynamic_sidebar_params', array( &$this, 'widget_sidebar_params' ) );
	}

	public function widget_sidebar_params( $params ) {
		$widget_id = $params[0]['widget_id'] ?? '';

		$params[0]['before_widget'] = '<div id="' . $widget_id . '" class="widget-in-tab tab-pane">';
		$params[0]['after_widget']  = '</div>';
		$params[0]['before_title']  = '<a href="#" class="tab-title">';
		$params[0]['after_title']   = '</a>';

		return $params;
	}

	public function form( $instance ) {
		do_action( 'hocwp_theme_widget_form_before', $instance, $this );

		$instance = wp_parse_args( $instance, $this->defaults );

		$name  = 'sidebar';
		$value = $instance[ $name ] ?? '';

		$args = array(
			'options'     => ht_util()->choose_sidebar_select_options(),
			'description' => __( 'Please do not select Sidebar that contains this widget.', 'hocwp-theme' )
		);

		ht_html_field()->widget_field( $this, $name, __( 'Sidebar:', 'hocwp-theme' ), $value, 'select', $args );

		do_action( 'hocwp_theme_widget_form_after', $instance, $this );
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']   = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['sidebar'] = ht_sanitize()->data( $new_instance['sidebar'], 'string' );

		return $instance;
	}
}