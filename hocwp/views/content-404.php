<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<div class="section page-404">
	<div class="container clearfix">
		<?php do_action( 'hocwp_theme_content_area_before' ); ?>
		<section class="error-404 not-found">
			<?php HT_Frontend()->content_404(); ?>
		</section>
		<!-- .error-404 -->
		<?php
		do_action( 'hocwp_theme_content_area_after' );
		get_sidebar();
		?>
	</div>
</div>