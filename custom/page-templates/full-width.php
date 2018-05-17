<?php
/**
 * Template Name: Full Width
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $hocwp_theme;
$hocwp_theme->temp_data['full_width'] = true;
get_header();
do_action( 'hocwp_theme_template_page' );
get_footer();