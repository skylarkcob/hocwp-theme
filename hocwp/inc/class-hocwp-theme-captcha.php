<?php
defined( 'ABSPATH' ) || exit;

if ( ! trait_exists( 'HOCWP_Theme_CAPTCHA_Utils' ) ) {
	require_once( __DIR__ . '/trait-hocwp-theme-captcha-utils.php' );
}

class HOCWP_Theme_CAPTCHA {
	use HOCWP_Theme_CAPTCHA_Utils;

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

	}

	public function get_object( $service = '' ) {
		if ( empty( $service ) || 'auto' == $service ) {
			$service = $this->detect_service();
		}

		if ( $service == HOCWP_THEME_CAPTCHA_SERVICE::RECAPTCHA ) {
			$version = ht_options()->get_tab( 'recaptcha_version', '', 'social' );

			if ( empty( $version ) ) {
				$version = 'v3';
			}

			$version = apply_filters( 'hocwp_theme_recaptcha_version', $version, $this );

			return new HOCWP_Theme_RECAPTCHA( $version );
		}

		return new HOCWP_Theme_HCAPTCHA();
	}
}

function ht_captcha() {
	$service = apply_filters( 'hocwp_theme_captcha_service', 'auto' );

	return HOCWP_Theme_CAPTCHA::instance()->get_object( $service );
}