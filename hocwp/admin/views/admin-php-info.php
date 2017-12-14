<div class="wrap">
	<hr class="wp-header-end">
	<embed id="hocwp-theme-phpinfo" src="<?php echo HOCWP_THEME_CORE_URL . '/admin/views/phpinfo.php' ?>"
	       class="widefat">
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				var $hocwp_theme_phpinfo = $('#hocwp-theme-phpinfo');
				$hocwp_theme_phpinfo.css({height: document.body.scrollHeight - 100});
			});
		</script>
</div>