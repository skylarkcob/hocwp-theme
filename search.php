<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header();
do_action( 'hocwp_theme_template_search' );
do_action( 'ht/template/search' );
get_footer();