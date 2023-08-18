<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( file_exists( dirname( __FILE__ ) . '/constants.php' ) ) {
	require_once dirname( __FILE__ ) . '/constants.php';
}

if ( ! trait_exists( 'HTC_Functions' ) ) {
	require_once dirname( __FILE__ ) . '/trait-functions.php';
}

class HOCWP_Theme_Custom {
	use HTC_Functions;

	/*
	 * Default function to register post type and taxonomy. Do not remove it.
	 */
	public function register_post_type_and_taxonomy() {
	}

	/*
	 * Default enqueue script action for front-end before core styles and scripts loaded. Do not remove it.
	 */
	public function enqueue_scripts_early() {
	}

	/*
	 * Default enqueue script action for front-end. Do not remove it.
	 */
	public function enqueue_scripts() {
	}

	/*
	 * Default theme general setting fields filter. Do not remove it.
	 */
	public function general_setting_fields( $fields, $options ) {
		return $fields;
	}

	/*
	 * Default theme home setting fields filter. Do not remove it.
	 */
	public function home_setting_fields( $fields, $options ) {
		return $fields;
	}

	/*
	 * Default theme general setting sections filter. Do not remove it.
	 */
	public function general_setting_sections( $sections ) {
		return $sections;
	}

	/*
	 * Default theme home setting sections filter. Do not remove it.
	 */
	public function home_setting_sections( $sections ) {
		return $sections;
	}

	/*
	 * Default AJAX action callback to execute AJAX on website. Do not remove it.
	 */
	public function ajax_callback() {
		$data   = array();
		$method = HT()->get_method_value( 'method', 'request', 'post' );
		$action = HT()->get_method_value( 'do_action', $method );

		switch ( $action ) {
			default:
		}

		wp_send_json_error( $data );
	}

	/*
	 * Default private AJAX action callback to execute AJAX on website. Do not remove it.
	 */
	public function ajax_private_callback() {
		$data   = array();
		$method = HT()->get_method_value( 'method', 'request', 'post' );
		$action = HT()->get_method_value( 'do_action', $method );

		switch ( $action ) {
			default:
		}

		wp_send_json_error( $data );
	}

	/*
	 * Default action to create post meta fields. Do not remove it.
	 */
	public function post_meta() {
	}

	/*
	 * Default action to create term meta fields. Do not remove it.
	 */
	public function term_meta() {
	}

	/*
	 * Default action to load custom hooks on website. Do not remove it.
	 */
	private function load_custom_hook() {
	}

	/*
	 * Default widgets init action for register sidebar or widget. Do not remove it.
	 */
	public function widgets_init() {
	}

	/*
	 * Default widgets init action for register nav menu. Do not remove it.
	 */
	public function menus_init() {

	}

	/*
	 * Default post and term meta configuration. Do not remove it.
	 */
	public function meta_config() {
		// =============== POST META CONFIGURATION =============== //


		// =============== TERM META CONFIGURATION =============== //

	}

	/* =============== That's all, stop editing! Happy coding. =============== */

	protected static $instance;

	public $term_meta_keys = array();
	public $post_meta_keys = array();

	/*
	 * Default function to get single instance for this class. Do not remove or change it.
	 */
	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function get_path_or_url( $suffix = '', $in_child = false, $url = false ) {
		if ( ! $in_child && HT_Control()->is_child_theme && defined( 'HOCWP_THEME_FORCE_PARENT' ) && HOCWP_THEME_FORCE_PARENT ) {
			$in_child = true;
		}

		if ( $url ) {
			$current = HOCWP_THEME_CUSTOM_CURRENT_URL;
			$base    = HOCWP_THEME_CUSTOM_URL;
		} else {
			$current = HOCWP_THEME_CUSTOM_CURRENT_PATH;
			$base    = HOCWP_THEME_CUSTOM_PATH;
		}

		if ( $in_child ) {
			return HT_Util()->get_path_or_url( $current, $suffix );
		}

		return HT_Util()->get_path_or_url( $base, $suffix );
	}

	/*
	 * Default function to Get theme custom folder url. Do not remove or change it.
	 */
	public function get_url( $suffix = '', $in_child = false ) {
		return $this->get_path_or_url( $suffix, $in_child, true );
	}

