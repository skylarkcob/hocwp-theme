<?php
defined( 'ABSPATH' ) || exit;

get_header();

if ( ht_util()->is_vr_theme() ) {
	$url = ht_vr()->get_vr_url();

	if ( ! empty( $url ) ) {
		$current_url = ht_util()->get_current_url( true );

		$results = ht()->get_params_from_url( $current_url );

		if ( ht()->array_has_value( $results ) ) {
			// Pass all params to panorama
			$url = add_query_arg( $results, $url );
		}
		?>
        <iframe class="main-tour" src="<?php echo esc_url( $url ); ?>"></iframe>
		<?php
	}
} else {
	echo wpautop( ht_message()->theme_or_site_incorrect_config() );
}

get_footer();