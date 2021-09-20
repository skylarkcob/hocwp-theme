<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

final class HOCWP_Theme_HTML_Field {

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {

	}

	private static function field_label( &$args, &$tag = '' ) {
		$label = isset( $args['label'] ) ? $args['label'] : '';

		if ( ! empty( $label ) ) {
			$lb  = new HOCWP_Theme_HTML_Tag( 'label' );
			$for = isset( $args['for'] ) ? $args['for'] : '';

			if ( empty( $for ) ) {
				$for = isset( $args['label_for'] ) ? $args['label_for'] : '';
			}

			if ( empty( $for ) ) {
				$for = $args['id'];
			}

			$lb->add_attribute( 'for', $for );
			$lb->set_text( $label );

			if ( isset( $args['type'] ) && ( 'radio' == $args['type'] || 'checkbox' == $args['type'] ) ) {
				if ( $tag instanceof HOCWP_Theme_HTML_Tag ) {
					$tag->set_text( $label );
					$tag->set_parent( $lb );
				}
			} else {
				$lb->output();
			}

			unset( $args['label'] );
		}

		unset( $args['label'], $args['for'], $args['label_for'] );
	}

	public static function layout( $args = array() ) {
		$options = isset( $args['options'] ) ? $args['options'] : '';

		if ( HT()->array_has_value( $options ) ) {
			$lists = array();

			foreach ( $options as $key => $option ) {
				if ( $option instanceof HOCWP_Theme_Layout ) {
					if ( ! empty( $option->image ) ) {
						$lists[ $option->id ] = sprintf( '<img class="show-modal-me" src="%s" alt="%s" title="%s">', esc_attr( $option->image ), esc_attr( $option->name ), esc_attr( $option->name ) );
					} else {
						$lists[ $option->id ] = $option->name;
					}
				} else {
					$lists[ $key ] = $option;
				}
			}

			$args['options'] = $lists;
			$args['type']    = 'radio';
			?>
            <div class="list-layout">
				<?php self::input( $args ); ?>
            </div>
			<?php
		}
	}

	public static function button( $args = array() ) {
		$defaults = array(
			'text'        => null,
			'type'        => 'primary',
			'name'        => 'submit',
			'wrap'        => true,
			'attributes'  => '',
			'button_type' => 'submit',
			'html_tag'    => 'input'
		);

		$args = wp_parse_args( $args, $defaults );

		$html_tag = $args['html_tag'];

		$button_type = $args['button_type'];

		if ( 'input' != $html_tag ) {
			$button = new HOCWP_Theme_HTML_Tag( $html_tag );

			$text = $args['text'];

			if ( empty( $text ) ) {
				$text = __( 'Submit', 'hocwp-theme' );
			}

			$button->set_text( $text );
			$button->add_attribute( 'class', $args['type'] );
			$button->add_attribute( 'name', $args['name'] );
			$button->add_attribute( 'type', $button_type );

			$attributes = $args['attributes'];
			$attributes = HT()->attribute_to_array( $attributes );

			foreach ( $attributes as $att => $value ) {
				$button->add_attribute( $att, $value );
			}

			$button->output();

			return;
		}

		ob_start();
		submit_button( $args['text'], $args['type'], $args['name'], $args['wrap'], $args['attributes'] );
		$html = ob_get_clean();

		if ( 'submit' != $button_type ) {
			$html = str_replace( 'type="submit"', 'type="' . esc_attr( $button_type ) . '"', $html );
		}

		echo $html;
	}

	public static function input( $args = array() ) {
		$defaults = array(
			'type' => 'text'
		);

		$args = wp_parse_args( $args, $defaults );

		if ( 'checkbox' == $args['type'] ) {
			$value = isset( $args['value'] ) ? absint( $args['value'] ) : 0;

			if ( 1 == $value ) {
				$args['checked'] = 'checked';
			}

			$args['value'] = 1;
		}

		$value   = isset( $args['value'] ) ? $args['value'] : '';
		$default = isset( $args['default'] ) ? $args['default'] : '';

		if ( ! isset( $args['value'] ) || '' === $value ) {
			$value = $default;
		}

		unset( $args['default'] );

		if ( 'radio' == $args['type'] || 'checkbox' == $args['type'] ) {
			$options = isset( $args['options'] ) ? $args['options'] : '';

			if ( is_array( $options ) && count( $options ) > 0 ) {
				unset( $args['options'] );

				foreach ( $options as $key => $data ) {
					if ( is_string( $data ) ) {
						$label = $data;
					} else {
						$label = ( is_array( $data ) && isset( $data['label'] ) ) ? $data['label'] : '';
					}

					$atts  = $args;
					$lb    = new HOCWP_Theme_HTML_Tag( 'label' );
					$input = new HOCWP_Theme_HTML_Tag( 'input' );
					$id    = isset( $atts['id'] ) ? $atts['id'] : '';
					$id    .= '_' . $key;

					if ( empty( $label ) ) {
						$label = $id;
					}

					$lb->add_attribute( 'for', $id );

					if ( empty( $label ) ) {
						$label = isset( $data['text'] ) ? $data['text'] : '';
					}

					if ( ! empty( $label ) ) {
						$input->set_text( $label );
						unset( $atts['label'] );
					}

					$atts['value'] = $key;
					$atts['id']    = $id;

					if ( $key == $value ) {
						$atts['checked'] = 'checked';
					}

					if ( 'checkbox' == $args['type'] ) {
						$atts['name']  = $key;
						$atts['value'] = 1;

						if ( is_array( $data ) && isset( $data['value'] ) && 1 == $data['value'] ) {
							$atts['checked'] = 'checked';
						} else {
							unset( $atts['checked'] );
						}
					}

					$attributes = $atts['attributes'] ?? '';
					unset( $atts['attributes'] );

					$input->set_attributes( $atts );

					$input->add_attributes( $attributes );

					$lb->set_text( $input );
					$lb->output();
					echo '<br>';
				}

				return;
			}
		}

		$input = new HOCWP_Theme_HTML_Tag( 'input' );

		if ( ( ! isset( $args['label'] ) || empty( $args['label'] ) ) && isset( $args['text'] ) && ! empty( $args['text'] ) ) {
			$args['label'] = $args['text'];
		}

		$right_label = isset( $args['right_label'] ) ? $args['right_label'] : '';

		if ( 1 != $right_label && true != $right_label ) {
			self::field_label( $args, $input );
		}

		$attributes = $args['attributes'] ?? '';
		unset( $args['attributes'] );

		$input->set_attributes( $args );

		$input->add_attributes( $attributes );

		$input->output();

		if ( 1 == $right_label || true == $right_label ) {
			self::field_label( $args, $input );
		}
	}

	public static function input_url( $args = array() ) {
		$args['type'] = 'url';
		self::input( $args );
	}

	public static function input_number( $args = array() ) {
		$args['type'] = 'number';
		self::input( $args );
	}

	public static function input_email( $args = array() ) {
		$args['type'] = 'email';
		self::input( $args );
	}

	public static function datetime_picker( $args = array() ) {
		$args['data-datetime-picker'] = 1;

		$args['autocomplete'] = 'off';

		self::input( $args );
	}

