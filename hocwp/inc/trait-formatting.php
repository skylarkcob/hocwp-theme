<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

trait HOCWP_Theme_Formatting {
	public function sanitize_phone_number( $phone ) {
		return preg_replace( '/[^\d+]/', '', $phone );
	}
}