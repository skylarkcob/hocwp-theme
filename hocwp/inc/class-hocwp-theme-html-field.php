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
			$input->set_text( $label );
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
}