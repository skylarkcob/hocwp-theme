<?php

final class HOCWP_Theme_Admin_Setting_Page {
	private $menu_slug = 'hocwp_theme';

	public $tabs;
	public $tab;

	public $settings;
	public $settings_section;
	public $settings_field;

	public $hook_suffix;

	public function __construct() {
		global $hocwp_theme;
		if ( isset( $hocwp_theme->option ) && $hocwp_theme->option instanceof HOCWP_Theme_Admin_Setting_Page ) {
			return;
		}
		$this->tab = isset( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'general';
		add_action( 'admin_menu', array( $this, 'admin_menu_action' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );
		add_action( 'admin_notices', array( $this, 'saved_notices' ) );
	}

	public function get_slug() {
		return $this->menu_slug;
	}

	private function get_option_group_and_name() {
		$option_group = $this->menu_slug;
		$option_name  = $this->menu_slug;
		if ( ! empty( $this->tab ) ) {
			$option_group .= '_' . $this->tab;
			$option_name .= '[' . $this->tab . ']';
		}

		return array( 'option_group' => $option_group, 'option_name' => $option_name );
	}

	public function settings_init() {
		/**
		 * Register Setting
		 */
		$data = $this->get_option_group_and_name();
		register_setting( $this->menu_slug, $this->menu_slug, array( $this, 'sanitize' ) );
		/**
		 * Add settings section
		 */
		$this->settings_section = apply_filters( 'hocwp_theme_settings_page_' . $this->tab . '_settings_section', $this->settings_section );
		foreach ( (array) $this->settings_section as $section ) {
			$section = $this->sanitize_section( $section );
			if ( $this->tab != $section['tab'] ) {
				continue;
			}
			add_settings_section( $section['id'], $section['title'], $section['callback'], $section['page'] );
		}
		/**
		 * Add Settings Field
		 */
		$this->settings_field = apply_filters( 'hocwp_theme_settings_page_' . $this->tab . '_settings_field', $this->settings_field );
		foreach ( (array) $this->settings_field as $field ) {
			$field = $this->sanitize_field( $field );
			if ( $this->tab != $field['tab'] ) {
				continue;
			}
			add_settings_field( $field['id'], $field['title'], $field['callback'], $field['page'], $field['section'], $field['args'] );
		}
	}

	private function sanitize_section_or_field( $data ) {
		$tab = isset( $data['tab'] ) ? $data['tab'] : '';
		if ( ! empty( $tab ) ) {
			if ( $tab == $this->tab ) {
				$data['args']['tab'] = $tab;
			}
		}
		$data['tab'] = $tab;
		if ( ! isset( $data['page'] ) ) {
			$data['page'] = $this->menu_slug;
		}

		return $data;
	}

	private function sanitize_section( $section ) {
		$section = $this->sanitize_section_or_field( $section );
		if ( ! isset( $section['callback'] ) ) {
			$section['callback'] = array( $this, 'section_callback' );
		}

		return $section;
	}

	private function sanitize_field( $field ) {
		$field    = $this->sanitize_section_or_field( $field );
		$field_id = isset( $field['args']['callback_args']['id'] ) ? $field['args']['callback_args']['id'] : $field['id'];
		if ( ! empty( $field_id ) ) {
			$field_id = $this->tab . '_' . $field_id;
		}
		if ( isset( $field['args']['label_for'] ) && true === $field['args']['label_for'] ) {
			$field['args']['label_for'] = $field_id;
		}
		$field['args']['class'] = $field['id'];
		if ( ! isset( $field['callback'] ) ) {
			$field['callback'] = array( $this, 'field_callback' );
		}
		if ( ! isset( $field['section'] ) ) {
			$field['section'] = 'default';
		}
		if ( ! isset( $field['args']['type'] ) ) {
			$field['args']['type'] = 'string';
		}
		$field['args']['callback_args']['id'] = ( isset( $field['args']['label_for'] ) && ! empty( $field['args']['label_for'] ) ) ? $field['args']['label_for'] : $field_id;
		if ( ! isset( $field['args']['callback'] ) ) {
			$field['args']['callback'] = array( 'HOCWP_Theme_HTML_Field', 'input' );
			if ( ! isset( $field['args']['before'] ) ) {
				$field['args']['before'] = '<fieldset>';
				$field['args']['after']  = '</fieldset>';
				$field['args']['before'] .= '<label for="' . $field['args']['callback_args']['id'] . '">';
				$field['args']['after'] = '</label>' . $field['args']['after'];
			}
		}
		if ( ! isset( $field['args']['callback_args']['name'] ) ) {
			$data                                   = $this->get_option_group_and_name();
			$field['args']['callback_args']['name'] = $data['option_name'] . '[' . $field['id'] . ']';
		}
		if ( ! isset( $field['args']['callback_args']['class'] ) ) {
			$field['args']['callback_args']['class'] = 'regular-text';
		}
		if ( ! isset( $field['args']['callback_args']['value'] ) ) {
			$options = $GLOBALS['hocwp_theme']->options;
			if ( isset( $options[ $this->tab ][ $field['id'] ] ) ) {
				$value = $options[ $this->tab ][ $field['id'] ];
			} else {
				$options = $GLOBALS['hocwp_theme']->defaults['options'];
				$value   = isset( $options[ $this->tab ][ $field['id'] ] ) ? $options[ $this->tab ][ $field['id'] ] : '';
			}
			$field['args']['callback_args']['value'] = $value;
		}
		if ( isset( $field['args']['callback_args']['type'] ) && ( 'radio' == $field['args']['callback_args']['type'] || 'checkbox' == $field['args']['callback_args']['type'] ) ) {
			if ( isset( $field['args']['callback_args']['options'] ) && HOCWP_Theme::array_has_value( $field['args']['callback_args']['options'] ) ) {
				unset( $field['args']['label_for'] );
			}
		}

		return $field;
	}

	public function section_callback() {
	}

	public function field_callback( $args ) {
		if ( ! isset( $args['callback'] ) || ! is_callable( $args['callback'] ) ) {
			$args['callback'] = array( 'HOCWP_Theme_HTML_Field', 'input' );
		}
		$callback_args = isset( $args['callback_args'] ) ? $args['callback_args'] : '';
		if ( isset( $args['before'] ) ) {
			echo $args['before'];
		}
		call_user_func( $args['callback'], $callback_args );
		$desc = isset( $args['description'] ) ? $args['description'] : '';
		if ( ! empty( $desc ) ) {
			$p = new HOCWP_Theme_HTML_Tag( 'p' );
			$p->add_attribute( 'class', 'description' );
			$p->set_text( $desc );
			$p->output();
		}
		if ( isset( $args['after'] ) ) {
			echo $args['after'];
		}
		if ( isset( $args['action'] ) ) {
			do_action( $args['action'] );
		}
	}

	public function sanitize( $input ) {
		$input   = apply_filters( 'hocwp_theme_sanitize_option', $input );
		$input   = apply_filters( 'hocwp_theme_sanitize_option_' . $this->tab, $input );
		$options = get_option( 'hocwp_theme' );
		$input   = wp_parse_args( $input, $options );

		return $input;
	}

	public function admin_menu_action() {
		$this->hook_suffix = add_theme_page( __( 'Theme Settings', 'hocwp-theme' ), __( 'Settings', 'hocwp-theme' ), 'manage_options', $this->menu_slug, array(
			$this,
			'html'
		) );
	}

	public function html() {
		?>
		<div class="wrap">
			<h1 class="hidden">&nbsp;</h1>
			<?php
			$this->tabs = apply_filters( 'hocwp_theme_settings_page_tabs', $this->tabs );
			if ( HOCWP_Theme::array_has_value( $this->tabs ) ) {
				?>
				<div id="nav">
					<h2 class="nav-tab-wrapper">
						<?php
						$current_url = HOCWP_Theme_Utility::get_current_url();
						$count       = 0;
						foreach ( $this->tabs as $key => $tab ) {
							$url   = add_query_arg( array( 'tab' => $key ), $current_url );
							$class = 'nav-tab';
							if ( $this->tab == $key || ( empty( $this->tab ) && 0 == $count ) ) {
								$class .= ' nav-tab-active';
							}
							?>
							<a class="<?php echo $class; ?>"
							   href="<?php echo esc_url( $url ); ?>"><?php echo $tab; ?></a>
							<?php
							$count ++;
						}
						?>
					</h2>
				</div>
				<?php
			}
			do_action( 'hocwp_theme_settings_page_' . $this->tab . '_form_before' );
			$display = apply_filters( 'hocwp_theme_settings_page_' . $this->tab . '_display_form', true );
			if ( $display ) {
				$this->form_table();
			}
			do_action( 'hocwp_theme_settings_page_' . $this->tab . '_form_after' );
			?>
		</div>
		<?php
	}

	private function form_table() {
		?>
		<form method="post" action="options.php">
			<input type="hidden" name="tab"
			       value="<?php echo isset( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'general'; ?>">
			<?php
			$data = $this->get_option_group_and_name();
			settings_fields( $this->menu_slug );
			global $wp_settings_fields;
			if ( isset( $wp_settings_fields[ $this->menu_slug ]['default'] ) ) {
				?>
				<table class="form-table">
					<tbody>
					<?php do_settings_fields( $this->menu_slug, 'default' ); ?>
					</tbody>
				</table>
				<?php
			}
			do_settings_sections( $this->menu_slug );
			do_action( 'hocwp_theme_settings_page_' . $this->tab );
			$defaults = array(
				'text'       => '',
				'type'       => 'primary',
				'name'       => 'submit',
				'wrap'       => true,
				'attributes' => null
			);
			$args     = apply_filters( 'hocwp_theme_settings_page_' . $this->tab . '_submit_button_args', $defaults );
			submit_button( $args['text'], $args['type'], $args['name'], $args['wrap'], $args['attributes'] );
			?>
		</form>
		<?php
	}

	public function saved_notices() {
		if ( isset( $_REQUEST['settings-updated'] ) && 'true' == $_REQUEST['settings-updated'] ) {
			HOCWP_Theme_Utility::admin_notice( __( "<strong>Notice:</strong> All settings have been changed. In order to save them please don't forget to click the 'Save Changes' button.", 'hocwp-theme' ) );
		}
	}
}

$GLOBALS['hocwp_theme']->option = new HOCWP_Theme_Admin_Setting_Page();