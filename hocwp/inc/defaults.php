<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $hocwp_theme, $hocwp_theme_metas;

if ( ! isset( $hocwp_theme ) ) {
	$hocwp_theme = hocwp_theme()->object;
}

if ( ! isset( $hocwp_theme->options ) || ! is_array( $hocwp_theme->options ) ) {
	$hocwp_theme->options = hocwp_theme()->get_options();
}

hocwp_theme()->settings = $hocwp_theme->options;

if ( ! ( $hocwp_theme_metas instanceof HOCWP_Theme_Metas ) ) {
	$hocwp_theme_metas = hocwp_theme()->new_instance( 'HOCWP_Theme_Metas' );
}