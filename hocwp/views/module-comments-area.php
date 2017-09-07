<?php
// You can start editing here -- including this comment!
if ( have_comments() ) : ?>
	<h2 class="comments-title">
		<?php
		$comment_count = get_comments_number();
		if ( 1 === $comment_count ) {
			printf(
			/* translators: 1: title. */
				__( 'One thought on &ldquo;%1$s&rdquo;', 'hocwp-theme' ),
				'<span>' . get_the_title() . '</span>'
			);
		} else {
			printf( // WPCS: XSS OK.
			/* translators: 1: comment count number, 2: title. */
				__( '%1$s thoughts on &ldquo;%2$s&rdquo;', 'hocwp-theme' ),
				number_format_i18n( $comment_count ),
				'<span>' . get_the_title() . '</span>'
			);
		}
		?>
	</h2><!-- .comments-title -->

	<?php the_comments_navigation(); ?>

	<ol class="comment-list">
		<?php
		wp_list_comments( array(
			'style'      => 'ol',
			'short_ping' => true,
		) );
		?>
	</ol><!-- .comment-list -->

	<?php the_comments_navigation();

	// If comments are closed and there are comments, let's leave a little note, shall we?
	if ( ! comments_open() ) : ?>
		<p class="no-comments"><?php _e( 'Comments are closed.', 'hocwp-theme' ); ?></p>
		<?php
	endif;
else:
	HOCWP_Theme_Utility::wrap_text( '<h3>', __( 'No comments.', 'hocwp-theme' ), '</h3>' );
	HOCWP_Theme_Utility::wrap_text( '<p>', __( 'You can be the first one to leave a comment.', 'hocwp-theme' ), '</p>' );
endif; // Check for have_comments().

comment_form();