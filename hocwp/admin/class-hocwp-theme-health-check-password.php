<?php
defined( 'ABSPATH' ) || exit;

class HOCWP_Theme_Health_Check_Password extends HOCWP_Theme_Health_Check {
	public $user;
	/**
	 * The name of the test.
	 *
	 * @var string
	 */
	protected $test = 'password';

	/**
	 * Runs the test.
	 */
	public function run() {
		if ( ! $this->check_weak_password() ) {
			$this->label          = esc_html__( 'Weak password not found', 'hocwp-theme' );
			$this->status         = self::STATUS_GOOD;
			$this->badge['color'] = 'blue';
			$this->description    = esc_html__( 'All admin accounts seem to use strong passwords.', 'hocwp-theme' );

			return;
		}

		$this->label          = esc_html__( 'Weak password found', 'hocwp-theme' );
		$this->status         = self::STATUS_CRITICAL;
		$this->badge['color'] = 'red';

		$this->description = esc_html__( 'One of the administrator accounts is using a weak password, try to replace all user passwords with strong passwords.', 'hocwp-theme' );

		$this->actions = sprintf(
			__( '<p>Manage <a href="%s">all administrator accounts</a> here.</p>', 'hocwp-theme' ),
			esc_url( admin_url( 'users.php?role=administrator' ) )
		);
	}

	public function check_weak_password() {
		$passwords = array(
			'admin',
			'c1khdv6H@',
			'123456',
			'123456789',
			'anhyeuem',
			'1234567890',
			'password',
			'maiyeuem',
			'12345678',
			'1234567',
			'khongbiet',
			'123123',
			'0123456789',
			'admin123',
			'root'
		);

		$args = array(
			'number' => - 1,
			'role'   => 'administrator'
		);

		$query = new WP_User_Query( $args );

		$users = $query->get_results();

		if ( ht()->array_has_value( $users ) ) {
			foreach ( $users as $user ) {
				if ( $user instanceof WP_User ) {
					foreach ( $passwords as $pass ) {
						if ( wp_check_password( $pass, $user->user_pass ) ) {
							return true;
						}
					}
				}
			}
		}

		return false;
	}
}