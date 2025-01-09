<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

do_action( 'ht/sidebar/before' );
do_action( 'hocwp_theme_sidebar_before' );

$full_width = ht_frontend()->is_full_width();

if ( ! $full_width ) {
	$sidebar = apply_filters( 'hocwp_theme_sidebar', 'sidebar-1' );
	$sidebar = apply_filters( 'ht/sidebar', $sidebar );
	$class   = 'widget-area sidebar';
	$class   .= ' ' . sanitize_html_class( $sidebar );
	$class   = apply_filters( 'hocwp_theme_sidebar_html_class', $class, $sidebar );
	$class   = apply_filters( 'ht/sidebar/html_class', $class, $sidebar );
	?>
    <aside id="secondary" class="<?php echo esc_attr( $class ); ?>">
		<?php
		do_action( 'ht/sidebar/top', $sidebar );
		do_action( 'hocwp_theme_sidebar_top', $sidebar );
		dynamic_sidebar( $sidebar );
		do_action( 'hocwp_theme_sidebar_bottom', $sidebar );
		do_action( 'ht/sidebar/bottom', $sidebar );
		?>
    </aside><!-- #secondary -->
	<?php
}

do_action( 'hocwp_theme_sidebar_after' );
do_action( 'ht/sidebar/after' );