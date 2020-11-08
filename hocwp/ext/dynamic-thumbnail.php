<?php
/*
 * Name: Dynamic Thumbnail
 * Description: Auto detect post thumbnail and displaying it dynamically.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HOCWP_Ext_Dynamic_Thumbnail' ) ) {
	final class HOCWP_Ext_Dynamic_Thumbnail extends HOCWP_Theme_Extension {
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

			add_action( 'after_setup_theme', array( $this, 'after_setup_theme_action' ) );

			if ( is_admin() ) {
				add_action( 'load-post.php', array( $this, 'meta_boxes' ) );
				add_action( 'load-post-new.php', array( $this, 'meta_boxes' ) );

				add_filter( 'hocwp_theme_setting_page_writing_fields', array(
					$this,
					'page_setting_writing_fields'
				) );

				add_action( 'save_post', array( $this, 'save_post_action' ), 999999 );
			} else {
				add_filter( 'hocwp_theme_post_thumbnail_size_display', array(
					$this,
					'post_thumbnail_size_display_filter'
				) );
			}
		}

		public function auto_set_featured_image( $post_id ) {
			$obj = get_post( $post_id );

			if ( ! empty( $obj->post_content ) ) {
				$url = HT()->get_first_image_source( $obj->post_content );

				if ( ! empty( $url ) ) {
					$id = HT_Media()->url_to_id( $url );

					if ( ! HT_Media()->exists( $id ) ) {
						$id = HT_Media()->download_image( $url, null, true );
					}

					if ( HT_Media()->exists( $id ) ) {
						set_post_thumbnail( $post_id, $id );
					}
				}
			}
		}

		public function save_post_action( $post_id ) {
			if ( wp_is_post_revision( $post_id ) ) {
				return;
			}

			// Check if post has thumbnail
			if ( ! isset( $_POST['_thumbnail_id'] ) || ! HT_Media()->exists( $_POST['_thumbnail_id'] ) ) {
				$auto_thumbnail = HT_Options()->get_tab( 'auto_thumbnail', '', 'writing' );

				if ( $auto_thumbnail ) {
					$this->auto_set_featured_image( $post_id );
				}
			}
		}

		public function page_setting_writing_fields( $fields ) {
			$args = array(
				'class' => 'medium-text',
				'type'  => 'checkbox',
				'label' => __( 'Enable function to find thumbnail from post content automatically.', 'hocwp-theme' )
			);

			$field = hocwp_theme_create_setting_field( 'auto_thumbnail', __( 'Auto Thumbnail', 'hocwp-theme' ), '', $args, 'boolean', 'writing' );

			$fields['auto_thumbnail'] = $field;

			return $fields;
		}

		public function is_thumbnail_size( $size ) {
			return ( is_string( $size ) && ( 'thumbnail' == $size || 'post-thumbnail' == $size ) );
		}

		public function post_thumbnail_size_display_filter( $size ) {
			//$size = HT_Util()->get_image_size( $size );

			return $size;
		}

		public function after_setup_theme_action() {
		}

		public function meta_boxes() {
			$post_types = get_post_types();
			$meta       = new HOCWP_Theme_Meta_Post();

			foreach ( $post_types as $post_type ) {
				if ( post_type_supports( $post_type, 'thumbnail' ) ) {
					$meta->add_post_type( $post_type );
				}
			}

			$meta->set_id( 'dynamic-thumbnail' );
			$meta->set_title( __( 'Dynamic Thumbnail', 'hocwp-theme' ) );
			$meta->form_table = true;

			$field = hocwp_theme_create_meta_field( '_thumbnail_url', __( 'Thumbnail Url:', 'hocwp-theme' ) );
			$meta->add_field( $field );
		}
	}
}

if ( ! function_exists( 'HTE_Dynamic_Thumbnail' ) ) {
	function HTE_Dynamic_Thumbnail() {
		return HOCWP_Ext_Dynamic_Thumbnail::get_instance();
	}
}

HTE_Dynamic_Thumbnail()->get_instance();

$load = apply_filters( 'hocwp_theme_load_extension_dynamic_thumbnail', HT_Extension()->is_active( __FILE__ ) );

if ( ! $load ) {
	return;
}

function hocwp_theme_post_thumbnail_html_filter( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
	if ( ! empty( $html ) ) {
		return $html;
	}

	if ( HT_Util()->is_amp() ) {
		return $html;
	}

	if ( empty( $size ) || 'post-thumbnail' == $size ) {
		$size = 'thumbnail';
	}

	$obj = get_post( $post_id );
	$alt = $obj->post_title;

	$sizes = apply_filters( 'hocwp_theme_post_thumbnail_size_display', $size, $post_id );

	if ( empty( $sizes ) || 'post-thumbnail' == $sizes ) {
		$sizes = 'thumbnail';
	}

	if ( is_string( $sizes ) ) {
		$sizes = HT_Util()->get_image_size( $sizes );
	}

	$class = '';
	$attr  = HOCWP_Theme::attribute_to_array( $attr );

	if ( isset( $attr['class'] ) ) {
		$class .= ' ' . $attr['class'];
		$class = trim( $class );
	}

	if ( false === strpos( $class, 'wp-post-image' ) ) {
		$class .= ' wp-post-image';
	}

	$lazyload = isset( $attr['lazyload'] ) ? $attr['lazyload'] : false;

	$url = get_post_meta( $post_id, '_thumbnail_url', true );

	$ext_url = $url;

	if ( ! HOCWP_Theme::is_image_url( $url ) ) {
		$url = HOCWP_Theme::get_first_image_source( $obj->post_content );
	}

	remove_filter( 'get_post_metadata', 'hocwp_theme_check_post_has_thumbnail', 10 );

	if ( ! has_post_thumbnail( $post_id ) && HT()->is_image_url( $url ) ) {
		$id = HT_Media()->download_image( $url );

		if ( HT()->is_positive_number( $id ) ) {
			set_post_thumbnail( $post_id, $id );

			$image = wp_get_attachment_image( $id, $size, false, $attr );

			return $image;
		}
	}

	add_filter( 'get_post_metadata', 'hocwp_theme_check_post_has_thumbnail', 10, 3 );

	$src = $url;

	$width  = ( is_array( $sizes ) && isset( $sizes['width'] ) ) ? $sizes['width'] : '';
	$height = isset( $sizes['height'] ) ? $sizes['height'] : '';

	$external = false;

	if ( ! empty( $url ) ) {
		$root_domain = HT()->get_domain_name( $url, true );
		$blog_domain = HT()->get_domain_name( home_url(), true );

		if ( $root_domain != $blog_domain ) {
			$external = true;
		}
	}

	if ( $external || HOCWP_Theme::is_image_url( $url ) ) {
		$file_path = '';

		if ( ! $external ) {
			$post_thumbnail_id = HT_Util()->get_attachment_id( $url );

			if ( HT()->is_positive_number( $post_thumbnail_id ) ) {
				$file_path = get_attached_file( $post_thumbnail_id );
			}
		}

		$dirs = wp_get_upload_dir();

		$file_name = basename( $url );

		if ( isset( $sizes['crop'] ) ) {
			$crop = (bool) $sizes['crop'];
		} else {
			if ( ! isset( $sizes['width'] ) && ! isset( $sizes['height'] ) && isset( $sizes[2] ) ) {
				$crop = (bool) $sizes[2];
			} else {
				$crop = (bool) get_option( 'thumbnail_crop' );
			}
		}

		if ( ! file_exists( $file_path ) ) {
			$pos = HT()->string_contain( $url, 'wp-content/uploads' );

			if ( false !== $pos && ! $external ) {
				$sub       = substr( $url, $pos - 1 );
				$file_path = ABSPATH . $sub;

				if ( file_exists( $file_path ) ) {
					$external = false;

					$url = home_url( $sub );
				}
			}

			if ( ! file_exists( $file_path ) ) {
				$src = HOCWP_THEME_CORE_URL . '/ext/thumbnail.php';
				$src = esc_url_raw( $src );

				$params = array(
					'src'     => $url,
					'crop'    => 0,
					'cache'   => isset( $attr['cache'] ) ? $attr['cache'] : 1,
					'quality' => isset( $attr['quality'] ) ? $attr['quality'] : 100
				);

				$src = add_query_arg( $params, $src );

				$udir = $dirs['basedir'];

				$file_path = trailingslashit( $udir ) . 'cache/' . $file_name;
			}
		}

		if ( ! file_exists( $file_path ) ) {
			$src = add_query_arg( 'src', $url, $src );
			HT_Util()->write_all_text( $file_path, HT_Util()->get_contents( $src ) );
		}

		$ext = pathinfo( $file_path, PATHINFO_EXTENSION );

		$new_size = sprintf( '$1-%sx%s.%s', $width, $height, $ext );

		$new_path = preg_replace( '/^(.*)\.' . $ext . '$/', $new_size, $file_path );

		$regen = false;

		if ( HT()->is_file( $file_path ) && ! file_exists( $new_path ) ) {
			$crop    = (bool) $crop;
			$resized = image_make_intermediate_size( $file_path, $width, $height, $crop );

			if ( ! $external && HT()->is_positive_number( $post_thumbnail_id ) && isset( $resized['file'] ) ) {
				$url = wp_get_attachment_url( $post_thumbnail_id );
				$src = dirname( $url );
				$src = trailingslashit( $src );
				$src .= $resized['file'];
			} else {
				$regen = true;
			}
		} elseif ( ! file_exists( $new_path ) ) {
			$regen = true;
		}

		if ( $regen ) {
			if ( file_exists( $new_path ) ) {
				$new_name = basename( $new_path );

				if ( $external ) {
					$src = trailingslashit( $dirs['baseurl'] );

					$src .= 'cache/' . $new_name;
				} else {
					$src = str_replace( $file_name, $new_name, $url );
				}
			} elseif ( ! empty( $ext_url ) ) {
				$src = $ext_url;
			}
		} elseif ( file_exists( $new_path ) ) {
			$src = str_replace( $dirs['basedir'], $dirs['baseurl'], $new_path );
		}


		if ( HT()->string_contain( $src, 'thumbnail.php' ) && file_exists( $file_path ) ) {
			$new_size = sprintf( '$1-%sx%s.%s', $width, $height, $ext );

			$new_path = preg_replace( '/^(.*)\.' . $ext . '$/', $new_size, $file_path );

			if ( file_exists( $new_path ) ) {
				$new_name = basename( $new_path );

				if ( $external ) {
					$src = trailingslashit( $dirs['baseurl'] );

					$src .= 'cache/' . $new_name;
				} else {
					$src = str_replace( $file_name, $new_name, $url );
				}
			}
		}

		if ( $lazyload ) {
			$html = sprintf( '<img class="%s" data-original="%s" src="%s" alt="%s" data-src="%s">', $class, $src, HOCWP_Theme_Utility::get_wp_image_url( 'blank.gif' ), $alt, $src );
		} else {
			$html = sprintf( '<img class="%s" src="%s" alt="%s">', $class, $src, $alt );
		}
	} else {
		$style = '';

		if ( ! empty( $width ) ) {
			$style .= "width:{$width}px;";
		}

		if ( ! empty( $height ) ) {
			$style .= "height:{$height}px;";
		}

		if ( ! function_exists( 'hocwp_theme_get_default_post_thumbnail' ) ) {
			require_once HOCWP_Theme()->core_path . '/inc/template-post.php';
		}

		$html = hocwp_theme_get_default_post_thumbnail( $sizes, $attr, $style );
	}

	return $html;
}

add_filter( 'post_thumbnail_html', 'hocwp_theme_post_thumbnail_html_filter', 10, 5 );

/*
 * Recheck post has thumbnail
 */
