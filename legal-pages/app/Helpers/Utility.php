<?php
namespace LegalPage\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Utility class with static helper functions for general use throughout the plugin.
 */
class Utility {

	/**
	 * Retrieves an option from the WordPress database, formatted according to Legal Page settings.
	 *
	 * This function gets an option using a combination of the provided menu, submenu, and key.
	 * If the option is not set, a default value is returned.
	 *
	 * @param string $menu The menu name or key to be used as part of the option name.
	 * @param string $submenu The submenu name or key to be used as part of the option name.
	 * @param string $key The specific option key to retrieve within the option array.
	 * @param mixed  $default Optional. The default value to return if the option key is not set. Default is an empty string.
	 *
	 * @return mixed The value of the option if it exists, or the default value if it doesn't.
	 */
	public static function get_option( $menu, $submenu, $key, $default = '' ) {
		$option = get_option( "legal_pages-{$menu}-{$submenu}" );

		if ( ! isset( $option[ $key ] ) || empty( $option[ $key ] ) ) {
			return $default;
		}

		return $option[ $key ];
	}

	/**
	 * Set an option value.
	 *
	 * @param string $menu The menu slug.
	 * @param string $submenu The submenu slug.
	 * @param string $key The option key.
	 * @param mixed  $value The value to set.
	 * @return bool True if the option was updated, false otherwise.
	 */
	public static function set_option( $menu, $submenu, $key, $value ) {
		$option_name = "legal_pages-{$menu}-{$submenu}";
		$option = get_option( $option_name, array() );

		$option[ $key ] = $value;

		return update_option( $option_name, $option );
	}

	/**
	 * Includes a template file from the 'view' directory.
	 *
	 * @param string $template The template file name.
	 * @param array  $args Optional. An associative array of variables to pass to the template file.
	 */
	public static function get_template( $template, $args = array() ) {
		$path = LEGAL_PAGES_PLUGIN_DIR . 'views/' . $template;

		if ( file_exists( $path ) ) {
			if ( ! empty( $args ) && is_array( $args ) ) {
				extract( $args );
			}

			ob_start();
			include $path;
			return ob_get_clean();
		}
	}

	/**
	 * Generates a hash
	 */
	public static function generate_hash() {
		return wp_hash( uniqid( wp_rand(), true ) );
	}
}
