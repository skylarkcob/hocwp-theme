<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HOCWP_Theme_Walker_Nav_Menu_Edit' ) ) {
	require HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-walker-nav-menu-edit.php';
}

final class HOCWP_Theme_Meta_Menu extends HOCWP_Theme_Meta {
	public $menus = array();

	/*
	 * Add new meta fields to menus.
	 */
	public function __construct() {
		global $pagenow;

		if ( 'nav-menus.php' == $pagenow || 'admin-ajax.php' == $pagenow ) {
			$this->load_style( 'hocwp-theme-admin-style' );
			parent::__construct();

			$this->set_get_value_callback( 'get_post_meta' );
			$this->set_update_value_callback( 'update_post_meta' );

			add_filter( 'wp_setup_nav_menu_item', array( $this, 'setup_nav_menu_item' ), 21 );
			add_action( 'wp_update_nav_menu_item', array( $this, 'update_nav_menu_item' ), 21, 2 );
			add_filter( 'wp_edit_nav_menu_walker', array( $this, 'edit_nav_menu_walker' ), 21, 2 );
			add_action( 'hocwp_theme_nav_menu_edit_fields', array( $this, 'add_fields' ), 21, 4 );
			add_filter( 'hocwp_theme_meta_field', array( $this, 'meta_field_filter' ), 21, 2 );
			add_filter( 'manage_nav-menus_columns', array( $this, 'manage_nav_menus_columns_filter' ), 21 );
			add_action( 'admin_enqueue_scripts', array( $this, 'custom_admin_scripts' ), 21 );
		}
	}

	/*
	 * Load styles and scripts.
	 */
	public function custom_admin_scripts() {
		HT_Enqueue()->sortable();
	}

	/*
	 * Add meta fields to user columns.
	 */
	public function manage_nav_menus_columns_filter( $columns ) {
		foreach ( (array) $this->fields as $field ) {
			$columns[ $field['id'] ] = rtrim( $field['title'], ':' );
		}

		return $columns;
	}

	/*
	 * Update meta key for meta field.
	 */
	public function meta_field_filter( $field, $object ) {
		if ( $object instanceof HOCWP_Theme_Meta_Menu ) {
			$field['meta_key'] = $this->build_menu_item_meta_key( $this->get_name( $field, true ) );
		}

		return $field;
	}

	/*
	 * Add meta to menus.
	 */
	public function set_menus( $menus ) {
		if ( is_array( $menus ) ) {
			$this->menus = $menus;
		}
	}

	/*
	 * Add meta to menu.
	 */
	public function add_menu( $menu ) {
		$menu = wp_get_nav_menu_object( $menu );

		if ( $menu instanceof WP_Term ) {
			if ( ! is_array( $this->menus ) ) {
				$this->menus = array();
			}

			if ( ! isset( $this->menus[ $menu->term_id ] ) ) {
				$this->menus[ $menu->term_id ] = $menu;
			}
		}
	}

	/*
	 * Add meta to menu by location.
	 */
	public function add_menu_by_location( $location ) {
		$locations = get_nav_menu_locations();

		if ( is_array( $locations ) && isset( $locations[ $location ] ) ) {
			$this->add_menu( $locations[ $location ] );
		}
	}

	/*
	 * Add meta fields to menu item.
	 */
	public function add_fields( $item, $depth, $args, $id ) {
		if ( $item instanceof WP_Post ) {
			if ( is_callable( $this->callback ) ) {
				call_user_func( $this->callback, $item, $depth, $args, $id );
			} else {
				$this->callback( $item );
			}
		}
	}

