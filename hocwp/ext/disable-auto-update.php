<?php
/*
 * Name: Disable Auto Updates
 * Description: Disable all auto updates, including: WordPress themes, plugins, and core.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'HOCWP_EXT_Disable_Auto_Update' ) ) {
	class HOCWP_EXT_Disable_Auto_Update extends HOCWP_Theme_Extension {
		protected static $instance;

		public static function get_instance() {
			if ( ! self::$instance instanceof self ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function __construct() {
			if ( self::$instance instanceof self ) {
				return;
			}

			parent::__construct( __FILE__ );

			add_filter( 'allow_dev_auto_core_updates', '__return_false' );
			add_filter( 'allow_minor_auto_core_updates', '__return_false' );
			add_filter( 'allow_major_auto_core_updates', '__return_false' );
			add_filter( 'auto_update_core', '__return_false' );
			add_filter( 'auto_update_plugin', '__return_false' );
			add_filter( 'auto_update_theme', '__return_false' );

			add_filter( 'site_transient_update_core', function ( $value ) {
				if ( is_object( $value ) && isset( $value->updates ) ) {
					if ( HT()->array_has_value( $value->updates ) ) {
						$value->updates = array();
					}
				}

				return $value;
			} );
		}
	}
}

HT_Extension()->register( new HOCWP_EXT_Disable_Auto_Update() );