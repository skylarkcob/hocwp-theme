<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$load = ( function_exists( 'hocwp_theme_load_extension_smtp' ) && hocwp_theme_load_extension_smtp() );

if ( ! $load ) {
	return;
}

function hocwp_theme_settings_page_smtp_tab( $tabs ) {
	$tabs['smtp'] = array(
		'text' => __( 'SMTP Email', 'hocwp-theme' ),
		'icon' => '<span class="dashicons dashicons-email-alt"></span>'
	);

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_smtp_tab' );

global $hocwp_theme;
if ( 'smtp' != $hocwp_theme->option->tab ) {
	return;
}

function hocwp_theme_settings_page_smtp_section() {
	$sections = array(
		array(
			'tab'         => 'smtp',
			'id'          => 'server_configuration',
			'title'       => __( 'Server Configuration', 'hocwp-theme' ),
			'description' => __( 'The configuration of SMTP servers.', 'hocwp-theme' )
		)
	);

	return $sections;
}

add_filter( 'hocwp_theme_settings_page_smtp_settings_section', 'hocwp_theme_settings_page_smtp_section' );

function hocwp_theme_settings_page_smtp_field() {
	$fields = array(
		array(
			'id'    => 'from_email',
			'title' => __( 'From Email', 'hocwp-theme' ),
			'tab'   => 'smtp',
			'args'  => array(
				'type'          => 'string',
				'label_for'     => true,
				'callback_args' => array(
					'type' => 'email'
				),
				'description'   => __( 'You can specify the email address that emails should be sent from. If you leave this blank, the default email will be used.', 'hocwp-theme' )
			)
		),
		array(
			'id'    => 'from_name',
			'title' => __( 'From Name', 'hocwp-theme' ),
			'tab'   => 'smtp',
			'args'  => array(
				'type'          => 'string',
				'label_for'     => true,
				'callback_args' => array(),
				'description'   => __( 'You can specify the name that emails should be sent from. If you leave this blank, the emails will be sent from WordPress.', 'hocwp-theme' )
			)
		),
		array(
			'id'    => 'return_path',
			'title' => __( 'Return Path', 'hocwp-theme' ),
			'tab'   => 'smtp',
			'args'  => array(
				'type'          => 'boolean',
				'label_for'     => true,
				'callback_args' => array(
					'type'  => 'checkbox',
					'label' => __( 'Set the return-path to match the From Email.', 'hocwp-theme' )
				)
			)
		),
		array(
			'section' => 'server_configuration',
			'id'      => 'host',
			'title'   => __( 'SMTP Host', 'hocwp-theme' ),
			'tab'     => 'smtp',
			'args'    => array(
				'type'          => 'string',
				'label_for'     => true,
				'callback_args' => array(
					'placeholder' => __( 'smtp.example.com', 'hocwp-theme' )
				)
			)
		),
		array(
			'section' => 'server_configuration',
			'id'      => 'port',
			'title'   => __( 'SMTP Port', 'hocwp-theme' ),
			'tab'     => 'smtp',
			'args'    => array(
				'type'          => 'string',
				'label_for'     => true,
				'callback_args' => array()
			)
		),
		array(
			'section' => 'server_configuration',
			'id'      => 'encryption',
			'title'   => __( 'Encryption', 'hocwp-theme' ),
			'tab'     => 'smtp',
			'args'    => array(
				'type'          => 'string',
				'label_for'     => true,
				'callback_args' => array(
					'type'    => 'radio',
					'options' => array(
						'none' => __( 'No encryption.', 'hocwp-theme' ),
						'ssl'  => __( 'Use SSL encryption.', 'hocwp-theme' ),
						'tls'  => __( 'Use TLS encryption.', 'hocwp-theme' )
					)
				),
				'description'   => __( 'TLS is not the same as STARTTLS. For most servers SSL is the recommended option.', 'hocwp-theme' )
			)
		),
		array(
			'section' => 'server_configuration',
			'id'      => 'username',
			'title'   => __( 'Username', 'hocwp-theme' ),
			'tab'     => 'smtp',
			'args'    => array(
				'type'          => 'string',
				'label_for'     => true,
				'callback_args' => array()
			)
		),
		array(
			'section' => 'server_configuration',
			'id'      => 'password',
			'title'   => __( 'Password', 'hocwp-theme' ),
			'tab'     => 'smtp',
			'args'    => array(
				'type'          => 'string',
				'label_for'     => true,
				'callback_args' => array(
					'type' => 'password'
				)
			)
		)
	);

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_smtp_settings_field', 'hocwp_theme_settings_page_smtp_field' );

function hocwp_theme_settings_page_smtp_form_after() {
	?>
	<div id="poststuff">
		<div class="postbox">
			<h3 class="hndle">
				<label for="title"><?php _e( 'Testing And Debugging Settings', 'hocwp-theme' ); ?></label>
			</h3>

			<div class="inside">
				<p><?php _e( 'You can use this section to send an email from your server using the above configured SMTP details to see if the email gets delivered.', 'hocwp-theme' ); ?></p>

				<form method="post" action="">
					<?php wp_nonce_field( 'hocwp_theme_test_smtp', 'hocwp_theme_test_smtp_nonce' ); ?>
					<table class="form-table">
						<tbody>
						<tr valign="top">
							<th scope="row">
								<label for="hocwp_theme_test_smtp_to"><?php _e( 'To:', 'hocwp-theme' ); ?></label>
							</th>
							<td>
								<input id="hocwp_theme_test_smtp_to" name="hocwp_theme_test_smtp_to" value=""
								       type="email" class="regular-text">

								<p class="description"><?php _e( "Enter the recipient's email address.", 'hocwp-theme' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="hocwp_theme_test_smtp_subject">
									<?php _e( 'Subject:', 'hocwp-theme' ); ?>
								</label>
							</th>
							<td>
								<input id="hocwp_theme_test_smtp_subject" name="hocwp_theme_test_smtp_subject" value=""
								       type="text" class="regular-text">

								<p class="description"><?php _e( 'Enter a subject for your message', 'hocwp-theme' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="hocwp_theme_test_smtp_message">
									<?php _e( 'Message:', 'hocwp-theme' ); ?>
								</label>
							</th>
							<td>
								<textarea name="hocwp_theme_test_smtp_message" id="hocwp_theme_test_smtp_message"
								          rows="5" class="widefat"></textarea>

								<p class="description"><?php _e( 'Write your email message', 'hocwp-theme' ); ?></p>
							</td>
						</tr>
						</tbody>
					</table>
					<?php submit_button( __( 'Send Test', 'hocwp-theme' ) ); ?>
				</form>
			</div>
		</div>
	</div>
	<?php
}

add_action( 'hocwp_theme_settings_page_smtp_form_after', 'hocwp_theme_settings_page_smtp_form_after' );

function hocwp_theme_settings_page_smtp_admin_notices_action() {
	if ( isset( $_POST['submit'] ) ) {
		if ( HT_Util()->verify_nonce( 'hocwp_theme_test_smtp', 'hocwp_theme_test_smtp_nonce' ) ) {
			$to_email = isset( $_POST['hocwp_theme_test_smtp_to'] ) ? $_POST['hocwp_theme_test_smtp_to'] : '';
			if ( ! is_email( $to_email ) ) {
				$to_email = get_option( 'admin_email' );
			}
			if ( is_email( $to_email ) ) {
				global $phpmailer;
				$tmp = $phpmailer;
				if ( ! is_object( $phpmailer ) || ! is_a( $phpmailer, 'PHPMailer' ) ) {
					load_template( ABSPATH . WPINC . '/class-phpmailer.php' );
					load_template( ABSPATH . WPINC . '/class-smtp.php' );
					$phpmailer = new PHPMailer( true );
				}
				$subject = isset( $_POST['hocwp_theme_test_smtp_subject'] ) ? $_POST['hocwp_theme_test_smtp_subject'] : '';
				if ( empty( $subject ) ) {
					$subject = __( 'SMTP Email', 'hocwp-theme' ) . ': ' . sprintf( __( 'Test mail to %s', 'hocwp-theme' ), $to_email );
				}
				$message = isset( $_POST['hocwp_theme_test_smtp_message'] ) ? $_POST['hocwp_theme_test_smtp_message'] : '';
				if ( empty( $message ) ) {
					$message = __( 'Thank you for using HocWP, your SMTP mail settings work successfully.', 'hocwp-theme' );
				}
				$phpmailer->SMTPDebug = true;
				$phpmailer->isHTML( true );
				ob_start();
				$result       = wp_mail( $to_email, $subject, $message );
				$sent         = $result;
				$smtp_debug   = ob_get_clean();
				$test_message = '<p><strong>' . __( 'Test Message Sent', 'hocwp-theme' ) . '</strong></p>';
				ob_start();
				var_dump( $result );
				$result = ob_get_clean();
				$test_message .= '<p>' . sprintf( __( 'The result was: %s', 'hocwp-theme' ), $result ) . '</p>';
				$test_message .= '<p>' . __( 'The full debugging output is shown below:', 'hocwp-theme' ) . '</p>';
				ob_start();
				var_dump( $phpmailer );
				$mailer_debug = ob_get_clean();
				$test_message .= '<pre>' . $mailer_debug . '</pre>';
				$test_message .= '<p>' . __( 'The SMTP debugging output is shown below:', 'hocwp-theme' ) . '</p>';
				$test_message .= '<pre>' . $smtp_debug . '</pre>';
				$args = array(
					'message' => $test_message
				);
				if ( ! $sent ) {
					$args['type'] = 'error';
				}
				HOCWP_Theme_Utility::admin_notice( $args );
				$phpmailer = $tmp;
			}
		}
	}
}

add_action( 'admin_notices', 'hocwp_theme_settings_page_smtp_admin_notices_action' );