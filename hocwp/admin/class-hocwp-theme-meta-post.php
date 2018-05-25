<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Meta_Post extends HOCWP_Theme_Meta {
	private $post_types;
	private $id;
	private $title;
	private $context;
	private $priority;

	public $form_table = false;

	public function __construct() {
		global $pagenow;

		if ( 'post.php' == $pagenow || 'post-new.php' == $pagenow ) {
			parent::__construct();
			$this->set_id( 'extra-information' );
			$this->set_title( __( 'Extra Information', 'hocwp-theme' ) );
			$this->set_callback( array( $this, 'callback' ) );
			$this->set_context( 'normal' );
			$this->set_priority( 'default' );
			$this->set_get_value_callback( 'get_post_meta' );
			$this->set_update_value_callback( 'update_post_meta' );
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes_action' ) );
			add_action( 'save_post', array( $this, 'save_post_action' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts_action' ), 20 );
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

		if ( is_array( $this->post_types ) && in_array( $post_type, $this->post_types ) ) {
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

	public function callback( $post, $args ) {
		echo '<div class="hocwp-theme">';
		wp_nonce_field( $this->id, $this->id . '_nonce' );

		if ( ! is_array( $this->fields ) || 1 == count( $this->fields ) ) {
			$field = $this->fields[0];

			if ( empty( $field['id'] ) ) {
				$this->form_table = false;
			}
		}

		if ( $this->form_table ) {
			echo '<table class="form-table">';
			foreach ( (array) $this->fields as $field ) {
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
			echo '</table>';
		} else {
			foreach ( (array) $this->fields as $field ) {
				$id    = $this->get_field_id( $field );
				$field = $this->sanitize_value( $post->ID, $field );
				$this->meta_row( $field, $id );
			}
		}

		echo '</div>';
	}

	public function save_post_action( $post_id ) {
		if ( ! HOCWP_Theme_Utility::can_save_post( $post_id, $this->id, $this->id . '_nonce' ) ) {
			return;
		}

		$this->save( $post_id );
	}

	public function admin_enqueue_scripts_action() {
		wp_enqueue_style( 'hocwp-theme-admin-post-style', HOCWP_THEME_CORE_URL . '/css/admin-post' . HOCWP_THEME_CSS_SUFFIX );
	}
}