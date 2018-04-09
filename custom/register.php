<?php
/**
 * Register sidebar and widget for using in theme.
 */
function hocwp_theme_custom_widgets_init() {

}

add_action( 'widgets_init', 'hocwp_theme_custom_widgets_init' );

function hocwp_theme_custom_register_post_type_and_taxonomy() {
	$args = array(
		'name'          => __( 'Slider Manager', 'hocwp-theme' ),
		'singular_name' => __( 'Slider', 'hocwp-theme' ),
		'supports'      => array( 'title', 'thumbnail' ),
		'public'        => false,
		'show_in_menu'  => true,
		'show_ui'       => true,
		'menu_position' => 81
	);
	$args = HT_Util()->post_type_args( $args );

	register_post_type( 'slider', $args );

	$args = array(
		'name'              => __( 'Slider ID', 'hocwp-theme' ),
		'public'            => false,
		'show_in_menu'      => true,
		'show_ui'           => true,
		'show_admin_column' => true
	);
	$args = HT_Util()->taxonomy_args( $args );

	register_taxonomy( 'slider_id_name', 'slider', $args );

	$args = array(
		'name'          => __( 'News Manager', 'hocwp-theme' ),
		'singular_name' => __( 'News', 'hocwp-theme' ),
		'supports'      => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'post-formats' ),
		'public'        => true,
		'show_in_menu'  => true,
		'show_ui'       => true,
		'menu_position' => 81,
		'taxonomies'    => array( 'post_tag' )
	);
	$args = HT_Util()->post_type_args( $args );

	register_post_type( 'news', $args );

	$args = array(
		'name'              => __( 'Filters', 'hocwp-theme' ),
		'singular_name'     => __( 'Filter', 'hocwp-theme' ),
		'public'            => true,
		'show_in_menu'      => true,
		'show_ui'           => true,
		'show_admin_column' => false,
		'rewrite'           => array(
			'slug' => 'filter'
		)
	);
	$args = HT_Util()->taxonomy_args( $args );

	register_taxonomy( 'post_filter', array( 'post', 'news' ), $args );
}

add_action( 'init', 'hocwp_theme_custom_register_post_type_and_taxonomy' );