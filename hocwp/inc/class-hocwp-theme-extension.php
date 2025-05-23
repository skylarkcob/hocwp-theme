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

		$this->file = $file;

		$this->basedir = dirname( $this->file );

		$this->get_headers();

		if ( isset( $this->data['Name'] ) && ! empty( $this->data['Name'] ) ) {
			$this->name = $this->data['Name'];

			$this->description = $this->data['Description'];
		} else {
			ht_util()->doing_it_wrong( __CLASS__, __( 'Please declare extension with Name and Description in header.', 'hocwp-theme' ), '6.4.2' );

			return;
		}

		$this->folder_path = $this->basedir;

		if ( empty( $this->folder_url ) ) {
			$this->folder_url = hocwp_theme()->core_url . '/ext';
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

		$this->basename = ht_extension()->get_basename( $this->file );

		add_filter( 'hocwp_theme_required_extensions', array( $this, 'required_extensions' ) );

		if ( is_admin() ) {
			if ( ht_admin()->is_theme_option_page() ) {
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
		if ( ht()->array_has_value( $this->required_extensions ) ) {
			$extensions = array_merge( $this->required_extensions, $extensions );
		}

		return $extensions;
	}

	public function get_headers() {
		$this->data = get_file_data( $this->file, ht_extension()->headers );

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

	public function get_option( $name, $default = '' ) {
		return ht_options()->get_tab( $name, $default, $this->option_name );
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
			ht_util()->doing_it_wrong( __CLASS__, sprintf( __( '%s is a singleton class and you cannot create a second instance.', 'hocwp-theme' ), get_class( $this ) ), '6.4.1' );

			return;
		}

		$this->active_extensions = (array) get_option( 'hocwp_theme_active_extensions', array() );

		if ( ! isset( hocwp_theme_object()->extensions ) ) {
			hocwp_theme_object()->extensions = array();
		}

		if ( is_admin() ) {
			add_action( 'deprecated_extension_run', array( $this, 'deprecated_extension_run_action' ), 10, 3 );
		}
	}

	public function register( $extension ) {
		if ( ! isset( hocwp_theme_object()->extensions ) || ! is_array( hocwp_theme_object()->extensions ) ) {
			hocwp_theme_object()->extensions = array();
		}

		hocwp_theme_object()->extensions[ $extension->basename ] = $extension;
	}

	public function deprecated_extension_run_action( $extension, $replacement, $version ) {
		$message = $this->get_deprecated_message( $extension, $version, $replacement );

		$tr_name = 'deprecated_extension_notices';
		$notices = get_transient( $tr_name );

		if ( ! is_array( $notices ) ) {
			$notices = array();
		}

		$notices[] = $message;

		set_transient( $tr_name, $notices );

		add_action( 'admin_notices', array( $this, 'admin_notices_action' ) );
	}

	public function admin_notices_action() {
		$tr_name = 'deprecated_extension_notices';

		if ( false !== ( $notices = get_transient( $tr_name ) ) ) {
			if ( ht()->array_has_value( $notices ) ) {
				foreach ( $notices as $notice ) {
					?>
                    <div class="notice notice-warning is-dismissible">
						<?php echo wpautop( $notice ); ?>
                    </div>
					<?php
				}
			}

			delete_transient( $tr_name );
		}
	}

	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get_data() {
		return get_file_data( $this->headers, array( 'Name' => 'Name', 'Description' => 'Description' ) );
	}

	public function get_deprecated_message( $extension, $version, $replacement = null ) {
		if ( ! is_null( $replacement ) ) {
			return sprintf( __( 'Extension %1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.', 'hocwp-theme' ), $extension, $version, $replacement );
		}

		return sprintf( __( 'Extension %1$s is <strong>deprecated</strong> since version %2$s with no alternative available.', 'hocwp-theme' ), $extension, $version );
	}

	public function deprecated( $extension, $version, $replacement = null ) {

		/**
		 * Fires when a deprecated extension is called.
		 *
		 * @param string $extension The extension that was called.
		 * @param string $replacement The extension that should have been called.
		 * @param string $version The version of Theme Core that deprecated the extension.
		 *
		 * @since 6.4.2
		 *
		 */
		do_action( 'deprecated_extension_run', $extension, $replacement, $version );

		/**
		 * Filters whether to trigger an error for deprecated extensions.
		 *
		 * @param bool $trigger Whether to trigger the error for deprecated extensions. Default true.
		 *
		 * @since 6.4.2
		 *
		 */
		if ( WP_DEBUG && apply_filters( 'deprecated_extension_trigger_error', true ) ) {
			trigger_error( $this->get_deprecated_message( $extension, $version, $replacement ) );
		}
	}

	public function get_paths() {
		return apply_filters( 'hocwp_theme_extension_paths', (array) $this->paths );
	}

	public function get_files() {
		$paths = $this->get_paths();

		foreach ( $paths as $path ) {
			if ( is_dir( $path ) ) {
				$tmp = ht()->scandir( $path );

				foreach ( $tmp as $key => $file ) {
					if ( '.' != $file && '..' != $file ) {
						$file = trailingslashit( $path ) . $file;
						if ( ht()->is_file( $file ) ) {
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
				if ( ht()->is_file( $file ) ) {
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
		if ( ! str_contains( $basename, 'ext/' ) ) {
			$basename = 'ext/' . $basename;
		}

		if ( ! str_contains( $basename, '.php' ) ) {
			$basename .= '.php';
		}

		return $basename;
	}
}

function ht_extension() {
	return HOCWP_Theme_Extension_Controller::get_instance();
}