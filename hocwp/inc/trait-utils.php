<?php
defined( 'ABSPATH' ) || exit;

if ( ! trait_exists( 'HOCWP_Theme_Deprecated' ) ) {
	require_once __DIR__ . '/trait-deprecated.php';
}

trait HOCWP_Theme_Utils {
	use HOCWP_Theme_Deprecated;

	public function make_image_lazyload( $content, $originals = array() ) {
		$dom = new DOMDocument();
		@$dom->loadHTML( $content );

		if ( ! in_array( 'data-original', $originals ) ) {
			$originals[] = 'data-original';
		}

		if ( ! in_array( 'data-src', $originals ) ) {
			$originals[] = 'data-src';
		}

		foreach ( $dom->getElementsByTagName( 'img' ) as $node ) {
			$old_src = $node->getAttribute( 'src' );

			foreach ( $originals as $attr ) {
				$node->setAttribute( $attr, $old_src );
			}

			$node->setAttribute( 'src', HOCWP_THEME_DOT_IMAGE_SRC );
		}

		return $dom->saveHtml();
	}

	public function price_format( $price, $decimals = 0, $format = null ) {
		if ( empty( $format ) ) {
			$format = _x( '$%s', 'price format', 'hocwp-theme' );
		}

		$formatted = sprintf( $format, number_format_i18n( $price, $decimals ) );

		return apply_filters( 'hocwp_theme_price_format', $formatted, $price, $decimals, $format );
	}

	public function is_localhost() {
		$domain = home_url();
		$domain = HT()->get_domain_name( $domain, true );

		return ( 'localhost' == $domain || str_contains( $domain, 'localhost' ) || str_contains( $domain, '127.0.0.1' ) || str_contains( $domain, '192.168.1.249' ) || str_contains( $domain, '192.168.1.213' ) || str_contains( $domain, '192.168.1.69' ) );
	}

	public function get_site_name() {
		$name = HT_Options()->get_general( 'site_short_name' );

		if ( empty( $name ) ) {
			$name = get_bloginfo( 'name' );
		}

		return apply_filters( 'hocwp_theme_site_name', $name );
	}

	/**
	 * Auto fetch all images from URL by using Extract Pics API.
	 *
	 * @param string $api_key The Extract Pics API key.
	 * @param string $url The URL for fetch images.
	 * @param string $args The arguments for return image result.
	 *
	 * @return array|mixed|string
	 */
	public function extract_pics_api( $api_key, $url, $args = array() ) {
		$api_key = apply_filters( 'extract_pics_api_key', $api_key, $url, $args );

		$root_tr_name = 'extract_pics_api_' . md5( $url );

		if ( false === ( $result = get_transient( $root_tr_name ) ) ) {
			$headers = array(
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type'  => 'application/json'
			);

			$tr_name = 'extract_pics_api_id_' . md5( $url );

			if ( false === ( $id = get_transient( $tr_name ) ) ) {
				$id = apply_filters( 'hocwp_theme_extract_pics_api_id', $id );

				if ( empty( $id ) ) {
					$remote = wp_remote_post( 'https://api.extract.pics/v0/extractions', array(
						'headers' => $headers,
						'body'    => json_encode( array( 'url' => $url ) )
					) );

					$body = json_decode( wp_remote_retrieve_body( $remote ) );
					// Set extract pics api url id
					set_transient( $tr_name, $body->data->id );
				}
			}

			if ( ! empty( $id ) ) {
				$remote = wp_remote_get( 'https://api.extract.pics/v0/extractions/' . $id, array(
					'headers' => $headers
				) );

				$res = json_decode( wp_remote_retrieve_body( $remote ) );

				if ( ! empty( $args ) && isset( $res->data->images ) && HT()->array_has_value( $res->data->images ) ) {
					$defaults = array(
						'extensions'   => array( 'jpg', 'jpeg', 'png', 'webp' ),
						'name_len'     => 20,
						'url_contains' => '/wp-content/uploads/' . date( 'Y/m' ) . '/',
						'number'       => 1
					);

					if ( ! is_array( $args ) ) {
						$args = array();
					}

					$args = wp_parse_args( $args, $defaults );

					$images = array();

					$number = $args['number'];

					foreach ( $res->data->images as $item ) {
						$image = $item->url ?? '';

						if ( ! empty( $image ) ) {
							$check = apply_filters( 'hocwp_theme_extract_pics_api_url_check_pre', false, $url, $args, $item );

							if ( ! $check ) {
								if ( empty( $args['url_contains'] ) || str_contains( $image, $args['url_contains'] ) ) {
									$parts = pathinfo( $image );
									$ext   = $parts['extension'] ?? '';
									$name  = $parts['filename'] ?? '';

									if ( in_array( $ext, $args['extensions'] ) && strlen( $name ) > $args['name_len'] ) {
										$check = apply_filters( 'hocwp_theme_extract_pics_api_url_check', $image, $url, $args, $item );

										if ( $check ) {
											if ( 1 == $number ) {
												$result = $image;
												break;
											}

											$images[] = $image;

											if ( count( $images ) >= $number ) {
												break;
											}
										}
									}
								}
							} else {
								if ( 1 == $number ) {
									$result = $image;
									break;
								}

								$images[] = $image;

								if ( count( $images ) >= $number ) {
									break;
								}
							}
						}
					}

					if ( empty( $result ) && HT()->array_has_value( $images ) ) {
						$result = $images;
					}
				} else {
					$result = $res;
				}

				if ( $result ) {
					set_transient( $root_tr_name, $result );
				}
			}
		}

		return $result;
	}
}