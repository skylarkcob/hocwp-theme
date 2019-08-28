<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Widget_Insert_Code extends WP_Widget {
	public $defaults;

	public function __construct() {
		$this->defaults = array(
			'interval' => 5,
			'code'     => ''
		);

		$this->defaults = apply_filters( 'hocwp_theme_widget_insert_code_defaults', $this->defaults, $this );

		$widget_options = array(
			'classname'   => 'hocwp-theme-widget-insert-code hocwp-widget-insert-code',
			'description' => _x( 'Insert code into sidebar by interval time.', 'widget description', 'hocwp-theme' )
		);

		$control_options = array(
			'width' => 400
		);

		parent::__construct( 'hocwp_widget_insert_code', 'HocWP Insert Code', $widget_options, $control_options );

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts_action' ), 99 );
		}
	}

	public function admin_enqueue_scripts_action() {
		global $pagenow;

		if ( 'widgets.php' == $pagenow ) {
			HT_Enqueue()->code_editor( array(
				'codemirror' => array(
					'indentUnit' => 2,
					'tabSize'    => 2
				)
			) );
		}
	}

	public function widget( $args, $instance ) {
		$instance = apply_filters( 'hocwp_theme_widget_insert_code_instance', $instance, $args, $this );

		$interval = $instance['interval'];

		$session_key = 'widget_insert_code_time_' . $this->id;

		$time = current_time( 'timestamp' );

		$session = isset( $_SESSION[ $session_key ] ) ? $_SESSION[ $session_key ] : '';

		if ( ! empty( $session ) ) {
			$session = strtotime( sprintf( '- %d minutes', $interval ), $session );

			if ( $time <= $session ) {
				return;
			}
		}

		$code = $instance['code'];

		if ( empty( $code ) ) {
			return;
		}

		$code = do_shortcode( $code );

		do_action( 'hocwp_theme_widget_before', $args, $instance, $this );
		echo $code;
		do_action( 'hocwp_theme_widget_after', $args, $instance, $this );

		$session = strtotime( sprintf( '+ %d minutes', $interval ), $time );

		$_SESSION[ $session_key ] = $session;
	}

	public function form( $instance ) {
		$interval = isset( $instance['interval'] ) ? absint( $instance['interval'] ) : $this->defaults['interval'];
		$code     = isset( $instance['code'] ) ? $instance['code'] : $this->defaults['code'];

		do_action( 'hocwp_theme_widget_form_before', $instance, $this );

		$args = array();

		$args['type']  = 'number';
		$args['class'] = 'widefat';

		HT_HTML_Field()->widget_field( $this, 'interval', _x( 'Interval time (minutes):', 'interval minutes', 'hocwp-theme' ), $interval, 'input', $args );

		unset( $args['type'] );
		$args['data-code-editor'] = 1;

		$args['container'] = 'div';

		HT_HTML_Field()->widget_field( $this, 'code', __( 'Code:', 'hocwp-theme' ), $code, 'textarea', $args );

		do_action( 'hocwp_theme_widget_form_after', $instance, $this );
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']    = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['interval'] = isset( $new_instance['interval'] ) ? absint( $new_instance['interval'] ) : $this->defaults['interval'];
		$instance['code']     = isset( $new_instance['code'] ) ? $new_instance['code'] : $this->defaults['code'];

		return $instance;
	}
}