<?php

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

		require_once( dirname( dirname( __FILE__ ) ) . '/functions.php' );
	}

	public function plugins_loaded() {
		load_plugin_textdomain( 'google-analytics-reports', false, basename( dirname( __DIR__) ) . '/languages' );
		if ( is_admin() ) {
			GoogleAnalyticsReports\Admin::get_instance()->register();
		}
	}

	public function get_prefix() {
		return $this->prefix;
	}
}
