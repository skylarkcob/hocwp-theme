<?php
global $pagenow, $plugin_page, $hocwp_theme;

if ( empty( $plugin_page ) && isset( $_GET['page'] ) ) {
	$plugin_page = $_GET['page'];
}

function hocwp_theme_admin_notices_action() {

}

add_action( 'admin_notices', 'hocwp_theme_admin_notices_action' );
if ( 'post.php' == $pagenow || 'post-new.php' == $pagenow || 'term.php' == $pagenow || 'edit-tags.php' == $pagenow ) {
	require HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-meta.php';
}
if ( 'post.php' == $pagenow || 'post-new.php' == $pagenow ) {
	require HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-meta-post.php';
}
if ( 'term.php' == $pagenow || 'edit-tags.php' == $pagenow ) {
	require HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-meta-term.php';
}
require HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-admin-setting-page.php';
if ( 'themes.php' == $pagenow && $plugin_page == $hocwp_theme->option->get_slug() ) {
	require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-general.php';
	//require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-writing.php';
	//require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-reading.php';
	require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-discussion.php';
	//require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-media.php';
	require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-permalinks.php';
	require HOCWP_THEME_CORE_PATH . '/ext/admin-setting-page-smtp.php';
	require HOCWP_THEME_CORE_PATH . '/ext/admin-setting-page-jwplayer.php';
	require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-social.php';
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

function hocwp_theme_wp_prepare_themes_for_js_filter( $prepared_themes ) {
	global $pagenow;
	if ( 'themes.php' == $pagenow && defined( 'HOCWP_THEME_NAME' ) ) {
		$theme = wp_get_theme();
		if ( isset( $prepared_themes[ $theme->get_stylesheet() ] ) ) {
			$prepared_themes[ $theme->get_stylesheet() ]['name'] = HOCWP_THEME_NAME;
		}
	}

	return $prepared_themes;
}

add_filter( 'wp_prepare_themes_for_js', 'hocwp_theme_wp_prepare_themes_for_js_filter' );

function hocwp_theme_admin_init_action() {
	if ( ! has_action( 'init', 'hocwp_theme_check_license' ) ) {
		exit;
	}
}

add_action( 'admin_init', 'hocwp_theme_admin_init_action' );

function hocwp_theme_post_submitbox_misc_actions_action( $post ) {
	wp_nonce_field( 'hocwp_theme_post_submitbox', 'hocwp_theme_post_submitbox_nonce' );
	$type  = get_post_type_object( $post->post_type );
	$value = get_post_meta( $post->ID, 'featured', true );
	do_action( 'hocwp_theme_post_submitbox_meta_field', $post );
	?>
	<div class="misc-pub-section misc-pub-featured">
		<input type="checkbox" id="featured" name="featured" value="1" <?php checked( 1, $value ); ?>>
		<label
			for="featured"><?php printf( __( 'Make this %s as featured?', 'hocwp-theme' ), $type->labels->singular_name ); ?></label>
	</div>
	<?php
}

add_action( 'post_submitbox_misc_actions', 'hocwp_theme_post_submitbox_misc_actions_action' );

function hocwp_theme_save_post_action( $post_id ) {
	if ( ! HOCWP_Theme_Utility::can_save_post( $post_id, 'hocwp_theme_post_submitbox', 'hocwp_theme_post_submitbox_nonce' ) ) {
		return;
	}
	if ( isset( $_POST['featured'] ) ) {
		update_post_meta( $post_id, 'featured', 1 );
	} else {
		update_post_meta( $post_id, 'featured', 0 );
	}
	do_action( 'hocwp_theme_post_submitbox_meta_field_save', $post_id );
}

add_action( 'save_post', 'hocwp_theme_save_post_action' );