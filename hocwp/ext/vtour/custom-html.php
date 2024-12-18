<?php
defined( 'ABSPATH' ) || exit;

if ( ht_util()->is_vr_theme() ) {
	do_action( 'hocwp_theme_site_branding' );
	do_action( 'hocwp_theme_main_menu' );

	$contact = ht_options()->get_general( 'contact' );

	if ( ! empty( $contact ) ) {
		$header = $contact['header'] ?? '';
		$id     = $contact['post_id'] ?? '';
		$obj    = get_post( $id );

		if ( $obj instanceof WP_Post ) {
			$class = 'popup-contact';

			if ( has_post_thumbnail( $obj ) ) {
				$class .= ' has-thumbnail';
			}
			?>
            <div id="contactPopup" class="<?php echo esc_attr( $class ); ?>" style="display: none">
                <div class="d-flex cols">
					<?php
					if ( has_post_thumbnail( $obj ) ) {
						$url = get_the_post_thumbnail_url( $obj, 'full' );
						?>
                        <div class="banner lozad"
                             data-background-image="<?php echo esc_url( $url ); ?>">
                        </div>
						<?php
					}
					?>
                    <div class="details">
						<?php
						if ( ! empty( $header ) ) {
							$header = ht_frontend()->apply_the_content( $header );
							$header = wpautop( $header );
							?>
                            <div class="info clearfix">
								<?php echo $header; ?>
                            </div>
							<?php
						}
						?>
                        <div class="entry-content">
							<?php
							$content = apply_filters( 'the_content', $obj->post_content, $id );
							echo $content;
							?>
                        </div>
                    </div>
                </div>
                <a id="contact" data-fancybox data-src="#contactPopup" href="javascript:"></a>
            </div>
			<?php
		}
	}

	$logo = ht_options()->get_general( 'right_logo' );

	if ( ! empty( $logo ) ) {
		$link  = $logo['link'] ?? '';
		$image = $logo['image'] ?? '';

		if ( ht_media()->exists( $image ) ) {
			$img = wp_get_attachment_image( $image, 'full' );

			if ( ! empty( $link ) ) {
				$img = sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $link ), $img );
			}
			?>
            <div class="right-logo">
				<?php echo $img; ?>
            </div>
			<?php
		}
	}
	?>
    <div id="tourTools" class="menu-toggle">
        <div class="control-menu">
			<?php $url = hte_vr()->folder_url . '/images/menutoggle.png'; ?>
            <img src="<?php echo esc_url( $url ); ?>" class="open-menu active" data-action="open_menu"
                 alt="<?php esc_attr_e( 'Open menu tools', 'hocwp-theme' ); ?>">
			<?php $url = hte_vr()->folder_url . '/images/close.png'; ?>
            <img src="<?php echo esc_url( $url ); ?>" class="close-menu" data-action="close_menu"
                 alt="<?php esc_attr_e( 'Close menu tools', 'hocwp-theme' ); ?>">
        </div>
        <div class="control-sound">
			<?php $url = hte_vr()->folder_url . '/images/soundon.png'; ?>
            <img src="<?php echo esc_url( $url ); ?>" class="sound-on active" data-action="sound_on"
                 alt="<?php esc_attr_e( 'Enable sound', 'hocwp-theme' ); ?>">
			<?php $url = hte_vr()->folder_url . '/images/soundoff.png'; ?>
            <img src="<?php echo esc_url( $url ); ?>" class="sound-of" data-action="sound_off"
                 alt="<?php esc_attr_e( 'Disable sound', 'hocwp-theme' ); ?>">
        </div>
        <div class="control-rotate">
			<?php $url = hte_vr()->folder_url . '/images/rotateon.png'; ?>
            <img src="<?php echo esc_url( $url ); ?>" class="rotate-on active" data-action="rotate_on"
                 alt="<?php esc_attr_e( 'Start auto tour', 'hocwp-theme' ); ?>">
			<?php $url = hte_vr()->folder_url . '/images/rotateoff.png'; ?>
            <img src="<?php echo esc_url( $url ); ?>" class="rotate-off" data-action="rotate_off"
                 alt="<?php esc_attr_e( 'Stop auto tour', 'hocwp-theme' ); ?>">
        </div>
        <div class="control-fullscreen">
			<?php $url = hte_vr()->folder_url . '/images/fullscreen.png'; ?>
            <img src="<?php echo esc_url( $url ); ?>" class="enter-full-screen active" data-action="full_screen"
                 alt="<?php esc_attr_e( 'Enter full screen mode', 'hocwp-theme' ); ?>">
			<?php $url = hte_vr()->folder_url . '/images/exitfullscreen.png'; ?>
            <img src="<?php echo esc_url( $url ); ?>" class="exit-full-screen" data-action="exit_full_screen"
                 alt="<?php esc_attr_e( 'Exit full screen mode', 'hocwp-theme' ); ?>">
        </div>
    </div>
	<?php $url = hte_vr()->folder_url . '/images/navigator.png'; ?>
    <img src="<?php echo esc_url( $url ); ?>" class="toggle-hotspots" data-action="toggle_hotspots"
         alt="<?php esc_attr_e( 'Show or hide hotspots', 'hocwp-theme' ); ?>">
	<?php
	$hotline = ht_options()->get_general( 'hotline' );
	$icon    = ht_options()->get_general( 'hotline_icon' );

	if ( ! empty( $hotline ) && ! empty( $icon ) ) {
		$phone = ht_sanitize()->phone( $hotline );
		?>
        <div class="hotline-box">
            <div class="inner">
                <a href="tel:<?php echo esc_attr( $phone ); ?>">
					<?php echo wp_get_attachment_image( $icon, 'full' ); ?>
                    <span><?php echo $hotline; ?></span>
                </a>
            </div>
        </div>
		<?php
	}

	// Play background music
	$music = ht_options()->get_general( 'bg_music' );

	ht_vr()->audio_html( $music, 'bgPlayer', 10000 );
	?>
    <div class="loading-cover loading-mirror manual"></div>
	<?php
	$skip = $_GET['skip_intro'] ?? '';

	if ( ! $skip ) {
		// Show intro image and sound
		$intro = ht_options()->get_general( 'intro_image' );

		if ( ht_media()->exists( $intro ) ) {
			?>
            <div class="loading-cover intro-cover manual">
                <div class="overlay" data-mode="dark"></div>
                <div class="inner">
                    <span class="close-box"><?php _e( '[ X ] Experience now !!!', 'hocwp-theme' ); ?></span>
                    <div class="intro-content">
						<?php
						ht_frontend()->lazy_image( 'intro-image', $intro );

						$music = ht_options()->get_general( 'intro_music' );

						ht_vr()->audio_html( $music, 'introPlayer', true );
						?>
                    </div>
                </div>
            </div>
			<?php
		}

		// Show audio on/off confirm
		?>
        <div class="loading-cover sound-confirm-cover manual">
            <div class="overlay" data-mode="dark"></div>
            <div class="inner">
                <div class="confirm-box centered">
                    <h2><?php _e( 'Enable audio?', 'hocwp-theme' ); ?></h2>
                    <hr>
                    <div class="text-center">
                        <button type="button" class="yes"><?php _e( 'Yes', 'hocwp-theme' ); ?></button>
                        <button type="button" class="no"><?php _e( 'No', 'hocwp-theme' ); ?></button>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}

	$skip = $_GET['skip_loading'] ?? '';

	if ( ! $skip ) {
		// Show loading image when users visit
		$loading = ht_options()->get_general( 'loading_image' );

		if ( ht_media()->exists( $loading ) ) {
			$bg_color = get_theme_mod( 'background_color' );

			$style = '';

			if ( ! empty( $bg_color ) ) {
				$style = 'background-color:#' . $bg_color;
			}
			?>
            <div class="loading-cover" style="<?php echo esc_attr( $style ); ?>">
                <div class="inner">
					<?php
					$text = ht_options()->get_general( 'loading_text' );
					echo wpautop( $text );
					ht_frontend()->lazy_image( 'loading-image', $loading );
					?>
                </div>
            </div>
			<?php
		}
	}
}