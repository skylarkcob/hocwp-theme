<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! trait_exists( 'HOCWP_Theme_Utils' ) ) {
	require_once dirname( __FILE__ ) . '/trail-utils.php';
}

class HOCWP_Theme_Utility {
	use HOCWP_Theme_Utils;

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	protected function __construct() {
	}

	public function is_amp( $mode = '' ) {
		global $wp_query;

		if ( isset( $wp_query->query['menu-amp'] ) ) {
			return true;
		}

		if ( ! empty( $mode ) ) {
			$options = get_option( 'amp-options' );

			if ( ! is_array( $options ) || ! isset( $options['theme_support'] ) ) {
				return false;
			}

			if ( is_array( $mode ) && ! in_array( $options['theme_support'], $mode ) ) {
				return false;
			}

			if ( ! is_array( $mode ) && $options['theme_support'] != $mode ) {
				return false;
			}

			$all_templates_supported = $options['all_templates_supported'] ?? '';

			if ( ! $all_templates_supported ) {
				$supported_templates = $options['supported_templates'] ?? '';

				if ( empty( $supported_templates ) || ! is_array( $supported_templates ) ) {
					return false;
				}

				if ( is_home() && ! in_array( 'is_home', $supported_templates ) ) {
					return false;
				}

				if ( ( is_singular() || is_page() || is_single() ) && ! in_array( 'is_singular', $supported_templates ) ) {
					return false;
				}

				if ( is_author() && ! in_array( 'is_author', $supported_templates ) ) {
					return false;
				}

				if ( is_date() && ! in_array( 'is_date', $supported_templates ) ) {
					return false;
				}

				if ( is_category() && ! in_array( 'is_category', $supported_templates ) ) {
					return false;
				}

				if ( is_tag() && ! in_array( 'is_tag', $supported_templates ) ) {
					return false;
				}

				if ( is_search() && ! in_array( 'is_search', $supported_templates ) ) {
					return false;
				}

				if ( is_404() && ! in_array( 'is_404', $supported_templates ) ) {
					return false;
				}

				$args = array(
					'public'   => true,
					'_builtin' => false
				);

				$post_types = get_post_types( $args );

				if ( HT()->array_has_value( $post_types ) ) {
					foreach ( $post_types as $post_type ) {
						if ( is_string( $post_type ) && is_post_type_archive( $post_type ) && ! in_array( 'is_post_type_archive[' . $post_type . ']', $supported_templates ) ) {
							return false;
						}
					}
				}

				$taxs = get_taxonomies( $args );

				if ( HT()->array_has_value( $taxs ) ) {
					foreach ( $taxs as $tax ) {
						if ( is_string( $tax ) && is_tax( $tax ) && ! in_array( 'is_tax[' . $tax . ']', $supported_templates ) ) {
							return false;
						}
					}
				}
			}
		}

		if ( isset( $_GET['amp'] ) ) {
			return true;
		}

		$amp = HT()->get_method_value( 'amp', 'get' );

		if ( 1 != $amp ) {
			$amp = get_query_var( 'amp' );
		}

		if ( 1 != $amp ) {
			$request = $_SERVER['REQUEST_URI'] ?? '';
			$request = basename( $request );

			if ( 'amp' === $request ) {
				return true;
			}
		}

		return ( 1 == $amp );
	}

	public function get_browser() {
		global $hocwp_theme;

		if ( ! isset( $hocwp_theme->browser ) || ! HT()->array_has_value( $hocwp_theme->browser ) ) {
			$hocwp_theme->browser = HT()->get_browser();
		}

		return $hocwp_theme->browser;
	}

	/**
	 * Get image url in WordPress core folder.
	 *
	 * @param string $name Image name.
	 *
	 * @return string Image url.
	 */
	public function get_wp_image_url( $name ) {
		return includes_url( 'images/' . $name );
	}

	/**
	 * Get WebP image name if exists when user enable Use WebP in reading setting tab.
	 *
	 * @param string $name Image name.
	 * @param string $path Directory contains image.
	 *
	 * @return string Regular image name or WebP image name.
	 */
	public function detect_webp_image_instead( $name, $path ) {
		$use_webp = HT_Options()->get_tab( 'use_webp', '', 'reading' );

		// Check using WebP images instead of regular images
		if ( $use_webp ) {
			$info = pathinfo( $name );

			if ( isset( $info['extension'] ) ) {
				$browser = HT_Util()->get_browser();

				$ext = 'webp';

				if ( isset( $browser['short_name'] ) ) {
					$short = strtolower( $browser['short_name'] );

					// Using jpg instead if browser not support webp
					if ( 'safari' === $short ) {
						$version = $browser['version'] ?? '';

						if ( version_compare( $version, '14', '<' ) ) {
							$ext = 'jpg';
						}
					} elseif ( 'msie' === $short ) {
						$ext = 'jpg';
					}
				}

				if ( $ext != $info['extension'] ) {
					$use_webp = $path . $info['filename'] . '.' . $ext;

					if ( file_exists( $use_webp ) ) {
						$name = $info['filename'] . '.' . $ext;
					}
				}
			}
		}

		return $name;
	}

	public function get_my_image_url( $name ) {
		$name = $this->detect_webp_image_instead( $name, HOCWP_THEME_CORE_PATH . '/images/' );

		return HOCWP_THEME_CORE_URL . '/images/' . $name;
	}

	public function get_custom_image_url( $name ) {
		$name = $this->detect_webp_image_instead( $name, HT_Custom()->get_path( 'images/' ) );

		return HT_Custom()->get_url( 'images/' . $name );
	}

	public function get_current_url( $with_param = false ) {
		global $hocwp_theme_protocol;
		$current_url = $hocwp_theme_protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		if ( ! $with_param ) {
			$current_url = HT()->get_url_without_param( $current_url );
		}

		return apply_filters( 'hocwp_theme_current_url', $current_url );
	}

	public function get_the_excerpt( $excerpt_length = null, $excerpt_more = null ) {
		if ( ! is_numeric( $excerpt_length ) ) {
			$excerpt_length = apply_filters( 'excerpt_length', 55 );
		}

		$obj     = get_post( get_the_ID() );
		$excerpt = ( empty( $obj->post_excerpt ) ) ? $obj->post_content : $obj->post_excerpt;
		$excerpt = wp_strip_all_tags( $excerpt );
		$excerpt = strip_shortcodes( $excerpt );

		return wp_trim_words( $excerpt, $excerpt_length, $excerpt_more );
	}

	public function return_post( $post_or_id = null, $output = OBJECT ) {
		$output = strtoupper( $output );

		if ( $post_or_id instanceof WP_Post ) {
			$current = $post_or_id;
		} elseif ( HT()->is_positive_number( $post_or_id ) ) {
			$current = get_post( $post_or_id );
		} else {
			$current = get_post( get_the_ID() );
		}

		if ( ! ( $current instanceof WP_Post ) ) {
			return new WP_Error();
		}

		if ( OBJECT == $output ) {
			return $current;
		}

		return $current->ID;
	}

	public function return_term( $term_or_id = null, $taxonomy = '', $output = OBJECT ) {
		$output = strtoupper( $output );

		if ( $term_or_id instanceof WP_Term ) {
			$current = $term_or_id;
		} elseif ( HT()->is_positive_number( $term_or_id ) ) {
			$current = get_term( $term_or_id, $taxonomy );
		} else {
			$current = get_queried_object();
		}

		if ( ! ( $current instanceof WP_Term ) ) {
			return new WP_Error();
		}

		if ( OBJECT == $output ) {
			return $current;
		}

		return $current->term_id;
	}

	public function force_user_login( $user_id, $remember = true ) {
		wp_set_auth_cookie( $user_id, $remember );
	}

	public function is_email( $email ) {
		return ( ! empty( $email ) && is_string( $email ) && is_email( $email ) );
	}

	public function return_user( $id_email_login = null, $output = OBJECT ) {
		$output = strtoupper( $output );

		$current = null;

		if ( 'FIRST_ADMIN' === $id_email_login ) {
			$user_query = new WP_User_Query( array( 'role' => 'Administrator', 'number' => 1 ) );

			$current = current( $user_query->get_results() );
		} elseif ( $id_email_login instanceof WP_User ) {
			$current = $id_email_login;
		} elseif ( HT()->is_positive_number( $id_email_login ) ) {
			$current = get_user_by( 'ID', $id_email_login );
		} elseif ( $this->is_email( $id_email_login ) ) {
			$current = get_user_by( 'email', $id_email_login );
		} else {
			if ( ! empty( $id_email_login ) ) {
				$current = get_user_by( 'login', $id_email_login );
			}
		}

		if ( ! ( $current instanceof WP_User ) && is_user_logged_in() ) {
			$current = wp_get_current_user();
		}

		if ( ! ( $current instanceof WP_User ) ) {
			return new WP_Error();
		}

		if ( OBJECT == $output ) {
			return $current;
		}

		return $current->ID;
	}

	public function apply_the_content( $content ) {
		if ( empty( $content ) ) {
			return $content;
		}

		$content = do_shortcode( $content );
		$content = do_blocks( $content );
		$content = wptexturize( $content );

		$content = shortcode_unautop( $content );
		$content = prepend_attachment( $content );
		$content = wp_filter_content_tags( $content );
		$content = wp_replace_insecure_home_url( $content );

		global $wp_embed;
		$content = $wp_embed->autoembed( $content );

		return apply_filters( 'hocwp_theme_the_content_filter', $content );
	}

	public function convert_terms_data( &$lists, $taxonomy, $return = 'term_id' ) {
		foreach ( $lists as $key => $value ) {
			$term = null;

			if ( is_numeric( $value ) ) {
				$term = get_term( $value, $taxonomy );
			} elseif ( is_string( $value ) ) {
				$term = get_term_by( 'slug', $value, $taxonomy );

				if ( ! ( $term instanceof WP_Term ) ) {
					$term = get_term_by( 'name', $value, $taxonomy );
				}
			}

			if ( $term instanceof WP_Term ) {
				if ( 'term_id' == $return ) {
					$value = $term->term_id;
				} elseif ( 'name' == $return ) {
					$value = $term->name;
				} elseif ( 'slug' == $return ) {
					$value = $term->slug;
				}

				$lists[ $key ] = $value;
			}
		}

		return $lists;
	}

