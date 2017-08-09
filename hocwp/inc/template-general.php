<?php
function hocwp_theme_module_site_header() {
	hocwp_theme_load_custom_module( 'module-site-header' );
}

add_action( 'hocwp_theme_module_site_header', 'hocwp_theme_module_site_header' );

function hocwp_theme_module_site_footer() {
	hocwp_theme_load_custom_module( 'module-site-footer' );
}

add_action( 'hocwp_theme_module_site_footer', 'hocwp_theme_module_site_footer' );

function hocwp_theme_template_index() {
	hocwp_theme_load_custom_template( 'template-index' );
}

add_action( 'hocwp_theme_template_index', 'hocwp_theme_template_index' );

function hocwp_theme_template_404() {
	hocwp_theme_load_custom_template( 'template-404' );
}

add_action( 'hocwp_theme_template_404', 'hocwp_theme_template_404' );

function hocwp_theme_template_archive() {
	hocwp_theme_load_custom_template( 'template-archive' );
}

add_action( 'hocwp_theme_template_archive', 'hocwp_theme_template_archive' );

function hocwp_theme_module_comments_area() {
	hocwp_theme_load_views( 'module-comments-area' );
}

add_action( 'hocwp_theme_module_comments_area', 'hocwp_theme_module_comments_area' );

function hocwp_theme_template_page() {
	hocwp_theme_load_custom_template( 'template-page' );
}

add_action( 'hocwp_theme_template_page', 'hocwp_theme_template_page' );

function hocwp_theme_template_search() {
	hocwp_theme_load_custom_template( 'template-search' );
}

add_action( 'hocwp_theme_template_search', 'hocwp_theme_template_search' );

function hocwp_theme_module_sidebar() {
	hocwp_theme_load_views( 'module-sidebar' );
}

add_action( 'hocwp_theme_module_sidebar', 'hocwp_theme_module_sidebar' );

function hocwp_theme_template_single() {
	hocwp_theme_load_custom_template( 'template-single' );
}

add_action( 'hocwp_theme_template_single', 'hocwp_theme_template_single' );

function hocwp_theme_content_area_before() {
	?>
	<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
	<?php
}

add_action( 'hocwp_theme_content_area_before', 'hocwp_theme_content_area_before', 3 );

function hocwp_theme_content_area_after() {
	?>
	</main><!-- #main -->
	</div><!-- #primary -->
	<?php
}

add_action( 'hocwp_theme_content_area_after', 'hocwp_theme_content_area_after', 3 );

function hocwp_theme_article_header_before() {
	echo '<header class="entry-header">';
}

add_action( 'hocwp_theme_article_header_before', 'hocwp_theme_article_header_before' );

function hocwp_theme_article_header_after() {
	echo '</header><!-- .entry-header -->';
}

add_action( 'hocwp_theme_article_header_after', 'hocwp_theme_article_header_after' );

function hocwp_theme_replace_search_submit_button( $form = '', $icon = '' ) {
	if ( empty( $form ) ) {
		$form = get_search_form( false );
	}
	ob_start();
	?>
	<button type="submit" class="btn js-search-submit search-submit">
		<?php
		if ( empty( $icon ) ) {
			HOCWP_Theme_SVG_Icon::search();
		} else {
			echo $icon;
		}
		?>
	</button>
	<?php
	$button = ob_get_clean();
	$search = '</label>';
	if ( false !== ( $pos = strpos( $form, $search ) ) ) {
		$form = substr( $form, 0, $pos + strlen( $search ) );
	} else {

	}
	$form .= "\n";
	$form .= $button;
	$form .= '</form>';

	return $form;
}

function hocwp_theme_get_the_archive_title_filter( $title ) {
	if ( is_search() ) {
		$title = sprintf( __( 'Search Results: %s', 'hocwp-theme' ), get_search_query() );
	}

	return $title;
}

add_filter( 'get_the_archive_title', 'hocwp_theme_get_the_archive_title_filter' );

function hocwp_theme_adjacent_post_link_filter( $output, $format, $link, $post, $adjacent ) {
	if ( $post instanceof WP_Post ) {
		$attr = '';
		switch ( $adjacent ) {
			case 'next':
				$attr = sprintf( 'data-text="%s"', __( 'Next', 'hocwp-theme' ) );
				break;
			case 'previous':
				$attr = sprintf( 'data-text="%s"', __( 'Previous', 'hocwp-theme' ) );
				break;
		}
		$output = HOCWP_Theme::add_html_attribute( 'div', $output, $attr );
	}

	return $output;
}

add_filter( 'next_post_link', 'hocwp_theme_adjacent_post_link_filter', 10, 5 );
add_filter( 'previous_post_link', 'hocwp_theme_adjacent_post_link_filter', 10, 5 );