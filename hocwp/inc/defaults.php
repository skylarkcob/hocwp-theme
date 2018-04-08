<?php
global $hocwp_theme, $is_opera, $hocwp_theme_protocol;

if ( empty( $hocwp_theme_protocol ) ) {
	$hocwp_theme_protocol = ( isset( $_SERVER['HTTPS'] ) && strtolower( $_SERVER['HTTPS'] ) != 'off' ) ? 'https://' : 'http://';
}

if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
	$is_opera = ( false !== strpos( $_SERVER['HTTP_USER_AGENT'], 'Opera' ) || false !== strpos( $_SERVER['HTTP_USER_AGENT'], 'OPR/' ) );
}

if ( ! is_object( $hocwp_theme ) ) {
	$hocwp_theme = new stdClass();
}

if ( ! isset( $hocwp_theme->client_info ) ) {
	$hocwp_theme->client_info = array();
}

$hocwp_theme->client_info['screen_width'] = isset( $_SESSION['screen_width'] ) ? $_SESSION['screen_width'] : 'unknown';

if ( ! isset( $hocwp_theme->temp_data ) ) {
	$hocwp_theme->temp_data = array();
}

if ( ! isset( $hocwp_theme->loop_data ) ) {
	$hocwp_theme->loop_data = array();
}

if ( ! isset( $hocwp_theme->options ) ) {
	$hocwp_theme->options = (array) get_option( 'hocwp_theme' );
}

if ( ! isset( $hocwp_theme->active_extensions ) ) {
	$hocwp_theme->active_extensions = (array) get_option( 'hocwp_theme_active_extensions', array() );
}

if ( ! isset( $hocwp_theme->option ) ) {
	$hocwp_theme->option = '';
}

$hocwp_theme->users_can_register = (bool) get_option( 'users_can_register' );

if ( ! isset( $hocwp_theme->defaults ) ) {
	$hocwp_theme->defaults = array();
}

$hocwp_theme->defaults['blacklist_keys']   = array();
$hocwp_theme->defaults['blacklist_keys'][] = 'sex';
$hocwp_theme->defaults['blacklist_keys'][] = 'adult';
$hocwp_theme->defaults['blacklist_keys'][] = 'porn';
$hocwp_theme->defaults['blacklist_keys'][] = 'ass';
$hocwp_theme->defaults['blacklist_keys'][] = 'penis';
$hocwp_theme->defaults['blacklist_keys'][] = 'tits';
$hocwp_theme->defaults['blacklist_keys'][] = 'viagra';

$hocwp_theme->defaults['blacklist_keys'][] = '37.58.100';
$hocwp_theme->defaults['blacklist_keys'][] = '1.52.133.67';
$hocwp_theme->defaults['blacklist_keys'][] = '5.144.176.59';
$hocwp_theme->defaults['blacklist_keys'][] = '46.151.52.36';
$hocwp_theme->defaults['blacklist_keys'][] = '46.161.41.199';
$hocwp_theme->defaults['blacklist_keys'][] = '46.151.52.32';
$hocwp_theme->defaults['blacklist_keys'][] = '46.151.52.42';
$hocwp_theme->defaults['blacklist_keys'][] = '178.74.109.248';
$hocwp_theme->defaults['blacklist_keys'][] = '46.151.52.66';
$hocwp_theme->defaults['blacklist_keys'][] = '46.151.52.30';
$hocwp_theme->defaults['blacklist_keys'][] = '46.151.52.33';
$hocwp_theme->defaults['blacklist_keys'][] = '46.161.41.199';
$hocwp_theme->defaults['blacklist_keys'][] = '46.151.52.61';
$hocwp_theme->defaults['blacklist_keys'][] = '46.151.52.46';
$hocwp_theme->defaults['blacklist_keys'][] = '46.151.52.62';
$hocwp_theme->defaults['blacklist_keys'][] = '46.151.52.38';
$hocwp_theme->defaults['blacklist_keys'][] = '46.151.52.40';
$hocwp_theme->defaults['blacklist_keys'][] = '46.151.52.37';
$hocwp_theme->defaults['blacklist_keys'][] = '46.151.52.35';
$hocwp_theme->defaults['blacklist_keys'][] = '46.151.52.71';
$hocwp_theme->defaults['blacklist_keys'][] = '46.151.52.43';
$hocwp_theme->defaults['blacklist_keys'][] = '46.151.52.68';

$hocwp_theme->defaults['date_format']     = get_option( 'date_format' );
$hocwp_theme->defaults['time_format']     = get_option( 'time_format' );
$hocwp_theme->defaults['timezone_string'] = get_option( 'timezone_string' );
$hocwp_theme->defaults['posts_per_page']  = get_option( 'posts_per_page' );
$hocwp_theme->defaults['locale']          = get_locale();

/*
 * SMTP Email
 */
$hocwp_theme->defaults['options']['smtp']['from_name']  = get_bloginfo( 'name' );
$hocwp_theme->defaults['options']['smtp']['from_email'] = get_bloginfo( 'admin_email' );
$hocwp_theme->defaults['options']['smtp']['port']       = 465;
$hocwp_theme->defaults['options']['smtp']['encryption'] = 'ssl';

/*
 * Discussion
 */
$hocwp_theme->defaults['options']['discussion']['avatar_size']    = 48;
$hocwp_theme->defaults['options']['discussion']['comment_system'] = 'default';

/*
 * General
 */
$hocwp_theme->defaults['options']['general']['logo_display'] = 'image';

/*
 * Home
 */
$hocwp_theme->defaults['options']['home']['posts_per_page'] = isset( $hocwp_theme->options['home']['posts_per_page'] ) ? absint( $hocwp_theme->options['home']['posts_per_page'] ) : $hocwp_theme->defaults['posts_per_page'];

/*
 * Reading
 */
$hocwp_theme->defaults['options']['reading']['excerpt_more'] = '&hellip;';

/*
 * Media
 */
$hocwp_theme->defaults['options']['media']['upload_per_day'] = 10;

/*
 * VIP
 */
$hocwp_theme->defaults['options']['vip']['post_price'] = 100;

$hocwp_theme->options = wp_parse_args( $hocwp_theme->options, $hocwp_theme->defaults['options'] );