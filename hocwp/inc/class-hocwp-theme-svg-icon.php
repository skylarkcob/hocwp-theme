<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

final class HOCWP_Theme_SVG_Icon {
	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {
	}

	public static function build( $path_d, $atts = array() ) {
		$defaults        = array(
			'xmlns' => 'http://www.w3.org/2000/svg'
		);
		$svg             = new HOCWP_Theme_HTML_Tag( 'svg' );
		$atts            = wp_parse_args( $atts, $defaults );
		$viewbox         = isset( $atts['viewBox'] ) ? $atts['viewBox'] : ( isset( $atts['viewbox'] ) ? $atts['viewbox'] : '' );
		$width           = isset( $atts['width'] ) ? $atts['width'] : '';
		$height          = isset( $atts['height'] ) ? $atts['height'] : '';
		$viewbox         = self::sanitize_viewbox( $viewbox, $width, $height );
		$atts['viewBox'] = $viewbox;
		unset( $atts['viewbox'], $atts['width'], $atts['height'] );
		$class         = isset( $atts['class'] ) ? $atts['class'] : '';
		$atts['class'] = HOCWP_Theme_Sanitize::html_class( $class, 'svg-icon' );
		$svg->set_attributes( $atts );
		if ( empty( $path_d ) ) {
			$path_d = isset( $atts['d'] ) ? $atts['d'] : '';
		}
		$path_d = (array) $path_d;
		$text   = '';
		foreach ( $path_d as $d ) {
			$path = new HOCWP_Theme_HTML_Tag( 'path' );
			$path->add_attribute( 'd', $d );
			$text .= $path->build();
		}
		$path = new HOCWP_Theme_HTML_Tag( 'path' );
		$path->add_attribute( 'd', $path_d );
		$svg->set_text( $text );
		$svg->output();
		unset( $svg, $path, $defaults, $class, $d, $text );
	}

	private static function sanitize_viewbox( $viewbox, $width = null, $height = null ) {
		if ( ! is_array( $viewbox ) ) {
			$viewbox = explode( ' ', $viewbox );
		}
		if ( count( $viewbox ) != 4 ) {
			$viewbox = array( 0, 0, 1972, 1972 );
		}
		if ( HOCWP_Theme::is_positive_number( $width ) ) {
			$viewbox[2] = $width;
		}
		if ( HOCWP_Theme::is_positive_number( $height ) ) {
			$viewbox[3] = $height;
		}
		$viewbox = array_map( 'intval', $viewbox );

		return implode( ' ', $viewbox );
	}

	private static function helper( $name, $d, $atts = array() ) {
		if ( ! HT()->string_contain( $name, 'icon' ) ) {
			$name = 'icon-' . str_replace( '_', '-', $name );
		}
		$class         = isset( $atts['class'] ) ? $atts['class'] : '';
		$atts['class'] = HOCWP_Theme_Sanitize::html_class( $class, $name );
		self::build( $d, $atts );
		unset( $class );
	}

	public static function search( $atts = array() ) {
		$d        = 'M12.86 11.32L18 16.5 16.5 18l-5.18-5.14v-.35a7 7 0 1 1 1.19-1.19h.35zM7 12A5 5 0 1 0 7 2a5 5 0 0 0 0 10z';
		$defaults = array(
			'width'  => 18,
			'height' => 18
		);
		$atts     = wp_parse_args( $atts, $defaults );
		self::helper( __FUNCTION__, $d, $atts );
		unset( $d, $defaults );
	}

	public static function close( $atts = array() ) {
		$d        = 'M19 6.41l-1.41-1.41-5.59 5.59-5.59-5.59-1.41 1.41 5.59 5.59-5.59 5.59 1.41 1.41 5.59-5.59 5.59 5.59 1.41-1.41-5.59-5.59z';
		$defaults = array(
			'width'  => 24,
			'height' => 24
		);
		$atts     = wp_parse_args( $atts, $defaults );
		self::helper( __FUNCTION__, $d, $atts );
		unset( $d, $defaults );
	}