	/**
	 * Convert CSV of Administrative Boundaries string to array data.
	 *
	 * @param $csv string CSV data.
	 * @param $district bool Load district.
	 * @param $commune bool Load commune.
	 *
	 * @return array Array of Administrative Boundaries data.
	 */
	public function convert_administrative_boundaries_to_array( $csv, $district, $commune ) {
		$abs = array();

		if ( is_string( $csv ) && file_exists( $csv ) ) {
			$csv = HT_Util()->read_all_text( $csv );
			$csv = HT()->explode_new_line( $csv );

			// Remove heading text
			array_shift( $csv );
			$csv = array_filter( $csv );
		}

		foreach ( (array) $csv as $ab ) {
			$ab = explode( ',', $ab );

			$name = array_shift( $ab );
			$id   = array_shift( $ab );

			if ( empty( $abs[ $id ]['name'] ) ) {
				$abs[ $id ]['name'] = $name;
				$abs[ $id ]['type'] = 'province';
			}

			if ( $district ) {
				$name = array_shift( $ab );
				$d_id = array_shift( $ab );

				if ( empty( $abs[ $id ][ $d_id ]['name'] ) ) {
					$abs[ $id ][ $d_id ]['name'] = $name;
					$abs[ $id ][ $d_id ]['type'] = 'district';
				}

				if ( $commune ) {
					$name = array_shift( $ab );
					$c_id = array_shift( $ab );

					if ( empty( $abs[ $id ][ $d_id ][ $c_id ]['name'] ) ) {
						$abs[ $id ][ $d_id ][ $c_id ]['name'] = $name;
						$abs[ $id ][ $d_id ][ $c_id ]['type'] = 'commune';
					}
				}
			}
		}

		return $abs;
	}

	/**
	 * Get list of functions for a filter or action hook.
	 *
	 * @param $hook string Hook name.
	 * @param $type string Hook type.
	 *
	 * @return int|WP_Hook|null Array of functions or null.
	 */
	public function get_hook_functions_for( string $hook, string $type = 'filter' ) {
		if ( empty( $hook ) ) {
			return null;
		}

		global $wp_filter, $wp_actions;

		if ( 'filter' == $type && isset( $wp_filter[ $hook ] ) ) {
			return $wp_filter[ $hook ];
		} elseif ( 'action' == $type && isset( $wp_actions[ $hook ] ) ) {
			return $wp_actions[ $hook ];
		}

		return null;
	}

	public function take_screenshot( $url, $params = array() ) {
		$base = 'http://s.wordpress.com/mshots/v1/';
		$base .= urlencode( $url );

		return add_query_arg( $params, $base );
	}

	public function get_term_link( $term ) {
		return '<a href="' . esc_url( get_term_link( $term ) ) . '" rel="category ' . HT_Sanitize()->html_class( $term->taxonomy ) . ' tag" title="' . esc_attr( $term->name ) . '">' . $term->name . '</a>';
	}

	public function get_term_drop_down( $args = array() ) {
		$defaults = array(
			'hide_empty'    => false,
			'hide_if_empty' => true,
			'hierarchical'  => true,
			'orderby'       => 'NAME',
			'show_count'    => true,
			'echo'          => false,
			'taxonomy'      => 'category'
		);

		unset( $args['echo'] );

		$selected = HT()->get_value_in_array( $args, 'selected' );

		if ( HT()->array_has_value( $selected ) ) {
			unset( $args['selected'] );
		}

		$args   = wp_parse_args( $args, $defaults );
		$select = wp_dropdown_categories( $args );

		if ( ! empty( $select ) ) {
			$required     = (bool) HT()->get_value_in_array( $args, 'required', false );
			$autocomplete = (bool) HT()->get_value_in_array( $args, 'autocomplete', false );

			if ( $required ) {
				$select = HT()->add_html_attribute( 'select', $select, 'required aria-required="true"' );
			}

			if ( ! $autocomplete ) {
				$select = HT()->add_html_attribute( 'select', $select, 'autocomplete="off"' );
			}
		}

		$attributes = HT()->get_value_in_array( $args, 'attributes' );

		if ( ! empty( $attributes ) ) {
			if ( ! is_string( $attributes ) ) {
				$attributes = HT()->attribute_to_array( $attributes );
			}

			if ( HT()->array_has_value( $attributes ) ) {
				foreach ( $attributes as $att => $value ) {
					$select = HT()->add_html_attribute( 'select', $select, sprintf( '%s="%s"', $att, $value ) );
				}
			}
		}

		if ( HT()->array_has_value( $selected ) ) {
			foreach ( $selected as $value ) {
				$select = str_replace( 'value="' . $value . '"', 'value="' . $value . '" selected="selected"', $select );
			}
		}

		return $select;
	}

	public function get_path_or_url( $parent, $suffix = '' ) {
		$path = $parent;

		if ( is_string( $suffix ) && ! empty( $suffix ) ) {
			$path = trailingslashit( $path );
			$path .= ltrim( $suffix, '/' );
		}

		return $path;
	}

	/**
	 * Get URL from wp-includes folder.
	 *
	 * @param $path
	 *
	 * @return string
	 */
	public function get_include_url( $path ) {
		$path = ltrim( $path, '/' );

		return home_url( 'wp-includes/' . $path );
	}

	public function blank_image_url() {
		return $this->get_include_url( 'images/blank.gif' );
	}

	public function fetch_feed( $args = array() ) {
		if ( ! is_array( $args ) ) {
			$args = array(
				'url' => $args
			);
		}

		$defaults = array(
			'number' => 5,
			'offset' => 0,
			'url'    => ''
		);

		$args = wp_parse_args( $args, $defaults );

		$number = absint( HT()->get_value_in_array( $args, 'number', 5 ) );
		$offset = HT()->get_value_in_array( $args, 'offset', 0 );
		$url    = HT()->get_value_in_array( $args, 'url' );

		if ( empty( $url ) ) {
			return '';
		}

		if ( ! function_exists( 'fetch_feed' ) ) {
			load_template( ABSPATH . WPINC . '/feed.php' );
		}

		$rss = fetch_feed( $url );

		if ( ! is_wp_error( $rss ) ) {
			if ( ! $rss->get_item_quantity() ) {
				$error = new WP_Error( 'feed_down', __( 'An error has occurred, which probably means the feed is down. Try again later.', 'hocwp-theme' ) );
				$rss->__destruct();
				unset( $rss );

				return $error;
			}

			$max    = $rss->get_item_quantity( $number );
			$result = $rss->get_items( $offset, $max );
		} else {
			$result = $rss;
		}

		return $result;
	}

	private function sanitize_feed_item_category( $cat ) {
		if ( is_object( $cat ) && isset( $cat->term ) ) {
			$cat = $cat->term;
		} elseif ( is_array( $cat ) && isset( $cat['term'] ) ) {
			$cat = $cat['term'];
		}

		return $cat;
	}

	public function get_feed_items( $args = array() ) {
		$items = $this->fetch_feed( $args );

		if ( HT()->array_has_value( $items ) ) {
			$result = array();

			foreach ( $items as $item ) {
				if ( ! $this->is_object_valid( $item ) ) {
					continue;
				}

				/** @noinspection PhpUndefinedMethodInspection */
				$description = $item->get_description();
				$thumbnail   = HT()->get_first_image_source( $description );
				$description = wp_strip_all_tags( $description );
				/** @noinspection PhpUndefinedMethodInspection */
				$content = $item->get_content();

				if ( empty( $thumbnail ) ) {
					$thumbnail = HT()->get_first_image_source( $content );
				}

				$cat = $item->get_category();
				$cat = $this->sanitize_feed_item_category( $cat );

				$cats = $item->get_categories();

				if ( HT()->array_has_value( $cats ) ) {
					$cats = array_map( array( $this, 'sanitize_feed_item_category' ), $cats );
				}

				/** @noinspection PhpUndefinedMethodInspection */
				$value = array(
					'permalink'   => $item->get_permalink(),
					'title'       => $item->get_title(),
					'date'        => $item->get_date(),
					'image_url'   => $thumbnail,
					'description' => $description,
					'content'     => $content,
					'modified'    => $item->get_updated_date(),
					'category'    => $cat,
					'categories'  => $cats
				);

				$value = apply_filters( 'hocwp_theme_feed_item_data', $value, $item, $args );

				$result[] = $value;
			}
		} else {
			return $items;
		}

		return $result;
	}

	public function is_object_valid( $object ) {
		return ( is_object( $object ) && ! is_wp_error( $object ) );
	}

	public function is_vr_theme() {
		$result = false;

		if ( defined( 'VR_DIR' ) ) {
			$result = true;
		}

		if ( ! $result && function_exists( 'HT_VR' ) ) {
			$file = trailingslashit( ABSPATH );
			$file .= HT_VR()->detect_vr_folder() . '/tour.xml';

			$result = file_exists( $file );
		}

		return apply_filters( 'hocwp_theme_is_vr_tour', $result );
	}

	public function get_file_or_dir_url( $file_or_dir ) {
		if ( ! empty( $file_or_dir ) ) {
			$file_or_dir = wp_normalize_path( $file_or_dir );

			$dir = ABSPATH;
			$dir = wp_normalize_path( $dir );
			$dir = untrailingslashit( $dir );
			$url = untrailingslashit( home_url() );
			$url = str_replace( '/', '\\', $url );
			$url = str_replace( $dir, $url, $file_or_dir );

			return str_replace( '\\', '/', $url );
		}

		return '';
	}

	public function ajax_overlay() {
		?>
        <div class="hocwp-theme ajax-overlay">
            <img src="<?php echo esc_url( self::get_my_image_url( 'loading-circle.gif' ) ); ?>" alt="">
        </div>
		<?php
	}

	public function generate_file_path( $dir, $url, $folder, $name, $version = '', $extension = 'zip' ) {
		if ( ! empty( $version ) ) {
			$name .= '_v' . $version;
		}

		$ts = current_time( 'timestamp' );

		$name .= sprintf( '_%s_%s_%s.' . $extension, date( 'Ymd', $ts ), date( 'Hi', $ts ), date( 's', $ts ) );

		$dest = trailingslashit( $dir ) . $folder;
		$uri  = trailingslashit( $url ) . $folder;

		if ( ! is_dir( $dest ) ) {
			mkdir( $dest, 0777, true );
		}

		$name = sanitize_file_name( $name );

		$result = array(
			'path' => wp_normalize_path( trailingslashit( $dest ) . $name ),
			'url'  => wp_normalize_path( trailingslashit( $uri ) . $name ),
			'size' => 'N/A'
		);

		$result['file_name'] = basename( $result['path'] );

		return $result;
	}

	public function export_database( $db_name = '', $destination = '' ) {
		if ( ! function_exists( 'exec' ) ) {
			return false;
		}

		if ( empty( $db_name ) ) {
			$db_name = DB_NAME;
		}

		$name = $db_name;
		$user = DB_USER;
		$pass = DB_PASSWORD;

		if ( stripos( PHP_OS, 'WIN' ) !== false ) {
			$root = dirname( $_SERVER['DOCUMENT_ROOT'] );
			$root = trailingslashit( $root ) . 'mysql/bin/mysqldump';
		} else {
			$root = 'mysqldump';
		}

		if ( empty( $destination ) ) {
			$destination = trailingslashit( ABSPATH ) . $db_name . '.sql';
		}

		$cmd = $root . " -u$user -p$pass $name > $destination";

		$res = call_user_func( 'exec', $cmd );

		if ( '' == $res ) {
			return true;
		}

		return false;
	}