	/*
	 * Default callback for menu item meta fields.
	 */
	private function callback( $item ) {
		if ( $item instanceof WP_Post ) {
			$options = get_user_option( 'managenav-menuscolumnshidden' );

			foreach ( (array) $this->fields as $field ) {
				$this->sanitize_field_data( $field, $item );

				$id    = $this->get_field_id( $field );
				$field = $this->sanitize_value( $item->ID, $field );
				$class = 'description description-wide field-' . $id;
				$class .= ' field-' . str_replace( '_', '-', $field['real_name'] );

				if ( HT()->in_array( $field['real_name'], $options ) ) {
					$class .= ' hidden-field';
				}
				?>
				<div class="<?php echo esc_attr( $class ); ?>">
					<label for="<?php echo $id; ?>"><?php echo $field['title']; ?></label>
					<?php
					unset( $field['callback_args']['label'] );
					call_user_func( $field['callback'], $field['callback_args'] );
					$desc = isset( $field['description'] ) ? $field['description'] : '';

					if ( ! empty( $desc ) ) {
						$p = new HOCWP_Theme_HTML_Tag( 'p' );
						$p->add_attribute( 'class', 'description' );
						$p->set_text( $desc );
						$p->output();
					}
					?>
				</div>
				<?php
			}
			?>
			<div class="custom-sortable">
				<fieldset>
					<legend><?php _e( 'Display:', 'hocwp-theme' ); ?></legend>
					<div class="sortable-inner">
						<?php
						$base_name = 'sortable';

						$value = get_post_meta( $item->ID, $this->build_menu_item_meta_key( $base_name ), true );

						$params = array(
							'id'        => $this->build_menu_item_field_id( $base_name, $item->ID ),
							'name'      => $this->build_menu_item_field_name( $base_name, $item->ID ),
							'label'     => '',
							'connects'  => false,
							'lists'     => array(
								'text'        => __( 'Text', 'hocwp-theme' ),
								'description' => __( 'Description', 'hocwp-theme' )
							),
							'list_type' => 'custom',
							'value'     => json_encode( $value )
						);

						foreach ( (array) $this->fields as $field ) {
							$this->sanitize_field_data( $field, $item );
							$sortable = isset( $field['callback_args']['sortable'] ) ? $field['callback_args']['sortable'] : '';

							if ( $sortable ) {
								$params['lists'][ $field['real_name'] ] = rtrim( $field['title'], ':' );
							}
						}

						HT_HTML_Field()->sortable( $params );
						?>
					</div>
				</fieldset>
			</div>
			<?php
		}
	}

	/*
	 * Sanitize field data to update meta key, real field name and menu meta field data.
	 */
	private function sanitize_field_data( &$field, $item ) {
		if ( ! ( $item instanceof WP_Post ) ) {
			$item = get_post( $item );
		}

		if ( ! ( $item instanceof WP_Post ) ) {
			return;
		}

		$field['meta_key']  = $this->build_menu_item_meta_key( $this->get_name( $field, true ) );
		$field['real_name'] = $field['callback_args']['name'];

		$class = isset( $field['callback_args']['class'] ) ? $field['callback_args']['class'] : '';
		$class .= ' code';
		$class = trim( $class );

		$name = $this->get_name( $field );
		$data = $this->build_menu_item_field_data( $name, $item->ID );

		$class .= ' ' . $data['class'];

		$field['callback_args']['name']  = $data['name'];
		$field['callback_args']['id']    = $data['id'];
		$field['callback_args']['class'] = trim( $class );

		$field['id'] = $data['id'];
	}

	/*
	 * Generate meta field id.
	 */
	public function build_menu_item_field_id( $field_name, $item_id ) {
		$id = str_replace( '_', '-', $field_name );

		return 'edit-menu-item-' . $id . '-' . $item_id;
	}

	/*
	 * Generate meta field name.
	 */
	public function build_menu_item_field_name( $field_name, $item_id ) {
		$name = str_replace( '_', '-', $field_name );

		return 'menu-item-' . $name . '[' . $item_id . ']';
	}

	/*
	 * Generate meta field class.
	 */
	public function build_menu_item_field_class( $field_name ) {
		$field_name = str_replace( '_', '-', $field_name );

		return 'edit-menu-item-' . $field_name;
	}

