<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_Plugin_Install_List_Table' ) ) {
	load_template( ABSPATH . 'wp-admin/includes/class-wp-plugin-install-list-table.php' );
}

class HOCWP_Theme_Plugin_Install_List_Table extends WP_Plugin_Install_List_Table {
	private $error;

	public function prepare_items() {
		load_template( ABSPATH . 'wp-admin/includes/plugin-install.php' );

		global $tabs, $tab, $paged, $type, $term;

		wp_reset_vars( array( 'tab' ) );

		$paged = $this->get_pagenum();

		$per_page = 12;

		// These are the tabs which are shown on the page
		$tabs = array();

		$tabs['recommended'] = array(
			'text'        => _x( 'Recommended', 'Plugin Installer', 'hocwp-theme' ),
			'description' => __( 'These suggestions are based on the plugins you and other users have installed.', 'hocwp-theme' )
		);

		if ( HOCWP_Theme::array_has_value( HOCWP_Theme_Requirement::get_required_plugins() ) ) {
			$tabs['required'] = array(
				'text'        => _x( 'Required', 'Plugin Installer', 'hocwp-theme' ),
				'description' => __( 'You must install these required plugins for theme can run normally.', 'hocwp-theme' )
			);
		}

		$nonmenu_tabs = array( 'plugin-information' ); // Valid actions to perform which do not have a Menu item.

		/**
		 * Filters the tabs shown on the Plugin Install screen.
		 *
		 * @since 2.7.0
		 *
		 * @param array $tabs The tabs shown on the Plugin Install screen. Defaults include 'featured', 'popular',
		 *                    'recommended', 'favorites', and 'upload'.
		 */
		$tabs = apply_filters( 'install_plugins_tabs', $tabs );

		/**
		 * Filters tabs not associated with a menu item on the Plugin Install screen.
		 *
		 * @since 2.7.0
		 *
		 * @param array $nonmenu_tabs The tabs that don't have a Menu item on the Plugin Install screen.
		 */
		$nonmenu_tabs = apply_filters( 'install_plugins_nonmenu_tabs', $nonmenu_tabs );

		// If a non-valid menu tab has been selected, And it's not a non-menu action.
		if ( empty( $tab ) || ( ! isset( $tabs[ $tab ] ) && ! in_array( $tab, (array) $nonmenu_tabs ) ) ) {
			$tab = key( $tabs );
		}

		$total_items = 0;

		if ( 'required' == $tab ) {
			$plugins = HOCWP_Theme_Requirement::get_required_plugins();
			$lists   = array();

			foreach ( $plugins as $name ) {
				$api = HT_Util()->get_wp_plugin_info( $name );

				if ( ! is_wp_error( $api ) ) {
					$lists[] = $api;
				}
			}

			$this->items = $lists;
			$total_items = count( $lists );
		} else {
			$args = array(
				'page'              => $paged,
				'per_page'          => $per_page,
				'fields'            => array(
					'last_updated'    => true,
					'icons'           => true,
					'active_installs' => true
				),
				'user'              => 'hocwp',
				'locale'            => get_user_locale(),
				'installed_plugins' => $this->get_installed_plugin_slugs(),
			);

			/**
			 * Filters API request arguments for each Plugin Install screen tab.
			 *
			 * The dynamic portion of the hook name, `$tab`, refers to the plugin install tabs.
			 * Default tabs include 'featured', 'popular', 'recommended', 'favorites', and 'upload'.
			 *
			 * @since 3.7.0
			 *
			 * @param array|bool $args Plugin Install API arguments.
			 */
			$args = apply_filters( "install_plugins_table_api_args_{$tab}", $args );

			if ( ! $args ) {
				return;
			}

			$tr_name = 'hocwp_theme_list_plugins_api_' . md5( json_encode( $args ) );

			if ( false === ( $api = get_transient( $tr_name ) ) ) {
				$api = plugins_api( 'query_plugins', $args );

				if ( ! is_wp_error( $api ) ) {
					set_transient( $tr_name, $api, DAY_IN_SECONDS );
				}

			}

			if ( is_wp_error( $api ) ) {
				$this->error = $api;

				return;
			}

			$this->items = (array) $api->plugins;
			$total_items = $api->info['results'];

			if ( isset( $api->info['groups'] ) ) {
				$this->groups = $api->info['groups'];
			}

		}

		if ( $this->orderby ) {
			uasort( $this->items, array( $this, 'order_callback' ) );
		}

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
		) );
	}

	protected function get_views() {
		global $tabs, $tab;

		$display_tabs = array();
		$url          = self_admin_url( 'themes.php?page=hocwp_theme_plugins' );

		foreach ( (array) $tabs as $action => $text ) {
			$class = ( $action === $tab ) ? ' current' : '';
			$href  = add_query_arg( 'tab', $action, $url );

			if ( is_array( $text ) ) {
				$text = isset( $text['text'] ) ? $text['text'] : ucwords( $action );
			}

			$display_tabs[ 'plugin-install-' . $action ] = "<a href='$href' class='$class'>$text</a>";
		}

		// No longer a real tab.
		unset( $display_tabs['plugin-install-upload'] );

		return $display_tabs;
	}

	public function views() {
		$views = $this->get_views();

		/** This filter is documented in wp-admin/inclues/class-wp-list-table.php */
		$views = apply_filters( "views_{$this->screen->id}", $views );

		$this->screen->render_screen_reader_content( 'heading_views' );
		?>
		<div class="wp-filter">
			<ul class="filter-links">
				<?php
				if ( ! empty( $views ) ) {
					foreach ( $views as $class => $view ) {
						$views[ $class ] = "\t<li class='$class'>$view";
					}

					echo implode( " </li>\n", $views ) . "</li>\n";
				}
				?>
			</ul>
		</div>
		<?php
	}
}