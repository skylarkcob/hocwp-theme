<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header();
?>
	<div class="confirm-goout" style="margin:20px 0;">
		<div class="container">
			<div style="text-align: center;">
				<?php
				$goto   = isset( $_GET['goto'] ) ? $_GET['goto'] : '';
				$domain = HT()->get_domain_name( home_url() );
				$msg    = __( 'This url is not belong to %s, please choose these options below to continue.', 'hocwp-theme' );
				do_action( 'hocwp_theme_goout_before' )
				?>
				<p><?php printf( $msg, $domain ); ?></p>
				<?php do_action( 'hocwp_theme_goout' ) ?>
				<p>
					<button class="btn btn-success"
					        data-url="<?php echo esc_url( $goto ); ?>"
					        onclick="return goTo(this);"><?php _e( 'Continue to this url', 'hocwp-theme' ); ?></button>
					<button class="btn btn-danger" onclick="goBack()"><?php _e( 'Go back', 'hocwp-theme' ); ?></button>
				</p>
				<?php do_action( 'hocwp_theme_goout_after' ) ?>
				<script>
					function goTo(data) {
						if ("" !== data.getAttribute("data-url")) {
							window.location.href = data.getAttribute("data-url");
						}
					}

					function goBack() {
						window.history.back();
					}
				</script>
			</div>
		</div>
	</div>
<?php get_footer(); ?>