<?php
/**
 * Plugin Name:     Google Analytics Reports
 * Plugin URI:      https://github.com/tarosky/google-analytics-reports
 * Description:     This is plugin to retreive data from Google Analytics Reporting API.
 * Author:          TAROSKY INC.
 * Author URI:      https://tarosky.co.jp
 * Text Domain:     google-analytics-reports
 * Domain Path:     /languages
 * Version:         nightly
 *
 * @package         GoogleAnalyticsReports
 */

require_once( dirname( __FILE__ ) . '/vendor/autoload.php' );

add_action( 'init', 'google_analytics_reports_activate_autoupdate' );

function google_analytics_reports_activate_autoupdate() {
	$plugin_slug = plugin_basename( __FILE__ ); // e.g. `hello/hello.php`.
	$gh_user = 'tarosky';                      // The user name of GitHub.
	$gh_repo = 'google-analytics-reports';       // The repository name of your plugin.
	// Activate automatic update.
	new Miya\WP\GH_Auto_Updater( $plugin_slug, $gh_user, $gh_repo );
}

Tarosky\GoogleAnalyticsReports::get_instance()->register();
