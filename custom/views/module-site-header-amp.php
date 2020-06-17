<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$href = home_url( '/menu-amp/' );

global $wp_query;

if ( isset( $wp_query->query['menu-amp'] ) ) {
	$href = '#';
}

$menu_button_label = HT_Options()->get_tab( 'menu_button_label', '', 'amp' );

if ( empty( $menu_button_label ) ) {
	$menu_button_label = _x( 'Menu', 'menu button label', 'hocwp-theme' );
}
?>
<div class="top-bar">
	<div class="container">
		<?php do_action( 'hocwp_theme_site_branding' ); ?>
		<div class="menu">
			<div class="inner">
				<a href="<?php echo esc_attr( $href ); ?>">
					<span class="menu-icon image-sprites">&nbsp;</span>
					<span><?php echo $menu_button_label; ?></span>
				</a>
			</div>
		</div>
	</div>
</div>