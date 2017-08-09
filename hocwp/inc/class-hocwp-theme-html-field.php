<?php

class HOCWP_Theme_HTML_Field {
	public static function input( $args = array() ) {
		$defaults = array(
			'type' => 'text'
		);
		$args     = wp_parse_args( $args, $defaults );
		$input    = new HOCWP_Theme_HTML_Tag( 'input' );
		$input->set_attributes( $args );
		$input->output();
	}
}