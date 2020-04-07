<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Health_Check_Email extends HOCWP_Theme_Health_Check {

	/**
	 * The name of the test.
	 *
	 * @var string
	 */
	protected $test = 'hocwp-theme-health-check-email';

	/**
	 * Runs the test.
	 */
	public function run() {
		if ( ! $this->has_default_email() ) {
			$this->label          = esc_html__( 'You changed the default admin email address or in debug mode', 'hocwp-theme' );
			$this->status         = self::STATUS_GOOD;
			$this->badge['color'] = 'blue';
			$this->description    = esc_html__( 'You are using a custom email address or a difference email address with default theme email address or your site is in debug mode.', 'hocwp-theme' );

			return;
		}

		$this->label          = esc_html__( 'You should change the default admin email address', 'hocwp-theme' );
		$this->status         = self::STATUS_RECOMMENDED;
		$this->badge['color'] = 'orange';

		$this->description = esc_html__( 'You still have the default WordPress admin email address. You must provide a valid email address for receiving notifications from your site.', 'hocwp-theme' );

		$this->actions = sprintf(
			esc_html__( '%1$sYou can change the admin email address in the site general setting page.%2$s', 'hocwp-theme' ),
			'<p><a href="' . esc_attr( admin_url( 'options-general.php' ) ) . '">',
			'</a></p>'
		);
	}

	/**
	 * Returns whether or not the site has the default email.
	 *
	 * @return bool
	 */
	public function has_default_email() {
		$value    = get_bloginfo( 'admin_email' );
		$default  = 'hocwp.net@gmail.com';
		$default2 = 'codewpvn@gmail.com';

		return ( ! HOCWP_THEME_DEVELOPING && ( $default === $value || $default2 === $value ) );
	}
}