<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_settings_page_general_tab( $tabs ) {
	$tabs['general'] = array(
		'text' => __( 'General', 'hocwp-theme' ),
		'icon' => '<span class="dashicons dashicons-admin-site"></span>'
	);

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_general_tab' );

global $hocwp_theme;
if ( 'general' != $hocwp_theme->option->tab ) {
	return;
}

function hocwp_theme_settings_page_general_section() {
	$sections = array(
		'site_identity' => array(
			'tab'         => 'general',
			'id'          => 'site_identity',
			'title'       => __( 'Site Identity', 'hocwp-theme' ),
			'description' => __( 'Provides logo and favicon so visitors can identify your site.', 'hocwp-theme' )
		)
	);

	$sections = apply_filters( 'hocwp_theme_setting_sections', $sections );

	return $sections;
}

add_filter( 'hocwp_theme_settings_page_general_settings_section', 'hocwp_theme_settings_page_general_section' );

function hocwp_theme_settings_page_general_field( $fields ) {
	global $hocwp_theme;
	$options      = $hocwp_theme->options['general'];
	$logo_display = $options['logo_display'];

	$value = isset( $options['site_icon'] ) ? $options['site_icon'] : '';

	if ( ! HOCWP_Theme::is_positive_number( $value ) ) {
		$value = get_option( 'site_icon' );
	}

	$fields[] = array(
		'tab'     => 'general',
		'section' => 'site_identity',
		'id'      => 'site_icon',
		'title'   => __( 'Site Icon', 'hocwp-theme' ),
		'args'    => array(
			'callback'      => array( 'HOCWP_Theme_HTML_Field', 'media_upload' ),
			'description'   => __( 'The Site Icon is used as a browser and app icon for your site. Icons must be square, and at least <strong>32</strong> pixels wide and tall.', 'hocwp-theme' ),
			'callback_args' => array(
				'value' => $value
			)
		)
	);

	$fields[] = array(
		'tab'     => 'general',
		'section' => 'site_identity',
		'id'      => 'logo_display',
		'title'   => __( 'Logo Display', 'hocwp-theme' ),
		'args'    => array(
			'label_for'     => true,
			'callback'      => array( 'HOCWP_Theme_HTML_Field', 'select' ),
			'callback_args' => array(
				'options'             => array(
					'image'  => array(
						'text'          => __( 'Image', 'hocwp-theme' ),
						'data-relation' => 'tr.logo_image'
					),
					'text'   => array(
						'text'          => __( 'Text', 'hocwp-theme' ),
						'data-relation' => 'tr.logo_text'
					),
					'custom' => array(
						'text'          => __( 'Custom HTML', 'hocwp-theme' ),
						'data-relation' => 'tr.logo_html'
					)
				),
				'class'               => 'auto-text relationship',
				'data-relation-group' => 'site-logo'
			)
		)
	);

	$class = array(
		'hidden',
		'site-logo'
	);

	$field_text = array(
		'tab'     => 'general',
		'section' => 'site_identity',
		'id'      => 'logo_text',
		'title'   => __( 'Logo Text', 'hocwp-theme' ),
		'args'    => array(
			'label_for'   => true,
			'description' => __( 'If empty, site name will be used. You can use <code>[DOMAIN]</code> for dynamic displaying.', 'hocwp-theme' )
		)
	);

	$field_custom = array(
		'tab'     => 'general',
		'section' => 'site_identity',
		'id'      => 'logo_html',
		'title'   => __( 'Logo HTML', 'hocwp-theme' ),
		'args'    => array(
			'label_for'     => true,
			'callback'      => array( 'HOCWP_Theme_HTML_Field', 'textarea' ),
			'callback_args' => array(
				'class' => 'widefat',
				'rows'  => 3
			)
		)
	);

	$value = isset( $options['logo_image'] ) ? $options['logo_image'] : '';

	if ( ! HOCWP_Theme::is_positive_number( $value ) ) {
		$value = get_theme_mod( 'custom_logo' );
	}

	$field_image = array(
		'tab'     => 'general',
		'section' => 'site_identity',
		'id'      => 'logo_image',
		'title'   => __( 'Logo Image', 'hocwp-theme' ),
		'args'    => array(
			'callback'      => array( 'HOCWP_Theme_HTML_Field', 'media_upload' ),
			'callback_args' => array(
				'value' => $value
			)
		)
	);

	$text_class   = $class;
	$custom_class = $class;

	switch ( $logo_display ) {
		case 'text':
			unset( $text_class[ array_search( 'hidden', $text_class ) ] );
			break;
		case 'custom':
			unset( $custom_class[ array_search( 'hidden', $custom_class ) ] );
			break;
		default:
			unset( $class[ array_search( 'hidden', $class ) ] );
	}

	$field_text['args']['class'] = $text_class;

	$fields[] = $field_text;

	$field_custom['args']['class'] = $custom_class;

	$fields[] = $field_custom;

	$field_image['args']['class'] = $class;

	$fields[] = $field_image;

	$fields = apply_filters( 'hocwp_theme_setting_fields', $fields, $options );

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_general_settings_field', 'hocwp_theme_settings_page_general_field' );

function hocwp_theme_admin_setting_page_general_scripts() {
	HT_Util()->enqueue_media();
	wp_enqueue_script( 'hocwp-theme-relationship-control' );
}

add_action( 'hocwp_theme_admin_setting_page_general_scripts', 'hocwp_theme_admin_setting_page_general_scripts' );