	public static function color_picker( $args = array() ) {
		$args['data-color-picker'] = 1;

		$args['class'] = 'medium-text';
		self::input( $args );
	}

	public static function textarea( $args = array() ) {
		$defaults = array(
			'class' => 'widefat',
			'rows'  => 10
		);

		$args     = wp_parse_args( $args, $defaults );
		$textarea = new HOCWP_Theme_HTML_Tag( 'textarea' );
		$value    = isset( $args['value'] ) ? $args['value'] : '';

		unset( $args['value'] );

		$textarea->set_text( $value );
		self::field_label( $args );

		$attributes = $args['attributes'] ?? '';
		unset( $args['attributes'] );

		$textarea->set_attributes( $args );

		$textarea->add_attributes( $attributes );

		$textarea->output();
	}

	public static function code_editor( $args = array() ) {
		$defaults = array(
			'data-code-editor' => 1
		);

		$args = wp_parse_args( $args, $defaults );

		self::textarea( $args );
	}

	public static function editor( $args = array() ) {
		if ( ! isset( $args['name'] ) && isset( $args['id'] ) ) {
			$args['name'] = $args['id'];
		}

		$args['textarea_name'] = $args['name'];

		if ( ! isset( $args['textarea_rows'] ) ) {
			$args['textarea_rows'] = 10;
		}

		$label = isset( $args['label'] ) ? $args['label'] : '';

		if ( ! empty( $label ) ) {
			HT_HTML_Field()->label( array( 'text' => $label, 'for' => $args['id'] ) );
		}

		unset( $label );

		wp_editor( $args['value'], $args['id'], $args );
	}

	public static function description( $args = array() ) {
		if ( isset( $args['description'] ) && ! empty( $args['description'] ) ) {
			$tag = isset( $args['description_tag'] ) ? $args['description_tag'] : 'p';
			printf( '<%s class="description">%s</%s>', $tag, $args['description'], $tag );
		}
	}

	public static function label( $args = array() ) {
		$label = new HOCWP_Theme_HTML_Tag( 'label' );

		$attributes = $args['attributes'] ?? '';
		unset( $args['attributes'] );

		$label->set_attributes( $args );

		$label->add_attributes( $attributes );

		$text = isset( $args['text'] ) ? $args['text'] : '';
		$label->set_text( $text );
		$label->output();
	}

	public static function option( $selected, $current, $args, $echo = false ) {
		if ( ( null == $current || empty( $current ) ) && ( null == $args || empty( $args ) ) ) {
			if ( null == $current ) {
				$current = '';
			}

			if ( null == $selected ) {
				$selected = '';
			} else {
				$selected = selected( $selected, $current, false );
			}

			$opt = '<option value="' . $current . '"' . $selected . '></option>';
		} else {
			$opt = new HOCWP_Theme_HTML_Tag( 'option' );

			if ( is_array( $args ) ) {
				$text = isset( $args['text'] ) ? $args['text'] : $current;
				$ov   = isset( $args['value'] ) ? $args['value'] : $current;
				unset( $args['text'] );
				$args['value'] = $ov;

				$attributes = $args['attributes'] ?? '';
				unset( $args['attributes'] );

				$opt->set_attributes( $args );

				$opt->add_attributes( $attributes );
			} else {
				if ( empty( $args ) ) {
					$text = $current;
				} else {
					$text = $args;
				}

				$opt->add_attribute( 'value', $current );
			}

			$opt->set_text( $text );

			if ( is_array( $selected ) ) {
				if ( in_array( $current, $selected ) ) {
					$selected = $current;
				} else {
					$selected = '';
				}
			}

			$selected = selected( $selected, $current, false );

			if ( ! empty( $selected ) ) {
				$opt->add_attribute( $selected );
			}

			$opt = $opt->build();
		}

		if ( $echo ) {
			echo $opt;
		}

		return $opt;
	}

	public static function select( $args = array() ) {
		$value   = isset( $args['value'] ) ? $args['value'] : '';
		$select  = new HOCWP_Theme_HTML_Tag( 'select' );
		$options = isset( $args['options'] ) ? $args['options'] : '';

		unset( $args['value'], $args['options'] );
		$oh = '';

		if ( ! empty( $options ) ) {
			$option_all = isset( $args['option_all'] ) ? $args['option_all'] : '';

			if ( ! empty( $option_all ) ) {
				if ( isset( $args['data-chosen'] ) && 1 == $args['data-chosen'] ) {
					$option_all = str_replace( '--', '', $option_all );
					$option_all = trim( $option_all );
					$lasts      = substr( $option_all, 0, - 3 );

					if ( '...' != $lasts && '&hellip;' != $lasts ) {
						$option_all .= '&hellip;';
					}

					$args['data-placeholder'] = $option_all;

					$oh .= self::option( null, null, null );
				} else {
					$oh .= self::option( $value, '', $option_all );
				}
			}

			foreach ( (array) $options as $key => $option ) {
				if ( is_array( $option ) && isset( $option['label'] ) ) {
					$optgroup = new HOCWP_Theme_HTML_Tag( 'optgroup' );
					$ops_html = '';
					$label    = $option['label'];

					unset( $option['label'] );

					foreach ( $option as $k => $child ) {
						if ( isset( $child['value'] ) ) {
							$k = $child['value'];
						}

						$ops_html .= self::option( $value, $k, $child );
					}

					$optgroup->set_text( $ops_html );
					$optgroup->add_attribute( 'label', $label );
					$oh .= $optgroup->build();
				} else {
					$oh .= self::option( $value, $key, $option );
				}
			}
		}

		self::field_label( $args );

		unset( $args['option_all'] );

		$attributes = $args['attributes'] ?? '';
		unset( $args['attributes'] );

		$select->set_attributes( $args );

		$select->add_attributes( $attributes );

		$select->set_text( $oh );
		$select->output();
	}

	public static function select_category( $args = array() ) {
		$args['taxonomy'] = 'category';
		self::select_term( $args );
	}

	public static function select_sidebar( $args = array() ) {
		$options = isset( $args['options'] ) ? $args['options'] : '';

		if ( empty( $options ) ) {
			$options = array(
				'' => __( '-- Choose sidebar --', 'hocwp-theme' )
			);

			global $wp_registered_sidebars;

			foreach ( $wp_registered_sidebars as $sidebar_id => $sidebar ) {
				if ( is_array( $sidebar ) ) {
					$name = isset( $sidebar['name'] ) ? $sidebar['name'] : '';

					if ( ! empty( $name ) ) {
						$name = sprintf( '%s (%s)', $name, $sidebar_id );
					}

					if ( empty( $name ) ) {
						$name = $sidebar_id;
					}

					$name = trim( $name );

					$options[ $sidebar_id ] = $name;
				}
			}

			$args['options'] = $options;
		}

		self::select( $args );
	}

