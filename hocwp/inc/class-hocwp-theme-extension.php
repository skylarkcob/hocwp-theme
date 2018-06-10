<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Extension {
	public $name;
	public $description;

	public $basename;
	public $file;

	public $data;

	public $required_plugins;
	public $required_extensions;
	public $is_active;

	public $basedir;
	public $folder_name = '';

	public $folder_path;
	public $folder_url;

	public $option_name;

	public function __construct( $file = null ) {
		if ( null == $file ) {
			$file = __FILE__;
		}

		$this->file    = $file;
		$this->basedir = dirname( $this->file );

		$this->folder_path = $this->basedir;

		if ( empty( $this->folder_url ) ) {
			$this->folder_url = HOCWP_Theme()->core_url . '/ext';
		}

		if ( empty( $this->folder_name ) ) {
			$this->set_folder_name();
		}

		if ( empty( $this->option_name ) ) {
			$this->set_option_name();
		}

		$this->folder_path = trailingslashit( $this->folder_path );
		$this->folder_path .= $this->folder_name;

		$this->folder_url = trailingslashit( $this->folder_url );
		$this->folder_url .= $this->folder_name;

		$this->get_headers();

		if ( isset( $this->data['Name'] ) && ! empty( $this->data['Name'] ) ) {
			$this->name        = $this->data['Name'];
			$this->description = $this->data['Description'];
		} else {
			_doing_it_wrong( __CLASS__, __( 'Please declare extension with Name and Description in header.', 'hocwp-theme' ), '6.4.2' );

			return;
		}

		$this->basename = HT_Extension()->get_basename( $this->file );

		add_filter( 'hocwp_theme_required_extensions', array( $this, 'required_extensions' ) );

		if ( is_admin() ) {
			if ( HT_Admin()->is_theme_option_page() ) {
				add_filter( 'hocwp_theme_settings_page_tabs', array( $this, 'option_tabs' ) );
				add_filter( 'hocwp_theme_settings_page_' . $this->option_name . '_settings_section', array(
					$this,
					'option_sections'
				) );
				add_filter( 'hocwp_theme_settings_page_' . $this->option_name . '_settings_field', array(
					$this,
					'option_fields'
				) );
				add_filter( 'hocwp_theme_sanitize_option_' . $this->option_name, array( $this, 'sanitize_options' ) );
				add_action( 'hocwp_theme_admin_setting_page_' . $this->option_name . '_scripts', array(
					$this,
					'option_scripts'
				) );
			}
		}
	}

	public function option_tabs( $tabs ) {
		return $tabs;
	}

	public function option_sections() {
		return array();
	}

	public function option_fields() {
		return array();
	}

	public function sanitize_options( $input ) {
		return $input;
	}

	public function option_scripts() {

	}

	public function set_option_name( $name = '' ) {
		if ( empty( $name ) ) {
			$name = $this->folder_name;
			$name = str_replace( '-', '_', $name );
		}

		$this->option_name = $name;
	}

	public function set_folder_name( $name = '' ) {
		if ( empty( $name ) ) {
			$name = sanitize_title( $this->name );
		}

		$this->folder_name = $name;
	}

	public function required_extensions( $extensions ) {
		if ( HT()->array_has_value( $this->required_extensions ) ) {
			$extensions = array_merge( $this->required_extensions, $extensions );
		}

		return $extensions;
	}

	public function get_headers() {
		$this->data = get_file_data( $this->file, HT_Extension()->headers );

		return $this->data;
	}

	private function add_required( &$data, $name ) {
		if ( ! is_array( $data ) ) {
			$data = array();
		}

		if ( ! in_array( $name, $data ) ) {
			$data[] = $name;
		}
	}

	public function add_required_plugin( $plugin ) {
		$this->add_required( $this->required_plugins, $plugin );
	}

	public function add_required_extension( $extension ) {
		$this->add_required( $this->required_extensions, $extension );
	}

	public function is_active() {
		return $this->is_active;
	}
}

class HOCWP_Theme_Extension_Controller {
	private static $instance;

	public $headers = array(
		'Name'        => 'Name',
		'Description' => 'Description'
	);

	public $paths = array(
		HOCWP_THEME_CORE_PATH . '/ext',
		HOCWP_THEME_CUSTOM_PATH . '/ext'
	);

	public $files = array();

	public $extensions = array();
	public $active_extensions = array();

