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
require HOCWP_THEME_CORE_PATH . '/admin/featured.php';

function hocwp_theme_admin_menu_extra() {
	add_theme_page( __( 'Theme Plugins', 'hocwp-theme' ), __( 'Theme Plugins', 'hocwp-theme' ), 'activate_plugins', 'hocwp_theme_plugins', 'hocwp_theme_admin_menu_theme_plugins_callback' );
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
	global $pagenow, $plugin_page;
	if ( 'themes.php' == $pagenow && 'hocwp_theme_plugins' == $plugin_page ) {
		wp_enqueue_script( 'plugin-install' );
		add_thickbox();
		wp_enqueue_script( 'updates' );
	}
}

add_action( 'admin_enqueue_scripts', 'hocwp_theme_enqueue_plugin_installer_scripts' );

function hocwp_theme_admin_notices_required_plugins() {
	if ( ! HOCWP_Theme_Requirement::check_required_plugins() ) {
		$link = '<a href="' . self_admin_url( 'themes.php?page=hocwp_theme_plugins&tab=required' ) . '">' . _x( 'this list', 'required plugins list', 'hocwp-theme' ) . '</a>';
		$args = array(
			'type'    => 'error',
			'message' => sprintf( __( 'You must install required plugins for theme can work properly. Try to install and activate all plugins in %s.', 'hocwp-theme' ), $link )
		);
		HOCWP_Theme_Utility::admin_notice( $args );
	}

	if ( ! HOCWP_Theme_Requirement::check_extension_woocommerce() ) {
		$link = '<a href="' . self_admin_url( 'themes.php?page=hocwp_theme&tab=extension' ) . '">' . _x( 'here', 'list extensions link', 'hocwp-theme' ) . '</a>';
		$args = array(
			'type'    => 'error',
			'message' => sprintf( __( 'You must enable WooCommerce extension for this theme. Try to activate it  %s.', 'hocwp-theme' ), $link )
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
	if ( ! is_callable( $callback ) ) {
		if ( empty( $callback ) ) {
			$callback = 'input';
		}
		$callback = array( 'HOCWP_Theme_HTML_Field', $callback );
		if ( ! is_callable( $callback ) ) {
			$callback = array( 'HOCWP_Theme_HTML_Field', 'input' );
		}
	}

	if ( empty( $data_type ) ) {
		$data_type = 'string';
	}

	$field = array(
		'tab'     => $tab,
		'section' => $section,
		'id'      => $id,
		'title'   => $title,
		'type'    => $data_type,
		'args'    => array(
			'type'          => $data_type,
			'callback'      => $callback,
			'callback_args' => array(
				'class' => 'widefat'
			)
		)
	);

	$callback_args = (array) $callback_args;

	if ( isset( $callback_args['description'] ) ) {
		$field['args']['description'] = $callback_args['description'];
		unset( $callback_args['description'] );
	}

	$field['args']['callback_args'] = wp_parse_args( $callback_args, $field['args']['callback_args'] );

	return $field;
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
	if ( ! is_callable( $callback ) ) {
		if ( empty( $callback ) ) {
			$callback = 'input';
		}
		$callback = array( 'HOCWP_Theme_HTML_Field', $callback );
		if ( ! is_callable( $callback ) ) {
			$callback = array( 'HOCWP_Theme_HTML_Field', 'input' );
		}
	}

	if ( empty( $data_type ) ) {
		$data_type = 'string';
	}

	$field = array(
		'id'            => $id,
		'title'         => $title,
		'type'          => $data_type,
		'callback'      => $callback,
		'callback_args' => array(
			'class' => 'widefat'
		)
	);

	$callback_args = (array) $callback_args;

	if ( isset( $callback_args['description'] ) ) {
		$field['description'] = $callback_args['description'];
		unset( $callback_args['description'] );
	}

	$field['callback_args'] = wp_parse_args( $callback_args, $field['callback_args'] );

	if ( isset( $callback_args['name'] ) && ! empty( $callback_args['name'] ) ) {
		$field['name'] = $callback_args['name'];
	}

	return $field;
}

function hocwp_theme_backup_wp_content_folders_theme( $folders ) {
	if ( defined( 'HOCWP_THEME_DEVELOPING' ) && HOCWP_THEME_DEVELOPING ) {
		$folders   = (array) $folders;
		$folders[] = 'themes\hocwp-theme';
	}

	return $folders;
}

add_filter( 'hocwp_theme_backup_wp_content_folders', 'hocwp_theme_backup_wp_content_folders_theme' );

function hocwp_theme_widget_form_before( $instance, $widget ) {
	$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
	echo '<div class="hocwp-theme">';
	?>
	<p>
		<label for="<?php echo $widget->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'hocwp-theme' ); ?></label>
		<input class="widefat" id="<?php echo $widget->get_field_id( 'title' ); ?>"
		       name="<?php echo $widget->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>"/>
	</p>
	<?php
}

add_action( 'hocwp_theme_widget_form_before', 'hocwp_theme_widget_form_before', 10, 2 );

function hocwp_theme_widget_form_after() {
	echo '</div>';
}

add_action( 'hocwp_theme_widget_form_after', 'hocwp_theme_widget_form_after' );

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

function hocwp_theme_default_ads_positions( $positions ) {
	$positions['related_posts'] = __( 'Related posts', 'hocwp-theme' );

	return $positions;
}

add_filter( 'hocwp_theme_ads_positions', 'hocwp_theme_default_ads_positions' );

function hocwp_theme_mce_buttons_filter( $mce_buttons, $editor_id ) {
	if ( 'content' == $editor_id ) {
		array_splice( $mce_buttons, 13, 0, 'wp_page' );
	}

	return $mce_buttons;
}

add_filter( 'mce_buttons', 'hocwp_theme_mce_buttons_filter', 10, 2 );

function hocwp_theme_get_feed_items() {
	$tr_name = 'hocwp_theme_feed_items';

	if ( false === ( $feeds = get_transient( $tr_name ) ) ) {
		$url   = 'https://hocwp.net/feed/';
		$feeds = HT_Util()->get_feed_items( $url );

		if ( HT()->array_has_value( $feeds ) ) {
			set_transient( $tr_name, $feeds, DAY_IN_SECONDS );
		}
	}

	return $feeds;
}

function hocwp_theme_wp_dashboard_setup() {
	$feeds = hocwp_theme_get_feed_items();

	if ( HT()->array_has_value( $feeds ) ) {
		wp_add_dashboard_widget( 'news_from_hocwp_team', __( 'News From HocWP Team', 'hocwp-theme' ), 'hocwp_theme_news_from_hocwp_team_callback' );
	}
}

add_action( 'wp_dashboard_setup', 'hocwp_theme_wp_dashboard_setup' );

function hocwp_theme_news_from_hocwp_team_callback() {
	$feeds = hocwp_theme_get_feed_items();

	if ( HT()->array_has_value( $feeds ) ) {
		?>
		<div class="wordpress-news">
			<div class="rss-widget">
				<ul>
					<?php
					$count = count( $feeds );
					foreach ( $feeds as $key => $feed ) {
						$date      = $feed['date'];
						$timestamp = strtotime( $date );
						$style     = 'border-bottom: 1px dotted #eee;padding-bottom: 15px;';

						if ( $key == ( $count - 1 ) ) {
							$style = '';
						}
						?>
						<li style="<?php echo $style; ?>">
							<a class="rsswidget"
							   href="<?php echo $feed['permalink']; ?>"
							   style="font-weight: 400" target="_blank"><?php echo $feed['title']; ?></a>
							<time
								datetime="<?php echo mysql2date( 'D, d M Y H:i:s +0000', $date, false ); ?>"><?php echo date_i18n( get_option( 'date_format' ), $timestamp ); ?></time>
						</li>
						<?php
					}
					?>
				</ul>
			</div>
		</div>
		<?php
	}
}

require HOCWP_THEME_CORE_PATH . '/admin/ajax.php';

require HOCWP_THEME_CORE_PATH . '/admin/dashboard-widgets.php';