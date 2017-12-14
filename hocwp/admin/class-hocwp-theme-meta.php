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
		$callback = $field['callback'];
		if ( is_array( $callback ) ) {
			if ( $callback === array( 'HOCWP_Theme_HTML_Field', 'media_upload' ) ) {
				$this->load_script( 'hocwp-theme-media-upload' );
			} elseif ( $callback === array( 'HOCWP_Theme_HTML_Field', 'google_maps' ) ) {
				$this->load_script( 'hocwp-theme-google-maps' );
			}
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
			case 'positive_number':
			case 'positive_integer':
				$field['callback_args']['min'] = 1;
				break;
			case 'non_negative_integer':
			case 'non_negative_number':
				$field['callback_args']['min'] = 0;
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
		$id = $this->get_name( $field );
		if ( false !== strpos( $id, '[' ) && false !== strpos( $id, '[' ) ) {
			$tmp = explode( '[', $id );
			$id  = array_shift( $tmp );
		}
		$value = isset( $_POST[ $id ] ) ? $_POST[ $id ] : '';
		$type  = $field['type'];
		$value = HOCWP_Theme_Sanitize::data( $value, $type );

		return $value;
	}

	public function get_name( $field ) {
		$id   = $field['id'];
		$name = isset( $field['name'] ) ? $field['name'] : '';
		if ( empty( $name ) ) {
			$name = isset( $field['callback_args']['name'] ) ? $field['callback_args']['name'] : '';
		}
		HT()->transmit( $id, $name );

		return $name;
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
			HT_Util()->enqueue_media();
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