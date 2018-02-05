<?php
global $hocwp_theme;
while ( have_posts() ) {
	the_post();
	do_action( 'hocwp_theme_content_area_before' );
	HT_Util()->breadcrumb();
	do_action( 'hocwp_theme_article_before' );
	$hocwp_theme->loop_data['is_single'] = true;
	do_action( 'hocwp_theme_the_title' );
	do_action( 'hocwp_theme_the_content' );
	do_action( 'hocwp_theme_article_after' );
	do_action( 'hocwp_theme_content_area_after' );
	get_sidebar();
}