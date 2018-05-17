<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_settings_page_custom_code_tab( $tabs ) {
	$tabs['custom_code'] = array(
		'text' => __( 'Custom Code', 'hocwp-theme' ),
		'icon' => '<span class="dashicons dashicons-editor-code"></span>'
	);

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
					'class'            => 'widefat',
					'data-code-editor' => 1,
					'rows'             => 8
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
					'class'            => 'widefat',
					'data-code-editor' => 1,
					'data-mode'        => 'css',
					'rows'             => 15
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
				'description'   => __( 'Add code between <code>&lt;head&gt;</code> and <code>&lt;/head&gt;</code> tag.', 'hocwp-theme' ),
				'callback_args' => array(
					'class'            => 'widefat',
					'data-code-editor' => 1,
					'rows'             => 15
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
				'description'   => __( 'Add code between <code>&lt;body&gt;</code> and <code>&lt;/body&gt;</code> tag.', 'hocwp-theme' ),
				'callback_args' => array(
					'class'            => 'widefat',
					'data-code-editor' => 1,
					'rows'             => 15
				)
			)
		),
		array(
			'id'    => 'footer',
			'title' => __( 'Footer Code', 'hocwp-theme' ),
			'tab'   => 'custom_code',
			'args'  => array(
				'type'          => 'string',
				'callback'      => array( 'HOCWP_Theme_HTML_Field', 'textarea' ),
				'description'   => __( 'Add code before closing <code>&lt;body&gt;</code> tag.', 'hocwp-theme' ),
				'callback_args' => array(
					'class'            => 'widefat',
					'data-code-editor' => 1,
					'rows'             => 15
				)
			)
		)
	);

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_custom_code_settings_field', 'hocwp_theme_settings_page_custom_code_field' );

function hocwp_theme_admin_setting_page_custom_code_scripts() {
	HT_Util()->enqueue_code_editor();
}

add_action( 'hocwp_theme_admin_setting_page_custom_code_scripts', 'hocwp_theme_admin_setting_page_custom_code_scripts' );