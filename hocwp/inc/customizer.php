<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'HT_Frontend' ) ) {
	require_once HOCWP_Theme()->core_path . '/inc/class-hocwp-theme-frontend.php';
}

add_action( 'customize_register', array( 'HOCWP_Theme_Customize', 'register' ) );

/**
 * Get custom colors for specific area.
 *
 * @param string $area
 * @param string $context
 *
 * @param string $theme_mod
 *
 * @return bool
 */
function hocwp_theme_get_color_for_area( $area = 'content', $context = 'text', $theme_mod = 'accent_accessible_colors' ) {
	if ( 'accent_accessible_colors' == $theme_mod ) {
		$defaults = HT_Frontend()->get_default_colors();
	} else {
		$defaults = array();
	}

	// Get the value from the theme-mod.
	$settings = get_theme_mod( $theme_mod, $defaults );

	// If we have a value return it.
	if ( isset( $settings[ $area ] ) && isset( $settings[ $area ][ $context ] ) ) {
		return $settings[ $area ][ $context ];
	}

	// Return false if the option doesn't exist.
	return false;
}

/**
 * Get css selector elements.
 *
 * @return mixed|void
 */
function hocwp_theme_get_elements_array() {
	// The array is formatted like this:
	// [key-in-saved-setting][sub-key-in-setting][css-property] = [elements].
	$elements = array(
		'content'       => array(
			'accent'     => array(
				'color'            => array(),
				'border-color'     => array(),
				'background-color' => array(),
				'fill'             => array()
			),
			'background' => array(
				'color'            => array(),
				'background-color' => array()
			),
			'text'       => array(
				'color'            => array(),
				'background-color' => array()
			),
			'secondary'  => array(
				'color'            => array(),
				'background-color' => array()
			),
			'borders'    => array(
				'border-color'        => array(),
				'background-color'    => array(),
				'border-bottom-color' => array(),
				'border-top-color'    => array(),
				'color'               => array()
			)
		),
		'header-footer' => array(
			'accent'     => array(
				'color'            => array(),
				'background-color' => array()
			),
			'background' => array(
				'color'            => array(),
				'background-color' => array()
			),
			'text'       => array(
				'color'               => array(),
				'background-color'    => array(),
				'border-bottom-color' => array(),
				'border-left-color'   => array()
			),
			'secondary'  => array(
				'color' => array()
			),
			'borders'    => array(
				'border-color'     => array(),
				'background-color' => array()
			)
		),
		'custom-color'  => array(
			'primary'   => array(
				'color'            => array(),
				'background-color' => array()
			),
			'secondary' => array(
				'color'            => array(),
				'background-color' => array()
			)
		)
	);

	if ( defined( 'HOCWP_THEME_CSS_ELEMENT_SELECTORS' ) && HT()->array_has_value( HOCWP_THEME_CSS_ELEMENT_SELECTORS ) ) {
		$elements = wp_parse_args( HOCWP_THEME_CSS_ELEMENT_SELECTORS, $elements );
	}

	/**
	 * Filters theme elements for customize style.
	 *
	 * @param array Array of elements
	 */

	return apply_filters( 'hocwp_theme_get_elements_array', $elements );
}

function hocwp_theme_get_customizer_css( $type = 'front-end' ) {
	$accent_hue_active = get_theme_mod( 'accent_hue_active' );

	if ( 'default' == $accent_hue_active ) {
		return '';
	}

	ob_start();

	/**
	 * Note – Styles are applied in this order:
	 * 1. Element specific
	 * 2. Helper classes
	 *
	 * This enables all helper classes to overwrite base element styles,
	 * meaning that any color classes applied in the block editor will
	 * have a higher priority than the base element styles.
	 */

	if ( 'auto_adjust' == $accent_hue_active ) {
		// Front-End Styles.
		if ( 'front-end' === $type ) {
			// Auto-calculated colors.
			$elements_definitions = hocwp_theme_get_elements_array();

			foreach ( $elements_definitions as $context => $props ) {
				foreach ( $props as $key => $definitions ) {
					foreach ( $definitions as $property => $elements ) {
						/*
						 * If we don't have an elements array or it is empty
						 * then skip this iteration early;
						 */
						if ( ! is_array( $elements ) || empty( $elements ) ) {
							continue;
						}

						$val = hocwp_theme_get_color_for_area( $context, $key );

						if ( $val ) {
							HT()->generate_css( implode( ',', $elements ), $property, $val );
						}
					}
				}
			}
		} elseif ( 'block-editor' === $type ) {

		} elseif ( 'classic-editor' === $type ) {

		}
	} else {
		if ( 'front-end' == $type ) {
			$elements_definitions = hocwp_theme_get_elements_array();

			if ( HT()->array_has_value( $elements_definitions ) ) {
				$custom_colors = isset( $elements_definitions['custom-color'] ) ? $elements_definitions['custom-color'] : '';

				if ( HT()->array_has_value( $custom_colors ) ) {
					foreach ( (array) $custom_colors as $key => $definitions ) {
						foreach ( $definitions as $property => $elements ) {
							/*
							 * If we don't have an elements array or it is empty
							 * then skip this iteration early;
							 */
							if ( ! is_array( $elements ) || empty( $elements ) ) {
								continue;
							}

							$val = hocwp_theme_get_color_for_area( 'custom-color', $key, 'custom_accessible_colors' );

							if ( $val ) {
								HT()->generate_css( implode( ',', $elements ), $property, $val );
							}
						}
					}
				}
			}
		} elseif ( 'block-editor' === $type ) {

		} elseif ( 'classic-editor' === $type ) {

		}
	}

	// Return the results.
	return ob_get_clean();

}