	public static function filters( $atts = array() ) {
		$d        = array(
			'M4.5 9.5c-2 0-4-1.7-4-4 0-2 1.8-3.8 4-3.8 2 0 4 1.7 4 4s-2 3.8-4 3.8zm0-5.8c-1 0-2 .8-2 2 0 1 1 1.8 2 1.8s2-.8 2-2c0-1-1-1.8-2-1.8zm8.8 13.6c-2 0-4-1.7-4-4 0-2 1.8-3.8 4-3.8 2 0 4 1.7 4 4 0 2-2 3.8-4 3.8zm0-5.8c-1 0-2 .8-2 2 0 1 1 1.8 2 1.8s2-.8 2-2c0-1-1-1.8-2-1.8zm9.4 14c-2 0-4-1.7-4-4 0-2 1.8-3.8 4-3.8 2 0 4 1.7 4 4s-1.8 3.8-4 3.8zm0-5.8c-1 0-2 .8-2 2 0 1 1 1.8 2 1.8s2-.8 2-2c0-1-1-1.8-2-1.8z',
			'M4.5 25.5c-.6 0-1-.4-1-1v-16c0-.6.4-1 1-1s1 .4 1 1v16c0 .6-.4 1-1 1zm8.8 0c-.6 0-1-.4-1-1v-7.2c0-.6.4-1 1-1s1 .4 1 1v7.2c0 .6-.5 1-1 1zm0-14.5c-.6 0-1-.4-1-1V2.7c0-.6.4-1 1-1s1 .4 1 1V10c0 .5-.5 1-1 1zm9.4 8.7c-.6 0-1-.4-1-1v-16c0-.6.4-1 1-1s1 .4 1 1v16c0 .6-.4 1-1 1z'
		);
		$defaults = array(
			'width'  => 27,
			'height' => 27
		);
		$atts     = wp_parse_args( $atts, $defaults );
		self::helper( __FUNCTION__, $d, $atts );
		unset( $d, $defaults );
	}

	public static function bars( $atts = array() ) {
		$d        = 'M27.429 24v2.286q0 0.464-0.339 0.804t-0.804 0.339h-25.143q-0.464 0-0.804-0.339t-0.339-0.804v-2.286q0-0.464 0.339-0.804t0.804-0.339h25.143q0.464 0 0.804 0.339t0.339 0.804zM27.429 14.857v2.286q0 0.464-0.339 0.804t-0.804 0.339h-25.143q-0.464 0-0.804-0.339t-0.339-0.804v-2.286q0-0.464 0.339-0.804t0.804-0.339h25.143q0.464 0 0.804 0.339t0.339 0.804zM27.429 5.714v2.286q0 0.464-0.339 0.804t-0.804 0.339h-25.143q-0.464 0-0.804-0.339t-0.339-0.804v-2.286q0-0.464 0.339-0.804t0.804-0.339h25.143q0.464 0 0.804 0.339t0.339 0.804z';
		$defaults = array(
			'width'  => 27,
			'height' => 32
		);
		$atts     = wp_parse_args( $atts, $defaults );
		self::helper( __FUNCTION__, $d, $atts );
		unset( $d, $defaults );
	}

	public static function sign_in( $atts = array() ) {
		$d = 'M1312 896q0 26-19 45l-544 544q-19 19-45 19t-45-19-19-45v-288h-448q-26 0-45-19t-19-45v-384q0-26 19-45t45-19h448v-288q0-26 19-45t45-19 45 19l544 544q19 19 19 45zm352-352v704q0 119-84.5 203.5t-203.5 84.5h-320q-13 0-22.5-9.5t-9.5-22.5q0-4-1-20t-.5-26.5 3-23.5 10-19.5 20.5-6.5h320q66 0 113-47t47-113v-704q0-66-47-113t-113-47h-312l-11.5-1-11.5-3-8-5.5-7-9-2-13.5q0-4-1-20t-.5-26.5 3-23.5 10-19.5 20.5-6.5h320q119 0 203.5 84.5t84.5 203.5z';
		self::helper( __FUNCTION__, $d, $atts );
		unset( $d );
	}

