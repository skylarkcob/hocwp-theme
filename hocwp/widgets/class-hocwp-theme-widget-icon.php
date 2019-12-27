<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Widget_Icon extends WP_Widget {
	public $defaults;

	public function __construct() {
		$this->defaults = array(
			'icon_image'       => '',
			'icon_url'         => '',
			'icon_html'        => '',
			'hover_icon_image' => '',
			'hover_icon_html'  => '',
			'text'             => '',
			'sortable'         => '',
			'background_image' => '',
			'html_class'       => '',
			'sortables'        => array(
				'icon'  => '<li class="ui-state-default ui-sortable-handle" data-value="icon">' . __( 'Icon', 'hocwp-theme' ) . '</li>',
				'title' => '<li class="ui-state-default ui-sortable-handle" data-value="title">' . __( 'Title', 'hocwp-theme' ) . '</li>',
				'text'  => '<li class="ui-state-default ui-sortable-handle" data-value="text">' . __( 'Text', 'hocwp-theme' ) . '</li>'
			)
		);

		$this->defaults = apply_filters( 'hocwp_theme_widget_icon_defaults', $this->defaults, $this );

		$widget_options = array(
			'classname'   => 'hocwp-theme-widget-icon hocwp-widget-icon',
			'description' => _x( 'Show icon with text.', 'widget description', 'hocwp-theme' )
		);

		$control_options = array(
			'width' => 400
		);

		parent::__construct( 'hocwp_widget_icon', 'HocWP Icon', $widget_options, $control_options );

		if ( ! is_admin() ) {
			add_filter( 'hocwp_theme_widget_before_html', array( $this, 'before_widget_filter' ), 10, 4 );
		}
	}

	/*
	 * Add widget background.
	 */
	public function before_widget_filter( $before_widget, $args, $instance, $widget ) {
		if ( $widget instanceof HOCWP_Theme_Widget_Icon ) {
			$background = isset( $instance['background_image'] ) ? $instance['background_image'] : '';

			if ( HT()->is_positive_number( $background ) && HT_Media()->exists( $background ) ) {
				$style = 'background-image: url("' . wp_get_attachment_image_url( $background, 'full' ) . '");';
				$style = esc_attr( $style );

				if ( false === strpos( $before_widget, 'style="' ) ) {
					$before_widget = preg_replace( '/class="/', 'style="' . $style . '" class="', $before_widget, 1 );
				} else {
					$before_widget = preg_replace( '/style="/', 'style="' . $style, $before_widget, 1 );
				}
			}

			$html_class = isset( $instance['html_class'] ) ? $instance['html_class'] : '';

			if ( ! empty( $html_class ) ) {
				$html_class = trim( $html_class );
				$html_class .= ' ';
				$before_widget = preg_replace( '/class="/', 'class="' . $html_class, $before_widget, 1 );
			}
		}

		return $before_widget;
	}

	/*
	 * Display widget on frontend.
	 */
	public function widget( $args, $instance ) {
		$instance = wp_parse_args( $instance, $this->defaults );

		$instance = apply_filters( 'hocwp_theme_widget_icon_instance', $instance, $args, $this );

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$title = ltrim( $title, '!' );

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

			$plain_title = esc_attr( wp_strip_all_tags( $title, true ) );

			if ( ! empty( $title ) && ! empty( $icon_url ) ) {
				$title = sprintf( '<a href="%s" title="%s">%s</a>', esc_attr( $icon_url ), $plain_title, $title );
			} else {
				$title = str_replace( '<span>', '<span title="' . $plain_title . '">', $title );
			}

			$name       = 'icon_image';
			$icon_image = isset( $instance[ $name ] ) ? $instance[ $name ] : '';

			$name      = 'icon_html';
			$icon_html = isset( $instance[ $name ] ) ? $instance[ $name ] : '';
			$icon      = $icon_html;

			if ( empty( $icon_html ) && HT()->is_positive_number( $icon_image ) ) {
				$icon      = wp_get_attachment_url( $icon_image );
				$icon_html = sprintf( '<img class="icon" src="%s" alt="%s">', $icon, ltrim( $instance['title'], '!' ) );
			}

			if ( ! empty( $icon_html ) && ! empty( $icon_url ) ) {
				$icon_html = sprintf( '<a href="%s">%s</a>', esc_attr( $icon_url ), $icon_html );
			}

			if ( ! empty( $icon_html ) ) {
				$icon_html = '<div class="icon-wrapper">' . $icon_html . '</div>';
			}

			$name = 'hover_icon_image';

			$icon_image = isset( $instance[ $name ] ) ? $instance[ $name ] : '';

			$name       = 'hover_icon_html';
			$hover_icon = isset( $instance[ $name ] ) ? $instance[ $name ] : '';

			if ( empty( $hover_icon ) && HT()->is_positive_number( $icon_image ) ) {
				$hover_icon = wp_get_attachment_url( $icon_image );
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

			echo '<div class="icon-box" data-icon="' . esc_attr( $icon ) . '" data-hover-icon=' . esc_attr( $hover_icon ) . '>';

			foreach ( $sortables as $sortable ) {
				if ( 'icon' == $sortable ) {
					echo $icon_html;
				} elseif ( 'title' == $sortable && ! empty( $title ) ) {
					// Remove all tags just keep title tag.
					$widget_title = $before_title . $title . $after_title;
					$widget_title = strip_tags( $widget_title, '<h3><span><a>' );
					echo $widget_title;
				} elseif ( 'text' == $sortable ) {
					echo $text;
				}
			}

			echo '</div>';
		}

		do_action( 'hocwp_theme_widget_after', $args, $instance, $this );
	}

	/*
	 * Display widget on backend.
	 */
	public function form( $instance ) {
		do_action( 'hocwp_theme_widget_form_before', $instance, $this );

		$instance = wp_parse_args( $instance, $this->defaults );
		?>
		<nav class="nav-tab-wrapper wp-clearfix">
			<a href="#widgetIconGeneral<?php echo $this->number; ?>"
			   class="nav-tab nav-tab-active"><?php _e( 'General', 'hocwp-theme' ); ?></a>
			<a href="#widgetIconAdvanced<?php echo $this->number; ?>"
			   class="nav-tab"><?php _e( 'Advanced', 'hocwp-theme' ); ?></a>
			<a href="#widgetIconSortable<?php echo $this->number; ?>"
			   class="nav-tab"><?php _e( 'Sortable', 'hocwp-theme' ); ?></a>
		</nav>
		<div class="tab-content">
			<div id="widgetIconGeneral<?php echo $this->number; ?>" class="tab-pane active">
				<?php
				$name  = 'icon_url';
				$value = isset( $instance[ $name ] ) ? $instance[ $name ] : '';
				HT_HTML_Field()->widget_field( $this, $name, __( 'Icon URL:', 'hocwp-theme' ), $value, 'input', array( 'type' => 'url' ) );

				$name  = 'icon_image';
				$value = isset( $instance[ $name ] ) ? $instance[ $name ] : '';
				HT_HTML_Field()->widget_field( $this, $name, __( 'Icon Image:', 'hocwp-theme' ), $value, 'media_upload', array( 'container' => 'div' ) );

				$name  = 'text';
				$value = isset( $instance[ $name ] ) ? $instance[ $name ] : '';
				HT_HTML_Field()->widget_field( $this, $name, __( 'Text:', 'hocwp-theme' ), $value, 'textarea', array( 'rows' => 3 ) );
				?>
			</div>
			<div id="widgetIconAdvanced<?php echo $this->number; ?>" class="tab-pane">
				<?php
				$name  = 'icon_html';
				$value = isset( $instance[ $name ] ) ? $instance[ $name ] : '';
				HT_HTML_Field()->widget_field( $this, $name, __( 'Icon HTML:', 'hocwp-theme' ), $value );

				$name  = 'hover_icon_image';
				$value = isset( $instance[ $name ] ) ? $instance[ $name ] : '';
				HT_HTML_Field()->widget_field( $this, $name, __( 'Hover Icon Image:', 'hocwp-theme' ), $value, 'media_upload', array( 'container' => 'div' ) );

				$name  = 'hover_icon_html';
				$value = isset( $instance[ $name ] ) ? $instance[ $name ] : '';
				HT_HTML_Field()->widget_field( $this, $name, __( 'Hover Icon HTML:', 'hocwp-theme' ), $value );

				$name  = 'background_image';
				$value = isset( $instance[ $name ] ) ? $instance[ $name ] : '';
				HT_HTML_Field()->widget_field( $this, $name, __( 'Background Image:', 'hocwp-theme' ), $value, 'media_upload', array( 'container' => 'div' ) );

				$name  = 'html_class';
				$value = isset( $instance[ $name ] ) ? $instance[ $name ] : '';
				HT_HTML_Field()->widget_field( $this, $name, __( 'HTML Class Attribute:', 'hocwp-theme' ), $value );
				?>
			</div>
			<div id="widgetIconSortable<?php echo $this->number; ?>" class="tab-pane">
				<?php
				$options = $instance['sortables'];

				$name  = 'sortable';
				$value = isset( $instance[ $name ] ) ? $instance[ $name ] : '';

				$args = array( 'options' => $options, 'list_type' => 'custom', 'connects' => false );

				HT_HTML_Field()->widget_field( $this, $name, __( 'Sortable:', 'hocwp-theme' ), $value, 'sortable', $args );
				?>
			</div>
		</div>
		<?php
		do_action( 'hocwp_theme_widget_form_after', $instance, $this );
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']            = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['icon_image']       = $new_instance['icon_image'];
		$instance['icon_url']         = esc_url( $new_instance['icon_url'] );
		$instance['icon_html']        = $new_instance['icon_html'];
		$instance['background_image'] = $new_instance['background_image'];
		$instance['text']             = $new_instance['text'];
		$instance['sortable']         = $new_instance['sortable'];
		$instance['hover_icon_image'] = $new_instance['hover_icon_image'];
		$instance['hover_icon_html']  = $new_instance['hover_icon_html'];
		$instance['html_class']       = sanitize_text_field( remove_accents( $new_instance['html_class'] ) );

		return $instance;
	}
}