<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

final class HOCWP_Theme_Minify {
	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {
	}

	public static function build_css_rule( $elements, $properties ) {
		$elements   = (array) $elements;
		$properties = (array) $properties;
		$before     = '';
		foreach ( $elements as $element ) {
			if ( empty( $element ) ) {
				continue;
			}
			$first_char = substr( $element, 0, 1 );
			if ( '.' !== $first_char && ! HT()->string_contain( $element, '.' ) ) {
				$element = '.' . $element;
			}
			$before .= $element . ',';
		}
		$before = trim( $before, ',' );
		$after  = '';
		foreach ( $properties as $key => $property ) {
			if ( empty( $key ) ) {
				continue;
			}
			$after .= $key . ':' . $property . ';';
		}
		$after = trim( $after, ';' );

		return $before . '{' . $after . '}';
	}

	public static function shorten_hex_css( $content ) {
		$content = preg_replace( '/(?<![\'"])#([0-9a-z])\\1([0-9a-z])\\2([0-9a-z])\\3(?![\'"])/i', '#$1$2$3', $content );

		return $content;
	}

	public static function shorten_zero_css( $content ) {
		$before  = '(?<=[:(, ])';
		$after   = '(?=[ ,);}])';
		$units   = '(em|ex|%|px|cm|mm|in|pt|pc|ch|rem|vh|vw|vmin|vmax|vm)';
		$content = preg_replace( '/' . $before . '(-?0*(\.0+)?)(?<=0)' . $units . $after . '/', '\\1', $content );
		$content = preg_replace( '/' . $before . '\.0+' . $after . '/', '0', $content );
		$content = preg_replace( '/' . $before . '(-?[0-9]+)\.0+' . $units . '?' . $after . '/', '\\1\\2', $content );
		$content = preg_replace( '/' . $before . '-?0+' . $after . '/', '0', $content );

		return $content;
	}

	public static function strip_white_space_css( $content ) {
		$content = preg_replace( '/^\s*/m', '', $content );
		$content = preg_replace( '/\s*$/m', '', $content );
		$content = preg_replace( '/\s+/', ' ', $content );
		$content = preg_replace( '/\s*([\*$~^|]?+=|[{};,>~]|!important\b)\s*/', '$1', $content );
		$content = preg_replace( '/([\[(:])\s+/', '$1', $content );
		$content = preg_replace( '/\s+([\]\)])/', '$1', $content );
		$content = preg_replace( '/\s+(:)(?![^\}]*\{)/', '$1', $content );
		$content = preg_replace( '/\s*([+-])\s*(?=[^}]*{)/', '$1', $content );
		$content = preg_replace( '/;}/', '}', $content );

		return trim( $content );
	}

	public static function css( $css_content, $online = false ) {
		if ( $online ) {
			$buffer = self::get_minified( 'https://cssminifier.com/raw', $css_content );
		} else {
			if ( HT()->is_file( $css_content ) ) {
				$filesystem  = HOCWP_Theme_Utility::filesystem();
				$css_content = $filesystem->get_contents( $css_content );
			}
			$buffer = $css_content;
			$buffer = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer );
			$buffer = str_replace( ': ', ':', $buffer );
			$buffer = str_replace( array( "\r\n", "\r", "\n", "\t", '  ', '    ', '    ' ), '', $buffer );
			$buffer = self::shorten_hex_css( $buffer );
			$buffer = self::shorten_zero_css( $buffer );
			$buffer = self::strip_white_space_css( $buffer );
		}

		return $buffer;
	}

	public static function js( $js ) {
		return self::get_minified( 'https://javascript-minifier.com/raw', $js );
	}

	public static function get_minified( $url, $content ) {
		if ( ! empty( $content ) ) {
			$filesystem = HOCWP_Theme_Utility::filesystem();

			if ( HT()->is_file( $content ) ) {
				$content = $filesystem->get_contents( $content );
			}

			$params = array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array(),
				'body'        => array( 'input' => $content ),
				'cookies'     => array()
			);

			$resp   = wp_remote_post( $url, $params );

			if ( ! is_wp_error( $resp ) ) {
				$content = wp_remote_retrieve_body( $resp );
			}
		}

		return $content;
	}

	public function is_error_content( $content ) {
		if ( ! empty( $content ) ) {
			if ( HT()->string_contain( $content, '<html>' ) || HT()->string_contain( $content, 'ESCAPED_SOURCE' ) ) {
				return new WP_Error( 'invalid_minified_content', $content );
			}
		}

		return false;
	}

	public static function generate( $file, $recompress = false ) {
		if ( ! HT()->is_file( $file ) || ! _hocwp_theme_is_css_or_js_file( $file ) ) {
			return;
		}

		$info = pathinfo( $file );

		if ( isset( $info['extension'] ) ) {
			$dir  = dirname( $file );
			$name = $info['filename'];
			$se   = substr( $name, - 4 );

			if ( '.min' != $se ) {
				$name .= '.min';
			} elseif ( ! $recompress ) {
				return;
			}

			$name .= '.' . $info['extension'];
			$min_file = $dir . '/' . $name;
			$minified = '';

			if ( 'js' == $info['extension'] ) {
				$minified = self::js( $file );
			} elseif ( 'css' == $info['extension'] ) {
				$minified = self::css( $file, true );
			} else {
				return;
			}

			if ( $error = HT_Minify()->is_error_content( $minified ) ) {
				if ( $error instanceof WP_Error ) {
					hocwp_theme_debug( $error->get_error_message() );
				}
			} else {
				$filesystem = HOCWP_Theme_Utility::filesystem();
				if ( ! $filesystem->put_contents( $min_file, $minified, FS_CHMOD_FILE ) ) {
					hocwp_theme_debug( __( 'File can not be compressed!', 'hocwp-theme' ) );
				}
			}
		}
	}
}

function HT_Minify() {
	return HOCWP_Theme_Minify::instance();
}