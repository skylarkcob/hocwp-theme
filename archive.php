<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header();
do_action( 'hocwp_theme_template_archive' );
do_action( 'ht/template/archive' );
get_footer();