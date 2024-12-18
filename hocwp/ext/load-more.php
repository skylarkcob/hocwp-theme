<?php
/*
 * Name: Load More
 * Description: Replace pagination with load more button.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'hocwp_theme_load_extension_load_more' ) ) {
	function hocwp_theme_load_extension_load_more() {
		$load = ht_extension()->is_active( __FILE__ );

		return apply_filters( 'hocwp_theme_load_extension_load_more', $load );
	}
}

$load = hocwp_theme_load_extension_load_more();

if ( ! $load ) {
	return;
}

if ( ! class_exists( 'HOCWP_Ext_Load_More' ) ) {
	final class HOCWP_Ext_Load_More extends HOCWP_Theme_Extension {
		protected static $instance;

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
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts_action' ) );
		}

		public function wp_enqueue_scripts_action() {
			wp_enqueue_script( 'load-more', $this->folder_url . '/script.js', array( 'jquery' ), false, true );

			$l10n = array(
				'button' => '<button class="load-more btn button" data-loading="' . esc_attr__( 'Loading...', 'hocwp-theme' ) . '">' . __( 'View more', 'hocwp-theme' ) . '</button>'
			);

			wp_localize_script( 'load-more', 'HTELoadMore', $l10n );
		}
	}
}

if ( ! function_exists( 'hte_load_more' ) ) {
	function hte_load_more() {
		return HOCWP_Ext_Load_More::get_instance();
	}
}

hte_load_more()->get_instance();