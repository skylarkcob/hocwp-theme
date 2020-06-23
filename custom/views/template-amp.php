<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $wp_query;
?>
<section>
	<div class="page-box">
		<?php
		if ( isset( $wp_query->query['menu-amp'] ) ) {
			$cse = HT_Options()->get_tab( 'search_engine_id', '', 'social' );

			if ( empty( $cse ) ) {
				$action = home_url( '/' );
				$name   = 's';
			} else {
				$action = 'https://cse.google.com/cse';
				$name   = 'q';
			}
			?>
			<div class="amp-menu">
				<div class="search-box">
					<form method="GET" action="<?php echo esc_attr( $action ); ?>" target="_blank">
						<input type="text" name="<?php echo esc_attr( $name ); ?>"
						       placeholder="<?php esc_attr_e( 'Search', 'hocwp-theme' ); ?>">
						<input type="hidden" value="<?php echo esc_attr( $cse ); ?>" name="cx">
						<input type="hidden" value="UTF-8" name="ie">
						<button type="submit"></button>
					</form>
				</div>
				<div class="menu-box">
					<?php
					if ( ! class_exists( 'HOCWP_Theme_Walker_Nav_Menu_AMP' ) ) {
						require_once HOCWP_Theme()->core_path . '/inc/class-hocwp-theme-walker-nav-menu-amp.php';
					}

					wp_nav_menu( array(
						'theme_location' => 'amp',
						'walker'         => new HOCWP_Theme_Walker_Nav_Menu_AMP()
					) );
					?>
				</div>
			</div>
			<?php
		} else {
			if ( have_posts() ) {

			} else {

			}
		}
		?>
	</div>
</section>
