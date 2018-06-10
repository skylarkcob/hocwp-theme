<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Utility {
	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	protected function __construct() {
	}

	public function is_amp() {
		if ( isset( $_GET['amp'] ) ) {
			return true;
		}

		$amp = HT()->get_method_value( 'amp', 'get' );

		if ( 1 != $amp ) {
			$amp = get_query_var( 'amp' );
		}

		if ( 1 != $amp ) {
			$request = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
			$request = basename( $request );

			if ( 'amp' === $request ) {
				return true;
			}
		}

		return ( 1 == $amp );
	}

	public static function get_wp_image_url( $name ) {
		return includes_url( 'images/' . $name );
	}

	public static function get_my_image_url( $name ) {
		return HOCWP_THEME_CORE_URL . '/images/' . $name;
	}

	public static function get_custom_image_url( $name ) {
		return HOCWP_THEME_CUSTOM_URL . '/images/' . $name;
	}

	public static function get_current_url( $with_param = false ) {
		global $hocwp_theme_protocol;
		$current_url = $hocwp_theme_protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		if ( $with_param ) {
			$params = isset( $_SERVER['QUERY_STRING'] ) ? $_SERVER['QUERY_STRING'] : '';

			if ( ! empty( $params ) ) {
				$params = explode( '&', $params );
				$parts  = array();

				foreach ( $params as $param ) {
					$param = explode( '=', $param );

					if ( 2 == count( $param ) ) {
						$parts[ $param[0] ] = $param[1];
					}
				}

				$current_url = add_query_arg( $parts, $current_url );
			}
		} else {
			$current_url = strtok( $current_url, '?' );
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
		} elseif ( 'ID' == $output ) {
			return $current->ID;
		}

		return $current->ID;
	}

	public function get_first_term( $post_id = null, $taxonomy = 'category' ) {
		$post_id = $this->return_post( $post_id, 'id' );
		$terms   = wp_get_post_terms( $post_id, $taxonomy );

		return ( HT()->array_has_value( $terms ) ) ? current( $terms ) : null;
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

		return $select;
	}

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

	public function get_feed_items( $args = array() ) {
		$items = $this->fetch_feed( $args );

		if ( HT()->array_has_value( $items ) ) {
			$result = array();

			foreach ( $items as $item ) {
				if ( ! $this->is_object_valid( $item ) ) {
					continue;
				}

				$description = $item->get_description();
				$thumbnail   = HT()->get_first_image_source( $description );
				$description = wp_strip_all_tags( $description );
				$content     = $item->get_content();

				if ( empty( $thumbnail ) ) {
					$thumbnail = HT()->get_first_image_source( $content );
				}

				$value = array(
					'permalink'   => $item->get_permalink(),
					'title'       => $item->get_title(),
					'date'        => $item->get_date(),
					'image_url'   => $thumbnail,
					'description' => $description,
					'content'     => $content
				);

				array_push( $result, $value );
			}
		} else {
			return $items;
		}

		return $result;
	}

	public function is_object_valid( $object ) {
		return ( is_object( $object ) && ! is_wp_error( $object ) ) ? true : false;
	}

	public static function get_file_or_dir_url( $file_or_dir ) {
		if ( ! empty( $file_or_dir ) ) {
			$file_or_dir = wp_normalize_path( $file_or_dir );

			$dir = ABSPATH;
			$dir = wp_normalize_path( $dir );
			$dir = untrailingslashit( $dir );
			$url = untrailingslashit( get_site_url() );
			$url = str_replace( '/', '\\', $url );
			$url = str_replace( $dir, $url, $file_or_dir );
			$url = str_replace( '\\', '/', $url );

			return $url;
		}

		return '';
	}

	public static function ajax_overlay() {
		?>
		<div class="hocwp-theme ajax-overlay">
			<img src="<?php echo esc_url( self::get_my_image_url( 'loading-circle.gif' ) ); ?>" alt="">
		</div>
		<?php
	}

	public static function filesystem() {
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			if ( ! function_exists( 'get_file_description' ) ) {
				require ABSPATH . 'wp-admin/includes/file.php';
			}

			WP_Filesystem();
		}

		return $wp_filesystem;
	}

	public function get_class_name_from_file( $file ) {
		$class = '';

		$buffer = HT_Util()->read_all_text( $file );

		if ( preg_match( '/class\s+(\w+)(.*)?\{/', $buffer, $matches ) ) {
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

	public static function get_contents( $url ) {
		$filesystem = self::filesystem();

		if ( $filesystem instanceof WP_Filesystem_Base ) {
			return $filesystem->get_contents( $url );
		}

		return '';
	}

	public static function read_all_text( $path ) {
		if ( HT()->is_file( $path ) ) {
			return self::get_contents( $path );
		}

		return '';
	}

	public static function write_all_text( $path, $text ) {
		$filesystem = self::filesystem();

		if ( $filesystem instanceof WP_Filesystem_Base ) {
			return $filesystem->put_contents( $path, $text );
		}

		return '';
	}

	public static function wrap_text( $before, $text, $after ) {
		echo $before . $text . $after;
	}

	public static function normalize_path( $path, $slash = '/' ) {
		if ( ! empty( $path ) ) {
			$path = wp_normalize_path( $path );

			if ( '/' !== $slash ) {
				$path = str_replace( '/', '\\', $path );
			}
		}

		return $path;
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

	public static function admin_notice( $args = array() ) {
		if ( ! is_array( $args ) ) {
			$args = array(
				'message' => $args
			);
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

		$message = isset( $args['message'] ) ? $args['message'] : '';

		if ( ! empty( $message ) ) {
			if ( $args['autop'] ) {
				$message = wpautop( $message );
			} else {
				$message = HT()->wrap_text( $message, '<p>', '</p>' );
			}

			$hidden_interval = isset( $args['hidden_interval'] ) ? $args['hidden_interval'] : 0;

			if ( HOCWP_Theme::is_positive_number( $hidden_interval ) ) {
				$class .= ' auto-hide';
				ob_start();
				?>
				<script>
					jQuery(document).ready(function ($) {
						setTimeout(function () {
							var notices = $('.hocwp-theme.notice.auto-hide');
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

			$echo = isset( $args['echo'] ) ? (bool) $args['echo'] : true;

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

	public static function get_image_sizes() {
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

	public static function get_image_size( $size ) {
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

	public static function get_image_width( $size ) {
		if ( ! $size = self::get_image_size( $size ) ) {
			return false;
		}

		if ( isset( $size['width'] ) ) {
			return $size['width'];
		}

		return false;
	}

	public static function get_image_height( $size ) {
		if ( ! $size = self::get_image_size( $size ) ) {
			return false;
		}

		if ( isset( $size['height'] ) ) {
			return $size['height'];
		}

		return false;
	}

	public static function timestamp_to_string( $timestamp, $format = null, $timezone = null ) {
		if ( ! is_int( $timestamp ) ) {
			$timestamp = intval( $timestamp );
		}

		global $hocwp_theme;
		$defaults = $hocwp_theme->defaults;

		if ( null == $format ) {
			$df     = ( isset( $defaults['date_format'] ) && ! empty( $defaults['date_format'] ) ) ? $defaults['date_format'] : 'Y-m-d';
			$tf     = ( isset( $defaults['time_format'] ) && ! empty( $defaults['time_format'] ) ) ? $defaults['time_format'] : 'H:i:s';
			$format = "$df $tf";
		}

		$date = new DateTime();
		$date->setTimestamp( $timestamp );

		if ( null == $timezone ) {
			if ( isset( $defaults['timezone_string'] ) && ! empty( $defaults['timezone_string'] ) ) {
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

	public static function verify_nonce( $nonce_action = - 1, $nonce_name = '_wpnonce' ) {
		if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
			$nonce = isset( $_REQUEST[ $nonce_name ] ) ? $_REQUEST[ $nonce_name ] : '';

			return wp_verify_nonce( $nonce, $nonce_action );
		}

		return true;
	}

	public static function can_save_post( $post_id, $nonce_action = - 1, $nonce_name = '_wpnonce' ) {
		if ( ! self::verify_nonce( $nonce_action, $nonce_name ) ) {
			return false;
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return false;
		}

		$obj = get_post( $post_id );

		if ( 'trash' == $obj->post_status || ( isset( $_REQUEST['action'] ) && ( 'untrash' == $_REQUEST['action'] || 'trash' == $_REQUEST['action'] ) ) ) {
			return false;
		}

		return true;
	}

	public function get_client_info( $save = false ) {
		if ( $save ) {
			$client_info = isset( $_COOKIE['hocwp_theme_client_info'] ) ? $_COOKIE['hocwp_theme_client_info'] : '';

			if ( empty( $client_info ) ) {
				$client_info = isset( $_SESSION['hocwp_theme_client_info'] ) ? $_SESSION['hocwp_theme_client_info'] : '';
			}

			if ( is_string( $client_info ) ) {
				$client_info = HT()->json_string_to_array( $client_info );
			}
		} else {
			global $hocwp_theme;

			if ( isset( $hocwp_theme->client_info ) ) {
				$client_info = $hocwp_theme->client_info;
			} else {
				$client_info = array();
			}

			if ( empty( $client_info ) ) {
				$client_info = $this->get_client_info( true );
			}
		}

		return (array) $client_info;
	}

	public function get_sidebars() {
		return $GLOBALS['wp_registered_sidebars'];
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

	public function term_link_html( $term ) {
		if ( ! ( $term instanceof WP_Term ) ) {
			return '';
		}
		$a = new HOCWP_Theme_HTML_Tag( 'a' );
		$a->add_attribute( 'href', esc_url( get_term_link( $term ) ) );
		$a->set_text( $term->name );
		$a->add_attribute( 'class', sanitize_html_class( $term->taxonomy ) );
		$tax = get_taxonomy( $term->taxonomy );
		if ( $tax->hierarchical ) {
			$a->add_attribute( 'rel', 'category' );
		} else {
			$a->add_attribute( 'rel', ' tag' );
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
		$parse = parse_url( $url, PHP_URL_QUERY );
		parse_str( $parse, $params );

		$id = '';

		if ( isset( $params['v'] ) && strlen( $params['v'] ) > 0 ) {
			$id = $params['v'];
		}

		return $id;
	}

	public function get_youtube_video_info( $url, $api_key = '' ) {
		if ( empty( $api_key ) ) {
			$api_key = hocwp_theme_get_option( 'google_api_key', '', 'social' );
		}

		$base = 'https://www.googleapis.com/youtube/v3/videos/';

		$params = array(
			'part' => 'snippet,contentDetails,statistics',
			'id'   => $this->get_youtube_video_id( $url ),
			'key'  => $api_key
		);

		$api_url = add_query_arg( $params, $base );

		$data = HT_Util()->get_contents( $api_url );

		return json_decode( $data );
	}

	public static function get_paged() {
		$paged = get_query_var( 'paged' );

		if ( ! HT()->is_positive_number( $paged ) ) {
			$paged = get_query_var( 'page' );
		}

		return ( HT()->is_positive_number( $paged ) ) ? $paged : 1;
	}

	public function get_title_separator() {
		return apply_filters( 'hocwp_theme_title_separator', ( class_exists( 'WPSEO_Utils' ) ) ? WPSEO_Utils::get_title_separator() : '&raquo;' );
	}

	public static function get_posts_per_page( $home = false ) {
		global $hocwp_theme;

		if ( null === $home ) {
			$home = is_home();
		}

		if ( $home ) {
			$ppp = $hocwp_theme->options['home']['posts_per_page'];
		} else {
			$ppp = $hocwp_theme->defaults['posts_per_page'];
		}

		if ( ! is_numeric( $ppp ) ) {
			$ppp = get_option( 'posts_per_page' );
		}

		return apply_filters( 'hocwp_theme_posts_per_page', $ppp, $home );
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

	public static function html_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {
		add_filter( 'wp_mail_content_type', 'hocwp_theme_wp_mail_content_type_filter', 99 );
		$sent = wp_mail( $to, $subject, $message, $headers, $attachments );
		remove_filter( 'wp_mail_content_type', 'hocwp_theme_wp_mail_content_type_filter', 99 );

		return $sent;
	}

	public static function post_types_support_featured() {
		$post_types = get_post_types( array( 'public' => true ) );
		unset( $post_types['attachment'] );

		return apply_filters( 'post_types_support_featured', $post_types );
	}

	public static function get_theme_option( $name, $default = '', $base = 'general' ) {
		global $hocwp_theme;
		$options = $hocwp_theme->options;
		$options = isset( $options[ $base ] ) ? $options[ $base ] : '';
		$value   = isset( $options[ $name ] ) ? $options[ $name ] : '';

		if ( empty( $value ) && gettype( $value ) != gettype( $default ) ) {
			$value = $default;
		}

		return $value;
	}

	public static function get_theme_option_term( $name, $taxonomy = 'category', $base = 'general', $slug = '' ) {
		$term_id = self::get_theme_option( $name, '', $base );
		if ( ! HT()->is_positive_number( $term_id ) && ! empty( $slug ) ) {
			$term = get_term_by( 'slug', $slug, $taxonomy );

			return $term;
		}

		return get_term( $term_id, $taxonomy );
	}

	public static function get_theme_option_post( $name, $post_type = 'any', $base = 'general', $slug = '' ) {
		$id = self::get_theme_option( $name, '', $base );

		if ( ! HT()->is_positive_number( $id ) ) {
			if ( ! empty( $slug ) ) {
				$args  = array(
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

	private function post_type_labels( $name, $singular_name, $menu_name ) {
		$labels = array(
			'name'                  => $name,
			'singular_name'         => $singular_name,
			'menu_name'             => $menu_name,
			'add_new'               => _x( 'Add New', 'custom post type', 'hocwp-theme' ),
			'add_new_item'          => sprintf( _x( 'Add New %s', 'cutom-post-type', 'hocwp-theme' ), $singular_name ),
			'edit_item'             => sprintf( _x( 'Edit %s', 'custom post type', 'hocwp-theme' ), $singular_name ),
			'new_item'              => sprintf( _x( 'New %s', 'custom post type', 'hocwp-theme' ), $singular_name ),
			'view_item'             => sprintf( _x( 'View %s', 'custom post type', 'hocwp-theme' ), $singular_name ),
			'view_items'            => sprintf( _x( 'View %s', 'custom post type', 'hocwp-theme' ), $name ),
			'search_items'          => sprintf( _x( 'Search %s', 'custom post type', 'hocwp-theme' ), $name ),
			'not_found'             => sprintf( _x( 'No %s found.', 'custom post type', 'hocwp-theme' ), $name ),
			'not_found_in_trash'    => sprintf( _x( 'No %s found in Trash.', 'custom post type', 'hocwp-theme' ), $name ),
			'parent_item_colon'     => sprintf( _x( 'Parent %s:', 'custom post type', 'hocwp-theme' ), $singular_name ),
			'all_items'             => sprintf( _x( 'All %s', 'custom post type', 'hocwp-theme' ), $name ),
			'archives'              => sprintf( _x( '%s Archives', 'custom post type', 'hocwp-theme' ), $singular_name ),
			'attributes'            => sprintf( _x( '%s Attributes', 'custom post type', 'hocwp-theme' ), $singular_name ),
			'insert_into_item'      => sprintf( _x( 'Insert into %s', 'custom post type', 'hocwp-theme' ), $singular_name ),
			'uploaded_to_this_item' => sprintf( _x( 'Uploaded to this %s', 'custom post type', 'hocwp-theme' ), $singular_name ),
			'featured_image'        => _x( 'Featured Image', 'custom post type', 'hocwp-theme' ),
			'set_featured_image'    => _x( 'Set featured image', 'custom post type', 'hocwp-theme' ),
			'remove_featured_image' => _x( 'Remove featured image', 'custom post type', 'hocwp-theme' ),
			'use_featured_image'    => _x( 'Use as featured image', 'custom post type', 'hocwp-theme' ),
			'filter_items_list'     => sprintf( _x( 'Filter %s list', 'custom post type', 'hocwp-theme' ), $name ),
			'items_list_navigation' => sprintf( _x( '%s list navigation', 'custom post type', 'hocwp-theme' ), $name ),
			'items_list'            => sprintf( _x( '%s list', 'custom post type', 'hocwp-theme' ), $name )
		);

		return $labels;
	}

	private function taxonomy_labels( $name, $singular_name, $menu_name ) {
		$labels = array(
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

		return $labels;
	}

	private function post_type_or_taxonomy_defaults( $args, $post_type = true ) {
		$args          = HT_Sanitize()->post_type_or_taxonomy_args( $args );
		$name          = $args['name'];
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

		$private = isset( $args['private'] ) ? $args['private'] : false;

		if ( ! $private && isset( $args['public'] ) ) {
			$private = ! ( $args['public'] );
		}

		if ( $private ) {
			$defaults['public']              = false;
			$defaults['show_ui']             = true;
			$defaults['public']              = false;
			$defaults['exclude_from_search'] = true;
			$defaults['show_in_nav_menus']   = false;
			$defaults['show_in_admin_bar']   = false;
			$defaults['menu_position']       = 9999999;
			$defaults['has_archive']         = false;
			$defaults['query_var']           = false;
			$defaults['rewrite']             = false;
			$defaults['feeds']               = false;

			if ( ! $post_type ) {
				$args['show_in_quick_edit'] = false;
				$args['show_admin_column']  = false;
				$args['show_tagcloud']      = false;
			}
		}

		unset( $args['labels'], $args['name'], $args['singular_name'], $args['menu_name'], $args['private'] );

		$args = wp_parse_args( $args, $defaults );

		$slug = isset( $args['rewrite']['slug'] ) ? $args['rewrite']['slug'] : '';

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
			the_title( sprintf( '<a href="%s" rel="bookmark">', esc_url( $permalink ) ), '</a>' );
		} else {
			$title = sprintf( '<a href="%s" rel="bookmark">', esc_url( $permalink ) ) . $title . '</a>';
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

	public function get_google_drive_file_url( $url, $api_key = '' ) {
		if ( empty( $api_key ) ) {
			$api_key = $this->get_theme_option( 'google_api_key', '', 'social' );
		}

		if ( ! empty( $api_key ) ) {
			$url = esc_url_raw( $url );

			$domain = HT()->get_domain_name( $url, true );

			if ( 'google.com' != $domain ) {
				return $url;
			}

			$parts = parse_url( $url );
			parse_str( $parts['query'], $query );
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

	public function load_google_javascript_sdk( $args = array() ) {
		global $hocwp_theme;
		$options = $hocwp_theme->options;
		$load    = isset( $args['load'] ) ? (bool) $args['load'] : false;
		$load    = apply_filters( 'hocwp_theme_load_google_sdk_javascript', $load );
		if ( ! $load ) {
			return;
		}
		$callback = isset( $args['callback'] ) ? $args['callback'] : '';
		if ( empty( $callback ) ) {
			return;
		}
		$locale = get_user_locale();
		if ( 'vi' == $locale ) {
			$locale = 'vi_VN';
		}
		?>
		<script>
			(function (d, s, id) {
				var js, gjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) {
					return;
				}
				js = d.createElement(s);
				js.id = id;
				js.async = "async";
				js.defer = "defer";
				js.src = "https://apis.google.com/js/api.js?language=<?php echo $locale; ?>";
				js.setAttribute("onload", "this.onload=function(){};<?php echo $callback; ?>()");
				js.setAttribute("onreadystatechange", "if (this.readyState === 'complete') this.onload()");
				gjs.parentNode.insertBefore(js, gjs);
			}(document, 'script', 'google-jssdk'));
		</script>
		<?php
	}

	public function load_facebook_javascript_sdk( $args = array() ) {
		$options = $this->get_theme_options( 'social' );
		$load    = isset( $args['load'] ) ? (bool) $args['load'] : false;
		$load    = apply_filters( 'hocwp_theme_load_facebook_sdk_javascript', $load );
		if ( $load ) {
			$sdk = isset( $options['facebook_sdk_javascript'] ) ? $options['facebook_sdk_javascript'] : '';
			if ( empty( $sdk ) ) {
				$app_id = isset( $options['facebook_app_id'] ) ? $options['facebook_app_id'] : '';
				if ( empty( $app_id ) ) {
					return;
				}
				$locale = get_user_locale();
				if ( 'vi' == $locale ) {
					$locale = 'vi_VN';
				}
				$version = isset( $args['version'] ) ? $args['version'] : '2.11';
				$version = trim( $version, 'v' );
				?>
				<div id="fb-root"></div>
				<script>(function (d, s, id) {
						var js, fjs = d.getElementsByTagName(s)[0];
						if (d.getElementById(id)) return;
						js = d.createElement(s);
						js.id = id;
						js.src = 'https://connect.facebook.net/<?php echo $locale; ?>/sdk.js#xfbml=1&version=v<?php echo $version; ?>&appId=<?php echo $app_id; ?>';
						fjs.parentNode.insertBefore(js, fjs);
					}(document, 'script', 'facebook-jssdk'));</script>
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
			switch ( $key ) {
				case 'share_count':
				case 'comment_count':
					$res = isset( $res['share'][ $key ] ) ? $res['share'][ $key ] : '';
					break;
				default:
					$res = isset( $res['og_object']['likes']['summary']['total_count'] ) ? $res['og_object']['likes']['summary']['total_count'] : '';
			}
		}

		return $res;
	}

	public function delete_transient( $transient_name = '' ) {
		global $wpdb;

		$query_root = "DELETE FROM $wpdb->options";
		$query_root .= " WHERE option_name like %s";
		$key_1 = '_transient_';
		$key_2 = '_transient_timeout_';
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

	public function display_ads( $args ) {
		if ( function_exists( 'hocwp_ext_ads_display' ) ) {
			hocwp_ext_ads_display( $args );
		}
	}

	public function enqueue_media() {
		wp_enqueue_media();
		wp_enqueue_script( 'hocwp-theme-media-upload' );
		wp_enqueue_style( 'hocwp-theme-media-upload-style' );
	}

	public function enqueue_sortable() {
		wp_enqueue_style( 'hocwp-theme-sortable-style' );
		wp_enqueue_script( 'hocwp-theme-sortable' );
	}

	public function enqueue_jquery_ui_style() {
		wp_enqueue_style( 'jquery-ui-style', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css' );
	}

	public function enqueue_datepicker() {
		$this->enqueue_jquery_ui_style();
		wp_enqueue_script( 'hocwp-theme-datepicker' );
	}

	public function enqueue_datetime_picker() {
		$this->enqueue_datepicker();
	}

	public function enqueue_color_picker() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'hocwp-theme-color-picker' );
	}

	public function enqueue_chosen() {
		wp_enqueue_style( 'chosen-style' );
		wp_enqueue_script( 'chosen-select' );
	}

	public function enqueue_ajax_overlay() {
		wp_enqueue_style( 'hocwp-theme-ajax-overlay-style' );
		wp_enqueue_script( 'hocwp-theme-ajax-button' );
	}

	public function enqueue_code_editor() {
		wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
		wp_enqueue_script( 'hocwp-theme-code-editor' );
	}

	public function get_theme_options( $tab ) {
		global $hocwp_theme;
		$options = isset( $hocwp_theme->options[ $tab ] ) ? $hocwp_theme->options[ $tab ] : '';
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		return $options;
	}

	public function recaptcha() {
		$options  = $this->get_theme_options( 'social' );
		$site_key = isset( $options['recaptcha_site_key'] ) ? $options['recaptcha_site_key'] : '';

		if ( empty( $site_key ) ) {
			return;
		}
		?>
		<script>
			(function (d, s, id) {
				var js, gjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) {
					return;
				}
				js = d.createElement(s);
				js.id = id;
				js.async = "async";
				js.defer = "defer";
				js.src = "https://www.google.com/recaptcha/api.js?hl=<?php echo get_locale(); ?>";
				gjs.parentNode.insertBefore(js, gjs);
			}(document, 'script', 'recaptcha-jssdk'));
		</script>
		<div class="g-recaptcha" data-sitekey="<?php echo $site_key; ?>" style="margin-bottom: 10px;"></div>
		<?php
	}

	public function recaptcha_valid( $response = null ) {
		if ( null == $response ) {
			$response = isset( $_POST['g-recaptcha-response'] ) ? $_POST['g-recaptcha-response'] : '';
		}

		$options    = $this->get_theme_options( 'social' );
		$secret_key = isset( $options['recaptcha_secret_key'] ) ? $options['recaptcha_secret_key'] : '';

		if ( empty( $secret_key ) ) {
			return false;
		}

		$url = 'https://www.google.com/recaptcha/api/siteverify';

		$params = array(
			'secret'   => $secret_key,
			'response' => $response
		);

		$url      = add_query_arg( $params, $url );
		$response = HT_Util()->get_contents( $url );
		$response = json_decode( $response );

		if ( $this->is_object_valid( $response ) ) {
			if ( $response->success ) {
				return true;
			}
		}

		return false;
	}

	public function get_admin_colors( $color = '' ) {
		global $_wp_admin_css_colors;

		$colors = $_wp_admin_css_colors;

		if ( ! empty( $color ) ) {
			$color = isset( $colors[ $color ] ) ? $colors[ $color ] : '';
		}

		return $color;
	}

	public function is_plugin_active( $plugin ) {
		if ( false !== strpos( $plugin, '.php' ) ) {
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

						if ( isset( $data['Name'] ) && ! empty( $data['Name'] ) ) {
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

							if ( isset( $data['Name'] ) && ! empty( $data['Name'] ) ) {
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

	public static function pagination( $args = array() ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.3.9', 'HT_Frontend()->' . __FUNCTION__ . '()' );
		HT_Frontend()->pagination( $args );
	}

	public function get_archive_title( $prefix = true ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.3.9', 'HT_Frontend()->' . __FUNCTION__ . '()' );

		return HT_Frontend()->get_archive_title( $prefix );
	}

	public static function breadcrumb( $args = array() ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.3.9', 'HT_Frontend()->' . __FUNCTION__ . '()' );
		HT_Frontend()->breadcrumb( $args );
	}

	public function facebook_share_button( $args = array() ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.3.9', 'HT_Frontend()->' . __FUNCTION__ . '()' );
		HT_Frontend()->facebook_share_button( $args );
	}

	public function addthis_toolbox( $args = array() ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.3.9', 'HT_Frontend()->' . __FUNCTION__ . '()' );
		HT_Frontend()->addthis_toolbox( $args );
	}

	public function back_to_top_button() {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.3.9', 'HT_Frontend()->' . __FUNCTION__ . '()' );
		HT_Frontend()->back_to_top_button();
	}

	public function is_admin_page( $pages, $admin_page = '' ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.3.9', 'HT_Admin()->' . __FUNCTION__ . '()' );

		return HT_Admin()->is_admin_page( $pages, $admin_page );
	}

	public function is_post_new_update_page() {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.3.9', 'HT_Admin()->' . __FUNCTION__ . '()' );

		return HT_Admin()->is_post_new_update_page();
	}

	public function is_edit_post_new_update_page() {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.3.9', 'HT_Admin()->' . __FUNCTION__ . '()' );

		return HT_Admin()->is_edit_post_new_update_page();
	}

	public function get_current_post_type() {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.3.9', 'HT_Admin()->' . __FUNCTION__ . '()' );

		return HT_Admin()->get_current_post_type();
	}

	public function get_current_new_post() {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '6.3.9', 'HT_Admin()->' . __FUNCTION__ . '()' );

		return HT_Admin()->get_current_new_post();
	}
}

function HT_Util() {
	return HOCWP_Theme_Utility::instance();
}