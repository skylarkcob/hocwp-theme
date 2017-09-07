<?php
function hocwp_theme_settings_page_custom_code_tab( $tabs ) {
	$tabs['custom_code'] = __( 'Custom Code', 'hocwp-theme' );

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_custom_code_tab' );

global $hocwp_theme;
if ( 'custom_code' != $hocwp_theme->option->tab ) {
	return;
}

function hocwp_theme_settings_page_custom_code_field() {
	$fields = array(
		array(
			'id'    => 'google_analytics',
			'title' => __( 'Google Analytics', 'hocwp-theme' ),
			'tab'   => 'custom_code',
			'args'  => array(
				'type'          => 'string',
				'callback'      => array( 'HOCWP_Theme_HTML_Field', 'textarea' ),
				'callback_args' => array(
					'class' => 'widefat'
				)
			)
		),
		array(
			'id'    => 'css',
			'title' => __( 'Cascading Style Sheets', 'hocwp-theme' ),
			'tab'   => 'custom_code',
			'args'  => array(
				'type'          => 'string',
				'callback'      => array( 'HOCWP_Theme_HTML_Field', 'textarea' ),
				'callback_args' => array(
					'class' => 'widefat'
				)
			)
		),
		array(
			'id'    => 'head',
			'title' => __( 'Head Code', 'hocwp-theme' ),
			'tab'   => 'custom_code',
			'args'  => array(
				'type'          => 'string',
				'callback'      => array( 'HOCWP_Theme_HTML_Field', 'textarea' ),
				'description'   => __( 'Add code at the end of the <code>&lt;head&gt;</code> tag.', 'hocwp-theme' ),
				'callback_args' => array(
					'class' => 'widefat'
				)
			)
		),
		array(
			'id'    => 'body',
			'title' => __( 'Body Code', 'hocwp-theme' ),
			'tab'   => 'custom_code',
			'args'  => array(
				'type'          => 'string',
				'callback'      => array( 'HOCWP_Theme_HTML_Field', 'textarea' ),
				'description'   => __( 'Add code before <code>&lt;/body&gt;</code> tag.', 'hocwp-theme' ),
				'callback_args' => array(
					'class' => 'widefat'
				)
			)
		)
	);

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_custom_code_settings_field', 'hocwp_theme_settings_page_custom_code_field' );