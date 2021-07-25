<?php
/*
 * Name: Classic Widgets
 * Description: Once activated, this extension restores the previous widgets settings screens and disables the block editor from managing widgets.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HOCWP_EXT_Classic_Widgets' ) ) {
	class HOCWP_EXT_Classic_Widgets extends HOCWP_Theme_Extension {
		protected static $instance;

		public static function get_instance() {
			if ( ! self::$instance instanceof self ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function __construct() {
			global $wp_version;

			if ( version_compare( $wp_version, '5.8', '<' ) ) {
				return;
			}

			if ( self::$instance instanceof self ) {
				return;
			}

			parent::__construct( __FILE__ );

			// Disables the block editor from managing widgets in the Gutenberg plugin.
			add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );
			// Disables the block editor from managing widgets.
			add_filter( 'use_widgets_block_editor', '__return_false' );

			remove_theme_support( 'widgets-block-editor' );
		}
	}
}

global $hocwp_theme;

if ( ! isset( $hocwp_theme->extensions ) || ! is_array( $hocwp_theme->extensions ) ) {
	$hocwp_theme->extensions = array();
}

$extension = HTE_Classic_Widgets()->get_instance();

$hocwp_theme->extensions[ $extension->basename ] = $extension;

function HTE_Classic_Widgets() {
	return HOCWP_EXT_Classic_Widgets::get_instance();
}