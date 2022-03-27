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

		global $hocwp_theme, $pagenow;

		if ( isset( $hocwp_theme->option ) && $hocwp_theme->option instanceof HOCWP_Theme_Admin_Setting_Page ) {
			return;
		}

		load_template( HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-admin-setting-tabs.php' );

		$this->tabs = HT_Admin_Setting_Tabs();

		add_action( 'admin_menu', array( $this, 'admin_menu_action' ) );

		if ( 'options.php' == $pagenow || $this->menu_slug == HT_Admin()->get_plugin_page() ) {
			add_action( 'admin_init', array( $this, 'settings_init' ) );
		}

		if ( $this->menu_slug == HT_Admin()->get_plugin_page() ) {
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
			$option_name  .= '[' . $this->tabs->tab_name . ']';
		}

		return array( 'option_group' => $option_group, 'option_name' => $option_name );
	}

	public function screen_settings_filter( $settings, $screen ) {
		if ( $screen instanceof WP_Screen ) {
			global $hocwp_theme;

			if ( $screen->id == $hocwp_theme->option->hook_suffix ) {
				$value = get_user_setting( 'theme_settings_collapse_expand' );

				ob_start();
				?>
                <fieldset class="metabox-prefs functions">
                    <legend><?php _e( 'Functions', 'hocwp-theme' ); ?></legend>
                    <label for="collapse-expand">
                        <input id="collapse-expand" type="checkbox" name="collapse_expand"
							<?php checked( 'on', $value ); ?> />
						<?php _e( 'Enable collapse and expand setting rows.', 'hocwp-theme' ); ?>
                    </label>
                </fieldset>
				<?php
				$settings .= ob_get_clean();

				$view_modes = array(
					'default' => __( 'Default', 'hocwp-theme' ),
					'classic' => __( 'Classic', 'hocwp-theme' )
				);

				$view_modes = apply_filters( 'hocwp_theme_admin_setting_page_view_modes', $view_modes );

				if ( HT()->array_has_value( $view_modes ) ) {
					$mode = get_user_setting( 'theme_settings_view_mode', 'default' );

					ob_start();
					?>
                    <fieldset class="metabox-prefs view-mode">
                        <legend><?php _e( 'View mode', 'hocwp-theme' ); ?></legend>
						<?php
						foreach ( $view_modes as $view_mode => $label ) {
							?>
                            <label for="<?php echo esc_attr( $view_mode ); ?>-view-mode">
                                <input id="<?php echo esc_attr( $view_mode ); ?>-view-mode" type="radio" name="mode"
                                       value="<?php echo esc_attr( $view_mode ); ?>" <?php checked( $view_mode, $mode ); ?> />
								<?php echo $label; ?>
                            </label>
							<?php
						}
						?>
                    </fieldset>
					<?php
					$settings .= ob_get_clean();
				}
			}
		}

		return $settings;
	}

	/**
	 * Add help tabs and sidebar to theme options screen.
	 */
	public function theme_options_help() {
		$help = '<p>' . sprintf( __( 'Each theme has its own customization settings. If you change themes, options may change or disappear, as they are theme-specific. In addition to the functions required by each theme, the optional functions included may not work with the current theme. Your current theme, <strong>%s</strong>, provides the following Theme Options by default:', 'hocwp-theme' ), HOCWP_THEME_NAME ) . '</p>' .
		        '<ol>' .
		        '<li>' . __( '<strong>Site Identity</strong>: With this setting, you can change the favicon icon and logo that represent the website.', 'hocwp-theme' ) . '</li>' .
		        '<li>' . __( '<strong>Site background</strong>: You can change the background image for the entire page or just use colors.', 'hocwp-theme' ) . '</li>' .
		        '<li>' . __( '<strong>Browser color</strong>: You can change the branding colors for mobile browsers.', 'hocwp-theme' ) . '</li>' .
		        '</ol>' .
		        '<p>' . __( 'Remember to click "<strong>Save Changes</strong>" to save any changes you have made to the theme options.', 'hocwp-theme' ) . '</p>';

		$sidebar = '<p><strong>' . __( 'For more information:', 'hocwp-theme' ) . '</strong></p>' .
		           '<p>' . __( '<a href="https://codewp47.com/huong-dan-su-dung/wordpress-dashboard-toan-tap/" target="_blank">How to use WordPress Dashboard</a>', 'hocwp-theme' ) . '</p>' .
		           '<p>' . __( '<a href="https://codewp47.com/huong-dan-su-dung/cai-dat-giao-dien/" target="_blank">Documentation on Theme Options</a>', 'hocwp-theme' ) . '</p>' .
		           '<p>' . __( '<a href="https://ldcuong.com/lien-he/" target="_blank">Contact Us</a>', 'hocwp-theme' ) . '</p>';


		$helps = apply_filters( 'hocwp_theme_setting_page_helps', array() );

		array_unshift( $helps, array(
			'title'    => __( 'Overview', 'hocwp-theme' ),
			'id'       => 'theme-options-help',
			'content'  => $help,
			'priority' => 1
		) );

		$links = apply_filters( 'hocwp_theme_setting_page_help_sidebar_links', array() );

		array_unshift( $links, array(
			'href' => 'https://ldcuong.com/lien-he/',
			'text' => __( 'Contact Us', 'hocwp-theme' )
		) );

		array_unshift( $links, array(
			'href' => 'https://codewp47.com/huong-dan-su-dung/cai-dat-giao-dien/',
			'text' => __( 'Documentation on Theme Options', 'hocwp-theme' )
		) );

		array_unshift( $links, array(
			'href' => 'https://codewp47.com/huong-dan-su-dung/wordpress-dashboard-toan-tap/',
			'text' => __( 'How to use WordPress Dashboard', 'hocwp-theme' )
		) );

		if ( HT()->array_has_value( $helps ) ) {
			$screen = get_current_screen();

			if ( HT()->array_has_value( $links ) ) {
				$sidebar = '<p><strong>' . __( 'For more information:', 'hocwp-theme' ) . '</strong></p>';

				foreach ( $links as $link ) {
					if ( ! isset( $link['href'] ) || ! isset( $link['text'] ) ) {
						continue;
					}

					$sidebar .= wpautop( sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $link['href'] ), esc_html( $link['text'] ) ) );
				}
			}

			if ( method_exists( $screen, 'add_help_tab' ) ) {
				foreach ( $helps as $tab ) {
					// WordPress 3.3.0.
					$screen->add_help_tab( $tab );
				}

				$screen->set_help_sidebar( $sidebar );
			}
		}
	}

	public function screen_options_and_help_action() {
		add_filter( 'screen_options_show_submit', '__return_true' );

		if ( isset( $_REQUEST['screen-options-apply'] ) || isset( $_GET['mode'] ) ) {
			$mode = $_REQUEST['mode'] ?? '';

			if ( ! empty( $mode ) ) {
				set_user_setting( 'theme_settings_view_mode', $mode );
			}

			$value = $_REQUEST['collapse_expand'] ?? '';

			set_user_setting( 'theme_settings_collapse_expand', $value );
		}

		add_filter( 'screen_settings', array( $this, 'screen_settings_filter' ), 10, 2 );

		$show = apply_filters( 'hocwp_theme_show_theme_setting_helps', true );

		if ( $show ) {
			$this->theme_options_help();
		}
	}

	public function settings_init() {
		global $hocwp_theme;

		// Action when theme setting page loaded
		add_action( "load-{$hocwp_theme->option->hook_suffix}", array( $this, 'screen_options_and_help_action' ) );

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

		$skip_tabs = array(
			'administration_tools',
			'language'
		);

		$skip_tabs = apply_filters( 'hocwp_theme_skip_multiple_language_setting_tabs', $skip_tabs );

		if ( function_exists( 'HOCWP_EXT_Language' ) && ! in_array( $this->tabs->tab_name, $skip_tabs ) ) {
			$multiple_option = HOCWP_EXT_Language()->get_option( 'multiple_option' );

			if ( $multiple_option && function_exists( 'pll_languages_list' ) ) {
				$langs   = pll_languages_list();
				$default = pll_default_language();
				unset( $langs[ array_search( $default, $langs ) ] );

				if ( HT()->array_has_value( $langs ) ) {
					$lists = array();

					foreach ( (array) $this->settings_field as $field ) {
						$lists[] = $field;
						HOCWP_EXT_Language()->generate_setting_with_language( $field, $lists );
					}

					$this->settings_field = $lists;
				}
			}
		}

		if ( isset( $_REQUEST['screen-options-apply'] ) ) {
			$collapse_expand = $_REQUEST['collapse_expand'] ?? '';
		} else {
			$collapse_expand = get_user_setting( 'theme_settings_collapse_expand' );
		}

		foreach ( (array) $this->settings_field as $key => $field ) {
			$field = $this->sanitize_field( $field );

			$this->settings_field[ $key ] = $field;

			if ( $this->tabs->tab_name != $field['tab'] ) {
				continue;
			}

			// Add title label for
			if ( ! isset( $field['args']['label_for'] ) && isset( $field['args']['callback_args']['id'] ) ) {
				$field['args']['label_for'] = $field['args']['callback_args']['id'];
			}

			$title = $field['title'];

			if ( 'on' == $collapse_expand ) {
				$title .= ' <span class="dashicons dashicons-admin-collapse" title="' . esc_attr__( 'Collapse', 'hocwp-theme' ) . '"></span>';
				$title .= ' <span class="dashicons dashicons-editor-expand" title="' . esc_attr__( 'Expand', 'hocwp-theme' ) . '"></span>';
			}

			add_settings_field( $field['id'], $title, $field['callback'], $field['page'], $field['section'], $field['args'] );
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

	private function get_field_value( $name, $default = '' ) {
		$options = $GLOBALS['hocwp_theme']->options;

		$value = '';

		if ( is_array( $name ) && 1 == count( $name ) ) {
			$name = current( $name );
		}

		if ( ! is_array( $name ) ) {
			if ( isset( $options[ $this->tabs->tab_name ][ $name ] ) ) {
				$value = $options[ $this->tabs->tab_name ][ $name ];
			} else {
				$options = HOCWP_Theme()->object->options;

				if ( isset( $options[ $this->tabs->tab_name ][ $name ] ) ) {
					$value = $options[ $this->tabs->tab_name ][ $name ];
				} else {
					$value = $default;
				}
			}
		} else {
			$count = count( $name );

			if ( 2 == $count ) {
				if ( isset( $options[ $this->tabs->tab_name ][ $name[0] ][ $name[1] ] ) ) {
					$value = $options[ $this->tabs->tab_name ][ $name[0] ][ $name[1] ];
				} else {
					$options = HOCWP_Theme()->object->options;

					if ( isset( $options[ $this->tabs->tab_name ][ $name[0] ][ $name[1] ] ) ) {
						$value = $options[ $this->tabs->tab_name ][ $name[0] ][ $name[1] ];
					} else {
						$value = $default;
					}
				}
			} elseif ( 3 == $count ) {
				if ( isset( $options[ $this->tabs->tab_name ][ $name[0] ][ $name[1] ][ $name[2] ] ) ) {
					$value = $options[ $this->tabs->tab_name ][ $name[0] ][ $name[1] ][ $name[2] ];
				} else {
					$options = HOCWP_Theme()->object->options;

					if ( isset( $options[ $this->tabs->tab_name ][ $name[0] ][ $name[1] ][ $name[2] ] ) ) {
						$value = $options[ $this->tabs->tab_name ][ $name[0] ][ $name[1] ][ $name[2] ];
					} else {
						$value = $default;
					}
				}
			}
		}

		return apply_filters( 'hocwp_theme_admin_setting_page_field_value', $value, $name, $this );
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

		$callback = $field['args']['callback'];

		// Check and sanitize all child fields
		$is_fields = ( is_array( $callback ) && isset( $callback['1'] ) && 'fields' == $callback[1] );

		if ( ! $is_fields ) {
			$is_fields = ( is_array( $callback ) && isset( $callback['1'] ) && 'inline_fields' == $callback[1] );
		}

		if ( $is_fields ) {
			// Get list child fields
			$fields = $field['args']['callback_args']['fields'] ?? '';

			if ( HT()->array_has_value( $fields ) ) {
				foreach ( $fields as $base => $a_field ) {
					// Check field args
					$a_args = $a_field['args'] ?? '';

					// Re-check callback args if empty args param.
					if ( empty( $a_args ) && isset( $a_field['callback_args'] ) ) {
						$a_args = $a_field['callback_args'];
					}

					if ( ! is_array( $a_args ) ) {
						$a_args = array();
					}

					// Check callback
					$a_cb = $a_field['callback'] ?? '';

					if ( ! is_callable( $a_cb ) ) {
						if ( empty( $a_cb ) ) {
							$a_cb = 'input';
						}

						$a_cb = array( 'HOCWP_Theme_HTML_Field', $a_cb );
					}

					if ( ! is_callable( $a_cb ) ) {
						unset( $fields[ $base ] );
						continue;
					}

					$a_field['callback'] = $a_cb;

					// Add field value
					$a_args['value'] = $this->get_field_value( array( $field['id'], $base ) );

					// Add field name and id hocwp_theme[tab][parent_base][base]
					$new_name = $field['page'] . '[' . $field['tab'] . '][' . $field['id'] . '][' . $base . ']';

					$a_args['name'] = $new_name;
					$a_args['id']   = HT_Sanitize()->html_id( $new_name );

					if ( empty( $a_args['label'] ) && isset( $a_field['label'] ) && ! empty( $a_field['label'] ) ) {
						$a_args['label'] = $a_field['label'];
					}

					if ( empty( $a_args['title'] ) && isset( $a_field['title'] ) && ! empty( $a_field['title'] ) ) {
						$a_args['title'] = $a_field['title'];
					}

					if ( empty( $a_args['description'] ) && isset( $a_field['description'] ) && ! empty( $a_field['description'] ) ) {
						$a_args['description'] = $a_field['description'];
					}

					if ( empty( $a_args['label'] ) ) {
						// Add field title label
						$a_args['label'] = $a_field['title'] ?? '';

						if ( empty( $a_args['label'] ) ) {
							$a_args['label'] = $a_field['label'] ?? '';
						}
					}

					// Add field class
					$a_args['class'] = $a_args['class'] ?? 'regular-text';

					$a_field['args'] = $a_args;
					$fields[ $base ] = $a_field;
				}

				$field['args']['callback_args']['fields'] = $fields;
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
			if ( isset( $field['args']['callback_args']['options'] ) && HT()->array_has_value( $field['args']['callback_args']['options'] ) ) {
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
		$this->update_list_tabs();

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
						$type  = strtolower( $type );
						$data  = isset( $input[ $this->tabs->tab_name ] ) ? $input[ $this->tabs->tab_name ] : array();
						$value = HT_Sanitize()->form_post( $name, $type, $data );

						// Remove empty json data value
						if ( 'json' == $type || 'array' == $type || 'sortable' == $type ) {
							if ( '[]' == $value ) {
								$value = '';
							}
						}

						$input[ $this->tabs->tab_name ][ $name ] = $value;
					}
				}
			}
		}

		// Filter theme options
		$input = apply_filters( 'hocwp_theme_sanitize_option', $input, $this );

		// Filter options for current setting page
		$input[ $this->tab ] = apply_filters( 'hocwp_theme_sanitize_option_' . $this->tabs->tab_name, $input[ $this->tab ], $this );

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

	private function update_list_tabs() {
		if ( empty( $this->tabs ) || ! ( $this->tabs instanceof HOCWP_Theme_Admin_Setting_Tabs ) || ! HT()->array_has_value( $this->tabs->tabs ) ) {
			$this->tabs->get();
		}
	}

	public function html() {
		$this->update_list_tabs();

		$theme = wp_get_theme();

		$tab_obj = $this->tabs->tab;

		$mode = get_user_setting( 'theme_settings_view_mode', 'default' );
		?>
        <div class="wrap hocwp-theme" data-view-mode="<?php echo esc_attr( $mode ); ?>">
            <h1 class="hidden"><?php _e( 'Theme Settings', 'hocwp-theme' ); ?></h1>
            <hr class="wp-header-end" style="clear: both;">
            <div class="settings-box clearfix module">
				<?php
				if ( 'classic' == $mode ) {
					?>
                    <div class="module-body clearfix">
						<?php $this->tabs->html(); ?>
                        <div class="settings-content">
							<?php
							do_action( 'hocwp_theme_settings_page_' . $this->tabs->tab_name . '_form_before' );

							if ( ! ( $tab_obj instanceof HOCWP_Theme_Admin_Setting_Tab ) || ( ! is_callable( $tab_obj->callback ) && ! file_exists( $tab_obj->callback ) ) ) {
								$display = apply_filters( 'hocwp_theme_settings_page_' . $this->tabs->tab_name . '_display_form', true );

								if ( $display ) {
									$this->form_table();
								}
							} else {
								if ( is_callable( $tab_obj->callback ) ) {
									call_user_func( $tab_obj->callback );
								} elseif ( file_exists( $tab_obj->callback ) ) {
									include $tab_obj->callback;
								}
							}

							do_action( 'hocwp_theme_settings_page_' . $this->tabs->tab_name . '_form_after' );
							?>
                        </div>
                    </div>
					<?php
				} else {
					?>
                    <div class="header module-header">
                        <div class="inner clearfix">
                            <div class="theme-info">
                                <h2><?php printf( __( '%s options', 'hocwp-theme' ), HOCWP_THEME_NAME ); ?></h2>

                                <p><?php printf( __( 'Version %s', 'hocwp-theme' ), $theme->get( 'Version' ) ); ?></p>
                            </div>
							<?php
							if ( ! ( $tab_obj instanceof HOCWP_Theme_Admin_Setting_Tab ) || $tab_obj->submit_button ) {
								?>
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
								<?php
							}
							?>
                        </div>
                    </div>
                    <div class="module-body clearfix">
						<?php $this->tabs->html(); ?>
                        <div class="settings-content">
							<?php
							do_action( 'hocwp_theme_settings_page_' . $this->tabs->tab_name . '_form_before' );

							if ( ! ( $tab_obj instanceof HOCWP_Theme_Admin_Setting_Tab ) || ( ! is_callable( $tab_obj->callback ) && ! file_exists( $tab_obj->callback ) ) ) {
								$display = apply_filters( 'hocwp_theme_settings_page_' . $this->tabs->tab_name . '_display_form', true );

								if ( $display ) {
									$this->form_table();
								}
							} else {
								if ( is_callable( $tab_obj->callback ) ) {
									call_user_func( $tab_obj->callback );
								} elseif ( file_exists( $tab_obj->callback ) ) {
									include $tab_obj->callback;
								}
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
					<?php
				}
				?>
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
		$tab_obj = $this->tabs->tab;
		?>
        <form id="hocwpOptions" method="post" action="options.php" autocomplete="off"
              data-tab="<?php echo esc_attr( $this->tab ); ?>">
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

			if ( ! ( $tab_obj instanceof HOCWP_Theme_Admin_Setting_Tab ) || $tab_obj->submit_button ) {
				$this->submit_button(
					array(
						'attributes' => array(
							'form' => 'hocwpOptions',
							'id'   => 'settingSubmitBottom'
						)
					)
				);
			}
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

			HT_Util()->admin_notice( $args );
		}
	}

	public function admin_enqueue_scripts_action() {
		do_action( 'hocwp_theme_admin_setting_page_scripts', $this );
		do_action( 'hocwp_theme_admin_setting_page_' . $this->tabs->tab_name . '_scripts' );

		$fields = $this->settings_field;

		// Auto detect and load styles & scripts for setting page
		if ( is_array( $fields ) ) {
			foreach ( $fields as $field ) {
				$tab = isset( $field['tab'] ) ? $field['tab'] : '';

				if ( $tab == $this->tabs->tab_name ) {
					$callback = isset( $field['args']['callback'][1] ) ? $field['args']['callback'][1] : '';

					// Check to load styles and scripts on setting page
					switch ( $callback ) {
						case 'sortable_category':
						case 'sortable':
						case 'sortable_post':
						case 'sortable_page':
						case 'sortable_term':
							HT_Enqueue()->sortable();
							break;
						case 'color_picker':
							HT_Enqueue()->color_picker();
							break;
						case 'date_picker':
							HT_Enqueue()->datetime_picker();
							break;
						case 'image_upload':
						case 'image_link':
						case 'media_upload':
						case 'content_image':
						case 'content_with_image':
							HT_Enqueue()->media_upload();
							break;
						case 'fields':
							HT_Enqueue()->media_upload();
							HT_Enqueue()->sortable();
							HT_Enqueue()->color_picker();
							HT_Enqueue()->code_editor();
							HT_Enqueue()->chosen();
							break;
						case 'images':
							HT_Enqueue()->media_upload();
							HT_Enqueue()->sortable();
							break;
						case 'code_editor':
							HT_Enqueue()->code_editor();
							break;
						case 'chosen_term':
						case 'chosen_post':
						case 'chosen':
							HT_Enqueue()->chosen();
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