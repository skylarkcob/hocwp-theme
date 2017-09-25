<?php
$sidebar = apply_filters( 'hocwp_theme_sidebar', 'sidebar-1' );
if ( ! is_active_sidebar( $sidebar ) ) {
	return;
}
?>
<aside id="secondary" class="widget-area sidebar <?php echo sanitize_html_class( $sidebar ); ?>">
	<?php dynamic_sidebar( $sidebar ); ?>
</aside><!-- #secondary -->