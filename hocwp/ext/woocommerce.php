<?php
/*
 * Name: WooCommerce
 * Description: Add more functionality for your shop site which runs base on WooCommerce Plugin.
 */
$load = apply_filters( 'hocwp_theme_load_extension_woocommerce', hocwp_theme_is_extension_active( __FILE__ ) );
if ( ! $load ) {
	return;
}