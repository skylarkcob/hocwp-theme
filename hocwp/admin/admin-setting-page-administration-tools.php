<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! current_user_can( 'manage_options' ) ) {
	return;
}

$tab = new HOCWP_Theme_Admin_Setting_Tab( 'administration_tools', __( 'Administration Tools', 'hocwp-theme' ), '<span class="dashicons dashicons-admin-tools"></span>', array(), 99999 );

$tab->submit_button = false;

$args = array(
	'title'       => __( 'Update Administrative Email', 'hocwp-theme' ),
	'description' => __( 'Changing admin email address does not require confirmation.', 'hocwp-theme' )
);

$tab->add_section( 'administrative_email', $args );

$tab->add_field( 'new_email', __( 'New Email', 'hocwp-theme' ), 'input', array( 'type' => 'email' ), 'string', 'administrative_email' );

$args = array(
	'buttons' => array(
		'change_admin_email' => array(
			'attributes'  => array(
				'data-ajax-button'  => 1,
				'data-message'      => __( 'Admin email has been changed successfully!', 'hocwp-theme' ),
				'data-change-email' => 1,
				'aria-label'        => __( 'Change', 'hocwp-theme' )
			),
			'button_type' => 'button',
			'text'        => __( 'Change', 'hocwp-theme' )
		),
		'send_test_email'    => array(
			'attributes'  => array(
				'data-ajax-button'     => 1,
				'data-message'         => __( 'Testing email has been sent successfully!', 'hocwp-theme' ),
				'data-send-test-email' => 1,
				'aria-label'           => __( 'Send Test Email', 'hocwp-theme' )
			),
			'button_type' => 'button',
			'text'        => __( 'Send Test Email', 'hocwp-theme' )
		)
	)
);

$tab->add_field( 'change_admin_email', '', 'buttons', $args, 'string', 'administrative_email' );

$args = array(
	'title'       => __( 'Change URL', 'hocwp-theme' ),
	'description' => __( 'Change all old URLs in database into new URL.', 'hocwp-theme' )
);

$tab->add_section( 'change_url', $args );

$tab->add_field( 'old_url', __( 'Old URL', 'hocwp-theme' ), 'input', array(), 'string', 'change_url' );
$tab->add_field( 'new_url', __( 'New URL', 'hocwp-theme' ), 'input', array(), 'string', 'change_url' );

$args = array(
	'attributes'  => array(
		'data-ajax-button'     => 1,
		'data-message'         => __( 'All URLs have been changed successfully!', 'hocwp-theme' ),
		'data-confirm-message' => __( 'Please make a backup before you change site URL.', 'hocwp-theme' ),
		'data-change-url'      => 1,
		'aria-label'           => __( 'Change URL', 'hocwp-theme' )
	),
	'button_type' => 'button'
);

$tab->add_field( 'submit_change_url', '', 'button', $args, 'string', 'change_url' );

$args = array(
	'title'       => __( 'Delete Cache', 'hocwp-theme' ),
	'description' => __( 'You need to delete site cache and CDN cache to see update.', 'hocwp-theme' )
);

$tab->add_section( 'delete_cache', $args );

$args = array(
	'class'       => 'regular-text',
	'description' => __( 'If you enter a API Token, you do not need to provide your API Key and email address below.', 'hocwp-theme' )
);

$tab->add_field( 'cloudflare_api_token', __( 'Cloudflare API Token', 'hocwp-theme' ), 'input', $args, 'string', 'delete_cache' );

$args['description'] = __( 'If you are not using API Token then you must provide API Key and email address.', 'hocwp-theme' );

$tab->add_field( 'cloudflare_api_key', __( 'Cloudflare API Key', 'hocwp-theme' ), 'input', $args, 'string', 'delete_cache' );

$args['type'] = 'email';

$tab->add_field( 'cloudflare_user_email', __( 'Cloudflare User Email', 'hocwp-theme' ), 'input', $args, 'string', 'delete_cache' );

unset( $args['description'], $args['type'] );

$tab->add_field( 'cloudflare_account_id', __( 'Cloudflare Account ID', 'hocwp-theme' ), 'input', $args, 'string', 'delete_cache' );
$tab->add_field( 'cloudflare_zone_id', __( 'Cloudflare Zone ID', 'hocwp-theme' ), 'input', $args, 'string', 'delete_cache' );
$tab->add_field( 'cloudflare_domain', __( 'Cloudflare Domain', 'hocwp-theme' ), 'input', $args, 'string', 'delete_cache' );

