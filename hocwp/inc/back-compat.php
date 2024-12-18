<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'HOCWP_Theme_Custom' ) ) {
	class HOCWP_Theme_Custom extends Abstract_HT_Custom {
		protected static $instance;

		/*
		 * Default function to get single instance for this class. Do not remove or change it.
		 */
		public static function get_instance() {
			if ( ! ( self::$instance instanceof self ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/*
		 * Default construct function. Do not remove or change it.
		 */
		public function __construct() {
			if ( self::$instance instanceof self ) {
				return;
			}

			add_action( 'after_setup_theme', array( $this, 'custom_after_setup_theme_action' ), 1 );
		}
	}
}

if ( ! function_exists( 'ht_custom' ) ) {
	function ht_custom() {
		return HOCWP_Theme_Custom::get_instance();
	}
}