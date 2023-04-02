<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$colors = HT_Util()->read_all_text( HOCWP_THEME_CORE_PATH . '/inc/colors.txt' );
$colors = json_decode( $colors, true );

$lists = array();

foreach ( $colors as $name => $rgb ) {
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
		return HT()->random_color_hex();
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
		$parts = HT()->get_string_between( $color, '(', ')' );
		$parts = explode( ',', $parts );

		if ( HT()->array_has_value( $parts ) ) {
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
			$color = hexdec( $color );
			$color = max( 0, min( 255, $color + $steps ) );
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

function HT_Color() {
	return HOCWP_Theme_Color::get_instance();
}