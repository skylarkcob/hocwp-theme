<?php

class HOCWP_Theme_Admin_Setting_Tab {
	public $name;
	public $label;
	public $icon;

	public $sections = array();
	public $fields = array();

	public $styles = array();
	public $scripts = array();

	public $priority;

	public $submit_button = true;
	public $callback = null;

	public function __construct( $name, $label, $icon = '', $args = array(), $priority = 10 ) {
		if ( empty( $name ) ) {
			_doing_it_wrong( __CLASS__, __( 'The tab name is not valid.', 'hocwp-theme' ), '6.4.4' );
		}

		if ( empty( $icon ) ) {
			$icon = '<span class="dashicons dashicons-admin-page"></span>';
		}

		if ( empty( $label ) ) {
			$label = $name;
		}

		$label = ucwords( $label );
		$label = strip_tags( $label );

		$this->name     = $name;
		$this->label    = $label;
		$this->icon     = $icon;
		$this->priority = $priority;

		add_filter( 'hocwp_theme_settings_page_tabs', array( $this, 'setting_tabs_filter' ), $this->priority );

		if ( $this->name != HT_Admin_Setting_Tabs()->tab_name ) {
			return;
		}

		add_filter( 'hocwp_theme_settings_page_' . $this->name . '_settings_section', array(
			$this,
			'sections_filter'
		) );

		$esc = isset( $args['enqueue_scripts_callback'] ) ? $args['enqueue_scripts_callback'] : '';

		if ( ! is_callable( $esc ) ) {
			$esc = array( $this, 'enqueue_scripts' );
		}

		add_action( 'hocwp_theme_admin_setting_page_' . $this->name . '_scripts', $esc );

		$cff = isset( $args['custom_fields_filter'] ) ? $args['custom_fields_filter'] : '';

		if ( ! empty( $cff ) ) {
			$this->fields = apply_filters( $cff, $this->fields, HT_Options()->get( $this->name ) );
		}

		add_filter( 'hocwp_theme_settings_page_' . $this->name . '_settings_field', array( $this, 'fields_filter' ) );
	}

	public function enqueue_scripts() {
		foreach ( $this->styles as $handle ) {
			wp_enqueue_style( $handle );
		}

		foreach ( $this->scripts as $handle ) {
			wp_enqueue_script( $handle );
		}
	}

	public function setting_tabs_filter( $tabs ) {
		$tabs[ $this->name ] = $this;

		return $tabs;
	}

	public function add_section( $name, $args = array() ) {
		$defaults = array(
			'tab'         => $this->name,
			'id'          => $name,
			'title'       => '',
			'description' => ''
		);

		$args = wp_parse_args( $args, $defaults );

		$this->sections[ $name ] = $args;
	}

	public function sections_filter() {
		$this->sections = apply_filters( 'hocwp_theme_setting_page_' . $this->name . '_sections', $this->sections );

		return $this->sections;
	}

	public function add_field_array( $data ) {
		if ( $data instanceof HOCWP_Theme_Admin_Setting_Field ) {
			$data = $data->generate();
		}

		$defaults = array(
			'tab' => $this->name
		);

		$data = wp_parse_args( $data, $defaults );

		$this->fields[] = $data;
	}

	public function add_field( $id, $title = '', $callback = 'input', $callback_args = array(), $data_type = 'string', $section = 'default' ) {
		if ( $id instanceof HOCWP_Theme_Admin_Setting_Field ) {
			$this->fields[] = $id->generate();
		} else {
			$this->fields[] = hocwp_theme_create_setting_field( $id, $title, $callback, $callback_args, $data_type, $this->name, $section );
		}
	}

	public function add_a_field( $field ) {
		$this->fields[] = $field;
	}

	public function fields_filter() {
		$this->fields = apply_filters( 'hocwp_theme_setting_page_' . $this->name . '_fields', $this->fields, HT_Options()->get( $this->name ) );

		if ( is_array( $this->fields ) ) {
			foreach ( $this->fields as $key => $field ) {
				if ( $field instanceof HOCWP_Theme_Admin_Setting_Field ) {
					$this->fields[ $key ] = $field->generate();
				}
			}
		}

		return $this->fields;
	}

	public function load_style( $handle ) {
		if ( ! in_array( $handle, $this->styles ) ) {
			$this->styles[] = $handle;
		}
	}

	public function load_script( $handle ) {
		if ( ! in_array( $handle, $this->scripts ) ) {
			$this->scripts[] = $handle;
		}
	}
}