	/*
	 * Generate meta key for menu item to save into database.
	 */
	public function build_menu_item_meta_key( $key_name ) {
		$key_name = str_replace( '-', '_', $key_name );

		return '_menu_item_' . $key_name;
	}

	/*
	 * Generate field data array.
	 */
	public function build_menu_item_field_data( $field_name, $item_id ) {
		$result = array(
			'id'    => $this->build_menu_item_field_id( $field_name, $item_id ),
			'name'  => $this->build_menu_item_field_name( $field_name, $item_id ),
			'class' => $this->build_menu_item_field_class( $field_name )
		);

		return $result;
	}

	/*
	 * Save menu item meta into database.
	 */
	public function update_nav_menu_item( $menu_id, $menu_item_db_id ) {
		foreach ( (array) $this->fields as $field ) {
			$this->sanitize_field_data( $field, $menu_id );
			$name = $this->get_name( $field, true );

			if ( isset( $_POST[ $name ][ $menu_item_db_id ] ) ) {
				$value = $_POST[ $name ][ $menu_item_db_id ];
				update_post_meta( $menu_item_db_id, $field['meta_key'], $value );
			}
		}

		$base_name = 'sortable';

		$name = $this->build_menu_item_field_name( $base_name, $menu_item_db_id );
		$name = $this->get_base_name( $name );

		if ( isset( $_POST[ $name ][ $menu_item_db_id ] ) ) {
			$value = $_POST[ $name ][ $menu_item_db_id ];
			$value = HT()->json_string_to_array( $value );

			update_post_meta( $menu_item_db_id, $this->build_menu_item_meta_key( $base_name ), $value );
		}
	}

	/*
	 * Add meta properties for menu item object.
	 */
	public function setup_nav_menu_item( $menu_item ) {
		if ( $menu_item instanceof WP_Post && 'nav_menu_item' == $menu_item->post_type ) {
			foreach ( (array) $this->fields as $field ) {
				$this->sanitize_field_data( $field, $menu_item );
				$name = $field['real_name'];

				$menu_item->{$name} = get_post_meta( $menu_item->ID, $this->build_menu_item_meta_key( $name ), true );
			}
		}

		return $menu_item;
	}

	/*
	 * Change walker for nav menu edit.
	 */
	public function edit_nav_menu_walker( $walker, $menu_id ) {
		$menu = wp_get_nav_menu_object( $menu_id );

		// Only apply walker for specific menu
		if ( $menu instanceof WP_Term && isset( $this->menus[ $menu_id ] ) ) {
			$walker = 'HOCWP_Theme_Walker_Nav_Menu_Edit';
		}

		return $walker;
	}
}

function hocwp_theme_add_menu_meta_columns() {
	$list_socials = HT_Options()->get_tab( 'list_socials', '', 'social' );

	if ( ! empty( $list_socials ) ) {
		$list_socials = explode( ',', $list_socials );
		$list_socials = array_map( 'trim', $list_socials );

		$list_socials = array_unique( $list_socials );
		$list_socials = array_filter( $list_socials );

		if ( HT()->array_has_value( $list_socials ) ) {
			//add_meta_box( 'hocwp-theme-socials', __( 'List Socials', 'hocwp-theme' ), 'hocwp_theme_admin_menu_list_socials_meta_box', 'nav-menus', 'side', 'low', array( 'list_socials' => $list_socials ) );
		}
	}
}

add_action( 'load-nav-menus.php', 'hocwp_theme_add_menu_meta_columns' );
add_action( 'load-admin-ajax.php', 'hocwp_theme_add_menu_meta_columns' );

