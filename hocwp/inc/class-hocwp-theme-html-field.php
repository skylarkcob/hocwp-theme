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

			$lb->add_attribute( 'for', $args['id'] );
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

		if ( 'radio' == $args['type'] || 'checkbox' == $args['type'] ) {
			$options = isset( $args['options'] ) ? $args['options'] : '';

			if ( is_array( $options ) && count( $options ) > 0 ) {
				unset( $args['options'] );
				$value = isset( $args['value'] ) ? $args['value'] : '';

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
					$id .= '_' . $key;

					if ( empty( $label ) ) {
						$label = $id;
					}

					$lb->add_attribute( 'for', $id );

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

					$input->set_attributes( $atts );
					$lb->set_text( $input );
					$lb->output();
					echo '<br>';
				}

				return;
			}
		}

		$input = new HOCWP_Theme_HTML_Tag( 'input' );
		self::field_label( $args, $input );
		$input->set_attributes( $args );
		$input->output();
	}

	public static function input_url( $args = array() ) {
		$args['type'] = 'url';
		self::input( $args );
	}

	public static function input_email( $args = array() ) {
		$args['type'] = 'email';
		self::input( $args );
	}

	public static function datetime_picker( $args = array() ) {
		$args['data-datetime-picker'] = 1;
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
		$textarea->set_attributes( $args );
		$textarea->output();
	}

	public static function editor( $args = array() ) {
		if ( ! isset( $args['name'] ) && isset( $args['id'] ) ) {
			$args['name'] = $args['id'];
		}
		$args['textarea_name'] = $args['name'];
		if ( ! isset( $args['textarea_rows'] ) ) {
			$args['textarea_rows'] = 10;
		}
		wp_editor( $args['value'], $args['id'], $args );
	}

	public static function label( $args = array() ) {
		$label = new HOCWP_Theme_HTML_Tag( 'label' );
		$label->set_attributes( $args );
		$text = isset( $args['text'] ) ? $args['text'] : '';
		$label->set_text( $text );
		$label->output();
	}

	public static function option( $selected, $current, $args, $echo = false ) {
		$opt = new HOCWP_Theme_HTML_Tag( 'option' );
		if ( is_array( $args ) ) {
			$text = isset( $args['text'] ) ? $args['text'] : $current;
			$ov   = isset( $args['value'] ) ? $args['value'] : $current;
			unset( $args['text'] );
			$args['value'] = $ov;
			$opt->set_attributes( $args );
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

					$oh .= '<option value=""></option>';
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

		$select->set_attributes( $args );
		$select->set_text( $oh );
		$select->output();
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
							'text'          => $obj->name,
							'value'         => $taxonomy . ',' . $obj->term_id,
							'data-term'     => $obj->term_id,
							'data-taxonomy' => $taxonomy
						);
					}
				}
			} else {
				$terms = HT_Util()->get_terms( $taxonomy, $term_args );

				foreach ( $terms as $obj ) {
					$options[ $obj->term_id ] = $obj->name;
				}
			}

			$args['options'] = $options;

			if ( ! isset( $args['option_all'] ) && 'widgets.php' != $pagenow ) {
				$args['option_all'] = __( '-- Choose term --', 'hocwp-theme' );
			}
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

	public static function sortable( $args = array() ) {
		$lists    = isset( $args['lists'] ) ? $args['lists'] : '';
		$lists    = (array) $lists;
		$lists    = array_filter( $lists );
		$connects = isset( $args['connects'] ) ? $args['connects'] : true;

		$value = isset( $args['value'] ) ? $args['value'] : '';

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
						$lists[] = $options[ $item ];
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
			$class = 'sortable hocwp-theme-sortable';

			$has_sub = isset( $args['has_sub'] ) ? $args['has_sub'] : false;

			if ( $connects || HT()->array_has_value( $connects ) ) {
				$ul->add_attribute( 'data-connect-with', $id );

				$class .= ' connect-lists';

				if ( ! $has_sub ) {
					$class .= ' ' . $id;
				}
			}

			$ul->add_attribute( 'class', $class );

			if ( ! $has_sub ) {
				$ul->add_attribute( 'data-sortable', 1 );
			}

			$li_html = '';

			foreach ( $lists as $list ) {
				if ( empty( $list ) ) {
					continue;
				}

				if ( ! HT()->string_contain( $list, '</li>' ) ) {
					$li = new HOCWP_Theme_HTML_Tag( 'li' );
					$li->add_attribute( 'class', 'ui-state-default' );
					$li->set_text( $list );
					$li_html .= $li->build();
				} else {
					$li_html .= $list;
				}
			}

			$ul->set_text( $li_html );
			$ul->output();

			if ( $connects || HT()->array_has_value( $connects ) ) {
				$class .= ' connected-result ';
				$ul = new HOCWP_Theme_HTML_Tag( 'ul' );
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
					foreach ( (array) $connects as $list ) {
						if ( empty( $list ) ) {
							continue;
						}

						if ( ! HT()->string_contain( $list, '</li>' ) ) {
							$li = new HOCWP_Theme_HTML_Tag( 'li' );
							$li->add_attribute( 'class', 'ui-state-default' );
							$li->set_text( $list );
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
				$obj = get_term_by( 'id', $std->id, $std->taxonomy );

				if ( $obj instanceof WP_Term ) {
					$results[ $obj->term_id ] = $obj;

					$tax = get_taxonomy( $obj->taxonomy );

					$sub = $id . '_' . $tax->name;

					$connects[] = '<li class="ui-state-default" data-taxonomy="' . $obj->taxonomy . '" data-id="' . $obj->term_id . '" data-connect-list="' . $sub . '">' . $obj->name . ' (' . $tax->labels->singular_name . ')</li>';
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

						$tmp .= '<li class="ui-state-default" data-taxonomy="' . $taxonomy . '" data-id="' . $obj->term_id . '" data-connect-list="' . $sub . '">' . $obj->name . ' (' . $tax->labels->singular_name . ')</li>';
					}

					$ul->set_text( $tmp );

					$item .= $ul->build();
				}

				$item .= '</li> ';
				$lists[] = $item;
			}

			$args['connect_sub'] = trim( $connect_sub );
		} else {
			$tax   = get_taxonomy( $taxonomy );
			$terms = HT_Util()->get_terms( $taxonomy, $term_args );

			foreach ( $terms as $obj ) {
				if ( array_key_exists( $obj->term_id, $results ) ) {
					continue;
				}

				$lists[] = '<li class="ui-state-default" data-taxonomy="' . $obj->taxonomy . '" data-id="' . $obj->term_id . '"> ' . $obj->name . ' (' . $tax->labels->singular_name . ')</li> ';
			}
		}

		$args['lists'] = $lists;
		self::sortable( $args );
	}

	public static function size( $args = array() ) {
		$name        = $args['name'];
		$name_width  = $name . '[width]';
		$name_height = $name . '[height]';
		$class       = isset( $args['class'] ) ? $args['class'] : '';
		$class .= ' small-text';
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
		self::input( $args );
	}

	public static function media_upload( $args = array() ) {
		$type       = isset( $args['type'] ) ? $args['type'] : '';
		$value      = isset( $args['value'] ) ? $args['value'] : '';
		$class      = 'select-media';
		$media_type = isset( $args['media_type'] ) ? $args['media_type'] : 'image';

		if ( HOCWP_Theme::is_positive_number( $value ) ) {
			$class .= ' has-media';
		}

		$style = '';

		$background_color = isset( $args['background_color'] ) ? $args['background_color'] : '';

		if ( ! empty( $background_color ) && HOCWP_Theme::is_positive_number( $value ) ) {
			$style .= 'background-color:' . $background_color . ';';
		}

		$custom_style = isset( $args['style'] ) ? $args['style'] : '';

		if ( ! empty( $custom_style ) ) {
			$style .= $custom_style;
		}

		if ( 'button' == $type ) {

		} else {
			$text = sprintf( __( 'Choose %s', 'hocwp-theme' ), $media_type );
			?>
			<div class="media-box">
				<p class="hide-if-no-js">
					<a href="javascript:" class="<?php echo $class; ?>"
					   data-text="<?php echo $text; ?>" data-media-type="<?php echo esc_attr( $media_type ); ?>"
					   data-target="<?php echo $args['id']; ?>" style="<?php echo $style; ?>">
						<?php
						if ( HOCWP_Theme::is_positive_number( $value ) ) {
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
				if ( HOCWP_Theme::is_positive_number( $value ) ) {
					$l10n = hocwp_theme_localize_script_l10n_media_upload();
					printf( $l10n['updateImageDescription'], $media_type );
					printf( $l10n['removeImageButton'], $media_type );
				}
				?>
				<input id="<?php echo $args['id']; ?>" name="<?php echo $args['name']; ?>" value="<?php echo $value; ?>"
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
		$args     = wp_parse_args( $args, $defaults );

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

			HT_HTML_Field()->label( array( 'text' => $label, 'for' => $widget->get_field_id( $name ) ) );

			if ( ! is_callable( $callback ) ) {
				$callback = array( __CLASS__, $callback );
			}

			if ( 'p' != $container ) {
				echo '<div class="clearfix">';
			}

			call_user_func( $callback, $args );

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