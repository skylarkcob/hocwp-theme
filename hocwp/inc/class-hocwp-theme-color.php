<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$file_colors = ht_util()->read_all_text( HOCWP_THEME_CORE_PATH . '/inc/colors.txt' );
$file_colors = json_decode( $file_colors, true );

$lists = array();

foreach ( $file_colors as $name => $rgb ) {
	$key = str_replace( ' ', '-', $name );

	$lists[ sanitize_key( $key ) ] = array(
		'name' => $name,
		'rgb'  => $rgb
	);
}

define( 'HOCWP_THEME_COLOR_NAMES', $lists );

class HOCWP_Theme_Color {
	public static $instance;

	protected function __construct() {
	}

	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function random() {
		return ht()->random_color_hex();
	}

	public function is_hex( $color ) {
		return preg_match( '/(^#[0-9A-F]{6}$)|(^#[0-9A-F]{3}$)/i', $color );
	}

	public function is_rgb( $color ) {
		if ( is_array( $color ) ) {
			$count = count( $color );

			if ( 4 == $count || 3 == $count ) {
				foreach ( $color as $key => $number ) {
					if ( ! is_numeric( $number ) ) {
						return false;
					}

					if ( 3 == $key && ( 0 > $number || 1 < $number ) ) {
						return false;
					}
				}

				return true;
			}
		}

		$result = false;

		$color = strtolower( $color );
		$parts = ht()->get_string_between( $color, '(', ')' );
		$parts = explode( ',', $parts );

		if ( ht()->array_has_value( $parts ) ) {
			$sub   = substr( $color, 0, 4 );
			$count = count( $parts );

			if ( 'rgba' === $sub && 4 == $count ) {
				$result = true;
			} else {
				$sub = substr( $color, 0, 3 );

				if ( 'rgb' === $sub && 3 == $count ) {
					$result = true;
				}
			}

			if ( $result ) {
				foreach ( $parts as $rgb ) {
					if ( ! is_numeric( $rgb ) ) {
						$result = false;
						break;
					}
				}
			}
		}

		return $result;
	}

	public function is_name( $color ) {
		$rgb = $this->name_to_rgb( $color );

		if ( ! empty( $rgb ) ) {
			return true;
		}

		return false;
	}

	public function sanitize_hex_color( $color ) {
		if ( empty( $color ) ) {
			return $color;
		}

		if ( is_array( $color ) ) {
			return array_map( array( $this, 'sanitize_hex_color' ), $color );
		}

		$first = substr( $color, 0, 1 );

		if ( '#' !== $first ) {
			$color = '#' . $color;
		}

		return $color;
	}

	public function name_to_rgb( $color ) {
		$colors = HOCWP_THEME_COLOR_NAMES;

		$color = str_replace( ' ', '-', $color );
		$color = sanitize_key( $color );

		if ( isset( $colors[ $color ] ) && is_array( $colors[ $color ] ) ) {
			if ( isset( $colors[ $color ]['name'] ) ) {
				return isset( $colors[ $color ]['rgb'] ) ? $colors[ $color ]['rgb'] : '';
			}

			return $colors[ $color ];
		}

		return '';
	}

	public function is_valid( $color ) {
		$result = false;

		if ( ! empty( $color ) && is_string( $color ) ) {
			if ( $this->is_hex( $color ) || $this->is_rgb( $color ) || $this->is_name( $color ) ) {
				$result = true;
			}
		}

		return $result;
	}

	function sort_colors( $list_colors ) {
		// Define a custom comparison function to sort the colors by their brightness
		$compare = function ( $a, $b ) {
			// Convert the hex color values to RGB values
			list( $r1, $g1, $b1 ) = sscanf( $a, "#%02x%02x%02x" );
			list( $r2, $g2, $b2 ) = sscanf( $b, "#%02x%02x%02x" );

			// Calculate the brightness of each color
			$brightness1 = ( $r1 * 299 + $g1 * 587 + $b1 * 114 ) / 1000;
			$brightness2 = ( $r2 * 299 + $g2 * 587 + $b2 * 114 ) / 1000;

			// Compare the brightness of the colors
			return $brightness2 - $brightness1;
		};

		// Sort the array of colors using the custom comparison function
		usort( $list_colors, $compare );

		return $list_colors;
	}

