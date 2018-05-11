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
					'class'            => 'widefat',
					'data-code-editor' => 1
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
					'data-mode'        => 'css'
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
					'class'            => 'widefat',
					'data-code-editor' => 1
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
					'class'            => 'widefat',
					'data-code-editor' => 1
				)
			)
		)
	);

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_custom_code_settings_field', 'hocwp_theme_settings_page_custom_code_field' );

function hocwp_theme_admin_setting_page_custom_code_scripts() {
	wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
	wp_enqueue_code_editor( array( 'type' => 'text/css' ) );
	wp_enqueue_script( 'hocwp-theme-code-editor' );
}

add_action( 'hocwp_theme_admin_setting_page_custom_code_scripts', 'hocwp_theme_admin_setting_page_custom_code_scripts' );