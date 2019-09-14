<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $pagenow, $plugin_page, $hocwp_theme, $post_type;

if ( empty( $plugin_page ) && isset( $_GET['page'] ) ) {
	$plugin_page = $_GET['page'];
}

if ( empty( $post_type ) ) {
	$post_type = HT_Admin()->get_current_post_type();
}

function hocwp_theme_admin_notices_action() {
	if ( ! HOCWP_THEME_DEVELOPING ) {
		$email = get_bloginfo( 'admin_email' );

		if ( is_email( $email ) && 'hocwp.net@gmail.com' == $email ) {
			$link = '<a href="' . admin_url( 'options-general.php' ) . '">' . _x( 'general settings page', 'setting page', 'hocwp-theme' ) . '</a>';

			$args = array(
				'type'    => 'error',
				'message' => sprintf( __( 'You must change administrator\'s email address for site working, please go to %s and update it.', 'hocwp-theme' ), $link )
			);

			HOCWP_Theme_Utility::admin_notice( $args );
		}
	}
}

add_action( 'admin_notices', 'hocwp_theme_admin_notices_action' );

require HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-admin-field.php';

if ( 'post.php' == $pagenow || 'post-new.php' == $pagenow || 'term.php' == $pagenow || 'edit-tags.php' == $pagenow || 'link.php' == $pagenow || 'link-add.php' == $pagenow || 'nav-menus.php' == $pagenow ) {
	require HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-meta-field.php';
	require HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-meta.php';
}

if ( 'post.php' == $pagenow || 'post-new.php' == $pagenow ) {
	require HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-meta-post.php';
}

if ( 'link.php' == $pagenow || 'link-add.php' == $pagenow ) {
	require HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-meta-bookmark.php';
}

if ( 'term.php' == $pagenow || 'edit-tags.php' == $pagenow ) {
	require HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-meta-term.php';
}

if ( 'nav-menus.php' == $pagenow ) {
	require HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-meta-menu.php';
}

require HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-admin-setting-field.php';
require HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-admin-setting-page.php';

add_action( 'after_setup_theme', function () {
	global $pagenow, $hocwp_theme, $plugin_page;

	if ( 'themes.php' == $pagenow && $plugin_page == $hocwp_theme->option->get_slug() ) {
		require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-general.php';
		require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-home.php';
		require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-writing.php';
		require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-reading.php';
		require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-discussion.php';
		require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-media.php';
		require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-permalinks.php';
		require HOCWP_THEME_CORE_PATH . '/ext/admin-setting-page-smtp.php';
		require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-social.php';
		require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-custom-code.php';
		require HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-extension.php';
	}
}, 20 );

if ( 'edit.php' == $pagenow || 'post.php' == $pagenow || 'post-new.php' == $pagenow || HOCWP_THEME_DOING_AJAX ) {
	require HOCWP_THEME_CORE_PATH . '/admin/featured.php';
}

function hocwp_theme_admin_menu_extra() {
	$title = __( 'Extensions', 'hocwp-theme' );
	add_theme_page( $title, $title, 'manage_options', 'themes.php?page=hocwp_theme&tab=extension' );

	$title = __( 'Theme Plugins', 'hocwp-theme' );
	add_theme_page( $title, $title, 'activate_plugins', 'hocwp_theme_plugins', 'hocwp_theme_admin_menu_theme_plugins_callback' );

	add_theme_page( 'phpinfo()', __( 'PHP Info', 'hocwp-theme' ), 'manage_options', 'hocwp_theme_phpinfo', 'hocwp_theme_admin_menu_phpinfo_callback' );
}

add_action( 'admin_menu', 'hocwp_theme_admin_menu_extra' );

function hocwp_theme_admin_menu_theme_plugins_callback() {
	load_template( HOCWP_THEME_CORE_PATH . '/admin/views/admin-theme-plugins.php' );
}

