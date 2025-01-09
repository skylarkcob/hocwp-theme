<?php
defined( 'ABSPATH' ) || exit;

if ( ! trait_exists( 'HT_Controller' ) ) {
	trait HT_Controller {
		public function util() {
			return ht_util();
		}

		public function media() {
			return ht_media();
		}

		public function query() {
			return ht_query();
		}

		public function option() {
			return ht_options();
		}

		public function child() {
			return ht_child();
		}

		public function const() {
			return ht_const();
		}

		public function custom() {
			return ht_custom();
		}

		public function setting_tabs() {
			return ht_admin_setting_tabs();
		}

		public function admin() {
			return ht_admin();
		}

		public function vr() {
			return ht_vr();
		}

		public function captcha() {
			return ht_captcha();
		}

		public function color() {
			return ht_color();
		}

		public function enqueue() {
			return ht_enqueue();
		}

		public function extension() {
			return ht_extension();
		}

		public function frontend() {
			return ht_frontend();
		}

		public function html_field() {
			return ht_html_field();
		}

		public function message() {
			return ht_message();
		}

		public function minify() {
			return ht_minify();
		}

		public function requirement() {
			return ht_requirement();
		}

		public function sanitize() {
			return ht_sanitize();
		}

		public function svg_icon() {
			return ht_svg_icon();
		}
	}
}