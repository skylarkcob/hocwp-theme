<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_get_header() {
	?>
    <!DOCTYPE html>
	<?php hocwp_theme_html_tag( 'html', '', get_language_attributes() ); ?>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
		<?php
		$responsive = apply_filters( 'hocwp_theme_enable_responsive', true );

		if ( $responsive ) {
			echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
		}

		wp_head();
		?>
    </head>
	<?php
	hocwp_theme_html_tag( 'body' ); // Open body

	if ( ! ht_util()->is_vr_theme() ) {
		wp_body_open();
	}
}

function hocwp_theme_get_footer() {
	if ( ! ht_util()->is_vr_theme() ) {
		wp_footer();
	}

	hocwp_theme_html_tag_close( 'body' ); // Close body
	hocwp_theme_html_tag_close( 'html' ); // Close html
}

function hocwp_theme_load_template( $_template_file, $include_once = false ) {
	if ( ht()->array_has_value( $_template_file ) ) {
		foreach ( $_template_file as $file ) {
			if ( ! ht()->string_contain( $file, '.php' ) ) {
				$file .= '.php';
			}

			if ( ht()->is_file( $file ) ) {
				$file = apply_filters( 'hocwp_theme_pre_load_template', $file );
				load_template( $file, $include_once );
				break;
			}
		}

		unset( $file );

		return;
	}

	if ( ! ht()->string_contain( $_template_file, '.php' ) ) {
		$_template_file .= '.php';
	}

	if ( ht()->is_file( $_template_file ) ) {
		$file = apply_filters( 'hocwp_theme_pre_load_template', $_template_file );
		load_template( $file, $include_once );
		unset( $file );
	}
}

function hocwp_theme_load_views( $name ) {
	$name = HOCWP_Theme_Sanitize::extension( $name, 'php' );

	if ( ! ht()->is_file( $name ) ) {
		$name = HOCWP_THEME_CORE_PATH . '/views/' . $name;
	}

	hocwp_theme_load_template( $name );
}

function hocwp_theme_load_custom_template( $name ) {
	$name = HOCWP_Theme_Sanitize::extension( $name, 'php' );

	if ( is_string( $name ) && ! ht()->is_file( $name ) ) {
		$name = ht_custom()->get_path( 'views/' . $name );
	} elseif ( ht()->array_has_value( $name ) ) {
		foreach ( (array) $name as $key => $single_name ) {
			if ( is_string( $single_name ) && ! ht()->is_file( $single_name ) ) {
				$name[ $key ] = ht_custom()->get_path( 'views/' . $single_name );
			}
		}
	}

	hocwp_theme_load_template( $name );
}

function hocwp_theme_load_custom_module( $name ) {
	if ( ht()->array_has_value( $name ) ) {
		foreach ( $name as $key => $single_name ) {
			$name[ $key ] = HOCWP_Theme_Sanitize::prefix( $single_name, 'module' );
		}
	} else {
		$name = HOCWP_Theme_Sanitize::prefix( $name, 'module' );
	}

	hocwp_theme_load_custom_template( $name );
}

function hocwp_theme_load_custom_loop( $name ) {
	$name = HOCWP_Theme_Sanitize::prefix( $name, 'loop' );
	hocwp_theme_load_custom_template( $name );
}

function hocwp_theme_load_template_none() {
	get_template_part( 'template-parts/content', 'none' );
}

function hocwp_theme_load_content_none() {
	hocwp_theme_load_template_none();
}

function hocwp_theme_load_content_404() {
	get_template_part( 'hocwp/views/content', '404' );
}

function hocwp_theme_get_inline_css() {
	return apply_filters( 'hocwp_theme_inline_css', '' );
}