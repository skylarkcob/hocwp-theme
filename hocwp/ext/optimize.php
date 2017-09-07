<?php
/*
 * Name: Optimize
 * Description: Optimize your site for better performance.
 */
$load = apply_filters( 'hocwp_theme_load_extension_optimize', hocwp_theme_is_extension_active( __FILE__ ) );
if ( ! $load ) {
	return;
}

function hocwp_theme_force_inline_css_and_js() {
	if ( ! HOCWP_THEME_DEVELOPING ) {
		global $wp_styles;
		$queues = isset( $wp_styles->queue ) ? $wp_styles->queue : '';
		if ( is_array( $queues ) ) {
			foreach ( $queues as $queue ) {
				if ( isset( $wp_styles->registered[ $queue ] ) ) {
					$data = $wp_styles->registered[ $queue ];
					$src  = $data->src;
					if ( false != strpos( $src, 'themes/hocwp-theme' ) ) {
						$name = basename( $src );
						if ( 'style.css' == $name ) {
							$src                             = HOCWP_THEME_CORE_URL . '/css/default' . HOCWP_THEME_CSS_SUFFIX;
							$src                             = add_query_arg( array(
								'ver'  => $GLOBALS['wp_version'],
								'test' => 't'
							), $src );
							$data->src                       = $src;
							$wp_styles->registered[ $queue ] = $data;
						}
						$filesystem = HOCWP_Theme_Utility::filesystem();
						$code       = $filesystem->get_contents( $src );
					}
				}
			}
		}
	}
}

//add_action( 'wp_enqueue_scripts', 'hocwp_theme_force_inline_css_and_js', 99 );