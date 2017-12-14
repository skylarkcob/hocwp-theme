<?php
function hocwp_theme_settings_page_social_tab( $tabs ) {
	$tabs['social'] = __( 'Socials', 'hocwp-theme' );

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_social_tab' );

global $hocwp_theme;
if ( 'social' != $hocwp_theme->option->tab ) {
	return;
}

function hocwp_theme_settings_page_social_section() {
	$sections = array();

	global $hocwp_theme;
	$options = $hocwp_theme->options;
	$options = isset( $options['social'] ) ? $options['social'] : '';
	if ( is_array( $options ) && isset( $options['list_socials'] ) && ! empty( $options['list_socials'] ) ) {
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
		'title'       => __( 'Facebook', 'hocwp-theme' ),
		'description' => __( 'See what your Facebook friends liked, shared, or commented on across the Web.', 'hocwp-theme' )
	);

	$sections['google'] = array(
		'tab'         => 'social',
		'id'          => 'google',
		'title'       => __( 'Google', 'hocwp-theme' ),
		'description' => __( 'All information about Google account and Google console.', 'hocwp-theme' )
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
		'title'   => __( 'APP ID', 'hocwp-theme' ),
		'args'    => array(
			'label_for' => true
		)
	);

	$fields[] = array(
		'tab'     => 'social',
		'section' => 'facebook',
		'id'      => 'facebook_sdk_javascript',
		'title'   => __( 'SDK JavaScript', 'hocwp-theme' ),
		'args'    => array(
			'label_for'     => true,
			'callback'      => array( 'HOCWP_Theme_HTML_Field', 'textarea' ),
			'callback_args' => array(
				'class' => 'widefat',
				'rows'  => 8
			)
		)
	);

	global $hocwp_theme;
	$options = $hocwp_theme->options;
	$options = isset( $options['social'] ) ? $options['social'] : '';
	if ( is_array( $options ) && isset( $options['list_socials'] ) && ! empty( $options['list_socials'] ) ) {
		$socials = $options['list_socials'];
		$socials = explode( ',', $socials );
		$socials = array_map( 'trim', $socials );
		foreach ( $socials as $social ) {
			$key      = sanitize_title( $social );
			$key      = str_replace( '-', '_', $social );
			$key      .= '_url';
			$fields[] = array(
				'tab'     => 'social',
				'section' => 'social_url',
				'id'      => $key,
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
		}
	}

	$fields[] = array(
		'tab'     => 'social',
		'section' => 'google',
		'id'      => 'google_api_key',
		'title'   => __( 'API Key', 'hocwp-theme' ),
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
		'title'   => __( 'Client ID', 'hocwp-theme' ),
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
		'id'      => 'recaptcha_site_key',
		'title'   => __( 'reCAPTCHA Site Key', 'hocwp-theme' ),
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
		'id'      => 'recaptcha_secret_key',
		'title'   => __( 'reCAPTCHA Secret Key', 'hocwp-theme' ),
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
		'title' => __( 'AddThis ID', 'hocwp-theme' ),
		'args'  => array(
			'label_for'     => true,
			'callback_args' => array(
				'class' => 'regular-text'
			)
		)
	);

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_social_settings_field', 'hocwp_theme_settings_page_social_field' );