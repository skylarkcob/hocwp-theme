<?php

abstract class HOCWP_Theme_Meta {
	protected $callback;
	protected $callback_args;
	protected $fields;
	protected $styles;
	protected $scripts;

	public function __construct() {
		$this->doing_it_wrong();
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 20 );
	}

	public function set_callback( $callback ) {
		$this->callback = $callback;
	}

	public function set_callback_args( $callback_args ) {
		$this->callback_args = $callback_args;
	}

	public function set_fields( $fields ) {
		if ( is_array( $fields ) ) {
			foreach ( $fields as $field ) {
				$this->add_field( $field );
			}
		} else {
			$this->add_field( $fields );
		}
	}

	public function add_field( $field ) {
		$field = $this->sanitize_field( $field );
		if ( ! is_array( $this->fields ) ) {
			$this->fields = array();
		}
		$this->fields[] = $field;
	}

	public function sanitize_field( $field ) {
		$defaults = array(
			'callback'      => array( 'HOCWP_Theme_HTML_Field', 'input' ),
			'type'          => 'string',
			'callback_args' => array(
				'class' => 'widefat'
			)
		);
		$field    = wp_parse_args( $field, $defaults );
		$type     = $field['type'];
		switch ( $type ) {
			case 'positive_integer':
				$field['callback_args']['min'] = 1;
				break;
		}
		$id   = isset( $field['id'] ) ? $field['id'] : '';
		$name = isset( $field['name'] ) ? $field['name'] : '';
		HOCWP_Theme::transmit( $id, $name );
		$title = isset( $field['title'] ) ? $field['title'] : '';
		$label = isset( $field['label'] ) ? $field['label'] : '';
		HOCWP_Theme::transmit( $title, $label );
		$field['callback_args']['id']    = $id;
		$field['callback_args']['name']  = $name;
		$field['callback_args']['label'] = $label;

		return $field;
	}

	protected function sanitize_data( $field ) {
		$id = $field['id'];
		if ( false !== strpos( $id, '[' ) && false !== strpos( $id, '[' ) ) {
			$tmp = explode( '[', $id );
			$id  = array_shift( $tmp );
		}
		$value = isset( $_POST[ $id ] ) ? $_POST[ $id ] : '';
		$type  = $field['type'];
		switch ( $type ) {
			case 'string':
				$value = maybe_serialize( $value );
				$value = strip_tags( $value );
				break;
			case 'bool':
			case 'boolean':
				$value = ( 1 == $value ) ? 1 : 0;
				break;
			case 'positive_integer':
				$value = absint( $value );
				if ( ! HOCWP_Theme::is_positive_number( $value ) ) {
					$value = '';
				}
				break;
			case 'timestamp':
				$value = strtotime( $value );
				break;
		}

		return $value;
	}

	private function add_style_or_script( &$arr, $handle ) {
		if ( ! is_array( $arr ) ) {
			$arr = array();
		}
		if ( ! in_array( $handle, $arr ) ) {
			$arr[] = $handle;
		}
	}

	public function load_style( $handle ) {
		$this->add_style_or_script( $this->styles, $handle );
	}

	public function load_script( $handle ) {
		$this->add_style_or_script( $this->scripts, $handle );
	}

	private function enqueue( $arr, $script = false ) {
		if ( HOCWP_Theme::array_has_value( $arr ) ) {
			$callback = 'wp_enqueue_style';
			if ( $script ) {
				$callback = 'wp_enqueue_script';
			}
			foreach ( $arr as $handle ) {
				call_user_func( $callback, $handle );
			}
		}
	}

	public function admin_scripts() {
		if ( is_array( $this->scripts ) && in_array( 'hocwp-theme-media-upload', $this->scripts ) ) {
			wp_enqueue_media();
			$this->load_style( 'hocwp-theme-media-upload-style' );
		}
		$this->enqueue( $this->styles );
		$this->enqueue( $this->scripts, true );
	}

	public function doing_it_wrong() {
		if ( ! did_action( 'admin_init' ) ) {
			$msg = __( 'You must call this class in callback of <strong>load-{$pagenow}</strong> action.', 'hocwp-theme' );
			_doing_it_wrong( __CLASS__, $msg, '4.8.1' );
		}
	}
}