<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Separator_Control extends WP_Customize_Control {
	public $display = true;

	public function __construct( $manager, $id, $args = array() ) {
		if ( isset( $args['display'] ) ) {
			$this->display = (bool) $args['display'];
		}

		parent::__construct( $manager, $id, $args );
	}

	/**
	 * Render the hr.
	 */
	public function render_content() {
		echo '<hr/>';
	}

	public function render() {
		$id    = 'customize-control-' . str_replace( array( '[', ']' ), array( '-', '' ), $this->id );
		$class = 'customize-control customize-control-' . $this->type;

		$style = '';

		if ( ! $this->display ) {
			$style = 'display:none';
		}

		printf( '<li id="%s" class="%s" style="%s">', esc_attr( $id ), esc_attr( $class ), esc_attr( $style ) );
		$this->render_content();
		echo '</li>';
	}
}

class HOCWP_Theme_Customize {
	/**
	 * Register customizer options.
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	public static function register( $wp_customize ) {
		if ( $wp_customize instanceof WP_Customize_Manager ) {

			/* Theme color choices --------------------- */

			// Enable picking an accent color.
			$wp_customize->add_setting(
				'accent_hue_active',
				array(
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => array( __CLASS__, 'sanitize_select' ),
					'transport'         => 'refresh',
					'default'           => 'default'
				)
			);

			// Choose which type of color using on theme.
			$wp_customize->add_control(
				'accent_hue_active',
				array(
					'type'    => 'radio',
					'section' => 'colors',
					'label'   => __( 'Primary Color', 'hocwp-theme' ),
					'choices' => array(
						'default'     => __( 'Default', 'hocwp-theme' ),
						'auto_adjust' => __( 'Auto Adjust', 'hocwp-theme' ),
						'custom'      => __( 'Custom', 'hocwp-theme' )
					)
				)
			);

			// Add the setting for the hue color picker.
			$wp_customize->add_setting(
				'accent_hue',
				array(
					'default'           => 344,
					'type'              => 'theme_mod',
					'sanitize_callback' => 'absint',
					'transport'         => 'postMessage'
				)
			);

			// Add setting to hold colors derived from the accent hue.
			$wp_customize->add_setting(
				'accent_accessible_colors',
				array(
					'default'           => ht_frontend()->get_default_colors(),
					'type'              => 'theme_mod',
					'transport'         => 'postMessage',
					'sanitize_callback' => array( __CLASS__, 'sanitize_accent_accessible_colors' )
				)
			);

