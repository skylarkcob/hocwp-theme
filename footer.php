<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package HocWP_Theme
 */

?>

	</div><!-- #content -->

	<footer id="colophon" class="site-footer">
		<?php do_action('hocwp_theme_module_site_footer'); ?>
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>