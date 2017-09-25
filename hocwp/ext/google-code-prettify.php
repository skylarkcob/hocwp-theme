<?php
/*
 * Name: Google Code Prettify
 * Description: Using Google Code Prettify for displaying source code.
 */
$load = apply_filters( 'hocwp_theme_load_extension_google_code_prettify', hocwp_theme_is_extension_active( __FILE__ ) );
if ( ! $load ) {
	return;
}

function hocwp_theme_gcp_admin_notices_action() {

}

add_action( 'admin_notices', 'hocwp_theme_gcp_admin_notices_action' );

function hocwp_theme_gcp_wp_enqueue_scripts_action() {
	wp_enqueue_script( 'google-code-prettify', 'https://rawgit.com/google/code-prettify/master/loader/run_prettify.js?autoload=true&skin=desert', array(), false, true );
}

add_action( 'wp_enqueue_scripts', 'hocwp_theme_gcp_wp_enqueue_scripts_action' );

function hocwp_theme_gcp_the_content_filter( $post_content ) {
	$post_content = str_replace( '[php]', '<pre class="lang-php prettyprint">', $post_content );
	$post_content = str_replace( '[/php]', '</pre>', $post_content );

	$post_content = str_replace( '[css]', '<pre class="lang-css prettyprint">', $post_content );
	$post_content = str_replace( '[/css]', '</pre>', $post_content );

	$post_content = str_replace( '[html]', '<pre class="lang-html prettyprint">', $post_content );
	$post_content = str_replace( '[/html]', '</pre>', $post_content );

	$post_content = preg_replace( '/<p[^>]*><\\/p[^>]*>/', '', $post_content );

	return $post_content;
}

add_filter( 'the_content', 'hocwp_theme_gcp_the_content_filter', 99 );