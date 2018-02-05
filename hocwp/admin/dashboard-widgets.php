<?php
function hocwp_theme_wp_dashboard_setup_action() {
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