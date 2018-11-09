<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$tab = new HOCWP_Theme_Admin_Setting_Tab( 'discussion', __( 'Discussion', 'hocwp-theme' ), '<span class="dashicons dashicons-testimonial"></span>' );

if ( HT_extension()->is_active( 'ext/comment-notification.php' ) ) {
	$tab->add_section( 'comment_notification', array(
		'title'    => __( 'Comment Notification', 'hocwp-theme' ),
		'callback' => 'hocwp_theme_settings_page_discussion_section_comment_notification_callback'
	) );
}

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

$tab->add_field_array( array(
	'id'    => 'avatar_size',
	'title' => __( 'Avatar Size', 'hocwp-theme' ),
	'args'  => array(
		'type'          => 'number',
		'callback_args' => array(
			'type' => 'number',
			'min'  => 4
		)
	)
) );

$tab->add_field_array( array(
	'id'    => 'comment_system',
	'title' => __( 'Comment System', 'hocwp-theme' ),
	'args'  => array(
		'type'          => 'string',
		'callback'      => array( 'HOCWP_Theme_HTML_Field', 'select' ),
		'callback_args' => array(
			'options' => array(
				'default'  => _x( 'Default', 'comment system', 'hocwp-theme' ),
				'facebook' => _x( 'Facebook', 'comment system', 'hocwp-theme' ),
				'disqus'   => _x( 'Disqus', 'comment system', 'hocwp-theme' )
			)
		)
	)
) );

$comment_system = HT_Options()->get_tab( 'comment_system', '', 'discussion' );

if ( 'disqus' == $comment_system ) {
	$tab->add_field_array( new HOCWP_Theme_Admin_Setting_Field( 'disqus_shortname', __( 'Disqus Shortname', 'hocwp-theme' ), 'input', array( 'class' => 'regular-text' ), 'string', 'discussion' ) );
}

$args = array(
	'type'  => 'checkbox',
	'label' => __( 'Using captcha for comment form?', 'hocwp-theme' )
);

$tab->add_field( 'captcha', __( 'Captcha', 'hocwp-theme' ), 'input', $args, 'boolean' );

if ( HT_extension()->is_active( 'ext/comment-notification.php' ) ) {
	$tab->add_field_array( array(
		'section' => 'comment_notification',
		'id'      => 'cn_mail_subject',
		'title'   => __( 'Mail Subject', 'hocwp-theme' ),
		'args'    => array(
			'type' => 'string'
		)
	) );

	$tab->add_field_array( array(
		'section' => 'comment_notification',
		'id'      => 'cn_mail_message',
		'title'   => __( 'Mail Message', 'hocwp-theme' ),
		'args'    => array(
			'type'          => 'string',
			'callback'      => array( 'HOCWP_Theme_HTML_Field', 'textarea' ),
			'callback_args' => array(
				'class' => 'widefat'
			)
		)
	) );
}