function hocwp_theme_admin_menu_list_socials_meta_box( $object, $box ) {
	$args = isset( $box['args'] ) ? $box['args'] : '';

	if ( HT()->array_has_value( $args ) ) {
		$list_socials = isset( $args['list_socials'] ) ? $args['list_socials'] : '';

		if ( HT()->array_has_value( $list_socials ) ) {
			global $nav_menu_selected_id;

			$taxonomy_name = 'list-socials';

			// Paginate browsing for large numbers of objects.
			$per_page = 50;
			$pagenum  = isset( $_REQUEST[ $taxonomy_name . '-tab' ] ) && isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1;
			$offset   = 0 < $pagenum ? $per_page * ( $pagenum - 1 ) : 0;

			$terms = array_slice( $list_socials, $offset, $per_page );

			if ( ! $terms || is_wp_error( $terms ) ) {
				echo '<p>' . __( 'No items.', 'hocwp-theme' ) . '</p>';

				return;
			}

			$num_pages = ceil( count( $list_socials ) / $per_page );

			$page_links = paginate_links(
				array(
					'base'               => add_query_arg(
						array(
							$taxonomy_name . '-tab' => 'all',
							'paged'                 => '%#%',
							'item-type'             => 'taxonomy',
							'item-object'           => $taxonomy_name
						)
					),
					'format'             => '',
					'prev_text'          => '<span aria-label="' . esc_attr__( 'Previous page', 'hocwp-theme' ) . '">' . __( '&laquo;', 'hocwp-theme' ) . '</span>',
					'next_text'          => '<span aria-label="' . esc_attr__( 'Next page', 'hocwp-theme' ) . '">' . __( '&raquo;', 'hocwp-theme' ) . '</span>',
					'before_page_number' => '<span class="screen-reader-text">' . __( 'Page', 'hocwp-theme' ) . '</span> ',
					'total'              => $num_pages,
					'current'            => $pagenum
				)
			);

			$walker = new Walker_Nav_Menu_Checklist();

			$current_tab = 'most-used';

			if ( isset( $_REQUEST[ $taxonomy_name . '-tab' ] ) && in_array( $_REQUEST[ $taxonomy_name . '-tab' ], array(
					'all',
					'most-used',
					'search'
				) )
			) {
				$current_tab = $_REQUEST[ $taxonomy_name . '-tab' ];
			}

			if ( ! empty( $_REQUEST[ 'quick-search-taxonomy-' . $taxonomy_name ] ) ) {
				$current_tab = 'search';
			}

			$removed_args = array(
				'action',
				'customlink-tab',
				'edit-menu-item',
				'menu-item',
				'page-tab',
				'_wpnonce',
			);

			$most_used_url = '';
			$view_all_url  = '';
			$search_url    = '';

			if ( $nav_menu_selected_id ) {
				$most_used_url = esc_url( add_query_arg( $taxonomy_name . '-tab', 'most-used', remove_query_arg( $removed_args ) ) );
				$view_all_url  = esc_url( add_query_arg( $taxonomy_name . '-tab', 'all', remove_query_arg( $removed_args ) ) );
				$search_url    = esc_url( add_query_arg( $taxonomy_name . '-tab', 'search', remove_query_arg( $removed_args ) ) );
			}
			?>
			<div id="taxonomy-<?php echo $taxonomy_name; ?>" class="taxonomydiv">
				<ul id="taxonomy-<?php echo $taxonomy_name; ?>-tabs" class="taxonomy-tabs add-menu-item-tabs">
					<li <?php echo( 'most-used' == $current_tab ? ' class="tabs"' : '' ); ?>>
						<a class="nav-tab-link" data-type="tabs-panel-<?php echo esc_attr( $taxonomy_name ); ?>-pop"
						   href="<?php echo $most_used_url; ?>#tabs-panel-<?php echo $taxonomy_name; ?>-pop">
							<?php esc_html_e( 'Most Used', 'hocwp-theme' ); ?>
						</a>
					</li>
					<li <?php echo( 'all' == $current_tab ? ' class="tabs"' : '' ); ?>>
						<a class="nav-tab-link" data-type="tabs-panel-<?php echo esc_attr( $taxonomy_name ); ?>-all"
						   href="<?php echo $view_all_url; ?>#tabs-panel-<?php echo $taxonomy_name; ?>-all">
							<?php _e( 'View All', 'hocwp-theme' ); ?>
						</a>
					</li>
					<li <?php echo( 'search' == $current_tab ? ' class="tabs"' : '' ); ?>>
						<a class="nav-tab-link"
						   data-type="tabs-panel-search-taxonomy-<?php echo esc_attr( $taxonomy_name ); ?>"
						   href="<?php echo $search_url; ?>#tabs-panel-search-taxonomy-<?php echo $taxonomy_name; ?>">
							<?php _e( 'Search', 'hocwp-theme' ); ?>
						</a>
					</li>
				</ul>
				<!-- .taxonomy-tabs -->

				<div id="tabs-panel-<?php echo $taxonomy_name; ?>-pop"
				     class="tabs-panel <?php echo( 'most-used' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' ); ?>"
				     role="region" aria-label="<?php esc_attr_e( 'Most Used', 'hocwp-theme' ); ?>" tabindex="0">
					<ul id="<?php echo $taxonomy_name; ?>checklist-pop" class="categorychecklist form-no-clear">
						<?php
						$popular_terms = array();

						foreach ( (array) $list_socials as $social ) {
							$item = array(
								'url'            => HT_Options()->get_tab( $social . '_url', '', 'social' ),
								'icon'           => HT_Options()->get_tab( $social . '_icon', '', 'social' ),
								'type'           => 'list-socials',
								'name'           => ucwords( $social ),
								'menu_item_type' => 'custom'
							);

							$popular_terms[] = json_decode( json_encode( $item ) );
						}

						$args['walker'] = $walker;
						echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $popular_terms ), 0, (object) $args );
						?>
					</ul>
				</div>
				<!-- /.tabs-panel -->

				<p class="button-controls wp-clearfix"
				   data-items-type="taxonomy-<?php echo esc_attr( $taxonomy_name ); ?>">
			<span class="list-controls hide-if-no-js">
				<input type="checkbox"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?>
				       id="<?php echo esc_attr( $taxonomy_name . '-tab' ); ?>" class="select-all"/>
				<label for="<?php echo esc_attr( $taxonomy_name . '-tab' ); ?>"><?php _e( 'Select All', 'hocwp-theme' ); ?></label>
			</span>

			<span class="add-to-menu">
				<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?>
				       class="button submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'hocwp-theme' ); ?>"
				       name="add-taxonomy-menu-item"
				       id="<?php echo esc_attr( 'submit-taxonomy-' . $taxonomy_name ); ?>"/>
				<span class="spinner"></span>
			</span>
				</p>

			</div>
			<?php
		}
	}
}

function hocwp_theme_wp_setup_nav_menu_item_admin_column_filter( $menu_item ) {
	if ( is_object( $menu_item ) && isset( $menu_item->type ) && 'list-socials' == $menu_item->type ) {
		if ( HOCWP_DOING_AJAX ) {
			$menu_item = array(
				'menu-item-type'  => 'custom',
				'menu-item-url'   => $menu_item->url,
				'menu-item-title' => $menu_item->name
			);

			return json_decode( json_encode( $menu_item ) );
		}

		$menu_item->ID               = 0;
		$menu_item->db_id            = 0;
		$menu_item->menu_item_parent = 0;
		$menu_item->object_id        = 0;
		$menu_item->post_parent      = 0;
		$menu_item->type             = 'custom';

		$menu_item->object     = '';
		$menu_item->type_label = '';

		$menu_item->title       = $menu_item->name;
		$menu_item->target      = '';
		$menu_item->attr_title  = '';
		$menu_item->description = '';
		$menu_item->classes     = array();
		$menu_item->xfn         = '';
	}

	return $menu_item;
}

//add_filter( 'wp_setup_nav_menu_item', 'hocwp_theme_wp_setup_nav_menu_item_admin_column_filter' );