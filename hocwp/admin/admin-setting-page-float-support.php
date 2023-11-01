<?php
defined( 'ABSPATH' ) || exit;

$tab = new HOCWP_Theme_Admin_Setting_Tab( 'float_support', __( 'Float Support', 'hocwp-theme' ), '<span class="dashicons dashicons-format-chat"></span>' );

$supports = array(
	'phone'    => __( 'Phone', 'hocwp-theme' ),
	'zalo'     => __( 'Zalo', 'hocwp-theme' ),
	'facebook' => __( 'Facebook', 'hocwp-theme' ),
	'maps'     => __( 'Maps', 'hocwp-theme' ),
	'twitter'  => __( 'Twitter', 'hocwp-theme' ),
	'email'    => __( 'Email', 'hocwp-theme' )
);

$supports = apply_filters( 'hocwp_theme_float_supports', $supports );

$sort_order = $tab->get_value( 'sort_order' );

if ( ! empty( $sort_order ) ) {
	$sort_order = json_decode( $sort_order );

	if ( HT()->array_has_value( $sort_order ) ) {
		$tmp      = $supports;
		$supports = array();

		foreach ( $sort_order as $key ) {
			if ( isset( $tmp[ $key ] ) ) {
				$supports[ $key ] = $tmp[ $key ];
				unset( $tmp[ $key ] );
			}
		}

		if ( HT()->array_has_value( $tmp ) ) {
			$supports += $tmp;
		}
	}
}

foreach ( $supports as $key => $label ) {
	$args = array(
		'fields' => array(
			$key . '_url'        => array(
				'callback' => 'input',
				'title'    => __( 'URL:', 'hocwp-theme' )
			),
			$key . '_text'       => array(
				'callback' => 'input',
				'title'    => __( 'Text:', 'hocwp-theme' )
			),
			$key . '_icon'       => array(
				'callback' => 'input',
				'title'    => __( 'Icon:', 'hocwp-theme' )
			),
			$key . '_icon_image' => array(
				'callback' => 'image_upload',
				'title'    => __( 'Icon Image:', 'hocwp-theme' )
			),
			$key . '_vibrate'    => array(
				'callback'      => 'input',
				'callback_args' => array(
					'label' => __( 'Apply vibrate effect.', 'hocwp-theme' ),
					'type'  => 'checkbox'
				)
			),
			$key . '_earthquake' => array(
				'callback'      => 'input',
				'callback_args' => array(
					'label' => __( 'Apply earthquake effect.', 'hocwp-theme' ),
					'type'  => 'checkbox'
				)
			)
		)
	);

	$tab->add_field( $key, $label, 'fields', $args, 'array' );
}

$tab->add_section( 'settings', array(
	'title'       => __( 'Settings', 'hocwp-theme' ),
	'description' => __( 'Customize style for front-end displaying.', 'hocwp-theme' )
) );

$args = array(
	'options' => array(
		'vertical'   => __( 'Vertical', 'hocwp-theme' ),
		'horizontal' => __( 'Horizontal', 'hocwp-theme' )
	),
	'class'   => 'regular-text'
);

$tab->add_field( 'style', __( 'Style', 'hocwp-theme' ), 'select', $args, 'string', 'settings' );

$args['options'] = array(
	'left'          => __( 'Left', 'hocwp-theme' ),
	'middle_left'   => __( 'Middle Left', 'hocwp-theme' ),
	'top_left'      => __( 'Top Left', 'hocwp-theme' ),
	'right'         => __( 'Right', 'hocwp-theme' ),
	'middle_right'  => __( 'Middle Right', 'hocwp-theme' ),
	'top_right'     => __( 'Top Right', 'hocwp-theme' ),
	'middle_bottom' => __( 'Middle Bottom', 'hocwp-theme' ),
	'middle_top'    => __( 'Middle Top', 'hocwp-theme' )
);

$tab->add_field( 'position', __( 'Position', 'hocwp-theme' ), 'select', $args, 'string', 'settings' );

$tab->add_field( 'margin', __( 'Margin', 'hocwp-theme' ), 'input', array( 'class' => 'regular-text' ), 'string', 'settings' );
$tab->add_field( 'padding', __( 'Padding', 'hocwp-theme' ), 'input', array( 'class' => 'regular-text' ), 'string', 'settings' );

$tab->add_field( 'border_radius', __( 'Border Radius', 'hocwp-theme' ), 'input', array( 'class' => 'regular-text' ), 'string', 'settings' );

$tab->add_field( 'background_color', __( 'Background Color', 'hocwp-theme' ), 'color_picker', array( 'class' => 'regular-text' ), 'color', 'settings' );

$tab->add_field( 'background_image', __( 'Background Image', 'hocwp-theme' ), 'image_upload', array( 'class' => 'regular-text' ), 'id', 'settings' );

$args = array(
	'lists'    => $supports,
	'connects' => false
);

$tab->add_field( 'sort_order', __( 'Sort Order', 'hocwp-theme' ), 'sortable', $args, 'array', 'settings' );