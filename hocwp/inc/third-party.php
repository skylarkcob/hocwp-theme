<?php
defined( 'ABSPATH' ) || exit;

class HOCWP_Theme_Third_Party {
	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ) );
		add_action( 'widgets_init', array( $this, 'widgets_init' ) );
	}

	public function wp_enqueue_scripts() {
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}

	public function widgets_init() {
		register_sidebar(
			array(
				'name'          => esc_html__( 'Sidebar', 'hocwp-theme' ),
				'id'            => 'sidebar-1',
				'description'   => esc_html__( 'Add widgets here.', 'hocwp-theme' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>'
			)
		);
	}

	public function after_setup_theme() {
		load_theme_textdomain( 'hocwp-theme', HOCWP_THEME_CUSTOM_PATH . '/languages' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );

		register_nav_menus(
			array(
				'menu-1' => esc_html__( 'Primary', 'hocwp-theme' )
			)
		);

		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script'
			)
		);

		add_theme_support( 'customize-selective-refresh-widgets' );

		$GLOBALS['content_width'] = apply_filters( 'hocwp_theme_content_width', 640 );
	}
}

new HOCWP_Theme_Third_Party();