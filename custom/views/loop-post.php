<?php
do_action( 'hocwp_theme_article_before' );
echo '<div class="inner">';
the_post_thumbnail( 'thumbnail', array( 'post_link' => true ) );
do_action( 'hocwp_theme_the_title' );
$views = get_post_meta( get_the_ID(), 'views', true );
$views = absint( $views );
?>
	<div class="post-meta">
		<div class="post-date">
			<i class="fa fa-clock-o"></i><?php echo get_the_date(); ?>
		</div>
		<div class="post-views">
			<i class="fa fa-eye"></i><?php echo number_format( $views ); ?>
		</div>
	</div>
<?php
do_action( 'hocwp_theme_the_excerpt' );
echo '</div>';
do_action( 'hocwp_theme_article_after' );