function hocwp_theme_admin_menu_phpinfo_callback() {
	load_template( HOCWP_THEME_CORE_PATH . '/admin/views/admin-php-info.php' );
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

	global $pagenow;

	if ( 'post.php' == $pagenow ) {
		$post_id = isset( $_GET['post'] ) ? $_GET['post'] : '';

		if ( HT()->is_positive_number( $post_id ) ) {
			$obj = get_post( $post_id );

			if ( $obj instanceof WP_Post ) {
				if ( $obj->post_author != get_current_user_id() && ! current_user_can( 'delete_others_posts' ) ) {
					wp_redirect( admin_url( 'edit.php' ) );
					exit;
				}
			}
		}
	}
}

add_action( 'admin_init', 'hocwp_theme_admin_init_action' );

function hocwp_theme_enqueue_plugin_installer_scripts() {
	wp_enqueue_script( 'plugin-install' );
	add_thickbox();
	wp_enqueue_script( 'updates' );
}

if ( 'themes.php' == $pagenow && 'hocwp_theme_plugins' == $plugin_page ) {
	add_action( 'admin_enqueue_scripts', 'hocwp_theme_enqueue_plugin_installer_scripts' );
}


function hocwp_theme_admin_notices_required_plugins() {
	if ( ! HT_Requirement()->check_required_plugins() ) {
		$link = '<a href="' . self_admin_url( 'themes.php?page=hocwp_theme_plugins&tab=required' ) . '">' . _x( 'this list', 'required plugins list', 'hocwp-theme' ) . '</a>';

		$args = array(
			'type'    => 'error',
			'message' => sprintf( __( 'You must install required plugins for theme can work properly. Try to install and activate all plugins in %s.', 'hocwp-theme' ), $link )
		);

		HOCWP_Theme_Utility::admin_notice( $args );
	}

	if ( ! HT_Requirement()->check_required_extensions() ) {
		$link = '<a href="' . self_admin_url( 'themes.php?page=hocwp_theme&tab=extension&extension_status=required' ) . '">' . _x( 'this list', 'required plugins list', 'hocwp-theme' ) . '</a>';

		$args = array(
			'type'    => 'error',
			'message' => sprintf( __( 'You must install all required extensions for theme can work properly. Try to install and activate all extensions in %s.', 'hocwp-theme' ), $link )
		);

		HOCWP_Theme_Utility::admin_notice( $args );
	}

	if ( ! HT_Requirement()->check_extension_woocommerce() ) {
		$link = '<a href="' . self_admin_url( 'themes.php?page=hocwp_theme&tab=extension' ) . '">' . _x( 'here', 'list extensions link', 'hocwp-theme' ) . '</a>';

		$args = array(
			'type'    => 'error',
			'message' => sprintf( __( 'You must enable WooCommerce extension for this theme. Try to activate it %s.', 'hocwp-theme' ), $link )
		);

		HOCWP_Theme_Utility::admin_notice( $args );
	}
}

add_action( 'admin_notices', 'hocwp_theme_admin_notices_required_plugins' );

/**
 * Create simple setting field.
 *
 * @param $id
 * @param $title
 * @param string $callback
 * @param array $callback_args
 * @param string $data_type
 * @param string $tab
 * @param string $section
 *
 * @return array
 */
function hocwp_theme_create_setting_field( $id, $title, $callback = 'input', $callback_args = array(), $data_type = 'string', $tab = 'general', $section = 'default' ) {
	$field = new HOCWP_Theme_Admin_Setting_Field( $id, $title, $callback, $callback_args, $data_type, $tab, $section );

	return $field->generate();
}

/**
 * Create simple setting field for using on homepage.
 *
 * @param $id
 * @param $title
 * @param string $callback
 * @param array $callback_args
 * @param string $data_type
 * @param string $section
 *
 * @return array
 */
function hocwp_theme_create_setting_field_for_home( $id, $title, $callback = 'input', $callback_args = array(), $data_type = 'string', $section = 'default' ) {
	return hocwp_theme_create_setting_field( $id, $title, $callback, $callback_args, $data_type, 'home', $section );
}

/**
 * Create simple meta field.
 *
 * @param $id
 * @param $title
 * @param string $callback
 * @param array $callback_args
 * @param string $data_type
 *
 * @return array
 */
