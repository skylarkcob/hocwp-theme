<?php
defined( 'ABSPATH' ) || exit;

class HOCWP_Theme_HCAPTCHA extends Abstract_HOCWP_Theme_CAPTCHA {
	public $service = HOCWP_THEME_CAPTCHA_SERVICE::HCAPTCHA;
	public $post_key = 'h-captcha-response';

	public function display_html() {
		// TODO: Implement display_html() method.
		$this->hcaptcha( $this->attributes, $this->script_prams, $this->insert_before );
	}

	public function check_valid( $params = array() ) {
		// TODO: Implement check_valid() method.
		return $this->hcaptcha_valid( $params );
	}
}