<?php
defined( 'ABSPATH' ) || exit;

final class HOCWP_THEME_CAPTCHA_SERVICE {
	const HCAPTCHA = 'hcaptcha';
	const RECAPTCHA = 'recaptcha';
}

trait HOCWP_Theme_CAPTCHA_Utils {
	public function detect_service() {
		$options    = HT_Options()->get_tab( null, null, 'social' );
		$site_key   = $options['hcaptcha_site_key'] ?? '';
		$secret_key = $options['hcaptcha_secret_key'] ?? '';

		$service = '';

		if ( ! empty( $site_key ) && ! empty( $secret_key ) ) {
			$service = HOCWP_THEME_CAPTCHA_SERVICE::HCAPTCHA;
		} else {
			$site_key   = $options['recaptcha_site_key'] ?? '';
			$secret_key = $options['recaptcha_secret_key'] ?? '';

			if ( ! empty( $site_key ) && ! empty( $secret_key ) ) {
				$service = HOCWP_THEME_CAPTCHA_SERVICE::RECAPTCHA;
			}
		}

		return $service;
	}

	public function is_captcha_valid( $url, $params = array(), $args = array() ) {
		$url = add_query_arg( $params, $url );

		$method = $args['method'] ?? 'REQUEST';
		$method = strtoupper( $method );

		unset( $args['method'] );

		if ( 'POST' == $method ) {
			$response = wp_remote_post( $url, $args );
		} elseif ( 'GET' == $method ) {
			$response = wp_remote_get( $url, $args );
		} else {
			$response = wp_remote_request( $url, $args );
		}

		$response = wp_remote_retrieve_body( $response );

		$response = json_decode( $response );

		if ( HT_Util()->is_object_valid( $response ) ) {
			if ( isset( $response->success ) && ( $response->success || 1 == $response->success ) ) {
				return true;
			}

			// reCAPTCHA Enterprise
			if ( isset( $response->score ) && isset( $response->tokenProperties ) ) {
				$token = $response->tokenProperties;

				if ( isset( $token->valid ) && $token->valid ) {
					return true;
				}
			}
		}

		return false;
	}

	public function check_recaptcha_config_valid( $options = array() ) {
		if ( empty( $options ) ) {
			$options = HT_Options()->get_tab( null, '', 'social' );
		}

		$site_key   = $options['recaptcha_site_key'] ?? '';
		$secret_key = $options['recaptcha_secret_key'] ?? '';

		return ( ! empty( $site_key ) && ! empty( $secret_key ) );
	}

	public function check_hcaptcha_config_valid( $options = array() ) {
		if ( empty( $options ) ) {
			$options = HT_Options()->get_tab( null, '', 'social' );
		}

		$site_key   = $options['hcaptcha_site_key'] ?? '';
		$secret_key = $options['hcaptcha_secret_key'] ?? '';

		return ( ! empty( $site_key ) && ! empty( $secret_key ) );
	}

	public function check_config_valid() {
		$options = HT_Options()->get_tab( null, '', 'social' );

		return ( $this->check_recaptcha_config_valid( $options ) || $this->check_hcaptcha_config_valid( $options ) );
	}

	public function show( $atts = array(), $script_params = array(), $insert_before = '' ) {
		$this->captcha( $atts, $script_params, $insert_before );
	}

	public function captcha( $atts = array(), $script_params = array(), $insert_before = '' ) {
		if ( ! $this->check_config_valid() ) {
			return;
		}

		$service = $this->detect_service();

		switch ( $service ) {
			case HOCWP_THEME_CAPTCHA_SERVICE::RECAPTCHA:
				$this->recaptcha( $atts, $script_params, $insert_before );
				break;
			case HOCWP_THEME_CAPTCHA_SERVICE::HCAPTCHA:
				$this->hcaptcha( $atts, $script_params, $insert_before );
				break;
		}
	}

	public function captcha_valid( $params = array() ) {
		$service = $this->detect_service();

		return match ( $service ) {
			HOCWP_THEME_CAPTCHA_SERVICE::RECAPTCHA => $this->recaptcha_valid( $params ),
			HOCWP_THEME_CAPTCHA_SERVICE::HCAPTCHA => $this->hcaptcha_valid( $params ),
			default => new WP_Error( 'empty_service', __( 'CAPTCHA service does not provide.', 'hocwp-theme' ) )
		};

	}

