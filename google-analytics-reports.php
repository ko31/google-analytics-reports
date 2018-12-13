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

GoogleAnalyticsReports::get_instance()->register();
