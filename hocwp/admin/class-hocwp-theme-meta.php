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

	protected $check_call_action = 'admin_init';

	protected $get_value_callback;
	protected $update_value_callback;

	public $single_value = true;

	public function __construct() {
		if ( ! empty( $this->check_call_action ) && ! did_action( $this->check_call_action ) ) {
			$this->doing_it_wrong();
		}

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
		if ( $field instanceof HOCWP_Theme_Meta_Field ) {
			$field = $field->generate();
		}

		$field = $this->sanitize_field( $field );

		if ( ! is_array( $this->fields ) ) {
			$this->fields = array();
		}

		$callback = $field['callback'];

		// Check to load styles and scripts on meta page
		if ( is_array( $callback ) ) {
			$class = 'HOCWP_Theme_HTML_Field';

			if ( $callback === array( $class, 'media_upload' )
			     || $callback === array( $class, 'image_link' )
			     || $callback === array( $class, 'images' )
			     || $callback === array( $class, 'image_upload' )
			     || $callback === array( $class, 'content_with_image' )
			) {
				$this->load_script( 'hocwp-theme' );
				$this->load_script( 'hocwp-theme-media-upload' );
				$this->load_style( 'hocwp-theme-admin-style' );

				if ( $callback === array( $class, 'images' ) ) {
					$this->load_script( 'sortable-images-box' );
				}
			} elseif ( array( $class, 'google_maps' ) === $callback ) {
				$this->load_script( 'hocwp-theme-google-maps' );
			} elseif ( array( $class, 'datetime_picker' ) === $callback
			           || array( $class, 'datepicker' ) === $callback ) {
				$this->load_script( 'hocwp-theme-datepicker' );
				$this->load_style( 'jquery-ui-style' );
			} elseif ( array( $class, 'color_picker' ) === $callback ) {
				$this->load_script( 'wp-color-picker' );
				$this->load_script( 'hocwp-theme-color-picker' );
			} elseif ( array( $class, 'code_editor' ) === $callback ) {
				ht_enqueue()->code_editor();
			} elseif ( array( $class, 'layout' ) === $callback ) {
				$this->load_script( 'hocwp-theme' );
				$this->load_script( 'hocwp-theme-admin' );
			} elseif ( array( $class, 'sortable' ) === $callback || array( $class, 'sortable_term' ) === $callback ) {
				$this->load_script( 'hocwp-theme' );
				$this->load_style( 'hocwp-theme-admin-style' );
				$this->load_style( 'hocwp-theme-sortable-style' );
				$this->load_script( 'hocwp-theme-sortable' );
			} elseif ( array( $class, 'chosen' ) === $callback
			           || array( $class, 'chosen_term' ) === $callback
			           || array( $class, 'chosen_post' ) === $callback
			) {
				$this->load_style( 'chosen-style' );
				$this->load_script( 'chosen-select' );
			}
		}

		$field = apply_filters( 'hocwp_theme_meta_field', $field, $this );

		$this->fields[] = $field;
	}

	public function sanitize_field( $field ) {
		if ( $field instanceof HOCWP_Theme_Meta_Field ) {
			$field = $field->generate();
		}

		$defaults = array(
			'callback'      => array( 'HOCWP_Theme_HTML_Field', 'input' ),
			'type'          => 'string',
			'callback_args' => array(
				'class' => 'widefat'
			)
		);

		$field = wp_parse_args( $field, $defaults );
		$type  = $field['type'];

		$cb = $field['callback'];

		// Sanitize field fields
		if ( $this->is_fields_field( $cb ) ) {
			$fields = $field['callback_args']['fields'] ?? '';

			if ( ht()->array_has_value( $fields ) ) {
				$id = $field['id'] ?? '';

				foreach ( $fields as $key => $data ) {
					$data = $this->sanitize_field( $data );

					if ( ! empty( $data['args'] ) && ! empty( $data['callback_args'] ) ) {
						$data['callback_args'] = wp_parse_args( $data['args'], $data['callback_args'] );
						unset( $data['args'] );
					}

					if ( empty( $data['name'] ) ) {
						$data['name'] = $key;
					}

					// Add new id for field base on root id
					if ( empty( $data['id'] ) ) {
						$new_id = $id;

						if ( ! empty( $new_id ) ) {
							$new_id .= '_';
						}

						$new_id .= $data['name'];

						$data['id'] = $new_id;
					}

					if ( empty( $data['callback_args']['id'] ) ) {
						$data['callback_args']['id'] = $data['id'];
					}

					if ( empty( $data['callback_args']['name'] ) ) {
						$data['callback_args']['name'] = $data['name'];
					}

					$fields[ $key ] = $data;
				}
			}

			$field['callback_args']['fields'] = $fields;
		}

		switch ( $type ) {
			case 'ID':
			case 'positive_number':
			case 'positive_integer':
				$field['callback_args']['min'] = 1;
				break;
			case 'non_negative_integer':
			case 'non_negative_number':
				$field['callback_args']['min'] = 0;
				break;
		}

		$id   = $field['id'] ?? '';
		$name = $field['name'] ?? '';
		ht()->transmit( $id, $name );
		$title = $field['title'] ?? '';
		$label = $field['label'] ?? '';
		ht()->transmit( $title, $label );
		$field['callback_args']['id']    = $id;
		$field['callback_args']['name']  = $name;
		$field['callback_args']['label'] = $label;

		return $field;
	}

	protected function sanitize_data( $field ) {
		$id = $this->get_name( $field, true );

		$value = $_POST[ $id ] ?? '';
		$type  = $field['type'];

		return ht_sanitize()->data( $value, $type );
	}

	public function get_base_name( $name ) {
		if ( ht()->string_contain( $name, '[' ) && ht()->string_contain( $name, ']' ) ) {
			$tmp  = explode( '[', $name );
			$name = array_shift( $tmp );
		}

		return $name;
	}

	public function get_name( $field, $base = false ) {
		$id   = $field['id'];
		$name = $field['name'] ?? '';

		if ( empty( $name ) ) {
			$name = $field['callback_args']['name'] ?? '';
		}

		ht()->transmit( $id, $name );

		if ( $base ) {
			$name = $this->get_base_name( $name );
		}

		unset( $id, $tmp );

		return $name;
	}

	public function get_field_id( $field ) {
		$id = $field['id'] ?? $this->get_name( $field );
		$id = str_replace( '[', '-', $id );

		return str_replace( ']', '-', $id );
	}

	protected function sanitize_value( $obj_id, $field ) {
		if ( ! isset( $field['callback_args']['value'] ) ) {
			if ( ! is_callable( $this->get_value_callback ) ) {
				ht_util()->doing_it_wrong( __FUNCTION__, __( 'Please set get_value_callback.', 'hocwp-theme' ), '6.3.2' );

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

			if ( ! empty( $field['meta_key'] ) ) {
				$meta_key = $field['meta_key'];
			} else {
				$meta_key = $this->get_name( $field );
			}

			if ( ht()->string_contain( $meta_key, '[' ) && ht()->string_contain( $meta_key, ']' ) ) {
				$tmp = explode( '[', $meta_key );

				foreach ( $tmp as $key => $a ) {
					$tmp[ $key ] = trim( $a, '[]' );
				}

				$meta_key = array_shift( $tmp );
				$meta     = call_user_func( $this->get_value_callback, $obj_id, $meta_key, $this->single_value );
				$count    = count( $tmp );
				$k        = 0;

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
				$value = call_user_func( $this->get_value_callback, $obj_id, $meta_key, $this->single_value );
			}

			$bk_value = $value;

			$type = $field['type'];

			if ( 'timestamp' == $type ) {
				$format = $field['callback_args']['data-date-format'] ?? '';

				if ( empty( $format ) ) {
					$format = hocwp_theme_object()->defaults['date_format'];
				}

				if ( 'F j, Y' == $format ) {
					$format = 'Y-m-d';
				}

				$field['callback_args']['data-date-format'] = ht()->javascript_datetime_format( $format );

				if ( ! empty( $value ) ) {
					$value = date( $format, $value );
				}
			}

			$cb = $field['callback'] ?? '';

			if ( $this->is_lat_long_field( $cb ) ) {
				$type = $field['callback_args']['type'] ?? '';

				if ( 'array' != $type ) {
					$name = $field['id'] ?? '';

					$field['callback_args'][ $name . '_latitude' ]  = call_user_func( $this->get_value_callback, $obj_id, $name . '_latitude', $this->single_value );
					$field['callback_args'][ $name . '_longitude' ] = call_user_func( $this->get_value_callback, $obj_id, $name . '_longitude', $this->single_value );
				}
			} elseif ( $this->is_fields_field( $cb ) ) {
				// Sanitize field fields value
				$fields = $field['callback_args']['fields'] ?? '';

				if ( ht()->array_has_value( $fields ) ) {
					foreach ( $fields as $key => $data ) {
						$data['callback_args']['value'] = call_user_func( $this->get_value_callback, $obj_id, $key, $this->single_value );

						$data['value'] = $data['callback_args']['value'];

						$fields[ $key ] = $data;
					}
				}

				$field['callback_args']['fields'] = $fields;
			}

			$field['callback_args']['value'] = $value;

			$atts = $field['callback_args']['attributes'] ?? '';

			if ( ! is_array( $atts ) && empty( $atts ) ) {
				$atts = array(
					'data-original-value' => $bk_value
				);

				$field['callback_args']['attributes'] = $atts;
			}
		}

		return $field;
	}

	public function is_fields_field( $cb ) {
		return ( array(
			         'HOCWP_Theme_HTML_Field',
			         'inline_fields'
		         ) == $cb
		         || 'inline_fields' == $cb
		         || array(
			            'HOCWP_Theme_HTML_Field',
			            'fields'
		            ) == $cb
		         || 'fields' == $cb );
	}

	public function is_lat_long_field( $cb ) {
		return ( array( 'HOCWP_Theme_HTML_Field', 'latitude_longitude' ) == $cb || 'latitude_longitude' == $cb );
	}

	public function is_checkbox_field( $field, $check_multi = false ) {
		$checkbox = false;

		if ( is_array( $field ) ) {
			if ( isset( $field['callback'][1] ) && 'input' == $field['callback'][1] ) {
				$input_type = $field['callback_args']['type'] ?? '';

				if ( 'checkbox' == $input_type ) {
					if ( $check_multi ) {
						$options = $field['callback_args']['options'] ?? '';

						if ( ht()->array_has_value( $options ) ) {
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
			ht_util()->doing_it_wrong( __FUNCTION__, __( 'Please set update_value_callback.', 'hocwp-theme' ), '6.3.2' );

			return;
		}

		global $pagenow;

		if ( 'link.php' == $pagenow ) {
			$obj = get_post( $obj_id );

			if ( $obj instanceof WP_Post && 'inherit' == $obj->post_status && 'revision' == $obj->post_type ) {
				wp_delete_post( $obj_id, true );
			}
		}

		foreach ( (array) $this->fields as $field ) {
			if ( isset( $field['callback_args']['disabled'] ) || isset( $field['callback_args']['readonly'] ) ) {
				continue;
			}

			$cb = $field['callback'] ?? '';

			$id = $this->get_name( $field, true );

			if ( empty( $id ) ) {
				continue;
			}

			if ( $this->is_lat_long_field( $cb ) ) {
				$type = $field['callback_args']['type'] ?? '';

				if ( 'array' != $type ) {
					$key   = $id . '_latitude';
					$value = $_POST[ $key ] ?? '';
					call_user_func( $this->update_value_callback, $obj_id, $key, $value );
					$key   = $id . '_longitude';
					$value = $_POST[ $key ] ?? '';
					call_user_func( $this->update_value_callback, $obj_id, $key, $value );
					continue;
				}
			} elseif ( $this->is_fields_field( $cb ) ) {
				$fields = $field['callback_args']['fields'] ?? '';

				if ( ht()->array_has_value( $fields ) ) {
					foreach ( $fields as $key => $data ) {
						$value = $this->sanitize_data( $data );
						call_user_func( $this->update_value_callback, $obj_id, $key, $value );
					}
				}

				// Skip update root key meta
				continue;
			}

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
		if ( ht()->array_has_value( $arr ) ) {
			$callback = 'wp_enqueue_style';

			if ( $script ) {
				$callback = 'wp_enqueue_script';
			}

			foreach ( $arr as $handle ) {
				if ( 'jquery-ui-style' == $handle ) {
					ht_enqueue()->jquery_ui_style();
				} else {
					call_user_func( $callback, $handle );
				}
			}
		}
	}

	public function admin_scripts() {
		if ( is_array( $this->scripts ) ) {
			if ( in_array( 'hocwp-theme-media-upload', $this->scripts ) || in_array( 'media-upload', $this->scripts ) ) {
				ht_enqueue()->media_upload();
			}

			if ( in_array( 'sortable-images-box', $this->scripts ) ) {
				wp_enqueue_editor();
				ht_enqueue()->sortable();
			}

			if ( in_array( 'sortable', $this->scripts ) ) {
				ht_enqueue()->sortable();
			}
		}

		$this->enqueue( $this->styles );
		$this->enqueue( $this->scripts, true );
	}

	public function doing_it_wrong() {
		$msg = __( 'You must call this class in callback of <strong>load-{$pagenow}</strong> action.', 'hocwp-theme' );
		ht_util()->doing_it_wrong( __CLASS__, $msg, '4.8.1' );
	}
}