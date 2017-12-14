<?php
function hocwp_theme_settings_page_reading_tab( $tabs ) {
	$tabs['reading'] = __( 'Reading', 'hocwp-theme' );

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

	return $sections;
}

add_filter( 'hocwp_theme_settings_page_reading_settings_section', 'hocwp_theme_settings_page_reading_section' );

function hocwp_theme_settings_page_reading_field() {
	$fields = array();

	$args     = array(
		'class' => 'medium-text'
	);
	$field    = hocwp_theme_create_setting_field( 'excerpt_more', __( 'Excerpt More', 'hocwp-theme' ), '', $args, 'string', 'reading' );
	$fields[] = $field;

	$args     = array(
		'class' => 'medium-text',
		'type'  => 'number'
	);
	$field    = hocwp_theme_create_setting_field( 'excerpt_length', __( 'Excerpt Length', 'hocwp-theme' ), '', $args, 'positive_integer', 'reading' );
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

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_reading_settings_field', 'hocwp_theme_settings_page_reading_field' );