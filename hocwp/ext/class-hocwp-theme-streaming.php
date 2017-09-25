<?php

abstract class HOCWP_Theme_Streaming {
	public $pattern;
	public $url;
	public $username;
	public $password;

	public function __construct() {

	}

	public function sanitize_url() {
		preg_match_all( $this->pattern, trailingslashit( $this->url ), $matches, PREG_SET_ORDER, 0 );

		return $matches;
	}
}