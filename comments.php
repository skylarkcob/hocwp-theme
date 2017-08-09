<?php
if ( post_password_required() ) {
	return;
}
?>
<div id="comments" class="comments-area">
	<?php do_action( 'hocwp_theme_module_comments_area' ); ?>
</div>
