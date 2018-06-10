<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
	<!DOCTYPE html>
	<?php hocwp_theme_html_tag( 'html', '', get_language_attributes() ); ?>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<?php
		$responsive = apply_filters( 'hocwp_theme_enable_responsive', true );

		if ( $responsive ) {
			echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
		}

		wp_head();
		?>
	</head>
<?php
hocwp_theme_html_tag( 'body' );
hocwp_theme_html_tag( 'div', 'site_container' );
?>
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'hocwp-theme' ); ?></a>
	<header id="masthead" class="site-header">
		<?php do_action( 'hocwp_theme_module_site_header' ); ?>
	</header>
<?php
do_action( 'hocwp_theme_site_header_after' );
hocwp_theme_html_tag( 'div', 'site_content' );
do_action( 'hocwp_theme_site_content_top' );