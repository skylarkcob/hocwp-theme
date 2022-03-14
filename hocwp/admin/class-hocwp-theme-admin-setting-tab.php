<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

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

	public $queried_object = '';
	public $link;
	public $link_text;

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
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu_action' ), 999 );
	}

	public function admin_bar_menu_action( $admin_bar ) {
		if ( $admin_bar instanceof WP_Admin_Bar ) {
			if ( ! empty( $this->queried_object ) ) {
				$title = '';

				if ( $this->queried_object instanceof WP_Post ) {
					$url  = get_permalink( $this->queried_object );
					$text = $this->queried_object->post_title;

					$obj = get_post_type_object( $this->queried_object->post_type );

					if ( $obj instanceof WP_Post_Type ) {
						$title = sprintf( '%s: %s', $obj->labels->singular_name, $text );
					}

				} elseif ( $this->queried_object instanceof WP_Term ) {
					$url  = get_term_link( $this->queried_object );
					$text = $this->queried_object->name;

					$obj = get_taxonomy( $this->queried_object->taxonomy );

					if ( $obj instanceof WP_Taxonomy ) {
						$title = sprintf( '%s: %s', $obj->labels->singular_name, $text );
					}
				} else {
					$url  = $this->queried_object;
					$text = $this->label;
				}

				if ( empty( $title ) ) {
					$title = sprintf( __( 'View %s front-end', 'hocwp-theme' ), $this->label );
				}

				$args = array(
					'id'    => $this->name,
					'title' => sprintf( __( 'View %s', 'hocwp-theme' ), $text ),
					'href'  => $url,
					'meta'  => array(
						'target' => '_blank',
						'title'  => sprintf( __( 'View %s', 'hocwp-theme' ), $title )
					)
				);

				$admin_bar->add_node( $args );
			} elseif ( ! empty( $this->link ) && $this->link_text ) {
				$args = array(
					'id'    => $this->name,
					'title' => sprintf( __( 'View %s', 'hocwp-theme' ), $this->link_text ),
					'href'  => $this->link,
					'meta'  => array(
						'target' => '_blank',
						'title'  => sprintf( __( 'View %s', 'hocwp-theme' ), $this->link_text )
					)
				);

				$admin_bar->add_node( $args );
			}
		}
	}

	public function add_queried_object( $object ) {
		$this->queried_object = $object;
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