<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_settings_page_reading_tab( $tabs ) {
	$tabs['reading'] = array(
		'text' => __( 'Reading', 'hocwp-theme' ),
		'icon' => '<span class="dashicons dashicons-visibility"></span>'
	);

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_reading_tab' );

if ( 'reading' != hocwp_theme_object()->option->tab ) {
	return;
}

function hocwp_theme_settings_page_reading_section() {
	$sections = array();

	$sections['optimize'] = array(
		'tab'   => 'reading',
		'id'    => 'optimize',
		'title' => __( 'Site Optimize', 'hocwp-theme' )
	);

	$sections['back_top_section'] = array(
		'tab'   => 'reading',
		'id'    => 'back_top_section',
		'title' => __( 'Back To Top Button', 'hocwp-theme' )
	);

	$sections['theme_customization'] = array(
		'tab'   => 'reading',
		'id'    => 'theme_customization',
		'title' => __( 'Theme Customization', 'hocwp-theme' )
	);

	return $sections;
}

add_filter( 'hocwp_theme_settings_page_reading_settings_section', 'hocwp_theme_settings_page_reading_section' );

function hocwp_theme_settings_page_reading_field() {
	global $wp_version;

	$fields = array();

	$field    = hocwp_theme_create_setting_field( 'theme_color', __( 'Theme Color', 'hocwp-theme' ), 'color_picker', '', 'string', 'reading', 'theme_customization' );
	$fields[] = $field;

	$args = array(
		'class' => 'medium-text',
		'type'  => 'checkbox',
		'label' => __( 'Show loading effect while page rendering.', 'hocwp-theme' )
	);

	$field    = hocwp_theme_create_setting_field( 'loading', __( 'Loading', 'hocwp-theme' ), '', $args, 'boolean', 'reading' );
	$fields[] = $field;

	$args = array(
		'class' => 'medium-text',
		'type'  => 'checkbox',
		'label' => __( 'Show popup to notify visitor website is using cookie.', 'hocwp-theme' )
	);

	$field    = hocwp_theme_create_setting_field( 'cookie_alert', __( 'Cookie Alert', 'hocwp-theme' ), '', $args, 'boolean', 'reading' );
	$fields[] = $field;

	$types = array(
		''        => __( '-- Choose breadcrumb type --', 'hocwp-theme' ),
		'default' => __( 'Default', 'hocwp-theme' )
	);

	if ( ht_util()->yoast_seo_exists() ) {
		$types['yoast_seo'] = 'Yoast SEO';
	}

	if ( function_exists( 'bcn_display' ) ) {
		$types['navxt'] = 'Breadcrumb NavXT';
	}

	if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
		$types['rank_math'] = 'Rank Math';
	}

	$args = array(
		'options' => $types
	);

	$field    = hocwp_theme_create_setting_field( 'breadcrumb_type', __( 'Breadcrumb Type', 'hocwp-theme' ), 'select', $args, 'string', 'reading', 'theme_customization' );
	$fields[] = $field;

	$types = array(
		''         => __( '-- Choose menu toggle icon --', 'hocwp-theme' ),
		'svg'      => __( 'SVG icon', 'hocwp-theme' ),
		'bars'     => __( 'Line bars', 'hocwp-theme' ),
		'burger'   => __( 'Line burger', 'hocwp-theme' ),
		'burger-3' => __( 'Line burger 3', 'hocwp-theme' )
	);

	$args = array(
		'options' => $types
	);

	$field    = hocwp_theme_create_setting_field( 'menu_toggle_icon', __( 'Menu Toggle Icon', 'hocwp-theme' ), 'select', $args, 'string', 'reading', 'theme_customization' );
	$fields[] = $field;

	$field    = hocwp_theme_create_setting_field( 'blog_page', __( 'Blog Page', 'hocwp-theme' ), 'select_page', '', 'positive_number', 'reading' );
	$fields[] = $field;

	$field    = hocwp_theme_create_setting_field( 'page_404', __( 'Not Found Page', 'hocwp-theme' ), 'select_page', '', 'positive_number', 'reading' );
	$fields[] = $field;

	$lists = get_post_types( array( 'public' => true ) );

	$args = array(
		'options'  => $lists,
		'multiple' => 'multiple'
	);

	$field    = hocwp_theme_create_setting_field( 'post_types', __( 'Query Post Types', 'hocwp-theme' ), 'chosen', $args, 'array', 'reading' );
	$fields[] = $field;

	$field    = hocwp_theme_create_setting_field( 'search_post_types', __( 'Search Post Types', 'hocwp-theme' ), 'chosen', $args, 'array', 'reading' );
	$fields[] = $field;

	$lists = array(
		'none'          => _x( 'No order', 'orderby', 'hocwp-theme' ),
		'ID'            => _x( 'Post ID', 'orderby', 'hocwp-theme' ),
		'author'        => _x( 'Post author', 'orderby', 'hocwp-theme' ),
		'title'         => _x( 'Post title', 'orderby', 'hocwp-theme' ),
		'name'          => _x( 'Post name', 'orderby', 'hocwp-theme' ),
		'type'          => _x( 'Post type', 'orderby', 'hocwp-theme' ),
		'date'          => _x( 'Post date', 'orderby', 'hocwp-theme' ),
		'modified'      => _x( 'Last modified date', 'orderby', 'hocwp-theme' ),
		'parent'        => _x( 'Post parent', 'orderby', 'hocwp-theme' ),
		'rand'          => _x( 'Random', 'orderby', 'hocwp-theme' ),
		'comment_count' => _x( 'Number of comments', 'orderby', 'hocwp-theme' ),
		'relevance'     => _x( 'Search terms', 'orderby', 'hocwp-theme' ),
		'menu_order'    => _x( 'Post order', 'orderby', 'hocwp-theme' )
	);

	$args = array(
		'options'  => $lists,
		'multiple' => 'multiple'
	);

	$field    = hocwp_theme_create_setting_field( 'orderby', __( 'Query Orderby', 'hocwp-theme' ), 'chosen', $args, 'array', 'reading' );
	$fields[] = $field;

	$args = array(
		'class' => 'medium-text'
	);

	$field    = hocwp_theme_create_setting_field( 'excerpt_more', __( 'Excerpt More', 'hocwp-theme' ), '', $args, 'string', 'reading' );
	$fields[] = $field;

	$args = array(
		'class' => 'medium-text',
		'type'  => 'number'
	);

	$field    = hocwp_theme_create_setting_field( 'excerpt_length', __( 'Excerpt Length', 'hocwp-theme' ), '', $args, 'positive_integer', 'reading' );
	$fields[] = $field;

	$field    = hocwp_theme_create_setting_field( 'excerpt_length_mobile', __( 'Excerpt Length Mobile', 'hocwp-theme' ), '', $args, 'positive_integer', 'reading' );
	$fields[] = $field;

	$field    = hocwp_theme_create_setting_field( 'default_content', __( 'Default Post Content', 'hocwp-theme' ), 'editor', array(), 'html', 'reading' );
	$fields[] = $field;

	$args = array(
		'class' => 'medium-text',
		'type'  => 'checkbox',
		'label' => __( 'Make last widget on sidebar sticky.', 'hocwp-theme' )
	);

	$field    = hocwp_theme_create_setting_field( 'sticky_last_widget', __( 'Sticky Last Widget', 'hocwp-theme' ), '', $args, 'boolean', 'reading' );
	$fields[] = $field;

	$args['label'] = __( 'Use different sidebar on each theme layout page.', 'hocwp-theme' );

	$field    = hocwp_theme_create_setting_field( 'variable_sidebar', __( 'Variable Sidebar', 'hocwp-theme' ), '', $args, 'boolean', 'reading' );
	$fields[] = $field;

	$args['label'] = __( 'Show float post nav links on single page.', 'hocwp-theme' );

	$field    = hocwp_theme_create_setting_field( 'float_post_nav', __( 'Float Post Nav', 'hocwp-theme' ), '', $args, 'boolean', 'reading' );
	$fields[] = $field;

	$args = array(
		'class'   => 'regular-text',
		'options' => array(
			''      => __( 'Default', 'hocwp-theme' ),
			'right' => _x( 'Right', 'sidebar position', 'hocwp-theme' ),
			'left'  => _x( 'Left', 'sidebar position', 'hocwp-theme' )
		)
	);

	$field    = hocwp_theme_create_setting_field( 'sidebar_position', __( 'Sidebar Position', 'hocwp-theme' ), 'select', $args, 'string', 'reading' );
	$fields[] = $field;

	$args = array(
		'type'  => 'checkbox',
		'label' => __( 'Add random end point to url for displaying random post?', 'hocwp-theme' )
	);

	$field    = hocwp_theme_create_setting_field( 'random', __( 'Random', 'hocwp-theme' ), '', $args, 'boolean', 'reading' );
	$fields[] = $field;

	$args['label'] = __( 'Redirect user to homepage if the search term is empty.', 'hocwp-theme' );

	$field    = hocwp_theme_create_setting_field( 'redirect_empty_search', __( 'Redirect Empty Search', 'hocwp-theme' ), '', $args, 'boolean', 'reading' );
	$fields[] = $field;

	if ( version_compare( $wp_version, '5.5', '>=' ) ) {
		$args = array(
			'type'  => 'checkbox',
			'label' => __( 'Since WordPress version 5.5, all images will be used lazy loading, check here if you want to disable this function?', 'hocwp-theme' )
		);

		$field    = hocwp_theme_create_setting_field( 'disable_lazy_loading', __( 'Disable Lazy Loading', 'hocwp-theme' ), '', $args, 'boolean', 'reading', 'optimize' );
		$fields[] = $field;
	}

	$args = array(
		'type'  => 'checkbox',
		'label' => __( 'Use WebP images instead of regular images to reduce page load time.', 'hocwp-theme' )
	);

	$field    = hocwp_theme_create_setting_field( 'use_webp', __( 'Use WebP', 'hocwp-theme' ), '', $args, 'boolean', 'reading', 'optimize' );
	$fields[] = $field;

	$args = array(
		'class' => 'medium-text',
		'type'  => 'checkbox',
		'label' => __( 'Displays the back to top button when user scrolls down the bottom of site.', 'hocwp-theme' )
	);

	$field    = hocwp_theme_create_setting_field( 'back_to_top', __( 'Active', 'hocwp-theme' ), '', $args, 'boolean', 'reading', 'back_top_section' );
	$fields[] = $field;

	$color = ht_util()->get_theme_option( 'back_top_bg', '', 'reading' );

	$args = array(
		'background_color' => $color,
		'style'            => ht_util()->get_theme_option( 'back_top_style', '', 'reading' )
	);

	$icon = ht_options()->get_tab( 'icon', '', 'reading' );

	if ( ! ht()->is_positive_number( $icon ) ) {
		unset( $args['style'] );
	}

	$field    = hocwp_theme_create_setting_field( 'back_top_icon', __( 'Icon', 'hocwp-theme' ), 'media_upload', $args, 'positive_number', 'reading', 'back_top_section' );
	$fields[] = $field;

	$field    = hocwp_theme_create_setting_field( 'back_top_icon_html', __( 'Icon HTML', 'hocwp-theme' ), 'input', $args, 'html', 'reading', 'back_top_section' );
	$fields[] = $field;

	$field    = hocwp_theme_create_setting_field( 'back_top_bg', __( 'Background Color', 'hocwp-theme' ), 'color_picker', '', 'string', 'reading', 'back_top_section' );
	$fields[] = $field;

	$field    = hocwp_theme_create_setting_field( 'back_top_style', __( 'Style Attribute', 'hocwp-theme' ), 'input', '', 'string', 'reading', 'back_top_section' );
	$fields[] = $field;

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_reading_settings_field', 'hocwp_theme_settings_page_reading_field' );

function hocwp_theme_admin_setting_page_reading_scripts() {
	ht_enqueue()->media_upload();
	ht_enqueue()->color_picker();
}

add_action( 'hocwp_theme_admin_setting_page_reading_scripts', 'hocwp_theme_admin_setting_page_reading_scripts' );