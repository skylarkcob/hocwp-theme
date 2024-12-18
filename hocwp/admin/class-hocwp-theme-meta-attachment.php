<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Meta_Attachment extends HOCWP_Theme_Meta_Post {
	protected $media_type = 'all';
	protected $check_call_action = 'widgets_init';
	protected $allow_pagenow = array( 'upload.php', 'post.php', 'admin-ajax.php' );

	public function __construct() {
		global $pagenow;

		if ( empty( $this->allow_pagenow ) || in_array( $pagenow, $this->allow_pagenow ) ) {
			$this->add_post_type( 'attachment' );
			$this->form_table = true;
			parent::__construct();
			$this->run_popup_hook();
		}
	}

	public function add_meta_boxes_action() {
		global $post_type;

		if ( 'attachment' == $post_type ) {
			$file_id = ht_admin()->get_current_post_id();

			$load = ( is_string( $this->media_type ) && 'all' == $this->media_type );

			if ( $load ) {
				parent::add_meta_boxes_action();

				return;
			}

			$load = ( is_string( $this->media_type ) && 'image' == $this->media_type && wp_attachment_is_image( $file_id ) );

			if ( ! $load ) {
				$load = ( is_string( $this->media_type ) && 'audio' == $this->media_type && wp_attachment_is( $this->media_type, $file_id ) );
			}

			if ( ! $load ) {
				$load = ( is_string( $this->media_type ) && 'video' == $this->media_type && wp_attachment_is( $this->media_type, $file_id ) );
			}

			if ( $load ) {
				parent::add_meta_boxes_action();

				return;
			}

			if ( is_array( $this->media_type ) ) {
				foreach ( $this->media_type as $a_type ) {
					if ( wp_attachment_is( $a_type, $file_id ) ) {
						$load = true;
						break;
					}
				}
			}

			if ( ! $load ) {
				return;
			}
		}

		parent::add_meta_boxes_action();
	}

	private function run_popup_hook() {
		global $pagenow;

		if ( 'upload.php' != $pagenow && 'admin-ajax.php' != $pagenow ) {
			return;
		}

		add_filter( 'attachment_fields_to_edit', array( $this, 'attachment_fields_to_edit_filter' ), 10, 2 );
		add_filter( 'attachment_fields_to_save', array( $this, 'attachment_fields_to_save_filter' ) );
	}

	public function attachment_fields_to_save_filter( $post ) {
		foreach ( $this->fields as $field ) {
			$id = $this->get_field_name( $field );

			if ( ! empty( $id ) && isset( $_POST[ $id ] ) ) {
				$value = $_POST[ $id ];
				$value = ht_sanitize()->data( $value, $field['type'] );

				update_post_meta( $post['ID'], $id, $value );
			}
		}

		return $post;
	}

	private function get_field_name( $field ) {
		$name = $field['name'] ?? '';

		if ( empty( $name ) ) {
			$name = $field['id'] ?? '';
		}

		return $name;
	}

	private function sanitize_media_field( &$field, $attachment = '' ) {
		// Check if field is valid first
		if ( isset( $field['label'] ) && ! empty( $field['label'] ) && isset( $field['input'] ) || ! empty( $field['input'] ) ) {
			return;
		}

		$id   = $field['id'] ?? '';
		$name = $field['name'] ?? '';
		ht()->transmit( $id, $name );

		$field['callback_args']['id'] = 'attachments-' . $attachment->ID . '-' . ht_sanitize()->html_id( $id );

		if ( empty( $field['value'] ) ) {
			if ( $attachment instanceof WP_Post ) {
				$name = $this->get_field_name( $field );

				$field['value'] = get_post_meta( $attachment->ID, $name, true );

				$field['callback_args']['value'] = $field['value'];
			}
		}

		if ( empty( $field['helps'] ) ) {
			$field['helps'] = $field['description'] ?? '';
		}

		$html = '';

		$callback_args = $field['callback_args'] ?? '';

		if ( is_array( $callback_args ) ) {
			unset( $callback_args['label'] );
			$field['callback_args'] = $callback_args;
		}

		if ( empty( $field['html'] ) ) {
			if ( isset( $field['callback'] ) && is_callable( $field['callback'] ) ) {
				ob_start();
				call_user_func( $field['callback'], $callback_args );
				$html = ob_get_clean();
			}
		}

		$defaults = array(
			'label'    => '',
			'input'    => '',
			'value'    => '',
			'html'     => '',
			'helps'    => '',
			'required' => ''
		);

		$field = wp_parse_args( $field, $defaults );

		if ( isset( $field['callback_args'] ) ) {
			$field = wp_parse_args( $field['callback_args'], $field );
		}

		if ( empty( $field['label'] ) ) {
			$field['label'] = $field['title'] ?? '';
		}

		if ( empty( $field['html'] ) && empty( $field['input'] ) && empty( $html ) ) {
			$field['input'] = 'text';
		} else {
			$field['html']  = $html;
			$field['input'] = 'html';
		}
	}

	public function attachment_fields_to_edit_filter( $fields, $post ) {
		foreach ( $this->fields as $field ) {
			$this->sanitize_media_field( $field, $post );

			$id = $field['name'] ?? '';

			if ( ! empty( $id ) ) {
				$fields[ $id ] = $field;
			}
		}

		return $fields;
	}

	public function set_media_type( $types ) {
		$this->add_media_type( $types );
	}

	public function add_media_type( $type ) {
		if ( is_array( $type ) ) {
			foreach ( $type as $a_type ) {
				$this->add_media_type( $a_type );
			}

			return;
		}

		if ( ! is_array( $this->media_type ) ) {
			if ( empty( $this->media_type ) || 'all' == $this->media_type ) {
				$this->media_type = array();
			} else {
				$this->media_type = array( $this->media_type );
			}
		}

		if ( ! in_array( $type, $this->media_type ) ) {
			$this->media_type[] = $type;
		}

		$this->media_type = array_unique( $this->media_type );
	}
}