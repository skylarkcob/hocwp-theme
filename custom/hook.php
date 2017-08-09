<?php
function hocwp_theme_custom_pre_get_posts( WP_Query $query ) {
	if ( $query->is_main_query() ) {
		if ( is_search() || is_archive() || is_front_page() ) {
			$post_types   = get_post_types( array( '_builtin' => false, 'public' => true ) );
			$post_types   = array_values( $post_types );
			$post_types[] = 'post';
			$post_types   = array_unique( $post_types );
			$query->set( 'post_type', $post_types );
			$tab = get_query_var( 'tab' );
			switch ( $tab ) {
				case 'featured':
					break;
				case 'modified':
					$query->set( 'orderby', 'modified' );
					$query->set( 'order', 'DESC' );
					break;
				case 'comment':
					global $wpdb;
					$sql  = "SELECT * FROM {$wpdb->prefix}posts ON ({$wpdb->prefix}posts.ID = {$wpdb->prefix}commnets.comment_post_ID) WHERE {$wpdb->prefix}comments.comment_approved=1 ORDER BY {$wpdb->prefix}comments.comment_date";
					$test = $wpdb->query( $sql );
					hocwp_theme_debug( $test );
					break;
			}
		}
	}
}

if ( ! is_admin() ) {
	add_action( 'pre_get_posts', 'hocwp_theme_custom_pre_get_posts' );
}

function hocwp_theme_custom_query_vars( $vars ) {
	$vars[] = 'tab';

	return $vars;
}

add_filter( 'query_vars', 'hocwp_theme_custom_query_vars' );

function hocwp_theme_custom_after_setup_theme() {

}

add_action( 'after_setup_theme', 'hocwp_theme_custom_after_setup_theme' );

function hocwp_theme_custom_post_thumbnail_html( $html, $post_id, $post_thumbnail_id ) {
	if ( ! HOCWP_Theme::is_positive_number( $post_thumbnail_id ) ) {
		$title = get_the_title( $post_id );
		$char  = substr( $title, 0, 1 );
		$html  = '<span class="title-thumbnail">' . $char . '</span>';
	}

	return $html;
}

add_filter( 'post_thumbnail_html', 'hocwp_theme_custom_post_thumbnail_html', 10, 3 );

function hocwp_theme_custom_admin_notices() {

}

add_action( 'admin_notices', 'hocwp_theme_custom_admin_notices' );

function hocwp_theme_custom_wp_footer_action() {

}

add_action( 'wp_footer', 'hocwp_theme_custom_wp_footer_action' );

function hocwp_theme_custom_comments_popup_link_class_filter( $class ) {
	$count = get_comments_number();
	if ( is_numeric( $count ) && $count > 50 ) {
		$class .= ' supernova';
	}

	return $class;
}

add_filter( 'hocwp_theme_comments_popup_link_class', 'hocwp_theme_custom_comments_popup_link_class_filter' );

function hocwp_theme_custom_sidebar_widgets_filter( $sidebars_widgets ) {
	if ( isset( $sidebars_widgets['sidebar-1'] ) ) {
		shuffle( $sidebars_widgets['sidebar-1'] );
	}

	return $sidebars_widgets;
}

add_filter( 'sidebars_widgets', 'hocwp_theme_custom_sidebar_widgets_filter' );