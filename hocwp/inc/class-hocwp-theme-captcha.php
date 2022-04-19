<?php
defined( 'ABSPATH' ) || exit;

final class HOCWP_THEME_CAPTCHA_SERVICE {
	const HCAPTCHA = 'hcaptcha';
	const RECAPTCHA = 'recaptcha';
}

class HOCWP_Theme_CAPTCHA {
	public $service = HOCWP_THEME_CAPTCHA_SERVICE::HCAPTCHA;
	public $attributes = array();
	public $script_prams = array();
	public $version = 'v3';
	public $post_key = '';

	public function __construct( $service = HOCWP_THEME_CAPTCHA_SERVICE::HCAPTCHA ) {
		if ( 'auto' == $service ) {
			$options    = HT_Options()->get_tab( null, null, 'social' );
			$site_key   = $options['hcaptcha_site_key'] ?? '';
			$secret_key = $options['hcaptcha_secret_key'] ?? '';

			if ( ! empty( $site_key ) && ! empty( $secret_key ) ) {
				$service = HOCWP_THEME_CAPTCHA_SERVICE::HCAPTCHA;
			} else {
				$site_key   = $options['recaptcha_site_key'] ?? '';
				$secret_key = $options['recaptcha_secret_key'] ?? '';

				if ( ! empty( $site_key ) && ! empty( $secret_key ) ) {
					$service = HOCWP_THEME_CAPTCHA_SERVICE::RECAPTCHA;
				}
			}
		}

		if ( $service == HOCWP_THEME_CAPTCHA_SERVICE::HCAPTCHA || $service == HOCWP_THEME_CAPTCHA_SERVICE::RECAPTCHA ) {
			$this->service = $service;
		}
	}

	public function set_attributes( $atts ) {
		if ( is_array( $atts ) ) {
			$this->attributes = $atts;
		}
	}

	public function set_script_params( $params ) {
		if ( is_array( $params ) ) {
			$this->script_prams = $params;
		}
	}

	public function set_version( $version ) {
		if ( ! empty( $version ) ) {
			$version = ltrim( $version, 'v' );

			$this->version = 'v' . $version;
		}
	}

	public function display_html() {
		switch ( $this->service ) {
			case HOCWP_THEME_CAPTCHA_SERVICE::RECAPTCHA:
				$this->post_key = 'g-recaptcha-response';
				HT_Util()->recaptcha( $this->version );
				break;
			case HOCWP_THEME_CAPTCHA_SERVICE::HCAPTCHA:
				$this->post_key = 'h-captcha-response';
				HT_Util()->hcaptcha( $this->attributes, $this->script_prams );
				break;
		}
	}

	public function check_valid() {
		return match ( $this->service ) {
			HOCWP_THEME_CAPTCHA_SERVICE::RECAPTCHA => HT_Util()->recaptcha_valid(),
			HOCWP_THEME_CAPTCHA_SERVICE::HCAPTCHA => HT_Util()->hcaptcha_valid(),
			default => new WP_Error( 'empty_service', __( 'CAPTCHA service does not provide.', 'hocwp-theme' ) ),
		};

	}
}