<?php
do_action( 'hocwp_theme_content_area_before' );
if ( have_posts() ) {
	$sortable = hocwp_theme_get_option( 'sortable', '', 'home' );
	if ( ! empty( $sortable ) ) {
		$sortables = json_decode( $sortable );
		foreach ( $sortables as $sortable ) {
			$term = get_category( $sortable->id );
			if ( $term instanceof WP_Term ) {
				$args  = array(
					'cat'            => $term->term_id,
					'post_type'      => 'post',
					'posts_per_page' => HT_Util()->get_posts_per_page( true )
				);
				$query = new WP_Query( $args );
				if ( $query->have_posts() ) {
					?>
					<div class="module cat-posts">
						<div class="module-header">
							<h2>
								<a href="<?php echo get_category_link( $term ); ?>"><?php echo $term->name; ?></a>
							</h2>
						</div>
						<div class="module-body">
							<div class="loop-posts">
								<?php
								while ( $query->have_posts() ) {
									$query->the_post();
									hocwp_theme_load_custom_loop( 'post-home' );
								}
								wp_reset_postdata();
								?>
							</div>
						</div>
					</div>
					<?php
				}
			}
		}
	} else {
		?>
		<div class="loop-posts">
			<?php
			while ( have_posts() ) {
				the_post();
				hocwp_theme_load_custom_loop( 'post-home' );
			}
			?>
		</div>
		<?php
		HT_Util()->pagination();
	}
} else {
	hocwp_theme_load_content_none();
}
do_action( 'hocwp_theme_content_area_after' );
get_sidebar();
