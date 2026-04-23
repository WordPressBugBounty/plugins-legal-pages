<?php
namespace LegalPage\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Interface Test
 *
 * Defines the standard methods that all tests must implement.
 */
interface Test {

	/**
	 * Get the test ID.
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Get the test title.
	 *
	 * @return string
	 */
	public function get_title();

	/**
	 * Get the test description.
	 *
	 * @return string
	 */
	public function get_description();

	/**
	 * Check if the test is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled();

	/**
	 * Get the settings fields for the test.
	 *
	 * @return array
	 */
	public function get_settings_fields();
}
