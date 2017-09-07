<?php
global $pagenow, $plugin_page, $hocwp_theme;

if ( empty( $plugin_page ) && isset( $_GET['page'] ) ) {
	$plugin_page = $_GET['page'];
}

function hocwp_theme_admin_notices_action() {

}

add_action( 'admin_notices', 'hocwp_theme_admin_notices_action' );

require HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-admin-setting-page.php';
if ( 'themes.php' == $pagenow && $plugin_page == $hocwp_theme->option->get_slug() ) {
	require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-general.php';
	require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-writing.php';
	require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-reading.php';
	require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-discussion.php';
	require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-media.php';
	require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-permalinks.php';
	require HOCWP_THEME_CORE_PATH . '/ext/admin-setting-page-smtp.php';
	require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-custom-code.php';
	require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-extension.php';
	if ( HOCWP_THEME_DEVELOPING ) {
		require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-development.php';
	}
}

function hocwp_theme_admin_menu_phpinfo() {
	add_theme_page( __( 'phpinfo()', 'hocwp-theme' ), __( 'PHP Info', 'hocwp-theme' ), 'manage_options', 'hocwp_theme_phpinfo', 'hocwp_theme_admin_menu_phpinfo_callback' );
}

add_action( 'admin_menu', 'hocwp_theme_admin_menu_phpinfo' );

function hocwp_theme_admin_menu_phpinfo_callback() {
	?>
	<div class="wrap">
		<embed id="hocwp-theme-phpinfo" src="<?php echo HOCWP_THEME_CORE_URL . '/admin/views/phpinfo.php' ?>"
		       class="widefat">
			<script type="text/javascript">
				jQuery(document).ready(function ($) {
					var $hocwp_theme_phpinfo = $('#hocwp-theme-phpinfo');
					$hocwp_theme_phpinfo.css({height: document.body.scrollHeight - 100});
				});
			</script>
	</div>
	<?php
}