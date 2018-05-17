<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( post_password_required() ) {
	return;
}

hocwp_theme_html_tag( 'div', 'comments_area', 'class="comments-area" id="comments"' );

if ( have_comments() ) {
	?>
	<h2 class="comments-title">
		<?php
		$comment_count = get_comments_number();
		if ( 1 === $comment_count ) {
			printf( __( 'One thought on &ldquo;%1$s&rdquo;', 'hocwp-theme' ), '<span>' . get_the_title() . '</span>' );
		} else {
			printf( __( '%1$s thoughts on &ldquo;%2$s&rdquo;', 'hocwp-theme' ), number_format_i18n( $comment_count ), '<span>' . get_the_title() . '</span>' );
		}
		?>
	</h2>
	<?php the_comments_navigation(); ?>
	<ol class="comment-list">
		<?php
		wp_list_comments( array(
			'style'      => 'ol',
			'short_ping' => true
		) );
		?>
	</ol>
	<?php
	the_comments_navigation();

	if ( ! comments_open() ) {
		?>
		<p class="no-comments"><?php _e( 'Comments are closed.', 'hocwp-theme' ); ?></p>
		<?php
	}
} else {
	HOCWP_Theme_Utility::wrap_text( '<h3>', __( 'No comments.', 'hocwp-theme' ), '</h3>' );
	HOCWP_Theme_Utility::wrap_text( '<p>', __( 'You can be the first one to leave a comment.', 'hocwp-theme' ), '</p>' );
}

if ( comments_open() ) {
	comment_form();
}

hocwp_theme_html_tag_close( 'div' );