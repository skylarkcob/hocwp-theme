<?php

class HOCWP_Theme_Streaming_Streamango extends HOCWP_Theme_Streaming {
	public $pattern = '/^https?:\/\/streamango.com\/(f|embed)\/?(.*?)\/(.*)(.mp4)?/';

	public function __construct( $url, $username, $password ) {
		$this->url      = $url;
		$this->username = $username;
		$this->password = $password;
	}

	public function get_data() {
		$matches = $this->sanitize_url();
		if ( empty( $matches ) ) {
			$rs['status'] = 0;
			$rs['why']    = __( 'Link not valid', 'hocwp-theme' );

			return wp_json_encode( $rs );
		}
		$id = $matches[0][2];
		if ( isset( $_COOKIE[ $id ] ) && true == $_COOKIE[ $id ] ) {
			$rs['status']        = 1;
			$rs['cookie']        = 1;
			$rs['file_id']       = $id;
			$rs['download_link'] = $_COOKIE[ $id ];

			return wp_json_encode( $rs );
		}
		$filesystem = HOCWP_Theme_Utility::filesystem();
		$url        = 'https://api.fruithosted.net/file/dlticket?file=' . $id . '&login=' . $this->username . '&key=' . $this->password;
		$args       = array(
			'headers' => array(
				'content-type' => 'applycation/json'
			)
		);
		$response   = wp_remote_get( $url, $args );
		$data       = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( $data['status'] != 200 ) {
			$rs['status'] = 0;
			$rs['why']    = __( 'Cannot get download ticket!', 'hocwp-theme' );

			return wp_json_encode( $rs );
		} else {
			$ticket = $data['result']['ticket'];
		}
		$rs['captcha_url'] = '';
		if ( isset( $data['result']['captcha_url'] ) ) {
			$rs['captcha_url'] = $data['result']['captcha_url'];
		}
		$sources        = array();
		$json_link      = 'https://api.fruithosted.net/file/dl?file=' . $id . '&ticket=' . $ticket . '&captcha_response=';
		$rs['status']   = 1;
		$rs['cookie']   = 0;
		$rs['file_id']  = $id;
		$rs['api_link'] = $json_link;

		return wp_json_encode( $rs );
	}
}