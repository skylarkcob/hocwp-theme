<?php
/*
 * Name: VR Tour
 * Description: Create VR Tour website.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'hocwp_theme_load_extension_vtour' ) ) {
	function hocwp_theme_load_extension_vtour() {
		$load = ht_extension()->is_active( __FILE__ );

		return apply_filters( 'hocwp_theme_load_extension_vtour', $load );
	}
}

$load = hocwp_theme_load_extension_vtour();

if ( ! $load ) {
	return;
}

if ( ! class_exists( 'HOCWP_Ext_VR' ) ) {
	final class HOCWP_Ext_VR extends HOCWP_Theme_Extension {
		protected static $instance;
		public $folder_name = 'vtour';

		public static function get_instance() {
			if ( ! ( self::$instance instanceof self ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function __construct() {
			if ( self::$instance instanceof self ) {
				return;
			}

			parent::__construct( __FILE__ );

			add_action( 'template_redirect', array( $this, 'template_redirect_action' ), 1 );
			add_filter( 'template_include', array( $this, 'template_include_filter' ), 999999 );
			require_once( __DIR__ . '/vtour/class-hocwp-theme-vr.php' );
			add_filter( 'body_class', array( $this, 'body_class_filter' ), 999999 );
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts_action' ), 9999 );
		}

		public function template_include_filter() {
			return $this->folder_path . '/template-index.php';
		}

		public function body_class_filter( $classes ) {
			$classes[] = 'vtour';

			return $classes;
		}

		public function template_redirect_action() {
			if ( ! is_home() && ! is_front_page() ) {
				wp_redirect( home_url() );
				exit;
			}
		}

		public function wp_enqueue_scripts_action() {
			wp_dequeue_script( 'query-monitor' );

			wp_enqueue_style( 'google-fonts', 'https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;0,700;1,400;1,500;1,700&display=swap' );
			wp_enqueue_script( 'lozad' );

			wp_enqueue_script( 'lozad' );
			wp_enqueue_style( 'wp-mediaelement' );
			wp_enqueue_script( 'wp-mediaelement' );
			wp_enqueue_style( 'hte-vr-style', $this->folder_url . '/style.css' );
			wp_enqueue_script( 'hte-vr', $this->folder_url . '/script.js', array( 'jquery' ) );
		}
	}
}

if ( ! function_exists( 'hte_vr' ) ) {
	function hte_vr() {
		return HOCWP_Ext_VR::get_instance();
	}
}

hte_vr()->get_instance();