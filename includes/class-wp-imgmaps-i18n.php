<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://mcdonald.me.uk/
 * @since      1.0.0
 *
 * @package    Wp_Imgmaps
 * @subpackage Wp_Imgmaps/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wp_Imgmaps
 * @subpackage Wp_Imgmaps/includes
 * @author     Ian McDonald <ian@mcdonald.me.uk>
 */
class Wp_Imgmaps_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wp-imgmaps',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
