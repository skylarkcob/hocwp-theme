<?php
function hocwp_theme_admin_notices_action() {
	//hocwp_theme_debug(wp_styles());
}
add_action('admin_enqueue_scripts', 'hocwp_theme_admin_notices_action');