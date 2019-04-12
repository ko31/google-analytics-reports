<?php
namespace Tarosky;


use Tarosky\GoogleAnalyticsReports\Tracker;
use Tarosky\GoogleAnalyticsReports\Uuid;

class GoogleAnalyticsReports {
	private $prefix = 'GoogleAnalyticsReports';

	public function __construct() {
	}

	public static function get_instance() {
		static $instance;
		if ( ! $instance ) {
			$instance = new GoogleAnalyticsReports();
		}

		return $instance;
	}

	public function register() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		// Load functions.
		require_once( dirname( dirname( dirname( __FILE__ ) ) ) . '/functions.php' );
		// If this is cli environment, register commands.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( \Tarosky\GoogleAnalyticsReports\Commands::COMMAND_NAME, \Tarosky\GoogleAnalyticsReports\Commands::class );
		}
	}

	public function plugins_loaded() {
		load_plugin_textdomain( 'google-analytics-reports', false, basename( dirname( __DIR__) ) . '/languages' );
		if ( is_admin() ) {
			GoogleAnalyticsReports\Admin::get_instance()->register();
		}
		Tracker::get_instance();
		Uuid::get_instance();
	}

	public function get_prefix() {
		return $this->prefix;
	}
}
