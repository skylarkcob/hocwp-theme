<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_get_feed_items() {
	$tr_name = 'hocwp_theme_feed_items';

	if ( false === ( $feeds = get_transient( $tr_name ) ) ) {
		$url   = 'https://hocwp.net/feed/';
		$feeds = HT_Util()->get_feed_items( $url );

		if ( HT()->array_has_value( $feeds ) ) {
			set_transient( $tr_name, $feeds, DAY_IN_SECONDS );
		}
	}

	return $feeds;
}

function hocwp_theme_wp_dashboard_setup() {
	$feeds = hocwp_theme_get_feed_items();

	if ( HT()->array_has_value( $feeds ) ) {
		wp_add_dashboard_widget( 'news_from_hocwp_team', __( 'News From HocWP Team', 'hocwp-theme' ), 'hocwp_theme_news_from_hocwp_team_callback' );
	}
}

add_action( 'wp_dashboard_setup', 'hocwp_theme_wp_dashboard_setup' );

function hocwp_theme_news_from_hocwp_team_callback() {
	$feeds = hocwp_theme_get_feed_items();

	if ( HT()->array_has_value( $feeds ) ) {
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
							<a class="rsswidget"
							   href="<?php echo $feed['permalink']; ?>"
							   style="font-weight: 400" target="_blank"><?php echo $feed['title']; ?></a>
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

function hocwp_theme_wp_dashboard_setup_action() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$post_types   = get_post_types( array( 'public' => true, '_builtin' => false ) );
	$post_types[] = 'post';
	$post_types[] = 'page';

	$args = array(
		'post_type'      => $post_types,
		'posts_per_page' => - 1,
		'post_status'    => 'draft'
	);

	if ( ! current_user_can( 'delete_others_posts' ) ) {
		$args['author'] = get_current_user_id();
	}

	$query = new WP_Query( $args );

	if ( $query->have_posts() ) {
		wp_add_dashboard_widget(
			'hocwp_theme_dashboard_widget_recent_draft',
			__( 'Recent Draft Posts', 'hocwp-theme' ),
			'hocwp_theme_dashboard_widget_recent_draft_callback',
			null,
			array( 'query' => $query )
		);
	}
}

add_action( 'wp_dashboard_setup', 'hocwp_theme_wp_dashboard_setup_action' );

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
					<a href="<?php echo $edit_url; ?>"
					   aria-label="<?php printf( __( 'Edit %s', 'hocwp-theme' ), get_the_title() ); ?>"><?php the_title(); ?></a>
					<time datetime="<?php echo get_the_date( 'c' ); ?>"><?php echo get_the_date( 'F j, Y' ); ?></time>
					<a href="<?php echo $edit_url; ?>">
						<button class="button-link">
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