function hocwp_theme_check_post_has_thumbnail( $check, $post_id, $meta_key ) {
	$obj = get_post( $post_id );

	if ( '_thumbnail_id' == $meta_key && $obj instanceof WP_Post && 'revision' != $obj->post_type ) {
		global $pagenow;

		if ( 'link.php' != $pagenow ) {
			remove_filter( 'get_post_metadata', 'hocwp_theme_check_post_has_thumbnail', 10 );
			$result = get_post_meta( $post_id, $meta_key, true );
			add_filter( 'get_post_metadata', 'hocwp_theme_check_post_has_thumbnail', 10, 3 );

			if ( empty( $result ) ) {
				remove_filter( 'get_post_metadata', 'hocwp_theme_check_post_has_thumbnail', 10 );
				$result = get_post_meta( $post_id, '_thumbnail_url', true );
				add_filter( 'get_post_metadata', 'hocwp_theme_check_post_has_thumbnail', 10, 3 );

				if ( empty( $result ) ) {
					$images = HOCWP_Theme::get_all_image_from_string( $obj->post_content, 'src' );

					if ( HOCWP_Theme::array_has_value( $images ) ) {
						$src = '';

						foreach ( $images as $image ) {
							$tmp = HOCWP_Theme::get_first_image_source( $image );

							if ( HT()->string_contain( $tmp, home_url() ) ) {
								$src   = $image;
								$check = - 1;
								break;
							}
						}

						if ( empty( $src ) ) {
							$src   = array_shift( $images );
							$check = - 1;
						}

						$src = HOCWP_Theme::get_first_image_source( $src );
						update_post_meta( $post_id, '_thumbnail_url', $src );
					}
				} else {
					$check = - 1;
				}
			}
		}
	}

	return $check;
}

