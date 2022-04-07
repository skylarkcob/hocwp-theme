<?php
defined( 'ABSPATH' ) || exit;

class HOCWP_Theme_Maps_Find_Place extends HOCWP_Theme_Google_Maps_API {
	public function __construct( $params, $output = '' ) {
		if ( HT()->array_has_value( $params ) ) {
			/*
			 * https://developers.google.com/maps/documentation/places/web-service/search-find-place
			 */
			$defaults = array(
				'input'        => '',
				'inputtype'    => 'textquery',
				'fields'       => 'business_status,formatted_address,geometry,icon,icon_mask_base_uri,icon_background_color,name,photo,place_id,plus_code,type,opening_hours,price_level,rating,user_ratings_total',
				'language'     => get_locale(),
				'locationbias' => 'ipbias'
			);

			$params = wp_parse_args( $params, $defaults );
		}

		parent::__construct( 'place', 'findplacefromtext', $params, $output );
	}

	public function is_valid() {
		$valid = parent::is_valid(); // TODO: Change the autogenerated stub

		return ( $valid && isset( $this->result->candidates ) && HT()->array_has_value( $this->result->candidates ) );
	}
}