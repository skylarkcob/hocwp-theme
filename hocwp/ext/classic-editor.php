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

global $hocwp_theme;

if ( ! isset( $hocwp_theme->extensions ) || ! is_array( $hocwp_theme->extensions ) ) {
	$hocwp_theme->extensions = array();
}

$extension = HTE_Classic_Editor()->get_instance();

$hocwp_theme->extensions[ $extension->basename ] = $extension;

function HTE_Classic_Editor() {
	return HOCWP_EXT_Classic_Editor::get_instance();
}