function hocwp_theme_create_meta_field( $id, $title, $callback = 'input', $callback_args = array(), $data_type = 'string' ) {
	$field = new HOCWP_Theme_Meta_Field( $id, $title, $callback, $callback_args, $data_type );

	return $field->generate();
}

function hocwp_theme_backup_wp_content_folders_theme( $folders ) {
	if ( defined( 'HOCWP_THEME_DEVELOPING' ) && HOCWP_THEME_DEVELOPING ) {
		$folders   = (array) $folders;
		$folders[] = 'themes\hocwp-theme';
	}

	return $folders;
}

add_filter( 'hocwp_theme_backup_wp_content_folders', 'hocwp_theme_backup_wp_content_folders_theme' );

function hocwp_theme_admin_head_action() {
	$icon = HT_Util()->get_theme_option( 'site_icon' );

	if ( HT()->is_positive_number( $icon ) ) {
		$icon = HT_Sanitize()->media_value( $icon );

		if ( ! empty( $icon['url'] ) ) {
			echo '<link rel="shortcut icon" type="' . $icon['mime_type'] . '" href="' . $icon['url'] . '"/>';
		}
	}
}

add_action( 'admin_head', 'hocwp_theme_admin_head_action' );

if ( 'widgets.php' == $pagenow || 'admin-ajax.php' == $pagenow ) {
	function hocwp_theme_widget_form_before( $instance, $widget ) {
		if ( $widget instanceof WP_Widget ) {
			$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
			$box_class = $widget->id_base;
			$box_class .= ' hocwp-theme';
			echo '<div class="' . $box_class . '">';
			?>
			<p>
				<label
					for="<?php echo $widget->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'hocwp-theme' ); ?></label>
				<input class="widefat" id="<?php echo $widget->get_field_id( 'title' ); ?>"
				       name="<?php echo $widget->get_field_name( 'title' ); ?>" type="text"
				       value="<?php echo $title; ?>"/>
			</p>
			<?php
		}
	}

	add_action( 'hocwp_theme_widget_form_before', 'hocwp_theme_widget_form_before', 10, 2 );

	function hocwp_theme_widget_form_after() {
		echo '</div>';
	}

	add_action( 'hocwp_theme_widget_form_after', 'hocwp_theme_widget_form_after' );
}

if ( 'profile.php' == $pagenow || 'edit-user.php' == $pagenow ) {
	function hocwp_theme_user_profile_fields( $user ) {
		?>
		<table class="form-table">
			<tbody>
			<?php do_action( 'hocwp_theme_user_profile_fields', $user ); ?>
			</tbody>
		</table>
		<?php
	}

	add_action( 'show_user_profile', 'hocwp_theme_user_profile_fields' );
	add_action( 'edit_user_profile', 'hocwp_theme_user_profile_fields' );

	function hocwp_theme_user_profile_updated( $user_id ) {
		do_action( 'hocwp_theme_user_profile_updated', $user_id );
	}

	add_action( 'personal_options_update', 'hocwp_theme_user_profile_updated' );
	add_action( 'edit_user_profile_update', 'hocwp_theme_user_profile_updated' );
}

if ( 'hocwp_ads' == $post_type ) {
	function hocwp_theme_default_ads_positions( $positions ) {
		$positions['related_posts'] = __( 'Related posts', 'hocwp-theme' );

		return $positions;
	}

	add_filter( 'hocwp_theme_ads_positions', 'hocwp_theme_default_ads_positions' );
}

function hocwp_theme_mce_buttons_filter( $mce_buttons, $editor_id ) {
	if ( 'content' == $editor_id ) {
		array_splice( $mce_buttons, 13, 0, 'wp_page' );
	}

	return $mce_buttons;
}

add_filter( 'mce_buttons', 'hocwp_theme_mce_buttons_filter', 10, 2 );

if ( 'admin-ajax.php' == $pagenow ) {
	require HOCWP_THEME_CORE_PATH . '/admin/ajax.php';
}

if ( 'index.php' == $pagenow ) {
	require HOCWP_THEME_CORE_PATH . '/admin/dashboard-widgets.php';
}