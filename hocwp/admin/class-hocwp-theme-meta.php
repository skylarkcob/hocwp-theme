<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class HOCWP_Theme_Meta {
	protected $callback;
	protected $callback_args;
	protected $fields;
	protected $styles;
	protected $scripts;

	protected $get_value_callback;
	protected $update_value_callback;

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

	public function set_get_value_callback( $callback ) {
		$this->get_value_callback = $callback;
	}

	public function set_update_value_callback( $callback ) {
		$this->update_value_callback = $callback;
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

		$field = wp_parse_args( $field, $defaults );
		$type  = $field['type'];

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
		$id = $this->get_name( $field, true );

		$value = isset( $_POST[ $id ] ) ? $_POST[ $id ] : '';
		$type  = $field['type'];
		$value = HT_Sanitize()->data( $value, $type );

		return $value;
	}

	public function get_name( $field, $base = false ) {
		$id   = $field['id'];
		$name = isset( $field['name'] ) ? $field['name'] : '';

		if ( empty( $name ) ) {
			$name = isset( $field['callback_args']['name'] ) ? $field['callback_args']['name'] : '';
		}

		HT()->transmit( $id, $name );

		if ( $base ) {
			if ( HT()->string_contain( $name, '[' ) && HT()->string_contain( $name, ']' ) ) {
				$tmp  = explode( '[', $name );
				$name = array_shift( $tmp );
			}
		}

		unset( $id, $tmp );

		return $name;
	}

	public function get_field_id( $field ) {
		$id = isset( $field['id'] ) ? $field['id'] : $this->get_name( $field );
		$id = str_replace( '[', '-', $id );
		$id = str_replace( ']', '-', $id );

		return $id;
	}

	protected function sanitize_value( $obj_id, $field ) {
		if ( ! isset( $field['callback_args']['value'] ) ) {
			if ( ! is_callable( $this->get_value_callback ) ) {
				_doing_it_wrong( __FUNCTION__, __( 'Please set get_value_callback.', 'hocwp-theme' ), '6.3.2' );

				return $field;
			}

			if ( $this->is_checkbox_field( $field, true ) ) {
				$options = $field['callback_args']['options'];

				foreach ( (array) $options as $key => $data ) {
					if ( ! is_array( $data ) ) {
						$data = array(
							'label' => $data
						);
					}

					if ( ! isset( $data['value'] ) ) {
						$tmp         = $field;
						$tmp['name'] = $key;
						unset( $tmp['callback'], $tmp['callback_args']['options'] );
						$tmp           = $this->sanitize_value( $obj_id, $tmp );
						$data['value'] = $tmp['callback_args']['value'];
					}

					$options[ $key ] = $data;
				}

				$field['callback_args']['options'] = $options;

				return $field;
			}

			$id = $this->get_name( $field );

			if ( HT()->string_contain( $id, '[' ) && HT()->string_contain( $id, ']' ) ) {
				$tmp = explode( '[', $id );

				foreach ( $tmp as $key => $a ) {
					$tmp[ $key ] = trim( $a, '[]' );
				}

				$id    = array_shift( $tmp );
				$meta  = call_user_func( $this->get_value_callback, $obj_id, $id, true );
				$count = count( $tmp );
				$k     = 0;

				while ( $k < $count && isset( $meta[ $tmp[ $k ] ] ) ) {
					$meta = $meta[ $tmp[ $k ] ];
					$k ++;
				}

				if ( 0 != $k ) {
					$value = $meta;
				} else {
					$value = '';
				}
			} else {
				$value = call_user_func( $this->get_value_callback, $obj_id, $id, true );
			}

			$type = $field['type'];

			if ( 'timestamp' == $type ) {
				$format = isset( $field['callback_args']['data-date-format'] ) ? $field['callback_args']['data-date-format'] : '';

				if ( empty( $format ) ) {
					global $hocwp_theme;
					$format = $hocwp_theme->defaults['date_format'];
				}

				$field['callback_args']['data-date-format'] = HOCWP_Theme::javascript_datetime_format( $format );

				if ( ! empty( $value ) ) {
					$value = date( $format, $value );
				}
			}

			$field['callback_args']['value'] = $value;
		}

		return $field;
	}

	public function is_checkbox_field( $field, $check_multi = false ) {
		$checkbox = false;

		if ( is_array( $field ) ) {
			if ( isset( $field['callback'][1] ) && 'input' == $field['callback'][1] ) {
				$input_type = isset( $field['callback_args']['type'] ) ? $field['callback_args']['type'] : '';

				if ( 'checkbox' == $input_type ) {
					if ( $check_multi ) {
						$options = isset( $field['callback_args']['options'] ) ? $field['callback_args']['options'] : '';

						if ( HT()->array_has_value( $options ) ) {
							$checkbox = true;
						}
					} else {
						$checkbox = true;
					}
				}
			}
		}

		return $checkbox;
	}

	protected function save( $obj_id ) {
		if ( ! is_callable( $this->update_value_callback ) ) {
			_doing_it_wrong( __FUNCTION__, __( 'Please set update_value_callback.', 'hocwp-theme' ), '6.3.2' );

			return;
		}

		foreach ( (array) $this->fields as $field ) {
			if ( isset( $field['callback_args']['disabled'] ) || isset( $field['callback_args']['readonly'] ) ) {
				continue;
			}

			$id = $this->get_name( $field, true );

			if ( $this->is_checkbox_field( $field, true ) ) {
				$options = $field['callback_args']['options'];

				$tmp = $field;

				foreach ( (array) $options as $key => $data ) {
					$name = $key;

					$tmp['name'] = $name;

					$value = $this->sanitize_data( $tmp );
					call_user_func( $this->update_value_callback, $obj_id, $name, $value );
				}
			}

			$value = $this->sanitize_data( $field );
			call_user_func( $this->update_value_callback, $obj_id, $id, $value );
		}
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