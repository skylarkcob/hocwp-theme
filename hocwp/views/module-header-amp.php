<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $wp_query;

$html_atts = 'amp ';
$html_atts .= get_language_attributes();

if ( is_singular() || is_single() || is_page() ) {
	$canonical = get_the_permalink();
} elseif ( is_home() ) {
	if ( isset( $wp_query->query['menu-amp'] ) ) {
		$canonical = home_url( '/menu-amp/' );
	} else {
		$canonical = home_url();
	}
} else {
	$canonical = HT_Util()->get_current_url();
}
?>
	<!doctype html>
	<?php hocwp_theme_html_tag( 'html', 'amp', $html_atts ); ?>
	<head>
		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
		<link rel="canonical" href="<?php echo esc_url( $canonical ); ?>">
		<?php wp_title( '' ); ?>
		<script async custom-element="amp-script" src="https://cdn.ampproject.org/v0/amp-script-0.1.js"></script>
		<?php
		echo '<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>';
		$css = '';

		$file = HT_Custom()->get_path() . '/css/amp.css';

		if ( file_exists( $file ) ) {
			$css = HT_Util()->read_all_text( $file );
		}

		$css .= hocwp_theme_get_customizer_css();

		$css .= HT_Options()->get_tab( 'custom_css', '', 'amp' );

		$css = apply_filters( 'hocwp_theme_amp_custom_style', $css );

		// Search and replace absolute URL to static URL.
		$css = str_replace( array(
			'../../hocwp/',
			'../images/'
		), array(
			get_template_directory_uri() . '/hocwp/',
			get_template_directory_uri() . '/custom/images/'
		), $css );

		HT()->wrap_text( $css, '<style amp-custom>', '</style>', true );
		?>
		<script async src="https://cdn.ampproject.org/v0.js"></script>
		<?php do_action( 'hocwp_theme_wp_head_amp' ); ?>
	</head>
<?php
hocwp_theme_html_tag( 'body', 'amp' );
hocwp_theme_html_tag( 'div', 'site_container' );
?>
	<header id="masthead" class="site-header">
		<?php do_action( 'hocwp_theme_module_site_header_amp' ); ?>
	</header>
<?php
do_action( 'hocwp_theme_site_header_after' );
hocwp_theme_html_tag( 'div', 'site_content' );
do_action( 'hocwp_theme_site_content_top' );