<?php
defined( 'ABSPATH' ) || exit;

/**
 * Custom template tags for this theme
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package HocWP_Theme
 */

if ( ! function_exists( 'hocwp_theme_posted_on' ) ) :
	/**
	 * Prints HTML with meta information for the current post-date/time.
	 */
	function hocwp_theme_posted_on() {
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';

		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
		}

		$time_string = sprintf(
			$time_string,
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() ),
			esc_attr( get_the_modified_date( DATE_W3C ) ),
			esc_html( get_the_modified_date() )
		);

		$posted_on = sprintf(
		/* translators: %s: post date. */
			esc_html_x( 'Posted on %s', 'post date', 'hocwp-theme' ),
			'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
		);

		echo '<span class="posted-on">' . $posted_on . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
endif;

if ( ! function_exists( 'hocwp_theme_posted_by' ) ) :
	/**
	 * Prints HTML with meta information for the current author.
	 */
	function hocwp_theme_posted_by() {
		$byline = sprintf(
		/* translators: %s: post author. */
			esc_html_x( 'by %s', 'post author', 'hocwp-theme' ),
			'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
		);

		echo '<span class="byline"> ' . $byline . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
endif;

if ( ! function_exists( 'hocwp_theme_entry_footer' ) ) :
	/**
	 * Prints HTML with meta information for the categories, tags and comments.
	 */
	function hocwp_theme_entry_footer() {
		// Hide category and tag text for pages.
		if ( 'post' === get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_category_list( esc_html__( ', ', 'hocwp-theme' ) );

			if ( $categories_list ) {
				/* translators: 1: list of categories. */
				printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'hocwp-theme' ) . '</span>', $categories_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			/* translators: used between list items, there is a space after the comma */
			$tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'hocwp-theme' ) );

			if ( $tags_list ) {
				/* translators: 1: list of tags. */
				printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'hocwp-theme' ) . '</span>', $tags_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}

		if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
			echo '<span class="comments-link">';
			comments_popup_link(
				sprintf(
					wp_kses(
					/* translators: %s: post title */
						__( 'Leave a Comment<span class="screen-reader-text"> on %s</span>', 'hocwp-theme' ),
						array(
							'span' => array(
								'class' => array()
							)
						)
					),
					wp_kses_post( get_the_title() )
				)
			);
			echo '</span>';
		}

		edit_post_link(
			sprintf(
				wp_kses(
				/* translators: %s: Name of current post. Only visible to screen readers */
					__( 'Edit <span class="screen-reader-text">%s</span>', 'hocwp-theme' ),
					array(
						'span' => array(
							'class' => array()
						)
					)
				),
				wp_kses_post( get_the_title() )
			),
			'<span class="edit-link">',
			'</span>'
		);
	}
endif;

if ( ! function_exists( 'hocwp_theme_post_thumbnail' ) ) :
	/**
	 * Displays an optional post thumbnail.
	 *
	 * Wraps the post thumbnail in an anchor element on index views, or a div
	 * element when on single views.
	 */
	function hocwp_theme_post_thumbnail() {
		if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
			return;
		}

		if ( is_singular() ) :
			?>
            <div class="post-thumbnail">
				<?php the_post_thumbnail(); ?>
            </div><!-- .post-thumbnail -->
		<?php else : ?>
            <a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1"
               title="<?php the_title(); ?>">
				<?php
				the_post_thumbnail(
					'post-thumbnail',
					array(
						'alt' => the_title_attribute(
							array(
								'echo' => false
							)
						)
					)
				);
				?>
            </a>
		<?php
		endif; // End is_singular().
	}
endif;

if ( ! function_exists( 'wp_body_open' ) ) :
	/**
	 * Shim for sites older than 5.2.
	 *
	 * @link https://core.trac.wordpress.org/ticket/12563
	 */
	function wp_body_open() {
		do_action( 'wp_body_open' );
	}
endif;

if ( ! defined( '' ) ) {
	/**
	 * Define if current theme is compatible with WordPress block editor and block widget.
	 */
	define( 'HOCWP_THEME_BLOCK_COMPATIBLE', false );
}

if ( ! defined( 'HOCWP_THEME_BLANK_STYLE' ) ) {
	/**
	 * Define theme load default styles and scripts or not.
	 *
	 * Data type: boolean
	 */
	define( 'HOCWP_THEME_BLANK_STYLE', false );
}

/**
 * Define the theme name.
 *
 * Data type: string
 */
if ( ! defined( 'HOCWP_THEME_NAME' ) ) {
	$path = get_stylesheet_directory();
	$path .= '/style.css';

	$data = get_file_data( $path, array( 'real_theme_name' => 'Real Theme Name' ) );

	$theme_name = ( is_array( $data ) && isset( $data['real_theme_name'] ) ) ? $data['real_theme_name'] : '';
	$theme_name = apply_filters( 'hocwp_theme_current_theme_name', $theme_name );

	define( 'HOCWP_THEME_NAME', $theme_name );
}

/**
 * Define theme support microformats or not.
 *
 * Data type: boolean
 */
if ( ! defined( 'HOCWP_THEME_SUPPORT_MICROFORMATS' ) ) {
	define( 'HOCWP_THEME_SUPPORT_MICROFORMATS', false );
}

if ( ! defined( 'HOCWP_THEME_REQUIRED_PLUGINS' ) ) {
	/**
	 * Define the required plugins for current theme.
	 *
	 * Data type: string
	 *
	 * Each plugin slug separates by commas.
	 */
	define( 'HOCWP_THEME_REQUIRED_PLUGINS', '' );
}

