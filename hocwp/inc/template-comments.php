<?php
function hocwp_theme_module_comments_area() {
	hocwp_theme_load_views( 'module-comments-area' );
}

add_action( 'hocwp_theme_module_comments_area', 'hocwp_theme_module_comments_area' );

function hocwp_theme_wp_list_comments_args_filter( $args ) {
	$args['avatar_size'] = $GLOBALS['hocwp_theme']->options['discussion']['avatar_size'];

	return $args;
}

add_filter( 'wp_list_comments_args', 'hocwp_theme_wp_list_comments_args_filter' );

function hocwp_theme_change_default_avatar( $avatar, $id_or_email, $size, $default, $alt, $args ) {
	if ( $id_or_email instanceof WP_Comment ) {

	}

	return $avatar;
}

add_filter( 'get_avatar', 'hocwp_theme_change_default_avatar', 10, 6 );

function hocwp_theme_comments_template( $args = array() ) {
	$defaults = array(
		'post_id'        => get_the_ID(),
		'comment_system' => 'default',
		'tabs'           => array(
			array(
				'href' => 'facebook',
				'text' => 'Facebook'
			),
			array(
				'href' => 'google',
				'text' => 'Google+'
			),
			array(
				'href' => 'wordpress',
				'text' => 'WordPress'
			),
			array(
				'href' => 'disqus',
				'text' => 'Disqus'
			)
		)
	);
	$post_id  = isset( $args['post_id'] ) ? $args['post_id'] : get_the_ID();
	$obj      = get_post( $post_id );
	if ( ! ( $obj instanceof WP_Post ) ) {
		return;
	}
	if ( comments_open( $post_id ) || get_comments_number( $post_id ) ) {
		switch ( $comment_system ) {
			case 'tabber':
				break;
			case 'facebook':
				break;
			case 'gplus':
			case 'google':
				break;
			case 'default_and_facebook':
				break;
			default:
				comments_template();
		}
	}
}

function hocwp_theme_comments_template_facebook( $args = array() ) {
	$defaults    = array(
		'colorscheme'  => 'light',
		'href'         => '',
		'mobile'       => '',
		'num_posts'    => 10,
		'order_by'     => 'social',
		'width'        => '100%',
		'loading_text' => __( 'Loading...', 'hocwp-theme' )
	);
	$args        = wp_parse_args( $args, $defaults );
	$args        = apply_filters( 'hocwp_theme_facebook_comment_args', $args );
	$colorscheme = $args['colorscheme'];
	$href        = $args['href'];
	if ( empty( $href ) ) {
		if ( is_single() || is_page() || is_singular() ) {
			$href = get_the_permalink();
		}
	}
	if ( empty( $href ) ) {
		$href = HOCWP_Theme_Utility::get_current_url();
	}
	$mobile       = $args['mobile'];
	$num_posts    = $args['num_posts'];
	$order_by     = $args['order_by'];
	$width        = $args['width'];
	$loading_text = $args['loading_text'];
	$div          = new HOCWP_Theme_HTML_Tag( 'div' );
	$atts         = array(
		'class'            => 'fb-comments',
		'data-colorscheme' => $colorscheme,
		'data-href'        => $href,
		'data-mobile'      => $mobile,
		'data-numposts'    => $num_posts,
		'data-order-by'    => $order_by,
		'data-width'       => $width
	);
	$div->set_attributes( $atts );
	$div->set_text( $loading_text );
	$div->output();
}

function hocwp_theme_comments_template_google() {
	?>
	<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
	<div id="google_comments"><?php _e( 'Loading...', 'hocwp-theme' ); ?></div>
	<script>
		gapi.comments.render('google_comments', {
			href: window.location,
			width: '624',
			first_party_property: 'BLOGGER',
			view_type: 'FILTERED_POSTMOD'
		});
	</script>
	<?php
}

function hocwp_theme_comments_template_disqus() {
	?>
	<div id="disqus_thread"><?php _e( 'Loading...', 'hocwp-theme' ); ?></div>
	<script>
		(function () {
			var d = document, s = d.createElement('script'), ts = +new Date();
			s.src = '//hocwp.disqus.com/embed.js';
			s.setAttribute('data-timestamp', ts.toString());
			(d.head || d.body).appendChild(s);
		})();
	</script>
	<noscript>Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript">comments powered by
			Disqus.</a></noscript>
	<?php
}