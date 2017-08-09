<?php
function hocwp_theme_article_before() {
	echo '<article ' . 'class="' . join( ' ', get_post_class() ) . '">';
}

add_action( 'hocwp_theme_article_before', 'hocwp_theme_article_before' );

function hocwp_theme_article_after() {
	echo '</article>';
}

add_action( 'hocwp_theme_article_after', 'hocwp_theme_article_after' );

function hocwp_theme_comments_open() {
	return ( ! post_password_required() && ( comments_open() || get_comments_number() ) );
}

function hocwp_theme_comments_popup_link() {
	if ( ! is_single() && hocwp_theme_comments_open() ) {
		$class = apply_filters( 'hocwp_theme_comments_popup_link_class', 'comments-link' );
		$class = explode( ' ', $class );
		$class = array_map( 'sanitize_html_class', $class );
		echo '<span class="' . implode( ' ', $class ) . '">';
		comments_popup_link(
			sprintf(
				wp_kses(
					__( '0 comment<span class="screen-reader-text"> on %s</span>', 'hocwp-theme' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				get_the_title()
			)
		);
		echo '</span>';
		unset( $class );
	}
}