	public static function select_term( $args = array() ) {
		$options = isset( $args['options'] ) ? $args['options'] : '';

		if ( ! HT()->array_has_value( $options ) ) {
			global $pagenow;

			$options  = array();
			$taxonomy = isset( $args['taxonomy'] ) ? $args['taxonomy'] : 'category';

			unset( $args['taxonomy'] );

			if ( is_array( $taxonomy ) && 1 == count( $taxonomy ) ) {
				$taxonomy = array_shift( $taxonomy );
			}

			$term_args = isset( $args['term_args'] ) ? $args['term_args'] : '';

			if ( ! is_array( $term_args ) ) {
				$term_args = array();
			}

			$default_args = array( 'hide_empty' => false );
			$term_args    = wp_parse_args( $term_args, $default_args );

			$taxonomies = null;

			if ( is_array( $taxonomy ) ) {
				$taxonomies = $taxonomy;

				foreach ( $taxonomies as $taxonomy ) {
					$tax = get_taxonomy( $taxonomy );

					$options[ $taxonomy ] = array(
						'label' => $tax->label
					);

					$terms = HT_Util()->get_terms( $taxonomy, $term_args );

					foreach ( $terms as $obj ) {
						$options[ $taxonomy ][] = array(
							'text'          => $obj->name . ' (' . $obj->count . ')',
							'value'         => $taxonomy . ',' . $obj->term_id,
							'data-term'     => $obj->term_id,
							'data-taxonomy' => $taxonomy
						);
					}
				}
			} else {
				$terms = HT_Util()->get_terms( $taxonomy, $term_args );

				foreach ( $terms as $obj ) {
					$options[ $obj->term_id ] = $obj->name . ' (' . $obj->count . ')';
				}
			}

			$args['options'] = $options;

			if ( ! isset( $args['option_all'] ) && 'widgets.php' != $pagenow ) {
				$option_all = __( '-- Choose term --', 'hocwp-theme' );

				if ( is_string( $taxonomy ) ) {
					$tax = get_taxonomy( $taxonomy );

					if ( $tax instanceof WP_Taxonomy ) {
						$option_all = sprintf( __( '-- Choose %s --', 'hocwp-theme' ), $tax->labels->singular_name );
					}
				}

				$args['option_all'] = $option_all;
			}
		}

		self::select( $args );
	}

	public static function select_menu( $args = array() ) {
		$options = isset( $args['options'] ) ? $args['options'] : '';

		if ( ! HT()->array_has_value( $options ) ) {
			global $pagenow;

			$options = array();
			$lists   = wp_get_nav_menus();

			if ( $lists ) {
				foreach ( $lists as $obj ) {
					$options[ $obj->term_id ] = $obj->name;
				}
			}

			if ( ! isset( $args['option_all'] ) && 'widgets.php' != $pagenow ) {
				$args['option_all'] = __( '-- Choose menu --', 'hocwp-theme' );
			}

			$args['options'] = $options;
		}

		self::select( $args );
	}

	public static function select_page( $args = array() ) {
		$options = isset( $args['options'] ) ? $args['options'] : '';

		if ( ! HT()->array_has_value( $options ) ) {
			global $pagenow;

			$options = array();
			$pages   = HT_Query()->pages();

			if ( $pages ) {
				foreach ( $pages as $obj ) {
					$options[ $obj->ID ] = $obj->post_title;
				}
			}

			if ( ! isset( $args['option_all'] ) && 'widgets.php' != $pagenow ) {
				$args['option_all'] = __( '-- Choose page --', 'hocwp-theme' );
			}

			$args['options'] = $options;
		}

		self::select( $args );
	}

	public static function select_post( $args = array() ) {
		$options = isset( $args['options'] ) ? $args['options'] : '';

		if ( ! HT()->array_has_value( $options ) ) {
			global $pagenow;

			$options = array();

			$post_type = isset( $args['post_type'] ) ? $args['post_type'] : 'post';
			unset( $args['post_type'] );

			$query = new WP_Query( array( 'post_type' => $post_type, 'post_status' => 'publish' ) );

			if ( $query->have_posts() ) {
				if ( isset( $args['value'] ) ) {
					$value = $args['value'];
					$obj   = get_post( $value );

					if ( $obj instanceof WP_Post ) {
						array_unshift( $query->posts, $obj );
					}
				}

				foreach ( $query->posts as $obj ) {
					$options[ $obj->ID ] = $obj->post_title;
				}
			}

			if ( ! isset( $args['option_all'] ) && 'widgets.php' != $pagenow ) {
				$default_text = __( '-- Choose post --', 'hocwp-theme' );

				if ( ! is_array( $post_type ) ) {
					$type = get_post_type_object( $post_type );

					if ( $type instanceof WP_Post_Type ) {
						$default_text = sprintf( __( '-- Choose %s --', 'hocwp-theme' ), $type->labels->singular_name );
					}
				}

				$args['option_all'] = $default_text;
			}

			$args['options'] = $options;
		}

		self::select( $args );
	}

	public static function chosen_post( $args = array() ) {
		if ( ! isset( $args['options'] ) ) {
			$post_args = isset( $args['post_args'] ) ? $args['post_args'] : array();

			if ( ! isset( $post_args['post_type'] ) ) {
				$post_args['post_type'] = isset( $args['post_type'] ) ? $args['post_type'] : '';
			}

			$defaults = array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => - 1
			);

			$post_args = wp_parse_args( $post_args, $defaults );

			$query = new WP_Query( $post_args );

			if ( $query->have_posts() ) {
				$options = array();

				foreach ( $query->get_posts() as $obj ) {
					if ( $obj instanceof WP_Post ) {
						$options[ $obj->ID ] = $obj->post_title;
					}
				}

				$defaults = array(
					'options' => $options
				);

				$args = wp_parse_args( $args, $defaults );
			}

			if ( ! isset( $args['option_all'] ) ) {
				$post_type = $post_args['post_type'];

				$type = get_post_type_object( $post_type );

				if ( $type instanceof WP_Post_Type ) {
					$args['option_all'] = sprintf( __( 'Choose %s', 'hocwp-theme' ), $type->labels->name );
				} else {
					$args['option_all'] = __( 'Choose items', 'hocwp-theme' );
				}
			}
		}

