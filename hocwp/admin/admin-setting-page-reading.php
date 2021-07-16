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

global $hocwp_theme;

if ( 'reading' != $hocwp_theme->option->tab ) {
	return;
}

function hocwp_theme_settings_page_reading_section() {
	$sections = array();

	if ( hocwp_theme_is_shop_site() ) {
		$sections['shop_section'] = array(
			'tab'   => 'reading',
			'id'    => 'shop_section',
			'title' => __( 'Shop Settings', 'hocwp-theme' )
		);
	}

	$sections['back_top_section'] = array(
		'tab'   => 'reading',
		'id'    => 'back_top_section',
		'title' => __( 'Back To Top Button', 'hocwp-theme' )
	);

	return $sections;
}

add_filter( 'hocwp_theme_settings_page_reading_settings_section', 'hocwp_theme_settings_page_reading_section' );

function hocwp_theme_settings_page_reading_field() {
	global $wp_version;

	$fields = array();

	$field    = hocwp_theme_create_setting_field( 'theme_color', __( 'Theme Color', 'hocwp-theme' ), 'color_picker', '', 'string', 'reading' );
	$fields[] = $field;

	$args = array(
		'class' => 'medium-text',
		'type'  => 'checkbox',
		'label' => __( 'Show popup to notify visitor website is using cookie.', 'hocwp-theme' )
	);

	$field    = hocwp_theme_create_setting_field( 'cookie_alert', __( 'Cookie Alert', 'hocwp-theme' ), '', $args, 'boolean', 'reading' );
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

	$args = array(
		'class' => 'medium-text',
		'type'  => 'checkbox',
		'label' => __( 'Make last widget on sidebar sticky.', 'hocwp-theme' )
	);

	$field    = hocwp_theme_create_setting_field( 'sticky_last_widget', __( 'Sticky Last Widget', 'hocwp-theme' ), '', $args, 'boolean', 'reading' );
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

		$field    = hocwp_theme_create_setting_field( 'disable_lazy_loading', __( 'Disable Lazy Loading', 'hocwp-theme' ), '', $args, 'boolean', 'reading' );
		$fields[] = $field;
	}

	if ( hocwp_theme_is_shop_site() ) {
		$fields[] = array(
			'tab'     => 'reading',
			'section' => 'shop_section',
			'id'      => 'products_per_page',
			'title'   => __( 'Products Per Page', 'hocwp-theme' ),
			'args'    => array(
				'label_for'     => true,
				'default'       => $GLOBALS['hocwp_theme']->defaults['posts_per_page'],
				'callback_args' => array(
					'class' => 'small-text',
					'type'  => 'number'
				)
			)
		);
	}

	$args = array(
		'class' => 'medium-text',
		'type'  => 'checkbox',
		'label' => __( 'Displays the back to top button when user scrolls down the bottom of site.', 'hocwp-theme' )
	);

	$field    = hocwp_theme_create_setting_field( 'back_to_top', __( 'Active', 'hocwp-theme' ), '', $args, 'boolean', 'reading', 'back_top_section' );
	$fields[] = $field;

	$color = HT_Util()->get_theme_option( 'back_top_bg', '', 'reading' );

	$args = array(
		'background_color' => $color,
		'style'            => HT_Util()->get_theme_option( 'back_top_style', '', 'reading' )
	);

	$icon = HT_Options()->get_tab( 'icon', '', 'reading' );

	if ( ! HT()->is_positive_number( $icon ) ) {
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
	HT_Enqueue()->media_upload();
	HT_Enqueue()->color_picker();
}

add_action( 'hocwp_theme_admin_setting_page_reading_scripts', 'hocwp_theme_admin_setting_page_reading_scripts' );