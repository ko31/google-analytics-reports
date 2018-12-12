<?php
/**
 * Plugin Name:     Bogo Date
 * Plugin URI:      https://github.com/tarosky/bogodate
 * Description:     Date format as a Bogo extension for WordPress.
 * Author:          tarosky, ko31
 * Author URI:      https://tarosky.co.jp/
 * Text Domain:     bogodate
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         Bogo_Date
 */

require_once( dirname( __FILE__ ) . '/vendor/autoload.php' );

register_activation_hook( __FILE__, function() {
	if ( ! defined( 'BOGO_VERSION' ) ) {
		deactivate_plugins( __FILE__ );
		exit( __( 'Sorry, Bogo is not installed.', 'bogodate' ) );
	}
});

BogoDate::get_instance()->register();