	public function zip_folder( $source, $destination ) {
		if ( ! extension_loaded( 'zip' ) || ! file_exists( $source ) ) {
			return false;
		}

		if ( ! class_exists( 'ZipArchive' ) ) {
			return false;
		}

		$zip = new ZipArchive();

		if ( $zip->open( $destination, ZipArchive::CREATE ) === true ) {
			$source = wp_normalize_path( $source );

			if ( is_dir( $source ) ) {
				$replace = trailingslashit( dirname( $source ) );

				$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $source ), RecursiveIteratorIterator::SELF_FIRST );

				foreach ( $files as $file ) {
					$file = wp_normalize_path( $file );

					$file_name = basename( $file );

					if ( is_dir( $file ) && ( $file_name === '.git' || $file_name === '.svn' ) ) {
						continue;
					}

					if ( '.git' == $file_name || str_contains( $file, '.git/' ) || str_contains( $file, '.git\\' ) ) {
						continue;
					}

					if ( '.svn' == $file_name || str_contains( $file, '.svn/' ) || str_contains( $file, '.svn\\' ) ) {
						continue;
					}

					if ( '.' == $file_name || '..' == $file_name ) {
						continue;
					}

					if ( in_array( substr( $file, strrpos( $file, '/' ) + 1 ), array( '.', '..' ) ) ) {
						continue;
					}

					$relative = str_replace( $replace, '', $file );

					if ( is_dir( $file ) ) {
						$zip->addEmptyDir( $relative );
					} elseif ( is_file( $file ) ) {
						$zip->addFile( $file, $relative );
					}
				}
			} else if ( is_file( $source ) ) {
				$zip->addFile( $source, basename( $source ) );
			}

