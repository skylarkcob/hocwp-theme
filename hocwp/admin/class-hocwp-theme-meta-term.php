<?php

final class HOCWP_Theme_Meta_Term extends HOCWP_Theme_Meta {
	protected $taxonomies = array();

	public function __construct() {
		global $pagenow;
		$taxonomy = isset( $_REQUEST['taxonomy'] ) ? $_REQUEST['taxonomy'] : '';
		if ( 'term.php' == $pagenow ) {
			parent::__construct();
			if ( ! empty( $taxonomy ) ) {
				add_action( $taxonomy . '_edit_form_fields', array( $this, 'edit_form_fields' ), 10, 2 );
			}
		}
		add_action( 'edit_' . $taxonomy, array( $this, 'edit' ), 10 );
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

	private function callback( $tag, $taxonomy ) {
		echo '<div class="hocwp-theme">';
		wp_nonce_field( $taxonomy, $taxonomy . '_nonce' );
		foreach ( (array) $this->fields as $field ) {
			if ( ! isset( $field['callback_args']['value'] ) ) {
				$id = $field['callback_args']['id'];
				if ( false !== strpos( $id, '[' ) && false !== strpos( $id, '[' ) ) {
					$tmp = explode( '[', $id );
					foreach ( $tmp as $key => $a ) {
						$tmp[ $key ] = trim( $a, '[]' );
					}
					$id    = array_shift( $tmp );
					$meta  = get_term_meta( $tag->term_id, $id, true );
					$count = count( $tmp );
					$k     = 0;
					while ( $k < $count && isset( $meta[ $tmp[ $k ] ] ) ) {
						$meta = $meta[ $tmp[ $k ] ];
						$k ++;
					}
					if ( 0 != $k ) {
						$value = $meta;
					} else {
						$value = '';
					}
				} else {
					$value = get_term_meta( $tag->term_id, $id, true );
				}
				$type = $field['type'];
				if ( 'timestamp' == $type ) {
					$format = isset( $field['callback_args']['data-date-format'] ) ? $field['callback_args']['data-date-format'] : '';
					if ( empty( $format ) ) {
						global $hocwp_theme;
						$format = $hocwp_theme->defaults['date_format'];
					}
					$field['callback_args']['data-date-format'] = HOCWP_Theme::javascript_datetime_format( $format );

					$value = date( $format, $value );
				}
				$field['callback_args']['value'] = $value;
			}
			?>
			<tr class="form-field term-<?php echo $id; ?>-wrap">
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
		echo '</div>';
	}

	public function edit( $term_id ) {
		$taxonomy = isset( $_REQUEST['taxonomy'] ) ? $_REQUEST['taxonomy'] : '';
		if ( ! HOCWP_Theme_Utility::verify_nonce( $taxonomy, $taxonomy . '_nonce' ) ) {
			return;
		}
		foreach ( $this->fields as $field ) {
			$id    = $field['id'];
			$value = $this->sanitize_data( $field );
			update_term_meta( $term_id, $id, $value );
		}
	}
}