if ( ! defined( 'HOCWP_THEME_REQUIRED_EXTENSIONS' ) ) {
	/**
	 * Define the required extensions for current theme.
	 *
	 * Data type: string
	 *
	 * Each plugin slug separates by commas.
	 */
	define( 'HOCWP_THEME_REQUIRED_EXTENSIONS', '' );
}

if ( ! defined( 'HOCWP_THEME_RECOMMENDED_EXTENSIONS' ) ) {
	/**
	 * Define the recommended extensions for current theme.
	 *
	 * Data type: string
	 *
	 * Each extension slug separates by commas.
	 */
	define( 'HOCWP_THEME_RECOMMENDED_EXTENSIONS', '' );
}

/*
 * Using Structured Data Markup on your site.
 *
 * Data type: boolean
 *
 * Google Search works hard to understand the content of a page. You can help us by providing explicit clues about
 * the meaning of a page to Google by including structured data on the page. Structured data is a standardized format
 * for providing information about a page and classifying the page content; for example, on a recipe page, what are
 * the ingredients, the cooking time and temperature, the calories, and so on.
 */
if ( ! defined( 'HOCWP_THEME_STRUCTURED_DATA' ) ) {
	define( 'HOCWP_THEME_STRUCTURED_DATA', false );
}

if ( ! function_exists( 'hocwp_theme_post_thumbnail' ) ) {
	function hocwp_theme_post_thumbnail( $size = 'thumbnail', $attr = '' ) {
		hocwp_theme_post_thumbnail_html( $size, $attr );
	}
}

if ( ! defined( 'HOCWP_THEME_OVERTIME' ) ) {
	/**
	 * Skip work time checking.
	 *
	 * Data type: boolean
	 *
	 * If you still want to continue working, just define this value to TRUE.
	 */
	define( 'HOCWP_THEME_OVERTIME', false );
}

if ( ! defined( 'HOCWP_THEME_BREAK_MINUTES' ) ) {
	/**
	 * Working time interval.
	 *
	 * Data type: integer
	 *
	 * You should take a short break every 25 minutes. You can increase this number to work more longer. Define this
	 * number to zero to skip this function.
	 */
	define( 'HOCWP_THEME_BREAK_MINUTES', 25 );
}

if ( ! defined( 'HOCWP_THEME_SUPPORTS' ) ) {
	/*
	 * Custom theme supports using for add_theme_support function. You can apply default site background color, default
	 * background image, custom logo width and height, custom color for specific element etc.
	 *
	 * With custom colors like this: [custom-color][type_name][HEX color]
	 */
	define( 'HOCWP_THEME_SUPPORTS', array(
		'custom-background' => array(
			'default-color' => '#ffffff',
			'default-image' => ''
		),
		'custom-logo'       => array(
			'height'      => 40,
			'width'       => 120,
			'flex-height' => true,
			'flex-width'  => true
		),
		'custom-color'      => array(
			'primary'   => '#0073aa',
			'secondary' => '#23282d',
			'link'      => '#0073aa',
			'footer'    => '#f7f7f7'
		),
		'custom-header'     => array(
			'default-image'          => '',
			'width'                  => 0,
			'height'                 => 0,
			'flex-height'            => false,
			'flex-width'             => false,
			'uploads'                => true,
			'random-default'         => false,
			'header-text'            => true,
			'default-text-color'     => '',
			'wp-head-callback'       => '',
			'admin-head-callback'    => '',
			'admin-preview-callback' => ''
		)
	) );
}

if ( ! defined( 'HOCWP_THEME_DEFAULT_COLORS' ) ) {
	/*
	 * Setting default colors to apply for accent hue color picker. Each key contains child key: text, accent, secondary
	 * and borders.
	 *
	 * [type_name][text or accent or secondary or borders][HEX color]
	 */
	define( 'HOCWP_THEME_DEFAULT_COLORS', array(
		'content'       => array(
			'text'      => '#444444',
			'accent'    => '#ffffff',
			'secondary' => '#4ca6cf',
			'borders'   => '#dadada'
		),
		'header-footer' => array(
			'text'      => '#ffffff',
			'accent'    => '#23282d',
			'secondary' => '#f7f7f7',
			'borders'   => '#dfdfdf'
		)
	) );
}

if ( ! defined( 'HOCWP_THEME_CSS_ELEMENT_SELECTORS' ) ) {
	/*
 * Setting default CSS selectors for apply Default colors above. The key name (type_name) must same with keys in
 * array HOCWP_THEME_DEFAULT_COLORS.
 *
 * You can fill like this: [type_name][text or accent or secondary or borders][css property] = [elements]
 */
	define( 'HOCWP_THEME_CSS_ELEMENT_SELECTORS', array(
		'content'      => array(
			'accent'    => array(
				'background-color' => array(),
				'color'            => array(),
				'border-color'     => array()
			),
			'secondary' => array(
				'background-color' => array(),
				'color'            => array()
			),
			'text'      => array(
				'color' => array()
			),
			'borders'   => array(
				'border-color' => array()
			)
		),
		'notice'       => array(
			'text'      => array(
				'color' => array()
			),
			'accent'    => array(
				'background-color' => array()
			),
			'secondary' => array(),
			'borders'   => array(
				'border-color' => array(),
				'color'        => array()
			)
		),
		'custom-color' => array(
			'primary'         => array(
				'background-color' => array(),
				'color'            => array(),
				'border-color'     => array()
			),
			'secondary'       => array(
				'background-color' => array(),
				'color'            => array()
			),
			'link'            => array(
				'color' => array()
			),
			'footer'          => array(
				'background-color' => array()
			),
			'breadcrumb'      => array(
				'color' => array()
			),
			'breadcrumb-link' => array(
				'color' => array()
			)
		)
	) );
}

