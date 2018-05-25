<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

final class HOCWP_Theme_Meta_Term extends HOCWP_Theme_Meta {
	protected $taxonomies = array();

	public function __construct() {
		global $pagenow;
		$taxonomy = isset( $_REQUEST['taxonomy'] ) ? $_REQUEST['taxonomy'] : '';

		if ( 'term.php' == $pagenow || 'edit-tags.php' == $pagenow ) {
			parent::__construct();

			$this->set_get_value_callback( 'get_term_meta' );
			$this->set_update_value_callback( 'update_term_meta' );

			if ( ! empty( $taxonomy ) ) {
				add_action( $taxonomy . '_term_edit_form_top', array( $this, 'edit_form_top' ), 10, 2 );
				add_action( $taxonomy . '_edit_form_fields', array( $this, 'edit_form_fields' ), 10, 2 );
			}
		}

		add_action( 'edited_' . $taxonomy, array( $this, 'edit' ), 10 );
	}

	public function set_taxonomies( $taxonomies ) {
		foreach ( (array) $taxonomies as $taxonomy ) {
			$this->add_taxonomy( $taxonomy );
		}
	}

	public function add_taxonomy( $taxonomy ) {
		if ( taxonomy_exists( $taxonomy ) ) {
			if ( ! is_array( $this->taxonomies ) ) {
				$this->taxonomies = array();
			}

			if ( ! in_array( $taxonomy, $this->taxonomies ) ) {
				$this->taxonomies[] = $taxonomy;
			}
		}
	}

	public function edit_form_fields( $tag, $taxonomy ) {
		if ( ! empty( $taxonomy ) && in_array( $taxonomy, $this->taxonomies ) ) {
			if ( is_callable( $this->callback ) ) {
				call_user_func( $this->callback, $tag, $taxonomy );
			} else {
				$this->callback( $tag, $taxonomy );
			}
		}
	}

	public function edit_form_top( $tag, $taxonomy ) {
		wp_nonce_field( $taxonomy, $taxonomy . '_nonce' );
	}

	private function callback( $tag, $taxonomy ) {
		foreach ( (array) $this->fields as $field ) {
			$id    = $this->get_field_id( $field );
			$field = $this->sanitize_value( $tag->term_id, $field );
			?>
			<tr class="hocwp-theme form-field term-<?php echo $id; ?>-wrap">
				<th scope="row">
					<label for="<?php echo $id; ?>"><?php echo $field['title']; ?></label>
				</th>
				<td>
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

					do_action( 'hocwp_theme_meta_term_' . $taxonomy . '_' . $id );
					?>
				</td>
			</tr>
			<?php
		}
	}

	public function edit( $term_id ) {
		$taxonomy = isset( $_REQUEST['taxonomy'] ) ? $_REQUEST['taxonomy'] : '';

		if ( ! HT_Util()->verify_nonce( $taxonomy, $taxonomy . '_nonce' ) ) {
			return;
		}

		$this->save( $term_id );
	}
}