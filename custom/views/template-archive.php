<?php
do_action( 'hocwp_theme_content_area_before' );
HT_Util()->breadcrumb();
do_action( 'hocwp_theme_article_before' );

if ( have_posts() ) {
	$title = get_the_archive_title();

	if ( is_category() ) {
		$title = single_cat_title( '', false );
	} elseif ( is_tag() ) {
		$title = single_tag_title( '', false );
	} elseif ( is_search() ) {
		$title = sprintf( __( 'Search results for "%s"', 'hocwp-theme' ), get_search_query() );
	}

	HT()->wrap_text( $title, '<h1 class="post-title text-center">', '</h1>', true );

	if ( is_category() ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term && ! empty( $term->description ) ) {
			?>
			<div class="term-description">
				<?php echo wpautop( $term->description ); ?>
			</div>
			<?php
		}
	}
	?>
	<div class="loop-posts list">
		<?php
		while ( have_posts() ) {
			the_post();
			hocwp_theme_load_custom_loop( 'post' );
		}
		?>
	</div>
	<?php
	HT_Util()->pagination();
} else {
	hocwp_theme_load_content_none();
}

do_action( 'hocwp_theme_content_area_after' );
get_sidebar();