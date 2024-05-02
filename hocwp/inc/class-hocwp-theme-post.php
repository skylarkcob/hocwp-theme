<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Post extends Abstract_HOCWP_Theme_Object {
	public $post;

	public function __construct( $post = null ) {
		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		} elseif ( ! ( $post instanceof WP_Post ) ) {
			$post = HT_Util()->return_post( $post );
		}

		$this->set( $post );
	}

	/*
	 * Get list array meta keys.
	 *
	 * @return array The meta keys array.
	 */
	public function get_meta_keys() {
		$this->meta_keys = apply_filters( 'hocwp_theme_post_meta_keys', $this->meta_keys, $this );

		return $this->meta_keys;
	}

	/**
	 * Get post ID.
	 *
	 * @return int Post ID.
	 */
	public function get_id() {
		return $this->post->ID;
	}

	/**
	 * Get post object.
	 *
	 * @return WP_Post Post object.
	 */
	public function get() {
		return $this->post;
	}

	/**
	 * Set post object.
	 *
	 * @param WP_Post $object The post object.
	 */
	public function set( $object ) {
		if ( $object instanceof WP_Post ) {
			$this->post = $object;

			// Set meta keys as post object properties.
			if ( HT()->array_has_value( $this->get_meta_keys() ) ) {
				foreach ( $this->get_meta_keys() as $key ) {
					$this->post->{$key} = $this->get_meta( $key );
				}
			}
		}
	}

	/**
	 * Check if is a post.
	 *
	 * @return bool True if is post else false.
	 */
	public function is( $post_type_or_taxonomy = null ) {
		$result = ( $this->get() instanceof WP_Post );

		if ( $result && ! empty( $post_type_or_taxonomy ) ) {
			$result = ( $post_type_or_taxonomy == $this->get()->post_type );
		}

		return $result;
	}

	public function amp_enabled() {
		$status = $this->get_meta( 'amp_status' );

		return ( 'disabled' != $status || 1 == $status );
	}

	/**
	 * Get post meta value.
	 *
	 * @param string $key The meta key.
	 * @param bool|true $single Return single item or array items.
	 *
	 * @return mixed The meta value.
	 */
	public function get_meta( $key, $single = true ) {
		return get_post_meta( $this->get_id(), $key, $single );
	}

	public function get_terms( $taxonomy = 'post_tag', $args = array() ) {
		return wp_get_object_terms( $this->get_id(), $taxonomy, $args );
	}

	public function get_ancestor_terms( $taxonomy = 'category', $output = OBJECT ) {
		$result = null;

		if ( is_taxonomy_hierarchical( $taxonomy ) && has_term( '', $taxonomy, $this->post->ID ) ) {
			$result = get_ancestors( $this->post->ID, $taxonomy, 'taxonomy' );

			if ( ! HT()->array_has_value( $result ) ) {
				$result = wp_get_post_terms( $this->post->ID, $taxonomy, array(
					'orderby' => 'parent',
					'fields'  => 'ids'
				) );
			}
		}

		if ( HT()->array_has_value( $result ) && OBJECT == $output ) {
			foreach ( $result as $key => $term_id ) {
				$result[ $key ] = get_term( $term_id, $taxonomy );
			}
		}

		return $result;
	}

	public function thumbnail( $size = 'thumbnail', $attr = '' ) {
		hocwp_theme_post_thumbnail_html( $size, $attr );
	}

	public function the_date( $format = '' ) {
		echo get_the_date( $format );
	}

	public function the_date_time( $format = '', $gmt = false ) {
		$ts = get_the_time( 'G', $this->get() );

		$timezone_string = get_option( 'timezone_string' );

		if ( empty( $timezone_string ) ) {
			$timezone_string = wp_timezone_string();
			$timezone_string = 'GMT' . $timezone_string;
		} elseif ( 'Asia/Ho_Chi_Minh' == $timezone_string ) {
			$timezone_string = 'GMT+7';
		}

		if ( empty( $format ) ) {
			$format = 'd/m/Y H:i';
		}

		$date = date( $format, $ts );

		if ( $gmt ) {
			$date .= ' ' . $timezone_string;
		}
		?>
        <time class="entry-date published updated"
              datetime="<?php echo esc_attr( date( 'c', $ts ) ); ?>"><?php echo $date; ?></time>
		<?php
	}

	/**
	 * Get post star ratings base on plugin kk Star Ratings.
	 *
	 * @return string
	 */
	public function get_ratings() {
		if ( function_exists( 'kk_star_ratings' ) ) {
			$avg = $this->get_meta( '_kksr_avg' );

			if ( empty( $avg ) ) {
				return '';
			}

			$stars = get_option( 'kksr_stars' );

			if ( 5 != $stars ) {
				$avg = ( $avg * $stars ) / 5;
			}

			$total = $this->get_meta( '_kksr_casts' );
			$total = sprintf( _x( '%s ratings', 'total ratings', 'hocwp-theme' ), '<span class="blg-hits-total-rating total">' . $total . '</span>' );

			return '<span>' . sprintf( '<span class="blg-hits-rating avg">%s</span>/%s (%s)', $avg, $stars, $total ) . '</span>';
		}

		return '';
	}

	public function human_time_diff( $ago = false, $modified = false, $gmt = false ) {
		if ( $this->post instanceof WP_Post ) {
			if ( $gmt ) {
				if ( $modified ) {
					$timestamp = strtotime( $this->post->post_modified_gmt );
				} else {
					$timestamp = strtotime( $this->post->post_date_gmt );
				}
			} else {
				if ( $modified ) {
					$timestamp = strtotime( $this->post->post_modified );
				} else {
					$timestamp = strtotime( $this->post->post_date );
				}
			}

			$diff = human_time_diff( $timestamp, current_time( 'timestamp' ) );

			if ( $ago ) {
				$diff = sprintf( __( '%s ago', 'hocwp-theme' ), $diff );
			}
			?>
            <time datetime="<?php echo esc_attr( get_the_time( 'c' ) ); ?>"><?php echo $diff; ?></time>
			<?php
		}
	}

	public function get_the_excerpt( $length = null, $more = null ) {
		if ( ! is_numeric( $length ) ) {
			$key = ( wp_is_mobile() ) ? 'excerpt_length_mobile' : 'excerpt_length';

			$length = HT_Options()->get_tab( $key, '', 'reading' );

			if ( ! is_numeric( $length ) ) {
				$length = 55;
			}

			$length = apply_filters( 'excerpt_length', $length );
		}

		$length = intval( $length );

		$excerpt = $this->post->post_excerpt;

		if ( empty( $excerpt ) ) {
			global $post;
			$tmp = $post;

			$post = $this->post;
			setup_postdata( $post );

			$excerpt = get_the_content( '' );

			$excerpt = strip_shortcodes( $excerpt );

			$excerpt = HT_Util()->apply_the_content( $excerpt );
			$excerpt = str_replace( ']]>', ']]&gt;', $excerpt );

			wp_reset_postdata();

			$post = $tmp;
		}

		if ( null == $more ) {
			$more = '&hellip;';
		}

		return wp_trim_words( $excerpt, $length, $more );
	}

	public function get_views() {
		if ( function_exists( 'pvc_get_post_views' ) ) {
			$views = pvc_get_post_views( $this->get_id() );
		} else {
			$views = $this->get_meta( 'views' );
		}

		return absint( $views );
	}

	public function the_excerpt( $length = null, $more = null ) {
		echo apply_filters( 'the_excerpt', $this->get_the_excerpt( $length, $more ) );
	}
}