	/*
	 * Default function to Get theme custom folder path. Do not remove or change it.
	 */
	public function get_path( $suffix = '', $in_child = false ) {
		return $this->get_path_or_url( $suffix, $in_child );
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

	/*
	 * Default init action to load all hooks and template. Do not remove or change it.
	 */
	public function custom_after_setup_theme_action() {
		add_action( 'init', array( $this, 'register_post_type_and_taxonomy' ), 0 );

		global $hocwp_theme_metas;

		if ( ! ( $hocwp_theme_metas instanceof HOCWP_Theme_Metas ) ) {
			$hocwp_theme_metas = new HOCWP_Theme_Metas();
		}

		if ( ! class_exists( 'HOCWP_Theme_Meta_Field' ) ) {
			require_once HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-meta-field.php';
		}

		$this->meta_config();

		add_filter( 'hocwp_theme_post_meta_keys', array( $this, 'post_meta_keys_filter' ), 10, 2 );
		add_filter( 'hocwp_theme_term_meta_keys', array( $this, 'term_meta_keys_filter' ), 10, 2 );

		if ( is_admin() ) {
			add_filter( 'hocwp_theme_setting_fields', array( $this, 'general_setting_fields' ), 99, 2 );
			add_filter( 'hocwp_theme_setting_page_home_fields', array( $this, 'home_setting_fields' ), 99, 2 );
			add_filter( 'hocwp_theme_setting_sections', array( $this, 'general_setting_sections' ) );
			add_filter( 'hocwp_theme_setting_page_home_sections', array( $this, 'home_setting_sections' ) );

			add_action( 'load-post.php', array( $this, 'post_meta' ) );
			add_action( 'load-post-new.php', array( $this, 'post_meta' ) );

			add_action( 'load-edit-tags.php', array( $this, 'term_meta' ) );

			if ( HOCWP_THEME_DOING_AJAX ) {
				add_action( 'wp_ajax_hocwp_theme_ajax', array( $this, 'ajax_callback' ) );
				add_action( 'wp_ajax_nopriv_hocwp_theme_ajax', array( $this, 'ajax_callback' ) );
				add_action( 'wp_ajax_hocwp_theme_ajax_private', array( $this, 'ajax_private_callback' ) );
			}
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_early' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 99 );
		}

		add_action( 'widgets_init', array( $this, 'widgets_init' ) );
		$this->menus_init();

		$this->load_custom_hook();
	}

	/**
	 * Add meta field to array list. Do not remove or change it.
	 *
	 * @param array|HOCWP_Theme_Meta_Field $field The meta field for post or term.
	 * @param string $type_name Post type name or taxonomy name.
	 * @param string $type This is a post_type type or taxonomy.
	 */
	private function add_meta_field_to_array( $field, $type_name, $type = 'post_type' ) {
		if ( $field instanceof HOCWP_Theme_Meta_Field ) {
			global $hocwp_theme_metas;

			if ( ! ( $hocwp_theme_metas instanceof HOCWP_Theme_Metas ) ) {
				$hocwp_theme_metas = new HOCWP_Theme_Metas();
			}

			$add = $hocwp_theme_metas->add( $field, $type_name, $type );

			if ( 'post_type' == $type ) {
				$this->post_meta_keys[ $field->get_id() ] = $add[1];
			} elseif ( 'taxonomy' == $type ) {
				$this->term_meta_keys[ $field->get_id() ] = $add[1];
			}
		}
	}

	/*
	 * Default post meta keys filter. Do not remove or change it.
	 */
	public function post_meta_keys_filter( $keys, $object ) {
		if ( ! is_array( $keys ) ) {
			$keys = array();
		}

		if ( HT()->array_has_value( $this->post_meta_keys ) && $object instanceof HOCWP_Theme_Post ) {
			if ( $object->is() ) {
				$object = $object->get();

				foreach ( $this->post_meta_keys as $key => $post_type ) {
					if ( ! in_array( $key, $keys ) && ( ( is_string( $post_type ) && $post_type == $object->post_type ) || ( is_array( $post_type ) && in_array( $object->post_type, $post_type ) ) ) ) {
						$keys[] = $key;
					}
				}
			}
		}

		return $keys;
	}

	/*
	 * Default term meta keys filter. Do not remove or change it.
	 */
	public function term_meta_keys_filter( $keys, $object ) {
		if ( ! is_array( $keys ) ) {
			$keys = array();
		}

		if ( HT()->array_has_value( $this->term_meta_keys ) && $object instanceof HOCWP_Theme_Term ) {
			if ( $object->is() ) {
				$object = $object->get();

				foreach ( $this->term_meta_keys as $key => $taxonomy ) {
					if ( ! in_array( $key, $keys ) && ( ( is_string( $taxonomy ) && $taxonomy == $object->taxonomy ) || ( is_array( $taxonomy ) && in_array( $object->taxonomy, $taxonomy ) ) ) ) {
						$keys[] = $key;
					}
				}
			}
		}

		return $keys;
	}

	/**
	 * Load theme custom module.
	 *
	 * @param string $name The full template PHP file name or without module and PHP extension.
	 */
	public function load_module( $name ) {
		hocwp_theme_load_custom_module( $name );
	}

	/**
	 * Load theme custom loop.
	 *
	 * @param string $name The full template PHP file name or without module and PHP extension.
	 */
	public function load_loop( $name ) {
		hocwp_theme_load_custom_loop( $name );
	}

	/**
	 * Load theme custom template.
	 *
	 * @param string $name The full template PHP file name or without module and PHP extension.
	 */
	public function load_template( $name ) {
		hocwp_theme_load_custom_template( $name );
	}

	/**
	 * Get image url in HocWP Theme custom folder.
	 *
	 * @param string $name The image name or path in sub-folder.
	 *
	 * @return string The full image url.
	 */
	public function get_image_url( $name ) {
		return HT_Util()->get_custom_image_url( $name );
	}
}

function HT_Custom() {
	return HOCWP_Theme_Custom::get_instance();
}

HT_Custom();