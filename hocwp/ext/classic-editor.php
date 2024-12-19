<?php
/*
 * Name: Classic Editor
 * Description: Once activated, this extension restores the previous ("classic") WordPress editor and the "Edit Post" screen.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HOCWP_EXT_Classic_Editor' ) ) {
	class HOCWP_EXT_Classic_Editor extends HOCWP_Theme_Extension {
		protected static $instance;

		public static function get_instance() {
			if ( ! self::$instance instanceof self ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function __construct() {
			global $wp_version;

			if ( version_compare( $wp_version, '5.0', '<' ) ) {
				return;
			}

			if ( self::$instance instanceof self ) {
				return;
			}

			parent::__construct( __FILE__ );

			add_filter( 'gutenberg_can_edit_post', '__return_false', 5 );
			add_filter( 'use_block_editor_for_post', '__return_false', 5 );
			add_filter( 'use_block_editor_for_post_type', '__return_false', 5 );
		}
	}
}

if ( ! isset( hocwp_theme_object()->extensions ) || ! is_array( hocwp_theme_object()->extensions ) ) {
	hocwp_theme_object()->extensions = array();
}

$extension = hte_classic_editor()->get_instance();

hocwp_theme_object()->extensions[ $extension->basename ] = $extension;

function hte_classic_editor() {
	return HOCWP_EXT_Classic_Editor::get_instance();
}