$args = array(
	'attributes'  => array(
		'data-ajax-button'  => 1,
		'data-message'      => __( 'All cache files have been deleted successfully!', 'hocwp-theme' ),
		'data-delete-cache' => 1,
		'aria-label'        => __( 'Delete', 'hocwp-theme' )
	),
	'button_type' => 'button',
	'text'        => __( 'Delete', 'hocwp-theme' )
);

$tab->add_field( 'submit_delete_cache', '', 'button', $args, 'string', 'delete_cache' );

$args = array(
	'title'       => __( 'Import & Export', 'hocwp-theme' ),
	'description' => __( 'Import and export theme settings or any options in database.', 'hocwp-theme' )
);

$tab->add_section( 'import_export', $args );

$value = HOCWP_Theme()->get_options();

$args = array(
	'attributes'  => array(
		'readonly' => 'readonly'
	),
	'value'       => $value,
	'description' => __( 'Copy the setting value and fill in the data entry box to import into the database when needed.', 'hocwp-theme' )
);

$tab->add_field( 'theme_settings', __( 'Theme Settings', 'hocwp-theme' ), 'textarea', $args, 'string', 'import_export' );

$args = array(
	'fields' => array(
		'option_name'   => array(
			'callback' => 'input',
			'title'    => __( 'Option name:', 'hocwp-theme' )
		),
		'inlines_field' => array(
			'callback' => 'inline_fields',
			'args'     => array(
				'fields' => array(
					'fetch'  => array(
						'callback' => 'button',
						'args'     => array(
							'attributes'  => array(
								'data-ajax-button'     => 1,
								'data-empty-message'   => __( 'Please enter option name to fetch value.', 'hocwp-theme' ),
								'data-message'         => __( 'Data has been fetched!', 'hocwp-theme' ),
								'data-confirm-message' => __( 'Please make a backup before you do this action.', 'hocwp-theme' ),
								'data-fetch'           => 1,
								'aria-label'           => __( 'Fetch', 'hocwp-theme' )
							),
							'button_type' => 'button',
							'text'        => __( 'Fetch', 'hocwp-theme' )
						)
					),
					'export' => array(
						'callback' => 'button',
						'args'     => array(
							'attributes'  => array(
								'data-ajax-button'     => 1,
								'data-message'         => __( 'Settings have been exported!', 'hocwp-theme' ),
								'data-confirm-message' => __( 'Please make a backup before you do this action.', 'hocwp-theme' ),
								'data-export'          => 1,
								'aria-label'           => __( 'Export', 'hocwp-theme' )
							),
							'button_type' => 'button',
							'text'        => __( 'Export', 'hocwp-theme' )
						)
					)
				)
			)
		)
	)
);

$tab->add_field( 'exports', __( 'Export', 'hocwp-theme' ), 'fields', $args, 'string', 'import_export' );

$args = array(
	'fields' => array(
		'option_name'   => array(
			'callback' => 'input',
			'title'    => __( 'Option name:', 'hocwp-theme' )
		),
		'option_value'  => array(
			'callback' => 'textarea',
			'title'    => __( 'Option value:', 'hocwp-theme' ),
			'args'     => array(
				'class' => 'widefat'
			)
		),
		'inlines_field' => array(
			'callback' => 'inline_fields',
			'args'     => array(
				'fields' => array(
					'load_settings' => array(
						'callback' => 'button',
						'args'     => array(
							'attributes'  => array(
								'data-ajax-button'     => 1,
								'data-message'         => __( 'Settings have been loaded!', 'hocwp-theme' ),
								'data-confirm-message' => __( 'Please make a backup before you do this action.', 'hocwp-theme' ),
								'data-load-settings'   => 1,
								'aria-label'           => __( 'Load Settings', 'hocwp-theme' )
							),
							'button_type' => 'button',
							'type'        => 'default',
							'text'        => __( 'Load settings', 'hocwp-theme' )
						)
					),
					'import'        => array(
						'callback' => 'button',
						'args'     => array(
							'attributes'  => array(
								'data-ajax-button'     => 1,
								'data-message'         => __( 'Settings have been imported!', 'hocwp-theme' ),
								'data-confirm-message' => __( 'Please make a backup before you do this action.', 'hocwp-theme' ),
								'data-import'          => 1,
								'aria-label'           => __( 'Import', 'hocwp-theme' )
							),
							'button_type' => 'button',
							'text'        => __( 'Import', 'hocwp-theme' )
						)
					),
					'input_file'    => array(
						'callback' => 'input',
						'args'     => array(
							'attributes' => array(
								'id'    => 'choose-setting-file',
								'style' => 'display:none'
							),
							'type'       => 'file'
						)
					)
				)
			)
		)
	)
);

