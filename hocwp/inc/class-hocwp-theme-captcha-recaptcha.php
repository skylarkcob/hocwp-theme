<?php
defined( 'ABSPATH' ) || exit;

class HOCWP_Theme_RECAPTCHA extends Abstract_HOCWP_Theme_CAPTCHA {
	public $version = 'v3';
	public $service = HOCWP_THEME_CAPTCHA_SERVICE::RECAPTCHA;
	public $post_key = 'g-recaptcha-response';

	public function __construct( $version = '' ) {
		parent::__construct();
		$this->set_version( $version );
	}

	public function set_version( $version ) {
		if ( ! empty( $version ) ) {
			if ( 'enterprise' != $version ) {
				$version = ltrim( $version, 'v' );
				$version = 'v' . $version;
			}

			$this->version = $version;
		}
	}

	public function display_html() {
		// TODO: Implement display_html() method.
		$this->attributes['version'] = $this->version;
		$this->recaptcha( $this->attributes, $this->script_prams, $this->insert_before );
	}

	public function check_valid( $params = array() ) {
		// TODO: Implement check_valid() method.
		return $this->recaptcha_valid( $params );
	}
}