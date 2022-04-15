<?php
defined( 'ABSPATH' ) || exit;

get_header();

if ( HT_Util()->is_vr_theme() ) {
	$url = HT_VR()->get_vr_url();

	if ( ! empty( $url ) ) {
		$current_url = HT_Util()->get_current_url( true );

		$results = HT()->get_params_from_url( $current_url );

		if ( HT()->array_has_value( $results ) ) {
			// Pass all params to panorama
			$url = add_query_arg( $results, $url );
		}
		?>
        <iframe class="main-tour" src="<?php echo esc_url( $url ); ?>"></iframe>
		<?php
	}
} else {
	echo wpautop( HT_Message()->theme_or_site_incorrect_config() );
}

get_footer();