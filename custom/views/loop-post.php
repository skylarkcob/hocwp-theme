<?php
$post_id = get_the_ID();
do_action( 'hocwp_theme_article_before' );
do_action( 'hocwp_theme_article_header_before' );
?>
	<div class="clear">
		<div class="thumb-box alignleft">
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'thumbnail' );
			} else {
				$color = get_post_meta( $post_id, 'color', true );
				if ( empty( $color ) ) {
					$color = HOCWP_Theme::random_color_hex();
					update_post_meta( $post_id, 'color', $color );
				}
				?>
				<div class="color" style="background-color: <?php echo $color; ?>">
					<span><?php echo mb_substr( get_the_title(), 0, 1 ); ?></span>
				</div>
				<?php
			}
			?>
		</div>
		<div class="summary alignleft">
			<div class="alignleft">
				<?php
				the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
				hocwp_theme_load_custom_module( 'post-terms-buttons' );
				?>
			</div>
			<div class="alignright">
				<?php hocwp_theme_comments_popup_link(); ?>
			</div>
		</div>
	</div>
<?php
do_action( 'hocwp_theme_article_header_after' );
do_action( 'hocwp_theme_article_after' );