	public static function sign_out( $atts = array() ) {
		$d = 'M704 1440q0 4 1 20t.5 26.5-3 23.5-10 19.5-20.5 6.5h-320q-119 0-203.5-84.5t-84.5-203.5v-704q0-119 84.5-203.5t203.5-84.5h320q13 0 22.5 9.5t9.5 22.5q0 4 1 20t.5 26.5-3 23.5-10 19.5-20.5 6.5h-320q-66 0-113 47t-47 113v704q0 66 47 113t113 47h312l11.5 1 11.5 3 8 5.5 7 9 2 13.5zm928-544q0 26-19 45l-544 544q-19 19-45 19t-45-19-19-45v-288h-448q-26 0-45-19t-19-45v-384q0-26 19-45t45-19h448v-288q0-26 19-45t45-19 45 19l544 544q19 19 19 45z';
		self::helper( __FUNCTION__, $d, $atts );
		unset( $d );
	}

	public static function arrow_left( $atts = array() ) {
		$d        = 'M42.311 14.044c-0.178-0.178-0.533-0.356-0.711-0.356h-33.778l10.311-10.489c0.178-0.178 0.356-0.533 0.356-0.711 0-0.356-0.178-0.533-0.356-0.711l-1.6-1.422c-0.356-0.178-0.533-0.356-0.889-0.356s-0.533 0.178-0.711 0.356l-14.578 14.933c-0.178 0.178-0.356 0.533-0.356 0.711s0.178 0.533 0.356 0.711l14.756 14.933c0 0.178 0.356 0.356 0.533 0.356s0.533-0.178 0.711-0.356l1.6-1.6c0.178-0.178 0.356-0.533 0.356-0.711s-0.178-0.533-0.356-0.711l-10.311-10.489h33.778c0.178 0 0.533-0.178 0.711-0.356 0.356-0.178 0.533-0.356 0.533-0.711v-2.133c0-0.356-0.178-0.711-0.356-0.889z';
		$defaults = array(
			'width'  => 43,
			'height' => 32
		);
		$atts     = wp_parse_args( $atts, $defaults );
		self::helper( __FUNCTION__, $d, $atts );
		unset( $d, $defaults );
	}

	public static function arrow_right( $atts = array() ) {
		$d        = 'M0.356 17.956c0.178 0.178 0.533 0.356 0.711 0.356h33.778l-10.311 10.489c-0.178 0.178-0.356 0.533-0.356 0.711 0 0.356 0.178 0.533 0.356 0.711l1.6 1.6c0.178 0.178 0.533 0.356 0.711 0.356s0.533-0.178 0.711-0.356l14.756-14.933c0.178-0.356 0.356-0.711 0.356-0.889s-0.178-0.533-0.356-0.711l-14.756-14.933c0-0.178-0.356-0.356-0.533-0.356s-0.533 0.178-0.711 0.356l-1.6 1.6c-0.178 0.178-0.356 0.533-0.356 0.711s0.178 0.533 0.356 0.711l10.311 10.489h-33.778c-0.178 0-0.533 0.178-0.711 0.356-0.356 0.178-0.533 0.356-0.533 0.711v2.311c0 0.178 0.178 0.533 0.356 0.711z';
		$defaults = array(
			'width'  => 43,
			'height' => 32
		);
		$atts     = wp_parse_args( $atts, $defaults );
		self::helper( __FUNCTION__, $d, $atts );
		unset( $d, $defaults );
	}
}

function HT_SVG_Icon() {
	return HOCWP_Theme_SVG_Icon::instance();
}