<?php
/*
 * Name: Comment Notification
 * Description: Notify for commenters when his comment has new reply.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$load = apply_filters( 'hocwp_theme_load_extension_comment_notification', HT_Extension()->is_active( __FILE__ ) );
if ( ! $load ) {
	return;
}

function hocwp_theme_comment_notification_transition_comment_status_action( $new_status, $old_status, $comment ) {
	if ( 'approved' == $new_status ) {
		hocwp_theme_comment_reply_notification( $comment );
	}
}

add_action( 'transition_comment_status', 'hocwp_theme_comment_notification_transition_comment_status_action', 10, 3 );

function hocwp_theme_comment_notification_wp_insert_comment_action( $id, $comment ) {
	hocwp_theme_comment_reply_notification( $comment );
}

add_action( 'wp_insert_comment', 'hocwp_theme_comment_notification_wp_insert_comment_action', 10, 2 );

function hocwp_theme_notify_comment_tags() {
	$tags = array(
		'[POST_TITLE]',
		'[COMMENT_LINK]',
		'[COMMENT_AUTHOR]',
		'[COMMENT_CONTENT]'
	);

	return $tags;
}

function hocwp_theme_comment_reply_notification( $comment ) {
	if ( 1 == $comment->comment_approved ) {
		if ( HOCWP_Theme::is_positive_number( $comment->comment_parent ) ) {
			$parent = get_comment( $comment->comment_parent );
			if ( is_email( $parent->comment_author_email ) && $parent->comment_author_email != $comment->comment_author_email ) {
				global $hocwp_theme;
				$options = $hocwp_theme->options['discussion'];
				$obj     = get_post( $parent->comment_post_ID );
				$subject = isset( $options['cn_mail_subject'] ) ? $options['cn_mail_subject'] : '';
				$tags    = hocwp_theme_notify_comment_tags();
				$replace = array(
					$obj->post_title,
					get_comment_link( $comment ),
					$parent->comment_author,
					$comment->comment_content
				);
				if ( empty( $subject ) ) {
					$subject = sprintf( __( 'Your comment on "%s" has new reply', 'hocwp-theme' ), $obj->post_title );
				} else {
					$subject = str_replace( $tags, $replace, $subject );
				}
				$message = isset( $options['cn_mail_message'] ) ? $options['cn_mail_message'] : '';
				if ( empty( $message ) ) {
					$message = wpautop( sprintf( __( 'Hello %s,', 'hocwp-theme' ), $parent->comment_author ) );
					$message .= wpautop( sprintf( __( 'Your comment on "%s" has new reply. You can see more details by clicking the link below:', 'hocwp-theme' ), $obj->post_title ) );
					$message .= wpautop( get_comment_link( $comment ) );
					$message .= wpautop( __( 'If the link above does not work, you can also copy and paste into the address bar of the browser to access.', 'hocwp-theme' ) );
				} else {
					$message = str_replace( $tags, $replace, $message );
					$message = wpautop( $message );
				}
				$message = wpautop( $message );
				$subject = strip_tags( $subject );
				$headers = array( 'Content-Type: text/html; charset=UTF-8' );
				wp_mail( $parent->comment_author_email, $subject, $message, $headers );
				unset( $obj, $subject );
			}
			unset( $parent );
		}
	}
}