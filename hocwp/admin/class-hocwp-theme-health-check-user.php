<?php
defined( 'ABSPATH' ) || exit;

class HOCWP_Theme_Health_Check_User extends HOCWP_Theme_Health_Check {
	public $user;
	/**
	 * The name of the test.
	 *
	 * @var string
	 */
	protected $test = 'user';

	/**
	 * Runs the test.
	 */
	public function run() {
		if ( ! $this->check_default_admin() ) {
			$this->label          = esc_html__( 'The default admin account has been changed', 'hocwp-theme' );
			$this->status         = self::STATUS_GOOD;
			$this->badge['color'] = 'blue';
			$this->description    = esc_html__( 'The default admin account contains developer information, simple passwords that are easy to remember, which can make your site accessible and unauthorized use.', 'hocwp-theme' );

			return;
		}

		$this->label          = esc_html__( 'You should change the default admin user', 'hocwp-theme' );
		$this->status         = self::STATUS_CRITICAL;
		$this->badge['color'] = 'red';

		$this->description = esc_html__( 'You are still using the default admin account. Let\'s create another admin account and decentralize this default admin account to a read-only group.', 'hocwp-theme' );

		$this->actions = sprintf(
			__( '<p>You can <a href="%s">create new administrator user</a> account, or go to the <a href="%s">administrator tools page</a> of the theme, enter the email address and click create a new admin account.</p>', 'hocwp-theme' ),
			esc_url( admin_url( 'user-new.php' ) ),
			esc_url( admin_url( 'themes.php?page=' . hocwp_theme()->get_prefix() . '&tab=administration_tools' ) )
		);
	}

	public function check_default_admin() {
		$this->user = get_user_by( 'email', 'hocwp.net@gmail.com' );

		if ( ! ( $this->user instanceof WP_User ) ) {
			$this->user = get_user_by( 'email', 'codewpvn@gmail.com' );
		}

		if ( ! ( $this->user instanceof WP_User ) ) {
			$this->user = get_user_by( 'login', 'huser' );
		}

		if ( ! ( $this->user instanceof WP_User ) ) {
			$this->user = get_user_by( 'admin', 'huser' );
		}

		if ( $this->user instanceof WP_User ) {
			if ( user_can( $this->user, 'manage_options' ) ) {
				return true;
			}
		}

		return false;
	}
}