	public function hcaptcha( $atts = array(), $script_params = array(), $insert_before = '' ) {
		if ( ! $this->check_hcaptcha_config_valid() ) {
			return;
		}

		$defaults = array(
			'hl' => get_locale()
		);

		$script_params = wp_parse_args( $script_params, $defaults );

		$url = 'https://www.hcaptcha.com/1/api.js';
		$url = add_query_arg( $script_params, $url );

		HT_Util()->inline_script( 'hcaptcha', $url );

		$div = new HOCWP_Theme_HTML_Tag( 'div' );

		$defaults = array(
			'data-sitekey' => HT_Options()->get_tab( 'hcaptcha_site_key', '', 'social' )
		);

		$atts = wp_parse_args( $atts, $defaults );

		$div->set_attributes( $atts );
		$div->add_attribute( 'class', 'h-captcha' );
		$div->add_attribute( 'style', 'margin-bottom: 10px;' );
		$div->output();
	}

	public function hcaptcha_valid( $params = array() ) {
		$defaults = array(
			'secret'   => HT_Options()->get_tab( 'hcaptcha_secret_key', '', 'social' ),
			'response' => $_POST['h-captcha-response'] ?? ''
		);

		$params = wp_parse_args( $params, $defaults );

		$url = 'https://hcaptcha.com/siteverify';

		return $this->is_captcha_valid( $url, $params );
	}

	public function add_recaptcha_script( $obj, $submit_field = '' ) {
		if ( ! $this->check_recaptcha_config_valid() ) {
			return $submit_field;
		}

		if ( $obj instanceof HOCWP_Theme_RECAPTCHA ) {
			$version = $obj->version;

			if ( 'v2_invisible' == $version || 'v3' == $version || 'enterprise' == $version ) {
				$site_key = HT_Options()->get_tab( 'recaptcha_site_key', '', 'social' );

				ob_start();
				?>
                <script>
                    let response = document.getElementsByName("recaptcha_version")[0],
                        form = null;

                    if (response) {
                        form = response.closest("form");
                    }

                    function setResponse(token) {
                        document.getElementById("g-recaptcha-response").value = token;
                        form.submit();
                    }

                    if (form) {
                        let button = form.querySelector("*[type='submit']");

                        if (button) {
                            let version = "<?php echo $version; ?>";

                            if ("v3" === version) {
                                let interval = setInterval(function () {
                                    if (window.grecaptcha) {
                                        grecaptcha.ready(function () {
                                            grecaptcha.execute("<?php echo $site_key; ?>", {action: "validate_captcha"}).then(function (token) {
                                                document.getElementById("captcha-response").value = token;
                                            });
                                        });
                                        clearInterval(interval);
                                    }
                                }, 200);
                            } else if ("enterprise" === version) {
                                let interval = setInterval(function () {
                                    if (window.grecaptcha) {
                                        grecaptcha.enterprise.ready(function () {
                                            grecaptcha.enterprise.execute("<?php echo $site_key; ?>", {action: "validate_captcha"}).then(function (token) {
                                                document.getElementById("captcha-response").value = token;
                                            });
                                        });
                                        clearInterval(interval);
                                    }
                                }, 200);
                            } else {
                                button.onclick = function (e) {
                                    e.preventDefault();
                                    grecaptcha.execute();
                                };
                            }
                        }
                    }
                </script>
				<?php
				$script = ob_get_clean();

				$submit_field .= $script;
			}
		}

		return $submit_field;
	}

