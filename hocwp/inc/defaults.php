<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $hocwp_theme_metas;

if ( ! isset( hocwp_theme_object()->options ) || ! is_array( hocwp_theme_object()->options ) ) {
	hocwp_theme_object()->options = hocwp_theme()->get_options();
}

hocwp_theme()->settings = hocwp_theme_object()->options;

if ( ! ( $hocwp_theme_metas instanceof HOCWP_Theme_Metas ) ) {
	$hocwp_theme_metas = hocwp_theme()->new_instance( 'HOCWP_Theme_Metas' );
}