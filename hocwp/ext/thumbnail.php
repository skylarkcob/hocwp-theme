<?php
$src      = isset( $_GET['src'] ) ? $_GET['src'] : '';
$width    = isset( $_GET['width'] ) ? intval( $_GET['width'] ) : '';
$height   = isset( $_GET['height'] ) ? intval( $_GET['height'] ) : '';
$crop     = (bool) ( isset( $_GET['crop'] ) ? $_GET['crop'] : 1 );
$cache    = isset( $_GET['cache'] ) ? $_GET['cache'] : 1;
$is_file  = ( is_file( $src ) && is_readable( $src ) );
$basedir  = dirname( __FILE__ );
$basename = basename( $basedir );

while ( ! empty( $basename ) && 'wp-content' != $basename ) {
	$basedir  = dirname( $basedir );
	$basename = basename( $basedir );
}

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( $basedir ) . '/' );
}

if ( ! class_exists( 'WP_Filesystem_Direct' ) ) {
	require ABSPATH . 'wp-includes/class-wp-error.php';
	require ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
	require ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
}

$file = new WP_Filesystem_Direct( null );

if ( ! function_exists( 'hocwp_theme_url_exists' ) ) {
	function hocwp_theme_url_exists( $url ) {
		$file = new WP_Filesystem_Direct( null );

		if ( empty( $file->get_contents( $url ) ) ) {
			return false;
		}

		return true;
	}
}

if ( ! $is_file && ! filter_var( $src, FILTER_VALIDATE_URL ) && ! hocwp_theme_url_exists( $src ) ) {
	return;
}

$basedir .= '/uploads/cache';

if ( ! is_dir( $basedir ) || ! file_exists( $basedir ) ) {
	mkdir( $basedir, 0755, true );
}

$info      = pathinfo( $src );
$extension = isset( $info['extension'] ) ? $info['extension'] : '';

if ( empty( $extension ) ) {
	return;
}

$filename = $info['filename'];

if ( is_numeric( $width ) ) {
	$filename .= '-' . $width;
}

if ( is_numeric( $height ) ) {
	$filename .= '-' . $height;
}

$filename .= '.' . $extension;
$regenerate = true;
$file_path  = $basedir . '/' . $filename;

if ( 1 == $cache && ( is_file( $file_path ) && is_readable( $file_path ) && filemtime( $file_path ) >= strtotime( '-14 days' ) ) ) {
	$regenerate = false;
}

if ( $regenerate ) {
	$image = null;

	switch ( $extension ) {
		case 'jpeg':
		case 'jpg':
			$image = imagecreatefromjpeg( $src );
			break;
		case 'png':
			$image = imagecreatefrompng( $src );
			break;
		case 'gif':
			$image = imagecreatefromgif( $src );
			break;
	}

	$im_width  = imagesx( $image );
	$im_height = imagesy( $image );
	$quality   = isset( $_GET['quality'] ) ? $_GET['quality'] : '';

	if ( ! is_numeric( $quality ) || 0 > $quality || 100 < $quality ) {
		$quality = 60;
	}

	if ( 'png' == $extension ) {
		if ( 90 < $quality ) {
			$quality = 60;
		}

		$quality /= 100;
		$quality *= 9;
		$quality = 9 - $quality;
	}

	if ( ! function_exists( 'hocwp_theme_save_image' ) ) {
		function hocwp_theme_save_image( $image, $file_path, $quality, $extension ) {
			switch ( $extension ) {
				case 'jpeg':
				case 'jpg':
					header( "Content-type: image/jpg" );
					imagejpeg( $image, $file_path, $quality );
					break;
				case 'png':
					header( "Content-type: image/png" );
					imagepng( $image, $file_path, $quality );
					break;
				case 'gif':
					header( "Content-type: image/gif" );
					imagegif( $image, $file_path );
					break;
			}
		}
	}

	if ( $width == $im_width && $height == $im_height ) {
		hocwp_theme_save_image( $image, $file_path, $quality, $extension );
		imagedestroy( $image );
	} else {
		$ratio = $im_width / $im_height;

		if ( ! is_numeric( $width ) || 1 > $width ) {
			if ( is_numeric( $height ) && 0 < $height ) {
				$width = round( $height * $ratio );
			} else {
				$width = $im_width;
			}
		}

		if ( ! is_numeric( $height ) || 1 > $height ) {
			if ( is_numeric( $width ) && 0 < $width ) {
				$height = round( $width / $ratio );
			} else {
				$height = $im_height;
			}
		}

		$thumb       = imagecreatetruecolor( $width, $height );
		$dx          = 0;
		$dy          = 0;
		$sx          = 0;
		$sy          = 0;
		$thumb_ratio = $width / $height;

		if ( $ratio >= $thumb_ratio ) {
			$new_height = $height;
			$new_width  = $im_width / ( $im_height / $height );
		} else {
			$new_width  = $width;
			$new_height = $im_height / ( $im_width / $width );
		}

		$dx = 0 - ( $new_width - $width ) / 2;
		$dy = 0 - ( $new_height - $height ) / 2;

		if ( ! is_numeric( $width ) ) {
			$width = $new_width;
		}

		if ( ! is_numeric( $height ) ) {
			$height = $new_height;
		}

		if ( $width > $im_width && $height > $im_height ) {
			$crop = false;
		}

		if ( $crop && $ratio != $thumb_ratio ) {
			$rect = array(
				'x'      => round( ( $im_width / 2 ) - ( $width / 2 ) ),
				'y'      => round( ( $im_height / 2 ) - ( $height / 2 ) ),
				'width'  => $width,
				'height' => $height
			);

			$thumb = imagecrop( $image, $rect );
		} else {
			imagecopyresampled( $thumb, $image, $dx, $dy, $sx, $sy, $width, $height, $im_width, $im_height );
		}

		hocwp_theme_save_image( $thumb, $file_path, $quality, $extension );
		imagedestroy( $image );
		imagedestroy( $thumb );
	}
}

if ( ! function_exists( 'hocwp_theme_set_modified_date' ) ) {
	function hocwp_theme_set_modified_date( $date ) {
		$since = isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ? stripslashes( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) : false;

		if ( $since && strtotime( $since ) >= $date ) {
			header( 'HTTP/1.1 304 Not Modified' );
			exit;
		}

		$modified = gmdate( 'D, d M Y H:i:s', $date ) . ' GMT';
		header( 'Last-Modified: ' . $modified );
	}
}

if ( 1 == $cache ) {
	clearstatcache();
	hocwp_theme_set_modified_date( filemtime( $file_path ) );
}

header( 'Content-type: image/png' );

if ( 1 == $cache ) {
	header( 'Cache-Control: must-revalidate' );
	header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', strtotime( '+14 days' ) ) . ' GMT' );
}

ob_clean();
flush();
echo $file->get_contents( $file_path );