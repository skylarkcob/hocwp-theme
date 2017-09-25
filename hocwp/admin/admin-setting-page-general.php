<?php
function hocwp_theme_settings_page_general_tab( $tabs ) {
	$tabs['general'] = __( 'General', 'hocwp-theme' );

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_general_tab' );

global $hocwp_theme;
if ( 'general' != $hocwp_theme->option->tab ) {
	return;
}

function hocwp_theme_settings_page_general_section() {
	$fields = array(
		'site_logo' => array(
			'tab'         => 'general',
			'id'          => 'site_logo',
			'title'       => __( 'Site Logo', 'hocwp-theme' ),
			'description' => __( 'When you install the template you have predefined settings for the logo within the selected style. Uploading your logo is as easy as opening the template manager and selecting the style parameters and uploading your logo in the logo field.', 'hocwp-theme' )
		)
	);

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_general_settings_section', 'hocwp_theme_settings_page_general_section' );

function hocwp_theme_settings_page_general_field() {
	global $hocwp_theme;
	$options      = $hocwp_theme->options['general'];
	$logo_display = $options['logo_display'];
	$fields       = array(
		array(
			'tab'     => 'general',
			'section' => 'site_logo',
			'id'      => 'logo_display',
			'title'   => __( 'Display', 'hocwp-theme' ),
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
		)

	);
	$class        = array(
		'hidden',
		'site-logo'
	);
	$field_text   = array(
		'tab'     => 'general',
		'section' => 'site_logo',
		'id'      => 'logo_text',
		'title'   => __( 'Text', 'hocwp-theme' ),
		'args'    => array(
			'label_for'   => true,
			'description' => __( 'If empty, site name will be used. You can use <code>[DOMAIN]</code> for dynamic displaying.', 'hocwp-theme' )
		)
	);
	$field_custom = array(
		'tab'     => 'general',
		'section' => 'site_logo',
		'id'      => 'logo_html',
		'title'   => __( 'HTML', 'hocwp-theme' ),
		'args'    => array(
			'label_for'     => true,
			'callback'      => array( 'HOCWP_Theme_HTML_Field', 'textarea' ),
			'callback_args' => array(
				'class' => 'widefat',
				'rows'  => 3
			)
		)
	);

	$field_image  = array(
		'tab'     => 'general',
		'section' => 'site_logo',
		'id'      => 'logo_image',
		'title'   => __( 'Image', 'hocwp-theme' ),
		'args'    => array(
			'callback' => array( 'HOCWP_Theme_HTML_Field', 'media_upload' ),
			'default'  => get_theme_mod( 'custom_logo' )
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
	$field_text['args']['class']   = $text_class;
	$fields[]                      = $field_text;
	$field_custom['args']['class'] = $custom_class;
	$fields[]                      = $field_custom;
	$field_image['args']['class']  = $class;
	$fields[]                      = $field_image;

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_general_settings_field', 'hocwp_theme_settings_page_general_field' );

function hocwp_theme_admin_setting_page_general_scripts() {
	wp_enqueue_media();
	wp_enqueue_script( 'hocwp-theme-media-upload' );
	wp_enqueue_style( 'hocwp-theme-media-upload-style' );
	wp_enqueue_script( 'hocwp-theme-relationship-control' );
}

add_action( 'hocwp_theme_admin_setting_page_general_scripts', 'hocwp_theme_admin_setting_page_general_scripts' );