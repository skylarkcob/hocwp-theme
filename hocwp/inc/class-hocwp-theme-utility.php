<?php

class HOCWP_Theme_Utility {
	public static function get_wp_image_url( $name ) {
		return includes_url( 'images/' . $name );
	}

	public static function get_my_image_url( $name ) {
		return HOCWP_THEME_CORE_URL . '/images/' . $name;
	}

	public static function get_custom_image_url( $name ) {
		return get_template_directory_uri() . '/custom/images/' . $name;
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
		$class    = 'notice fade hocwp-theme';
		$class .= ' notice-' . $args['type'];
		if ( $args['dismissible'] ) {
			$class .= ' is-dismissible';
		}
		$message = isset( $args['message'] ) ? $args['message'] : '';
		if ( ! empty( $message ) ) {
			$message         = wpautop( $message );
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
			if ( ! isset( $size['width'] ) ) {
				if ( isset( $size[0] ) ) {
					$size['width'] = $size[0];
				} else {
					$size['width'] = 0;
				}
			}
			if ( ! isset( $size['height'] ) ) {
				if ( isset( $size[1] ) ) {
					$size['height'] = $size[1];
				} else {
					$size['height'] = 0;
				}
			}
			$size[0] = $size['width'];
			$size[1] = $size['height'];

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

	public static function timestamp_to_string( $timestamp, $format = null ) {
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
		if ( isset( $defaults['timezone_string'] ) && ! empty( $defaults['timezone_string'] ) ) {
			$ts = new DateTimeZone( $defaults['timezone_string'] );
			$date->setTimezone( $ts );
		}

		return $date->format( $format );
	}

	public static function verify_nonce( $nonce_action = - 1, $nonce_name = '_wpnonce' ) {
		if ( null != $nonce_action ) {
			$nonce = isset( $_POST[ $nonce_name ] ) ? $_POST[ $nonce_name ] : '';
			if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
				return false;
			}
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

		return true;
	}

	public static function pagination( $args = array() ) {
		$defaults = array(
			'query' => $GLOBALS['wp_query']
		);
		$args     = wp_parse_args( $args, $defaults );
		$query    = $args['query'];
		if ( 2 > $query->max_num_pages ) {
			return;
		}
		$big   = 999999999;
		$pla   = array(
			'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format'  => '?paged=%#%',
			'current' => max( 1, get_query_var( 'paged' ) ),
			'total'   => $query->max_num_pages,
			'type'    => 'array'
		);
		$args  = wp_parse_args( $args, $pla );
		$items = paginate_links( $args );
		if ( HOCWP_Theme::array_has_value( $items ) ) {
			echo '<ul class="pagination">';
			if ( isset( $args['label'] ) ) {
				echo '<li class="label-item">' . $args['label'] . '</li>';
			}
			foreach ( $items as $item ) {
				echo '<li>' . $item . '</li>';
			}
			echo '</ul>';
			$current_total = isset( $args['current_total'] ) ? $args['current_total'] : false;
			if ( $current_total ) {
				if ( ! is_string( $current_total ) ) {
					$current_total = __( 'Page [CURRENT]/[TOTAL]', 'hocwp-theme' );
				}
				$search        = array(
					'[CURRENT]',
					'[TOTAL]'
				);
				$replace       = array(
					self::get_paged(),
					$query->max_num_pages
				);
				$current_total = str_replace( $search, $replace, $current_total );
				?>
				<ul class="pagination current-total">
					<li>
						<a href="javascript:" title=""><?php echo $current_total; ?></a>
					</li>
				</ul>
				<?php
			}
		}
	}

	public static function get_paged() {
		return ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
	}

	public static function html_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {
		add_filter( 'wp_mail_content_type', 'hocwp_theme_wp_mail_content_type_filter', 99 );
		$sent = wp_mail( $to, $subject, $message, $headers, $attachments );
		remove_filter( 'wp_mail_content_type', 'hocwp_theme_wp_mail_content_type_filter', 99 );

		return $sent;
	}
}