			return $zip->close();
		}

		return false;
	}

	public function filesystem() {
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			if ( ! function_exists( 'get_file_description' ) ) {
				require ABSPATH . 'wp-admin/includes/file.php';
			}

			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';

			$method = get_filesystem_method( false, false );

			if ( ! $method ) {
				return false;
			}

			if ( ! class_exists( "WP_Filesystem_$method" ) ) {

				/**
				 * Filters the path for a specific filesystem method class file.
				 *
				 * @param string $path Path to the specific filesystem method class file.
				 * @param string $method The filesystem method to use.
				 *
				 * @since 2.6.0
				 *
				 * @see get_filesystem_method()
				 *
				 */
				$abstraction_file = apply_filters( 'filesystem_method_file', ABSPATH . 'wp-admin/includes/class-wp-filesystem-' . $method . '.php', $method );

				if ( ! file_exists( $abstraction_file ) ) {
					return false;
				}

				require_once $abstraction_file;
			}
			$method = "WP_Filesystem_$method";

			$wp_filesystem = new $method( false );

			/*
			 * Define the timeouts for the connections. Only available after the constructor is called
			 * to allow for per-transport overriding of the default.
			 */
			if ( ! defined( 'FS_CONNECT_TIMEOUT' ) ) {
				WP_Filesystem();
			}
		}

		return $wp_filesystem;
	}

	public function get_class_name_from_file( $file ) {
		$class = '';

		$buffer = HT_Util()->read_all_text( $file );

		if ( preg_match( '/class\s+(\w+)(.*)?{/', $buffer, $matches ) ) {
			$class = $matches[1];
		}

		unset( $buffer, $matches );

		return $class;
	}

	public function rest_api_get( $base_url, $object = 'posts', $query = '' ) {
		$base_url = trailingslashit( $base_url ) . 'wp-json/wp/v2/' . $object;

		if ( ! empty( $query ) ) {
			$base_url .= '?' . $query;
		}

		$data = HT_Util()->read_all_text( $base_url );

		if ( ! empty( $data ) ) {
			$data = json_decode( $data );
		}

		return $data;
	}

	public function get_contents( $url ) {
		$result = '';

		if ( ! empty( $url ) ) {
			$filesystem = self::filesystem();

			if ( $filesystem instanceof WP_Filesystem_Direct ) {
				$result = $filesystem->get_contents( $url );
			} elseif ( $filesystem instanceof WP_Filesystem_Base ) {
				if ( $filesystem instanceof WP_Filesystem_FTPext && empty( $filesystem->link ) ) {
					return $result;
				}

				$result = $filesystem->get_contents( $url );
			}
		}

		return $result;
	}

	public function read_all_text( $path ) {
		if ( HT()->is_file( $path ) ) {
			return self::get_contents( $path );
		}

		return '';
	}

	public function write_all_text( $path, $text ) {
		if ( empty( $path ) ) {
			return '';
		}

		$filesystem = self::filesystem();

		if ( $filesystem instanceof WP_Filesystem_Base ) {
			return $filesystem->put_contents( $path, $text );
		}

		return '';
	}

	public function wrap_text( $before, $text, $after ) {
		echo $before . $text . $after;
	}

	public function normalize_path( $path, $slash = '/' ) {
		if ( ! empty( $path ) ) {
			$path = wp_normalize_path( $path );

			if ( '/' !== $slash ) {
				$path = str_replace( '/', '\\', $path );
			}
		}

		return $path;
	}

	public function get_min_max_meta( $meta_key, $type = 'min' ) {
		$type = strtolower( $type );

		global $wpdb;

		$sql = "SELECT ";

		if ( 'min' == $type ) {
			$sql .= 'MIN';
		} else {
			$sql .= 'MAX';
		}

		$sql .= "(CAST(meta_value AS UNSIGNED)) FROM $wpdb->postmeta WHERE meta_key = '" . $meta_key . "'";

		$number = absint( $wpdb->get_var( $sql ) );

		return apply_filters( 'hocwp_theme_min_max_value', $number, $meta_key, $type );
	}

	public function timestamp_to_countdown( $timestamp ) {
		$now     = current_time( 'timestamp' );
		$diff    = absint( $timestamp - $now );
		$days    = floor( $diff / DAY_IN_SECONDS );
		$hours   = floor( floor( $diff % DAY_IN_SECONDS ) / HOUR_IN_SECONDS );
		$minutes = floor( floor( $diff % HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS );
		$seconds = floor( $diff % MINUTE_IN_SECONDS );

		return array(
			'd' => $days,
			'h' => $hours,
			'm' => $minutes,
			's' => $seconds
		);
	}

	public function get_current_weekday( $format = null, $timestamp = '' ) {
		if ( ! empty( $timestamp ) && HT()->is_positive_number( $timestamp ) ) {
			$weekday = date( 'l', $timestamp );
		} else {
			$weekday = current_time( 'l' );
		}

		$weekday = strtolower( $weekday );

		if ( HOCWP_THEME_SUPPORT_PHP8 ) {
			$weekday = HT_PHP8()->match( $weekday, array(
				'monday'    => __( 'Monday', 'hocwp-theme' ),
				'tuesday'   => __( 'Tuesday', 'hocwp-theme' ),
				'wednesday' => __( 'Wednesday', 'hocwp-theme' ),
				'thursday'  => __( 'Thursday', 'hocwp-theme' ),
				'friday'    => __( 'Friday', 'hocwp-theme' ),
				'saturday'  => __( 'Saturday', 'hocwp-theme' ),
				'default'   => __( 'Sunday', 'hocwp-theme' )
			) );
		} else {
			switch ( $weekday ) {
				case 'monday':
					$weekday = __( 'Monday', 'hocwp-theme' );
					break;
				case 'tuesday':
					$weekday = __( 'Tuesday', 'hocwp-theme' );
					break;
				case 'wednesday':
					$weekday = __( 'Wednesday', 'hocwp-theme' );
					break;
				case 'thursday':
					$weekday = __( 'Thursday', 'hocwp-theme' );
					break;
				case 'friday':
					$weekday = __( 'Friday', 'hocwp-theme' );
					break;
				case 'saturday':
					$weekday = __( 'Saturday', 'hocwp-theme' );
					break;
				default:
					$weekday = __( 'Sunday', 'hocwp-theme' );
			}
		}

		if ( ! empty( $format ) ) {
			$weekday = sprintf( '%s, %s', $weekday, current_time( $format ) );
		}

		return $weekday;
	}

	public function date_intervals() {
		$date_intervals = array(
			'all'     => __( 'All date', 'hocwp-theme' ),
			'daily'   => __( 'Daily', 'hocwp-theme' ),
			'weekly'  => __( 'Weekly', 'hocwp-theme' ),
			'monthly' => __( 'Monthly', 'hocwp-theme' ),
			'yearly'  => __( 'Yearly', 'hocwp-theme' )
		);

		return apply_filters( 'hocwp_theme_date_intervals', $date_intervals );
	}

	public function admin_notice( $args = array() ) {
		if ( ! is_array( $args ) ) {
			$args = array(
				'message' => $args
			);
		}

		if ( ! isset( $args['message'] ) && isset( $args['text'] ) ) {
			$args['message'] = $args['text'];
		}

		$defaults = array(
			'type'        => 'success',
			'dismissible' => true,
			'autop'       => true
		);

		$args  = wp_parse_args( $args, $defaults );
		$class = 'notice fade hocwp-theme';

		$class .= ' notice-' . $args['type'];

		if ( $args['dismissible'] ) {
			$class .= ' is-dismissible';
		}

		$message = $args['message'] ?? '';

		if ( ! empty( $message ) ) {
			if ( $args['autop'] ) {
				$message = wpautop( $message );
			} else {
				$message = HT()->wrap_text( $message, '<p>', '</p>' );
			}

			$hidden_interval = $args['hidden_interval'] ?? 0;

			if ( HT()->is_positive_number( $hidden_interval ) ) {
				$class .= ' auto-hide';
				ob_start();
				?>
                <script>
                    jQuery(document).ready(function ($) {
                        setTimeout(function () {
                            const notices = $(".hocwp-theme.notice.auto-hide");
                            notices.fadeOut(1000);
                        }, <?php echo $hidden_interval; ?>);
                    });
                </script>
				<?php
				$message .= ob_get_clean();
			}

			if ( isset( $args['id'] ) ) {
				$result = sprintf( '<div id="%s" class="%s">%s</div>', $args['id'], esc_attr( $class ), $message );
			} else {
				$result = sprintf( '<div class="%1$s">%2$s</div>', esc_attr( $class ), $message );
			}

			$echo = $args['echo'] ?? true;

			if ( $echo ) {
				echo $result;
			}

			return $result;
		}

		return '';
	}

	public function get_terms( $taxonomy, $args = array() ) {
		$defaults = array( 'taxonomy' => $taxonomy );
		$args     = wp_parse_args( $args, $defaults );
		$query    = new WP_Term_Query( $args );

		return $query->get_terms();
	}

	public function get_image_sizes() {
		global $_wp_additional_image_sizes;
		$sizes = array();

		foreach ( get_intermediate_image_sizes() as $_size ) {
			if ( in_array( $_size, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {
				$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
				$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
				$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
				$sizes[ $_size ] = array(
					'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
					'height' => $_wp_additional_image_sizes[ $_size ]['height'],
					'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
				);
			}
		}

		return $sizes;
	}

	public function get_image_size( $size ) {
		if ( ! ( is_array( $size ) || has_image_size( $size ) ) ) {
			$size  = strval( $size );
			$sizes = self::get_image_sizes();

			if ( 'post-thumbnail' == $size && isset( $sizes['thumbnail'] ) ) {
				$size = 'thumbnail';
			} else if ( ( 'thumbnail' == $size && ! isset( $sizes['thumbnail'] ) ) || ( 'thumbnail' == $size && isset( $sizes['post-thumbnail'] ) ) ) {
				$size = 'post-thumbnail';
			}

			if ( isset( $sizes[ $size ] ) ) {
				$size = $sizes[ $size ];
			}
		}

		return HT_Sanitize()->size( $size );
	}

	public function get_image_width( $size ) {
		if ( ! $size = self::get_image_size( $size ) ) {
			return false;
		}

		if ( isset( $size['width'] ) ) {
			return $size['width'];
		}

		return false;
	}

	public function get_image_height( $size ) {
		if ( ! $size = self::get_image_size( $size ) ) {
			return false;
		}

		if ( isset( $size['height'] ) ) {
			return $size['height'];
		}

		return false;
	}

	public function get_user_activation_key( $key, $user ) {
		global $wp_hasher, $wpdb;

		$user = $this->return_user( $user );

		if ( ! ( $user instanceof WP_User ) ) {
			return false;
		}

		if ( empty( $wp_hasher ) ) {
			require_once ABSPATH . WPINC . '/class-phpass.php';
			$wp_hasher = new PasswordHash( 8, true );
		}

		$hashed = time() . ':' . $wp_hasher->HashPassword( $key );

		$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array(
			'user_login' => $user->user_login,
			'ID'         => $user->ID
		) );

		$_SESSION['user_activation_key'] = $hashed;

		return $hashed;
	}

	public function get_timezone() {
		$times = get_option( 'timezone_string' );

		// Remove old Etc mappings. Fallback to gmt_offset.
		if ( str_contains( $times, 'Etc/GMT' ) ) {
			$times = '';
		}

		if ( empty( $times ) ) { // Create a UTC+- zone if no timezone string exists
			$current_offset = get_option( 'gmt_offset' );

			if ( 0 == $current_offset ) {
				$times = 'UTC+0';
			} elseif ( $current_offset < 0 ) {
				$times = 'UTC' . $current_offset;
			} else {
				$times = 'UTC+' . $current_offset;
			}
		}

		return $times;
	}

	public function timestamp_to_string( $timestamp, $format = null, $timezone = null ) {
		if ( ! is_int( $timestamp ) ) {
			$timestamp = intval( $timestamp );
		}

		global $hocwp_theme;

		$defaults = $hocwp_theme->defaults;

		if ( null == $format ) {
			$df = ( ! empty( $defaults['date_format'] ) ) ? $defaults['date_format'] : 'Y-m-d';
			$tf = ( ! empty( $defaults['time_format'] ) ) ? $defaults['time_format'] : 'H:i:s';

			$format = "$df $tf";
		}

		$date = new DateTime();
		$date->setTimestamp( $timestamp );

		if ( null == $timezone ) {
			if ( ! empty( $defaults['timezone_string'] ) ) {
				$ts = new DateTimeZone( $defaults['timezone_string'] );
				$date->setTimezone( $ts );
			}
		} else {
			$ts = new DateTimeZone( $timezone );
			$date->setTimezone( $ts );
		}

		return $date->format( $format );
	}

	public function insert_term( $term, $taxonomy, $args = array() ) {
		$override = HT()->get_value_in_array( $args, 'override', false );

		if ( ! $override ) {
			$exists = get_term_by( 'name', $term, $taxonomy );

			if ( $exists instanceof WP_Term ) {
				return;
			}
		}

		wp_insert_term( $term, $taxonomy, $args );
	}

	public function verify_nonce( $nonce_action = - 1, $nonce_name = '_wpnonce' ) {
		if ( '_wpnonce' == $nonce_name || ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
			$nonce = $_REQUEST[ $nonce_name ] ?? '';

			return wp_verify_nonce( $nonce, $nonce_action );
		}

		return true;
	}

	public function can_save_post( $post_id, $nonce_action = - 1, $nonce_name = '_wpnonce' ) {
		if ( ! self::verify_nonce( $nonce_action, $nonce_name ) ) {
			return false;
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		global $pagenow;

		if ( 'link.php' == $pagenow || 'link-add.php' == $pagenow ) {
			return true;
		}

		$obj = get_post( $post_id );

		if ( $obj instanceof WP_Post && ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) ) {
			return false;
		}

		if ( $obj instanceof WP_Post ) {
			if ( 'trash' == $obj->post_status || ( isset( $_REQUEST['action'] ) && ( 'untrash' == $_REQUEST['action'] || 'trash' == $_REQUEST['action'] ) ) ) {
				return false;
			}
		}

		return true;
	}

	public function get_client_info( $save = false ) {
		if ( $save ) {
			$client_info = $_COOKIE['hocwp_theme_client_info'] ?? '';

			if ( empty( $client_info ) ) {
				$client_info = $_SESSION['hocwp_theme_client_info'] ?? '';
			}

			if ( is_string( $client_info ) ) {
				$client_info = HT()->json_string_to_array( $client_info );
			}
		} else {
			global $hocwp_theme;

			$client_info = $hocwp_theme->client_info ?? array();

			if ( empty( $client_info ) ) {
				$client_info = $this->get_client_info( true );
			}
		}

		return (array) $client_info;
	}

	public function get_sidebars() {
		return $GLOBALS['wp_registered_sidebars'];
	}

	public function add_sidebar_to_list_options( &$options, $post_type = '' ) {
		if ( is_array( $options ) ) {
			if ( ! empty( $post_type ) ) {
				$args = array(
					'post_type'      => $post_type,
					'posts_per_page' => - 1
				);

				$query = new WP_Query( $args );

				if ( $query->have_posts() ) {
					foreach ( $query->posts as $post ) {
						$options[ $post->post_name ] = $post->post_title;
					}
				}
			}

			$sidebars = $this->get_sidebars();

			foreach ( $sidebars as $sidebar_id => $sidebar ) {
				if ( is_array( $sidebar ) ) {
					$name = $sidebar['name'] ?? '';

					if ( ! empty( $name ) ) {
						$name = sprintf( '%s (%s)', $name, $sidebar_id );
					}

					if ( empty( $name ) ) {
						$name = $sidebar_id;
					}

					$name = trim( $name );

					$options[ $sidebar_id ] = $name;
				}
			}
		}
	}

	public function choose_sidebar_select_options( $post_type = '' ) {
		$options = array(
			'' => __( '-- Choose sidebar --', 'hocwp-theme' )
		);

		$this->add_sidebar_to_list_options( $options, $post_type );

		return $options;
	}

	public function get_sidebar_by( $key, $value ) {
		$sidebars = $this->get_sidebars();

		$result = array();

		foreach ( $sidebars as $id => $sidebar ) {
			switch ( $key ) {
				default:
					if ( $id == $value ) {
						$result = $sidebar;
					}
			}
		}

		unset( $sidebars, $id, $sidebar );

		return $result;
	}

	public function get_first_term( $post_id = null, $taxonomy = 'category' ) {
		$post_id = $this->return_post( $post_id, 'id' );
		$terms   = (array) wp_get_post_terms( $post_id, $taxonomy );

		return ( HT()->array_has_value( $terms ) ) ? current( $terms ) : null;
	}

	public function get_first_taxonomy_or_term( $post_id, $output = 'term', $hierarchical = true ) {
		$output = strtolower( $output );

		$post_type = ( is_numeric( $post_id ) ) ? get_post_type( $post_id ) : $post_id;
		$taxs      = get_object_taxonomies( $post_type, 'objects' );
		$taxonomy  = '';

		foreach ( $taxs as $tax ) {
			if ( $tax instanceof WP_Taxonomy && ( 'any' === $hierarchical || false === $hierarchical || $tax->hierarchical ) ) {
				$taxonomy = $tax;
			}
		}

		if ( ! ( $taxonomy instanceof WP_Taxonomy ) && ( 'any' === $hierarchical || false === $hierarchical ) ) {
			$taxonomy = array_shift( $taxs );
		}

		if ( $taxonomy instanceof WP_Taxonomy ) {
			if ( 'term' != $output && 'terms' != $output ) {
				return $taxonomy->name;
			}

			if ( HT()->is_positive_number( $post_id ) ) {
				$terms = wp_get_object_terms( $post_id, $taxonomy->name );
			} else {
				$terms = HT_Util()->get_terms( $taxonomy->name, array( 'hide_empty' => false ) );
			}

			while ( ! HT()->is_array_has_value( $terms ) && HT()->is_array_has_value( $taxs ) && $taxonomy instanceof WP_Taxonomy ) {
				$taxonomy = array_shift( $taxs );

				if ( ! ( $taxonomy instanceof WP_Taxonomy ) || ( 'any' !== $hierarchical && false !== $hierarchical && ! $taxonomy->hierarchical ) ) {
					$terms = array();
					continue;
				}

				if ( HT()->is_id_number( $post_id ) ) {
					$terms = wp_get_object_terms( $post_id, $taxonomy->name );
				} else {
					$terms = HT_Util()->get_terms( $taxonomy->name, array( 'hide_empty' => false ) );
				}
			}

			if ( HT()->is_array_has_value( $terms ) ) {
				if ( 'terms' == $output ) {
					return $terms;
				}

				$terms = (array) $terms;

				$term = current( $terms );

				if ( $term instanceof WP_Term ) {
					return $term;
				}
			}
		}

		return false;
	}

	public function loop_terms( $terms, $before = '', $after = '' ) {
		if ( HT()->array_has_value( $terms ) ) {
			echo $before . PHP_EOL;

			foreach ( $terms as $term ) {
				echo $this->term_link_html( $term, true );
			}

			echo $after . PHP_EOL;
		}
	}

	public function term_link_html( $term, $li = false, $format = '' ) {
		if ( ! ( $term instanceof WP_Term ) ) {
			return '';
		}

		if ( empty( $format ) ) {
			$format = $term->name;
		} else {
			$search = array(
				'%term_name%',
				'%count%',
				'%slug%',
				'%term_id%',
				'%taxonomy%'
			);

			$replace = array(
				$term->name,
				$term->count,
				$term->slug,
				$term->term_id,
				$term->taxonomy
			);

			$format = str_replace( $search, $replace, $format );
		}

		$a = new HOCWP_Theme_HTML_Tag( 'a' );
		$a->add_attribute( 'href', esc_url( get_term_link( $term ) ) );
		$a->set_text( $format );
		$a->add_attribute( 'class', sanitize_html_class( $term->taxonomy ) );
		$a->add_attribute( 'data-slug', $term->slug );
		$a->add_attribute( 'data-taxonomy', $term->taxonomy );
		$a->add_attribute( 'data-id', $term->term_id );
		$a->add_attribute( 'title', $term->name );
		$tax = get_taxonomy( $term->taxonomy );

		if ( $tax->hierarchical ) {
			$a->add_attribute( 'rel', 'category' );
		} else {
			$a->add_attribute( 'rel', ' tag' );
		}

		if ( $li ) {
			$li = new HOCWP_Theme_HTML_Tag( 'li' );
			$li->add_attribute( 'class', 'term-item term-' . $term->slug . ' tax-' . sanitize_html_class( $term->taxonomy ) );
			$li->add_attribute( 'data-slug', $term->slug );
			$li->add_attribute( 'data-taxonomy', $term->taxonomy );
			$li->add_attribute( 'data-id', $term->term_id );
			$li->set_text( $a );

			return $li->build();
		}

		return $a->build();
	}

	public function the_terms( $args = array() ) {
		$terms  = HT()->get_value_in_array( $args, 'terms' );
		$before = HT()->get_value_in_array( $args, 'before' );
		$sep    = HT()->get_value_in_array( $args, 'separator', ', ' );
		$after  = HT()->get_value_in_array( $args, 'after' );

		if ( HT()->array_has_value( $terms ) ) {
			echo $before;

			$html = '';

			foreach ( $terms as $term ) {
				$html .= $this->term_link_html( $term ) . $sep;
			}

			$html = trim( $html, $sep );

			echo $html;
			echo $after;
		} else {
			$post_id  = HT()->get_value_in_array( $args, 'post_id', get_the_ID() );
			$taxonomy = HT()->get_value_in_array( $args, 'taxonomy' );
			the_terms( $post_id, $taxonomy, $before, $sep, $after );
		}
	}

	public function get_youtube_video_id( $url ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__, '6.9.4', 'HOCWP_Theme_YouTube_API::get_video_id' );

		return HOCWP_Theme_YouTube_API::get_video_id( $url );
	}

	public function calculate_place_distance( $destinations, $origins, $args = array() ) {
		if ( ! is_array( $args ) ) {
			$args = array(
				'key' => $args
			);
		}

		$defaults = array(
			'destinations' => $destinations,
			'origins'      => $origins
		);

		$args = wp_parse_args( $args, $defaults );

		$api = new HOCWP_Theme_Maps_Distance_Matrix( $args );

		return $api->get_result();
	}

	public function find_near_place( $query, $latitude, $longitude, $args = array() ) {
		if ( ! is_array( $args ) ) {
			$args = array(
				'key' => $args
			);
		}

		$defaults = array(
			'input'    => $query,
			'location' => $latitude . ',' . $longitude
		);

		$args = wp_parse_args( $args, $defaults );

		$api = new HOCWP_Theme_Maps_Autocomplete( $args );

		return $api->get_result();
	}

	public function latlong_to_address( $params = array() ) {
		if ( ! is_array( $params ) && ! empty( $params ) ) {
			$params = array(
				'key' => $params
			);
		}

		if ( ! isset( $params['latlng'] ) ) {
			$lat = $params['latitude'] ?? '';
			$lng = $params['longitude'] ?? '';

			if ( ! empty( $lat ) && ! empty( $lng ) ) {
				$params['latlng'] = $lat . ',' . $lng;
			}
		}

		$code = new HOCWP_Theme_Maps_Geocode( $params );

		return $code->get_result();
	}

	public function get_youtube_video_info( $url, $api_key = '' ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__, '6.9.4', 'HOCWP_Theme_YouTube_API::fetch' );

		return ( new HOCWP_Theme_YouTube_API( $url ) )->fetch();
	}

	public function get_user_role_names( $user ) {
		$user = HT_Util()->return_user( $user );

		if ( $user instanceof WP_User ) {
			$roles = $user->roles;

			if ( HT()->array_has_value( $roles ) ) {
				if ( ! function_exists( 'get_editable_roles' ) ) {
					require_once ABSPATH . 'wp-admin/includes/user.php';
				}

				$all_roles = get_editable_roles();

				if ( is_array( $all_roles ) ) {
					foreach ( $roles as $key => $role ) {
						if ( isset( $all_roles[ $role ]['name'] ) ) {
							$roles[ $key ] = translate_user_role( $all_roles[ $role ]['name'] );
						}
					}
				}
			}

			return $roles;
		}

		return null;
	}

	public function get_paged() {
		$paged = get_query_var( 'paged' );

		if ( ! HT()->is_positive_number( $paged ) ) {
			$paged = get_query_var( 'page' );
		}

		$paged = apply_filters( 'hocwp_theme_current_paged', $paged );

		return ( HT()->is_positive_number( $paged ) ) ? $paged : 1;
	}

	public function yoast_seo_exists() {
		return ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) || is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' ) );
	}

	public function get_yoast_seo_title_separator() {
		$sep = '&raquo;';

		if ( class_exists( 'WPSEO_Options' ) ) {
			if ( function_exists( 'YoastSEO' ) && method_exists( YoastSEO()->helpers->options, 'get_title_separator' ) ) {
				$sep = YoastSEO()->helpers->options->get_title_separator();
			} elseif ( method_exists( 'WPSEO_Utils', 'get_title_separator' ) ) {
				$sep = WPSEO_Utils::get_title_separator();
			} else {
				$sep = WPSEO_Options::get( 'separator' );
			}
		}

		return $sep;
	}

	public function get_title_separator() {
		return apply_filters( 'hocwp_theme_title_separator', $this->get_yoast_seo_title_separator() );
	}

	public function get_posts_per_page( $home = false ) {
		if ( null === $home ) {
			$home = is_home();
		}

		if ( $home ) {
			$ppp = HT_Options()->get_home( 'posts_per_page' );
		} else {
			$ppp = HT_Options()->get_default( 'posts_per_page' );
		}

		if ( ! is_numeric( $ppp ) ) {
			$ppp = get_option( 'posts_per_page' );
		}

		return apply_filters( 'hocwp_theme_posts_per_page', $ppp, $home );
	}

	// List file bits
	public function upload_files( $files, $name_format = '' ) {
		$tmp = $files['name'] ?? '';

		$result = array();

		if ( ! empty( $tmp ) ) {
			$count = 0;
			$has   = count( $tmp );

			if ( empty( $name_format ) ) {
				$name_format = uniqid( 'upload-' ) . '-%s';
			}

			while ( $count < $has ) {
				$tmp  = $files['tmp_name'][ $count ];
				$name = $files['name'][ $count ];

				$up = $this->upload_file( sprintf( $name_format, $name ), $tmp );

				if ( ! empty( $up['id'] ) ) {
					$result[] = $up;
				}

				$count ++;
			}
		}

		return $result;
	}

	public function upload_file( $file_name, $bits, $check_bytes = 100 ) {
		$upload = wp_upload_bits( $file_name, null, $bits );

		if ( isset( $upload['file'] ) && file_exists( $upload['file'] ) ) {
			if ( HT()->is_positive_number( $check_bytes ) ) {
				$bytes = filesize( $upload['file'] );

				if ( ! $bytes || ! is_numeric( $bytes ) || $bytes < $check_bytes ) {
					unlink( $upload['file'] );

					return $this->upload_file( $file_name, $this->read_all_text( $bits ), null );
				}
			}

			$filename = basename( $file_name );

			$filetype = wp_check_filetype( $filename );

			$attachment = array(
				'guid'           => $upload['url'],
				'post_mime_type' => $filetype['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
				'post_content'   => '',
				'post_status'    => 'inherit'
			);

			$attach_id = wp_insert_attachment( $attachment, $upload['file'] );

			$upload['id'] = $attach_id;

			if ( HT()->is_positive_number( $attach_id ) ) {
				if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
					load_template( ABSPATH . 'wp-admin/includes/image.php' );
				}

				$attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
				wp_update_attachment_metadata( $attach_id, $attach_data );
				$upload['data'] = $attach_data;

				unset( $attach_data );
			}

			unset( $filename, $attachment, $attach_id );
		}

		return $upload;
	}

	public function get_attachment_id( $url ) {
		$attachment_id = 0;

		$dir = wp_upload_dir();

		if ( HT()->string_contain( $url, $dir['baseurl'] . '/' ) ) {
			$file = basename( $url );

			$query_args = array(
				'post_type'   => 'attachment',
				'post_status' => 'inherit',
				'fields'      => 'ids',
				'meta_query'  => array(
					array(
						'value'   => $file,
						'compare' => 'LIKE',
						'key'     => '_wp_attachment_metadata',
					),
				)
			);

			$query = new WP_Query( $query_args );

			if ( $query->have_posts() ) {
				foreach ( $query->posts as $post_id ) {
					$meta          = wp_get_attachment_metadata( $post_id );
					$original_file = basename( $meta['file'] );

					$cropped_image_files = wp_list_pluck( $meta['sizes'], 'file' );

					if ( $original_file === $file || in_array( $file, $cropped_image_files ) ) {
						$attachment_id = $post_id;
						break;
					}
				}
			}
		}

		return $attachment_id;
	}

	public function html_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {
		if ( ! function_exists( 'hocwp_theme_wp_mail_content_type_filter' ) ) {
			/** @noinspection PhpIncludeInspection */
			require_once HOCWP_THEME_CORE_PATH . '/ext/smtp.php';
		}

		add_filter( 'wp_mail_content_type', 'hocwp_theme_wp_mail_content_type_filter', 99 );
		$sent = wp_mail( $to, $subject, $message, $headers, $attachments );
		remove_filter( 'wp_mail_content_type', 'hocwp_theme_wp_mail_content_type_filter', 99 );

		return $sent;
	}

	public function post_types_support_featured() {
		$post_types = get_post_types( array( 'public' => true ) );
		unset( $post_types['attachment'] );

		return apply_filters( 'post_types_support_featured', $post_types );
	}

	public function post_types_support_featured_sortable() {
		$post_types = HT_Util()->post_types_support_featured();

		return apply_filters( 'post_types_support_featured_sortable', $post_types );
	}

	public function check_post_valid( $id_or_object, $post_type = null ) {
		if ( HT()->is_positive_number( $id_or_object ) ) {
			$id_or_object = get_post( $id_or_object );
		}

		if ( $id_or_object instanceof WP_Post ) {
			if ( ! empty( $post_type ) && $post_type != $id_or_object->post_type ) {
				return false;
			}

			return true;
		}

		return false;
	}

	public function check_page_valid( $page, $check_current_page = false, $page_template = true ) {
		if ( HT()->is_positive_number( $page ) ) {
			$page = get_post( $page );
		}

		if ( ! $this->check_post_valid( $page, 'page' ) ) {
			return false;
		}

		if ( ! $page_template ) {
			return true;
		}

		$page_template = get_post_meta( $page->ID, '_wp_page_template', true );

		if ( ! empty( $page->post_content ) || ( 'default' != $page_template && file_exists( get_stylesheet_directory() . '/' . $page_template ) ) ) {
			if ( $check_current_page ) {
				if ( is_page( $page->ID ) ) {
					return true;
				}

				return false;
			}

			return true;
		}

		return false;
	}

	/**
	 * Get theme option value of setting tab by key with default fallback.
	 *
	 * @param string|int $name Option key name.
	 * @param mixed $default Default value fallback.
	 * @param string|int $base Setting tab name or any base name to get sub value.
	 *
	 * @return mixed The option value of key in base index or full options array.
	 */
	public function get_theme_option( $name, $default = '', $base = 'general' ) {
		$value = '';

		// Try to get mobile settings first
		if ( wp_is_mobile() && 'mobile' != $base ) {
			$value = $this->get_theme_option( $name, $default, 'mobile' );
		}

		if ( '' === $value ) {
			$options = HOCWP_Theme()->get_options();

			// Get base option value from options
			if ( HT()->is_array_key_valid( $base ) ) {
				$options = $options[ $base ] ?? '';
			}

			// Get option value by key name
			if ( HT()->is_array_key_valid( $name ) ) {
				$value = false;

				if ( function_exists( 'HOCWP_EXT_Language' ) && function_exists( 'pll_current_language' ) ) {
					$lang = pll_current_language();

					if ( ! empty( $lang ) ) {
						$dl = pll_default_language();

						if ( $lang != $dl ) {
							$ln    = $name . '_' . $lang;
							$value = $options[ $ln ] ?? '';
						}
					}
				}

				if ( empty( $value ) ) {
					$value = $options[ $name ] ?? '';
				}
			} else {
				$value = $options;
			}

			if ( empty( $value ) && gettype( $value ) != gettype( $default ) && ! isset( $options[ $name ] ) ) {
				$value = $default;
			}
		}

		return apply_filters( 'hocwp_theme_option', maybe_unserialize( $value ), $name, $default, $base );
	}

	public function get_theme_option_term( $name, $taxonomy = 'category', $base = 'general', $slug = '' ) {
		$term_id = self::get_theme_option( $name, '', $base );

		if ( ! HT()->is_positive_number( $term_id ) && ! empty( $slug ) ) {
			return get_term_by( 'slug', $slug, $taxonomy );
		}

		return get_term( $term_id, $taxonomy );
	}

	public function get_meta_option( $object_id, $option_name, $option_tab, $meta_callback, $default = '' ) {
		$value = call_user_func( $meta_callback, $object_id, $option_name, true );

		if ( '' == $value || is_wp_error( $value ) || null == $value ) {
			$value = HT_Options()->get_tab( $option_name, $default, $option_tab );
		}

		if ( '' == $value || is_wp_error( $value ) || null == $value ) {
			$value = $default;
		}

		return $value;
	}

	public function get_theme_option_post( $name, $post_type = 'any', $base = 'general', $slug = '' ) {
		$id = self::get_theme_option( $name, '', $base );

		if ( ! HT()->is_positive_number( $id ) ) {
			if ( ! empty( $slug ) ) {
				$args = array(
					'post_type'   => $post_type,
					'name'        => $slug,
					'post_status' => 'publish'
				);

				$query = new WP_Query( $args );

				if ( $query->have_posts() ) {
					return current( $query->posts );
				}
			}

			return null;
		}

		return get_post( $id );
	}

	public function get_theme_option_page( $option_name, $tab, $slug = '' ) {
		return HT_Util()->get_theme_option_post( $option_name, 'page', $tab, $slug );
	}

	public function post_type_labels( $name, $singular_name = '', $menu_name = '' ) {
		if ( empty( $singular_name ) ) {
			$singular_name = $name;
		}

		if ( empty( $menu_name ) ) {
			$menu_name = $name;
		}

		/** @noinspection SqlNoDataSourceInspection */

		return array(
			'name'                     => $name,
			'singular_name'            => $singular_name,
			'menu_name'                => $menu_name,
			'add_new'                  => _x( 'Add New', 'custom post type', 'hocwp-theme' ),
			'add_new_item'             => sprintf( _x( 'Add New %s', 'cutom-post-type', 'hocwp-theme' ), $singular_name ),
			'edit_item'                => sprintf( _x( 'Edit %s', 'custom post type', 'hocwp-theme' ), $singular_name ),
			'new_item'                 => sprintf( _x( 'New %s', 'custom post type', 'hocwp-theme' ), $singular_name ),
			'view_item'                => sprintf( _x( 'View %s', 'custom post type', 'hocwp-theme' ), $singular_name ),
			'view_items'               => sprintf( _x( 'View %s', 'custom post type', 'hocwp-theme' ), $name ),
			'search_items'             => sprintf( _x( 'Search %s', 'custom post type', 'hocwp-theme' ), $name ),
			'not_found'                => sprintf( _x( 'No %s found.', 'custom post type', 'hocwp-theme' ), $name ),
			'not_found_in_trash'       => sprintf( _x( 'No %s found in Trash.', 'custom post type', 'hocwp-theme' ), $name ),
			'parent_item_colon'        => sprintf( _x( 'Parent %s:', 'custom post type', 'hocwp-theme' ), $singular_name ),
			'all_items'                => sprintf( _x( 'All %s', 'custom post type', 'hocwp-theme' ), $name ),
			'archives'                 => sprintf( _x( '%s Archives', 'custom post type', 'hocwp-theme' ), $singular_name ),
			'attributes'               => sprintf( _x( '%s Attributes', 'custom post type', 'hocwp-theme' ), $singular_name ),
			'insert_into_item'         => sprintf( _x( 'Insert into %s', 'custom post type', 'hocwp-theme' ), $singular_name ),
			'uploaded_to_this_item'    => sprintf( _x( 'Uploaded to this %s', 'custom post type', 'hocwp-theme' ), $singular_name ),
			'featured_image'           => _x( 'Featured Image', 'custom post type', 'hocwp-theme' ),
			'set_featured_image'       => _x( 'Set featured image', 'custom post type', 'hocwp-theme' ),
			'remove_featured_image'    => _x( 'Remove featured image', 'custom post type', 'hocwp-theme' ),
			'use_featured_image'       => _x( 'Use as featured image', 'custom post type', 'hocwp-theme' ),
			'filter_items_list'        => sprintf( _x( 'Filter %s list', 'custom post type', 'hocwp-theme' ), $name ),
			'items_list_navigation'    => sprintf( _x( '%s list navigation', 'custom post type', 'hocwp-theme' ), $name ),
			'items_list'               => sprintf( _x( '%s list', 'custom post type', 'hocwp-theme' ), $name ),
			'item_published'           => sprintf( _x( '%s published.', 'custom post type', 'hocwp-theme' ), $name ),
			'item_published_privately' => sprintf( _x( '%s published privately.', 'custom post type', 'hocwp-theme' ), $name ),
			'item_reverted_to_draft'   => sprintf( _x( '%s reverted to draft.', 'custom post type', 'hocwp-theme' ), $name ),
			'item_scheduled'           => sprintf( _x( '%s scheduled.', 'custom post type', 'hocwp-theme' ), $name ),
			'item_updated'             => sprintf( _x( '%s updated.', 'custom post type', 'hocwp-theme' ), $name ),
		);
	}

	public function taxonomy_labels( $name, $singular_name = '', $menu_name = '' ) {
		if ( empty( $singular_name ) ) {
			$singular_name = $name;
		}

		if ( empty( $menu_name ) ) {
			$menu_name = $name;
		}

		return array(
			'name'                       => $name,
			'singular_name'              => $singular_name,
			'menu_name'                  => $menu_name,
			'search_items'               => sprintf( _x( 'Search %s', 'custom taxonomy term', 'hocwp-theme' ), $name ),
			'popular_items'              => sprintf( _x( 'Popular %s', 'custom taxonomy term', 'hocwp-theme' ), $name ),
			'all_items'                  => sprintf( _x( 'All %s', 'custom taxonomy term', 'hocwp-theme' ), $name ),
			'parent_item'                => sprintf( _x( 'Parent %s', 'custom taxonomy term', 'hocwp-theme' ), $singular_name ),
			'parent_item_colon'          => sprintf( _x( 'Parent %s:', 'custom taxonomy term', 'hocwp-theme' ), $singular_name ),
			'edit_item'                  => sprintf( _x( 'Edit %s', 'custom taxonomy term', 'hocwp-theme' ), $singular_name ),
			'view_item'                  => sprintf( _x( 'View %s', 'custom taxonomy term', 'hocwp-theme' ), $singular_name ),
			'update_item'                => sprintf( _x( 'Update %s', 'custom taxonomy term', 'hocwp-theme' ), $singular_name ),
			'add_new_item'               => sprintf( _x( 'Add New %s', 'custom taxonomy term', 'hocwp-theme' ), $singular_name ),
			'new_item_name'              => sprintf( _x( 'New %s Name', 'custom taxonomy term', 'hocwp-theme' ), $singular_name ),
			'separate_items_with_commas' => sprintf( _x( 'Separate %s with commas', 'custom taxonomy term', 'hocwp-theme' ), $name ),
			'add_or_remove_items'        => sprintf( _x( 'Add or remove %s', 'custom taxonomy term', 'hocwp-theme' ), $name ),
			'choose_from_most_used'      => sprintf( _x( 'Choose from the most used %s', 'custom taxonomy term', 'hocwp-theme' ), $name ),
			'not_found'                  => sprintf( _x( 'No %s found.', 'custom taxonomy term', 'hocwp-theme' ), $name ),
			'no_terms'                   => sprintf( _x( 'No %s', 'custom taxonomy term', 'hocwp-theme' ), $name ),
			'items_list_navigation'      => sprintf( _x( '%s list navigation', 'custom taxonomy term', 'hocwp-theme' ), $name ),
			'items_list'                 => sprintf( _x( '%s list', 'custom taxonomy term', 'hocwp-theme' ), $name ),
			'most_used'                  => _x( 'Most Used', 'custom taxonomy term', 'hocwp-theme' ),
			'back_to_items'              => sprintf( _x( '&larr; Back to %s', 'custom taxonomy term', 'hocwp-theme' ), $name )
		);
	}

	private function post_type_or_taxonomy_defaults( $args, $post_type = true ) {
		$args = HT_Sanitize()->post_type_or_taxonomy_args( $args );
		$name = $args['name'];

		$singular_name = $args['singular_name'];
		$menu_name     = $args['menu_name'];

		if ( empty( $name ) ) {
			return $args;
		}

		if ( $post_type ) {
			$labels = $this->post_type_labels( $name, $singular_name, $menu_name );
		} else {
			$labels = $this->taxonomy_labels( $name, $singular_name, $menu_name );
		}

		$defaults = array(
			'labels' => $labels,
			'public' => true
		);

		$private = $args['private'] ?? false;

		if ( ! $private && isset( $args['public'] ) ) {
			$private = ! ( $args['public'] );
		}

		if ( $private ) {
			$defaults['public']              = false;
			$defaults['show_ui']             = true;
			$defaults['exclude_from_search'] = true;
			$defaults['show_in_nav_menus']   = false;
			$defaults['show_in_admin_bar']   = false;
			$defaults['menu_position']       = 9999999;
			$defaults['has_archive']         = false;
			$defaults['query_var']           = false;
			$defaults['rewrite']             = false;
			$defaults['feeds']               = false;

			if ( ! $post_type ) {
				$defaults['show_in_quick_edit'] = false;
				$defaults['show_admin_column']  = false;
				$defaults['show_tagcloud']      = false;
			}
		}

		unset( $args['labels'], $args['name'], $args['singular_name'], $args['menu_name'], $args['private'] );

		$args = wp_parse_args( $args, $defaults );

		if ( ! isset( $args['rewrite'] ) || ! is_array( $args['rewrite'] ) ) {
			$args['rewrite'] = array();
		}

		$slug = $args['rewrite']['slug'] ?? '';

		if ( isset( $args['public'] ) && $args['public'] ) {
			if ( empty( $slug ) ) {
				$slug = sanitize_title( $singular_name );
			}

			$slug = str_replace( '_', '-', $slug );

			$args['rewrite']['slug'] = $slug;
		}

		return $args;
	}

	/**
	 * Generate arguments for register_post_type function.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function post_type_args( $args = array() ) {
		$args = $this->post_type_or_taxonomy_defaults( $args );

		return apply_filters( 'hocwp_theme_post_type_args', $args );
	}

	/**
	 * Generate arguments for register_taxonomy function.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function taxonomy_args( $args = array() ) {
		$args = $this->post_type_or_taxonomy_defaults( $args, false );

		return apply_filters( 'hocwp_theme_taxonomy_args', $args );
	}

	/**
	 * Generate arguments for register_sidebar function.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function sidebar_args( $args = array() ) {
		$defaults = array(
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => "</section>\n",
			'before_title'  => '<h3 class="widgettitle widget-title">',
			'after_title'   => "</h3>\n",
			'description'   => __( 'Add widgets here.', 'hocwp-theme' )
		);

		$args = wp_parse_args( $args, $defaults );

		return apply_filters( 'hocwp_theme_sidebar_args', $args );
	}

	public function status_text( $current, $text, $active_text ) {
		$span = new HOCWP_Theme_HTML_Tag( 'span' );
		$span->add_attribute( 'data-text', $text );
		$span->add_attribute( 'data-active-text', $active_text );

		if ( 1 === $current || true === $current ) {
			$span->set_text( $active_text );
		} else {
			$span->set_text( $text );
		}

		$span->add_attribute( 'data-current', HT()->bool_to_int( $current ) );
		$span->add_attribute( 'data-post-id', get_the_ID() );
		$span->output();
	}

	public function get_post_title( $post_id = 0, $keys = array( 'different_title', 'short_title' ) ) {
		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}

		if ( HT()->array_has_value( $keys ) ) {
			$key = array_shift( $keys );

			$tmp = get_post_meta( $post_id, $key, true );

			while ( empty( $tmp ) && HT()->array_has_value( $keys ) ) {
				$key = array_shift( $keys );

				$tmp = get_post_meta( $post_id, $key, true );
			}

			if ( ! empty( $tmp ) ) {
				return $tmp;
			}
		}

		return get_the_title( $post_id );
	}

	public function get_wpseo_post_title( $post_id ) {
		$title = get_post_meta( $post_id, '_yoast_wpseo_title', true );

		if ( empty( $title ) ) {
			$title = get_the_title( $post_id );
		}

		return $title;
	}

	public function the_title_link_html( $args = array() ) {
		$title     = HT()->get_value_in_array( $args, 'title' );
		$permalink = HT()->get_value_in_array( $args, 'permalink', get_permalink() );

		if ( empty( $title ) ) {
			/** @noinspection HtmlUnknownTarget */
			the_title( sprintf( '<a href="%s" rel="bookmark" title="%s">', esc_url( $permalink ), esc_attr( $title ) ), '</a>' );
		} else {
			/** @noinspection HtmlUnknownTarget */
			$title = sprintf( '<a href="%s" rel="bookmark" title="%s">', esc_url( $permalink ), esc_attr( $title ) ) . $title . '</a>';
			echo $title;
		}
	}

	public function check_user_password( $password, $user ) {
		return ( $user instanceof WP_User && wp_check_password( $password, $user->user_pass, $user->ID ) );
	}

	public function message_html( $message, $type = 'info' ) {
		$p = new HOCWP_Theme_HTML_Tag( 'p' );

		if ( ! empty( $type ) ) {
			$p->add_attribute( 'class', 'text-left alert alert-' . $type );
		}

		$p->set_text( $message );

		return $p->build();
	}

	public function get_menu_items( $menu, $args = array() ) {
		$object = wp_get_nav_menu_object( $menu );

		if ( ! ( $object instanceof WP_Term ) ) {
			return null;
		}

		return wp_get_nav_menu_items( $object->name, $args );
	}

	public function get_nav_menu_items_by_location( $location, $args = array() ) {
		$locations = get_nav_menu_locations();

		if ( ! is_array( $locations ) || ! isset( $locations[ $location ] ) ) {
			return null;
		}

		return $this->get_menu_items( $locations[ $location ], $args );
	}

	public function get_google_drive_file_url( $url, $api_key = '' ) {
		if ( empty( $api_key ) ) {
			$api_key = HT_Options()->get_google_api_key();
		}

		if ( ! empty( $api_key ) ) {
			$url = esc_url_raw( $url );

			$domain = HT()->get_domain_name( $url, true );

			if ( 'google.com' != $domain ) {
				return $url;
			}

			$query = HT()->get_params_from_url( $url );

			$id = '';

			if ( isset( $query['id'] ) ) {
				$id = $query['id'];
			} else {
				$parts = explode( '/', $url );
				$key   = array_search( 'd', $parts );
				if ( is_int( $key ) && isset( $parts[ $key + 1 ] ) ) {
					$id = $parts[ $key + 1 ];
				}
			}

			if ( empty( $id ) ) {
				$last = array_pop( $parts );
				$id   = remove_query_arg( 'e', $last );
			}

			if ( ! empty( $id ) ) {
				$url = 'https://www.googleapis.com/drive/v3/files/' . $id . '?alt=media&key=' . $api_key;
			}
		}

		return $url;
	}

	public function inline_script( $id, $src, $atts = array(), $async = true, $defer = true, $insert_before = '' ) {
		$src = add_query_arg( 'hl', get_locale(), $src );
		$src = add_query_arg( 'language', get_locale(), $src );
		?>
        <script>
            (function (d, s, id) {
                let js, gjs = d.getElementsByTagName(s)[0],
                    insertBefore = "<?php echo $insert_before; ?>",
                    node = null;

                if (insertBefore) {
                    node = d.getElementById(insertBefore);
                }

                if (d.getElementById(id)) {
                    return;
                }

                js = d.createElement(s);
                js.id = id;
                js.src = "<?php echo esc_url_raw( $src ); ?>";
				<?php
				if ( $async ) {
					echo 'js.async = "async";';
				}

				if ( $defer ) {
					echo 'js.defer = "defer";';
				}

				foreach ( $atts as $key => $value ) {
					echo 'js.setAttribute("' . esc_attr( $key ) . '", "' . esc_attr( $value ) . '");';
				}
				?>
                if (!node) {
                    node = gjs;
                }

                node.parentNode.insertBefore(js, node);
            }(document, "script", "<?php echo esc_attr( $id ); ?>"));
        </script>
		<?php
	}

	public function load_google_javascript_sdk( $args = array() ) {
		$load = $args['load'] ?? false;
		$load = apply_filters( 'hocwp_theme_load_google_sdk_javascript', $load );

		if ( ! $load ) {
			return;
		}

		$callback = $args['callback'] ?? '';

		if ( empty( $callback ) ) {
			return;
		}

		$locale = $args['locale'] ?? '';

		if ( empty( $locale ) ) {
			$locale = get_user_locale();
		}

		if ( 'vi' == $locale ) {
			$locale = 'vi_VN';
		}

		$src = 'https://apis.google.com/js/api.js';

		$atts = array(
			'onload'             => 'this.onload=' . $callback . '()',
			'onreadystatechange' => 'if (this.readyState === "complete") this.onload()'
		);

		$this->inline_script( 'google-jssdk', $src, $atts );
	}

	public function load_facebook_javascript_sdk( $args = array() ) {
		$options = $this->get_theme_options( 'social' );

		$load = $args['load'] ?? false;
		$load = apply_filters( 'hocwp_theme_load_facebook_sdk_javascript', $load );

		if ( $load ) {
			$sdk = $options['facebook_sdk_javascript'] ?? '';

			if ( empty( $sdk ) ) {
				$app_id = $args['app_id'] ?? '';

				if ( empty( $app_id ) ) {
					$app_id = $options['facebook_app_id'] ?? '';
				}

				if ( empty( $app_id ) ) {
					return;
				}

				$locale = $args['locale'] ?? '';

				if ( empty( $locale ) ) {
					$locale = get_user_locale();
				}

				if ( 'vi' == $locale ) {
					$locale = 'vi_VN';
				}

				$version = $args['version'] ?? '18.0';
				$version = trim( $version, 'v' );

				$src = 'https://connect.facebook.net/';
				$src .= $locale;
				$src .= '/sdk.js#xfbml=1&version=v';
				$src .= $version;
				$src .= '&appId=';
				$src .= $app_id;
				$this->inline_script( 'facebook-jssdk', $src );
				?>
                <div id="fb-root"></div>
				<?php
			} else {
				echo $sdk;
			}
		}
	}

	public function get_facebook_data_for_url( $url, $key = 'likes' ) {
		$url  = trailingslashit( $url );
		$base = 'https://graph.facebook.com/?fields=og_object{likes.limit(0).summary(true)},share';
		$url  = add_query_arg( 'ids', $url, $base );

		$res = wp_remote_get( $url );

		if ( ! is_wp_error( $res ) ) {
			$res = wp_remote_retrieve_body( $res );
			$res = json_decode( $res, true );
			$res = array_shift( $res );
		}

		if ( HT()->array_has_value( $res ) && ! empty( $key ) ) {
			if ( HOCWP_THEME_SUPPORT_PHP8 ) {
				$res = HT_PHP8()->match( $key, array(
					array(
						array(
							'share_count',
							'comment_count'
						),
						$res['share'][ $key ] ?? ''
					),
					'default' => $res['og_object']['likes']['summary']['total_count'] ?? ''
				) );
			} else {
				switch ( $key ) {
					case 'share_count':
					case 'comment_count':
						$res = $res['share'][ $key ] ?? '';
						break;
					default:
						$res = $res['og_object']['likes']['summary']['total_count'] ?? '';
				}
			}
		}

		return $res;
	}

	public function loop_select_option( $lists, $current ) {
		foreach ( $lists as $key => $label ) {
			if ( empty( $label ) ) {
				$label = ucfirst( $key );
			}
			?>
            <option
                    value="<?php echo esc_attr( $key ); ?>"<?php selected( $current, $key ); ?>><?php echo $label; ?></option>
			<?php
		}
	}

	public function menu_toggle_button( $args = array() ) {
		$id               = $args['id'] ?? '';
		$class            = $args['class'] ?? '';
		$control          = $args['control'] ?? '';
		$mobile_menu_icon = $args['icon'] ?? '';
		?>
        <button id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $class ); ?>"
                aria-controls="<?php echo $control; ?>" data-icon-type="<?php echo esc_attr( $mobile_menu_icon ); ?>"
                aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle menu', 'hocwp-theme' ); ?>">
			<?php
			if ( 'svg' == $mobile_menu_icon ) {
				HT_SVG_Icon()->bars();
				HT_SVG_Icon()->close();
			} elseif ( 'bars' == $mobile_menu_icon || 'burger-3' == $mobile_menu_icon ) {
				?>
                <span class="line-1"></span>
                <span class="line-2"></span>
                <span class="line-3"></span>
				<?php
			} elseif ( 'burger' == $mobile_menu_icon ) {
				?>
                <span class="line-1"></span>
                <span class="line-3"></span>
				<?php
			} else {
				echo $mobile_menu_icon;
			}
			?>
            <span class="screen-reader-text"><?php esc_html_e( 'Menu', 'hocwp-theme' ); ?></span>
        </button>
		<?php
	}

	public function get_table_prefix() {
		global $wpdb;

		if ( is_multisite() ) {
			return $wpdb->base_prefix;
		} else {
			return $wpdb->get_blog_prefix( 0 );
		}
	}

	public function create_database_table( $table_name, $sql_column ) {
		if ( str_contains( $sql_column, 'CREATE TABLE' ) || str_contains( $sql_column, 'create table' ) ) {
			HT_Util()->doing_it_wrong( __FUNCTION__, __( 'The <strong>$sql_column</strong> argument just only contains MySQL query inside (), it isn\'t full MySQL query.', 'hocwp-theme' ), '6.5.2' );

			return;
		}

		global $wpdb;

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
			$charset_collate = '';

			if ( ! empty( $wpdb->charset ) ) {
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}

			if ( ! empty( $wpdb->collate ) ) {
				$charset_collate .= " COLLATE $wpdb->collate";
			}

			/** @noinspection SqlNoDataSourceInspection */
			$sql = "CREATE TABLE ";
			$sql .= "$table_name ( $sql_column ) $charset_collate;\n";

			if ( ! function_exists( 'dbDelta' ) ) {
				load_template( ABSPATH . 'wp-admin/includes/upgrade.php' );
			}

			dbDelta( $sql );
		}
	}

	public function is_database_table_exists( $table_name ) {
		global $wpdb;

		if ( ! HT()->string_contain( $table_name, $wpdb->prefix ) ) {
			$table_name = $wpdb->prefix . $table_name;
		}

		$result = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" );

		if ( empty( $result ) ) {
			return false;
		}

		return true;
	}

	public function delete_transient( $transient_name = '' ) {
		global $wpdb;

		/** @noinspection SqlNoDataSourceInspection */
		$query_root = "DELETE FROM $wpdb->options";
		$query_root .= " WHERE option_name like %s";
		$key_1      = '_transient_';
		$key_2      = '_transient_timeout_';

		if ( ! empty( $transient_name ) ) {
			$transient_name = '%' . $transient_name . '%';

			$key_1 .= $transient_name;
			$key_2 .= $transient_name;
		}

		$key_1 = $wpdb->prepare( $query_root, $key_1 );
		$key_2 = $wpdb->prepare( $query_root, $key_2 );

		$wpdb->query( $key_1 );
		$wpdb->query( $key_2 );
	}

	public function clear_div( $class = 'clearfix' ) {
		printf( '<div class="clear %s"></div>', esc_attr( $class ) );
	}

	public function display_ads( $args, $random = false ) {
		if ( HT()->is_google_pagespeed() ) {
			return;
		}

		if ( function_exists( 'hocwp_ext_ads_display' ) ) {
			hocwp_ext_ads_display( $args, $random );
		}
	}

	public function get_theme_options( $tab ) {
		$options = HOCWP_Theme()->get_options();

		$options = $options[ $tab ] ?? '';

		if ( ! is_array( $options ) ) {
			$options = array();
		}

		return $options;
	}

	public function get_admin_colors( $color = '' ) {
		global $_wp_admin_css_colors;

		$colors = $_wp_admin_css_colors;

		if ( ! empty( $color ) ) {
			$color = $colors[ $color ] ?? '';
		}

		return $color;
	}

	public function toggle_duration() {
		return apply_filters( 'hocwp_theme_toggle_duration', 250 );
	}

	public function unique_id( $prefix = '' ) {
		static $id_counter = 0;

		if ( function_exists( 'wp_unique_id' ) ) {
			return wp_unique_id( $prefix );
		}

		return $prefix . ++ $id_counter;
	}

	public function is_plugin_active( $plugin ) {
		if ( str_contains( $plugin, '.php' ) ) {
			return is_plugin_active( $plugin );
		}

		$plugin_dir = trailingslashit( WP_PLUGIN_DIR ) . $plugin;

		if ( ! is_dir( $plugin_dir ) ) {
			return false;
		} else {
			$files = scandir( $plugin_dir );

			foreach ( $files as $file ) {
				if ( '.' !== $file && '..' !== $file ) {
					$file = trailingslashit( $plugin_dir ) . $file;

					if ( is_file( $file ) ) {
						$data = get_file_data( $file, array( 'Name' => 'Plugin Name' ) );

						if ( ! empty( $data['Name'] ) ) {
							$data = HT_Util()->get_plugin_info( $data['Name'] );

							if ( empty( $data ) || ! isset( $data['basename'] ) ) {
								return is_plugin_active( $data['basename'] );
							}
						}
					}
				}
			}
		}

		return false;
	}

	public function get_wp_plugin_info( $name, $args = array(), $cache = true, $action = 'plugin_information' ) {
		$defaults = array(
			'fields' => array(
				'last_updated'      => true,
				'icons'             => true,
				'active_installs'   => true,
				'short_description' => true
			),
			'slug'   => $name
		);

		$args = wp_parse_args( $args, $defaults );

		$tr_name = 'hocwp_theme_plugin_api_' . md5( json_encode( $args ) );

		if ( ! $cache || false === ( $api = get_transient( $tr_name ) ) ) {
			if ( ! function_exists( 'plugins_api' ) ) {
				require ABSPATH . 'wp-admin/includes/plugin-install.php';
			}

			$api = plugins_api( $action, $args );
		}

		if ( $cache && ! is_wp_error( $api ) ) {
			if ( ! is_numeric( $cache ) ) {
				$cache = DAY_IN_SECONDS;
			}

			set_transient( $tr_name, $api, $cache );
		}

		return $api;
	}

	public function get_plugin_info( $name, $folder_name = '' ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! $name && ! empty( $folder_name ) ) {
			$plugin_dir = $folder_name;

			if ( ! is_dir( $plugin_dir ) ) {
				$plugin_dir = trailingslashit( WP_PLUGIN_DIR ) . $folder_name;
			}

			if ( is_dir( $plugin_dir ) ) {
				$files = scandir( $plugin_dir );

				foreach ( $files as $file ) {
					if ( '.' !== $file && '..' !== $file ) {
						$file = trailingslashit( $plugin_dir ) . $file;

						if ( is_file( $file ) ) {
							$headers = array(
								'Name' => 'Plugin Name'
							);

							$data = get_file_data( $file, $headers );

							if ( ! empty( $data['Name'] ) ) {
								return HT_Util()->get_plugin_info( $data['Name'] );
							}
						}
					}
				}
			}

			$api = HT_Util()->get_wp_plugin_info( $folder_name );

			if ( ! is_wp_error( $api ) ) {
				if ( ! is_array( $api ) ) {
					$api = (array) $api;
				}

				return $api;
			}

			return null;
		}

		if ( is_string( $name ) && ! empty( $name ) ) {
			$plugins = get_plugins();

			foreach ( $plugins as $file => $data ) {
				if ( $name == $data['Name'] ) {
					$data['basename'] = $file;

					return $data;
				}
			}
		}

		return null;
	}

	public function background_image_css( $image, $color = '' ) {
		$style = '';

		if ( ! empty( $color ) ) {
			$style .= 'background-color:' . $color . ';';
		}

		if ( HT_Media()->exists( $image ) ) {
			$style .= sprintf( 'background-image: url("%s");', wp_get_original_image_url( $image ) );
		}

		return trim( $style );
	}

	public function doing_it_wrong( $function, $message, $version ) {
		if ( is_admin() ) {
			add_action( 'admin_menu', function () use ( $function, $message, $version ) {
				_doing_it_wrong( $function, $message, $version );
			} );
		} else {
			add_action( 'wp', function () use ( $function, $message, $version ) {
				_doing_it_wrong( $function, $message, $version );
			} );
		}
	}


}

function HT_Util() {
	return HOCWP_Theme_Utility::instance();
}