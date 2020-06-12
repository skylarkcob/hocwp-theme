<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

final class HOCWP_Theme_Admin_Setting_Page {
	protected static $instance;

	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private $menu_slug = '';

	public $tabs;
	public $tab;

	public $settings;
	public $settings_section;
	public $settings_field;

	public $hook_suffix;

	public $scripts;

	public function __construct() {
		$this->menu_slug = HOCWP_Theme()->get_prefix();

		if ( self::$instance instanceof self ) {
			return;
		}

		global $hocwp_theme, $plugin_page, $pagenow;

		if ( isset( $hocwp_theme->option ) && $hocwp_theme->option instanceof HOCWP_Theme_Admin_Setting_Page ) {
			return;
		}

		load_template( HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-admin-setting-tabs.php' );

		$this->tabs = HT_Admin_Setting_Tabs();

		add_action( 'admin_menu', array( $this, 'admin_menu_action' ) );

		if ( 'options.php' == $pagenow || $this->menu_slug == $plugin_page ) {
			add_action( 'admin_init', array( $this, 'settings_init' ) );
		}
		if ( $this->menu_slug == $plugin_page ) {
			$this->tab = $this->tabs->tab_name;
			add_action( 'admin_notices', array( $this, 'saved_notices' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts_action' ), 99 );
		}
	}

	public function get_slug() {
		return $this->menu_slug;
	}

	public function load_script( $tab, $script ) {
		if ( ! is_array( $this->scripts ) ) {
			$this->scripts = array();
		}

		if ( ! isset( $this->scripts[ $tab ] ) ) {
			$this->scripts[ $tab ][] = $script;
		} else {
			if ( ! array_search( $script, $this->scripts[ $tab ] ) ) {
				$this->scripts[ $tab ][] = $script;
			}
		}
	}

	private function get_option_group_and_name() {
		$option_group = $this->menu_slug;
		$option_name  = $this->menu_slug;

		if ( ! empty( $this->tabs->tab_name ) ) {
			$option_group .= '_' . $this->tabs->tab_name;
			$option_name .= '[' . $this->tabs->tab_name . ']';
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
		$this->settings_section = apply_filters( 'hocwp_theme_settings_page_' . $this->tabs->tab_name . '_settings_section', $this->settings_section );

		foreach ( (array) $this->settings_section as $section ) {
			$section = $this->sanitize_section( $section );

			if ( $this->tabs->tab_name != $section['tab'] ) {
				continue;
			}

			add_settings_section( $section['id'], $section['title'], $section['callback'], $section['page'] );
		}

		/**
		 * Add Settings Field
		 */
		$this->settings_field = apply_filters( 'hocwp_theme_settings_page_' . $this->tabs->tab_name . '_settings_field', $this->settings_field );

		foreach ( (array) $this->settings_field as $key => $field ) {
			$field = $this->sanitize_field( $field );

			$this->settings_field[ $key ] = $field;

			if ( $this->tabs->tab_name != $field['tab'] ) {
				continue;
			}

			add_settings_field( $field['id'], $field['title'], $field['callback'], $field['page'], $field['section'], $field['args'] );
		}
	}

	private function sanitize_section_or_field( $data ) {
		if ( ! isset( $data['id'] ) && isset( $data['title'] ) ) {
			$data['id'] = sanitize_title( $data['title'] );
		}

		$tab = isset( $data['tab'] ) ? $data['tab'] : '';

		if ( ! empty( $tab ) ) {
			if ( $tab == $this->tabs->tab_name ) {
				$data['args']['tab'] = $tab;
			}
		}

		$data['tab'] = $tab;

		if ( ! isset( $data['page'] ) ) {
			$data['page'] = $this->menu_slug;
		}

		if ( isset( $data['callback'] ) && ! is_callable( $data['callback'] ) ) {
			unset( $data['callback'] );
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

	private function get_field_name( $field ) {
		$name = $field['id'];

		return $name;
	}

	private function sanitize_field( $field ) {
		if ( $field instanceof HOCWP_Theme_Admin_Setting_Field ) {
			$field = $field->generate();
		}

		$field    = $this->sanitize_section_or_field( $field );
		$field_id = isset( $field['args']['callback_args']['id'] ) ? $field['args']['callback_args']['id'] : $field['id'];

		if ( is_array( $field_id ) ) {
			$field_id = isset( $field_id['id'] ) ? $field_id['id'] : '';
		}

		if ( ! empty( $field_id ) ) {
			$field_id = $this->tabs->tab_name . '_' . $field_id;
		}

		$label_for = isset( $field['args']['label_for'] ) ? $field['args']['label_for'] : '';

		if ( true === $label_for ) {
			$label_for = $field_id;

			$field['args']['label_for'] = $label_for;
		}

		$tr_class = isset( $field['args']['class'] ) ? $field['args']['class'] : '';

		if ( is_array( $tr_class ) ) {
			$tr_class = implode( ' ', $tr_class );
		}

		$tr_class .= ' ' . $field['id'];

		$field['args']['class'] = trim( $tr_class );

		if ( ! isset( $field['callback'] ) ) {
			$field['callback'] = array( $this, 'field_callback' );
		}

		if ( ! isset( $field['section'] ) ) {
			$field['section'] = 'default';
		}

		if ( ! isset( $field['args']['type'] ) ) {
			$field['args']['type'] = 'string';
		}

		$field['args']['callback_args']['id'] = ( ! empty( $label_for ) ) ? $label_for : $field_id;

		if ( ! isset( $field['args']['callback'] ) ) {
			$field['args']['callback'] = array( 'HOCWP_Theme_HTML_Field', 'input' );

			if ( ! isset( $field['args']['before'] ) ) {
				$field['args']['before'] = '<fieldset>';
				$field['args']['after']  = '</fieldset>';
			}
		}

		if ( ! isset( $field['args']['callback_args']['name'] ) ) {
			$data = $this->get_option_group_and_name();

			$field['args']['callback_args']['name'] = $data['option_name'] . '[' . $field['id'] . ']';
		}

		if ( ! isset( $field['args']['callback_args']['class'] ) ) {
			$field['args']['callback_args']['class'] = 'regular-text';
		}

		if ( ! isset( $field['args']['callback_args']['value'] ) ) {
			$options = $GLOBALS['hocwp_theme']->options;

			if ( isset( $options[ $this->tabs->tab_name ][ $field['id'] ] ) ) {
				$value = $options[ $this->tabs->tab_name ][ $field['id'] ];
			} else {
				$options = HOCWP_Theme()->object->options;

				if ( isset( $options[ $this->tabs->tab_name ][ $field['id'] ] ) ) {
					$value = $options[ $this->tabs->tab_name ][ $field['id'] ];
				} elseif ( isset( $field['args']['default'] ) ) {
					$value = $field['args']['default'];
				} else {
					$value = '';
				}
			}

			$field['args']['callback_args']['value'] = $value;
		}

		$type = isset( $field['args']['callback_args']['type'] ) ? $field['args']['callback_args']['type'] : '';

		if ( ! empty( $type ) && ( 'radio' == $type || 'checkbox' == $type ) ) {
			if ( isset( $field['args']['callback_args']['options'] ) && HOCWP_Theme::array_has_value( $field['args']['callback_args']['options'] ) ) {
				unset( $field['args']['label_for'] );
			}
		}

		$data_type = isset( $field['args']['type'] ) ? $field['args']['type'] : 'string';

		switch ( $data_type ) {
			case 'positive_number':
			case 'positive_integer':
				$field['args']['callback_args']['min'] = 1;
				break;
			case 'non_negative_integer':
			case 'non_negative_number':
				$field['args']['callback_args']['min'] = 0;
				break;
		}

		return $field;
	}

	/**
	 * Default section callback for displaying description below section title.
	 *
	 * @param $args
	 */
	public function section_callback( $args ) {
		$callback = isset( $args['callback'][0] ) ? $args['callback'][0] : '';

		if ( $callback instanceof HOCWP_Theme_Admin_Setting_Page && isset( $args['id'] ) ) {
			$secs = $callback->settings_section;
			$id   = $args['id'];

			if ( isset( $secs[ $id ]['description'] ) ) {
				echo wpautop( $secs[ $id ]['description'] );
			} else {
				foreach ( $secs as $section ) {
					if ( is_array( $section ) && isset( $section['id'] ) && $id == $section['id'] && isset( $section['description'] ) ) {
						echo wpautop( $section['description'] );
					}
				}
			}
		}
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
		if ( empty( $this->tab ) ) {
			$this->tab = $this->tabs->get_tab_name();
		}

		if ( ! empty( $this->tabs->tab_name ) ) {
			$this->settings_field = apply_filters( 'hocwp_theme_settings_page_' . $this->tabs->tab_name . '_settings_field', $this->settings_field );

			if ( HT()->array_has_value( $this->settings_field ) ) {
				foreach ( $this->settings_field as $field ) {
					$field = $this->sanitize_field( $field );
					$name  = $this->get_field_name( $field );
					$type  = isset( $field['args']['type'] ) ? $field['args']['type'] : '';

					if ( ! empty( $type ) ) {
						$data = isset( $input[ $this->tabs->tab_name ] ) ? $input[ $this->tabs->tab_name ] : array();

						$input[ $this->tabs->tab_name ][ $name ] = HT_Sanitize()->form_post( $name, $type, $data );
					}
				}
			}
		}

		$input = apply_filters( 'hocwp_theme_sanitize_option', $input );
		$input = apply_filters( 'hocwp_theme_sanitize_option_' . $this->tabs->tab_name, $input );

		$options = (array) get_option( $this->menu_slug );

		if ( ! is_array( $input ) ) {
			$input = array();
		}

		$input = wp_parse_args( $input, $options );
		do_action( 'hocwp_theme_settings_saved', $input, $this );

		return $input;
	}

	public function admin_menu_action() {
		$this->hook_suffix = add_theme_page( __( 'Theme Settings', 'hocwp-theme' ), __( 'Settings', 'hocwp-theme' ), 'manage_options', $this->menu_slug, array(
			$this,
			'html'
		) );
	}

	public function html() {
		$theme = wp_get_theme();
		?>
		<div class="wrap hocwp-theme">
			<h1 class="hidden"><?php _e( 'Theme Settings', 'hocwp-theme' ); ?></h1>
			<hr class="wp-header-end" style="clear: both;">
			<div class="settings-box clearfix module">
				<div class="header module-header">
					<div class="inner clearfix">
						<div class="theme-info">
							<h2><?php printf( __( '%s options', 'hocwp-theme' ), HOCWP_THEME_NAME ); ?></h2>

							<p><?php printf( __( 'Version %s', 'hocwp-theme' ), $theme->get( 'Version' ) ); ?></p>
						</div>
						<div class="save-changes">
							<?php
							$this->submit_button(
								array(
									'attributes' => array(
										'form' => 'hocwpOptions',
										'id'   => 'settingSubmitTop'
									)
								)
							);
							?>
						</div>
					</div>
				</div>
				<div class="module-body clearfix">
					<?php $this->tabs->html() ?>
					<div class="settings-content">
						<?php
						do_action( 'hocwp_theme_settings_page_' . $this->tabs->tab_name . '_form_before' );

						$display = apply_filters( 'hocwp_theme_settings_page_' . $this->tabs->tab_name . '_display_form', true );

						if ( $display ) {
							$this->form_table();
						}

						do_action( 'hocwp_theme_settings_page_' . $this->tabs->tab_name . '_form_after' );
						?>
					</div>
				</div>
				<div class="module-footer clearfix">
					<div class="author-info">
						<p><?php printf( __( 'This theme is created by <a target="_blank" href="%s">HocWP Team</a>. If you have any questions please feel free to <a target="_blank" href="%s">contact us</a> for more information.', 'hocwp-theme' ), $theme->get( 'ThemeURI' ), $theme->get( 'AuthorURI' ) ); ?></p>
					</div>
					<div class="core-version">
						<p><?php printf( __( 'Theme core version %s', 'hocwp-theme' ), HOCWP_THEME_CORE_VERSION ); ?></p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	private function submit_button( $args = array() ) {
		$defaults = array(
			'text'       => '',
			'type'       => 'primary',
			'name'       => 'submit',
			'wrap'       => true,
			'attributes' => null
		);

		$args = wp_parse_args( $args, $defaults );

		$args = apply_filters( 'hocwp_theme_settings_page_' . $this->tabs->tab_name . '_submit_button_args', $args );

		submit_button( $args['text'], $args['type'], $args['name'], $args['wrap'], $args['attributes'] );
	}

	private function form_table() {
		?>
		<form id="hocwpOptions" method="post" action="options.php" autocomplete="off">
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
			do_action( 'hocwp_theme_settings_page_' . $this->tabs->tab_name );

			$this->submit_button(
				array(
					'attributes' => array(
						'form' => 'hocwpOptions',
						'id'   => 'settingSubmitBottom'
					)
				)
			);
			?>
		</form>
		<?php
	}

	public function saved_notices() {
		if ( isset( $_REQUEST['settings-updated'] ) && 'true' == $_REQUEST['settings-updated'] ) {
			$args = array(
				'message'         => __( "<strong>Notice:</strong> All settings have been changed. In order to save them please don't forget to click the 'Save Changes' button.", 'hocwp-theme' ),
				'hidden_interval' => 2000
			);

			HOCWP_Theme_Utility::admin_notice( $args );
		}
	}

	public function admin_enqueue_scripts_action() {
		do_action( 'hocwp_theme_admin_setting_page_scripts', $this );
		do_action( 'hocwp_theme_admin_setting_page_' . $this->tabs->tab_name . '_scripts' );

		$fields = $this->settings_field;

		if ( is_array( $fields ) ) {
			foreach ( $fields as $field ) {
				$tab = isset( $field['tab'] ) ? $field['tab'] : '';

				if ( $tab == $this->tabs->tab_name ) {
					$callback = isset( $field['args']['callback'][1] ) ? $field['args']['callback'][1] : '';

					switch ( $callback ) {
						case 'sortable_category':
						case 'sortable':
						case 'sortable_term':
							HT_Enqueue()->sortable();
							break;
						case 'color_picker':
							HT_Enqueue()->color_picker();
							break;
						case 'image_link':
						case 'media_upload':
							HT_Enqueue()->media_upload();
							break;
						case 'images':
							HT_Enqueue()->media_upload();
							HT_Enqueue()->sortable();
							break;
					}
				}
			}
		}
	}
}

if ( ! $GLOBALS['hocwp_theme']->option ) {
	$GLOBALS['hocwp_theme']->option = new HOCWP_Theme_Admin_Setting_Page();
}