			// Add the hue-only color picker for the accent color.
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'accent_hue',
					array(
						'section'         => 'colors',
						'settings'        => 'accent_hue',
						'description'     => __( 'Apply a custom color for links, buttons, featured images.', 'hocwp-theme' ),
						'mode'            => 'hue',
						'active_callback' => function () use ( $wp_customize ) {
							return ( 'auto_adjust' === $wp_customize->get_setting( 'accent_hue_active' )->value() );
						}
					)
				)
			);

			/* Separator --------------------- */

			$wp_customize->add_setting(
				'hocwp_theme_customize_color_border_1',
				array(
					'sanitize_callback' => 'wp_filter_nohtml_kses'
				)
			);

			$wp_customize->add_control(
				new HOCWP_Theme_Separator_Control(
					$wp_customize,
					'hocwp_theme_customize_color_border_1',
					array(
						'section'         => 'colors',
						'display'         => false,
						'active_callback' => function () use ( $wp_customize ) {
							$type = $wp_customize->get_setting( 'accent_hue_active' )->value();

							return ( 'custom' === $type || 'auto_adjust' == $type );
						}
					)
				)
			);

			/* Theme custom accent colors --------------------- */

			if ( defined( 'HOCWP_THEME_DEFAULT_COLORS' ) && ht()->array_has_value( HOCWP_THEME_DEFAULT_COLORS ) ) {
				foreach ( HOCWP_THEME_DEFAULT_COLORS as $key => $data ) {
					if ( 'content' != $key && 'header-footer' != $key && 'custom-color' != $key ) {
						$wp_customize->add_setting(
							$key . '_background_color',
							array(
								'default'              => '',
								'theme_supports'       => 'custom-background',
								'sanitize_callback'    => 'sanitize_hex_color',
								'sanitize_js_callback' => 'maybe_hash_hex_color',
								'transport'            => 'postMessage'
							)
						);

						$wp_customize->add_control(
							new WP_Customize_Color_Control(
								$wp_customize,
								$key . '_background_color',
								array(
									'label'           => sprintf( __( 'Background Color (%s)', 'hocwp-theme' ), $key ),
									'section'         => 'colors',
									'active_callback' => function () use ( $wp_customize ) {
										return ( 'auto_adjust' === $wp_customize->get_setting( 'accent_hue_active' )->value() );
									}
								)
							)
						);
					}
				}
			}

			/* Theme custom colors --------------------- */

			if ( current_theme_supports( 'custom-color' ) ) {
				$supports = get_theme_support( 'custom-color' );

				if ( ht()->array_has_value( $supports ) ) {
					// Add setting to hold colors derived from the custom color settings.
					$wp_customize->add_setting(
						'custom_accessible_colors',
						array(
							'default'           => ht_frontend()->get_default_colors(),
							'type'              => 'theme_mod',
							'transport'         => 'postMessage',
							'sanitize_callback' => array( __CLASS__, 'sanitize_accent_accessible_colors' )
						)
					);

					foreach ( $supports as $colors ) {
						if ( ht()->array_has_value( $colors ) ) {
							foreach ( $colors as $key => $color ) {
								if ( is_string( $color ) && sanitize_hex_color( $color ) ) {
									$setting = 'custom_color_' . $key;

									$wp_customize->add_setting(
										$setting,
										array(
											'default'           => $color,
											'type'              => 'theme_mod',
											'transport'         => 'postMessage',
											'sanitize_callback' => 'sanitize_hex_color'
										)
									);

									$wp_customize->add_control(
										new WP_Customize_Color_Control(
											$wp_customize,
											$setting,
											array(
												'label'           => sprintf( __( 'Theme Custom Color (%s)', 'hocwp-theme' ), $key ),
												'settings'        => $setting,
												'section'         => 'colors',
												'active_callback' => function () use ( $wp_customize ) {
													return ( 'custom' === $wp_customize->get_setting( 'accent_hue_active' )->value() );
												}
											)
										)
									);
								}
							}
						}
					}
				}
			}

			// Update background color with postMessage, so inline CSS output is updated as well.
			$wp_customize->get_setting( 'background_color' )->transport = 'postMessage';
		}
	}


	/**
	 * Sanitize select.
	 *
	 * @param string $input The input from the setting.
	 * @param object $setting The selected setting.
	 *
	 * @return string $input|$setting->default The input from the setting or the default setting.
	 */
	public static function sanitize_select( $input, $setting ) {
		$input   = sanitize_key( $input );
		$choices = $setting->manager->get_control( $setting->id )->choices;

		return ( array_key_exists( $input, $choices ) ? $input : $setting->default );
	}

	/**
	 * Sanitize boolean for checkbox.
	 *
	 * @param bool $checked Whether or not a box is checked.
	 *
	 * @return bool
	 */
	public static function sanitize_checkbox( $checked ) {
		return ( ( isset( $checked ) && true === $checked ) ? true : false );
	}

	public static function sanitize_accent_accessible_colors( $value ) {
		// Make sure the value is an array. Do not typecast, use empty array as fallback.
		$value = is_array( $value ) ? $value : array();

		// Loop values.
		foreach ( $value as $key => $values ) {
			if ( ht()->array_has_value( $values ) ) {
				foreach ( $values as $context => $color_val ) {
					$value[ $key ][ $context ] = sanitize_hex_color( $color_val );
				}
			}
		}

		return $value;
	}

	public static function customize_opacity_range() {
		/**
		 * Filter the input attributes for opacity
		 *
		 * @param array $attrs {
		 *     The attributes
		 *
		 * @type int $min Minimum value
		 * @type int $max Maximum value
		 * @type int $step Interval between numbers
		 * }
		 */
		return apply_filters(
			'hocwp_theme_customize_opacity_range',
			array(
				'min'  => 0,
				'max'  => 90,
				'step' => 5
			)
		);
	}
}