function hocwp_theme_get_customizer_color_vars() {
	$colors = array(
		'content'       => array(
			'setting' => 'background_color',
		),
		'header-footer' => array(
			'setting' => 'header_footer_background_color',
		)
	);

	if ( defined( 'HOCWP_THEME_DEFAULT_COLORS' ) && HT()->array_has_value( HOCWP_THEME_DEFAULT_COLORS ) ) {
		foreach ( HOCWP_THEME_DEFAULT_COLORS as $key => $data ) {
			if ( 'content' != $key && 'header-footer' != $key && 'custom-color' != $key ) {
				$colors[ $key ] = array(
					'setting' => $key . '_background_color'
				);
			}
		}
	}

	if ( current_theme_supports( 'custom-color' ) ) {
		$custom_color = get_theme_support( 'custom-color' );

		if ( HT()->array_has_value( $custom_color ) ) {
			$items = array();

			foreach ( $custom_color as $lists ) {
				if ( HT()->array_has_value( $lists ) ) {
					foreach ( $lists as $key => $color ) {
						$setting = 'custom_color_' . $key;

						$items[ $key ] = array( 'setting' => $setting );
					}
				}
			}

			if ( HT()->array_has_value( $items ) ) {
				$colors['custom-color'] = $items;
			}
		}
	}

	return apply_filters( 'hocwp_theme_customizer_color_vars', $colors );
}

function hocwp_theme_customize_controls_enqueue_scripts_action() {
	$theme_version = wp_get_theme()->get( 'Version' );

	// Add main customizer js file.
	wp_enqueue_script( 'hocwp-theme-customize', HOCWP_Theme()->core_url . '/js/customize.js', array(
		'jquery',
		'hocwp-theme'
	), $theme_version, false );

	// Add script for color calculations.
	wp_enqueue_script( 'hocwp-theme-color-calculations', HOCWP_Theme()->core_url . '/js/color-calculations.js', array(
		'wp-color-picker',
		'hocwp-theme'
	), $theme_version, false );

	// Add script for controls.
	wp_enqueue_script( 'hocwp-theme-customize-controls', HOCWP_Theme()->core_url . '/js/customize-controls.js', array(
		'hocwp-theme-color-calculations',
		'customize-controls',
		'underscore',
		'jquery',
		'hocwp-theme'
	), $theme_version, false );

	wp_localize_script( 'hocwp-theme-customize-controls', 'hocwpThemeCustomizer', array( 'colors' => hocwp_theme_get_customizer_color_vars() ) );
}

add_action( 'customize_controls_enqueue_scripts', 'hocwp_theme_customize_controls_enqueue_scripts_action' );

function hocwp_theme_customize_preview_init_action() {
	$theme_version = wp_get_theme()->get( 'Version' );

	wp_enqueue_script( 'hocwp-theme-customize-preview', HOCWP_Theme()->core_url . '/js/customize-preview.js', array(
		'customize-preview',
		'customize-selective-refresh',
		'jquery',
		'hocwp-theme'
	), $theme_version, true );

	if ( ! function_exists( 'hocwp_theme_get_inline_css' ) ) {
		require_once HOCWP_THEME_CORE_PATH . '/inc/template.php';
	}

	wp_localize_script( 'hocwp-theme-customize-preview', 'hocwpThemeCustomizer', array(
		'colors'     => hocwp_theme_get_customizer_color_vars(),
		'elements'   => hocwp_theme_get_elements_array(),
		'inline_css' => hocwp_theme_get_inline_css()
	) );
}

add_action( 'customize_preview_init', 'hocwp_theme_customize_preview_init_action' );

function hocwp_theme_customize_inline_css() {
	$data = hocwp_theme_get_customizer_css();

	if ( ! function_exists( 'hocwp_theme_get_inline_css' ) ) {
		require_once HOCWP_THEME_CORE_PATH . '/inc/template.php';
	}

	// Allow user add custom inline css.
	$data .= hocwp_theme_get_inline_css();

	if ( ! empty( $data ) ) {
		wp_add_inline_style( 'hocwp-theme-custom-style', $data );
	}
}

add_action( 'wp_enqueue_scripts', 'hocwp_theme_customize_inline_css', 999 );

/**
 * Run custom code after customize saved.
 */
function hocwp_theme_customize_save_after_action() {
	$customized = isset( $_POST['customized'] ) ? $_POST['customized'] : '';

	if ( ! empty( $customized ) ) {
		$customized = HT()->json_string_to_array( $customized );

		if ( isset( $customized['custom_logo'] ) ) {
			// Remove theme logo general option if customize logo empty.
			if ( empty( $customized['custom_logo'] ) ) {
				HT_Options()->update( 'logo_image', '', 'general' );
			}
		}
	}
}

add_action( 'customize_save_after', 'hocwp_theme_customize_save_after_action' );