<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?><?php hocwp_theme_attribute( 'body' ); ?>>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'hocwp-theme' ); ?></a>
	<header id="masthead" class="site-header">
		<?php do_action( 'hocwp_theme_module_site_header' ); ?>
	</header>
	<!-- #masthead -->
	<div id="content" class="site-content">
<?php
do_action( 'hocwp_theme_site_content_top' );