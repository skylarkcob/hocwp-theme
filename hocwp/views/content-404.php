<div class="section 404">
    <div class="container">
		<?php do_action( 'hocwp_theme_content_area_before' ); ?>
        <section class="error-404 not-found">
			<?php
			$html = apply_filters( 'hocwp_theme_404_content', '' );
			if ( ! empty( $html ) ) {
				echo $html;
			} else {
				?>
                <header class="page-header">
                    <h1 class="page-title"><?php esc_html_e( 'Oops! That page can&rsquo;t be found.', 'hocwp-theme' ); ?></h1>
                </header>
                <!-- .page-header -->

                <div class="page-content entry-content">
                    <p><?php esc_html_e( 'It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'hocwp-theme' ); ?></p>

					<?php
					get_search_form();

					the_widget( 'WP_Widget_Recent_Posts' );
					?>

                    <div class="widget widget_categories">
                        <h2 class="widget-title"><?php esc_html_e( 'Most Used Categories', 'hocwp-theme' ); ?></h2>
                        <ul>
							<?php
							wp_list_categories( array(
								'orderby'    => 'count',
								'order'      => 'DESC',
								'show_count' => 1,
								'title_li'   => '',
								'number'     => 10,
							) );
							?>
                        </ul>
                    </div>
                    <!-- .widget -->

					<?php

					/* translators: %1$s: smiley */
					$archive_content = '<p>' . sprintf( esc_html__( 'Try looking in the monthly archives. %1$s', 'hocwp-theme' ), convert_smilies( ':)' ) ) . '</p>';
					the_widget( 'WP_Widget_Archives', 'dropdown=1', "after_title=</h2>$archive_content" );

					the_widget( 'WP_Widget_Tag_Cloud' );
					?>

                </div>
                <!-- .page-content -->
				<?php
			}
			do_action( 'hocwp_theme_content_404' );
			?>
        </section>
        <!-- .error-404 -->
		<?php
		do_action( 'hocwp_theme_content_area_after' );
		get_sidebar();
		?>
    </div>
</div>