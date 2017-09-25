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

function hocwp_theme_widget_title_filter( $title ) {
	if ( ! is_admin() && ! empty( $title ) ) {
		if ( ! mb_strpos( $title, '</span>' ) ) {
			$title = '<span>' . $title . '</span>';
		}
	}

	return $title;
}

add_filter( 'widget_title', 'hocwp_theme_widget_title_filter' );

function hocwp_theme_template_single() {
	hocwp_theme_load_custom_template( 'template-single' );
}

add_action( 'hocwp_theme_template_single', 'hocwp_theme_template_single' );

function hocwp_theme_content_area_before() {
	?>
	<div id="primary" class="content-area">
	<main id="main" class="site-main">
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
				$attr = sprintf( 'data-text="%s"', __( 'Next Posts', 'hocwp-theme' ) );
				break;
			case 'previous':
				$attr = sprintf( 'data-text="%s"', __( 'Previous Posts', 'hocwp-theme' ) );
				break;
		}
		$output = HOCWP_Theme::add_html_attribute( 'div', $output, $attr );
	}

	return $output;
}

add_filter( 'next_post_link', 'hocwp_theme_adjacent_post_link_filter', 10, 5 );
add_filter( 'previous_post_link', 'hocwp_theme_adjacent_post_link_filter', 10, 5 );

function hocwp_theme_recheck_has_nav_menu( $has_nav_menu, $location ) {
	if ( ! $has_nav_menu ) {
		$menu = wp_nav_menu( array( 'theme_location' => $location, 'echo' => false, 'fallback_cb' => '' ) );
	}

	return $has_nav_menu;
}

add_filter( 'has_nav_menu', 'hocwp_theme_recheck_has_nav_menu', 10, 2 );

function hocwp_theme_human_time_diff_filter( $since, $diff ) {
	if ( $diff < MINUTE_IN_SECONDS ) {
		$secs = $diff;
		if ( $secs <= 1 ) {
			$secs = 1;
		}
		$since = sprintf( _n( '%s sec', '%s secs', $secs, 'hocwp-theme' ), $secs );
	}

	return $since;
}

add_filter( 'human_time_diff', 'hocwp_theme_human_time_diff_filter', 10, 2 );

function hocwp_theme_navigation_markup_template_filter() {
	$template = '<nav class="navigation %1$s">
		<h2 class="screen-reader-text">%2$s</h2>
		<div class="nav-links">%3$s</div>
	</nav>';

	return $template;
}

add_filter( 'navigation_markup_template', 'hocwp_theme_navigation_markup_template_filter' );

function hocwp_theme_wp_head_action() {
	global $hocwp_theme;
	$options = $hocwp_theme->options;
	if ( isset( $options['custom_code']['head'] ) ) {
		echo $options['custom_code']['head'];
	}
	$css = isset( $options['custom_code']['css'] ) ? $options['custom_code']['css'] : '';
	if ( ! empty( $css ) ) {
		if ( ! class_exists( 'HOCWP_Theme_Minify' ) ) {
			require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-minify.php';
		}
		$css   = strip_tags( $css );
		$css   = HOCWP_Theme_Minify::css( $css );
		$style = new HOCWP_Theme_HTML_Tag( 'style' );
		$style->set_text( $css );
		$style->add_attribute( 'type', 'text/css' );
		$style->output();
	}
}

add_action( 'wp_head', 'hocwp_theme_wp_head_action' );

function hocwp_theme_wp_footer_action() {
	global $hocwp_theme;
	$options = $hocwp_theme->options;
	$load    = apply_filters( 'hocwp_theme_load_facebook_sdk_javascript', false );
	if ( $load ) {
		$sdk = isset( $options['social']['facebook_sdk_javascript'] ) ? $options['social']['facebook_sdk_javascript'] : '';
		echo $sdk;
	}
	$agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
	if ( empty( $agent ) || false === strpos( $agent, 'Page Speed' ) || false === strpos( $agent, 'Speed Insights' ) ) {
		$google_analytics = isset( $options['custom_code']['google_analytics'] ) ? $options['custom_code']['google_analytics'] : '';
		echo $google_analytics;
	}
	if ( isset( $options['custom_code']['body'] ) ) {
		echo $options['custom_code']['body'];
	}
}

add_action( 'wp_footer', 'hocwp_theme_wp_footer_action' );

function hocwp_theme_site_branding_action() {
	?>
	<div class="site-branding">
		<?php the_custom_logo(); ?>
	</div><!-- .site-branding -->
	<?php
}

add_action( 'hocwp_theme_site_branding', 'hocwp_theme_site_branding_action' );

