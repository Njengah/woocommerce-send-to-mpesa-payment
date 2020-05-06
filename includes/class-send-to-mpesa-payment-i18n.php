<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://njengah.com
 * @since      1.0.0
 *
 * @package    Send_To_Mpesa_Payment
 * @subpackage Send_To_Mpesa_Payment/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Send_To_Mpesa_Payment
 * @subpackage Send_To_Mpesa_Payment/includes
 * @author     Joe Njenga <plugins@njengah.com>
 */
class Send_To_Mpesa_Payment_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'send-to-mpesa-payment',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
