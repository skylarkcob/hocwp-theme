<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header();
do_action( 'hocwp_theme_template_index' );
do_action( 'ht/template/index' );
get_footer();