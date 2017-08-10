<?php
if ( is_active_sidebar( 'footer' ) ) {
	?>
	<div class="footer-widgets">
		<?php dynamic_sidebar( 'footer' ); ?>
	</div>
	<?php
}
?>
<div class="inner">
	<div id="copyright">
		<?php printf( __( '%s. Please ask for our permission before using our content outside our declared rights.', 'hocwp-theme' ), 'HocWP &copy; 2008 - ' . date( 'Y' ) ); ?>
	</div>
</div>