<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.linkedin.com/in/christian-verzosa-4917341a1/
 * @since      1.0.0
 *
 * @package    Rymera_Movies
 * @subpackage Rymera_Movies/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Rymera_Movies
 * @subpackage Rymera_Movies/includes
 * @author     Christian Eclevia Verzosa <christian.e.verzosa@gmail.com>
 */
class Rymera_Movies_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'rymera-movies',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
