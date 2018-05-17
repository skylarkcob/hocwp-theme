<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

do_action( 'hocwp_theme_sidebar_before' );
$sidebar = apply_filters( 'hocwp_theme_sidebar', 'sidebar-1' );
?>
	<aside id="secondary" class="widget-area sidebar <?php echo sanitize_html_class( $sidebar ); ?>">
		<?php
		do_action( 'hocwp_theme_sidebar_top', $sidebar );
		dynamic_sidebar( $sidebar );
		do_action( 'hocwp_theme_sidebar_bottom', $sidebar );
		?>
	</aside><!-- #secondary -->
<?php
do_action( 'hocwp_theme_sidebar_after' );