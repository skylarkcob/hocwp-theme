<?php

class HOCWP_Theme_Widget_Tabber extends WP_Widget {
	public $defaults;

	public function __construct() {
		$this->defaults = array(
			'sidebar' => '',
			'class'   => ''
		);

		$this->defaults = apply_filters( 'hocwp_theme_widget_tabber_defaults', $this->defaults, $this );

		$widget_options = array(
			'classname'   => 'hocwp-theme-widget-tabber hocwp-widget-tabber',
			'description' => __( 'Display widget as tabber.', 'hocwp-theme' )
		);

		$control_options = array(
			'width' => 400
		);

		parent::__construct( 'hocwp_widget_tabber', 'HocWP Tabber', $widget_options, $control_options );
	}

	public function widget( $args, $instance ) {
		$instance = wp_parse_args( $instance, $this->defaults );

		$instance = apply_filters( 'hocwp_theme_widget_tabber_instance', $instance, $args, $this );
	}

	public function form( $instance ) {
		do_action( 'hocwp_theme_widget_form_before', $instance, $this );

		$instance = wp_parse_args( $instance, $this->defaults );

		global $wp_registered_sidebars;

		$options = array();

		foreach ( $wp_registered_sidebars as $sidebar ) {
			HT()->debug( $sidebar );
		}

		do_action( 'hocwp_theme_widget_form_after', $instance, $this );
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = sanitize_text_field( $new_instance['title'] );

		return $instance;
	}
}