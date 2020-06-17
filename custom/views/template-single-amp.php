<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

HT_Custom()->load_module( 'ads-page-header' );

if ( have_posts() ) {
	$term = null;

	while ( have_posts() ) {
		the_post();
		$post_id = get_the_ID();
		?>
		<div class="page-row">
			<div class="container">
				<div class="page-box">
					<?php
					$terms = wp_get_post_categories( $post_id );

					if ( HT()->array_has_value( $terms ) ) {
						$term = array_shift( $terms );

						if ( HT()->is_positive_number( $term ) ) {
							$term = get_category( $term );
						}

						$tmp = $term;

						while ( $tmp instanceof WP_Term && HT()->is_positive_number( $tmp->parent ) ) {
							$tmp = get_term( $tmp->parent, $tmp->taxonomy );
						}

						if ( $tmp instanceof WP_Term ) {
							?>
							<div class="path-menus">
								<ul class="list-unstyled list-inline">
									<li class="list-inline-item active">
										<a href="<?php echo esc_attr( get_term_link( $tmp ) ); ?>"><?php echo $tmp->name; ?></a>
									</li>
								</ul>
							</div>
							<?php
						}
					}
					?>
					<div class="row content-row">
						<div class="col-md-12 col-sm-12 col-lg-9">
							<div class="inner current-post">
								<?php
								HOCWP_Theme()->add_loop_data( 'is_single', true );
								do_action( 'hocwp_theme_article_before' );
								do_action( 'hocwp_theme_the_title' );
								?>
								<div class="meta">
									<?php
									if ( 'post' == get_post_type( $post_id ) ) {
										?>
										<span class="author"><?php the_author(); ?></span>
										<?php
									}
									?>
									<span class="time"><i class="fa fa-clock-o"
									                      aria-hidden="true"></i><?php HOCWP_Theme()->the_date( '', $post_id ); ?></span>
								</div>
								<?php
								do_action( 'hocwp_theme_the_content' );

								if ( has_tag() ) {
									?>
									<p class="tags">
										<i class="fa fa-tag" aria-hidden="true"></i>
										<?php the_tags(); ?>
									</p>
									<?php
								}

								do_action( 'hocwp_theme_article_after' );
								HOCWP_Theme()->remove_loop_data( 'is_single' );
								?>
							</div>
							<div class="border-bottom-box"></div>
							<?php
							if ( $term instanceof WP_Term ) {
								$args = array(
									'post_type'      => 'post',
									'post_status'    => 'publish',
									'cat'            => $term->term_id,
									'posts_per_page' => 6,
									'post__not_in'   => array( $post_id )
								);

								$query = new WP_Query( $args );

								if ( $query->have_posts() ) {
									global $post;
									$bk    = $post;
									$lists = $query->get_posts();
									?>
									<div class="post-box same-category">
										<div class="pd-20">
											<h2 class="box-heading border-heading"><?php _e( 'Same category', 'hocwp-theme' ); ?></h2>

											<div class="row">
												<div class="col-md-8 col-sm-8">
													<div <?php post_class( 'large full-thumb' ); ?>>
														<?php
														$post = array_shift( $lists );
														setup_postdata( $post );
														the_post_thumbnail( HT_Custom()->image_sizes['same_cat_large'], array( 'post_link' => true ) );
														do_action( 'hocwp_theme_the_title', array( 'container_tag' => 'h3' ) );
														do_action( 'hocwp_theme_the_excerpt' );
														wp_reset_postdata();
														?>
													</div>
												</div>
												<div class="col-md-4 col-sm-4 sub-col">
													<div class="sub-box">
														<?php
														if ( HT()->array_has_value( $lists ) ) {
															foreach ( $lists as $post ) {
																setup_postdata( $post );
																?>
																<div <?php post_class(); ?>>
																	<?php
																	the_post_thumbnail( HT_Custom()->image_sizes['single_sub_col'], array( 'post_link' => true ) );
																	do_action( 'hocwp_theme_the_title', array( 'container_tag' => 'h3' ) );
																	?>
																	<div class="meta">
																	<span
																		class="time"><?php printf( __( '%s ago', 'hocwp-theme' ), human_time_diff( get_the_time( 'G', $post ) ) ); ?></span>
																	</div>
																</div>
																<?php
															}

															wp_reset_postdata();
														}
														?>
													</div>
												</div>
											</div>
										</div>
									</div>
									<?php
									$post = $bk;
								}
							}

							$args = array(
								'post_type'      => 'post',
								'post_status'    => 'publish',
								'posts_per_page' => 8,
								'post__not_in'   => array( $post_id )
							);

							$query = new WP_Query( $args );

							if ( $query->have_posts() ) {
								?>
								<div class="new-posts">
									<div class="pd-20">
										<h2 class="box-heading border-heading"><?php _e( 'Recent news', 'hocwp-theme' ); ?></h2>

										<div class="loop loop-posts">
											<?php
											$count = 0;

											while ( $query->have_posts() ) {
												$query->the_post();
												HT_Custom()->load_loop( 'post' );
												$count ++;

												if ( 4 == $count ) {
													$args['posts_per_page'] = 6;

													$query = HT_Query()->featured_posts( $args );

													if ( $query->have_posts() ) {
														?>
														<div class="slider-cats hot-events">
															<div class="row">
																<div class="col-xs-12 col-md-12">
																	<h2 class="box-title border-heading">
																		<span><?php _e( 'Interested posts', 'hocwp-theme' ); ?></span>
																	</h2>
																</div>
															</div>
															<div class="row loop loop-posts full-width">
																<?php
																while ( $query->have_posts() ) {
																	$query->the_post();
																	?>
																	<div <?php post_class( 'small col-md-4 col-xs-4' ); ?>>
																		<div class="inner">
																			<?php
																			the_post_thumbnail( HT_Custom()->image_sizes['archive_hot_events'], array( 'post_link' => true ) );
																			do_action( 'hocwp_theme_the_title', array( 'container_tag' => 'h3' ) );
																			?>
																		</div>
																	</div>
																	<?php
																}

																wp_reset_postdata();
																?>
															</div>
														</div>
														<?php
													}
												}
											}

											wp_reset_postdata();
											?>
										</div>
									</div>
								</div>
								<?php
							}
							?>
						</div>
					</div>
					<div class="sidebar-bottom"></div>
				</div>
			</div>
		</div>
		<?php
	}
}