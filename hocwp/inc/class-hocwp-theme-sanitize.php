<?php

class HOCWP_Theme_Sanitize {
	public static function extension( $file, $extension ) {
		$extension = trim( $extension, '' );
		$extension = trim( $extension, '.' );
		$parts     = pathinfo( $file );
		if ( ! isset( $parts['extension'] ) || $extension != $parts['extension'] ) {
			$file .= '.' . $extension;
		}

		return $file;
	}

	public static function prefix( $string, $prefix, $sep = '-' ) {
		$pre_len = mb_strlen( $prefix );
		$sub     = mb_substr( $string, 0, $pre_len );
		if ( $prefix != $sub ) {
			$string = $prefix . $sep . $string;
		}

		return $string;
	}

	public static function html_class( $classes, $add = '' ) {
		if ( ! is_array( $classes ) ) {
			$classes = explode( ' ', $classes );
		}
		if ( ! empty( $add ) ) {
			if ( is_array( $add ) ) {
				$classes = wp_parse_args( $classes, $add );
			} elseif ( ! in_array( $add, $classes ) ) {
				$classes[] = $add;
			}
		}
		$classes = array_unique( $classes );
		$classes = array_filter( $classes );
		$classes = array_map( 'sanitize_html_class', $classes );

		return implode( ' ', $classes );
	}

	public static function media_url( $url, $media_id ) {
		if ( HOCWP_Theme::is_positive_number( $media_id ) && hocwp_theme_media_file_exists( $media_id ) ) {
			$details = wp_get_attachment_image_src( $media_id, 'full' );
			$url     = isset( $details[0] ) ? $details[0] : '';
		}

		return $url;
	}

	public static function media_value( $value ) {
		$id   = 0;
		$url  = '';
		$icon = '';
		$size = '';
		if ( ! is_array( $value ) ) {
			if ( is_numeric( $value ) ) {
				$id = $value;
			} else {
				$url = $value;
			}
		} else {
			$url = isset( $value['url'] ) ? $value['url'] : '';
			$id  = isset( $value['id'] ) ? $value['id'] : '';
			$id  = absint( $id );
		}
		if ( ! HOCWP_Theme::is_positive_number( $id ) ) {
			$id = attachment_url_to_postid( $url );
		}
		if ( HOCWP_Theme::is_positive_number( $id ) ) {
			$url  = self::media_url( $url, $id );
			$icon = wp_mime_type_icon( $id );
			$size = filesize( get_attached_file( $id ) );
		}
		$result = array(
			'id'          => $id,
			'url'         => $url,
			'type_icon'   => $icon,
			'is_image'    => hocwp_theme_is_image( $url, $id ),
			'size'        => $size,
			'size_format' => size_format( $size, 2 ),
			'mime_type'   => get_post_mime_type( $id )
		);

		return $result;
	}
}