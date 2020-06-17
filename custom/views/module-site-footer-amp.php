<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$amp_footer = HT_Options()->get_tab( 'amp_footer', '', 'amp' );

if ( ! empty( $amp_footer ) ) {
	$amp_footer = do_shortcode( $amp_footer );
	$amp_footer = wpautop( $amp_footer );
	echo $amp_footer;
}