<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Meta_Bookmark extends HOCWP_Theme_Meta {
	private $post_types;
	private $id;
	private $title;
	private $context;
	private $priority;

	public $form_table = false;

	public function __construct() {
		global $pagenow;

		if ( 'link.php' == $pagenow || 'link-add.php' == $pagenow ) {
			parent::__construct();

			$this->set_id( 'extra-information' );
			$this->set_title( __( 'Extra Information', 'hocwp-theme' ) );
			$this->set_callback( array( $this, 'callback' ) );
			$this->set_context( 'normal' );
			$this->set_priority( 'default' );
			$this->set_get_value_callback( 'get_post_meta' );
			$this->set_update_value_callback( 'update_post_meta' );
			$this->add_post_type( 'link' );

			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes_action' ) );
			add_action( 'edit_link', array( $this, 'save_post_action' ) );
			add_action( 'add_link', array( $this, 'save_post_action' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts_action' ), 20 );
		}
	}

	public function set_post_types( $post_types ) {
		foreach ( (array) $post_types as $post_type ) {
			$this->add_post_type( $post_type );
		}
	}

	public function add_post_type( $post_type ) {
		if ( ! is_array( $this->post_types ) ) {
			$this->post_types = array();
		}

		if ( $post_type instanceof WP_Post_Type ) {
			$post_type = $post_type->name;
		}

		if ( is_string( $post_type ) ) {
			if ( ! in_array( $post_type, $this->post_types ) ) {
				if ( post_type_exists( $post_type ) || 'link' == $post_type ) {
					$this->post_types[] = $post_type;
				}
			}
		}
	}

	public function set_id( $id ) {
		$this->id = $id;
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

		if ( ( empty( $post_type ) && in_array( 'link', $this->post_types ) ) || ( is_array( $this->post_types ) && in_array( $post_type, $this->post_types ) ) ) {
			add_meta_box( $this->id, $this->title, $this->callback, $this->post_types, $this->context, $this->priority, $this->callback_args );
		}
	}

	private function meta_row( $field, $id ) {
		?>
		<div class="meta-row">
			<fieldset>
				<?php
				$html = isset( $field['callback_args']['html'] ) ? $field['callback_args']['html'] : '';

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
						$desc = isset( $field['description'] ) ? $field['description'] : '';

						if ( ! empty( $desc ) ) {
							$p = new HOCWP_Theme_HTML_Tag( 'p' );
							$p->add_attribute( 'class', 'description' );
							$p->set_text( $desc );
							$p->output();
						}
					}
				}

				echo $html;
				do_action( 'hocwp_theme_meta_post_' . $this->id . '_' . $id );
				?>
			</fieldset>
		</div>
		<?php
	}

	public function callback( $link, $args ) {
		if ( ! is_object( $link ) ) {
			return;
		}

		$link_id = isset( $link->link_id ) ? $link->link_id : 0;

		echo '<div class="hocwp-theme">';
		wp_nonce_field( $this->id, $this->id . '_nonce' );

		if ( ! is_array( $this->fields ) || 1 == count( $this->fields ) ) {
			$field = $this->fields[0];

			if ( empty( $field['id'] ) ) {
				$this->form_table = false;
			}
		}

		if ( $this->form_table ) {
			echo '<table class="form-table links-table">';
			foreach ( (array) $this->fields as $field ) {
				$id    = $this->get_field_id( $field );
				$field = $this->sanitize_value( $link_id, $field );
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
			echo '</table>';
		} else {
			foreach ( (array) $this->fields as $field ) {
				$id    = $this->get_field_id( $field );
				$field = $this->sanitize_value( $link_id, $field );
				$this->meta_row( $field, $id );
			}
		}

		echo '</div>';
	}

	public function save_post_action( $post_id ) {
		if ( null == $post_id || ! isset( $post_id ) || empty( $post_id ) || ! is_numeric( $post_id ) ) {
			$post_id = isset( $_POST['link_id'] ) ? $_POST['link_id'] : '';
		}

		if ( ! HT()->is_positive_number( $post_id ) ) {
			return;
		}

		if ( ! HT_Util()->can_save_post( $post_id, $this->id, $this->id . '_nonce' ) ) {
			return;
		}

		$this->save( $post_id );
	}

	public function admin_enqueue_scripts_action() {
		wp_enqueue_style( 'hocwp-theme-admin-post-style', HOCWP_THEME_CORE_URL . '/css/admin-post' . HOCWP_THEME_CSS_SUFFIX );
	}
}