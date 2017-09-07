<?php

class HOCWP_Theme_Utility {
	public static function get_wp_image_url( $name ) {
		return includes_url( 'images/' . $name );
	}

	public static function get_my_image_url( $name ) {
		return HOCWP_THEME_CORE_URL . '/images/' . $name;
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
		}

		return apply_filters( 'hocwp_theme_current_url', $current_url );
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

	public static function wrap_text( $before, $text, $after ) {
		echo $before . $text . $after;
	}

	public static function admin_notice( $args = array() ) {
		if ( ! is_array( $args ) ) {
			$args = array(
				'message' => $args
			);
		}
		$defaults = array(
			'type'        => 'success',
			'dismissible' => true
		);
		$args     = wp_parse_args( $args, $defaults );
		$class    = 'notice fade';
		$class .= ' notice-' . $args['type'];
		if ( $args['dismissible'] ) {
			$class .= ' is-dismissible';
		}
		$message = isset( $args['message'] ) ? $args['message'] : '';
		if ( ! empty( $message ) ) {
			$message = wpautop( $message );
			printf( '<div class="%1$s">%2$s</div>', esc_attr( $class ), $message );
		}
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
		if ( is_array( $size ) || has_image_size( $size ) ) {
			if ( ! isset( $size['width'] ) && isset( $size[0] ) ) {
				$size['width'] = $size[0];
			}
			if ( ! isset( $size['height'] ) && isset( $size[1] ) ) {
				$size['height'] = $size[1];
			}

			return $size;
		}
		$sizes = self::get_image_sizes();
		if ( isset( $sizes[ $size ] ) ) {
			return $sizes[ $size ];
		}

		return false;
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
}