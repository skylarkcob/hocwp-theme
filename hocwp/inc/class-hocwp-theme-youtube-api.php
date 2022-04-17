<?php
defined( 'ABSPATH' ) || exit;

class HOCWP_Theme_YouTube_API extends Abstract_HOCWP_Theme_Google_API {
	public $base = 'https://www.googleapis.com/youtube/';
	public $cache_prefix = 'youtube_api_';
	public $version = 3;
	public $name = '';
	public $video_url;
	private $video_id;

	public function __construct( $video_url, $params = array(), $name = 'videos', $version = '' ) {
		parent::__construct();

		$this->set_version( $version );
		$this->name = $name;

		if ( is_array( $params ) ) {
			if ( 'videos' == $this->name ) {
				// Convert URL to video ID
				if ( wp_http_validate_url( $video_url ) ) {
					$this->video_url = $video_url;
					$this->video_id  = self::get_video_id( $video_url );
				} else {
					$this->video_id = $video_url;
				}

				$defaults = array(
					'part' => 'snippet,contentDetails,statistics',
					'id'   => $this->video_id
				);

				$params = wp_parse_args( $params, $defaults );
			}

			$this->params = $params;
		}
	}

	public function get_thumbnail_url() {
		if ( $this->is_valid() ) {
			$info = $this->result->items[0];

			$thumb = $info->snippet->thumbnails->maxres->url ?? '';

			if ( empty( $thumb ) ) {
				$thumb = $info->snippet->thumbnails->standard->url ?? '';
			}

			if ( empty( $thumb ) ) {
				$thumb = $info->snippet->thumbnails->high->url ?? '';
			}

			if ( empty( $thumb ) ) {
				$thumb = $info->snippet->thumbnails->medium->url ?? '';
			}

			if ( empty( $thumb ) ) {
				$thumb = $info->snippet->thumbnails->default->url ?? '';
			}

			return $thumb;
		}

		return '';
	}

	public function set_version( $version ) {
		if ( empty( $version ) ) {
			$version = $this->version;
		}

		$version = ltrim( $version, 'v' );

		$this->version = 'v' . $version;
	}

	public static function get_video_id( $url ) {
		$params = HT()->get_params_from_url( $url );

		$id = '';

		if ( isset( $params['v'] ) && strlen( $params['v'] ) > 0 ) {
			$id = $params['v'];
		}

		if ( empty( $id ) && str_contains( $url, '/embed/' ) ) {
			$params = explode( '/embed/', $url );

			if ( 2 == count( $params ) ) {
				$id = $params[1];
			}
		}

		if ( empty( $id ) && str_contains( $url, 'youtu.be/' ) ) {
			$params = explode( 'youtu.be/', $url );

			if ( 2 == count( $params ) ) {
				$id = $params[1];
			}
		}

		if ( str_contains( $id, '/?' ) ) {
			$params = explode( '/?', $id );
			$id     = current( $params );
		}

		if ( str_contains( $id, '?' ) ) {
			$params = explode( '?', $id );
			$id     = current( $params );
		}

		unset( $parse, $params );

		return $id;
	}

	public function is_valid() {
		if ( ! is_object( $this->result ) ) {
			$this->fetch();
		}

		return ( is_object( $this->result ) && isset( $this->result->items ) && HT()->array_has_value( $this->result->items ) );
	}

	public function build_query_url() {
		// TODO: Implement build_query_url() method.
		if ( ! empty( $this->query_url ) && ! is_wp_error( $this->query_url ) ) {
			return $this->query_url;
		}

		if ( empty( $this->name ) ) {
			return new WP_Error( 'invalid_name', __( 'API name cannot be empty, can be videos, search,...', 'hocwp-theme' ) );
		}

		if ( ! HT()->array_has_value( $this->params ) ) {
			return new WP_Error( 'invalid_params', __( 'Query parameters must be provided in full.', 'hocwp-theme' ) );
		}

		$this->query_url = $this->base . $this->version . '/' . $this->name . '/';

		return $this->query_url;
	}
}