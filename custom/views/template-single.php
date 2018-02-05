<?php
global $hocwp_theme;
while ( have_posts() ) {
	the_post();
	do_action( 'hocwp_theme_content_area_before' );
	HT_Util()->breadcrumb();
	do_action( 'hocwp_theme_article_before' );
	$hocwp_theme->loop_data['is_single'] = true;
	do_action( 'hocwp_theme_the_title' );
	$views = get_post_meta( get_the_ID(), 'views', true );
	$views = absint( $views );
	?>
	<div class="post-meta">
		<div class="cats">
			<i class="fa fa-compress"></i><?php the_category( ', ' ); ?>
		</div>
		<div class="post-date">
			<i class="fa fa-clock-o"></i><?php the_date(); ?>
		</div>
		<div class="post-views">
			<i class="fa fa-eye"></i><?php echo number_format( $views ); ?>
		</div>
		<?php HT_Util()->addthis_toolbox(); ?>
	</div>
	<?php
	do_action( 'hocwp_theme_the_content' );
	?>
	<div class="term-links">
		<span><?php _e( 'Category:', 'hocwp-theme' ); ?></span><?php the_category( ', ' ); ?>
	</div>
	<?php
	if ( has_tag() ) {
		?>
		<div class="term-links">
			<?php the_tags( '<span>' . __( 'Tags:', 'hocwp-theme' ) . '</span>', ', ', '</span>' ); ?>
		</div>
		<?php
	}
	do_action( 'hocwp_theme_article_after' );
	$args  = array(
		'posts_per_page' => 6,
		'post_type'      => 'post'
	);
	$query = HT_Query()->related_posts( $args );
	if ( $query->have_posts() ) {
		?>
		<div class="related-posts">
			<h3><?php _e( 'Related posts', 'hocwp-theme' ); ?></h3>

			<div class="loop-posts grid">
				<?php
				while ( $query->have_posts() ) {
					$query->the_post();
					hocwp_theme_load_custom_loop( 'post-home' );
				}
				wp_reset_postdata();
				?>
			</div>
		</div>
		<?php
	}
	hocwp_theme_comments_template();
	do_action( 'hocwp_theme_content_area_after' );
	get_sidebar();
}