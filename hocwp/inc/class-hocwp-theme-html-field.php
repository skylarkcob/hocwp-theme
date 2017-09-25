<?php

class HOCWP_Theme_HTML_Field {
	public static function input( $args = array() ) {
		$defaults = array(
			'type' => 'text'
		);
		$args     = wp_parse_args( $args, $defaults );
		if ( 'checkbox' == $args['type'] ) {
			$value = isset( $args['value'] ) ? absint( $args['value'] ) : 0;
			if ( 1 == $value ) {
				$args['checked'] = 'checked';
			}
			$args['value'] = 1;
		}
		$label = isset( $args['label'] ) ? $args['label'] : '';
		if ( 'radio' == $args['type'] || 'checkbox' == $args['type'] ) {
			$options = isset( $args['options'] ) ? $args['options'] : '';
			if ( is_array( $options ) && count( $options ) > 0 ) {
				unset( $args['options'] );
				$value = isset( $args['value'] ) ? $args['value'] : '';
				foreach ( $options as $key => $label ) {
					$atts  = $args;
					$lb    = new HOCWP_Theme_HTML_Tag( 'label' );
					$input = new HOCWP_Theme_HTML_Tag( 'input' );
					$id    = isset( $atts['id'] ) ? $atts['id'] : '';
					$id .= '_' . $key;
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
					$input->set_attributes( $atts );
					$lb->set_text( $input );
					$lb->output();
					echo '<br>';
				}

				return;
			}
		}
		$input = new HOCWP_Theme_HTML_Tag( 'input' );
		if ( ! empty( $label ) ) {
			$lb = new HOCWP_Theme_HTML_Tag( 'label' );
			$lb->add_attribute( 'for', $args['id'] );
			$lb->set_text( $label );
			if ( 'radio' == $args['type'] || 'checkbox' == $args['type'] ) {
				$input->set_text( $label );
				$input->set_parent( $lb );
			} else {
				$lb->output();
			}
			unset( $args['label'] );
		}
		$input->set_attributes( $args );
		$input->output();
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
		$textarea->set_attributes( $args );
		$textarea->output();
	}

	public static function editor( $args = array() ) {
		$args['textarea_name'] = $args['name'];
		if ( ! isset( $args['textarea_rows'] ) ) {
			$args['textarea_rows'] = 10;
		}
		wp_editor( $args['value'], $args['id'], $args );
	}

	public static function select( $args = array() ) {
		$value   = isset( $args['value'] ) ? $args['value'] : '';
		$select  = new HOCWP_Theme_HTML_Tag( 'select' );
		$options = isset( $args['options'] ) ? $args['options'] : '';
		unset( $args['value'], $args['options'] );
		$oh = '';
		foreach ( (array) $options as $key => $option ) {
			$opt = new HOCWP_Theme_HTML_Tag( 'option' );
			if ( is_array( $option ) ) {
				$text = isset( $option['text'] ) ? $option['text'] : $key;
				$ov   = isset( $option['value'] ) ? $option['value'] : $key;
				unset( $option['text'] );
				$option['value'] = $ov;
				$opt->set_attributes( $option );
			} else {
				if ( empty( $option ) ) {
					$text = $key;
				} else {
					$text = $option;
				}
				$opt->add_attribute( 'value', $key );
			}
			$opt->set_text( $text );
			$selected = selected( $value, $key, false );
			if ( ! empty( $selected ) ) {
				$opt->add_attribute( $selected );
			}
			$oh .= $opt->build();
		}
		$select->set_attributes( $args );
		$select->set_text( $oh );
		$select->output();
	}

	public static function media_upload( $args = array() ) {
		$type       = isset( $args['type'] ) ? $args['type'] : '';
		$value      = isset( $args['value'] ) ? $args['value'] : '';
		$class      = 'select-media';
		$media_type = isset( $args['media_type'] ) ? $args['media_type'] : 'image';
		if ( HOCWP_Theme::is_positive_number( $value ) ) {
			$class .= ' has-media';
		}
		if ( 'button' == $type ) {

		} else {
			$text = sprintf( __( 'Choose %s', 'hocwp-theme' ), $media_type );
			?>
			<div class="media-box">
				<p class="hide-if-no-js">
					<a href="javascript:" class="<?php echo $class; ?>"
					   data-text="<?php echo $text; ?>" data-media-type="<?php echo esc_attr( $media_type ); ?>">
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
}