<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $hocwp_theme;

$hocwp_theme = HOCWP_Theme()->object;

if ( ! isset( $hocwp_theme->options ) || ! is_array( $hocwp_theme->options ) ) {
	$hocwp_theme->options = HOCWP_Theme()->get_options();
}