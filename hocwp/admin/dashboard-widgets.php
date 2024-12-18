<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_get_feed_items() {
	$tr_name = 'hocwp_theme_feed_items';

	if ( false === ( $feeds = get_transient( $tr_name ) ) ) {
		$url   = 'https://hocwp.net/feed/';
		$feeds = ht_util()->get_feed_items( $url );

		if ( ht()->array_has_value( $feeds ) ) {
			set_transient( $tr_name, $feeds, DAY_IN_SECONDS );
		}
	}

	return $feeds;
}

function hocwp_theme_news_from_hocwp_team_callback() {
	$feeds = hocwp_theme_get_feed_items();

	if ( ht()->array_has_value( $feeds ) ) {
		?>
        <div class="wordpress-news">
            <div class="rss-widget">
                <ul>
					<?php
					$count = count( $feeds );
					foreach ( $feeds as $key => $feed ) {
						$date      = $feed['date'];
						$timestamp = strtotime( $date );
						$style     = 'border-bottom: 1px dotted #eee;padding-bottom: 15px;';

						if ( $key == ( $count - 1 ) ) {
							$style = '';
						}
						?>
                        <li style="<?php echo $style; ?>">
                            <a class="rsswidget" href="<?php echo $feed['permalink']; ?>" style="font-weight: 400"
                               target="_blank"
                               title="<?php echo esc_attr( $feed['title'] ); ?>"><?php echo $feed['title']; ?></a>
                            <time
                                    datetime="<?php echo mysql2date( 'D, d M Y H:i:s +0000', $date, false ); ?>"><?php echo date_i18n( get_option( 'date_format' ), $timestamp ); ?></time>
                        </li>
						<?php
					}
					?>
                </ul>
            </div>
        </div>
		<?php
	}
}

function hocwp_theme_dashboard_widget_recent_draft_callback( $tmp, $args ) {
	$query = isset( $args['args']['query'] ) ? $args['args']['query'] : null;

	if ( ! ( $query instanceof WP_Query ) || ! $query->have_posts() ) {
		return;
	}
	?>
    <ul>
		<?php
		while ( $query->have_posts() ) {
			$query->the_post();
			$edit_url = get_edit_post_link();
			?>
            <li>
                <div class="draft-title">
                    <a href="<?php echo $edit_url; ?>" title="<?php the_title(); ?>"
                       aria-label="<?php printf( __( 'Edit %s', 'hocwp-theme' ), get_the_title() ); ?>"><?php the_title(); ?></a>
                    <time datetime="<?php echo get_the_date( DATE_W3C ); ?>"><?php echo get_the_date( 'F j, Y' ); ?></time>
                    <a href="<?php echo $edit_url; ?>" title="<?php esc_attr_e( 'Edit', 'hocwp-theme' ); ?>">
                        <button class="button-link" aria-label="<?php esc_attr_e( 'Edit', 'hocwp-theme' ); ?>"
                                title="<?php esc_attr_e( 'Edit', 'hocwp-theme' ); ?>">
                            <span class="dashicons dashicons-edit"></span>
                        </button>
                    </a>
                </div>
            </li>
			<?php
		}

		wp_reset_postdata();
		?>
    </ul>
	<?php
}