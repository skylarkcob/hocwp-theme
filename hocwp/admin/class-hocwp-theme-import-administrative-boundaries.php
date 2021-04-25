<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HOCWP_Theme_Background_Process' ) ) {
	require_once HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-background-process.php';
}

if ( ! trait_exists( 'HOCWP_Theme_Import_Administrative_Boundaries' ) ) {
	require_once HOCWP_THEME_CORE_PATH . '/admin/trait-hocwp-theme-import-administrative-boundaries.php';
}

class HOCWP_Theme_Import_Administrative_Boundaries_Process extends HOCWP_Theme_Background_Process {
	use HOCWP_Theme_Import_Administrative_Boundaries;

	/**
	 * @var string
	 */
	protected $action = 'import_administrative_boundaries';

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over
	 *
	 * @return mixed
	 */
	protected function task( $item ) {
		$this->background_import( $item );
		$this->really_long_running_task();

		return false;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		parent::complete();

		// Show notice to user or perform some other arbitrary task...
	}
}