	public function find_related_color( $color ) {
		$color = ltrim( $color, '#' );

		$related_colors = array();

		for ( $i = 0; $i < 255; $i += 5 ) {
			$r = hexdec( substr( $color, 0, 2 ) );
			$g = hexdec( substr( $color, 2, 2 ) );
			$b = hexdec( substr( $color, 4, 2 ) );

			$new_r = max( 0, min( 255, $r + $i ) );
			$new_g = max( 0, min( 255, $g + $i ) );
			$new_b = max( 0, min( 255, $b + $i ) );

			$new_color = sprintf( "%02x%02x%02x", $new_r, $new_g, $new_b );

			$distance = sqrt( pow( $r - $new_r, 2 ) + pow( $g - $new_g, 2 ) + pow( $b - $new_b, 2 ) );

			$related_colors[] = array( 'color' => $new_color, 'distance' => $distance );
		}

		usort( $related_colors, function ( $a, $b ) {
			return $a['distance'] - $b['distance'];
		} );

		return $related_colors;
	}

	public function hex_to_rgb( $color, $opacity = false ) {
		if ( empty( $color ) ) {
			return null;
		}

		if ( $this->is_rgb( $color ) ) {
			return $color;
		}

		$color = str_replace( '#', '', $color );

		if ( strlen( $color ) == 6 ) {
			$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		} elseif ( strlen( $color ) == 3 ) {
			$hex = array( str_repeat( $color[0], 2 ), str_repeat( $color[1], 2 ), str_repeat( $color[2], 2 ) );
		} else {
			return null;
		}

		$rgb = array_map( 'hexdec', $hex );

		if ( $opacity ) {
			if ( abs( $opacity ) > 1 ) {
				$opacity = 1.0;
			}

			$output = 'rgba(' . implode( ',', $rgb ) . ',' . $opacity . ')';
		} else {
			$output = 'rgb(' . implode( ',', $rgb ) . ')';
		}

		return $output;
	}

	/**
	 * Make color more bright or more dark.
	 *
	 * @param string $hex The color name in hex format, 3 or 6 characters length.
	 * @param int $steps The percent of value you want to adjust.
	 *
	 * @return string
	 */
	public function adjust_brightness( $hex, $steps ) {
		$steps = ( $steps * 255 ) / 100;
		$steps = max( - 255, min( 255, $steps ) );

		$hex = str_replace( '#', '', $hex );

		if ( strlen( $hex ) == 3 ) {
			$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1 ), 2 );
		}

		$color_parts = str_split( $hex, 2 );

		$return = '#';

		foreach ( $color_parts as $color ) {
			$color  = hexdec( $color );
			$color  = max( 0, min( 255, $color + $steps ) );
			$return .= str_pad( dechex( $color ), 2, '0', STR_PAD_LEFT );
		}

		return $return;
	}

	public function is_light( $color ) {
		if ( ! $this->is_hex( $color ) ) {
			return null;
		}

		// Remove the '#' from the beginning of the color string, if present
		$color = ltrim( $color, '#' );

		// Convert the color string to its RGB components
		$r = hexdec( substr( $color, 0, 2 ) );
		$g = hexdec( substr( $color, 2, 2 ) );
		$b = hexdec( substr( $color, 4, 2 ) );

		// Calculate the luminance of the color using the formula: 0.299 * R + 0.587 * G + 0.114 * B
		$luminance = ( 0.299 * $r + 0.587 * $g + 0.114 * $b ) / 255;

		// If the luminance is greater than or equal to 0.5, the color is considered light, otherwise it's considered dark
		return $luminance >= 0.5;
	}
}

function ht_color() {
	return HOCWP_Theme_Color::get_instance();
}