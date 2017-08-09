<?php
do_action( 'hocwp_theme_content_area_before' );
while ( have_posts() ) {
	the_post();
	do_action( 'hocwp_theme_article_before' );
	?>
	<header class="entry-header">
		<?php
		if ( is_singular() ) :
			the_title( '<h1 class="entry-title">', '</h1>' );
		else :
			the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
		endif;
		?>
		<div class="entry-meta">
			<?php hocwp_theme_posted_on(); ?>
		</div>
		<!-- .entry-meta -->
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php
		the_content( sprintf(
			wp_kses(
			/* translators: %s: Name of current post. */
				__( 'Continue reading %s <span class="meta-nav">&rarr;</span>', 'hocwp-theme' ),
				array(
					'span' => array(
						'class' => array(),
					),
				)
			),
			the_title( '<span class="screen-reader-text">"', '"</span>', false )
		) );

		wp_link_pages( array(
			'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'hocwp-theme' ),
			'after'  => '</div>',
		) );
		?>
	</div><!-- .entry-content -->

	<footer class="entry-footer">
		<?php hocwp_theme_load_custom_module( 'post-terms-buttons' ); ?>
	</footer><!-- .entry-footer -->
	<?php
	do_action( 'hocwp_theme_article_after' );
	the_post_navigation();
	if ( hocwp_theme_comments_open() ) {
		comments_template();
	}
}
do_action( 'hocwp_theme_content_area_after' );
get_sidebar();