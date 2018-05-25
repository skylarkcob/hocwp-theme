<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_settings_page_reading_tab( $tabs ) {
	$tabs['reading'] = array(
		'text' => __( 'Reading', 'hocwp-theme' ),
		'icon' => '<span class="dashicons dashicons-visibility"></span>'
	);

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_reading_tab' );

global $hocwp_theme;

if ( 'reading' != $hocwp_theme->option->tab ) {
	return;
}

function hocwp_theme_settings_page_reading_section() {
	$sections = array();

	if ( hocwp_theme_is_shop_site() ) {
		$sections['shop_section'] = array(
			'tab'   => 'reading',
			'id'    => 'shop_section',
			'title' => __( 'Shop Settings', 'hocwp-theme' )
		);
	}

	$sections['back_top_section'] = array(
		'tab'   => 'reading',
		'id'    => 'back_top_section',
		'title' => __( 'Back To Top Button', 'hocwp-theme' )
	);

	return $sections;
}

add_filter( 'hocwp_theme_settings_page_reading_settings_section', 'hocwp_theme_settings_page_reading_section' );

function hocwp_theme_settings_page_reading_field() {
	$fields = array();

	$field    = hocwp_theme_create_setting_field( 'theme_color', __( 'Theme Color', 'hocwp-theme' ), 'color_picker', '', 'string', 'reading' );
	$fields[] = $field;

	$field    = hocwp_theme_create_setting_field( '404', __( 'Not Found Page', 'hocwp-theme' ), 'select_page', '', 'positive_number', 'reading' );
	$fields[] = $field;

	$args = array(
		'class' => 'medium-text'
	);

	$field    = hocwp_theme_create_setting_field( 'excerpt_more', __( 'Excerpt More', 'hocwp-theme' ), '', $args, 'string', 'reading' );
	$fields[] = $field;

	$args = array(
		'class' => 'medium-text',
		'type'  => 'number'
	);

	$field    = hocwp_theme_create_setting_field( 'excerpt_length', __( 'Excerpt Length', 'hocwp-theme' ), '', $args, 'positive_integer', 'reading' );
	$fields[] = $field;

	$args = array(
		'class' => 'medium-text',
		'type'  => 'checkbox',
		'label' => __( 'Make last widget on sidebar sticky.', 'hocwp-theme' )
	);

	$field    = hocwp_theme_create_setting_field( 'sticky_last_widget', __( 'Sticky Last Widget', 'hocwp-theme' ), '', $args, 'boolean', 'reading' );
	$fields[] = $field;

	$args = array(
		'class'   => 'regular-text',
		'options' => array(
			''      => __( 'Default', 'hocwp-theme' ),
			'right' => _x( 'Right', 'sidebar position', 'hocwp-theme' ),
			'left'  => _x( 'Left', 'sidebar position', 'hocwp-theme' )
		)
	);

	$field    = hocwp_theme_create_setting_field( 'sidebar_position', __( 'Sidebar Position', 'hocwp-theme' ), 'select', $args, 'string', 'reading' );
	$fields[] = $field;

	$args = array(
		'type'  => 'checkbox',
		'label' => __( 'Add random end point to url for displaying random post?', 'hocwp-theme' )
	);

	$field    = hocwp_theme_create_setting_field( 'random', __( 'Random', 'hocwp-theme' ), '', $args, 'boolean', 'reading' );
	$fields[] = $field;

	if ( hocwp_theme_is_shop_site() ) {
		$fields[] = array(
			'tab'     => 'reading',
			'section' => 'shop_section',
			'id'      => 'products_per_page',
			'title'   => __( 'Products Per Page', 'hocwp-theme' ),
			'args'    => array(
				'label_for'     => true,
				'default'       => $GLOBALS['hocwp_theme']->defaults['posts_per_page'],
				'callback_args' => array(
					'class' => 'small-text',
					'type'  => 'number'
				)
			)
		);
	}

	$args = array(
		'class' => 'medium-text',
		'type'  => 'checkbox',
		'label' => __( 'Displays the back to top button when user scrolls down the bottom of site.', 'hocwp-theme' )
	);

	$field    = hocwp_theme_create_setting_field( 'back_to_top', __( 'Active', 'hocwp-theme' ), '', $args, 'boolean', 'reading', 'back_top_section' );
	$fields[] = $field;

	$color = HT_Util()->get_theme_option( 'back_top_bg', '', 'reading' );

	$args = array(
		'background_color' => $color,
		'style'            => HT_Util()->get_theme_option( 'back_top_style', '', 'reading' )
	);

	$field    = hocwp_theme_create_setting_field( 'back_top_icon', __( 'Icon', 'hocwp-theme' ), 'media_upload', $args, 'positive_number', 'reading', 'back_top_section' );
	$fields[] = $field;

	$field    = hocwp_theme_create_setting_field( 'back_top_bg', __( 'Background Color', 'hocwp-theme' ), 'color_picker', '', 'string', 'reading', 'back_top_section' );
	$fields[] = $field;

	$field    = hocwp_theme_create_setting_field( 'back_top_style', __( 'Style Attribute', 'hocwp-theme' ), 'input', '', 'string', 'reading', 'back_top_section' );
	$fields[] = $field;

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_reading_settings_field', 'hocwp_theme_settings_page_reading_field' );

function hocwp_theme_admin_setting_page_reading_scripts() {
	HT_Util()->enqueue_media();
	HT_Util()->enqueue_color_picker();
}

add_action( 'hocwp_theme_admin_setting_page_reading_scripts', 'hocwp_theme_admin_setting_page_reading_scripts' );