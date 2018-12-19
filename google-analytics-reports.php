<?php
/**
 * Plugin Name:     Google Analytics Reports
 * Plugin URI:      https://github.com/tarosky/google-analytics-reports
 * Description:     This is plugin to retreive data from Google Analytics Reporting API.
 * Author:          tarosky, ko31
 * Author URI:      https://tarosky.co.jp
 * Text Domain:     google-analytics-reports
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         GoogleAnalyticsReports
 */

require_once( dirname( __FILE__ ) . '/vendor/autoload.php' );

GoogleAnalyticsReports::get_instance()->register();