	public function __construct() {
		if ( self::$instance ) {
			_doing_it_wrong( __CLASS__, sprintf( __( '%s is a singleton class and you cannot create a second instance.', 'hocwp-theme' ), get_class( $this ) ), '6.4.1' );

			return;
		}

		$this->active_extensions = (array) get_option( 'hocwp_theme_active_extensions', array() );

		self::$instance = $this;

		global $hocwp_theme;

		if ( ! isset( $hocwp_theme->extensions ) ) {
			$hocwp_theme->extensions = array();
		}
	}

	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function deprecated( $extension, $version, $replacement = null ) {

		/**
		 * Fires when a deprecated extension is called.
		 *
		 * @since 6.4.2
		 *
		 * @param string $extension The extension that was called.
		 * @param string $replacement The extension that should have been called.
		 * @param string $version The version of Theme Core that deprecated the extension.
		 */
		do_action( 'deprecated_extension_run', $extension, $replacement, $version );

		/**
		 * Filters whether to trigger an error for deprecated extensions.
		 *
		 * @since 6.4.2
		 *
		 * @param bool $trigger Whether to trigger the error for deprecated extensions. Default true.
		 */
		if ( WP_DEBUG && apply_filters( 'deprecated_extension_trigger_error', true ) ) {
			if ( ! is_null( $replacement ) ) {
				/* translators: 1: Extension name, 2: version number, 3: alternative extension name */
				trigger_error( sprintf( __( 'Extension %1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.', 'hocwp-theme' ), $extension, $version, $replacement ) );
			} else {
				/* translators: 1: Extension name, 2: version number */
				trigger_error( sprintf( __( 'Extension %1$s is <strong>deprecated</strong> since version %2$s with no alternative available.', 'hocwp-theme' ), $extension, $version ) );
			}
		}
	}

	public function get_paths() {
		return apply_filters( 'hocwp_theme_extension_paths', (array) $this->paths );
	}

	public function get_files() {
		$paths = $this->get_paths();

		foreach ( $paths as $path ) {
			if ( is_dir( $path ) ) {
				$tmp = scandir( $path );

				foreach ( $tmp as $key => $file ) {
					if ( '.' != $file && '..' != $file ) {
						$file = trailingslashit( $path ) . $file;
						if ( HT()->is_file( $file ) ) {
							$data = get_file_data( $file, $this->headers );

							if ( ! empty( $data['Name'] ) && ! in_array( $file, $this->files ) ) {
								$this->files[] = $file;
							}
						}
					}
				}
			}
		}

		return apply_filters( 'hocwp_theme_extension_files', $this->files );
	}

	public function sanitize_file( $file ) {
		$file = str_replace( "\\\\", "\\", $file );
		$file = str_replace( "/", "\\", $file );

		foreach ( $this->get_paths() as $path ) {
			$file = str_replace( dirname( $path ), '', $file );
		}

		$file = str_replace( "\\", "/", $file );
		$file = ltrim( $file, '/' );

		$parts = explode( '/', $file );

		if ( 2 < count( $parts ) ) {
			$parts = array_slice( $parts, - 2, 2 );

			$file = implode( '/', $parts );
		}

		return $file;
	}

	public function is_active( $file ) {
		$file = $this->sanitize_file( $file );

		return in_array( $file, $this->active_extensions );
	}

	public function get_extensions() {
		$files = $this->get_files();
		sort( $files );

		foreach ( $files as $file ) {
			if ( '.' != $file && '..' != $file ) {
				if ( HT()->is_file( $file ) ) {
					$data = get_file_data( $file, $this->headers );

					if ( ! empty( $data['Name'] ) ) {
						$data['dir'] = $file;

						$this->extensions[ $file ] = $data;
					}
				}
			}
		}

		return $this->extensions;
	}

	public function has( $file ) {
		$file = $this->get_basename( $file );

		return in_array( $file, $this->extensions );
	}

	public function get_basename( $file ) {
		if ( is_file( $file ) ) {
			$file = basename( dirname( $file ) ) . '/' . basename( $file );
		}

		return $this->sanitize_basename( $file );
	}

	public function sanitize_basename( $basename ) {
		if ( false === strpos( $basename, 'ext/' ) ) {
			$basename = 'ext/' . $basename;
		}

		if ( false === strpos( $basename, '.php' ) ) {
			$basename .= '.php';
		}

		return $basename;
	}
}

function HT_Extension() {
	return HOCWP_Theme_Extension_Controller::get_instance();
}