$tab->add_field( 'imports', __( 'Import', 'hocwp-theme' ), 'fields', $args, 'string', 'import_export' );

$args = array(
	'title'       => __( 'Database Optimize', 'hocwp-theme' ),
	'description' => __( 'Update database, remove transients and more.', 'hocwp-theme' )
);

$tab->add_section( 'database_optimize', $args );

$args = array(
	'description' => __( 'Enter the transient name you want to delete, all transients will be deleted if this field is empty.', 'hocwp-theme' )
);

$tab->add_field( 'remove_transient', __( 'Remove Transients', 'hocwp-theme' ), 'input', $args, 'string', 'database_optimize' );

$args = array(
	'attributes'  => array(
		'data-ajax-button'      => 1,
		'data-message'          => __( 'Transients have been removed!', 'hocwp-theme' ),
		'data-confirm-message'  => __( 'Please make a backup before you do this action.', 'hocwp-theme' ),
		'data-delete-transient' => 1,
		'aria-label'            => __( 'Delete', 'hocwp-theme' )
	),
	'button_type' => 'button',
	'text'        => __( 'Delete', 'hocwp-theme' )
);

$tab->add_field( 'delete_transient', '', 'button', $args, 'string', 'database_optimize' );

$args = array(
	'title'       => __( 'Vietnamese Administrative Boundaries', 'hocwp-theme' ),
	'description' => __( 'Import information about Vietnamese administrative boundaries into the database automatically.', 'hocwp-theme' )
);

$tab->add_section( 'vn_administrative_boundaries', $args );

$args = array(
	'type' => 'checkbox',
	'text' => __( 'Import the administrative boundary at the district level?', 'hocwp-theme' )
);

$tab->add_field( 'district', __( 'District', 'hocwp-theme' ), 'input', $args, 'boolean', 'vn_administrative_boundaries' );

$args = array(
	'type' => 'checkbox',
	'text' => __( 'Import the administrative boundary at the commune level?', 'hocwp-theme' )
);

$tab->add_field( 'commune', __( 'Commune', 'hocwp-theme' ), 'input', $args, 'boolean', 'vn_administrative_boundaries' );

$taxs = get_taxonomies( array(), 'objects' );

$options = array(
	'' => __( 'Choose taxonomy', 'hocwp-theme' )
);

foreach ( $taxs as $tax ) {
	if ( $tax instanceof WP_Taxonomy ) {
		$options[ $tax->name ] = sprintf( '%s (%s)', $tax->labels->singular_name, $tax->name );
	}
}

$args = array(
	'options' => $options
);

$tab->add_field( 'ab_taxonomy', __( 'Taxonomy', 'hocwp-theme' ), 'select', $args, 'string', 'vn_administrative_boundaries' );

$args = array(
	'attributes'  => array(
		'data-ajax-button' => 1,
		'data-message'     => __( 'Data has been imported successfully!', 'hocwp-theme' ),
		'data-import-ab'   => 1,
		'aria-label'       => __( 'Import', 'hocwp-theme' )
	),
	'button_type' => 'button',
	'text'        => __( 'Import', 'hocwp-theme' )
);

$tab->add_field( 'import_ab', '', 'button', $args, 'string', 'vn_administrative_boundaries' );

$tab->load_script( 'jquery' );
$tab->load_script( 'hocwp-theme' );
$tab->load_script( 'hocwp-theme-ajax-button' );

function hocwp_theme_setting_page_administration_tools_script() {
	wp_enqueue_script( 'hocwp-theme-administration-tools', HOCWP_Theme()->core_url . '/js/admin-administration-tools.js', array( 'jquery' ), false, true );
}

add_action( 'hocwp_theme_admin_setting_page_' . $tab->name . '_scripts', 'hocwp_theme_setting_page_administration_tools_script' );