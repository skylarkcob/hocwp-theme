<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header();
do_action( 'hocwp_theme_template_single' );
do_action( 'ht/template/single' );
get_footer();