<?php
do_action( 'hocwp_theme_site_content_bottom' );
echo '</div><!-- #content -->';
?>
	<footer id="colophon" class="site-footer">
		<?php do_action( 'hocwp_theme_module_site_footer' ); ?>
	</footer><!-- #colophon -->
<?php
echo '</div><!-- #page -->';
wp_footer();
echo '</body>';
echo '</html>';