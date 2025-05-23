<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_settings_page_social_tab( $tabs ) {
	$tabs['social'] = array(
		'text' => __( 'Socials', 'hocwp-theme' ),
		'icon' => '<span class="dashicons dashicons-share"></span>'
	);

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_social_tab' );

if ( 'social' != hocwp_theme_object()->option->tab ) {
	return;
}

function hocwp_theme_settings_page_social_section() {
	$sections = array();

	$options = ht_options()->get( 'social' );

	if ( is_array( $options ) && ! empty( $options['list_socials'] ) ) {
		$sections['social_url'] = array(
			'tab'         => 'social',
			'id'          => 'social_url',
			'title'       => __( 'Social Urls', 'hocwp-theme' ),
			'description' => __( 'Enter url to your social networking account.', 'hocwp-theme' )
		);
	}

	$sections['facebook'] = array(
		'tab'         => 'social',
		'id'          => 'facebook',
		'title'       => 'Facebook',
		'description' => __( 'See what your Facebook friends liked, shared, or commented on across the Web.', 'hocwp-theme' )
	);

	$sections['google'] = array(
		'tab'         => 'social',
		'id'          => 'google',
		'title'       => 'Google',
		'description' => __( 'All information about Google account and Google console.', 'hocwp-theme' )
	);

	$sections['captcha'] = array(
		'tab'         => 'social',
		'id'          => 'captcha',
		'title'       => __( 'CAPTCHA', 'hocwp-theme' ),
		'description' => __( 'CAPTCHA helps protect you from spam and password decryption by asking you to complete a simple test that proves you are human and not a computer trying to break into a password protected account.', 'hocwp-theme' )
	);

	return $sections;
}

add_filter( 'hocwp_theme_settings_page_social_settings_section', 'hocwp_theme_settings_page_social_section' );

function hocwp_theme_settings_page_social_field() {
	$fields = array();

	$fields[] = array(
		'tab'   => 'social',
		'id'    => 'list_socials',
		'title' => __( 'List Socials', 'hocwp-theme' ),
		'args'  => array(
			'label_for'     => true,
			'description'   => __( 'You can specify a list of the socials on your site if current theme supports. Each social separated by commas. E.g: facebook, google, youtube', 'hocwp-theme' ),
			'callback_args' => array(
				'class' => 'widefat'
			)
		)
	);

	$fields[] = array(
		'tab'     => 'social',
		'section' => 'facebook',
		'id'      => 'facebook_app_id',
		'title'   => 'APP ID',
		'args'    => array(
			'label_for' => true
		)
	);

	$fields[] = array(
		'tab'     => 'social',
		'section' => 'facebook',
		'id'      => 'facebook_app_secret',
		'title'   => 'APP Secret',
		'args'    => array(
			'label_for' => true
		)
	);

	$fields[] = array(
		'tab'     => 'social',
		'section' => 'facebook',
		'id'      => 'facebook_access_token',
		'title'   => 'Access Token',
		'args'    => array(
			'label_for'     => true,
			'callback_args' => array(
				'class' => 'widefat'
			)
		)
	);

	$fields[] = array(
		'tab'     => 'social',
		'section' => 'facebook',
		'id'      => 'facebook_sdk_javascript',
		'title'   => 'SDK JavaScript',
		'args'    => array(
			'label_for'     => true,
			'callback'      => array( 'HOCWP_Theme_HTML_Field', 'textarea' ),
			'callback_args' => array(
				'class' => 'widefat',
				'rows'  => 8
			)
		)
	);

	$options = ht_options()->get( 'social' );

	if ( is_array( $options ) && ! empty( $options['list_socials'] ) ) {
		$socials = $options['list_socials'];
		$socials = explode( ',', $socials );
		$socials = array_map( 'trim', $socials );

		foreach ( $socials as $social ) {
			$key = sanitize_title( $social );
			$key = str_replace( '-', '_', $key );

			$url = $key . '_url';

			$fields[] = array(
				'tab'     => 'social',
				'section' => 'social_url',
				'id'      => $url,
				'title'   => ucwords( $social ),
				'args'    => array(
					'label_for'     => true,
					'type'          => 'url',
					'callback_args' => array(
						'class' => 'regular-text',
						'type'  => 'url'
					)
				)
			);

			$icon = $key . '_icon';

			$fields[] = array(
				'tab'     => 'social',
				'section' => 'social_url',
				'id'      => $icon,
				'title'   => sprintf( __( '%s Icon', 'hocwp-theme' ), ucwords( $social ) ),
				'args'    => array(
					'label_for'     => true,
					'type'          => 'text',
					'callback_args' => array(
						'class' => 'regular-text',
						'type'  => 'text'
					)
				)
			);
		}
	}

	$fields[] = array(
		'tab'     => 'social',
		'section' => 'google',
		'id'      => 'google_api_key',
		'title'   => 'API Key',
		'args'    => array(
			'label_for'     => true,
			'callback_args' => array(
				'class' => 'regular-text'
			)
		)
	);

	$fields[] = array(
		'tab'     => 'social',
		'section' => 'google',
		'id'      => 'google_api_key_http',
		'title'   => 'API Key (HTTP Referrer Restriction)',
		'args'    => array(
			'label_for'     => true,
			'callback_args' => array(
				'class' => 'regular-text'
			)
		)
	);

	$fields[] = array(
		'tab'     => 'social',
		'section' => 'google',
		'id'      => 'google_api_key_ip',
		'title'   => 'API Key (IP Address Restriction)',
		'args'    => array(
			'label_for'     => true,
			'callback_args' => array(
				'class' => 'regular-text'
			)
		)
	);

	$fields[] = array(
		'tab'     => 'social',
		'section' => 'google',
		'id'      => 'google_client_id',
		'title'   => 'Client ID',
		'args'    => array(
			'label_for'     => true,
			'callback_args' => array(
				'class' => 'regular-text'
			)
		)
	);

	$fields[] = array(
		'tab'     => 'social',
		'section' => 'google',
		'id'      => 'search_engine_id',
		'title'   => 'Search engine ID',
		'args'    => array(
			'label_for'     => true,
			'callback_args' => array(
				'class' => 'regular-text'
			)
		)
	);

	$fields[] = array(
		'tab'   => 'social',
		'id'    => 'addthis_id',
		'title' => 'AddThis ID',
		'args'  => array(
			'label_for'     => true,
			'callback_args' => array(
				'class' => 'regular-text'
			)
		)
	);

	$fields[] = array(
		'tab'   => 'social',
		'id'    => 'addthis_widget_id',
		'title' => 'AddThis Tool ID',
		'args'  => array(
			'label_for'     => true,
			'callback_args' => array(
				'class' => 'regular-text'
			)
		)
	);

	$fields[] = array(
		'tab'   => 'social',
		'id'    => 'fix_zalo_me',
		'title' => __( 'Fix Zalo Me', 'hocwp-theme' ),
		'args'  => array(
			'label_for'     => true,
			'callback'      => array( 'HOCWP_Theme_HTML_Field', 'textarea' ),
			'description'   => __( 'List zalo account phone number with QR code. Each account contains phone number and QR code separated by colon ":" then put in new line if you have more than one account. Example: 0987654321:ktewgb7i3ixe', 'hocwp-theme' ),
			'callback_args' => array(
				'class' => 'widefat',
				'rows'  => 8
			)
		)
	);

	$fields[] = array(
		'tab'     => 'social',
		'section' => 'captcha',
		'id'      => 'recaptcha_site_key',
		'title'   => 'reCAPTCHA Site Key',
		'args'    => array(
			'label_for'     => true,
			'callback_args' => array(
				'class' => 'regular-text'
			)
		)
	);

	$fields[] = array(
		'tab'     => 'social',
		'section' => 'captcha',
		'id'      => 'recaptcha_secret_key',
		'title'   => __( 'reCAPTCHA Secret Key', 'hocwp-theme' ),
		'args'    => array(
			'label_for'     => true,
			'callback_args' => array(
				'class' => 'regular-text'
			)
		)
	);

	$args = array(
		'options' => array(
			''             => __( '-- Choose version --', 'hocwp-theme' ),
			'v2'           => _x( 'V2', 'recaptcha', 'hocwp-theme' ),
			'v2_invisible' => _x( 'V2 Invisilbe', 'recaptcha', 'hocwp-theme' ),
			'v3'           => _x( 'V3', 'recaptcha', 'hocwp-theme' ),
			'enterprise'   => _x( 'Enterprise', 'recaptcha', 'hocwp-theme' )
		),
		'class'   => 'regular-text'
	);

	$fields[] = new HOCWP_Theme_Admin_Setting_Field( 'recaptcha_version', __( 'ReCAPTCHA Version', 'hocwp-theme' ), 'select', $args, 'string', 'social', 'captcha' );

	$fields[] = array(
		'tab'     => 'social',
		'section' => 'captcha',
		'id'      => 'recaptcha_project_id',
		'title'   => __( 'ReCAPTCHA Project ID', 'hocwp-theme' ),
		'args'    => array(
			'label_for'     => true,
			'callback_args' => array(
				'class' => 'regular-text'
			)
		)
	);

	$fields[] = array(
		'tab'     => 'social',
		'section' => 'captcha',
		'id'      => 'hcaptcha_site_key',
		'title'   => 'hCAPTCHA Site Key',
		'args'    => array(
			'label_for'     => true,
			'callback_args' => array(
				'class' => 'regular-text'
			)
		)
	);

	$fields[] = array(
		'tab'     => 'social',
		'section' => 'captcha',
		'id'      => 'hcaptcha_secret_key',
		'title'   => 'hCAPTCHA Secret Key',
		'args'    => array(
			'label_for'     => true,
			'callback_args' => array(
				'class' => 'regular-text'
			)
		)
	);

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_social_settings_field', 'hocwp_theme_settings_page_social_field' );