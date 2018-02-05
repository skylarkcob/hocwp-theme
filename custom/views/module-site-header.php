<?php
do_action( 'hocwp_theme_site_branding' );
do_action( 'hocwp_theme_main_menu' );
$slider = hocwp_theme_get_option( 'slider' );
if ( ! empty( $slider ) ) {
	?>
	<div class="main-slider">
		<?php echo $slider; ?>
	</div>
	<?php
}