<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class HOCWP_Theme_Health_Check {
	protected $base_id = 'hocwp-theme-health-check';

	/**
	 * The health check section in which 'good' results should be shown.
	 *
	 * @var string
	 */
	const STATUS_GOOD = 'good';

	/**
	 * The health check section in which 'recommended' results should be shown.
	 *
	 * @var string
	 */
	const STATUS_RECOMMENDED = 'recommended';

	/**
	 * The health check section in which 'critical' results should be shown.
	 *
	 * @var string
	 */
	const STATUS_CRITICAL = 'critical';

	/**
	 * The value of the section header in the Health check.
	 *
	 * @var string
	 */
	protected $label = '';

	/**
	 * Section the result should be displayed in.
	 *
	 * @var string
	 */
	protected $status = '';

	/**
	 * What the badge should say with a color.
	 *
	 * @var array
	 */
	protected $badge = [
		'label' => '',
		'color' => '',
	];

	/**
	 * Additional details about the results of the test.
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * A link or button to allow the end user to take action on the result.
	 *
	 * @var string
	 */
	protected $actions = '';

	/**
	 * The name of the test.
	 *
	 * @var string
	 */
	protected $test = '';

	/**
	 * Whether or not the test should be ran on AJAX as well.
	 *
	 * @var bool True when is async, default false.
	 */
	protected $async = false;

	/**
	 * Runs the test and returns the result.
	 */
	abstract public function run();

	/**
	 * Registers the test to WordPress.
	 */
	public function register_test() {
		if ( $this->is_async() ) {
			add_filter( 'site_status_tests', [ $this, 'add_async_test' ] );

			add_action( 'wp_ajax_health-check-' . $this->get_test_name(), [ $this, 'get_async_test_result' ] );

			return;
		}

		add_filter( 'site_status_tests', [ $this, 'add_test' ] );
	}

	/**
	 * Runs the test.
	 *
	 * @param array $tests Array with the current tests.
	 *
	 * @return array The extended array.
	 */
	public function add_test( $tests ) {
		$tests['direct'][ $this->get_test_name() ] = [
			'test' => [ $this, 'get_test_result' ],
		];

		return $tests;
	}

	/**
	 * Runs the test in async mode.
	 *
	 * @param array $tests Array with the current tests.
	 *
	 * @return array The extended array.
	 */
	public function add_async_test( $tests ) {
		$tests['async'][ $this->get_test_name() ] = [
			'test' => $this->get_test_name(),
		];

		return $tests;
	}

	/**
	 * Formats the test result as an array.
	 *
	 * @return array The formatted test result.
	 */
	public function get_test_result() {
		$this->run();
		$this->add_signature();

		return [
			'label'       => $this->label,
			'status'      => $this->status,
			'badge'       => $this->get_badge(),
			'description' => $this->description,
			'actions'     => $this->actions,
			'test'        => $this->get_test_name(),
		];
	}

	/**
	 * Formats the test result as an array.
	 */
	public function get_async_test_result() {
		wp_send_json_success( $this->get_test_result() );
	}

	/**
	 * Retrieves the badge and ensure usable values are set.
	 *
	 * @return array The proper formatted badge.
	 */
	protected function get_badge() {
		if ( ! is_array( $this->badge ) ) {
			$this->badge = [];
		}

		if ( empty( $this->badge['label'] ) ) {
			$this->badge['label'] = __( 'Theme', 'hocwp-theme' );
		}

		if ( empty( $this->badge['color'] ) ) {
			$this->badge['color'] = 'blue';
		}

		return $this->badge;
	}

	/**
	 * WordPress converts the underscores to dashes. To prevent issues we have
	 * to do it as well.
	 *
	 * @return string The formatted testname.
	 */
	protected function get_test_name() {
		if ( ! str_contains( $this->test, $this->base_id ) ) {
			$this->test = $this->base_id . '-' . $this->test;
		}

		return str_replace( '_', '-', $this->test );
	}

	/**
	 * Checks if the health check is async.
	 *
	 * @return bool True when check is async.
	 */
	protected function is_async() {
		return ! empty( $this->async );
	}

	/**
	 * Adds a text to the bottom of the Site Health check to indicate it is a Theme Site Health Check.
	 */
	protected function add_signature() {
		$this->actions .= sprintf( esc_html__( '%1$sThis was reported by %2$s.%3$s', 'hocwp-theme' ), '<p class="hocwp-theme-site-health__signature"><small><i>', 'HocWP Theme', '</i></small></p>' );
	}
}

// Add site health check items
function hocwp_theme_admin_health_check_init() {
	require_once( HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-health-check-email.php' );
	require_once( HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-health-check-user.php' );
	require_once( HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-health-check-password.php' );

	$heal_checks = array(
		new HOCWP_Theme_Health_Check_Email(),
		new HOCWP_Theme_Health_Check_User(),
		new HOCWP_Theme_Health_Check_Password()
	);

	foreach ( $heal_checks as $checker ) {
		if ( $checker instanceof HOCWP_Theme_Health_Check ) {
			$checker->register_test();
		}
	}
}

add_action( 'admin_init', 'hocwp_theme_admin_health_check_init' );