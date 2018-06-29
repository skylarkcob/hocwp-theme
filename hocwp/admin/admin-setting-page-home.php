<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$tab = new HOCWP_Theme_Admin_Setting_Tab( 'home', __( 'Home', 'hocwp-theme' ), '<span class="dashicons dashicons-admin-home"></span>' );

$args = array(
	'type'  => 'number',
	'class' => 'small-text'
);

$tab->add_field( 'posts_per_page', __( 'Posts Per Page', 'hocwp-theme' ), '', $args );