add_filter( 'get_post_metadata', 'hocwp_theme_check_post_has_thumbnail', 10, 3 );

function hocwp_theme_post_thumbnail_size_filter( $size ) {
	$sizes = HOCWP_Theme_Utility::get_image_size( $size );

	if ( ! empty( $sizes ) ) {
		$size = $sizes;
	}

	return $size;
}

add_filter( 'post_thumbnail_size', 'hocwp_theme_post_thumbnail_size_filter', 99 );

function hocwp_theme_get_attachment_image_attributes_filter( $attr, $attachment, $size ) {
	$post_id = get_the_ID();

	if ( ! is_array( $attr ) ) {
		$attr = array();
	}

	if ( empty( $size ) ) {
		$size = HT_Util()->get_image_size( 'thumbnail' );
	}

	$class = isset( $attr['class'] ) ? $attr['class'] : '';
	$class = trim( $class );

	if ( is_array( $size ) ) {
		unset( $size['width'], $size['height'] );
		$replace = join( 'x', $size );
		$replace = ltrim( $replace, 'x' );
	} else {
		$replace = $size;
	}

	$classes = explode( ' ', $class );

	foreach ( $classes as $key => $part ) {
		$part = explode( 'xx', $part );

		$classes[ $key ] = current( $part );
	}

	$class = join( ' ', $classes );

	unset( $classes, $key, $part );

	if ( false !== strpos( $class, 'attachment- size-' ) ) {
		$class = str_replace( 'attachment- size-', 'attachment-' . $replace . ' size-' . $replace, $class );
	} elseif ( 'attachment- size-' == $class ) {
		$class = 'attachment-' . $replace . ' size-' . $replace;
	} elseif ( is_array( $size ) ) {
		$class = str_replace( 'x' . $replace, '', $class );
		$class = str_replace( 'x size', ' size', $class );
		$class = rtrim( $class, 'x' );
	}

	// Find and remove duplicate size in class name.
	if ( HT()->array_has_value( $size ) ) {
		$find  = join( 'x', $size );
		$find  .= 'x';
		$class = str_replace( $find, '', $class );
	}

	unset( $replace );

	$sizes = HT_Util()->get_image_sizes();

	$name = HT_Media()->convert_image_size_to_name( $size, $sizes );

	unset( $sizes );

	if ( ! empty( $name ) ) {
		$class .= ' size-' . sanitize_html_class( $name );
	}

	unset( $name );

	if ( false === strpos( $class, 'wp-post-image' ) ) {
		$class .= ' wp-post-image';
	}

	$class = str_replace( 'x wp-post-image', ' wp-post-image', $class );

	if ( $attachment instanceof WP_Post ) {
		$class                 .= ' attachment-id-' . $attachment->ID;
		$attr['data-media-id'] = $attachment->ID;
	}

	$object = get_post( $post_id );

	if ( $object instanceof WP_Post ) {
		$class                .= ' post-type-' . $object->post_type;
		$attr['data-post-id'] = $object->ID;
	}

	unset( $post_id, $object );

	$attr['class'] = $class;

	unset( $class );

	return $attr;
}

add_filter( 'wp_get_attachment_image_attributes', 'hocwp_theme_get_attachment_image_attributes_filter', 99, 3 );