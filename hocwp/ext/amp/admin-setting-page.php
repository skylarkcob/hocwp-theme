<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_settings_page_amp_tab( $tabs ) {
	$tabs['amp'] = array(
		'text' => __( 'AMP', 'hocwp-theme' ),
		'icon' => '<span class="dashicons dashicons-smartphone"></span>'
	);

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_amp_tab' );

function hocwp_theme_settings_page_amp_section() {
	$sections = array();

	return $sections;
}

add_filter( 'hocwp_theme_settings_page_amp_settings_section', 'hocwp_theme_settings_page_amp_section' );

function hocwp_theme_settings_page_amp_field() {
	$fields = array();

	$options = ht_util()->get_theme_options( 'amp' );

	$field    = hocwp_theme_create_setting_field( 'menu_button_label', __( 'Menu Button Label', 'hocwp-theme' ), 'input', array(), 'string', 'amp' );
	$fields[] = $field;

	$args = array(
		'class' => 'medium-text',
		'type'  => 'checkbox',
		'label' => __( 'Disable default font from AMP page styles.', 'hocwp-theme' )
	);

	$field    = hocwp_theme_create_setting_field( 'remove_default_font', __( 'Remove Default Font', 'hocwp-theme' ), '', $args, 'boolean', 'amp' );
	$fields[] = $field;

	$args = array(
		'description' => __( 'These fonts are used on the AMP site. Each font is separated by commas.', 'hocwp-theme' )
	);

	$field    = hocwp_theme_create_setting_field( 'fonts', __( 'Fonts', 'hocwp-theme' ), 'input', $args, 'string', 'amp' );
	$fields[] = $field;

	$fonts = isset( $options['fonts'] ) ? $options['fonts'] : '';

	if ( ! empty( $fonts ) ) {
		$fonts = explode( ',', $fonts );
		$fonts = array_map( 'trim', $fonts );

		$args = array(
			'type' => 'url'
		);

		foreach ( $fonts as $font ) {
			$key = strtolower( $font );
			$key = str_replace( ' ', '_', $key );

			$field    = hocwp_theme_create_setting_field( 'font_' . $key, sprintf( __( 'Font %s URL', 'hocwp-theme' ), $font ), 'input', $args, 'string', 'amp' );
			$fields[] = $field;
		}
	}

	$args = array(
		'data-code-editor' => 1,
		'data-mode'        => 'css'
	);

	$field    = hocwp_theme_create_setting_field( 'custom_css', __( 'Custom CSS', 'hocwp-theme' ), 'textarea', $args, 'text', 'amp' );
	$fields[] = $field;

	$field    = hocwp_theme_create_setting_field( 'amp_footer', __( 'Site Footer', 'hocwp-theme' ), 'editor', array(), 'html', 'amp' );
	$fields[] = $field;

	$args = array(
		'data-code-editor' => 1,
		'data-mode'        => 'htmlmixed'
	);

	$field    = hocwp_theme_create_setting_field( 'amp_head', __( 'Add to Head', 'hocwp-theme' ), 'textarea', $args, 'html', 'amp' );
	$fields[] = $field;

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_amp_settings_field', 'hocwp_theme_settings_page_amp_field' );

function hocwp_theme_admin_setting_page_amp_scripts() {
	if ( function_exists( 'ht_enqueue' ) ) {
		ht_enqueue()->code_editor();
	} else {
		ht_util()->enqueue_code_editor();
	}
}

add_action( 'hocwp_theme_admin_setting_page_amp_scripts', 'hocwp_theme_admin_setting_page_amp_scripts' );