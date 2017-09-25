<?php

class HOCWP_Theme_Meta_Post extends HOCWP_Theme_Meta {
	private $post_types;
	private $id;
	private $title;
	private $context;
	private $priority;

	public function __construct() {
		global $pagenow;
		if ( 'post.php' == $pagenow || 'post-new.php' == $pagenow ) {
			parent::__construct();
			$this->set_id( 'extra-information' );
			$this->set_title( __( 'Extra Information', 'hocwp-theme' ) );
			$this->set_callback( array( $this, 'callback' ) );
			$this->set_context( 'advanced' );
			$this->set_priority( 'default' );
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
		if ( in_array( $post_type, $this->post_types ) ) {
			add_meta_box( $this->id, $this->title, $this->callback, $this->post_types, $this->context, $this->priority, $this->callback_args );
		}
	}

	public function callback( $post, $args ) {
		echo '<div class="hocwp-theme">';
		wp_nonce_field( $this->id, $this->id . '_nonce' );
		foreach ( (array) $this->fields as $field ) {
			if ( ! isset( $field['callback_args']['value'] ) ) {
				$id = $field['callback_args']['id'];
				if ( false !== strpos( $id, '[' ) && false !== strpos( $id, '[' ) ) {
					$tmp = explode( '[', $id );
					foreach ( $tmp as $key => $a ) {
						$tmp[ $key ] = trim( $a, '[]' );
					}
					$id    = array_shift( $tmp );
					$meta  = get_post_meta( $post->ID, $id, true );
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
					$value = get_post_meta( $post->ID, $id, true );
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
			<div class="meta-row">
				<?php
				call_user_func( $field['callback'], $field['callback_args'] );
				$desc = isset( $field['description'] ) ? $field['description'] : '';
				if ( ! empty( $desc ) ) {
					$p = new HOCWP_Theme_HTML_Tag( 'p' );
					$p->add_attribute( 'class', 'description' );
					$p->set_text( $desc );
					$p->output();
				}
				do_action( 'hocwp_theme_meta_post_' . $this->id . '_' . $id );
				?>
			</div>
			<?php
		}
		echo '</div>';
	}

	public function save_post_action( $post_id ) {
		if ( ! HOCWP_Theme_Utility::can_save_post( $post_id, $this->id, $this->id . '_nonce' ) ) {
			return;
		}
		foreach ( $this->fields as $field ) {
			$id    = $field['id'];
			$value = $this->sanitize_data( $field );
			update_post_meta( $post_id, $id, $value );
		}
	}

	public function admin_enqueue_scripts_action() {
		wp_enqueue_style( 'hocwp-theme-admin-post-style', HOCWP_THEME_CORE_URL . '/css/admin-post' . HOCWP_THEME_CSS_SUFFIX );
	}
}