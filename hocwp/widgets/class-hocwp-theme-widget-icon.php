<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Widget_Icon extends WP_Widget {
	public $defaults;

	public function __construct() {
		$this->defaults = array(
			'icon_image' => '',
			'icon_url'   => '',
			'icon_html'  => '',
			'text'       => '',
			'sortable'   => '',
			'sortables'  => array(
				'icon'  => '<li class="ui-state-default ui-sortable-handle" data-value="icon">' . __( 'Icon', 'hocwp-theme' ) . '</li>',
				'title' => '<li class="ui-state-default ui-sortable-handle" data-value="title">' . __( 'Title', 'hocwp-theme' ) . '</li>',
				'text'  => '<li class="ui-state-default ui-sortable-handle" data-value="text">' . __( 'Text', 'hocwp-theme' ) . '</li>'
			)
		);
		$this->defaults = apply_filters( 'hocwp_theme_widget_icon_defaults', $this->defaults, $this );

		$widget_options = array(
			'classname'   => 'hocwp-theme-widget-icon hocwp-widget-icon',
			'description' => __( 'Show icon with text.', 'hocwp-theme' )
		);

		$control_options = array(
			'width' => 400
		);

		parent::__construct( 'hocwp_widget_icon', 'HocWP Icon', $widget_options, $control_options );
	}

	public function widget( $args, $instance ) {
		$instance = wp_parse_args( $instance, $this->defaults );

		$instance = apply_filters( 'hocwp_theme_widget_icon_instance', $instance, $args, $this );

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$instance['show_title'] = false;

		do_action( 'hocwp_theme_widget_before', $args, $instance, $this );
		$html = apply_filters( 'hocwp_theme_widget_icon_html', '', $instance, $args, $this );

		if ( ! empty( $html ) ) {
			echo $html;
		} else {
			$name     = 'icon_url';
			$icon_url = isset( $instance[ $name ] ) ? $instance[ $name ] : '';

			if ( is_email( $icon_url ) ) {
				$icon_url = sprintf( 'mailto:%s?subject=%s', $icon_url, $title );
			}

			if ( ! empty( $title ) && ! empty( $icon_url ) ) {
				$title = sprintf( '<a href="%s">%s</a>', esc_attr( $icon_url ), $title );
			}

			$name = 'icon_image';

			$icon_image = isset( $instance[ $name ] ) ? $instance[ $name ] : '';

			$name      = 'icon_html';
			$icon_html = isset( $instance[ $name ] ) ? $instance[ $name ] : '';

			if ( empty( $icon_html ) && HT()->is_positive_number( $icon_image ) ) {
				$icon_html = sprintf( '<img class="icon" src="%s" alt="%s">', wp_get_attachment_url( $icon_image ), $instance['title'] );
			}

			if ( ! empty( $icon_html ) && ! empty( $icon_url ) ) {
				$icon_html = sprintf( '<a href="%s">%s</a>', esc_attr( $icon_url ), $icon_html );
			}

			$name = 'text';
			$text = isset( $instance[ $name ] ) ? $instance[ $name ] : '';
			$text = wpautop( $text );

			$name     = 'sortable';
			$sortable = isset( $instance[ $name ] ) ? $instance[ $name ] : '';

			if ( empty( $sortable ) ) {
				$sortable = array_keys( $instance['sortables'] );
				$sortable = json_encode( $sortable );
			}

			$sortables = json_decode( $sortable, true );

			$before_title = isset( $args['before_title'] ) ? $args['before_title'] : '<h3 class="widget-title">';
			$after_title  = isset( $args['after_title'] ) ? $args['after_title'] : '</h3>';

			echo '<div class="icon-box">';

			foreach ( $sortables as $sortable ) {
				if ( 'icon' == $sortable ) {
					echo $icon_html;
				} elseif ( 'title' == $sortable && ! empty( $title ) ) {
					echo $before_title . $title . $after_title;
				} elseif ( 'text' == $sortable ) {
					echo $text;
				}
			}

			echo '</div>';
		}

		do_action( 'hocwp_theme_widget_after', $args, $instance, $this );
	}

	public function form( $instance ) {
		do_action( 'hocwp_theme_widget_form_before', $instance, $this );

		$instance = wp_parse_args( $instance, $this->defaults );

		$name  = 'icon_url';
		$value = isset( $instance[ $name ] ) ? $instance[ $name ] : '';
		HT_HTML_Field()->widget_field( $this, $name, __( 'Icon URL:', 'hocwp-theme' ), $value, 'input', array( 'type' => 'url' ) );

		$name  = 'icon_image';
		$value = isset( $instance[ $name ] ) ? $instance[ $name ] : '';
		HT_HTML_Field()->widget_field( $this, $name, __( 'Icon Image:', 'hocwp-theme' ), $value, 'media_upload', array( 'container' => 'div' ) );

		$name  = 'icon_html';
		$value = isset( $instance[ $name ] ) ? $instance[ $name ] : '';
		HT_HTML_Field()->widget_field( $this, $name, __( 'Icon HTML:', 'hocwp-theme' ), $value );

		$name  = 'text';
		$value = isset( $instance[ $name ] ) ? $instance[ $name ] : '';
		HT_HTML_Field()->widget_field( $this, $name, __( 'Text:', 'hocwp-theme' ), $value, 'textarea', array( 'rows' => 3 ) );

		$options = $instance['sortables'];

		$name  = 'sortable';
		$value = isset( $instance[ $name ] ) ? $instance[ $name ] : '';

		$args = array( 'options' => $options, 'list_type' => 'custom', 'connects' => false );

		HT_HTML_Field()->widget_field( $this, $name, __( 'Sortable:', 'hocwp-theme' ), $value, 'sortable', $args );

		do_action( 'hocwp_theme_widget_form_after', $instance, $this );
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']      = sanitize_text_field( $new_instance['title'] );
		$instance['icon_image'] = $new_instance['icon_image'];
		$instance['icon_url']   = esc_url( $new_instance['icon_url'] );
		$instance['icon_html']  = $new_instance['icon_html'];
		$instance['text']       = $new_instance['text'];
		$instance['sortable']   = $new_instance['sortable'];

		return $instance;
	}
}