	public function recaptcha( $atts = array(), $script_params = array(), $insert_before = '' ) {
		if ( ! $this->check_recaptcha_config_valid() ) {
			return;
		}

		if ( ! is_array( $atts ) ) {
			$atts = array(
				'version' => $atts
			);
		}

		$version = $atts['version'] ?? '';

		$options  = HT_Util()->get_theme_options( 'social' );
		$site_key = $options['recaptcha_site_key'] ?? '';

		if ( empty( $site_key ) ) {
			return;
		}
		?>
        <input type="hidden" name="recaptcha_version" value="<?php echo esc_attr( $version ); ?>">
        <input type="hidden" name="recaptcha_site_key" value="<?php echo esc_attr( $site_key ); ?>">
		<?php
		if ( 'v2' == $version ) {
			$src = 'https://www.google.com/recaptcha/api.js';

			HT_Util()->inline_script( 'recaptcha-jssdk', $src );
			?>
            <div class="g-recaptcha" data-sitekey="<?php echo $site_key; ?>"
                 data-version="<?php echo esc_attr( $version ); ?>" style="margin-bottom: 10px;"></div>
			<?php
		} elseif ( 'v2_invisible' == $version ) {
			$src = 'https://www.google.com/recaptcha/api.js';

			HT_Util()->inline_script( 'recaptcha-jssdk', $src );
			?>
            <div class="g-recaptcha" data-sitekey="<?php echo $site_key; ?>" data-size="invisible"
                 data-callback="setResponse" data-version="<?php echo esc_attr( $version ); ?>"
                 style="margin-bottom: 10px;"></div>
			<?php
		} elseif ( 'v3' == $version ) {
			$src = 'https://www.google.com/recaptcha/api.js';
			$src = add_query_arg( 'render', $site_key, $src );
			HT_Util()->inline_script( 'recaptcha-jssdk', $src );
			?>
            <input type="hidden" id="captcha-response" name="g-recaptcha-response"
                   data-version="<?php echo esc_attr( $version ); ?>">
			<?php
		} elseif ( 'enterprise' == $version ) {
			$src = 'https://www.google.com/recaptcha/enterprise.js';
			$src = add_query_arg( 'render', $site_key, $src );
			HT_Util()->inline_script( 'recaptcha-jssdk', $src );
			?>
            <input type="hidden" id="captcha-response" name="g-recaptcha-response"
                   data-version="<?php echo esc_attr( $version ); ?>">
			<?php
		}
	}

	public function control_captcha_errors( $response, $errors = null ) {
		if ( is_wp_error( $response ) || ! $response ) {
			$code = 'invalid_captcha';
			$msg  = __( '<strong>Error:</strong> Please correct the CAPTCHA.', 'hocwp-theme' );

			if ( is_wp_error( $response ) && $response instanceof WP_Error ) {
				$code = $response->get_error_code();
				$msg  = $response->get_error_message();
			}

			if ( ! is_wp_error( $errors ) || ! ( $errors instanceof WP_Error ) ) {
				$errors = new WP_Error( $code, $msg );
			} else {
				$errors->add( $code, $msg );
			}
		}

		return $errors;
	}

	public function recaptcha_valid( $params = array() ) {
		$options    = HT_Util()->get_theme_options( 'social' );
		$secret_key = $options['recaptcha_secret_key'] ?? '';

		if ( empty( $secret_key ) ) {
			return false;
		}

		$version = $_POST['recaptcha_version'] ?? '';

		$url  = 'https://www.google.com/recaptcha/api/siteverify';
		$args = array();

		if ( 'enterprise' == $version ) {
			$token = $_POST['g-recaptcha-response'] ?? '';
			$token = htmlspecialchars( $token, ENT_QUOTES, 'UTF-8' );

			$site_key   = $_POST['recaptcha_site_key'] ?? '';
			$project_id = HT_Options()->get_tab( 'recaptcha_project_id', '', 'social' );

			if ( empty( $site_key ) || empty( $project_id ) ) {
				return false;
			}

			$url = 'https://recaptchaenterprise.googleapis.com/v1beta1/projects/' . $project_id . '/assessments?key=' . $secret_key;

			$body = array(
				'event' => array(
					'token'          => $token,
					'siteKey'        => $site_key,
					'expectedAction' => 'validate_captcha',
					'userAgent'      => HT()->get_user_agent(),
					'userIpAddress'  => HT()->get_IP()
				)
			);

			$body = json_encode( $body );

			$args = array(
				'headers'     => array( 'Content-Type' => 'application/json' ),
				'method'      => 'POST',
				'body'        => $body,
				'data_format' => 'body'
			);
		} else {
			$defaults = array(
				'secret'   => $secret_key,
				'response' => $_POST['g-recaptcha-response'] ?? '',
				'remoteip' => HT()->get_IP()
			);

			$params = wp_parse_args( $params, $defaults );
		}

		return $this->is_captcha_valid( $url, $params, $args );
	}
}