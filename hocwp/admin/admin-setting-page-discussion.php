<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_settings_page_discussion_tab( $tabs ) {
	$tabs['discussion'] = array(
		'text' => __( 'Discussion', 'hocwp-theme' ),
		'icon' => '<span class="dashicons dashicons-testimonial"></span>'
	);

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_discussion_tab' );

global $hocwp_theme;
if ( 'discussion' != $hocwp_theme->option->tab ) {
	return;
}

function hocwp_theme_settings_page_discussion_section() {
	$sections = array();

	if ( HT_extension()->is_active( 'ext/comment-notification.php' ) ) {
		$sections[] = array(
			'id'       => 'comment_notification',
			'title'    => __( 'Comment Notification', 'hocwp-theme' ),
			'tab'      => 'discussion',
			'callback' => 'hocwp_theme_settings_page_discussion_section_comment_notification_callback'
		);
	}

	return $sections;
}

add_filter( 'hocwp_theme_settings_page_discussion_settings_section', 'hocwp_theme_settings_page_discussion_section' );

function hocwp_theme_settings_page_discussion_section_comment_notification_callback() {
	$tags = hocwp_theme_notify_comment_tags();
	$tag  = '';
	foreach ( $tags as $value ) {
		$tag .= '<code>' . $value . '</code>, ';
	}
	$tag  = trim( $tag, ', ' );
	$desc = sprintf( __( 'Notify commenters when their comment has new reply. You can use these tags in subject and message: %s', 'hocwp-theme' ), $tag );
	echo wpautop( $desc );
}

function hocwp_theme_settings_page_discussion_field() {
	$fields = array(
		array(
			'id'    => 'avatar_size',
			'title' => __( 'Avatar Size', 'hocwp-theme' ),
			'tab'   => 'discussion',
			'args'  => array(
				'type'          => 'number',
				'callback_args' => array(
					'type' => 'number',
					'min'  => 4
				)
			)
		),
		array(
			'id'    => 'comment_system',
			'title' => __( 'Comment System', 'hocwp-theme' ),
			'tab'   => 'discussion',
			'args'  => array(
				'type'          => 'string',
				'callback'      => array( 'HOCWP_Theme_HTML_Field', 'select' ),
				'callback_args' => array(
					'options' => array(
						'default'  => _x( 'Default', 'comment system', 'hocwp-theme' ),
						'facebook' => _x( 'Facebook', 'comment system', 'hocwp-theme' )
					)
				)
			)
		)
	);

	$args     = array(
		'type'  => 'checkbox',
		'label' => __( 'Using captcha for comment form?', 'hocwp-theme' )
	);
	$field    = hocwp_theme_create_setting_field( 'captcha', __( 'Captcha', 'hocwp-theme' ), 'input', $args, 'boolean', 'discussion' );
	$fields[] = $field;

	if ( HT_extension()->is_active( 'ext/comment-notification.php' ) ) {
		$fields[] = array(
			'section' => 'comment_notification',
			'id'      => 'cn_mail_subject',
			'title'   => __( 'Mail Subject', 'hocwp-theme' ),
			'tab'     => 'discussion',
			'args'    => array(
				'type' => 'string'
			)
		);
		$fields[] = array(
			'section' => 'comment_notification',
			'id'      => 'cn_mail_message',
			'title'   => __( 'Mail Message', 'hocwp-theme' ),
			'tab'     => 'discussion',
			'args'    => array(
				'type'          => 'string',
				'callback'      => array( 'HOCWP_Theme_HTML_Field', 'textarea' ),
				'callback_args' => array(
					'class' => 'widefat'
				)
			)
		);
	}

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_discussion_settings_field', 'hocwp_theme_settings_page_discussion_field' );

function hocwp_theme_sanitize_option_discussion( $input ) {
	if ( ! is_array( $input ) ) {
		$input = array();
	}

	return $input;
}

add_filter( 'hocwp_theme_sanitize_option_discussion', 'hocwp_theme_sanitize_option_discussion' );