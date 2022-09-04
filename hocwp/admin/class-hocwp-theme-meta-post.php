<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Meta_Post extends HOCWP_Theme_Meta {
	private $post_types;
	private $id;
	private $title;
	private $context;
	private $priority; // The priority within the context where the box should show. Accepts 'high', 'core', 'default', or 'low'.
	protected $allow_pagenow = array( 'post.php', 'post-new.php', 'edit.php' );

	public $form_table = false;

	public function __construct() {
		global $pagenow;

		if ( empty( $this->allow_pagenow ) || in_array( $pagenow, $this->allow_pagenow ) ) {
			parent::__construct();
			$this->set_id( 'extra-information' );
			$this->set_title( __( 'Extra Information', 'hocwp-theme' ) );
			$this->set_callback( array( $this, 'callback' ) );
			$this->set_context( 'normal' );
			$this->set_priority( 'default' );
			$this->set_get_value_callback( 'get_post_meta' );
			$this->set_update_value_callback( 'update_post_meta' );
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes_action' ) );

			$pt = HT_Admin()->get_current_post_type();

			if ( 'attachment' == $pt ) {
				add_action( 'edit_attachment', array( $this, 'save_post_action' ) );
			} else {
				add_action( 'save_post', array( $this, 'save_post_action' ) );
			}

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts_action' ), 20 );
		}

		if ( 'edit.php' == $pagenow ) {
			add_action( 'quick_edit_custom_box', array( $this, 'quick_edit_custom_box' ), 10, 2 );

			add_filter( 'manage_posts_columns', array( $this, 'posts_columns_filter' ) );
			add_filter( 'manage_pages_columns', array( $this, 'posts_columns_filter' ) );
		}
	}

	public function posts_columns_filter( $columns ) {
		global $post_type;

		if ( in_array( $post_type, $this->post_types ) ) {
			foreach ( $this->fields as $field ) {
				$sac = $field['show_admin_column'] ?? '';

				if ( $sac ) {
					$columns[ $field['id'] ] = $field['title'];
				}
			}
		}

		return $columns;
	}

	public function quick_edit_custom_box( $column_name, $post_type ) {
		if ( in_array( $post_type, $this->post_types ) ) {
			$html = '<div class="clear"></div>';

			foreach ( $this->fields as $field ) {
				if ( $column_name == $field['id'] ) {
					$siqe = $field['show_in_quick_edit'] ?? '';

					if ( $siqe ) {
						ob_start();
						echo '<fieldset class="inline-edit-col-center" style="margin-bottom: 10px;">' . PHP_EOL;
						echo '<div class="inline-edit-col">' . PHP_EOL;
						$this->meta_row_html( $field, $field['id'] );
						echo '</div>' . PHP_EOL;
						echo '</fieldset>' . PHP_EOL;
						$html .= ob_get_clean();
					}
				}
			}

			if ( ! empty( $html ) ) {
				$html = '<div class="clearfix"></div>' . $html;
				echo $html;
			}
		}
	}

	public function set_post_types( $post_types ) {
		foreach ( (array) $post_types as $post_type ) {
			$this->add_post_type( $post_type );
		}
	}

	public function add_post_type( $post_type ) {
		if ( post_type_exists( $post_type ) ) {
			if ( ! is_array( $this->post_types ) ) {
				$this->post_types = array();
			}

			if ( ! in_array( $post_type, $this->post_types ) ) {
				$this->post_types[] = $post_type;
			}
		}
	}

	public function set_id( $id ) {
		$id = sanitize_title( $id );

		$this->id = $id;
	}

	public function get_id() {
		return $this->id;
	}

	public function set_title( $title ) {
		$this->title = $title;
	}

	public function set_context( $context ) {
		$this->context = $context;
	}

	public function set_priority( $priority ) {
		$this->priority = $priority;
	}

	public function add_meta_boxes_action() {
		global $post_type;

		if ( is_array( $this->post_types ) && in_array( $post_type, $this->post_types ) ) {
			add_meta_box( $this->get_id(), $this->title, $this->callback, $this->post_types, $this->context, $this->priority, $this->callback_args );
		}
	}

	private function meta_row_html( $field, $id = '' ) {
		if ( empty( $id ) ) {
			$id = $field['id'] ?? '';
		}

		$html = $field['callback_args']['html'] ?? '';

		unset( $field['callback_args']['html'] );

		if ( isset( $field['callback_args']['message'] ) ) {
			$message = $field['callback_args']['message'];

			if ( ! $this->form_table ) {
				$message = wpautop( $message );
			}

			echo $message;
		} else {
			if ( ! empty( $id ) ) {
				if ( ! $this->form_table && isset( $field['title'] ) && ! empty( $field['title'] ) ) {
					HT_HTML_Field()->label( array( 'text' => $field['title'], 'for' => $id ) );
					unset( $field['title'] );
					unset( $field['label'] );
					unset( $field['callback_args']['label'] );
				}

				call_user_func( $field['callback'], $field['callback_args'] );
				$desc = $field['description'] ?? '';

				if ( ! empty( $desc ) ) {
					$p = new HOCWP_Theme_HTML_Tag( 'p' );
					$p->add_attribute( 'class', 'description' );
					$p->set_text( $desc );
					$p->output();
				}
			}
		}

		echo $html;
		do_action( 'hocwp_theme_meta_post_' . $this->get_id() . '_' . $id );
	}

	private function meta_row( $field, $id = '' ) {
		if ( empty( $id ) ) {
			$id = $field['id'] ?? '';
		}
		?>
        <div class="meta-row">
            <fieldset>
				<?php $this->meta_row_html( $field, $id ); ?>
            </fieldset>
        </div>
		<?php
	}

	public function callback( $post, $args ) {
		if ( ! is_array( $args ) ) {
			return;
		}

		echo '<div class="hocwp-theme">';
		wp_nonce_field( $this->get_id(), $this->get_id() . '_nonce' );

		if ( ! is_array( $this->fields ) || 1 == count( $this->fields ) ) {
			$field = $this->fields[0] ?? '';

			if ( empty( $field['id'] ) ) {
				$this->form_table = false;
			}
		}

		if ( ! HT()->array_has_value( $this->fields ) ) {
			return;
		}

		if ( $this->form_table ) {
			echo '<table class="form-table">';

			foreach ( $this->fields as $field ) {
				$id    = $this->get_field_id( $field );
				$field = $this->sanitize_value( $post->ID, $field );
				$title = $field['title'];

				unset( $field['callback_args']['label'] );
				?>
                <tr>
                    <th>
                        <label for="<?php echo esc_attr( $id ); ?>"><?php echo $title; ?></label>
                    </th>
                    <td>
						<?php $this->meta_row( $field, $id ); ?>
                    </td>
                </tr>
				<?php
			}

			do_action( 'hocwp_theme_meta_post_fields', $this );

			echo '</table>';
		} else {
			foreach ( $this->fields as $field ) {
				$id    = $this->get_field_id( $field );
				$field = $this->sanitize_value( $post->ID, $field );
				$this->meta_row( $field, $id );
			}

			do_action( 'hocwp_theme_meta_post_fields', $this );
		}

		echo '</div>';
	}

	public function save_post_action( $post_id ) {
		if ( ! HT_Util()->can_save_post( $post_id, $this->get_id(), $this->get_id() . '_nonce' ) ) {
			return;
		}

		$this->save( $post_id );
	}

	public function admin_enqueue_scripts_action() {
		wp_enqueue_style( 'hocwp-theme-admin-post-style', HOCWP_THEME_CORE_URL . '/css/admin-post' . HOCWP_THEME_CSS_SUFFIX );
	}
}