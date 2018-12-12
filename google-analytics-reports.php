<?php
/**
 * Plugin Name:     Google Analytics Reports
 * Plugin URI:      https://github.com/ko31/google-analytics-reports
 * Description:
 * Author:          ko31
 * Author URI:      https://go-sign.info
 * Text Domain:     google-analytics-reports
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         GoogleAnalyticsReports
 */

require_once( dirname( __FILE__ ) . '/vendor/autoload.php' );

//register_activation_hook( __FILE__, function() {
//	if ( ! defined( 'BOGO_VERSION' ) ) {
//		deactivate_plugins( __FILE__ );
//		exit( __( 'Sorry, Bogo is not installed.', 'bogodate' ) );
//	}
//});

GoogleAnalyticsReports::get_instance()->register();
