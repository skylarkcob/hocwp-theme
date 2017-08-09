<?php
function hocwp_theme_custom_post_tabs() {
	$defaults = array(
		'recent'   => __( 'Recent Posts', 'hocwp-theme' ),
		'featured' => __( 'Featured Posts', 'hocwp-theme' ),
		'modified' => __( 'Recent Modified Posts', 'hocwp-theme' ),
		'comment'  => __( 'Recent Commented Posts', 'hocwp-theme' )
	);

	return apply_filters( 'hocwp_theme_custom_post_tabs', $defaults );
}