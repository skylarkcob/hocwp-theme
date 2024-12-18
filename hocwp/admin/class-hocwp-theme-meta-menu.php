<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HOCWP_Theme_Walker_Nav_Menu_Edit' ) ) {
	require( HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-walker-nav-menu-edit.php' );
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
		ht_enqueue()->sortable();
	}

	/*
	 * Add meta fields to user columns.
	 */
	public function manage_nav_menus_columns_filter( $columns ) {
		foreach ( (array) $this->fields as $field ) {
			if ( is_array( $field ) && isset( $field['title'] ) ) {
				$columns[ $field['id'] ] = rtrim( $field['title'], ':' );
			}
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

				if ( ht()->in_array( $field['real_name'], $options ) ) {
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

							if ( $sortable && is_array( $field ) && isset( $field['title'] ) ) {
								$params['lists'][ $field['real_name'] ] = rtrim( $field['title'], ':' );
							}
						}

						ht_html_field()->sortable( $params );
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
			} elseif ( isset( $_POST[ 'menu-item-' . $name ][ $menu_item_db_id ] ) ) {
				$value = $_POST[ 'menu-item-' . $name ][ $menu_item_db_id ];
				update_post_meta( $menu_item_db_id, $field['meta_key'], $value );
			}
		}

		$base_name = 'sortable';

		$name = $this->build_menu_item_field_name( $base_name, $menu_item_db_id );
		$name = $this->get_base_name( $name );

		if ( isset( $_POST[ $name ][ $menu_item_db_id ] ) ) {
			$value = $_POST[ $name ][ $menu_item_db_id ];
			$value = ht()->json_string_to_array( $value );

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

/**
 * Add custom list widgets to left menu sidebar
 */
function hocwp_theme_add_menu_meta_columns() {
	$list_socials = ht_options()->get_tab( 'list_socials', '', 'social' );

	if ( ! empty( $list_socials ) ) {
		$list_socials = explode( ',', $list_socials );
		$list_socials = array_map( 'trim', $list_socials );

		$list_socials = array_unique( $list_socials );
		$list_socials = array_filter( $list_socials );

		if ( ht()->array_has_value( $list_socials ) ) {
			add_meta_box( 'hocwp-theme-socials', __( 'List Socials', 'hocwp-theme' ), 'hocwp_theme_admin_menu_custom_list_meta_box', 'nav-menus', 'side', 'low', array(
				'lists' => $list_socials,
				'id'    => 'list-socials'
			) );
		}
	}

	// List post type archive links
	$args = array(
		'public'      => true,
		'has_archive' => true
	);

	$lists = get_post_types( $args, 'objects' );

	if ( ht()->array_has_value( $lists ) ) {
		add_meta_box( 'hocwp-theme-post-types', __( 'List Post Types', 'hocwp-theme' ), 'hocwp_theme_admin_menu_custom_list_meta_box', 'nav-menus', 'side', 'low', array(
			'lists' => $lists,
			'id'    => 'list-post-types'
		) );
	}
}

add_action( 'load-nav-menus.php', 'hocwp_theme_add_menu_meta_columns' );
add_action( 'load-admin-ajax.php', 'hocwp_theme_add_menu_meta_columns' );

/**
 * Generate widgets for menu check list sidebar
 *
 * @param $object
 * @param $box
 */
function hocwp_theme_admin_menu_custom_list_meta_box( $object, $box ) {
	$args = isset( $box['args'] ) ? $box['args'] : '';

	if ( ht()->array_has_value( $args ) ) {
		$lists = isset( $args['lists'] ) ? $args['lists'] : '';

		if ( ht()->array_has_value( $lists ) ) {
			global $nav_menu_selected_id;

			$base_name = $args['id'] ?? 'custom-list';

			if ( ! $lists || is_wp_error( $lists ) ) {
				echo '<p>' . __( 'No items.', 'hocwp-theme' ) . '</p>';

				return;
			}

			$walker = new Walker_Nav_Menu_Checklist();
			?>
            <div id="box-<?php echo $base_name; ?>" class="custom-box hocwp-theme-meta-box taxonomydiv">
                <div id="tabs-panel-<?php echo $base_name; ?>-pop" class="tabs-panel tabs-panel-active" tabindex="0">
                    <ul id="<?php echo $base_name; ?>-checklist-pop"
                        class="item-check-list hocwp-theme-custom-list categorychecklist form-no-clear">
						<?php
						$items = array();

						foreach ( (array) $lists as $list_item ) {
							$item = '';

							if ( 'list-socials' == $base_name ) {
								$url = ht_options()->get_tab( $list_item . '_url', '', 'social' );
								$url = add_query_arg( 'theme_list_social', 1, $url );

								$item = array(
									'url'            => $url,
									'icon'           => ht_options()->get_tab( $list_item . '_icon', '', 'social' ),
									'type'           => $base_name,
									'name'           => ucwords( $list_item ),
									'menu_item_type' => 'custom'
								);
							} elseif ( 'list-post-types' == $base_name ) {
								$url = get_post_type_archive_link( $list_item->name );
								$url = add_query_arg( 'post_type', $list_item->name, $url );

								$item = array(
									'url'            => $url,
									'icon'           => '',
									'type'           => $base_name,
									'name'           => sprintf( '%s (%s)', $list_item->labels->singular_name, $list_item->name ),
									'menu_item_type' => 'custom'
								);
							}

							if ( ht()->array_has_value( $item ) ) {
								$items[] = json_decode( json_encode( $item ) );
							}
						}

						$args['walker'] = $walker;
						echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $items ), 0, (object) $args );
						?>
                    </ul>
                </div>
                <!-- /.tabs-panel -->

                <p class="button-controls wp-clearfix" data-items-type="custom">
					<span class="list-controls hide-if-no-js">
						<input type="checkbox"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?>
						       id="<?php echo esc_attr( $base_name . '-tab' ); ?>" class="select-all"/>
						<label
                                for="<?php echo esc_attr( $base_name . '-tab' ); ?>"><?php _e( 'Select All', 'hocwp-theme' ); ?></label>
					</span>
                    <span class="add-to-menu">
						<button type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?>
						        class="button disabled right"
                                name="add-custom-menu-item"
                                id="<?php echo esc_attr( 'submit-custom-' . $base_name ); ?>"
                                aria-label="<?php esc_attr_e( 'Add to Menu', 'hocwp-theme' ); ?>"><?php esc_html_e( 'Add to Menu', 'hocwp-theme' ); ?></button>
						<span class="spinner"></span>
					</span>
                </p>
            </div>
			<?php
		}
	}
}

