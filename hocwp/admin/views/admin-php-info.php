<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="wrap">
	<hr class="wp-header-end" style="clear: both;">
	<embed id="hocwp-theme-phpinfo" src="<?php echo HOCWP_THEME_CORE_URL . '/admin/views/phpinfo.php' ?>"
	       class="widefat">
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				var phpInfo = $('#hocwp-theme-phpinfo');
				if (phpInfo.length) {
					phpInfo.css({height: document.body.scrollHeight - 100});
				}
			});
		</script>
</div>