<?php
function hocwp_theme_debug( $value ) {
	if ( HOCWP_THEME_DEVELOPING ) {
		if ( is_array( $value ) || is_object( $value ) ) {
			error_log( print_r( $value, true ) );
		} else {
			error_log( $value );
		}
	}
}