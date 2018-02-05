<?php
do_action( 'hocwp_theme_article_before' );
echo '<div class="inner">';
the_post_thumbnail( 'thumbnail', array( 'post_link' => true ) );
do_action( 'hocwp_theme_the_title' );
echo '</div>';
do_action( 'hocwp_theme_article_after' );