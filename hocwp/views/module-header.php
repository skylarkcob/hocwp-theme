<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( HT_Frontend()->is_amp( array( 'transitional', 'standard' ) ) ) {
	hocwp_theme_load_views( 'module-header-amp' );

	return;
} else {
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
}

hocwp_theme_html_tag( 'body' ); // Open body
wp_body_open();

$blank_body = apply_filters( 'hocwp_theme_blank_body', false );

if ( $blank_body ) {
	return;
}

if ( ! defined( 'HOCWP_THEME_BLANK_STYLE' ) || ! HOCWP_THEME_BLANK_STYLE ) {
	hocwp_theme_html_tag( 'div', 'site_container' ); // Open .site
	?>
    <a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'hocwp-theme' ); ?></a>
	<?php
	do_action( 'hocwp_theme_site_header_before' );
	hocwp_theme_html_tag( 'header', 'site_header', array(
		'class' => 'site-header',
		'id'    => 'masthead'
	) ); // Open .site-header
	do_action( 'hocwp_theme_module_site_header' );
	hocwp_theme_html_tag_close( 'header' ); // Close .site-header
	do_action( 'hocwp_theme_site_header_after' );
	hocwp_theme_html_tag( 'div', 'site_content' ); // Open .site-content
	do_action( 'hocwp_theme_site_content_top' );
} else {
	do_action( 'hocwp_theme_module_site_header' );
}