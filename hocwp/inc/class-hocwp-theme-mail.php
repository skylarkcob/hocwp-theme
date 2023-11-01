<?php
defined( 'ABSPATH' ) || exit;

class HOCWP_Theme_Mail {
	public static function send_user_confirm_email( $user ) {
		if ( is_numeric( $user ) ) {
			$user = get_user_by( 'ID', $user );
		}

		if ( $user instanceof WP_User ) {
			$key = get_password_reset_key( $user );
			$url = wp_login_url();

			$url = add_query_arg( array(
				'key'       => $key,
				'do_action' => 'confirm_email',
				'email'     => $user->user_email
			), $url );

			$url = wp_nonce_url( $url );

			$subject = sprintf( __( 'Welcome to %s - activate your account now', 'hocwp-theme' ), get_bloginfo( 'name' ) );

			ob_start();
			?>
            <div style="margin:0;padding:60px 0;background:#f2f2f2;font-family:Helvetica,Arial,sans-serif">
                <div style="margin:0 auto;width:100%;max-width:600px;background:#fff;border-bottom:3px solid #e8e8e8">
                    <div style="padding:40px 35px 15px">
                        <p style="color:#333;font-size:16px"><?php printf( __( 'Hi %s!', 'hocwp-theme' ), $user->display_name ); ?></p>
                        <p style="color:#333;font-size:16px"><?php printf( __( 'Thank you for creating an account on <a href="%s" target="_blank">%s</a>.', 'hocwp-theme' ), esc_url( home_url( '/' ) ), get_bloginfo( 'name' ) ); ?></p>
                        <p style="color:#333;font-size:16px"><?php _e( 'Please activate your account now, by clicking on the following link:', 'hocwp-theme' ); ?><br>
                            <strong><a href="<?php echo esc_url( $url ); ?>" target="_blank"><?php _e( 'activate my account', 'hocwp-theme' ); ?></a></strong>
                        </p>
                        <hr>
						<?php do_action( 'hocwp_theme_email_confirm_user_email_body', $user ); ?>
                        <p style="color:#333;font-size:16px;margin:40px 0 0"><?php _e( 'Thank you!', 'hocwp-theme' ); ?></p>
                    </div>
                </div>
            </div>
			<?php
			$message = ob_get_clean();
			wp_mail( $user->user_email, $subject, $message );
		}
	}
}