		self::chosen( $args );
	}

	public static function chosen_term( $args = array() ) {
		if ( ! isset( $args['options'] ) ) {
			$term_args = isset( $args['term_args'] ) ? $args['term_args'] : array();

			if ( ! isset( $term_args['taxonomy'] ) ) {
				$term_args['taxonomy'] = isset( $args['taxonomy'] ) ? $args['taxonomy'] : '';
			}

			$terms = HT_Util()->get_terms( $term_args['taxonomy'], $term_args );

			if ( HT()->array_has_value( $terms ) ) {
				$options = array();

				foreach ( $terms as $term ) {
					if ( $term instanceof WP_Term ) {
						$options[ $term->term_id ] = $term->name;
					}
				}

				$defaults = array(
					'options' => $options
				);

				$args = wp_parse_args( $args, $defaults );
			}

			if ( ! isset( $args['option_all'] ) ) {
				$name = $term_args['taxonomy'];

				$type = get_taxonomy( $name );

				if ( $type instanceof WP_Taxonomy ) {
					$args['option_all'] = sprintf( __( 'Choose %s', 'hocwp-theme' ), $type->labels->name );
				} else {
					$args['option_all'] = __( 'Choose items', 'hocwp-theme' );
				}
			}
		}

		self::chosen( $args );
	}

	public static function chosen( $args = array() ) {
		$args['data-chosen'] = 1;

		if ( isset( $args['multiple'] ) ) {
			$args['name'] = $args['name'] . '[]';
		}

		if ( isset( $args['callback'] ) ) {
			$callback = $args['callback'];
			unset( $args['callback'] );

			if ( is_callable( $callback ) ) {
				call_user_func( $callback, $args );
			} elseif ( is_callable( array( __CLASS__, $callback ) ) ) {
				call_user_func( array( __CLASS__, $callback ), $args );
			}
		} else {
			self::select( $args );
		}
	}

	public static function icon_text( $args = array() ) {
		$name = isset( $args['name'] ) ? $args['name'] : '';

		if ( ! empty( $name ) ) {
			$id = isset( $args['id'] ) ? $args['id'] : '';

			if ( empty( $id ) ) {
				$id = HT_Sanitize()->html_id( $name );
			}

			$value = isset( $args['value'] ) ? $args['value'] : '';

			$count = 0;

			if ( is_array( $value ) ) {
				$count = count( $value );
			}
			?>
            <div class="allow-add-data">
                <div class="inner">
                    <ul data-list-type="custom" class="widefat sortable sub-sortable hocwp-theme-sortable"
                        data-count="<?php echo esc_attr( $count ); ?>">
						<?php
						if ( HT()->array_has_value( $value ) ) {
							$count = 0;

							foreach ( $value as $data ) {
								$fi = isset( $data['icon'] ) ? $data['icon'] : '';
								$ft = isset( $data['text'] ) ? $data['text'] : '';
								$fu = isset( $data['url'] ) ? $data['url'] : '';

								if ( empty( $fi ) && empty( $ft ) ) {
									continue;
								}
								?>
                                <li class="ui-state-default ui-sortable-handle">
                                    <input placeholder="<?php esc_attr_e( 'Icon', 'hocwp-theme' ); ?>" type="text"
                                           class="regular-text"
                                           id="<?php echo esc_attr( $id . '_icon_' . $count ); ?>"
                                           name="<?php echo esc_attr( $name . '[' . $count . '][icon]' ); ?>"
                                           value="<?php echo esc_attr( $fi ); ?>">
                                    <input placeholder="<?php esc_attr_e( 'Text', 'hocwp-theme' ); ?>" type="text"
                                           class="regular-text"
                                           id="<?php echo esc_attr( $id . '_text_' . $count ); ?>"
                                           name="<?php echo esc_attr( $name . '[' . $count . '][text]' ); ?>"
                                           value="<?php echo esc_attr( $ft ); ?>">
                                    <input placeholder="<?php esc_attr_e( 'URL', 'hocwp-theme' ); ?>" type="url"
                                           class="regular-text"
                                           id="<?php echo esc_attr( $id . '_url_' . $count ); ?>"
                                           name="<?php echo esc_attr( $name . '[' . $count . '][url]' ); ?>"
                                           value="<?php echo esc_attr( $fu ); ?>">
                                    <span class="remove"
                                          title="<?php esc_attr_e( 'Remove', 'hocwp-theme' ); ?>">&times;</span>
                                </li>
								<?php
								$count ++;
							}
						}
						?>
                        <li class="ui-state-default ui-sortable-handle base-data" style="display: none">
                            <input placeholder="<?php esc_attr_e( 'Icon', 'hocwp-theme' ); ?>"
                                   type="text"
                                   class="regular-text"
                                   id="<?php echo esc_attr( $id . '_icon_' ); ?>"
                                   name="<?php echo esc_attr( $name . '[%count%][icon]' ); ?>"
                                   value="">
                            <input placeholder="<?php esc_attr_e( 'Text', 'hocwp-theme' ); ?>"
                                   type="text"
                                   class="regular-text"
                                   id="<?php echo esc_attr( $id . '_text_' ); ?>"
                                   name="<?php echo esc_attr( $name . '[%count%][text]' ); ?>"
                                   value="">
                            <input placeholder="<?php esc_attr_e( 'URL', 'hocwp-theme' ); ?>"
                                   type="url"
                                   class="regular-text"
                                   id="<?php echo esc_attr( $id . '_url_' ); ?>"
                                   name="<?php echo esc_attr( $name . '[%count%][url]' ); ?>"
                                   value="">
                            <span class="remove" title="<?php esc_attr_e( 'Remove', 'hocwp-theme' ); ?>">&times;</span>
                        </li>
                    </ul>
					<?php //self::input( $args ); ?>
                </div>
                <button type="button" name="add-row"
                        class="button add-data-html"
                        aria-label="<?php esc_attr_e( 'Add', 'hocwp-theme' ); ?>"><?php _e( 'Add', 'hocwp-theme' ); ?></button>
            </div>
			<?php
		}
	}

	public static function icon_remove( $title = '' ) {
		if ( empty( $title ) ) {
			$title = __( 'Remove', 'hocwp-theme' );
		}

		ob_start();
		?>
        <span class="dashicons dashicons-no-alt" title="<?php echo esc_attr( $title ); ?>"></span>
		<?php
		return ob_get_clean();
	}

	public static function images( $args = array() ) {
		$name  = isset( $args['name'] ) ? $args['name'] : '';
		$value = isset( $args['value'] ) ? $args['value'] : '';
		$id    = isset( $args['id'] ) ? $args['id'] : '';

		$images = $value;

		if ( is_array( $value ) ) {
			$value = json_encode( $value );
		}

		$column = isset( $args['column'] ) ? $args['column'] : '';

		$class = 'images-box wp-media-buttons';

		if ( isset( $args['auto_height'] ) && $args['auto_height'] ) {
			$class .= ' auto-height';
		}
		?>
        <div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $class ); ?>"
             data-column="<?php echo esc_attr( $column ); ?>">
            <button type="button" class="button insert-medias insert-images add_media"
                    aria-label="<?php esc_attr_e( 'Add images', 'hocwp-theme' ); ?>"><span
                        class="wp-media-buttons-icon"></span> <?php _e( 'Add images', 'hocwp-theme' ); ?></button>
			<?php
			if ( ! empty( $images ) ) {
				?>
                <button type="button" class="button remove-medias remove-images add_media"
                        aria-label="<?php esc_attr_e( 'Remove all images', 'hocwp-theme' ); ?>"><span
                            class="wp-media-buttons-icon"></span> <?php _e( 'Remove all images', 'hocwp-theme' ); ?>
                </button>
				<?php
			}
			?>
            <ul class="list-images clearfix" data-list-type="image" data-sortable="1">
				<?php
				if ( ! empty( $images ) ) {
					$images = json_decode( $images );

					if ( HT()->array_has_value( $images ) ) {
						foreach ( $images as $id ) {
							?>
                            <li class="ui-state-default" data-id="<?php echo esc_attr( $id ); ?>">
								<?php
								echo wp_get_attachment_image( $id, 'full', false, array( 'title' => get_the_title( $id ) ) );
								echo self::icon_remove();
								?>
                            </li>
							<?php
						}
					}
				}
				?>
            </ul>
            <input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>">
			<?php self::description( $args ); ?>
        </div>
		<?php
	}

	public static function sortable( $args = array() ) {
		$lists = isset( $args['lists'] ) ? $args['lists'] : '';
		$lists = (array) $lists;
		$lists = array_filter( $lists );

		$connects = isset( $args['connects'] ) ? $args['connects'] : true;

		$value = isset( $args['value'] ) ? $args['value'] : '';

		if ( '[]' == $value ) {
			$value = '';
		}

		if ( empty( $lists ) ) {
			$options = isset( $args['options'] ) ? $args['options'] : '';
			$options = (array) $options;
			$options = array_filter( $options );

			if ( HT()->array_has_value( $options ) ) {
				if ( empty( $value ) ) {
					$value = array_keys( $options );
					$value = json_encode( $value );
					$lists = $options;
				} else {
					$items = json_decode( $value, true );

					foreach ( $items as $item ) {
						$lists[ $item ] = $options[ $item ];
					}
				}
			}
		}

		if ( HT()->array_has_value( $lists ) || HT()->array_has_value( $connects ) ) {
			$id = $args['id'];
			$id = sanitize_html_class( $id );
			unset( $args['lists'] );
			unset( $args['connects'] );

			$connect_sub = isset( $args['connect_sub'] ) ? $args['connect_sub'] : '';
			unset( $args['connect_sub'] );

			$list_type = isset( $args['list_type'] ) ? $args['list_type'] : '';
			unset( $args['list_type'] );

			if ( empty( $list_type ) ) {
				_doing_it_wrong( __CLASS__ . ':' . __FUNCTION__, __( 'You must pass list_type in arguments for this sortable list.', 'hocwp-theme' ), '6.1.8' );

				return;
			}

			$ul = new HOCWP_Theme_HTML_Tag( 'ul' );
			$ul->add_attribute( 'data-list-type', $list_type );
			$class = isset( $args['class'] ) ? $args['class'] : '';
			$class .= ' sortable hocwp-theme-sortable';

			$has_sub = isset( $args['has_sub'] ) ? $args['has_sub'] : false;

			if ( $connects || HT()->array_has_value( $connects ) ) {
				$ul->add_attribute( 'data-connect-with', $id );

				$class .= ' connect-lists';

				if ( ! $has_sub ) {
					$class .= ' ' . $id;
				}
			}

			$class = trim( $class );

			$ul->add_attribute( 'class', $class );

			if ( ! $has_sub ) {
				$ul->add_attribute( 'data-sortable', 1 );
			}

			$li_html = '';

			if ( empty( $value ) ) {
				foreach ( $lists as $key => $list ) {
					if ( empty( $list ) ) {
						continue;
					}

					if ( ! HT()->string_contain( $list, '</li>' ) ) {
						$li = new HOCWP_Theme_HTML_Tag( 'li' );
						$li->add_attribute( 'class', 'ui-state-default' );
						$li->set_text( $list );
						$li->add_attribute( 'data-value', $key );
						$li_html .= $li->build();
					} else {
						$li_html .= $list;
					}
				}
			} else {
				$tmp = $value;

				if ( ! is_array( $tmp ) ) {
					$tmp = json_decode( $tmp, true );
				}

				if ( ! is_array( $tmp ) ) {
					$tmp = array();
				}

				foreach ( (array) $tmp as $key ) {
					if ( is_array( $key ) || is_object( $key ) ) {
						continue;
					}

					$list = isset( $lists[ $key ] ) ? $lists[ $key ] : '';

					if ( empty( $list ) ) {
						continue;
					}

					if ( ! HT()->string_contain( $list, '</li>' ) ) {
						$li = new HOCWP_Theme_HTML_Tag( 'li' );
						$li->add_attribute( 'class', 'ui-state-default' );
						$li->set_text( $list );
						$li->add_attribute( 'data-value', $key );
						$li_html .= $li->build();
					} else {
						$li_html .= $list;
					}

					unset( $lists[ $key ] );
				}

				if ( HT()->array_has_value( $lists ) ) {
					foreach ( $lists as $key => $list ) {
						if ( empty( $list ) ) {
							continue;
						}

						if ( ! HT()->string_contain( $list, '</li>' ) ) {
							$li = new HOCWP_Theme_HTML_Tag( 'li' );
							$li->add_attribute( 'class', 'ui-state-default' );
							$li->set_text( $list );
							$li->add_attribute( 'data-value', $key );
							$li_html .= $li->build();
						} else {
							$li_html .= $list;
						}

						$tmp[] = $key;
					}

					$args['value'] = json_encode( $tmp );
				}
			}

			$ul->set_text( $li_html );
			$ul->output();

			if ( $connects || HT()->array_has_value( $connects ) ) {
				$class .= ' connected-result ';
				$ul    = new HOCWP_Theme_HTML_Tag( 'ul' );
				$ul->add_attribute( 'data-list-type', $list_type );

				if ( ! $has_sub ) {
					$ul->add_attribute( 'data-connect-with', $id );
				} else {
					if ( ! empty( $connect_sub ) ) {
						$ul->add_attribute( 'data-connect-with', $connect_sub );
						$class .= ' ' . $connect_sub;
					}
				}

				$ul->add_attribute( 'class', $class );
				$ul->add_attribute( 'data-sortable', 1 );
				$li_html = '';

				if ( HT()->array_has_value( $connects ) ) {
					foreach ( (array) $connects as $key => $list ) {
						if ( empty( $list ) ) {
							continue;
						}

						if ( ! HT()->string_contain( $list, '</li>' ) ) {
							$li = new HOCWP_Theme_HTML_Tag( 'li' );
							$li->add_attribute( 'class', 'ui-state-default' );
							$li->set_text( $list );
							$li->add_attribute( 'data-value', $key );
							$li_html .= $li->build();
						} else {
							$li_html .= $list;
						}
					}
				}

				$ul->set_text( $li_html );
				$ul->output();
			}

			unset( $args['options'] );

			$args['type'] = 'hidden';
			self::input( $args );
		}
	}

	public static function sortable_category( $args = array() ) {
		$defaults = array(
			'taxonomy' => 'category'
		);

		$args = wp_parse_args( $args, $defaults );

		self::sortable_term( $args );
	}

	public static function term_label( $term, $tax ) {
		if ( $term instanceof WP_Term && $tax instanceof WP_Taxonomy ) {
			return sprintf( __( '%s (ID: %s - Taxonomy: %s - Post count: %s)', 'hocwp-theme' ), $term->name, $term->term_id, $tax->labels->singular_name, $term->count );
		} elseif ( $term instanceof WP_Term ) {
			return sprintf( __( '%s (ID: %s - Taxonomy: %s - Post count: %s)', 'hocwp-theme' ), $term->name, $term->term_id, $term->taxonomy, $term->count );
		} elseif ( $tax instanceof WP_Taxonomy ) {
			return sprintf( __( 'Unknown name (Taxonomy: %s)', 'hocwp-theme' ), $tax->labels->singular_name );
		}

		return __( 'Unknown', 'hocwp-theme' );
	}

	public static function sortable_term( $args = array() ) {
		$id = $args['id'];
		$id = sanitize_html_class( $id );

		$taxonomy = isset( $args['taxonomy'] ) ? $args['taxonomy'] : 'category';
		unset( $args['taxonomy'] );

		if ( is_array( $taxonomy ) && 1 == count( $taxonomy ) ) {
			$taxonomy = array_shift( $taxonomy );
		}

		$default_args = array( 'hide_empty' => false );
		$term_args    = isset( $args['term_args'] ) ? $args['term_args'] : array();
		$term_args    = wp_parse_args( $term_args, $default_args );
		unset( $args['term_args'] );
		$args['list_type'] = 'term';

		$value = isset( $args['value'] ) ? $args['value'] : '';

		$results = array();

		if ( ! empty( $value ) ) {
			$values   = json_decode( $value );
			$connects = array();

			foreach ( $values as $std ) {
				if ( ! is_object( $std ) ) {
					continue;
				}

				$obj = get_term_by( 'id', $std->id, $std->taxonomy );

				if ( $obj instanceof WP_Term ) {
					$results[ $obj->term_id ] = $obj;

					$tax = get_taxonomy( $obj->taxonomy );

					$sub = $id . '_' . $tax->name;

					$label = self::term_label( $obj, $tax );

					$connects[] = '<li class="ui-state-default" data-taxonomy="' . $obj->taxonomy . '" data-id="' . $obj->term_id . '" data-connect-list="' . $sub . '">' . $label . '</li>';
				}
			}

			if ( 0 < count( $connects ) ) {
				$args['connects'] = $connects;
			}
		}

		$lists = array();

		if ( is_array( $taxonomy ) ) {
			$taxonomies = $taxonomy;

			$connect_sub = '';

			foreach ( $taxonomies as $taxonomy ) {
				$tax = get_taxonomy( $taxonomy );

				if ( ! ( $tax instanceof WP_Taxonomy ) ) {
					continue;
				}

				$item = '<li class="ui-state-default has-child">';
				$item .= '<a href="javascript:">' . $tax->label . '</a>';

				$terms = HT_Util()->get_terms( $taxonomy, $term_args );

				if ( HT()->array_has_value( $terms ) ) {
					$args['has_sub'] = true;

					$connects = isset( $args['connects'] ) ? $args['connects'] : true;

					$ul = new HOCWP_Theme_HTML_Tag( 'ul' );

					$class = 'sortable sub-sortable';

					$sub = $id . '_' . $taxonomy;

					if ( $connects || HT()->array_has_value( $connects ) ) {
						$ul->add_attribute( 'data-connect-with', $id );
						$class .= ' ' . $sub;

						$connect_sub .= $sub . ' ';
					}

					$ul->add_attribute( 'class', $class );
					$ul->add_attribute( 'data-sortable', 1 );
					$ul->add_attribute( 'data-connect-with', $sub );

					$tmp = '';

					foreach ( $terms as $obj ) {
						if ( array_key_exists( $obj->term_id, $results ) ) {
							continue;
						}

						$label = self::term_label( $obj, $tax );

						$tmp .= '<li class="ui-state-default" data-taxonomy="' . $taxonomy . '" data-id="' . $obj->term_id . '" data-connect-list="' . $sub . '" title="' . esc_attr( $label ) . '">' . $label . '</li>';
					}

					$ul->set_text( $tmp );

					$item .= $ul->build();
				}

				$item    .= '</li>';
				$lists[] = $item;
			}

			$args['connect_sub'] = trim( $connect_sub );
		} else {
			$tax = get_taxonomy( $taxonomy );

			if ( ! $tax instanceof WP_Taxonomy ) {
				return;
			}

			$terms = HT_Util()->get_terms( $taxonomy, $term_args );

			foreach ( $terms as $obj ) {
				if ( array_key_exists( $obj->term_id, $results ) ) {
					continue;
				}

				$label = self::term_label( $obj, $tax );

				$lists[] = '<li class="ui-state-default" data-taxonomy="' . $obj->taxonomy . '" data-id="' . $obj->term_id . '" title="' . esc_attr( $label ) . '">' . $label . '</li>';
			}
		}

		$args['lists'] = $lists;
		self::sortable( $args );
	}

	public static function sortable_page( $args = array() ) {
		$args['post_type'] = 'page';
		self::sortable_post( $args );
	}

	public static function sortable_post( $args = array() ) {
		$id = $args['id'];
		$id = sanitize_html_class( $id );

		$post_type = isset( $args['post_type'] ) ? $args['post_type'] : 'post';
		unset( $args['post_type'] );

		if ( is_array( $post_type ) && 1 == count( $post_type ) ) {
			$post_type = array_shift( $post_type );
		}

		$post_args = isset( $args['post_args'] ) ? $args['post_args'] : array();

		$defaults = array(
			'post_type'   => $post_type,
			'numberposts' => 50
		);

		$post_args = wp_parse_args( $post_args, $defaults );

		unset( $args['post_args'] );

		$args['list_type'] = 'post';

		$value = isset( $args['value'] ) ? $args['value'] : '';

		$results = array();

		if ( ! empty( $value ) ) {
			$values   = json_decode( $value );
			$connects = array();

			foreach ( $values as $std ) {
				$obj = get_post( $std->id );

				if ( $obj instanceof WP_Post ) {
					$results[ $obj->ID ] = $obj;

					$type = get_post_type_object( $obj->post_type );

					$sub = $id . '_' . $type->name;

					$connects[] = '<li class="ui-state-default" data-post-type="' . $obj->post_type . '" data-id="' . $obj->ID . '" data-connect-list="' . $sub . '">' . $obj->post_title . ' (' . $type->labels->singular_name . ')</li>';
				}
			}

			if ( 0 < count( $connects ) ) {
				$args['connects'] = $connects;
			}
		}

		$lists = array();

		if ( is_array( $post_type ) ) {
			$post_types = $post_type;

			$connect_sub = '';

			foreach ( $post_types as $post_type ) {
				$type = get_post_type_object( $post_type );

				if ( ! ( $type instanceof WP_Post_Type ) ) {
					continue;
				}

				$item = '<li class="ui-state-default has-child">';
				$item .= '<a href="javascript:">' . $type->label . '</a>';

				$list_posts = get_posts( $post_args );

				if ( HT()->array_has_value( $list_posts ) ) {
					$args['has_sub'] = true;

					$connects = isset( $args['connects'] ) ? $args['connects'] : true;

					$ul = new HOCWP_Theme_HTML_Tag( 'ul' );

					$class = 'sortable sub-sortable';

					$sub = $id . '_' . $post_type;

					if ( $connects || HT()->array_has_value( $connects ) ) {
						$ul->add_attribute( 'data-connect-with', $id );
						$class .= ' ' . $sub;

						$connect_sub .= $sub . ' ';
					}

					$ul->add_attribute( 'class', $class );
					$ul->add_attribute( 'data-sortable', 1 );
					$ul->add_attribute( 'data-connect-with', $sub );

					$tmp = '';

					foreach ( $list_posts as $obj ) {
						if ( array_key_exists( $obj->ID, $results ) ) {
							continue;
						}

						$tmp .= '<li class="ui-state-default" data-post-type="' . $post_type . '" data-id="' . $obj->ID . '" data-connect-list="' . $sub . '">' . $obj->post_title . ' (' . $type->labels->singular_name . ')</li>';
					}

					$ul->set_text( $tmp );

					$item .= $ul->build();
				}

				$item    .= '</li>';
				$lists[] = $item;
			}

			$args['connect_sub'] = trim( $connect_sub );
		} else {
			$type       = get_post_type_object( $post_type );
			$list_posts = get_posts( $post_args );

			foreach ( $list_posts as $obj ) {
				if ( array_key_exists( $obj->ID, $results ) ) {
					continue;
				}

				$lists[] = '<li class="ui-state-default" data-post-type="' . $obj->post_type . '" data-id="' . $obj->ID . '">' . $obj->post_title . ' (' . $type->labels->singular_name . ')</li>';
			}
		}

		$args['lists'] = $lists;
		self::sortable( $args );
	}

	public static function size( $args = array() ) {
		$name          = $args['name'];
		$name_width    = $name . '[width]';
		$name_height   = $name . '[height]';
		$class         = isset( $args['class'] ) ? $args['class'] : '';
		$class         .= ' small-text';
		$args['class'] = trim( $class );
		$args['type']  = 'number';
		$args['min']   = 0;
		$args['step']  = 1;
		$size          = isset( $args['value'] ) ? $args['value'] : '';
		$size          = HT_Sanitize()->size( $size );
		$args['name']  = $name_width;
		$args['value'] = $size[0];
		self::field_label( $args );
		self::input( $args );

		echo ' <span>x </span>&nbsp;';
		$args['name']  = $name_height;
		$args['value'] = $size[1];

		$args['id'] = $args['id'] . '_height';

		self::input( $args );
	}

	public static function content_with_image( $args = array() ) {
		$base_value = isset( $args['value'] ) ? $args['value'] : '';
		$base_id    = isset( $args['id'] ) ? $args['id'] : '';
		$base_name  = isset( $args['name'] ) ? $args['name'] : '';

		$content_key = isset( $args['content_key'] ) ? $args['content_key'] : '';

		if ( empty( $content_key ) ) {
			$content_key = 'content';
		}

		$content_key = HT_Sanitize()->html_id( $content_key );

		$content_args = isset( $args['content_args'] ) ? $args['content_args'] : '';

		if ( ! is_array( $content_args ) ) {
			$content_args = array();
		}

		$id = $base_id;

		if ( ! empty( $id ) ) {
			$id .= '_' . $content_key;
		}

		$name = $base_name;

		if ( ! empty( $name ) ) {
			$name .= '[' . $content_key . ']';
		}

		$defaults = array(
			'id'    => $id,
			'name'  => $name,
			'value' => isset( $base_value[ $content_key ] ) ? $base_value[ $content_key ] : '',
			'class' => 'widefat',
			'label' => __( 'Content:', 'hocwp-theme' )
		);

		$defaults = wp_parse_args( $content_args, $defaults );

		$content_callback = isset( $args['content_callback'] ) ? $args['content_callback'] : '';

		echo '<div class="content-image-box">';

		if ( ! is_callable( $content_callback ) ) {
			$content_callback = array( HT_HTML_Field(), $content_callback );
		}

		if ( is_callable( $content_callback ) ) {
			call_user_func( $content_callback, $defaults );
		} else {
			self::input( $defaults );
		}

		$id = $base_id;

		if ( ! empty( $id ) ) {
			$id .= '_image';
		}

		$name = $base_name;

		if ( ! empty( $name ) ) {
			$name .= '[image]';
		}

		$args = array(
			'id'    => $id,
			'name'  => $name,
			'value' => isset( $base_value['image'] ) ? $base_value['image'] : ''
		);

		self::media_upload( $args );

		echo '</div>'; /* Closing .content-image-box */
	}

	public static function image_link( $args = array() ) {
		$defaults = array(
			'content_key'      => 'link',
			'content_args'     => array(
				'label' => __( 'Image Link:', 'hocwp-theme' )
			),
			'content_callback' => array( 'HOCWP_Theme_HTML_Field', 'input_url' )
		);

		$args = wp_parse_args( $args, $defaults );

		self::content_with_image( $args );
	}

	public static function media_upload( $args = array() ) {
		$type       = isset( $args['type'] ) ? $args['type'] : '';
		$value      = isset( $args['value'] ) ? $args['value'] : '';
		$class      = 'select-media';
		$media_type = isset( $args['media_type'] ) ? $args['media_type'] : 'image';

		if ( 'image' != $media_type ) {
			$type = 'button';
		}

		if ( HT()->is_positive_number( $value ) || ( 'file' == $media_type && isset( $value['url'] ) ) && ! empty( $value['url'] ) ) {
			$class .= ' has-media';
		}

		$style = '';

		$background_color = isset( $args['background_color'] ) ? $args['background_color'] : '';

		if ( ! empty( $background_color ) && HT()->is_positive_number( $value ) ) {
			$style .= 'background-color:' . $background_color . ';';
		}

		$custom_style = isset( $args['style'] ) ? $args['style'] : '';

		if ( ! empty( $custom_style ) ) {
			$style .= $custom_style;
		}

		if ( 'button' == $type ) {
			$value = HT_Sanitize()->media_value( $value );
			$class .= ' button';
			$id    = isset( $value['id'] ) ? $value['id'] : '';
			$text  = __( 'Add media', 'hocwp-theme' );
			$url   = isset( $value['url'] ) ? $value['url'] : '';
			$rms   = 'display: none';

			if ( ! empty( $url ) ) {
				$rms = '';
			}
			?>
            <div class="media-box">
                <p class="hide-if-no-js">
                    <label>
                        <input class="regular-text media-url" id="<?php echo $args['id']; ?>_url"
                               name="<?php echo $args['name']; ?>[url]"
                               value="<?php echo $url; ?>"
                               type="text">
                    </label>
                    <a href="javascript:" class="<?php echo $class; ?>"
                       data-text="<?php echo $text; ?>" data-media-type="<?php echo esc_attr( $media_type ); ?>"
                       data-target="<?php echo $args['id']; ?>" style="<?php echo $style; ?>">
						<?php echo $text ?>
                    </a>
                    <button type="button"
                            class="remove-media-data button"
                            style="<?php echo $rms; ?>"
                            aria-label="<?php esc_attr_e( 'Remove media', 'hocwp-theme' ); ?>"><?php _e( 'Remove media', 'hocwp-theme' ); ?></button>
                </p>
                <input id="<?php echo $args['id']; ?>_id" name="<?php echo $args['name']; ?>[id]"
                       value="<?php echo $id; ?>"
                       type="hidden" class="media-id">
            </div>
			<?php
		} else {
			$text = sprintf( __( 'Choose %s', 'hocwp-theme' ), $media_type );
			?>
            <div class="media-box">
                <p class="hide-if-no-js">
                    <a href="javascript:" class="<?php echo $class; ?>"
                       data-text="<?php echo $text; ?>" data-media-type="<?php echo esc_attr( $media_type ); ?>"
                       data-target="<?php echo $args['id']; ?>" style="<?php echo $style; ?>">
						<?php
						if ( HT()->is_positive_number( $value ) ) {
							$img = new HOCWP_Theme_HTML_Tag( 'img' );
							$img->add_attribute( 'src', wp_get_attachment_url( $value ) );
							$img->output();
						} else {
							echo $text;
						}
						?>
                    </a>
                </p>
				<?php
				if ( HT()->is_positive_number( $value ) ) {
					$l10n = hocwp_theme_localize_script_l10n_media_upload();
					printf( $l10n['updateImageDescription'], $media_type );
					printf( $l10n['removeImageButton'], $media_type );
				}
				?>
                <input id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['name'] ); ?>"
                       value="<?php echo esc_attr( $value ); ?>"
                       type="hidden">
            </div>
			<?php
		}
	}

	public static function google_maps( $args = array() ) {
		$defaults = array(
			'latitude'     => '21.003118',
			'longitude'    => '105.820141',
			'scrollwheel'  => false,
			'zoom'         => 5,
			'marker_title' => __( 'Drag to find address!', 'hocwp-theme' ),
			'draggable'    => false,
			'address'      => '',
			'id'           => 'google_maps',
			'name'         => '',
			'value'        => ''
		);

		$args = wp_parse_args( $args, $defaults );

		$zoom = $args['zoom'];

		$latitude  = $args['latitude'];
		$longitude = $args['longitude'];

		$value = isset( $args['value'] ) ? $args['value'] : '';

		if ( ! empty( $value ) ) {
			$value = json_decode( $value, true );

			if ( isset( $value['lat'] ) && ! empty( $value['lat'] ) ) {
				$latitude = $value['lat'];
			}

			if ( isset( $value['lng'] ) && ! empty( $value['lng'] ) ) {
				$longitude = $value['lng'];
			}

			$zoom = absint( $zoom * 3 );
		}

		$div = new HOCWP_Theme_HTML_Tag( 'div' );
		$div->add_attribute( 'id', $args['id'] . '_marker' );
		$div->add_attribute( 'class', 'hocwp-field-maps google-maps-marker hocwp-theme' );
		$div->add_attribute( 'data-scrollwheel', HT()->bool_to_int( $args['scrollwheel'] ) );
		$post_id = isset( $args['post_id'] ) ? $args['post_id'] : '';
		$div->add_attribute( 'data-post-id', $post_id );
		$div->add_attribute( 'data-zoom', $zoom );
		$div->add_attribute( 'data-marker-title', $args['marker_title'] );
		$div->add_attribute( 'data-draggable', HT()->bool_to_int( $args['draggable'] ) );
		$div->add_attribute( 'data-address', $args['address'] );
		$div->add_attribute( 'data-latitude', $latitude );
		$div->add_attribute( 'data-longitude', $longitude );
		$div->add_attribute( 'style', 'width: 100 %; height: 350px; position: relative; background-color: rgb( 229, 227, 223 ); overflow: hidden;' );
		$div->output();

		if ( isset( $args['name'] ) && ! empty( $args['name'] ) ) {
			$input_args = array(
				'type'  => 'hidden',
				'id'    => $args['id'],
				'name'  => $args['name'],
				'value' => $args['value']
			);

			if ( isset( $args['required'] ) && $args['required'] ) {
				$input_args['required'] = 'required';
			}

			self::input( $input_args );
		}
	}

	public function update_meta( $args = array() ) {
		$defaults = array(
			'tag_name' => 'button',
			'text'     => ''
		);

		$args = wp_parse_args( $args, $defaults );

		$defaults = array(
			'data-meta-type'   => 'post',
			'data-meta-value'  => '',
			'data-ajax-meta'   => 1,
			'data-ajax-button' => 1,
			'data-meta-key'    => '',
			'data-id'          => '',
			'data-text'        => '',
			'data-undo-text'   => '',
			'data-object-id'   => ''
		);

		$attributes = isset( $args['attributes'] ) ? $args['attributes'] : array();

		if ( ! is_array( $attributes ) ) {
			$attributes = array();
		}

		$args['attributes'] = wp_parse_args( $attributes, $defaults );

		$html = new HOCWP_Theme_HTML_Tag( $args['tag_name'] );

		$html->set_attributes( $args['attributes'] );

		$html->set_text( $args['text'] );
		$html->output();
	}

	public function widget_field( $widget, $name, $label, $value, $callback = 'input', $args = array() ) {
		if ( $widget instanceof WP_Widget ) {
			$defaults = array(
				'id'    => $widget->get_field_id( $name ),
				'name'  => $widget->get_field_name( $name ),
				'value' => $value,
				'class' => 'widefat'
			);

			if ( is_string( $callback ) && 'input' == $callback ) {
				$defaults['type'] = 'text';
			}

			$args = wp_parse_args( $args, $defaults );

			$container = isset( $args['container'] ) ? $args['container'] : 'p';

			if ( 'p' == $container && is_string( $callback ) ) {
				if ( HT()->string_contain( $callback, 'sortable' ) || HT()->string_contain( $callback, 'editor' ) || HT()->string_contain( $callback, 'media' ) ) {
					$container = 'div';
				}
			}

			$c_atts = isset( $args['container_attributes'] ) ? $args['container_attributes'] : '';

			if ( is_array( $c_atts ) ) {
				$c_atts = HT()->attributes_to_string( $c_atts );
			}

			$c_atts = $container . ' ' . $c_atts;
			$c_atts = trim( $c_atts );
			printf( '<%s>', $c_atts );

			$right_label = isset( $args['right_label'] ) ? $args['right_label'] : '';

			if ( ! $right_label ) {
				$type = isset( $args['type'] ) ? $args['type'] : '';

				if ( 'radio' == $type || 'checkbox' == $type ) {
					$right_label = true;
				}
			}

			if ( 1 != $right_label && true != $right_label ) {
				HT_HTML_Field()->label( array( 'text' => $label, 'for' => $widget->get_field_id( $name ) ) );
			}

			if ( ! is_callable( $callback ) ) {
				$callback = array( __CLASS__, $callback );
			}

			if ( 'p' != $container ) {
				echo '<div class="clearfix">';
			}

			call_user_func( $callback, $args );

			if ( 1 == $right_label || true == $right_label ) {
				HT_HTML_Field()->label( array( 'text' => $label, 'for' => $widget->get_field_id( $name ) ) );
			}

			if ( 'p' != $container ) {
				echo '</div>';
			}

			if ( isset( $args['description'] ) ) {
				HT()->wrap_text( $args['description'], '<em class="desc">', '</em>', true );
			}

			printf( '</%s>', $container );
		}
	}
}

function HT_HTML_Field() {
	return HOCWP_Theme_HTML_Field::instance();
}