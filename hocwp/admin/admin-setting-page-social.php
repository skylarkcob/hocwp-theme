<?php
function hocwp_theme_settings_page_social_tab( $tabs ) {
	$tabs['social'] = __( 'Socials', 'hocwp-theme' );

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_social_tab' );

global $hocwp_theme;
if ( 'social' != $hocwp_theme->option->tab ) {
	return;
}

function hocwp_theme_settings_page_social_section() {
	$fields = array(
		'facebook' => array(
			'tab'         => 'social',
			'id'          => 'facebook',
			'title'       => __( 'Facebook', 'hocwp-theme' ),
			'description' => __( 'See what your Facebook friends liked, shared, or commented on across the Web.', 'hocwp-theme' )
		)
	);

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_social_settings_section', 'hocwp_theme_settings_page_social_section' );

function hocwp_theme_settings_page_social_field() {
	$fields = array(
		array(
			'tab'     => 'social',
			'section' => 'facebook',
			'id'      => 'facebook_app_id',
			'title'   => __( 'APP ID', 'hocwp-theme' ),
			'args'    => array(
				'label_for' => true
			)
		),
		array(
			'tab'     => 'social',
			'section' => 'facebook',
			'id'      => 'facebook_sdk_javascript',
			'title'   => __( 'SDK JavaScript', 'hocwp-theme' ),
			'args'    => array(
				'label_for'     => true,
				'callback'      => array( 'HOCWP_Theme_HTML_Field', 'textarea' ),
				'callback_args' => array(
					'class' => 'widefat',
					'rows'  => 8
				)
			)
		)
	);

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_social_settings_field', 'hocwp_theme_settings_page_social_field' );