function hocwp_theme_wp_setup_nav_menu_item_admin_column_filter( $menu_item ) {
	if ( is_object( $menu_item ) && isset( $menu_item->type ) ) {
		$change = false;

		if ( 'list-socials' == $menu_item->type ) {
			$menu_item->attr_title  = ( isset( $menu_item->icon ) && ! empty( $menu_item->icon ) ) ? $menu_item->icon : $menu_item->name;
			$menu_item->list_social = 1;
			$menu_item->classes     = array( 'social-item' );
			$menu_item->title       = $menu_item->name;
			$change                 = true;
		} elseif ( 'list-post-types' == $menu_item->type ) {
			$menu_item->attr_title     = '';
			$menu_item->list_post_type = 1;
			$menu_item->classes        = array( 'post-type-item' );
			$menu_item->title          = $menu_item->name;
			$change                    = true;
		}

		if ( $change ) {
			$menu_item->type             = 'custom';
			$menu_item->ID               = 0;
			$menu_item->db_id            = 0;
			$menu_item->menu_item_parent = 0;
			$menu_item->object_id        = 0;
			$menu_item->post_parent      = 0;
			$menu_item->object           = '';
			$menu_item->type_label       = '';
			$menu_item->target           = '';
			$menu_item->description      = '';
			$menu_item->xfn              = '';
		}
	}

	return $menu_item;
}

add_filter( 'wp_setup_nav_menu_item', 'hocwp_theme_wp_setup_nav_menu_item_admin_column_filter' );

function hocwp_theme_wp_add_nav_menu_item_action( $menu_id, $menu_item_db_id, $args ) {
	if ( ! ht()->is_positive_number( $menu_id ) && ht()->is_positive_number( $menu_item_db_id ) ) {
		$object = isset( $args['menu-item-object'] ) ? $args['menu-item-object'] : '';

		if ( 'custom' == $object ) {
			$url = isset( $args['menu-item-url'] ) ? $args['menu-item-url'] : '';

			if ( ! empty( $url ) ) {
				$params = ht()->get_params_from_url( $url );

				if ( isset( $params['theme_list_social'] ) && 1 == $params['theme_list_social'] ) {
					update_post_meta( $menu_item_db_id, 'theme_list_social', 1 );
				} elseif ( isset( $params['post_type'] ) && ! empty( $params['post_type'] ) ) {
					update_post_meta( $menu_item_db_id, 'post_type', $params['post_type'] );
				}
			}
		}
	}
}

add_action( 'wp_add_nav_menu_item', 'hocwp_theme_wp_add_nav_menu_item_action', 10, 3 );
add_action( 'wp_update_nav_menu_item', 'hocwp_theme_wp_add_nav_menu_item_action', 10, 3 );