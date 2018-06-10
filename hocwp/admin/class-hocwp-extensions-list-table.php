<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	load_template( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class HOCWP_Extensions_List_Table extends WP_List_Table {

	private $extension_status = array(
		'active',
		'inactive'
	);

	public function __construct() {
		global $status, $page;

		parent::__construct( array(
			'singular' => __( 'extension', 'hocwp-theme' ),
			'plural'   => __( 'extensions', 'hocwp-theme' ),
			'ajax'     => false
		) );

		$status = 'all';

		if ( isset( $_REQUEST['extension_status'] ) && in_array( $_REQUEST['extension_status'], $this->extension_status ) ) {
			$status = $_REQUEST['extension_status'];
		}

		if ( isset( $_REQUEST['s'] ) ) {
			$_SERVER['REQUEST_URI'] = add_query_arg( 's', wp_unslash( $_REQUEST['s'] ) );
		}

		$page = $this->get_pagenum();
	}

	protected function get_table_classes() {
		return array( 'widefat', $this->_args['plural'], 'plugins' );
	}

	public function ajax_user_can() {
		return current_user_can( 'manage_options' );
	}

	public function prepare_items() {
		global $hocwp_theme, $status, $totals, $page, $orderby, $order, $s;

		$status = isset( $_GET['extension_status'] ) ? $_GET['extension_status'] : 'all';

		wp_reset_vars( array( 'orderby', 'order' ) );
		$all_extensions   = $this->get_extensions();
		$required_exts    = HT_Requirement()->get_required_extensions();
		$recommended_exts = HT_Requirement()->get_recommended_extensions();

		$extensions = array(
			'all'         => $all_extensions,
			'search'      => array(),
			'active'      => array(),
			'inactive'    => array(),
			'required'    => array(),
			'recommended' => array()
		);

		foreach ( $extensions['all'] as $key => $data ) {
			$extension = new HOCWP_Theme_Extension( $data['dir'] );

			if ( HT_extension()->is_active( $key ) ) {
				$extensions['active'][ $key ] = $data;
			} else {
				$extensions['inactive'][ $key ] = $data;
			}

			if ( in_array( $extension->basename, $required_exts ) ) {
				$extensions['required'][ $key ] = $data;
			}

			if ( in_array( $extension->basename, $recommended_exts ) ) {
				$extensions['recommended'][ $key ] = $data;
			}
		}

		$screen = $this->screen;

		if ( empty( $s ) ) {
			$s = isset( $_REQUEST['s'] ) ? $_REQUEST['s'] : '';
		}

		if ( strlen( $s ) ) {
			$status = 'search';

			$extensions[ $status ] = array_filter( $extensions['all'], array( $this, '_search_callback' ) );
		}

		$totals = array();

		foreach ( $extensions as $type => $list ) {
			$totals[ $type ] = count( $list );
		}

		if ( empty( $extensions[ $status ] ) && ! in_array( $status, array( 'all', 'search' ) ) ) {
			$status = 'all';
		}

		$this->items = $extensions[ $status ];

		$total_this_page = $totals[ $status ];

		if ( ! $orderby ) {
			$orderby = 'Name';
		} else {
			$orderby = ucfirst( $orderby );
		}

		if ( ! $order ) {
			$order = 'asc';
		}

		$order = strtoupper( $order );
		uasort( $this->items, array( $this, '_order_callback' ) );

		$extensions_per_page = $this->get_items_per_page( str_replace( '-', '_', $screen->id . '_per_page' ), get_option( 'posts_per_page' ) );

		$start = ( $page - 1 ) * $extensions_per_page;

		if ( $total_this_page > $extensions_per_page ) {
			$this->items = array_slice( $this->items, $start, $extensions_per_page );
		}

		$this->set_pagination_args( array(
			'total_items' => $total_this_page,
			'per_page'    => $extensions_per_page,
		) );

		$columns  = $this->get_columns();
		$hidden   = get_hidden_columns( $screen );
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
	}

	private function get_extensions() {
		return HT_Extension()->get_extensions();
	}

	public function _search_callback( $extension ) {
		global $s;

		foreach ( $extension as $value ) {
			if ( is_string( $value ) && false !== stripos( strip_tags( $value ), urldecode( $s ) ) ) {
				return true;
			}
		}

		return false;
	}

	public function _order_callback( $extension_a, $extension_b ) {
		global $orderby, $order;
		$a = $extension_a[ $orderby ];
		$b = $extension_b[ $orderby ];

		if ( $a == $b ) {
			return 0;
		}

		if ( 'DESC' === $order ) {
			return strcasecmp( $b, $a );
		} else {
			return strcasecmp( $a, $b );
		}
	}

	public function no_items() {
		global $hocwp_theme;

		if ( ! empty( $_REQUEST['s'] ) ) {
			$s = esc_html( wp_unslash( $_REQUEST['s'] ) );
			printf( __( 'No extensions found for &#8220;%s&#8221;.', 'hocwp-theme' ), $s );
		} elseif ( ! empty( $hocwp_theme->extensions['all'] ) ) {
			_e( 'No extensions found.', 'hocwp-theme' );
		} else {
			_e( 'You do not appear to have any extensions available at this time.', 'hocwp-theme' );
		}
	}

	function get_columns() {
		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'name'        => __( 'Name', 'hocwp-theme' ),
			'description' => __( 'Description', 'hocwp-theme' )
		);

		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'name'        => array( 'name', true ),
			'description' => array( 'description', false )
		);

		return $sortable_columns;
	}

	protected function get_views() {
		global $totals, $status;
		$status_links = array();

		foreach ( $totals as $type => $count ) {
			if ( ! $count ) {
				continue;
			}

			switch ( $type ) {
				case 'all':
					$text = _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $count, 'hocwp theme extensions', 'hocwp-theme' );
					break;
				case 'active':
					$text = _n( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', $count, 'hocwp-theme' );
					break;
				case 'inactive':
					$text = _n( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', $count, 'hocwp-theme' );
					break;
				case 'required':
					$text = _n( 'Required <span class="count">(%s)</span>', 'Required <span class="count">(%s)</span>', $count, 'hocwp-theme' );
					break;
				case 'recommended':
					$text = _n( 'Recommended <span class="count">(%s)</span>', 'Recommended <span class="count">(%s)</span>', $count, 'hocwp-theme' );
					break;
			}

			if ( 'search' !== $type ) {
				$status_links[ $type ] = sprintf( "<a href='%s' %s>%s</a>",
					add_query_arg( 'extension_status', $type, 'themes.php?page=hocwp_theme&tab=extension' ),
					( $type === $status ) ? ' class="current"' : '',
					sprintf( $text, number_format_i18n( $count ) )
				);
			}
		}

		return $status_links;
	}

	function get_bulk_actions() {
		global $status;
		$actions = array();

		if ( 'active' != $status ) {
			$actions['activate'] = __( 'Activate', 'hocwp-theme' );
		}

		if ( 'inactive' != $status ) {
			$actions['deactivate'] = __( 'Deactivate', 'hocwp-theme' );
		}

		return $actions;
	}

	public function single_row( $item ) {
		global $status, $page, $s, $totals;
		list( $extension_file, $extension_data ) = array( $item['dir'], $item );
		$context = $status;
		$screen  = $this->screen;

		$actions = array(
			'deactivate' => '',
			'activate'   => ''
		);

		$is_active = HT_extension()->is_active( $extension_file );

		$baseurl = 'themes.php?page=hocwp_theme&tab=extension&extension=' . $extension_file . '&extension_status=' . $context . '&paged=' . $page . '&s=' . $s;

		if ( $is_active ) {
			$actions['deactivate'] = '<a href="' . wp_nonce_url( $baseurl . '&action=deactivate', 'deactivate-extension_' . $extension_file ) . '" aria-label="' . esc_attr( sprintf( _x( 'Deactivate %s', 'hocwp theme extension', 'hocwp-theme' ), $extension_data['Name'] ) ) . '">' . __( 'Deactivate', 'hocwp-theme' ) . '</a>';
		} else {
			$actions['activate'] = '<a href="' . wp_nonce_url( $baseurl . '&action=activate', 'activate-extension_' . $extension_file ) . '" class="edit" aria-label="' . esc_attr( sprintf( _x( 'Activate %s', 'hocwp-theme-extension', 'hocwp-theme' ), $extension_data['Name'] ) ) . '">' . __( 'Activate', 'hocwp-theme' ) . '</a>';
		}

		$actions        = array_filter( $actions );
		$class          = $is_active ? 'active' : 'inactive';
		$checkbox_id    = "checkbox_" . md5( $extension_data['Name'] );
		$checkbox       = "<label class='screen-reader-text' for='" . $checkbox_id . "' >" . sprintf( __( 'Select %s', 'hocwp-theme' ), $extension_data['Name'] ) . "</label>"
		                  . "<input type='checkbox' name='checked[]' value='" . esc_attr( $extension_file ) . "' id='" . $checkbox_id . "' />";
		$description    = '<p>' . ( $extension_data['Description'] ? $extension_data['Description'] : '&nbsp;' ) . '</p>';
		$extension_name = $extension_data['Name'];

		printf( '<tr class="%s" data-plugin="%s">',
			esc_attr( $class ),
			esc_attr( $extension_file )
		);

		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$extra_classes = '';
			if ( in_array( $column_name, $hidden ) ) {
				$extra_classes = ' hidden';
			}

			switch ( $column_name ) {
				case 'cb':
					echo "<th scope='row' class='check-column'>$checkbox</th>";
					break;
				case 'name':
					echo "<td class='plugin-title column-primary'><strong>$extension_name</strong>";
					echo $this->row_actions( $actions, true );
					echo "</td>";
					break;
				case 'description':
					$classes = 'column-description desc';

					echo "<td class='$classes{$extra_classes}'>
						<div class='extension-description'>$description</div></td>";
					break;
				default:
					$classes = "$column_name column-$column_name $class";
					echo "<td class='$classes{$extra_classes}'>";
					do_action( 'manage_extensions_custom_column', $column_name, $extension_file, $extension_data );
					echo "</td>";
			}
		}

		echo "</tr>";
		do_action( 'after_hocwp_theme_extension_row', $extension_file, $extension_data, $status );
		do_action( "after_hocwp_theme_extension_row_{$extension_file}", $extension_file, $extension_data, $status );
	}

	private function active_list_control( $extension_file, $options = '' ) {
		if ( empty( $options ) ) {
			$options = $GLOBALS['hocwp_theme']->active_extensions;
		}

		$extension_file = HT_Extension()->sanitize_file( $extension_file );
		$action         = $this->current_action();

		switch ( $action ) {
			case 'activate':
				if ( ! in_array( $extension_file, $options ) ) {
					$options[] = $extension_file;
				}

				do_action( 'hocwp_theme_extension_' . $extension_file . '_activation' );
				break;
			case 'deactivate':
				unset( $options[ array_search( $extension_file, $options ) ] );

				do_action( 'hocwp_theme_extension_' . $extension_file . '_deactivation' );
				break;
		}

		return $options;
	}

	public function process_bulk_action() {
		$action = $this->current_action();

		$extension = isset( $_GET['extension'] ) ? $_GET['extension'] : '';
		$extension = str_replace( '\\\\', '\\', $extension );

		if ( isset( $_REQUEST['_wpnonce'] ) ) {
			$nonce = $_REQUEST['_wpnonce'];

			if ( ! HT_Util()->verify_nonce( $action . '-extension_' . $extension, $nonce ) ) {
				return;
			}
		}

		$checked = isset( $_POST['checked'] ) ? $_POST['checked'] : '';
		$options = $GLOBALS['hocwp_theme']->active_extensions;
		$change  = false;
		$count   = 0;

		if ( is_array( $checked ) ) {
			foreach ( $checked as $file ) {
				$before  = maybe_serialize( $options );
				$options = $this->active_list_control( $file, $options );

				if ( md5( $before ) != md5( maybe_serialize( $options ) ) ) {
					$count ++;
				}
			}

			$change = true;
		} elseif ( ! empty( $action ) && isset( $_GET['extension'] ) ) {
			$file    = $_GET['extension'];
			$options = $this->active_list_control( $file, $options );
			$change  = true;
			$count ++;
		}

		if ( $change ) {
			$options = array_filter( $options );
			update_option( 'hocwp_theme_active_extensions', $options );
			$message = '';

			switch ( $action ) {
				case 'activate':
					$message = sprintf( __( '<strong>Notice:</strong> %s extension(s) activated.', 'hocwp-theme' ), $count );
					do_action( 'hocwp_theme_extension_activation' );
					break;
				case 'deactivate':
					$message = sprintf( __( '<strong>Notice:</strong> %s extension(s) deactivated.', 'hocwp-theme' ), $count );
					do_action( 'hocwp_theme_extension_deactivation' );
					break;
			}

			set_transient( 'hocwp_theme_extension_message', $message );
			set_transient( 'hocwp_theme_flush_rewrite_rules', 1 );
			?>
			<script type="text/javascript">
				window.location.href = '<?php echo admin_url( 'themes.php?page=hocwp_theme&tab=extension' ); ?>';
			</script>
			<?php
		}
	}

	public function admin_notices() {
		$action  = $this->current_action();
		$tr_name = 'hocwp_theme_extension_message';

		if ( empty( $action ) && ! isset( $_GET['extension'] ) && false !== ( $message = get_transient( $tr_name ) ) ) {
			if ( ! empty( $message ) ) {
				HOCWP_Theme_Utility::admin_notice( $message );
			}

			delete_transient( $tr_name );
		}
	}

	protected function get_primary_column_name() {
		return 'name';
	}
}