function hocwp_theme_theme_mod_custom_logo_filter( $mod ) {
	global $hocwp_theme;
	$options      = $hocwp_theme->options;
	$logo_display = $options['general']['logo_display'];
	if ( 'image' == $logo_display ) {
		$id = isset( $options['general']['logo_image'] ) ? $options['general']['logo_image'] : '';
		if ( HOCWP_Theme::is_positive_number( $id ) ) {
			$mod = $id;
		}
	} else {
		$mod = null;
	}

	return $mod;
}

add_filter( 'theme_mod_custom_logo', 'hocwp_theme_theme_mod_custom_logo_filter' );

function hocwp_theme_get_custom_logo_filter( $html ) {
	global $hocwp_theme;
	$options      = $hocwp_theme->options;
	$logo_display = $options['general']['logo_display'];
	if ( 'image' != $logo_display ) {
		if ( 'text' == $logo_display ) {
			$text = isset( $options['general']['logo_text'] ) ? $options['general']['logo_text'] : '';
			if ( empty( $text ) ) {
				$text = get_bloginfo( 'name', 'display' );
			} else {
				$domain = HOCWP_Theme::get_domain_name( home_url() );
				$text   = str_replace( '[DOMAIN]', $domain, $text );
			}
			$text = strip_tags( $text );
			if ( ! empty( $text ) ) {
				if ( is_front_page() && is_home() ) : ?>
					<h1 class="site-title">
						<a class="navbar-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"
						   rel="home"><?php echo $text; ?></a>
					</h1>
				<?php else : ?>
					<p class="site-title">
						<a class="navbar-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"
						   rel="home"><?php echo $text; ?></a>
					</p>
					<?php
				endif;
				$description = get_bloginfo( 'description', 'display' );
				if ( $description || is_customize_preview() ) : ?>
					<p class="site-description"><?php echo $description; /* WPCS: xss ok. */ ?></p>
					<?php
				endif;
			}
		} elseif ( 'custom' == $logo_display ) {
			if ( isset( $options['general']['logo_html'] ) ) {
				echo isset( $options['general']['logo_html'] );
			}
		}
	}

	return $html;
}

add_filter( 'get_custom_logo', 'hocwp_theme_get_custom_logo_filter' );

function hocwp_theme_socials( $args = array() ) {
	$defaults = array(
		'socials' => array(
			'facebook' => array(
				'base'  => 'https://www.facebook.com/sharer/sharer.php?u=[URL]',
				'class' => 'btn btn-primary btn-sm'
			),
			'gplus'    => array(
				'base'  => 'https://plus.google.com/share?url=[URL]',
				'class' => 'btn btn-danger btn-sm',
				'name'  => 'Google+'
			),
			'twitter'  => array(
				'base'     => 'https://twitter.com/intent/tweet?url=[URL]',
				'class'    => 'btn btn-info btn-sm',
				'username' => ''
			)
		),
		'url'     => '',
		'post_id' => ''
	);
	$args     = wp_parse_args( $args, $defaults );
	$url      = $args['url'];
	if ( empty( $url ) ) {
		$post_id = $args['post_id'];
		if ( HOCWP_Theme::is_positive_number( $post_id ) ) {
			$url = get_permalink( $post_id );
		} else {
			$url = hocwp_get_current_url();
		}
	}
	$socials = $args['socials'];
	if ( empty( $url ) ) {
		return;
	}
	?>
	<div class="social">
		<?php
		foreach ( $socials as $social => $data ) {
			$base  = $data['base'];
			$base  = str_replace( '[URL]', $url, $base );
			$class = $data['class'];
			$class .= ' ' . sanitize_html_class( $social );
			$name = isset( $data['name'] ) ? $data['name'] : ucwords( $social );
			?>
			<a href="<?php echo esc_url( $base ); ?>" rel="nofollow" target="_blank" class="<?php echo $class; ?>"
			   title="<?php echo esc_attr( sprintf( __( 'Share on %s', 'hocwp-theme' ), $name ) ); ?>"><?php echo $name; ?></a>
			<?php
		}
		?>
	</div>
	<?php
}

function hocwp_theme_get_option( $name, $default = '', $base = 'general' ) {
	global $hocwp_theme;
	$options = $hocwp_theme->options;
	$options = isset( $options[ $base ] ) ? $options[ $base ] : '';
	$value   = isset( $options[ $name ] ) ? $options[ $name ] : '';
	if ( empty( $value ) && gettype( $value ) != gettype( $default ) ) {
		$value = $default;
	}

	return $value;
}