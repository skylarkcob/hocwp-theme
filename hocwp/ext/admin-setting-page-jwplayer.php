<?php
$load = $load = hocwp_theme_load_extension_jwplayer();
if ( ! $load ) {
	return;
}

function hocwp_theme_settings_page_jwplayer_tab( $tabs ) {
	$tabs['jwplayer'] = __( 'JW Player', 'hocwp-theme' );

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_jwplayer_tab' );

global $hocwp_theme;
if ( 'jwplayer' != $hocwp_theme->option->tab ) {
	return;
}

function hocwp_theme_settings_page_jwplayer_section() {
	$fields = array(
		'streamango'   => array(
			'tab'         => 'jwplayer',
			'id'          => 'streamango',
			'title'       => __( 'Streamango', 'hocwp-theme' ),
			'description' => __( 'Streamango streaming API settings.', 'hocwp-theme' )
		),
		'streamcherry' => array(
			'tab'         => 'jwplayer',
			'id'          => 'streamcherry',
			'title'       => __( 'Streamcherry', 'hocwp-theme' ),
			'description' => __( 'Streamcherry streaming API settings.', 'hocwp-theme' )
		)
	);
	$fields = array();

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_jwplayer_settings_section', 'hocwp_theme_settings_page_jwplayer_section' );

function hocwp_theme_settings_page_jwplayer_field() {
	$fields    = array();
	$fields[]  = array(
		'id'    => 'key',
		'title' => __( 'Key', 'hocwp-theme' ),
		'tab'   => 'jwplayer',
		'args'  => array(
			'type'      => 'string',
			'label_for' => true
		)
	);
	$skins_dir = get_template_directory() . '/custom/lib/jwplayer/skins';
	if ( is_dir( $skins_dir ) ) {
		$files = scandir( $skins_dir );
		unset( $files[0], $files[1] );
		if ( HOCWP_Theme::array_has_value( $files ) ) {
			$opts = array(
				__( '-- Choose skin --', 'hocwp-theme' )
			);
			foreach ( $files as $file ) {
				$info                      = pathinfo( $file );
				$opts[ $info['filename'] ] = ucfirst( $info['filename'] );
			}
			$fields[] = array(
				'id'    => 'skin',
				'title' => __( 'Skin', 'hocwp-theme' ),
				'tab'   => 'jwplayer',
				'args'  => array(
					'type'          => 'string',
					'callback'      => array( 'HOCWP_Theme_HTML_Field', 'select' ),
					'callback_args' => array(
						'options' => $opts
					)
				)
			);
		}
	}
	/*
	$fields[] = array(
		'tab'   => 'jwplayer',
		'id'    => 'endpoint',
		'title' => __( 'Endpoint', 'hocwp-theme' ),
		'args'  => array(
			'label_for' => true
		)
	);
	*/

	/*
	$fields[] = array(
		'tab'     => 'jwplayer',
		'section' => 'streamango',
		'id'      => 'streamango_username',
		'title'   => __( 'Username', 'hocwp-theme' ),
		'args'    => array(
			'label_for' => true
		)
	);
	*/

	/*
	$fields[] = array(
		'tab'     => 'jwplayer',
		'section' => 'streamango',
		'id'      => 'streamango_password',
		'title'   => __( 'Password', 'hocwp-theme' ),
		'args'    => array(
			'label_for' => true
		)
	);
	*/

	/*
	$fields[] = array(
		'tab'     => 'jwplayer',
		'section' => 'streamcherry',
		'id'      => 'streamcherry_username',
		'title'   => __( 'Username', 'hocwp-theme' ),
		'args'    => array(
			'label_for' => true
		)
	);
	*/

	/*
	$fields[] = array(
		'tab'     => 'jwplayer',
		'section' => 'streamcherry',
		'id'      => 'streamcherry_password',
		'title'   => __( 'Password', 'hocwp-theme' ),
		'args'    => array(
			'label_for' => true
		)
	);
	*/

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_jwplayer_settings_field', 'hocwp_theme_settings_page_jwplayer_field' );

function hocwp_theme_settings_page_jwplayer_flush_rules() {
	set_transient( 'hocwp_theme_flush_rewrite_rules', 1 );
}

add_action( 'hocwp_theme_settings_saved', 'hocwp_theme_settings_page_jwplayer_flush_rules' );
add_action( 'init', 'hocwp_theme_settings_page_jwplayer_flush_rules' );