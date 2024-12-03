<?php
/**
 * HocWP Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package HocWP_Theme
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( '_S_VERSION' ) ) {
	$theme = new WP_Theme( basename( dirname( __FILE__ ) ), dirname( __FILE__, 2 ) );

	$version = '1.0.0';

	if ( $theme->exists() ) {
		$version = $theme->get( 'Version' );
	}

	// Replace the version number of the theme on each release.
	define( '_S_VERSION', $version );
}

if ( ! function_exists( 'hocwp_theme_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function hocwp_theme_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on HocWP Theme, use a find and replace
		 * to change 'hocwp-theme' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'hocwp-theme', get_template_directory() . '/custom/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus(
			array(
				'menu-1' => esc_html__( 'Primary', 'hocwp-theme' ),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
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

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );
	}
endif;
add_action( 'after_setup_theme', 'hocwp_theme_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function hocwp_theme_content_width() {
	// This variable is intended to be overruled from themes.
	// Open WPCS issue: {@link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/1043}.
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$GLOBALS['content_width'] = apply_filters( 'hocwp_theme_content_width', 640 );
}

add_action( 'after_setup_theme', 'hocwp_theme_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function hocwp_theme_widgets_init() {
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

add_action( 'widgets_init', 'hocwp_theme_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function hocwp_theme_scripts() {
	wp_enqueue_style( 'hocwp-theme-style', get_stylesheet_uri(), array(), _S_VERSION );
	wp_style_add_data( 'hocwp-theme-style', 'rtl', 'replace' );

	wp_enqueue_script( 'hocwp-theme-navigation', get_template_directory_uri() . '/js/navigation.js', array( 'hocwp-theme' ), _S_VERSION, true );

	wp_enqueue_script( 'hocwp-theme-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array( 'hocwp-theme' ), _S_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}

add_action( 'wp_enqueue_scripts', 'hocwp_theme_scripts' );

/**
 * Custom template tags for this theme.
 */
require( get_template_directory() . '/inc/template-tags.php' );

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require( get_template_directory() . '/inc/template-functions.php' );

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require( get_template_directory() . '/inc/jetpack.php' );
}

if ( file_exists( get_template_directory() . '/hocwp/load.php' ) ) {
	require( get_template_directory() . '/hocwp/load.php' );
}