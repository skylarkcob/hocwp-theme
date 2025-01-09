<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HT_Constants' ) ) {
	class HT_Constants {
		// Custom post type

		// Custom taxonomy

		// Rewrite slug

		// Post meta

		// Term meta

		private static $instance = null;

		public static function get_instance() {
			if ( self::$instance === null ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}

	if ( ! function_exists( 'ht_const' ) ) {
		function ht_const() {
			return HT_Constants::get_instance();
		}
	}
}